<?php

namespace common\modules\crossposting\components;

use common\modules\crossposting\Module as cross;
use common\modules\crossposting\models\XMLModel as model;
use common\modules\crossposting\models\interfaces\ObjectParamsIntf;
use common\modules\crossposting\models\interfaces\ObjectItemIntf;
use common\modules\crossposting\models\CedTransfert;
use common\modules\crossposting\models\CedTransfertSequence;
use common\helpers\FileHelper;
use common\modules\crossposting\models\CedPartners;

/**
 * @param \common\modules\crossposting\models\Sequence[] $sequenceList
 */
class Sequence extends \yii\base\Component {

    public $modelClass = "common\modules\crossposting\models\CedTransfertSequence";

    /**
     * @var \common\modules\crossposting\Module
     */
    public $module;

    /**
     * Максимальное количество объектов в одной порции очереди
     * @var integer
     */
    public $splitItemsSize = 1000;

    /**
     * @var \common\modules\crossposting\models\CedTransfertSequence
     */
    public $model;

    /**
     * Получает и воспроизводит очередной итем.
     * @return null | \common\modules\crossposting\models\CedTransfertSequence
     * @throws \yii\base\UserException
     */
    public function doItem($regularExport = false) {
        /* @var $item \common\modules\crossposting\models\CedTransfertSequence */
        if ($regularExport && !ctype_print($regularExport)) {
            $item = $this->getRegularSequenceExportItem();
        } elseif ($regularExport && ctype_print($regularExport)) {
            $item = $this->getSequenceItem($regularExport);
        } else {
            $item = $this->getSequenceItem();
        }
        //prer($item,1,1);
        if (!is_null($item)) {
            try {
                $item->beginItem();
                $model = CedTransfert::findOne($item->transfert_id);

                switch ($model->tr_type) {
                    case CedTransfert::TYPE_EXPORT:
                        \TLog::export($item->transfert_id, $this->module)::crosspost();
                        break;
                    case CedTransfert::TYPE_IMPORT:
                        \TLog::import($item->transfert_id, $this->module)::crosspost();
                        break;
                }

                $alias         = $model->getSettings($model->tr_type)->vendor->alias;
                \TLog::title($model->settings->title);
                $componentName = __NAMESPACE__ . "\\vendors\\" . ucfirst($alias) . ucfirst($model->tr_type);
                $this->module->setComponents([
                    $model->tr_type => [
                        "class"    => $componentName,
                        "transfer" => $model,
                        "partner"  => $model->settings->partner,
                        "vendor"   => $model->settings->vendor,
                        "settings" => $model->settings,
                    ]
                ]);

                $filename = $this->module->{$model->tr_type}->work();
                if ($filename) {
                    CedPartners::flushCacheDependency($model->settings->partner);
                    $item->done = true;
                    $item->endItem();
                    $model->finish();
                    FileHelper::zipFile($filename, $filename);
                    unlink($filename);
                    //\TLog::flush();
                    return $item;
                }
            } catch (\Exception $ex) {
                $item->doneErrors = true;
                $item->endItem();
                \TLog::error($ex->getMessage(), $ex->getFile(), $ex->getLine())::flush();
                throw new \yii\base\UserException($ex->getMessage());
            } finally {
                $item->endItem();
                \TLog::flush();
                \TLog::s_Unlink();
            }
        }
        return null;
    }

    public function getRegularSequenceExportItem() {
        $this->model = $this->modelClass::letRegularExportItem();
        return $this->model;
    }

    public function getSequenceItem() {
        //$this->module->syslog->info($this->isBusy());
        if (empty($this->isBusy())) {
            $this->model = $this->modelClass::letNextItem();
            return $this->model;
        }
    }

    public function getSequenceItemId($id) {
        $this->model = $this->modelClass::findOne($id);
        return $this->model;
    }

    /**
     * Возвращает true если в очереди есть занятые задания
     * @return boolean
     */
    public function isBusy() {
        return !empty($this->modelClass::isBusy());
    }

    /**
     * @param \common\modules\crossposting\components\BaseCross $component
     */
    public function prepareTransfer(&$component) {
        $this->model = CedTransfertSequence::findByTransferID($component->transfer->id);
        if (is_null($this->model) && ($component->transfer->settings->sheduleTransfer || $component->transfer->settings->doSequence)) {
            $this->model                = CedTransfertSequence::create($component->transfer->id,
                            $component->transfer->settings->transferInterval);
            $this->model->sheduleExport = $component->transfer->settings->sheduleTransfer;
            $this->model->filename      = $component->transfer->settings->outFiles[0];
            if ($this->model->save()) {
                \TLog::info("Создана очередь для задания");
            }
        } elseif ($this->model && ($component->transfer->settings->sheduleTransfer || $component->transfer->settings->doSequence)) {
            $this->model->sheduleExport    = $component->transfer->settings->sheduleTransfer;
            $this->model->shedule_interval = $component->transfer->settings->transferInterval;
            $this->model->filename         = $component->transfer->settings->outFiles[0];
            if (!empty($this->model->getDirtyAttributes())) {
                \TLog::info("Изменена очередь для задания");
            }
            $this->model->save();
        } elseif ($this->model && (!$component->transfer->settings->sheduleTransfer && !$component->transfer->settings->doSequence)) {
            if ($this->model->delete()) {
                \TLog::info("Удалена очередь для задания");
            }
        }
    }

    /**
     * @deprecated 
     * @param \common\modules\crossposting\components\Import | \common\modules\crossposting\components\Export $component
     */
    public function addTransfer($component) {
        if ($component instanceof Import) {
            /* @var $component \common\modules\crossposting\components\Import */
            $this->splitXML($this->module->getFullTransferFileName($component->transfer->filename),
                    $component->vendor->alias);
            $multi = false;
            if (empty($this->_files)) {
                $this->_files = [$component->transfer->filename];
            } else {
                $multi = true;
            }
            foreach ($this->_files as $file) {
                $model               = new $this->modelClass();
                $model->multy        = $multi;
                $model->created_at   = time();
                $model->transfert_id = $component->transfer->id;
                $model->filename     = $file;
                $model->save();
                $this->module->log->info(sprintf("Создана очередь для импорта %s, файл %s",
                                $model->transfert_id, $model->filename));
            }
            if ($multi) {
                try {
                    unlink(\yii::getAlias($this->module->getFullTransferFileName($component->transfer->filename)));
                } catch (\Exception $ex) {
                    $this->module->log->error($ex->getMessage());
                }
            }
        }
        if ($component instanceof Export) {
            /* @var $component \common\modules\crossposting\components\Export */
            /* Устанавливаем количество итераций экспорта */
            $objectCount = $component->letParts();

            $component->settings->partsNum = ceil($objectCount / $component->settings->exportItemsLimit);

            for ($i = 0; $i < $component->settings->partsNum; $i++) {
                $model               = new $this->modelClass();
                $model->multy        = $component->settings->partsNum > 1;
                $model->created_at   = time();
                $model->transfert_id = $component->transfer->id;
                $model->filename     = $model->multy ? "{$component->transfer->id}_part{$i}.xml"
                            : "{$component->transfer->id}.xml";
                if ($i == 0) {
                    //self::_prepareExportShedule($model, $component);
                }
                $model->save();
                $component->settings->outFiles[] = $model->filename;
                $this->module->log->info(sprintf("Создана очередь для экспорта %s, файл %s",
                                $model->transfert_id, $model->filename));
            }
            $component->transfer->setAttribute("settings",
                    $component->settings->toJSON());
            $component->transfer->save();
        }
    }

    /**
     * @param \common\modules\crossposting\components\Import | \common\modules\crossposting\components\Export $component
     */
    public function updateTransfer(&$component) {
        if ($component instanceof Export) {
            /* @var $component \common\modules\crossposting\components\Export */
            /* Устанавливаем количество итераций экспорта */
            $objectCount = $component->letParts();

            $component->getSettings()->partsNum = ceil($objectCount / $component->transfer->settings->exportItemsLimit);

            $this->model = $component->transfer->sequenceOne;

            //self::_prepareExportShedule($this->model, $component);

            $this->model->save();
            $this->module->log->info(sprintf("Обновлена очередь для экспорта %s, файл %s",
                            $this->model->transfert_id, $this->model->filename));
            $component->transfer->setAttribute("settings",
                    $component->settings->toJSON());
            $component->transfer->save();
        } elseif ($component instanceof Import) {
            /* @var $component \common\modules\crossposting\components\Import */
            $this->model = $component->transfer->sequenceOne;
            if (!$this->model) {
                $this->addTransfer($component);
            }
            $this->module->log->info(sprintf("Обновлена очередь для импорта %s, файл %s",
                            $this->model->transfert_id, $this->model->filename));
            $component->transfer->setAttribute("settings",
                    $component->settings->toJSON());
            $component->transfer->save();
        }
    }

    /**
     * @deprecated 
     * @param CedTransfertSequence $model
     * @param \common\modules\crossposting\components\Export $exportComponent
     */
    static function _prepareExportShedule(CedTransfertSequence &$model,
            Export &$exportComponent) {
        /* Находим очередь с установленным флагом регулярного использования */
        $issetSequence = CedTransfertSequence::findSheduleExport();
        foreach ($issetSequence as $seq) {
            /* Если таковой имеется, то убираем из него все признаки регулярного использования */
            if ($seq->id != $model->id) {
                $seq->sheduleExport = false;
                $seq->save();
                /* В модели экспорта тоже сбрасываем регулярный запуск */
                CedTransfert::findOne($seq->transfert_id)->unShedule(true);
            }
        }

        /* Теперь установим нужные флаги в текущие позиции, которые передали в параметрах */
        $model->sheduleExport = $exportComponent->getSettings()->sheduleExport;
    }

    private $_files = [];

    /**
     * Делит XML файл на части, если количество объектов в нём превышает self::splitItemsSize
     * 
     * @param string $fileName Имя исходного файла. Может быть с регистрированным алиасом
     * @param string $vendorID ID партнёра
     */
    public function splitXML($fileName, $vendorID) {
        /* @var $input \common\modules\crossposting\models\interfaces\ObjectParamsIntf */
        /* @var $output \common\modules\crossposting\models\interfaces\ObjectParamsIntf */
        $input  = null;
        $output = null;
        model::create($vendorID, null, \yii::getAlias($fileName), $input);
        $length = $input->getObjectItems()->length;
        if ($length > $this->splitItemsSize) {
            $pathInfo = pathinfo($fileName);
            $parts    = ceil($length / $this->splitItemsSize);
            $idx      = 0;
            for ($p = 0; $p < $parts; $p++) {
                model::create($vendorID, null, null, $output);
                $output->document->formatOutput = true;
                $output->setRoot($output->getRootNodeName());
                $output->setObjectItemsRoot();
                for ($i = 0; $i < $this->splitItemsSize; $i++) {
                    $item = $input->getObjectItems()->item($idx);
                    if ($item) {
                        $item = $output->document->importNode($item, true);
                        $output->getObjectItemsRoot()->appendChild($item);
                    } else {
                        break;
                    }
                    $idx++;
                }
                $ofn            = $pathInfo["filename"] . "_part{$p}" . ".{$pathInfo["extension"]}";
                $outputFileName = \yii::getAlias($pathInfo["dirname"] . "/" . $ofn);
                if (file_put_contents($outputFileName,
                                $output->document->saveXML())) {
                    $this->_files[] = $ofn;
                    $ev             = new \common\modules\crossposting\CrossEvent();
                    $ev->detail     = ["splitFile" => $outputFileName];
                    $this->module->trigger(cross::EV_SPLIT_FILE_CREATED, $ev);
                }
            }
            $this->module->trigger(cross::EV_SPLIT_OK);
        }
    }

}

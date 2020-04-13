<?php

namespace common\modules\crossposting\components;

use \common\modules\crossposting\Module as cross;
use common\modules\crossposting\models\CedTransfert;
use common\modules\crossposting\CrossEvent;
use common\components\CEDException;
use common\helpers\ArrayHelper as ah;
use common\models\dictionary\Helper;
use common\models\CedDictionaryItemsPartnerMap as cdipm;
use common\modules\geobase\models\Linking;
use common\modules\crossposting\models\xml\vendors\homesoverseas\Document as xml;
use common\modules\crossposting\models\CedPartnersObjectsMap as cpom;
use common\models\dictionary\CedDictionaryItem as cdi;
use common\modules\crossposting\models\CedObjectsMediaSequence as coms;
use yii\db\Expression as exp;
use common\models\dictionary\CedDictionaryItem;
use common\models\CedObjects;
use common\models\CedObjectsSimple;

class Import extends BaseCross {

    const ITEM_TYPE_SALE = "sale";

    /**
     * @var \common\modules\crossposting\models\xml\vendors\homesoverseas\Document
     */
    protected $xml;

    /**
     * Термины словарей CED
     * @var array
     */
    public $dictionaryTerms;

    /**
     * Термины словарей для вендора
     * @var array
     */
    public $terms;

    /**
     * Термины словарей для вендора
     * @var array
     */
    public $proptypeTerms;

    /**
     * Карта геонеймов для вендора
     * @var array
     */
    public $geomap;

    /**
     * @param \common\modules\crossposting\models\ImportSettings $settings
     */
    public function create($settings = null) {
        if (!$settings) {
            throw new CEDException("Для создания импорта необходимы настройки ImportSettings.");
        }
        $this->settings = $settings;
        $this->partner  = $settings->getPartner();
        parent::create();

        $this->transfer = CedTransfert::createImport(
                        \yii::$app->user->identity->id, $this->partner->id,
                        $this->settings);
        \TLog::import($this->transfer->id, $this->module)::title("Новый импорт")::info("Создан импорт");

        $this->transfer->getSettings(CedTransfert::TYPE_IMPORT)->transferID = $this->transfer->id;
        $this->transfer->getSettings(CedTransfert::TYPE_IMPORT)->outFiles[] = "{$this->transfer->id}.xml";

        /* заглушка */
//        copy(\yii::getAlias("@common/models/partners/common/import/tg20190226.xml"),
//                            \yii::getAlias("{$this->module->transferFilePath}/{$this->transfer->filename}"));
//        $ev         = new CrossEvent();
//        $ev->detail = &$this;
//        $this->module->trigger(cross::EV_IMPORT_CREATED, $ev);
    }

    protected function beginWork() {
        \TLog::success("Начало импорта");
        if (!$this->partner->permanent_link) {
            \TLog::warning("У партнёра отсутствует постоянный линк на файл данных");
        } else {
            $data  = self::loadRemoteFile($this->partner->permanent_link);
            $bytes = $this->moveDataFile($data);
            if ($bytes) {
                \TLog::success("Загружены данные {$bytes} байт");
            }
        }

        $filename  = $this->module->getFullTransferFileName($this->transfer->filename);
        $this->xml = xml::create("{$this->module->vendorsNamespace}\\{$this->vendor->alias}",
                        "", $filename);
        $this->xml->loadFile();
        \TLog::info("Обнаружено {$this->xml->objectsLength} объектов");

        \TLog::Log()->data->getStatistic()->supposedNum = $this->xml->objectsLength;

        $this->prepareData();
    }

    public function prepareData() {
        $this->letDictionaryTerms();
        $this->letTerms();
        $this->letProptypeTerms();
        $this->letGeomap();
    }

    protected function letDictionaryTerms() {
        try {
            $this->dictionaryTerms = Helper::CedObjectTerms();
            \TLog::success("Получена карта свойств объектов.");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function letTerms() {
        try {
            $this->terms = ah::map(cdipm::find()
                                    ->joinWithoutProptypes()
                                    ->andWhere(["partner_id" => $this->vendor->id])->all(),
                            "partner_dict_item", "ced_dict_item");
            \TLog::success("Получена карта свойств вендора.");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function letProptypeTerms() {
        try {
            $this->proptypeTerms = ah::map(cdipm::find()
                                    ->joinWithProptypes()
                                    ->andWhere(["partner_id" => $this->vendor->id])->all(),
                            "partner_dict_item", "ced_dict_item");
            \TLog::success("Получена карта типов недвижимости вендора.");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function letGeomap() {
        try {
            $this->geomap = ah::map(Linking::findAll(["partner_id" => is_null($this->vendor->geo_partner)
                                    ? $this->vendor->id : $this->vendor->geo_partner]),
                            "partner_geo_id", "ced_geoname_id");
            \TLog::success("Получена карта географии вендора.");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Ищет объект в картах партнёров.
     * @param string $id
     * @return cpom | null
     */
    protected function findOwnerObject($id) {
        return cpom::findVendorProperty($this->getVendorId(), $id)->one();
    }

    protected function getVendorPropValue($vendorPropID) {
        return $this->terms[$vendorPropID];
    }

    protected function prepareGeoname(&$cedObject, &$item) {
        
    }

    protected function moveDataFile($data) {
        return file_put_contents($this->module->getFullTransferFileName($this->transfer->filename),
                $data);
    }

    /**
     * Загрузка удалённоо файла импорта
     * @param string $url
     */
    public static function loadRemoteFile($url) {
        $options = [
            "http" => [
                "user_agent" => \yii::$app->params["own.userAgent"],
                "timeout"    => 3600,
            ]
        ];
        $context = stream_context_create($options);
        try {
            \TLog::info(sprintf("Пытаюсь загрузить данные с %s", $url));
            return file_get_contents($url);
        } catch (\Exception $ex) {
            \TLog::error(sprintf("Не удалось загрузить данные с %s", $url));
            throw new \common\components\CEDException($ex->getMessage());
        }
    }

    protected function uploadPhoto($remoteURL, $objectID) {
        return coms::add($objectID, $remoteURL, $this->transfer->id);
    }

    public static function sequencePhotos($numItems = 100) {
        coms::$sequencePartCount = $numItems;
        coms::$temporaryDir      = sys_get_temp_dir();
        $seq                     = coms::letPart();
        if (is_array($seq) && count($seq)) {
            foreach ($seq as $photo) {
                try {
                    if ($photo->uploadPhoto()) {
                        $photo->delete();
                    }
                } catch (\Exception $ex) {
                    $photo->delete();
                }
            }
        }
    }

    public function clearObjectPropAttributes(&$cedObject) {
        if (!$cedObject) {
            throw new \yii\base\UserException("Нет объекта");
        }
        try {
            foreach ($cedObject->mainPropsList as $propID => $prop) {
                /* @var $prop \common\models\dictionary\CedDictionaryItem */
                try {
                    if ($prop->_flags_import & $this->settings->importFlags) {
                        if ($cedObject->mainPropsList[$prop]->_flags & CedDictionaryItem::_FLAG_TERMIN_VALUE_IS_SET) {
                            $cedObject->{$prop} = [];
                        } else {
                            $cedObject->{$prop} = null;
                        }
                    }
                } catch (Exception $ex) {
                    prer($ex);
                }
            }
        } catch (\Exception $ex) {
            
        }
        return $this;
    }

    public function saleUnavailables() {
        $cnt          = 0;
        $availables   = $this->xml->getIds();
        $unavailables = CedObjectsSimple::findUnavailables($availables,
                        $this->vendor->id, $this->partner->id)->all();
        foreach ($unavailables as $obj) {
            if ($obj->status == CedObjects::STATUS_PUBLISHED) {
                $obj->importID = $this->transfer->id;

                \TLog::s_incSaled();

                $cnt += $obj->changeStatus(CedObjects::STATUS_SALED);
            }
        }
        \TLog::success(sprintf("%d объектам установлен флаг «Продано»", $cnt));
    }

}

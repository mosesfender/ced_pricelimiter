<?php

namespace common\modules\crossposting;

use common\models\CedPartners;
use common\helpers\FileHelper;

/**
 * crosspost module definition class
 * @param \common\modules\crossposting\components\Sequence $sequence
 * @param \common\components\Log $log
 * @param \common\components\Log $syslog
 * @param \common\modules\crossposting\components\Import $import
 * @param \common\modules\crossposting\components\Export $export
 */
class Module extends \yii\base\Module {

    /** EVENTS */
    const EV_SPLIT_FILE_CREATED = "split_file_created";
    const EV_SPLIT_OK           = "split_ok";
    const EV_IMPORT_CREATED     = "import_created";
    const EV_EXPORT_CREATED     = "export_created";

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace       = "common\\modules\\crossposting\\controllers";
    public $componentsNamespace       = "common\\modules\\crossposting\\components";
    public $componentsVendorNamespace = "common\\modules\\crossposting\\components\\vendors";
    public $modelsNamespace           = "common\\modules\\crossposting\\models";
    public $vendorsNamespace          = "common\\modules\\crossposting\\models\\xml\\vendors";
    public $transferFilePath          = "@wrap/transfer/files";
    public $transferLogPath           = "@wrap/transfer/logs";
    public $temporaryPath             = "@wrap/transfer/temp";
    public $linkBasePath              = "https://domire.ru/crosspost/";
    public $clearTmpFilesAfterSec     = 86400;
    public $clearActionLogAfterDays   = 20; // Удалять записи логов кросспоста через дней
    private $_definitions             = [
        "components" => [
            "sequence" => [
                "class" => "common\\modules\\crossposting\\components\\Sequence"
            ],
            "log"      => [
                "class"   => "common\\components\\Log",
                "logPath" => "@wrap/transfer/logs",
            ],
            "syslog"   => [
                "class"   => "common\\components\\Log",
                "logPath" => "@wrap/transfer/logs",
                "logFile" => "syslog.log",
            ],
            "import"   => [
                "class" => "common\\modules\\crossposting\\components\\Import",
            ],
            "export"   => [
                "class" => "common\\modules\\crossposting\\components\\Export",
            ]
        ]
    ];

    public function __construct($id, $parent = null, $config = array()) {
        $config = \yii\helpers\ArrayHelper::merge($config, $this->_definitions);
        parent::__construct($id, $parent, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function init() {
        parent::init();

        foreach ([$this->transferFilePath, $this->transferLogPath, $this->temporaryPath] as $path) {
            $dirname = \yii::getAlias($path);
            if (!is_dir($dirname)) {
                FileHelper::createDirectory($dirname);
            }
        }

        models\XMLModel::$module = &$this;
        $this->on(self::EV_SPLIT_FILE_CREATED,
                  function($ev) {
            
        });
        $this->on(self::EV_SPLIT_OK, function($ev) {
            
        });
        /**
         * @param \common\modules\crossposting\components\Import
         */
        $this->on(self::EV_IMPORT_CREATED,
                  function(CrossEvent $ev) {
            $this->sequence->addTransfer($ev->detail);
        });
        /**
         * @param \common\modules\crossposting\components\Export
         */
        $this->on(self::EV_EXPORT_CREATED,
                  function(CrossEvent $ev) {
            $this->sequence->addTransfer($ev->detail);
        });
    }

    public function get($id, $throwException = true) {
        if (!isset($this->module)) {
            return parent::get($id, $throwException);
        }

        $component = parent::get($id, false);
        if ($component === null) {
            $component = $this->module->get($id, $throwException);
        }
        try {
            $component->module = &$this;
        } catch (\Exception $ex) {
            
        }
        return $component;
    }

    public function letPartner($id) {
        return CedPartners::findOne($id);
    }

    public function getFullTransferFileName($filename) {
        return \yii::getAlias("{$this->transferFilePath}/{$filename}");
    }

    public function getFullTmpFileName($filename) {
        return \yii::getAlias("{$this->temporaryPath}/{$filename}");
    }

    /**
     * Очищает временную директорию от файлов старше время создания + интервал, 
     * указанный в self::$clearTmpFilesAfterSec
     * 
     * @return array Список удалённых файлов
     */
    public function clearTemporary() {
        $ret   = [];
        $files = FileHelper::findFiles(\yii::getAlias($this->temporaryPath));
        foreach ($files as $file) {
            if ((filemtime($file) + $this->clearTmpFilesAfterSec) < time()) {
                $ret[] = $file;
                unlink($file);
            }
        }
        return $ret;
    }

    /**
     * Возвращает массив импортёров [id => title, …]
     * Список получает из названий имеющихся компонентов-вендоров импорта.
     * @return array
     */
    public function getImporters() {
        $res = [];
        $dir = FileHelper::normalizePath(\yii::getAlias("@wrap") . DIRECTORY_SEPARATOR . $this->componentsVendorNamespace,
                                                        DIRECTORY_SEPARATOR);
        foreach (FileHelper::findFiles($dir) as $file) {
            $pi   = pathinfo($file);
            $_tmp = stristr($pi["filename"], "import", true);
            if ($_tmp) {
                $model = models\CedPartners::find()
                        ->andFilterCompare("LOWER(alias)", strtolower($_tmp))
                        ->one();
                if ($model) {
                    $res[$model->id] = $model->title;
                }
            }
        }
        return $res;
    }

}

class CrossEvent extends \yii\base\Event {

    public $detail;

}

require_once realpath(__DIR__ . '/components/TLog.php');

<?php

namespace common\modules\crossposting\models;

use yii\base\Model;
use common\models\CedPartners;
use common\helpers\ArrayHelper;

/**
 * @param \common\models\CedPartners $partner
 * @param \common\models\CedPartners $vendor
 */
class TransferSettings extends Model {

    const STAGE_PRE             = 0x1;
    const STAGE_POST            = 0x2;
    const STAGE_CHANGE_VIEWMODE = 0x4;
    const STAGE_EDIT            = 0x8;
    const LIST_MODE_SIMPLE      = 0x10;
    const LIST_MODE_SEPARATED   = 0x20;

    public $stage;
    public $viewMode         = 0x10;
    public $transferID;
    public $partnerID;
    public $vendorID;
    public $title;
    public $sheduleTransfer  = 0;
    public $transferInterval = 1;
    public $doSequence       = 0;
    private $_oldAttributes;

    /**
     * Количество файлов экспорта (итераций очереди)
     * @var integer
     */
    public $partsNum = 1;

    /**
     * Имена выходных файлов
     * @var array
     */
    public $outFiles = [];

    /**
     * Индекс текущего файла из списка self::$outFiles
     * @var int
     */
    public $currentFile = 0;

    public function rules() {
        return [
            [["stage", "partnerID", "vendorID"], "required"],
            [["stage", "viewMode", "partnerID", "vendorID", "transferInterval",
            "sheduleTransfer", "doSequence"], "integer"],
            ["stage", "default", "value" => self::STAGE_POST],
            ["viewMode", "default", "value" => self::LIST_MODE_SIMPLE],
            ["string", "default", "value" => ""],
            [["transferID", "title"], "string"],
        ];
    }

    public function attributeLabels() {
        return ArrayHelper::merge(parent::attributeLabels(),
                        [
                    "transferInterval" => "Периодичность запуска",
                    "sheduleTransfer"  => "Периодическое выполнение",
                    "doSequence"       => "Поместить в очередь для однократного выполнения",
        ]);
    }

    /**
     * Возвращает готовую модель из сохранённой сериализованной записи у вендора
     * @param integer $vendorID ID партнёра
     * @return \common\modules\crossposting\models\TransferSettings
     */
    public static function ModelFromVendor($vendorID) {
        
    }

    /**
     * Возвращает готовую модель из сохранённой сериализованной записи у трансфера
     * @param integer $transferID ID трансфера
     */
    public static function ModelFromTransfer($transferID) {
        
    }

    public function toJSON() {
        return json_encode($this);
    }

    public function fromJson($json) {
        $this->setAttributes((array) json_decode($json), false);
        $this->_oldAttributes = $this->attributes;
        return $this;
    }

    public function getPartner() {
        return CedPartners::findOne($this->partnerID);
    }

    public function getVendor() {
        return CedPartners::findOne($this->vendorID);
    }

    public function getDirtyAttributes() {
        $ret = [];
        foreach ($this->attributes as $key => $value) {
            if ($this->_oldAttributes[$key] != $value) {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    public static function Intervals() {
        return [
            86400  => "1 день",
            172800 => "2 дня",
            259200 => "3 дня",
            432000 => "5 дней",
            864000 => "10 дней",
        ];
    }

}

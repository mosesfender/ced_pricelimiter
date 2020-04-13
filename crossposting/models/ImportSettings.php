<?php

namespace common\modules\crossposting\models;

use yii\base\Model;
use common\models\CedPartners;
use common\helpers\ArrayHelper;
use common\helpers\BitwiseHelper;

/**
 * @property \common\models\CedPartners[] $partners
 */
class ImportSettings extends TransferSettings {

    /**
     * Общее количество объектов импорта
     * @var int
     */
    public $itemsCount = 0;

    /**
     * Флаги импорта
     * @var int
     */
    public $importFlags = 0;

    public function rules() {
        return ArrayHelper::merge(parent::rules(),
                                  [
                    [["itemsCount", "importFlags"], "integer"],
                    [["itemsCount", "importFlags"], "default", "value" => 0],
        ]);
    }

    public function attributeLabels() {
        return ArrayHelper::merge(parent::attributeLabels(),
                                  [
                    "itemsCount"   => "Объектов в импорте",
                    "vendor.title" => "Вендор импорта",
                    "title"        => "Название",
                    "importFlags"  => "Флаги импорта",
        ]);
    }

    public function setAttributes($values, $safeOnly = true) {
        if (is_array($values)) {
            $attributes = array_flip($safeOnly ? $this->safeAttributes() : $this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    if ($name == "importFlags" && is_array($value)) {
                        $value = BitwiseHelper::ArrayValues2Bitwise($value);
                    }
                    $this->$name = $value;
                } elseif ($safeOnly) {
                    $this->onUnsafeAttribute($name, $value);
                }
            }
        }
    }

    /**
     * @iheritdoc
     * @return \common\modules\crossposting\models\ImportSettings
     */
    public static function ModelFromVendor($vendorID) {
        $ret    = new ImportSettings([
            "vendorID" => $vendorID,
        ]);
        $vendor = CedPartners::findOne($vendorID);
        if ($vendor) {
            try {
                return $ret->fromJson($vendor->import_settings);
            } catch (\Exception $ex) {
                
            }
        }
        return $ret;
    }

    public static function ModelFromTransfer($transferID) {
        $ret      = new ImportSettings([
            "transferID" => $transferID,
        ]);
        $transfer = CedTransfert::findOne($transferID);
        if ($transfer) {
            try {
                return $ret->fromJson($transfer->settings);
            } catch (\Exception $ex) {
                
            }
        }
        return $ret;
    }

    public function getPartners() {
        return $this->getPartnersQuery()->all();
    }

    /**
     * @return \common\models\CedPartnersQuery
     */
    public function getPartnersQuery() {
        return CedPartners::find();
    }

    public function toJSON() {
        if (is_array($this->importFlags)) {
            $this->importFlags = BitwiseHelper::ArrayValues2Bitwise($this->importFlags);
        }
        return json_encode($this);
    }

}

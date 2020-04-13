<?php

namespace common\modules\crossposting\models;

use common\helpers\ArrayHelper;
use common\modules\crossposting\models\CedTransfert;
use common\modules\crossposting\Module;

/**
 * @property CedTransfert $transfer
 */
class CedTransfertSequenceWork extends \yii\db\ActiveRecord {

//    /**
//     * @var array attribute values indexed by attribute names
//     */
//    private $_attributes = [];
//
//    /**
//     * @var array|null old attribute values indexed by attribute names.
//     * This is `null` if the record [[isNewRecord|is new]].
//     */
//    private $_oldAttributes;
//
//    /**
//     * @var array related models indexed by the relation names
//     */
//    private $_related = [];
//
//    /**
//     * @var array relation names indexed by their link attributes
//     */
//    private $_relationsDependencies = [];

    public static function tableName() {
        return "ced_transfert_sequence";
    }

    public function attributes() {
        return ArrayHelper::merge(parent::attributes(),
                                  ["t", "diff", "pcaption", "__stat", "begin_time"]);
    }

    /**
     * {@inheritdoc}
     * @return CedTransfertSequenceWorkQuery the active query used by this AR class.
     */
    public static function find() {
        return new CedTransfertSequenceWorkQuery(get_called_class());
    }

    /**
     * @return CedTransfertSequenceWorkQuery the active query used by this AR class.
     */
    public static function findG() {
        return new CedTransfertSequenceWorkQuery(get_called_class());
    }

    public function getTransfer() {
        return $this->hasOne(CedTransfert::class, ["id" => "transfert_id"]);
    }

    public function getBeginTime() {
        $dt = new \DateTime();
        return $this->begin_time - $dt->getOffset();
    }

    public function setExtra($val) {
        $this->extra_start = (int) $val;
    }

    public static function populateRecord($record, $row) {
        parent::populateRecord($record, $row);
        $columns = array_flip($record->attributes());
        /* @var $cm \common\modules\crossposting\Module */
        $cm      = \yii::$app->getModule("crosspost");
        $_fn     = \yii::getAlias("{$cm->temporaryPath}/{$row["transfert_id"]}_stat");
        //prer([(int)$row["_flags"], $_fn, file_exists($_fn)], 1);
        if (((int) $row["_flags"] & 0x1) && (file_exists($_fn))) {
            //prer([file_get_contents($_fn)]);
            $record->setAttribute("__stat", json_decode(file_get_contents($_fn)));
        }
        foreach ($row as $name => $value) {
            if ($name == "diff") {
                $record->setAttribute("pcaption",
                                      self::prepareInterval($value, $row));
            }
            if ($name == "pcaption" || $name == "__stat") {
                continue;
            }
            if (isset($columns[$name])) {
                $record->setAttribute($name, $value);
            } elseif ($record->canSetProperty($name)) {
                $record->$name = $value;
            }
        }
    }

    private static function prepareInterval($sec, $row) {
        $day;
        $hour;
        $min;
        $parse = function($sec)use(&$day, &$hour, &$min) {
            $dayP  = $sec % 86400;
            $day   = floor($sec / 86400);
            $hourP = $dayP % 3600;
            $hour  = floor($dayP / 3600);
            $min   = floor($hourP / 60);
        };

        
        
        if ($row["extra_start"] == 1 && !($row["_flags"] & 0x1)) {
            return "Будет запущен вне очереди в ближайшее время";
        }
        if ($row["_flags"] & 0x1) {
            return "Запущен";
        }
        if (!($row["_flags"] & 0x20)) {
            return "Будет запущен через несколько минут";
        }
        if ($sec <= 0) {
            $parse(abs($sec));
            return "Должен был быть запущен " .
                    ($day ? "{$day} д. " : "") .
                    ($hour ? "{$hour} ч. " : "") .
                    ($min ? "{$min} мин. " : "") .
                    " назад";
        }

        $parse($sec);
        return "Будет запущен через ~ " .
                ($day ? "{$day} д. " : "") .
                ($hour ? "{$hour} ч. " : "") .
                ($min ? "{$min} мин. " : "");
    }

}

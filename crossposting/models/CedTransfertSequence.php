<?php

namespace common\modules\crossposting\models;

use Yii;
use common\modules\crossposting\models\CedTransfertSequenceWorkQuery;

/**
 * This is the model class for table "ced_transfert_sequence".
 *
 * @property int $id
 * @property string $transfert_id
 * @property int $created_at
 * @property int $begin_at
 * @property int $end_at
 * @property string $filename
 * @property int $_flags
 * @property int $shedule_interval
 * @property int $begin_time
 * @property int $extra_start
 * 
 * @property CedTransfert $transfer
 * 
 * **** flags *****
 * @property int $busy
 * @property int $done
 * @property int $multy
 * @property int $doneErrors
 * @property int $sheduleExport
 */
class CedTransfertSequence extends \yii\db\ActiveRecord {

    /** Занимает очередь */
    const FLAG_BUSY = 0x1;

    /* Задание завершено */
    const FLAG_DONE = 0x2;

    /* Задание на несколько файлов */
    const FLAG_MULTI = 0x4;

    /* Задание на несколько файлов завершено полностью */
    const FLAG_MULTI_DONE = 0x8;

    /* Задание завершено c ошибкой */
    const FLAG_DONE_ERROR = 0x10;

    /* Задание для регулярного выполнения по расписанию */
    const FLAG_REGULAR_EXPORT = 0x20;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'ced_transfert_sequence';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['created_at', 'begin_at', 'end_at', '_flags', 'shedule_interval', 'begin_time',
            'extra_start'],
                'integer'],
            [['_flags', 'shedule_interval'], 'default', 'value' => 0],
            [['transfert_id', 'filename'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id'               => 'ID',
            'transfert_id'     => 'Transfert ID',
            'created_at'       => 'Created At',
            'begin_at'         => 'Begin At',
            'end_at'           => 'End At',
            'filename'         => 'Filename',
            '_flags'           => 'Flags',
            'shedule_interval' => 'Интервал автозапуска',
        ];
    }

    /**
     * {@inheritdoc}
     * @return CedTransfertSequenceQuery the active query used by this AR class.
     */
    public static function find() {
        return new CedTransfertSequenceQuery(get_called_class());
    }

    public static function findNextItem() {
        return new CedTransfertSequenceWorkQuery(get_called_class());
    }

    /**
     * @param string $transferID
     * @return CedTransfertSequence
     */
    public static function findByTransferID($transferID) {
        return self::findOne(["transfert_id" => $transferID]);
    }

    /**
     * Возвращает модель очереди задания для регулярного экспорта
     * @return CedTransfertSequence[]
     */
    public static function findSheduleExport() {
        return self::find()->where(["&", "_flags", self::FLAG_REGULAR_EXPORT])->all();
    }

    /**
     * 
     * @param string $transferID
     * @param int $sheduleInterval
     * @return \common\modules\crossposting\models\CedTransfertSequence
     */
    public static function create($transferID, $sheduleInterval) {
        return new CedTransfertSequence([
            "transfert_id"     => $transferID,
            "shedule_interval" => $sheduleInterval,
            "created_at"       => time()
        ]);
    }

    /**
     * Возвращает очередное задание из ещё не выполненных
     * @return \common\modules\crossposting\models\CedTransfertSequence | null
     */
    public static function letNextItem() {
//        $ret = self::find()
//                ->where(["NOT", ["&", "_flags", self::FLAG_DONE | self::FLAG_DONE_ERROR]]);
        $ret = self::findNextItem()
                ->having(["OR",
            ["<", "diff", 0],
            ["extra_start" => 1]
        ]);
        //prer($ret->createCommand()->rawSql,0,1);
        return $ret->one();
    }

    public static function letRegularExportItem() {
        $ret = self::find()
                ->where(["&", "_flags", self::FLAG_REGULAR_EXPORT]);
        return $ret->one();
    }

    public function getTransfer() {
        return $this->hasOne(CedTransfert::class, ["id" => "transfert_id"]);
    }

    /**
     * Возвращает занятые задания очереди
     * @return \common\modules\crossposting\models\CedTransfertSequence[] | []
     */
    public static function isBusy() {
        return self::find()
                        ->where(["&", "_flags", self::FLAG_BUSY])
                        ->all();
    }

    public function beginItem() {
        $this->begin_at = time();
        $this->setBusy(true);
        $this->save();
    }

    public function endItem() {
        $this->end_at = time();
        $this->setBusy(false);
        $this->setExtra(false);
        $this->save();
        if (!$this->getSheduleExport()) {
            $this->delete();
        }
    }

    public function setExtra($val) {
        $this->extra_start = (int) $val;
    }

    public function setBusy($val) {
        if ($val) {
            $this->_flags = $this->_flags | self::FLAG_BUSY;
        } else {
            $this->_flags = $this->_flags & ~ self::FLAG_BUSY;
        }
    }

    public function setDone($val) {
        if ($val) {
            $this->_flags = $this->_flags | self::FLAG_DONE;
        } else {
            $this->_flags = $this->_flags & ~ self::FLAG_DONE;
        }
    }

    public function getMulty() {
        return $this->_flags & self::FLAG_MULTI;
    }

    public function setMulty($val) {
        if ($val) {
            $this->_flags = $this->_flags | self::FLAG_MULTI;
        } else {
            $this->_flags = $this->_flags & ~ self::FLAG_MULTI;
        }
    }

    public function getDoneErrors() {
        return $this->_flags & self::FLAG_DONE_ERROR;
    }

    public function setDoneErrors() {
        $this->_flags = $this->_flags & ~ self::FLAG_DONE_ERROR;
    }

    public function getSheduleExport() {
        return $this->_flags & self::FLAG_REGULAR_EXPORT;
    }

    public function setSheduleExport($val) {
        if ((int) $val) {
            $this->_flags = $this->_flags | self::FLAG_REGULAR_EXPORT;
        } else {
            $this->_flags = $this->_flags & ~ self::FLAG_REGULAR_EXPORT;
        }
    }

}

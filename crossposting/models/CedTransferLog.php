<?php

namespace common\modules\crossposting\models;

use Yii;
use common\components\ServiceLogEvent;

/**
 * This is the model class for table "ced_transfer_log".
 *
 * @property int $id
 * @property string $transfer_id
 * @property int $begin_at
 * @property int $end_at
 * @property string $transfer_data
 * @property int $_flags
 *
 * @property CedTransferLogModel $data
 * @property \common\modules\crossposting\Module $module
 */
class CedTransferLog extends \yii\db\ActiveRecord {

    const FLAG_TYPE_EXPORT           = 0x1;
    const FLAG_TYPE_IMPORT           = 0x2;
    const FLAG_TYPE_CROSSPOST_ACTION = 0x4;

    /**
     *
     * @var \common\modules\crossposting\Module
     */
    private $_module;

    /**
     * @var CedTransferLogModel
     */
    private $_data;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->_data = new CedTransferLogModel(["owner" => $this]);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'ced_transfer_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['transfer_id'], 'required'],
            [['begin_at', 'end_at'], 'integer'],
            [['transfer_id'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id'          => 'ID',
            'transfer_id' => 'Transfer ID',
            'begin_at'    => 'Begin At',
            'end_at'      => 'End At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCedTransferLogData() {
        return $this->hasOne(CedTransferLogData::className(), ['id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return CedTransferLogQuery the active query used by this AR class.
     */
    public static function find() {
        return new CedTransferLogQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     * @return CedTransferLogQuery the active query used by this AR class.
     */
    public static function findTransfer($id) {
        return self::find()->where(["transfer_id" => $id]);
    }

    /**
     * 
     * @return \common\modules\crossposting\models\CedTransferLogModel
     */
    public function getData() {
        return $this->_data;
    }

    public static function populateRecord($record, $row) {
        $columns = static::getTableSchema()->columns;
        foreach ($row as $name => $value) {
            if ($name == "transfer_data") {
                $record->getData()->unserialize($value);
                continue;
            }
            if (isset($columns[$name])) {
                $row[$name] = $columns[$name]->phpTypecast($value);
            }
        }
        parent::populateRecord($record, $row);
    }

    public function save($runValidation = true, $attributeNames = null) {
        $this->transfer_data = $this->_data->serialize();
        $this->end_at        = time();
        return parent::save($runValidation, $attributeNames);
    }

    public function setImport($transferID = null, &$module) {
        $this->_module     = $module;
        $this->transfer_id = $transferID;
        $this->_flags      |= self::FLAG_TYPE_IMPORT;
        $this->_flags      = $this->_flags & ~ self::FLAG_TYPE_EXPORT;
    }

    public function setExport($transferID = null, &$module) {
        $this->_module     = $module;
        $this->transfer_id = $transferID;
        $this->_flags      |= self::FLAG_TYPE_EXPORT;
        $this->_flags      = $this->_flags & ~ self::FLAG_TYPE_IMPORT;
    }

    public function setCrosspostingAction() {
        $this->_flags |= self::FLAG_TYPE_CROSSPOST_ACTION;
    }

    /**
     * @return \common\modules\crossposting\Module
     */
    public static function module() {
        return \yii::$app->getModule("crosspost");
    }

    /**
     * 
     * @return \common\modules\crossposting\Module
     */
    public function getModule() {
        return $this->_module;
    }

    /**
     * 
     * @param \common\modules\crossposting\Module $val
     */
    public function setModule($val) {
        $this->_module = $val;
    }

    /**
     * Удаляет устаревшие записи лога
     */
    public static function RemoveOuttimeLogRecords() {
        $interval = self::module()->clearActionLogAfterDays * 86400;
        $qq       = "DELETE FROM " . self::tableName() . " WHERE _flags & 0x4 AND begin_at < (UNIX_TIMESTAMP() - {$interval})";
        $res      = \yii::$app->db->createCommand($qq)->execute();
        \yii::$app->trigger(ServiceLogEvent::EVENT_NAME,
                new ServiceLogEvent([
            "type"    => ServiceLogEvent::TYPE_SERVICE,
            "message" => "Удалено {$res} устаревших логов кросспостинга",
        ]));
    }

}

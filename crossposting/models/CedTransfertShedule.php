<?php

namespace common\modules\crossposting\models;

use Yii;

/**
 * This is the model class for table "ced_transfert_shedule".
 *
 * @property int $id
 * @property string $transfer_id
 * @property int $week_day
 * @property int $day_time
 */
class CedTransfertShedule extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'ced_transfert_shedule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['week_day', 'day_time'], 'integer'],
            [['transfer_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id'          => 'ID',
            'transfer_id' => 'Transfer ID',
            'week_day'    => 'Week Day',
            'day_time'    => 'Day Time',
        ];
    }

    /**
     * {@inheritdoc}
     * @return CedTransfertSheduleQuery the active query used by this AR class.
     */
    public static function find() {
        return new CedTransfertSheduleQuery(get_called_class());
    }

}

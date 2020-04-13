<?php

use yii\db\Migration;
use common\modules\crossposting\models\CedTransfertSequence as cts;

/**
 * Class m191107_113519_transfer_interval
 */
class m191107_113519_transfer_interval extends Migration {

    const COLUMN_NAME = "shedule_interval";

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        try {
            $this->addColumn(cts::tableName(), self::COLUMN_NAME,
                    \yii\db\mysql\Schema::TYPE_INTEGER);
        } catch (\Exception $ex) {
            
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        return $this->dropColumn(cts::tableName(), self::COLUMN_NAME);
    }

}

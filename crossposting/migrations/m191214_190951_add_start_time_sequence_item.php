<?php

use yii\db\Migration;
use common\modules\crossposting\models\CedTransfertSequence;

/**
 * Class m191214_190951_add_start_time_sequence_item
 */
class m191214_190951_add_start_time_sequence_item extends Migration {

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->addColumn(CedTransfertSequence::tableName(), "begin_time",
                         $this->integer(11)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        return $this->dropColumn(CedTransfertSequence::tableName(), "begin_time");
    }

}

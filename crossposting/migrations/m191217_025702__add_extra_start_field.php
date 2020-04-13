<?php

use yii\db\Migration;
use common\modules\crossposting\models\CedTransfertSequence;

/**
 * Class m191217_025702__add_extra_start_field
 */
class m191217_025702__add_extra_start_field extends Migration {

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        try {
            $this->addColumn(CedTransfertSequence::tableName(), "extra_start",
                             $this->smallInteger(1)->defaultValue(0));
        } catch (\Exception $ex) {
            
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        try {
            $this->dropColumn(CedTransfertSequence::tableName(), "extra_start");
        } catch (Exception $ex) {
            
        }
        return true;
    }

}

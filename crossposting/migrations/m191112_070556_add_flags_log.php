<?php

use yii\db\Migration;

/**
 * Class m191112_070556_add_flags_log
 */
class m191112_070556_add_flags_log extends Migration {

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        try {
            $this->addColumn("ced_transfer_log", "_flags",
                    $this->integer()->defaultValue(0));
        } catch (Exception $ex) {
            
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        return $this->dropColumn("ced_transfer_log", "_flags");
    }

}

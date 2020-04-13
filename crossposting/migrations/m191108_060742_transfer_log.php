<?php

use yii\db\Migration;
use common\components\Schema;

/**
 * Class m191108_060742_transfer_log
 */
class m191108_060742_transfer_log extends Migration {

    const TABLE_NAME = "ced_transfer_log";

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        try {

            $this->createTable(self::TABLE_NAME,
                    [
                "id"            => $this->primaryKey(),
                "transfer_id"   => $this->string(50)->notNull(),
                "begin_at"      => $this->integer(11),
                "end_at"        => $this->integer(11),
                "transfer_data" => $this->text(),
                    ], $tableOptions);
        } catch (\Exception $ex) {
            
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        return $this->dropTable(self::TABLE_NAME);
    }

}

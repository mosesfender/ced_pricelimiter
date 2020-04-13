<?php

use yii\db\Migration;

/**
 * Class m191114_153432_limiter_table
 */
class m191114_153432_limiter_table extends Migration {

    const TABLE_LIMITER = "ced_objects_price_limiter";

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->createTable(self::TABLE_LIMITER,
                [
            "id"            => $this->primaryKey(),
            "geonameid"     => $this->integer(),
            "country_code"  => $this->string(2),
            "property_type" => $this->string(30),
            "min_price"     => $this->integer(),
            "currency"      => $this->string(3),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        return $this->dropTable(self::TABLE_LIMITER);
    }

}

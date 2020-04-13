<?php

namespace common\modules\pricelimiter\models;

class PropertyTypes extends \common\components\ActiveRecord {

    public static function tableName() {
        return "ced_dictionary_items";
    }

    public function attributes(){
        return ["id", "label"];
    }
    
    public static function find() {
        return new PropertyTypesQuery(get_called_class());
    }

}

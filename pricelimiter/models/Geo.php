<?php

namespace common\modules\pricelimiter\models;

/**
 * @property int $geonameid
 * @property string $label
 * @property string $asciiname
 * @property string $name
 * @property string $country_code
 */
class Geo extends \common\components\ActiveRecord {

    public static function tableName() {
        return "gn_ced_geonames";
    }

    public function attributes() {
        return [
            "geonameid", "label", "asciiname", "name", "country_code"
        ];
    }

    public static function find() {
        return new GeoQuery(get_called_class());
    }

}

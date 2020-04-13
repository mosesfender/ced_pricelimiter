<?php

namespace common\modules\crossposting\models;

use common\models\CedObjects;
use common\models\CedObjectsData;
use common\models\CedObjectsLight;

/**
 * @property \common\models\CedObjects $propertyCO
 * @property \common\models\CedObjectsLight $propertyLight
 */
class CedPartnersObjectsMap extends \common\models\CedPartnersObjectMap {

    public static function find(): CedPartnersObjectMapQuery {
        return new CedPartnersObjectMapQuery(get_called_class());
    }

    public static function findVendorProperty($vendorID, $propertyID): CedPartnersObjectMapQuery {
        return self::find()
                        ->where(["AND",
                            ["partner_id" => $vendorID],
                            ["partner_object_id" => $propertyID],
        ]);
    }

    /**
     * @return \common\models\CedObjectsQuery
     */
    public function getPropertyCO() {
        return $this->hasOne(CedObjects::class, ["id" => "object_id"]);
    }

    /**
     * @return \common\models\CedObjectsQuery
     */
    public function getPropertyData() {
        return $this->hasOne(CedObjectsData::class, ["id" => "object_id"]);
    }

    /**
     * @return \common\models\CedObjectsQuery
     */
    public function getPropertyLight() {
        return $this->hasOne(CedObjectsLight::class, ["id" => "object_id"]);
    }

}

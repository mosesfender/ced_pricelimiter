<?php

namespace common\modules\pricelimiter\models;

use common\helpers\ArrayHelper;

/**
 * @property int $price_org
 * @property int $price_eur
 * @property int $price_usd
 * @property int $price_rub
 * @property string $price_currency
 * @property string $price_type
 * @property string $proptype
 */
class CedObjects extends \common\models\CedObjectsSimple {

    public function attributes() {
        return ArrayHelper::merge(parent::attributes(),
                        [
                    "price_orig", "price_eur", "price_usd", "price_rub", "price_currency", "price_type", "proptype"
        ]);
    }

}

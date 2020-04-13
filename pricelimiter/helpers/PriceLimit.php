<?php

namespace common\modules\pricelimiter\helpers;

use common\modules\pricelimiter\models\GnGeonamesHash;
use common\helpers\ArrayHelper;
use common\modules\pricelimiter\models\CedObjectsPriceLimiter;

class PriceLimit {

    /**
     * Получает геомодель
     */
    public static function getGeoModel() {
        
    }

    /**
     * Проверяет, попадает ли геонейм под лимит.
     * @param int $geonameid    
     * @param int $price        
     * @param string $proptype
     * @return boolean          true если попадает
     */
    public static function isLimit($geonameid, $price, $proptype) {
        $ret    = false;
        $grid   = CedObjectsPriceLimiter::findGrid();
        $hashes = GnGeonamesHash::find()->where(["geonameid" => $geonameid])->all();
        $h      = [];
        foreach ($hashes as $hash) {
            $h = ArrayHelper::merge($h, $hash->extrudeGeonames());
        }
        $h = array_reverse(array_unique($h));
        foreach ($h as $gn) {
            if (key_exists($gn, $grid)) {
                if (isset($grid[$gn][$proptype])) {
                    $ret = $price < $grid[$gn][$proptype];
                    break;
                }
            }
        }
        return $ret;
    }

}

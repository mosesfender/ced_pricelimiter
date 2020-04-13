<?php

namespace common\modules\pricelimiter\models;

use Yii;
use common\helpers\CacheDependency;
use common\modules\pricelimiter\models\GnGeonamesHash;
use common\modules\pricelimiter\models\GnGeonamesHashQuery;
use common\modules\pricelimiter\models\CedObjects;
use yii\db\Expression as exp;
use common\components\Flags;

/**
 * This is the model class for table "ced_objects_price_limiter".
 *
 * @property int $id
 * @property int $geonameid
 * @property string $country_code
 * @property string $property_type
 * @property int $min_price
 * @property string $currency
 * 
 * @property \common\modules\pricelimiter\models\Geo $geo
 */
class CedObjectsPriceLimiter extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'ced_objects_price_limiter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['geonameid', 'min_price'], 'integer'],
            [['country_code'], 'string', 'max' => 2],
            [['property_type'], 'string', 'max' => 30],
            [['currency'], 'string', 'max' => 3],
            [['currency'], 'default', 'value' => 'eur'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id'            => 'ID',
            'geonameid'     => 'Geonameid',
            'country_code'  => 'Country Code',
            'property_type' => 'Property Type',
            'min_price'     => 'Min Price',
            'currency'      => 'Currency',
        ];
    }

    /**
     * {@inheritdoc}
     * @return CedObjectsPriceLimiterQuery the active query used by this AR class.
     */
    public static function find() {
        return new CedObjectsPriceLimiterQuery(get_called_class());
    }

    /**
     * 
     * @param int $geonameid
     * @param string $proptype
     * @return CedObjectsPriceLimiter
     */
    public static function findOneModel($geonameid, $proptype) {
        return self::find()
                        ->where(["AND",
                            ["geonameid" => $geonameid],
                            ["property_type" => $proptype],
                        ])
                        ->one();
    }

    /**
     * 
     * @return \common\modules\pricelimiter\models\Geo
     */
    public function getGeo() {
        return $this->hasOne(Geo::class, ["geonameid" => "geonameid"]);
    }

    /**
     * 
     * @return array
     */
    public static function findGrid() {
        $cache = self::getCache();
        $ret   = [];
        $ret   = $cache->getOrSet($cache->cacheKey,
                function() {
            $models = self::find()->all();
            foreach ($models as $mod) {
                /* @var $mod \common\modules\pricelimiter\models\CedObjectsPriceLimiter */
                $ret[$mod->geonameid][$mod->property_type] = $mod->min_price;
            }
            return $ret;
        }, 3600, CacheDependency::getFileDependency($cache->dependencyName));
        return $ret;
    }

    /**
     * Возвращает запрос на поиск присутствующих в таблице дочерних регионов со значениями лимитов
     * @param int $geonameid
     * @return \common\modules\pricelimiter\models\CedGeonamesHashQuery
     */
    public static function getHashes($geonameid, $onlyChilds = true) {
        $ret = GnGeonamesHash::find()
                ->ced()
                ->geoname($geonameid);
        if ($onlyChilds) {
            $ret->onlyChilds($geonameid);
        }
        return $ret;
    }

    /**
     * Возвращает запрос на поиск присутствующих в таблице дочерних регионов со значениями лимитов
     * @param int $geonameid
     * @return \common\modules\pricelimiter\models\CedObjectsPriceLimiterQuery
     */
    public static function findChilds($geonameid, $onlyChilds = true) {
        return self::find()
                        ->where(["geonameid" => self::getHashes($geonameid,
                                    $onlyChilds)->select("geonameid")]);
    }

    /**
     * Возвращает запрос для выбора геонеймов, которые следует игнорировать при лимитировании 
     * геонейма $geonameid, поскольку под ним есть геонеймы со своими лимитами.
     * @param int $geonameid
     * @return \common\modules\pricelimiter\models\CedObjectsPriceLimiterQuery
     */
    public static function getIgnoredGeonames($geonameid) {
        $keys = self::findChilds($geonameid)->select("geonameid")->column();
        if (empty($keys)) {
            return null;
        }
        $_tmp = [];
        foreach ($keys as $key) {
            $_tmp[] = "\\.{$key}\\.";
        }
        return GnGeonamesHash::find()
                        ->where(["OR",
                            ["geonameid" => $keys],
                            ["REGEXP", "hash", implode("|", $_tmp)]
                        ])
                        ->ced();
    }

    /**
     * 
     * @param int $geonameid
     * @return \common\models\CedObjectsSimpleQuery
     */
    public static function getObjectsForGeoname($geonameid) {
        $ignored  = self::getIgnoredGeonames($geonameid);
        $geonames = self::getHashes($geonameid, false)
                ->select("CAST(geonameid as INT)");
        if ($ignored) {
            $geonames->andWhere(["NOT IN", "geonameid", $ignored->select("geonameid")]);
        }
        $ret = CedObjects::find()
                ->where(["AND",
                    ["geonameid" => $geonames],
                    ["NOT", ["&", \common\models\CedObjectsSimple::tableName() . "._flags", Flags::CO_NO_LIMIT]]
                ])->withPrice()
                ->withProptype()
                ->withLimitedPartners();
        return $ret;
    }

    public static function flushCache() {
        CacheDependency::setFileDependency(self::getCache()->dependencyName);
    }

    /**
     * @return \common\modules\pricelimiter\components\FileCache 
     */
    static function getCache() {
        return \yii::$app->getModule("pricelimiter")->cache;
    }

}

<?php

namespace common\modules\pricelimiter\models;

use yii\db\Expression as exp;
use common\modules\geobase\models\GnAlternatenames as ga;
use common\models\CedLocationEstateTaxonometry as clet;

class GeoQuery extends \yii\db\ActiveQuery implements \yii\db\QueryInterface {

    public function init() {
        parent::init();
        $this->select(["gn.geonameid", "gn.asciiname", "gn.name", "ga.label", "gn.country_code"]);
        $this->from(["gn" => $this->modelClass::tableName()]);
        $this->leftJoin(["ga" => ga::tableName()],
                ["gn.geonameid" => new exp("ga.geonameid")]);
        $this->andWhere(["ga.language" => \yii::$app->language]);
    }

    public function country() {
        $this->orWhere(["gn.ced_feature_code" => "country"]);
        return $this;
    }

    public function regions() {
        $this->orWhere(["gn.ced_feature_code" => "region"]);
        return $this;
    }

    public function towns() {
        $this->orWhere(["gn.ced_feature_code" => "town"]);
        return $this;
    }

    public function taxAvailableGeo() {
        $this->leftJoin(["clet" => clet::tableName()],
                ["clet.geonameid" => new exp("gn.geonameid")]);
        $this->andWhere(["AND",
            ["clet.counter_type" => "all"],
            ["!=", "clet.cnt", 0],
        ]);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @return Geo[]|array
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Geo|array|null
     */
    public function one($db = null) {
        return parent::one($db);
    }

}

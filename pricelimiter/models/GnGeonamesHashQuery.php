<?php

namespace common\modules\pricelimiter\models;

/**
 * This is the ActiveQuery class for [[GnGeonamesHash]].
 *
 * @see GnGeonamesHash
 */
class GnGeonamesHashQuery extends \common\modules\geobase\models\GnGeonamesHashQuery {

    public function ced() {
        $this->andWhere(["hash_type" => "ced"]);
        return $this;
    }

    public function canonical() {
        $this->andWhere(["hash_type" => "canonical"]);
        return $this;
    }

    public function geoname($geonameid) {
        $this->andWhere(["LIKE", "hash", ".{$geonameid}."]);
        return $this;
    }

    public function onlyChilds($geonameid) {
        $this->andWhere(["!=", "geonameid", $geonameid]);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     * @return GnGeonamesHash[]|array
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return GnGeonamesHash|array|null
     */
    public function one($db = null) {
        return parent::one($db);
    }

}

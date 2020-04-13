<?php

namespace common\modules\pricelimiter\models;

use yii\db\Expression as exp;
use common\models\CedLocationEstateTaxonometry;

class CedObjectsPriceLimiterQuery extends \yii\db\ActiveQuery {

    /**
     * {@inheritdoc}
     * @return CedObjectsPriceLimiter[]|array
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CedObjectsPriceLimiter|array|null
     */
    public function one($db = null) {
        return parent::one($db);
    }

}

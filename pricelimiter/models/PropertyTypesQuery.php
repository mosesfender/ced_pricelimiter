<?php

namespace common\modules\pricelimiter\models;

use yii\db\Expression as exp;
use common\models\dictionary\CedDictionaryItemsLang as lang;

class PropertyTypesQuery extends \yii\db\ActiveQuery {

    public function init() {
        parent::init();
        $this->select(["pt.id", "lang.label"]);
        $this->from(["pt" => $this->modelClass::tableName()]);
        $this->leftJoin(["lang" => lang::tableName()],
                ["pt.id" => new exp("lang.ced_dictionary_items_id")]);
        $this->andWhere(["lang.language" => \yii::$app->language]);
        $this->andWhere(["pt.dict_id" => "propertytype"]);
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

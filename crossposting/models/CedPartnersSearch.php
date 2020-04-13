<?php

namespace common\modules\crossposting\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\crossposting\models\CedPartners;

class CedPartnersSearch extends CedPartners {

    public function search($params) {
        $query = CedPartners::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
            ],
            'sort'       => [
                'defaultOrder' => [
                    'title' => SORT_ASC
                ],
                'attributes'   => [
                    "id", "title"
                ]
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere(["like", "id", $this->id])
                ->andFilterWhere(["like", "prefix", $this->prefix])
                ->andFilterWhere(["like", "alias", $this->alias])
                ->andFilterWhere(["like", "title", $this->title]);

        return $dataProvider;
    }

}

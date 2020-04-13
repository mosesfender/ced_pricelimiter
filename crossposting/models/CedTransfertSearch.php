<?php

namespace common\modules\crossposting\models;

use yii\data\ActiveDataProvider;
use common\modules\crossposting\models\CedTransfert;

class CedTransfertSearch extends CedTransfert {

    public function search($params) {
        $query = CedTransfert::find();

        $dataProvider = new ActiveDataProvider([
            "query"      => $query,
            "pagination" => [
            ],
            "sort"       => [
                "defaultOrder" => [
                    "created_at" => SORT_DESC
                ],
                "attributes"   => [
                ]
            ],
        ]);

        $this->load($params);

        if (empty($this->tr_type)) {
            $this->tr_type = null;
        }
        $query->andWhere(["tr_type" => $this->tr_type]);

        return $dataProvider;
    }

}

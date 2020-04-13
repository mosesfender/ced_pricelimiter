<?php

namespace common\modules\pricelimiter\models;

use yii\data\ActiveDataProvider;
use common\helpers\ArrayHelper as ah;

class GeoSearch extends \common\modules\pricelimiter\models\Geo {

    /** Выбирать страны */
    const PARAM_COUNTRIES = 0x1;

    /** Выбирать регионы */
    const PARAM_REGIONS = 0x2;

    /** Выбирать населённые пункты */
    const PARAM_TOWNS = 0x4;

    /** Выбирать только географию, в которой есть наши объекты  */
    const PARAM_TAX_AVAILABLE = 0x8;

    public $paramKeys = [];

    public function rules() {
        return ah::merge(parent::rules(),
                        [
                    [["paramKeys"], "safe"],
        ]);
    }

    public function search($params) {

        $query = self::find()
                ->orderBy("label");

        $this->load($params);

        if (!is_array($this->paramKeys)) {
            $this->paramKeys = [];
        }

        $sub = ["OR"];
        if (in_array(self::PARAM_COUNTRIES, $this->paramKeys)) {
            $sub[] = ["gn.ced_feature_code" => "country"];
        }
        if (in_array(self::PARAM_REGIONS, $this->paramKeys)) {
            $sub[] = ["gn.ced_feature_code" => "region"];
        }
        if (in_array(self::PARAM_TOWNS, $this->paramKeys)) {
            $sub[] = ["gn.ced_feature_code" => "town"];
        }
        if (!empty($sub)) {
            $query->andWhere($sub);
        }
        if (in_array(self::PARAM_TAX_AVAILABLE, $this->paramKeys)) {
            $query->taxAvailableGeo();
        }


        //prer($query->createCommand()->rawSql);
        $provider = new ActiveDataProvider([
            "query"      => $query,
            "pagination" => false,
            "sort"       => false,
        ]);

        return $provider;
    }

    public static function filterParams() {
        return [
            self::PARAM_TAX_AVAILABLE => "С нашими объектами",
            self::PARAM_COUNTRIES     => "Страны",
            self::PARAM_REGIONS       => "Регионы",
            self::PARAM_TOWNS         => "Населёные пункты",
        ];
    }

}

<?php

namespace common\modules\pricelimiter\controllers;

use backend\components\BaseController as Controller;
use common\modules\pricelimiter\models\Geo;
use common\modules\pricelimiter\models\GeoSearch;
use common\modules\pricelimiter\models\PropertyTypes;
use common\modules\pricelimiter\models\CedObjectsPriceLimiter as copl;
use common\modules\pricelimiter\models\CedObjects;
use \common\models\CedObjectsUpdateLog as UpdateLog;
use common\helpers\ArrayHelper as ah;
use common\helpers\BitwiseHelper as bh;
use yii\helpers\ReplaceArrayValue as rav;
use yii\filters\VerbFilter;

class DefaultController extends Controller {

    public function behaviors() {
        return ah::merge(parent::behaviors(),
                        [
                    'verbs' => [
                        'class'   => VerbFilter::className(),
                        'actions' => [
                            'store-value'            => ['POST'],
                            'do-process-for-geoname' => ['POST', 'GET'],
                        ],
                    ],
        ]);
    }

    public function actionIndex() {
        $qp          = \yii::$app->request->getQueryParams();
        unset($qp["_csrf"]);
        /* @var $uac \common\components\UAC\SimpleUac */
        $uac         = \yii::$app->uac;
        $isAvailable = $uac->isAvailable("pricelimiter_search");
        $params      = null;

        if (isset($qp["GeoSearch"]["paramKeys"])) {
            $qp["GeoSearch"]["paramKeys"] = new rav($qp["GeoSearch"]["paramKeys"]);
        }

        $params = ah::merge($uac->restore($params), $qp);
        $uac->store($params);

        $search      = new GeoSearch();
        $geoProvider = $search->search($params);

        $types = PropertyTypes::find();

        return $this->render('index',
                        [
                    "search"         => $search,
                    "geoProvider"    => $geoProvider,
                    "proptypesQuery" => $types,
                    "values"         => copl::findGrid(),
        ]);
    }

    public function actionStoreValue() {
        /** @var $post [[geonameid] => 661882, [proptype] => houses, [value] => 1] */
        $post  = \yii::$app->request->post();
        $model = copl::findOneModel($post["geonameid"], $post["proptype"]);

        if (empty(trim($post["value"])) && $model) {
            if ($model->delete()) {
                copl::flushCache();
                return $this->asJsonSuccess(["deleted"], $model);
            }
        }

        if (!$model) {
            $model = new copl();
        }
        $model->setAttribute("geonameid", $post["geonameid"]);
        $model->setAttribute("property_type", $post["proptype"]);
        $model->setAttribute("min_price", $post["value"]);
        $model->setAttribute("country_code", $model->geo->country_code);
        $model->setAttribute("currency", "eur");
        if ($model->save()) {
            copl::flushCache();
            return $this->asJsonSuccess("saved", $model);
        }
    }

    public function actionProbe() {
        prer(copl::getObjectsForGeoname(3194884)->createCommand()->rawSql);
    }

    public function actionDoProcessForGeoname() {
        set_time_limit(300);
        $geonameid = \yii::$app->request->post("geonameid");
        if ($geonameid) {
            try {
                $vals = copl::findGrid()[$geonameid];
            } catch (\yii\base\ErrorException $ex) {
                if ($ex->getCode() == 8) {
                    return $this->asJsonError("Ошибка",
                                    "Для этого региона не установлено лимитов");
                }
            }
            $objects = copl::getObjectsForGeoname(\yii::$app->request->post("geonameid"))->all();
            $cnt     = 0;
            foreach ($objects as $object) {
                /* @var $object \common\modules\pricelimiter\models\CedObjects */
                if (key_exists($object->proptype, $vals)
                        && $object->status == "published"
                        && $object->price_eur != 0
                        //&& $object->price_type == "ptsingle"
                        && $object->price_eur < $vals[$object->proptype]) {
                    $object->status    = "saled";
                    $object->update_at = time();
                    UpdateLog::add($object->id,
                            [UpdateLog::REC_TYPE_STATUS => $object->getOldAttribute("status")],
                            [UpdateLog::REC_TYPE_STATUS => $object->status],
                            null, -4);

                    $cnt += $object->save();
                    //prer($object, 1, 1);
                }
            }
            return $this->asJsonSuccess("Лимитер цен",
                            "Применен лимитатор цен для {$cnt} объектов недвижимости в регионе {$geonameid}");
        } else {
            return $this->asJsonError("Лимитер цен",
                            "Невозможно применить лимитатор цен, так как не указан регион.");
        }
    }

    /**
     * @return \common\modules\pricelimiter\Module
     */
    protected function getModule() {
        return $this->module;
    }

}

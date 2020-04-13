<?php

/* @var $this \common\components\View */
/* @var $model \common\models\CedPartnersSearch */
/* @var $provider \yii\data\ActiveDataProvider */

use common\helpers\Html;
use common\widgets\mfGridView\MFGridView as GridView;
use yii\grid\ActionColumn;
use yii\widgets\Pjax;
use common\modules\crossposting\models\CedPartners;
use common\modules\crossposting\models\CedTransfert;

?>
<?php Pjax::begin(["id" => "pjax_companies_grid"]) ?>
<?=

GridView::widget([
    "id"           => "companies_grid",
    "dataProvider" => $provider,
    "filterModel"  => $model,
    "tableOptions" => [
        "class" => "table eclipse table-striped table-bordered",
    ],
    "rowOptions"   => function($model) {
        if (!$model->enabled) {
            return ["class" => "inactive"];
        }
    },
    "sizer"   => [
        "label" => "Рядов",
        "sizes" => [5 => 5, 10 => 10, 25 => 25, 50 => 50],
    ],
    "pager"   => [
        "firstPageLabel" => "первая",
        "lastPageLabel"  => "последняя",
    ],
    "columns" => [
        //["class" => "yii\grid\SerialColumn"],
        [
            "attribute"      => "id",
            "headerOptions"  => ["class" => "w-10p text-right"],
            "contentOptions" => ["class" => "text-right"],
        ],
        [
            "attribute" => "title",
            //"header"    => "Компания",
            "format"    => "raw",
            "value"     => function(CedPartners $model) {
                return Html::a("<b>{$model->title}</b> ({$model->prefix})");
            }
        ],
        [
            "label"  => "Импорты",
            //"headerOptions"  => ["class" => "w-100p"],
            "format" => "raw",
            "value"  => function(CedPartners $model) {
                $ret = Html::beginTag("ul");
                foreach ($model->imports as $import) {
                    try {
                        $ret .= Html::tag("li",
                                          Html::a($import->getSettings(CedTransfert::TYPE_IMPORT)->title,
                                                                       "javascript:;",
                                                                       [
                                            "data-transfer-id" => $import->id]));
                    } catch (\Exception $ex) {
                        
                    }
                }
                return $ret . Html::endTag("ul");
            }
        ],
        [
            "label"         => "Действия",
            "format"        => "raw",
            "headerOptions" => ["class" => "w-10p"],
            "value"         => function(CedPartners $model) {
                $ret = Html::a(Html::svgLink("eye", "svg16"), "javascript:;",
                                             [
                            "class" => "btn-view-company",
                            "title" => "Детали",
                            "data"  => [
                                "toggle" => "tooltip",
                            ]
                ]);
                if ($model->canImport) {
                    $ret .= Html::a(Html::svgLink("file-import", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-create-import",
                                "title" => "Создать импорт для компании",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                }
                return $ret;
            }
        ],
    ],
]);
?>
<?php Pjax::end(); ?>

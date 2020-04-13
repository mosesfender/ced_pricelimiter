<?php

use common\widgets\mfGridView\MFGridView as GridView;
use common\helpers\Html;
use common\modules\crossposting\models\CedTransfert as ct;
use common\widgets\CedTextInput\CedTextInputWidget;

/* @var $this \common\components\View */
/* @var $provider \yii\data\ActiveDataProvider */
/* @var $model \common\modules\crossposting\models\CedTransfertSearch */
?>

<?=

GridView::widget([
    "id"           => "transfer_list",
    "dataProvider" => $provider,
    "jsGridClass"  => "CED.TTransferList",
    "tableOptions" => [
        "class" => "table eclipse table-striped table-bordered",
    ],
    "sizer"        => [
        "label" => "Рядов",
        "sizes" => [10 => 10, 25 => 25, 50 => 50],
    ],
    "pager"        => [
        "firstPageLabel" => "первая",
        "lastPageLabel"  => "последняя",
    ],
    "searchForm"   => [
        GridView::SRC_FLD_ID               => "transfer_search_form",
        GridView::SRC_FLD_SUBMIT_ON_CHANGE => true,
    ],
    "rowOptions"   => function(\common\modules\crossposting\models\CedTransfert $model) {
        
    },
    "columns" => [
        [
            "header"         => "ID",
            "contentOptions" => ["class" => "idrow"],
            "format"         => "html",
            "value"          => function(ct $model) {
                $ret = "";
                switch ($model->tr_type) {
                    case ct::TYPE_EXPORT:
                        $ret .= Html::tag("span", "",
                                          ["class" => "glyphicon glyphicon-cloud-upload"]);
                        break;
                    case ct::TYPE_IMPORT:
                        $ret .= Html::tag("span", "",
                                          ["class" => "glyphicon glyphicon-cloud-download"]);
                        break;
                }
                $ret .= "{$model->id} {$model->getSettings($model->tr_type)->title}";
                return $ret;
            }
        ],
        [
            "header" => "Очередь",
            "format" => "raw",
            "headerOptions" => ["class" => "text-center w-10p"],
            "contentOptions" => ["class" => "text-center"],
            "value"  => function(ct $model) {
                $res      = "";
                $sequence = $model->sequenceOne;
                if ($sequence) {
                    $res = Html::svgLink("check-circle", "svg16");
                }
                return $res;
            }
        ],
        "created_at:datetime",
        "finished_at:datetime",
        "user.fullname",
        [
            "header"         => "Действия",
            "contentOptions" => ["class" => "idrow"],
            "format"         => "raw",
            "value"          => function(ct $model) {
                $ret = "";
                if ($model->tr_type == ct::TYPE_EXPORT) {
                    $ret .= Html::a(Html::svgLink("eye", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-view-settings",
                                "title" => "Смотреть установки",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::a(Html::svgLink("external-link-square",
                                                  "svg16"), "javascript:;",
                                                  [
                                "class" => "btn-view-links",
                                "title" => "Ссылки на файлы",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::a(Html::svgLink("edit", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-edit",
                                "title" => "Редактировать",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::a(Html::svgLink("eye-solid", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-view-export",
                                "title" => "Смотреть",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::a(Html::svgLink("file-alt", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-popup-log",
                                "title" => "Лог",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::a(Html::svgLink("trash", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-remove",
                                "title" => "Удалить",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                }
                if ($model->tr_type == ct::TYPE_IMPORT) {
                    $ret .= Html::a(Html::svgLink("eye", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-view-import-settings",
                                "title" => "Смотреть установки",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::dummySvg();
                    $ret .= Html::a(Html::svgLink("edit", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-edit",
                                "title" => "Редактировать",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::a(Html::svgLink("eye-solid", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-view-import",
                                "title" => "Смотреть",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::a(Html::svgLink("file-alt", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-popup-log",
                                "title" => "Лог",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                    $ret .= Html::a(Html::svgLink("trash", "svg16"),
                                                  "javascript:;",
                                                  [
                                "class" => "btn-remove",
                                "title" => "Удалить",
                                "data"  => [
                                    "toggle" => "tooltip",
                                ]
                    ]);
                }
                return $ret;
            }
        ],
    ]
]);
?>


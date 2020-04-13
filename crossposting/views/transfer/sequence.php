<?php

use common\widgets\mfGridView\MFGridView as GridView;
use common\modules\crossposting\models\CedTransfertSequenceWork;
use common\helpers\Html;
use yii\helpers\Url;

\nkovacs\datetimepicker\DateTimePickerAsset::register($this);
/* @var $this \common\components\View */
/* @var $provider \yii\data\ActiveDataProvider */

$this->title                   = "Очередь обмена данными";
$this->params['breadcrumbs'][] = ['label' => "Кросспостинг", 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ced-sequence-index">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php
            echo GridView::widget([
                "id"           => "sequence_list",
                "dataProvider" => $provider,
                "jsGridClass"  => "CED.TSequenceGrid",
                "jsGridParams" => [
                    "sequenceRefresh"    => 3,
                    "sequenceRefreshUrl" => Url::toRoute("transfer/sequence-progress"),
                ],
                "layout"       => "{summary}\n{items}",
                "tableOptions" => [
                    "class" => "table eclipse table-striped table-bordered",
                ],
                "rowOptions"   => function(CedTransfertSequenceWork $model) {
                    
                },
                "columns"                      => [
                    [
                        "headerOptions" => ["class" => "w-90p"],
                        "format"        => "raw",
                        "value"         => function(CedTransfertSequenceWork $model) {
                            return Html::a(Html::svgLink("eye", "svg32"),
                                                         "javascript:;",
                                                         [
                                        "class" => "btn-popup-transfert",
                                        "title" => "Информация о задании",
                                        "data"  => [
                                            "toggle" => "tooltip",
                                        ]
                                    ])
                                    . Html::a(Html::svgLink("stopwatch", "svg32"),
                                                            "javascript:;",
                                                            [
                                        "class" => "btn-start-time",
                                        "title" => "Установка времени запуска",
                                        "data"  => [
                                            "toggle" => "tooltip",
                                        ]
                            ]);
                        }
                    ],
                    [
                        "format" => "raw",
                        "value"  => function(CedTransfertSequenceWork $model) {
                            return "<div>{$model->id}.<b>{$model->transfert_id}</b></div>"
                                    . "<div>{$model->transfer->getSettings($model->transfer->tr_type)->title}</div>";
                        }
                    ],
                    [
                        "format" => "raw",
                        "value"  => function(CedTransfertSequenceWork $model) {
                            $ret = Html::beginTag("div",
                                                  ["class" => "order-details"]);
                            $ret .= Html::tag("span", "Последний запуск:",
                                              ["class" => "label"]);
                            $ret .= Html::tag("span",
                                              \yii::$app->formatter->asDatetime($model->begin_at),
                                                                                [
                                        "class" => "detail"]);
                            $ret .= Html::tag("span", "Последний останов:",
                                              ["class" => "label"]);
                            $ret .= Html::tag("span",
                                              \yii::$app->formatter->asDatetime($model->end_at),
                                                                                [
                                        "class" => "detail"]);
                            $ret .= Html::endTag("div");
                            return $ret;
                        }
                    ],
                    [
                        "header"         => "Прогресс",
                        "contentOptions" => ["role" => "progress"],
                    ],
                ]
            ]);
            ?> 
        </div>
    </div>
</div>
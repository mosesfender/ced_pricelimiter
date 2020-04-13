<?php

use common\helpers\Html;
use common\modules\crossposting\widgets\LogGrid\Grid;
use yii\data\ArrayDataProvider;
use common\modules\crossposting\models\CedTransferLogModel as lm;
use common\modules\crossposting\models\LogItem;
use common\modules\crossposting\models\CedTransferLog;

/* @var $model \common\modules\crossposting\models\CedTransferLog */

\yii::$app->formatter->datetimeFormat = "php:d.m.Y H:i:s";

$provider = new ArrayDataProvider([
    "allModels"  => $model->data->logItems,
    "sort"       => false,
    "pagination" => false
        ]);
?>

<div class="panel panel-collapse">
    <div class="panel-heading">
        <?= $model->id ?>. <?= \yii::$app->formatter->asDatetime($model->begin_at) ?> — <?= \yii::$app->formatter->asDatetime($model->end_at) ?>
        <br><?= $model->data->title ?>
    </div>
    <div class="panel-body">
        <div class="col-md-8 col-sm-12">
            <?php
            echo Grid::widget([
                "dataProvider" => $provider,
                "rowOptions"   => function(\common\modules\crossposting\models\LogItem $row) {
                    switch ($row->type) {
                        case lm::ITEM_TYPE_INFO:
                            return ["class" => "info"];
                            break;
                        case lm::ITEM_TYPE_SUCCESS:
                            return ["class" => "success"];
                            break;
                        case lm::ITEM_TYPE_WARNING:
                            return ["class" => "warning"];
                            break;
                        case lm::ITEM_TYPE_ERROR:
                            return ["class" => "danger"];
                            break;
                    }
                },
                "columns" => [
                    [
                        "attribute"     => "time",
                        "headerOptions" => ["style" => "width: 20px"],
                        "value"         => function(\common\modules\crossposting\models\LogItem $data) {
                            $st = explode(".", $data->time);
                            try {
                                return \yii::$app->formatter->asDatetime($st[0]) . ".{$st[1]}";
                            } catch (\Exception $ex) {
                                return \yii::$app->formatter->asDatetime(reset($st));
                            }
                        },
                    ],
                    [
                        "attribute"     => "type",
                        "headerOptions" => ["style" => "width: 20px"],
                        "value"         => function(\common\modules\crossposting\models\LogItem $data) {
                            return lm::typeNames($data->type);
                        },
                    ],
                    [
                        "attribute" => "message",
                        "value"     => function(\common\modules\crossposting\models\LogItem $data) {
                            $ret = "";
                            if ($data->cedObjectID) {
                                $ret .= "({$data->cedObjectID}) ";
                            }
                            if ($data->partnerObjectID) {
                                $ret .= "({$data->partnerObjectID}) ";
                            }
                            return $ret . $data->message;
                        }
                    ],
                ]
            ]);
            ?>
        </div>
        <div class="col-md-4 col-sm-12">
            <?php if ($model->_flags & CedTransferLog::FLAG_TYPE_CROSSPOST_ACTION): ?>
                <?php $stat = $model->data->getStatistic(false); ?>
                <?php
                echo \yii\widgets\DetailView::widget([
                    "model"      => $stat,
                    "attributes" => [
                        "supposedNum",
                        "totalNum",
                        "crossposted",
                        "plleft",
                        "plsaled",
                        "saled",
                        "nosale",
                    ]
                ]);
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>
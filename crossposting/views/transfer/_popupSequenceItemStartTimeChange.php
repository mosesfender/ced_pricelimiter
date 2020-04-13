<?php

use nkovacs\datetimepicker\DateTimePicker;
use common\helpers\Html;
/* @var $model \common\modules\crossposting\models\CedTransfertSequenceWork */
?>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="panel panel-success">
            <div class="panel-body">
                Задание «<?= $model->transfert_id ?>» запускается в это время суток:
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 form-group">
                <?php
                echo Html::hiddenInput("begin_time_item_id", $model->id);
                echo DateTimePicker::widget([
                    "name"  => "begin_time",
                    "id"    => "p_begin_time",
                    "type"  => DateTimePicker::TYPE_TIME,
                    "value" => \yii::$app->formatter->asTime($model->getBeginTime()),
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <?php
                echo Html::a("Сохранить", "javascript:;",
                             ["class" => "save-start-time btn btn-success"]);
                ?>
            </div>
        </div>
    </div>
</div>
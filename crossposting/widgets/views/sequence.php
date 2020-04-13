<?php

use common\helpers\Html;
use yii\helpers\Url;

/* @var $label string */
?>

<div class="panel panel-default panel-labeled sequence-widget">
    <label><?= $label ?></label>
    <div class="panel-body">
        <?= Html::a("Смотреть очередь", Url::toRoute("transfer/sequence-list"),
                ["class" => "btn btn-primary"]); ?>
    </div>
</div>
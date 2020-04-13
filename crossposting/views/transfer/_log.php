<?php

use common\helpers\Html;
use yii\data\ActiveDataProvider;
use yii\widgets\ListView;

/* @var $models common\modules\crossposting\models\CedTransferLogQuery */
$provider = new ActiveDataProvider([
    "query"      => $models,
    "pagination" => false,
    "sort"       => false,
        ]);
?>

<div class="log-index panel panel-default">
    <div class="panel-heading">
        <?= $provider->models[0]->transfer_id ?>
    </div>
    <div class="panel-body">
        <?=
        ListView::widget([
            "dataProvider" => $provider,
            "itemView"     => "_logItem"
        ]);
        ?>
    </div>
</div>
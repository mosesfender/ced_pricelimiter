<?php

use common\helpers\Html;
use yii\data\ActiveDataProvider;
use yii\widgets\ListView;

$this->title                   = "Общий лог кросспостинга";
$this->params['breadcrumbs'][] = ['label' => "Кросспостинг", 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;

/* @var $models common\modules\crossposting\models\CedTransferLogQuery */
$provider = new ActiveDataProvider([
    "query" => $models,
    "sort"  => false,
        ]);
?>

<div class="log-index panel panel-default">
    <div class="panel-body">
        <?=
        ListView::widget([
            "dataProvider" => $provider,
            "itemView"     => "_logItem"
        ]);
        ?>
    </div>
</div>
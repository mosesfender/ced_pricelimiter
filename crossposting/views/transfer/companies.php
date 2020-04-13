<?php

use common\helpers\Html;

/* @var $model \common\models\CedPartnersSearch */
/* @var $provider \yii\data\ActiveDataProvider */

$this->title                   = "Компании для импорта";
$this->params['breadcrumbs'][] = ['label' => "Кросспостинг", 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ced-partners-index">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="panel-body">
            <?php
            echo $this->render("_companiesList",
                    [
                "model"    => $model,
                "provider" => $provider,
            ]);
            ?>
        </div>
    </div>
</div>

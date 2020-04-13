<?php

use common\helpers\Html;
use yii\widgets\Pjax;

common\widgets\CedTextInput\CedTextInputAsset::register($this);
common\modules\crossposting\views\forms\assets\TransferSettingsAsset::register($this);

$this->title                   = "Список обмена данными";
$this->params['breadcrumbs'][] = ['label' => "Кросспостинг", 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ced-transfer-index">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php Pjax::begin(["id" => "transfer-grid-pjax", "enablePushState" => false]) ?>

            <?php
            echo $this->render("_transferSearch", compact("model"));
            ?> 
            <?php
            echo $this->render("_transferList", compact("model", "provider"));
            ?> 

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
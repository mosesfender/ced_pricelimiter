<?php

use common\helpers\Html;
use common\modules\crossposting\widgets\ExportButtons;
use common\modules\crossposting\widgets\ImportButtons;
use common\modules\crossposting\widgets\Transfers;
use common\modules\crossposting\widgets\Sequence;

/* @var $vendors \common\models\CedPartners[] */

$this->title                   = "Кросспостинг";
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="crosspost-default-index">
    <div class="row">
        <div class="col-md-6">
            <?= ExportButtons::widget(["label" => "Создание экспортов DOMIRE"]) ?>
        </div>  
        <div class="col-md-6">
            <?= ImportButtons::widget(["label" => "Создание импортов DOMIRE"]) ?>
        </div>  
        <div class="col-md-6">
            <?= Transfers::widget(["label" => "Кросспосты"]) ?>
        </div>  
        <div class="col-md-6">
            <?= Sequence::widget(["label" => "Очередь"]) ?>
        </div>  
        <div class="col-md-6">
            <div class="panel panel-default panel-labeled">
                <label>Логи</label>
                <div class="panel-body">
                    <?php
                    echo Html::a("Общий лог",
                            ["/crosspost/transfer/summary-log"],
                            ["class" => "btn btn-primary"]);
                    ?>
                </div>
            </div>        
        </div>        
    </div>
</div>

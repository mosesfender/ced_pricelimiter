<?php

use common\helpers\Html;
use yii\helpers\Url;

/* @var $this \common\components\View */
/* @var $model \common\modules\crossposting\models\CedTransfert */

$this->title                   = "Информация об импорте";
$this->params['breadcrumbs'][] = ['label' => "Кросспостинг", 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="crosspost-default-index">
    <h2>Импорт «<?= $model->settings->title ?>»</h2>    
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="buttonbar">
                <?=
                Html::a("К списку", Url::toRoute("transfer/transfer-list"),
                        ["class" => "btn btn-primary"]);
                ?>
                <?=
                Html::a("Редактировать",
                        Url::toRoute(["transfer/edit-transfer", "id" => $model->id]),
                        ["class" => "btn btn-primary"]);
                ?>
            </div>
        </div>
        <div class="panel-body">
            <?=
            $this->render("_popupImportSettings", ["model" => $model])
            ?>
        </div>
    </div>
</div>
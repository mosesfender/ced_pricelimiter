<?php
/* @var $this \common\components\View */
/* @var $model \common\modules\crossposting\models\CedTransfert */

use common\modules\crossposting\models\CedTransfert as ct;
use common\models\CedPartners as cp;
use yii\widgets\DetailView;
use common\helpers\Html;
use yii\helpers\Url;

$this->title                   = "Информация об экспорте";
$this->params['breadcrumbs'][] = ['label' => "Кросспостинг", 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="crosspost-default-index">
    <h2>Экспорт «<?= $model->id ?>»</h2>    
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
            <?= $this->render("_popupExportSettings",
                    ["model" => $model]) ?>
        </div>
    </div>
</div>

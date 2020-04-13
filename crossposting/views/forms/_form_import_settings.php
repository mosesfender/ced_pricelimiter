<?php

use common\modules\crossposting\models\CedTransfert;
use common\modules\crossposting\models\CedPartners;
use common\modules\crossposting\models\ImportSettings as is;
use common\modules\yeesoft\core\widgets\ActiveForm;
use common\modules\crossposting\components\Import;
use common\helpers\Html;
use yii\helpers\Url;
use common\helpers\BitwiseHelper;
use yii\widgets\DetailView;

/* @var $model \common\modules\crossposting\models\CedTransfert */
/* @var $component \common\modules\crossposting\components\Import */
/* @var $settings \common\modules\crossposting\models\ImportSettings */
?>

<div class="import-settings-form">
    <?php if ($component->settings->stage == is::STAGE_PRE): ?>
        <h2>Создание импорта для компании «<?= $component->settings->partner->title ?>»</h2>
    <?php else: ?>
        <h2>Установки импорта «<?= $component->settings->transferID ?>»</h2>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="buttonbar">
                <?=
                Html::a("К списку", Url::toRoute("transfer/transfer-list"),
                        ["class" => "btn btn-primary"]);
                ?>
            </div>            
        </div>
        <div class="panel-body">
            <?php
            $form = ActiveForm::begin([
                        "id"             => "import_settings_form",
                        "validateOnBlur" => false,
            ]);
            ?>

            <?php
            echo DetailView::widget([
                "model"      => $component->settings->getPartner(),
                "attributes" => [
                    "title",
                    "permanent_link",
                    "importVendor.title",
                    "geoPartner.title",
                ]
            ]);
            ?>


            <?php
            if ($component->transfer) {
                $form->field($component->transfer, "id")->hiddenInput()->label(false);
            }
            ?>
            <?= $form->field($component->settings, "transferID")->hiddenInput()->label(false); ?>
            <?= $form->field($component->settings, "partnerID")->hiddenInput()->label(false); ?>
            <?= $form->field($component->settings, "vendorID")->hiddenInput()->label(false); ?>
            <?= $form->field($component->settings, "stage")->hiddenInput()->label(false); ?>

            <div class="col-md-12">
                <?= $form->field($component->settings, "title");
                ?>
            </div>
            <div class="col-md-4">
                <?php
                echo $form->field($component->settings, 'importFlags')
                        ->checkboxList(CedPartners::ImportFlags(),
                                [
                            "value" => BitwiseHelper::BitValues2Array($component->settings->importFlags),
                ]);
                ?>
            </div>

            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-3">
                        <?php
                        echo $form->field($component->settings,
                                        'sheduleTransfer')
                                ->checkbox();
                        ?>
                    </div>
                    <div class="col-md-2 interval hidden">
                        <?php
                        echo $form->field($component->settings,
                                        'transferInterval')
                                ->dropDownList(is::Intervals(),
                                        ["disabled" => true]);
                        ?>
                    </div>
                    <div class="col-md-12 do-sequence hidden">
                        <?php
                        echo $form->field($component->settings, 'doSequence')
                                ->checkbox();
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <?=
                Html::button($component->settings->stage == is::STAGE_EDIT ? "Сохранить"
                                    : "Создать импорт",
                        ["class" => "btn btn-danger sbmt"]);
                ?>
            </div>            

            <?php $form->end(); ?>
        </div>
    </div>
</div>

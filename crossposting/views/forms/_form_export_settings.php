<?php

use common\modules\yeesoft\core\widgets\ActiveForm;
use common\modules\yeesoft\core\helpers\Html;
use common\modules\crossposting\models\ExportSettings as es;
use common\modules\crossposting\widgets\ShedulerWidget\Sheduler;
use common\modules\crossposting\models\CedTransfert;
use common\modules\crossposting\models\CedPartners as cp;
use common\models\CedPartnersObjectMap as cpom;
use yii\db\Expression as exp;
use yii\helpers\Url;

/* @var $this \common\components\View */
/* @var $settings \common\modules\crossposting\models\ExportSettings | \common\modules\crossposting\models\CedTransfert */
/* @var $transfer \common\modules\crossposting\models\CedTransfert */
/* @var $alert string */

$alert = "";
if ($settings instanceof \common\modules\crossposting\models\CedTransfert) {
    $transfer        = $settings;
    $settings        = $settings->getSettings(CedTransfert::TYPE_EXPORT);
    $settings->stage = es::STAGE_EDIT;
}

$partners = [];
if ($settings->viewMode & es::LIST_MODE_SIMPLE) {
    $qq         = $settings
            ->getPartnersQuery()
            ->orderBy("title ASC")
            ->select(["*", "cid" => "CONCAT('" . cp::CID_ALL . "', '.', id)"]);
    $partners[] = $qq->all();
    $alert      = "В этом режиме для экспорта выбираются все объекты компаний, вне зависимости откуда они появились.";
} elseif ($settings->viewMode & es::LIST_MODE_SEPARATED) {
    $qq         = cp::find()
            ->groupBy("cp.id")
            ->select(["cp.*", "cid" => "CONCAT('" . cp::CID_NO_IMPORTED . "', '.', cp.id)"])
            ->from(["cp" => cp::tableName()])
            ->leftJoin(["cpom" => cpom::tableName()],
                    ["cp.id" => new exp("cpom.sub_partner_id")])
            ->where(["AND",
        ["OR",
            ["!=", "cpom.partner_id", $settings->vendor->id],
            ["!=", "cp.import_processor", $settings->vendor->id],
        ],
        ["!=", "cp.id", $settings->vendor->id]
    ]);
    $partners[] = $qq->orderBy("title ASC")->all();
    $qq         = cp::find()
            ->groupBy("cp.id")
            ->select(["cp.*", "cid" => "CONCAT('" . cp::CID_IMPORTED . "', '.', cp.id)"])
            ->from(["cp" => cp::tableName()])
            ->leftJoin(["cpom" => cpom::tableName()],
                    ["cp.id" => new exp("cpom.sub_partner_id")])
            ->where(["AND",
        ["AND",
            ["cpom.partner_id" => $settings->vendor->id],
            ["cp.import_processor" => $settings->vendor->id],
        ],
        ["!=", "cp.id", $settings->vendor->id]
    ]);
    $partners[] = $qq->orderBy("title ASC")->all();
    $alert      = "В этом режиме для экспорта в верхнем списке объекты, которые не импортировались с {$settings->vendor->title}, "
            . "в нижнем те, которые импортировались с {$settings->vendor->title}.";
}
?>

<div class="export-settings-form">
    <h2>Установки экспорта «<?= $settings->vendor->title ?>»</h2>

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
                        "id"             => "property-object-form",
                        "validateOnBlur" => false,
            ]);
            ?>
            <div class="col-md-6 alert alert-warning" role="alert">Внимание! Набор компаний в режимах несовместим, при переключении режима отмеченные компании не сохраняются.</div>
            <div class="col-md-6 text-right">
                <?php
                echo $form->field($settings, "viewMode")
                        ->radioList([0x10 => "Режим полного экспорта", 0x20 => "Режим разделения"],
                                [
                            "class"       => "buttoned inline",
                            "unsellect"   => null,
                            "itemOptions" => [
                                "wrapOptions"  => [
                                    "class" => "radio"
                                ],
                                "addonOptions" => [
                                    "class" => "back"
                                ]
                            ]
                        ])->label(false);
                ?>
            </div>
            <div class="col-md-12 alert alert-success" role="alert"><?= $alert ?></div>
            <div class="col-md-12">
                <?php
                if ($settings->stage == es::STAGE_EDIT) {
                    echo $form->field($transfer, "id")->hiddenInput()->label(false);
                }
                ?>
                <?= $form->field($settings, "transferID")->hiddenInput()->label(false); ?>
                <?= $form->field($settings, "vendorID")->hiddenInput()->label(false); ?>
                <?= $form->field($settings, "partnerID")->hiddenInput()->label(false); ?>
                <?= $form->field($settings, "stage")->hiddenInput()->label(false); ?>
                <?= $form->field($settings, "itemsCount")->hiddenInput()->label(false); ?>
                <?= $form->field($settings, "title"); ?>
                <?php
                foreach ($partners as $idx => $partnerList):
                    if ($settings->viewMode == es::LIST_MODE_SIMPLE) {
                        $caption = "Компании, объекты которых включаются в экспорт";
                    } elseif ($settings->viewMode == es::LIST_MODE_SEPARATED) {
                        switch ($idx) {
                            case 0:
                                $caption = "Объекты компаний, которые не были импортированы с {$settings->vendor->title}";
                                break;
                            case 1:
                                $caption = "Объекты компаний, импортированные с {$settings->vendor->title}";
                                break;
                        }
                    }
                    echo $form->field($settings, "exportCompaniesIds")
                            ->checkboxList($partnerList,
                                    [
                                "class"    => "columned-checkboxlist",
                                "unselect" => null,
                                "item"     => function($index, $label, $name, $checked, $value)use(&$settings, $partnerList, $idx) {
                                    /* @var $label \common\models\CedPartners */
                                    if ($settings->viewMode == es::LIST_MODE_SIMPLE) {
                                        $count = $label->getPublishedObjectsCount($settings->vendor->id,
                                                cp::CID_ALL);
                                    } elseif ($settings->viewMode == es::LIST_MODE_SEPARATED) {
                                        switch ($idx) {
                                            case 0:
                                                $count = $label->getPublishedObjectsCount($settings->vendor->id,
                                                        cp::CID_NO_IMPORTED);
                                                break;
                                            case 1:
                                                $count = $label->getPublishedObjectsCount($settings->vendor->id,
                                                        cp::CID_IMPORTED);
                                                break;
                                        }
                                    }
                                    $cid = $label->cid;
                                    return Html::checkbox($name,
                                                    in_array($cid,
                                                            $settings->exportCompaniesIds,
                                                            true),
                                                    [
                                                "id"           => "eci{$cid}",
                                                "value"        => $cid,
                                                "disabled"     => !$label->canExport || !$count,
                                                "label"        => "{$label->title} <sup>{$count}</sup>",
                                                "labelOptions" => [
                                                    "for" => "eci{$cid}"
                                                ],
                                                "itemOptions"  => [
                                                    "wrapOptions" => [
                                                        "class"      => "checkbox",
                                                        "data-count" => $count
                                                    ]
                                                ]
                                    ]);
                                }
                            ])->label($caption);
                endforeach;
                ?>
            </div>

            <div class="col-md-12 error-message">Превышен лимит количества объектов для экспорта!</div>

            <div class="col-md-6">
                <?=
                        $form->field($settings, "exportItemsLimit",
                                ["options" => ["class" => "form-group form-inline"]])
                        ->label("Лимит количества объектов в одном файле");
                ?>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <?=
                    Html::label('Выбрано объектов в отмеченных компаниях: <span class="choosed-items">0</span>',
                            null,
                            [
                        "class" => "control-label"
                    ]);
                    ?>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <?php
//                    echo Html::checkbox("saveToVendor", false,
//                            [
//                        "id"           => "chb_savetovendor",
//                        "label"        => "Сохранить установки для использования в дальнейшем",
//                        "labelOptions" => [
//                            "for" => "chb_savetovendor",
//                        ]
//                    ]);
                    ?>
                </div>
            </div>

            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3">
                        <?php
                        echo $form->field($settings, 'sheduleTransfer')
                                ->checkbox();
                        ?>
                    </div>
                    <div class="col-md-2 interval hidden">
                        <?php
                        echo $form->field($settings, 'transferInterval')
                                ->dropDownList(es::Intervals(),
                                        ["disabled" => true]);
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12 do-sequence hidden">
                <?php
                echo $form->field($settings, 'doSequence')
                        ->checkbox();
                ?>
            </div>

            <div class="col-md-12">
                <?=
                Html::button($settings->stage == es::STAGE_EDIT ? "Сохранить" : "Создать экспорт",
                        ["class" => "btn btn-danger sbmt"]);
                ?>
            </div>
            <?php $form->end(); ?>
        </div>

    </div>
</div>
</div>
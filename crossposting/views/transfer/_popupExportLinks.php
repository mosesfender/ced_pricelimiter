<?php

use common\helpers\Html;
use common\widgets\CedTextInput\CedTextInputWidget;
use common\modules\crossposting\models\CedTransfert;

/* @var $module \common\modules\crossposting\Module */
/* @var $id string */

$links = $module->export->generateFileLinks($id);
?>

<div class="transfer-links">
    <?php
    foreach ($links as $name => $files):
        /* @var $files \common\modules\crossposting\components\ExportFiles */
        ?>
        <div class="panel panel-default panel-labeled btn-">
            <label><?= $name ?></label>
            <div class="panel-body">
                <div class="form-group row">
                    <?=
                    Html::label("ZIP:", null,
                            ["class" => "col-sm-1 col-form-label"]);
                    ?>
                    <div class="col-sm-11">
                        <?php
                        echo CedTextInputWidget::widget([
                            "name"         => "ziplink",
                            "value"        => $files->zipFile,
                            "wrapCssClass" => "form-group",
                            "options"      => [
                                "data-ancestor" => "mfTextInput",
                                "class"         => "form-control"
                            ],
                            "addons"       => [
                                "buttons" => [
                                    "addonConstructor" => "mf.TSvgButtons",
                                    "addonPosition"    => "parent",
                                    "attributes"       => [
                                        "class" => "btn-row",
                                    ],
                                    "addons"           => [
                                        "clipboardBtn" => [
                                            "addonConstructor" => "mf.TSvgImportedAddon",
                                            "addonPosition"    => "owner",
                                            "svgClass"         => "input-button",
                                            "href"             => "#clipboard",
                                            "attributes"       => [
                                                "class" => "btn clipboard",
                                                "title" => "Скопировать ссылку в буфер",
                                            ]
                                        ],
                                        "downloadBtn"  => [
                                            "addonConstructor" => "mf.TSvgImportedAddon",
                                            "addonPosition"    => "owner",
                                            "svgClass"         => "input-button",
                                            "href"             => "#download",
                                            "attributes"       => [
                                                "class" => "btn download",
                                                "title" => "Загрузить файл",
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ]);
                        ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?=
                    Html::label("XML:", null,
                            ["class" => "col-sm-1 col-form-label"]);
                    ?>
                    <div class="col-sm-11">
                        <?php
                        echo CedTextInputWidget::widget([
                            "name"    => "unziplink",
                            "value"   => $files->unZipFile,
                            "options" => [
                                "data-ancestor" => "mfTextInput",
                                "class"         => "form-control"
                            ],
                            "addons"  => [
                                "buttons" => [
                                    "addonConstructor" => "mf.TSvgButtons",
                                    "addonPosition"    => "parent",
                                    "attributes"       => [
                                        "class" => "btn-row",
                                    ],
                                    "addons"           => [
                                        "clipboardBtn" => [
                                            "addonConstructor" => "mf.TSvgImportedAddon",
                                            "addonPosition"    => "owner",
                                            "svgClass"         => "input-button",
                                            "href"             => "#clipboard",
                                            "attributes"       => [
                                                "class" => "btn clipboard",
                                                "title" => "Скопировать ссылку в буфер",
                                            ]
                                        ],
                                        "downloadBtn"  => [
                                            "addonConstructor" => "mf.TSvgImportedAddon",
                                            "addonPosition"    => "owner",
                                            "svgClass"         => "input-button",
                                            "href"             => "#download",
                                            "attributes"       => [
                                                "class" => "btn download",
                                                "title" => "Загрузить файл",
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
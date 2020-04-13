<?php

use common\modules\crossposting\models\CedTransfert as ct;
use common\modules\crossposting\models\CedPartners;
use common\modules\crossposting\models\ExportSettings;
use yii\widgets\DetailView;

/* @var $model \common\modules\crossposting\models\CedTransfert */

$settings = $model->getSettings(ct::TYPE_EXPORT);
?>

<div class="transfer-settings">
    <?=
    DetailView::widget([
        "model"      => $model,
        "attributes" => [
            "id",
            [
                "label"  => "Название",
                "format" => "html",
                "value"  => function(\common\modules\crossposting\models\CedTransfert $model) {
                    /* @var $set \common\modules\crossposting\models\ExportSettings */
                    $set = $model->getSettings(ct::TYPE_EXPORT);
                    return $set->title;
                }
            ],
            "created_at:datetime",
            "finished_at:datetime",
            [
                "attribute" => "filename",
                "format"    => "html",
                "value"     => function(\common\modules\crossposting\models\CedTransfert $model) {
                    /* @var $set \common\modules\crossposting\models\ExportSettings */
                    $ret = "<ul>";
                    $set = $model->getSettings(ct::TYPE_EXPORT);
                    foreach ($set->outFiles as $file) {
                        $ret .= "<li>{$file}</li>";
                    }
                    $ret         .= "</ul>";
                    return $ret;
                }
            ],
            [
                "label" => "Объектов в экспорте",
                "value" => function(\common\modules\crossposting\models\CedTransfert $model) {
                    return $model->getSettings(ct::TYPE_EXPORT)->itemsCount;
                }
            ],
            "user.fullname",
            [
                "label"  => "Объекты компаний",
                "format" => "html",
                "value"  => function(\common\modules\crossposting\models\CedTransfert $model) {
                    /* @var $set \common\modules\crossposting\models\ExportSettings */
                    return $this->render("__companiesUl",
                                    ["list" =>
                                $model->getSettings(ct::TYPE_EXPORT)->utilCompanyWithExportMode()
                    ]);
                }
            ],
            [
                "label"  => "Очередь",
                "format" => "html",
                "value"  => function(\common\modules\crossposting\models\CedTransfert $model) {
                    /* @var $set \common\modules\crossposting\models\ExportSettings */
                    /* @var $partners \common\models\CedPartners[] */
                    $set = $model->getSettings(ct::TYPE_EXPORT);
                    $ret = "<ul>";
                    foreach ($model->sequence as $seq) {
                        $rem = [];
                        if ($seq->begin_at) {
                            $rem[] = "стартовало <b>" . \yii::$app->formatter->asDatetime($seq->begin_at) . "</b>";
                        } else {
                            $rem[] = "не начато";
                        }
                        if ($seq->end_at) {
                            $rem[] = "завершёно <b>" . \yii::$app->formatter->asDatetime($seq->end_at) . "</b>";
                        } else {
                            $rem[] = "не завершено";
                        }
                        if ($seq->doneErrors) {
                            $rem[] = "завершено с ошибками";
                        }
                        $ret .= "<li>{$seq->filename} (" . implode(" | ", $rem) . ")</li>";
                    }
                    $ret .= "</ul>";
                    return $ret;
                }
            ],
            [
                "label"  => "Периодический запуск",
                "format" => "html",
                "value"  => function(\common\modules\crossposting\models\CedTransfert $model) {
                    /* @var $set \common\modules\crossposting\models\ExportSettings */
                    /* @var $partners \common\models\CedPartners[] */
                    $interval = $model->getSettings(ct::TYPE_EXPORT)->transferInterval / 3600;
                    return $model->getSettings(ct::TYPE_EXPORT)->sheduleTransfer
                                ? "Да (через {$interval} часов)" : "Нет";
                }
            ],
        ]
    ]);
    ?>
</div>
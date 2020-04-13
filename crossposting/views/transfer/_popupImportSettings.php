<?php

use common\modules\crossposting\models\CedTransfert as ct;
use common\modules\crossposting\models\CedPartners;
use common\modules\crossposting\models\ExportSettings;
use yii\widgets\DetailView;
use common\helpers\Html;
use common\helpers\StringHelper;

/* @var $model \common\modules\crossposting\models\CedTransfert */

$settings = $model->getSettings(ct::TYPE_IMPORT);
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
//            [
//                "label" => "Объектов в импорте",
//                "value" => function(\common\modules\crossposting\models\CedTransfert $model) {
//                    return $model->getSettings(ct::TYPE_EXPORT)->itemsCount;
//                }
//            ],
            "partner.title",
            [
                "label"  => "Флаги импорта",
                "format" => "raw",
                "value"  => function($model) {
                    /* @var $set \common\modules\crossposting\models\ImportSettings */
                    $set = $model->getSettings(ct::TYPE_IMPORT);
                    return "<ul>" . Html::ArrayValuesToStringFromVals(
                                    $set->importFlags,
                                    CedPartners::ImportFlags(), false, "\n",
                                    "li"
                            ) . "</ul>";
                }
            ],
            [
                "label"  => "Очередь",
                "format" => "html",
                "value"  => function($model) {
                    /* @var $set \common\modules\crossposting\models\ImportSettings */
                    /* @var $partners \common\models\CedPartners[] */
                    $set = $model->getSettings(ct::TYPE_IMPORT);
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
                    try {
                        $_bts = StringHelper::secondsToHiFormat($model->sequenceOne->begin_time);
                    } catch (\Exception $ex) {
                        
                    }
                    $interval = $model->getSettings(ct::TYPE_IMPORT)->transferInterval / 3600;
                    return $model->getSettings(ct::TYPE_IMPORT)->sheduleTransfer
                                ? sprintf("Да (через %d часов в %s)", $interval,
                                          $_bts) : "Нет";
                }
            ],
            [
                "label"  => "Однократный запуск",
                "format" => "html",
                "value"  => function(\common\modules\crossposting\models\CedTransfert $model) {
                    /* @var $set \common\modules\crossposting\models\ImportSettings */
                    /* @var $partners \common\models\CedPartners[] */
                    return $model->getSettings(ct::TYPE_IMPORT)->doSequence ? "Да"
                                : "Нет";
                }
            ],
            "user.fullname",
        ]
    ]);
    ?>
</div>
<?php

use common\modules\crossposting\models\CedTransfert as ct;
use common\helpers\Html;
use yii\helpers\Url;

/* @var $label string */
/* @var $items \common\modules\crossposting\models\CedTransfert[] */
?>

<div class="panel panel-default panel-labeled transfer-widget">
    <label><?= $label ?></label>
    <div class="panel-body">
        <ul>
            <?php foreach ($items as $item): ?>
                <li>
                    <span>
                        <?php
                        if ($item->tr_type == ct::TYPE_EXPORT) {
                            echo "Экспорт";
                        }
                        if ($item->tr_type == ct::TYPE_IMPORT) {
                            echo "Импорт";
                        }
                        ?>
                    </span>
                    <span><?= $item->id ?></span>
                    <span><?= \yii::$app->formatter->asDatetime($item->finished_at) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <?= Html::a("К списку", Url::toRoute("transfer/transfer-list"),
                ["class" => "btn btn-primary"]); ?>
    </div>
</div>
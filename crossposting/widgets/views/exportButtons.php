<?php

use common\helpers\Html;
use common\modules\crossposting\models\TransferSettings;

/* @var $label string */
/* @var $vendors \common\models\CedPartners[] */
?>
<div class="panel panel-default panel-labeled">
    <label><?= $label ?></label>
    <div class="panel-body">
        <?php foreach ($vendors as $vendor): ?>
            <?php
            echo Html::a("Экспорт {$vendor->title}",
                    ["/crosspost/transfer/create-export"],
                    [
                "data-method" => "POST",
                "data-params" => [
                    "ExportSettings[vendorID]" => $vendor->id,
                    "ExportSettings[stage]"    => TransferSettings::STAGE_PRE,
                ],
                "class"       => "btn btn-primary"
            ]);
            ?>
<?php endforeach; ?>
    </div>
</div>
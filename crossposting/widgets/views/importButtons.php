<?php

use common\helpers\Html;
use common\modules\crossposting\models\TransferSettings;

/* @var $label string */
?>
<div class="panel panel-default panel-labeled">
    <label><?= $label ?></label>
    <div class="panel-body">
        <?php
        echo Html::a("Список компаний для импорта",
                "/crosspost/transfer/import-companies",
                [
            "class" => "btn btn-primary"
        ]);
        ?>
    </div>
</div>
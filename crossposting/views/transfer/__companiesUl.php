<?php

use common\models\CedPartners as cp;
use common\helpers\Html;

/* @var $list array See models\ExportSettings::utilCompanyWithExportMode */
?>

<ul>
    <?php foreach ($list as $item): ?>
        <?php
        $modeStr = "";
        switch ($item["mode"]) {
            case cp::CID_ALL:
                $modeStr = "все объекты";
                break;
            case cp::CID_NO_IMPORTED:
                $modeStr = "не импортированные";
                break;
            case cp::CID_IMPORTED:
                $modeStr = "импортированные";
                break;
        }
        echo Html::tag("li", "{$item["title"]}\t($modeStr)");
        ?>
    <?php endforeach; ?>
</ul>
<?php

namespace common\modules\pricelimiter\widgets;

use common\helpers\Html;

class GeoDataCell extends \yii\grid\DataColumn {

    public $format = "raw";

    public function getDataCellValue($model, $key, $index) {
        return Html::tag("span", $model->label) . Html::a(Html::svgLink("running",
                                "svg16"), "javascript:;", ["data-ancestor" => "run-process"]);
    }

}

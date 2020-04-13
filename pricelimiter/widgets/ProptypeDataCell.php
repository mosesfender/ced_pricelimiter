<?php

namespace common\modules\pricelimiter\widgets;

use common\helpers\Html;

class ProptypeDataCell extends \yii\grid\DataColumn {

    public function getDataCellValue($model, $key, $index) {
        $values = $this->grid->values;
        if (isset($values[$key]) && isset($values[$key][$model->id])) {
            return $values[$key][$model->id];
        }
        return "";
    }

    public function renderHeaderCell() {
        return Html::tag('th',
                        Html::tag("div", Html::tag("span", $this->renderHeaderCellContent())),
                        $this->headerOptions);
    }
}

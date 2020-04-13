<?php

namespace common\modules\crossposting\widgets;

use common\models\CedPartners as cp;

class ExportButtons extends \yii\base\Widget {

    public $label;

    public function run(): string {
        $label = $this->label;
        $vendors = cp::find()
                ->where(["AND",
                    ["&", "_flags", cp::FLAG_HAS_EXPORTER],
                    ["&", "_flags", cp::FLAG_ENABLED],
                ])
                ->all();
        return $this->render("exportButtons", compact("vendors", "label"));
    }

}

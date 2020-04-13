<?php

namespace common\modules\crossposting\widgets;

class ImportButtons extends \yii\base\Widget {

    public $label;

    public function run(): string {
        $label = $this->label;
        return $this->render("importButtons", compact("label"));
    }

}

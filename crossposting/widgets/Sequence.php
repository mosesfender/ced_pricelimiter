<?php

namespace common\modules\crossposting\widgets;

class Sequence extends \yii\base\Widget {

    public $label;

    public function run(): string {
        $label = $this->label;
        return $this->render("sequence", compact("label"));
    }

}

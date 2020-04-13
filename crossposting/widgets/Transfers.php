<?php

namespace common\modules\crossposting\widgets;

use \common\modules\crossposting\models\CedTransfert;

class Transfers extends \yii\base\Widget {

    public $label;
    public $maxItems = 5;

    public function run(): string {

        return $this->render("transfers",
                        [
                    "label" => $this->label,
                    "items" => $this->getItems(),
        ]);
    }

    protected function getItems() {
        return CedTransfert::find()
                        ->orderBy("finished_at DESC")
                        ->limit($this->maxItems)
                        ->all();
    }

}

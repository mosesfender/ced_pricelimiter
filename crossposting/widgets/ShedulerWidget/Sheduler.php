<?php

namespace common\modules\crossposting\widgets\ShedulerWidget;

class Sheduler extends \yii\base\Widget {

    /**
     * @var \common\modules\crossposting\models\CedTransfertShedule
     */
    public $model;

    public function run() {
        $this->getView()->registerAssetBundle(ShedulerWidgetAsset::class);
        return $this->render("index",
                        [
                    "model" => $this->model,
        ]);
    }

}

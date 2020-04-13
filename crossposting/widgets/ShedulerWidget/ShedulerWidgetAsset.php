<?php

namespace common\modules\crossposting\widgets\ShedulerWidget;

use yii\web\AssetBundle;

class ShedulerWidgetAsset extends AssetBundle {

    public $sourcePath     = '@common/modules/crossposting/widgets/ShedulerWidget/views/assets/dist';
    public $css            = [
    ];
    public $js             = [
        "index.js"
    ];
    public $depends        = [
    ];
    public $publishOptions = [
        "forceCopy" => YII_ENV != "prod"
    ];

}

<?php

namespace common\modules\pricelimiter\widgets;

use yii\web\AssetBundle;

class LimiterGridViewAsset extends AssetBundle {

    public $sourcePath     = '@common/modules/pricelimiter/widgets/assets/dist';
    public $css            = [
        "limitergridview.css"
    ];
    public $js             = [
        "pricelimiter.js"
    ];
    public $depends        = [
    ];
    public $publishOptions = [
        "forceCopy" => YII_ENV != "prod"
    ];

}

<?php

namespace common\modules\crossposting\views\forms\assets;

use yii\web\AssetBundle;

class TransferSettingsAsset extends AssetBundle {

    public $sourcePath     = __DIR__ . "/dist";
    public $css            = [
        "style.css"
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

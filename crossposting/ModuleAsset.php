<?php

namespace common\modules\crossposting;

use yii\web\AssetBundle;

class ModuleAsset extends AssetBundle {

    public $sourcePath     = '@common/modules/crossposting';
    public $css            = [
        'css/categories.css',
    ];
    public $js             = [
    ];
    public $depends        = [
        'v1\components\AppAsset'
    ];
    public $publishOptions = [
        "forceCopy" => YII_ENV != "prod"
    ];

}

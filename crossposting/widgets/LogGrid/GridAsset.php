<?php

namespace common\modules\crossposting\widgets\LogGrid;

use yii\web\AssetBundle;

class GridAsset extends AssetBundle {

    public $sourcePath     = '@common/modules/crossposting/widgets/LogGrid';
    public $css            = [
        'style.css',
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

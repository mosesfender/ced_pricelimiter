<?php

namespace common\modules\pricelimiter\components;

class FileCache extends \yii\caching\FileCache {

    public $cachePath      = "@common/cache/pricelimiter";
    public $cacheKey       = "pl";
    public $dependencyName = "pl.dep";

}

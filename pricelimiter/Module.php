<?php

namespace common\modules\pricelimiter;

use yii\caching\FileCache;

/**
 * pricelimiter module definition class
 */
class Module extends \yii\base\Module {

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\pricelimiter\controllers';

    /**
     * {@inheritdoc}
     */
    public function init() {
        parent::init();
    }

    /**
     * @return \common\modules\pricelimiter\components\FileCache
     */
    public function getCache() {
        return $this->cache;
    }

}

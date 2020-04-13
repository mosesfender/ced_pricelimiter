<?php

namespace common\modules\crossposting\controllers;

use \common\modules\yeesoft\core\controllers\admin\BaseController as Controller;
use common\modules\crossposting\models\XMLModel as model;
use common\models\CedPartners as cp;
use common\modules\yeesoft\core\web\MultilingualUrlManager;
use common\modules\crossposting\models\xml\vendors\homesoverseas\Document;

/**
 * Default controller for the `crosspost` module
 */
class DefaultController extends Controller {

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex() {
        return $this->render('index');
    }

    public function actionProbe() {
        $stat = new \common\modules\crossposting\models\TransferStat([
            "outFileName" => "@wrap/transfer/temp/probe",
        ]);
        prer($stat->flush());
    }

    /**
     * @return \common\modules\crossposting\Module
     */
    protected function getModule() {
        return $this->module;
    }

}

require_once realpath(__DIR__ . '/../components/TLog.php');

<?php

namespace common\modules\crossposting\controllers;

use common\modules\yeesoft\core\controllers\admin\BaseController;
use common\modules\crossposting\components\Export;
use common\modules\crossposting\models\ExportSettings;
use common\modules\crossposting\models\ImportSettings;
use common\modules\crossposting\views\forms\assets\TransferSettingsAsset;
use common\modules\crossposting\behaviors\PartnersBehavior;
use common\modules\crossposting\models\CedTransfert;
use \common\modules\crossposting\models\CedTransfertSearch;
use common\components\UAC\SimpleUac;
use yii\helpers\Url;
use common\helpers\ArrayHelper as ah;
use common\modules\crossposting\components\Sequence;
use common\helpers\CommonHelper as ch;
use common\modules\crossposting\models\CedPartners;
use common\modules\crossposting\models\CedPartnersSearch;
use common\modules\crossposting\models\CedTransfertSequence;
use common\modules\crossposting\models\CedTransfertSequenceWork as ctsw;
use common\models\ServiceLog;
use common\components\CEDException;

class TransferController extends BaseController {

    public function init() {
        parent::init();
        TransferSettingsAsset::register($this->getView());
    }

    public function actionCreateExport() {
        $post = \yii::$app->request->post();
        if (!isset($post["ExportSettings"]["viewMode"])) {
            $post["ExportSettings"]["viewMode"] = ExportSettings::LIST_MODE_SIMPLE;
        }
        switch ($post["ExportSettings"]["stage"]) {
            case ExportSettings::STAGE_CHANGE_VIEWMODE:
                $model        = new ExportSettings();
                $model->load($post, "ExportSettings");
                $model->stage = ExportSettings::STAGE_POST;
                return $this->render("export", compact("model"));
                break;
            case ExportSettings::STAGE_PRE:
                $model        = ExportSettings::ModelFromVendor($post["ExportSettings"]["vendorID"]);
                $model->stage = ExportSettings::STAGE_POST;
                $model->title = "";
                if (!$model->partnerID) {
                    $model->setAttributes([
                        "vendorID"  => $post["ExportSettings"]["vendorID"],
                        "stage"     => ExportSettings::STAGE_POST,
                        "viewMode"  => $post["ExportSettings"]["viewMode"],
                        "partnerID" => 2,
                    ]);
                }
                return $this->render("export", compact("model"));
                break;
            case ExportSettings::STAGE_POST:

                $model           = new ExportSettings($post["ExportSettings"]);
                $exportComponent = $this->getModule()->export;
                $exportComponent->create($model);
                $exportComponent->transfer->getSettings(CedTransfert::TYPE_EXPORT);
                $exportComponent->sequenceComponent->prepareTransfer($exportComponent);
                $exportComponent->transfer->save();
                return $this->redirect(Url::toRoute(["view-export", "id" => $exportComponent->transfer->id]));
                break;
        }
    }

    public function actionCreateImport() {
        $post = \yii::$app->request->post();
        //prer($post, 0, 1);
        if (!isset($post["ImportSettings"])) {
            $partner = CedPartners::findOne($post["id"]);

            $this->getModule()->import->settings = new ImportSettings([
                "partnerID" => $partner->id,
                "vendorID"  => $partner->import_processor,
                "stage"     => ImportSettings::STAGE_PRE,
            ]);

            $this->getModule()->import->settings->importFlags = $this->getModule()->import->settings->partner->_flags_import;
            $this->getModule()->import->settings->title       = "Импорт «{$this->getModule()->import->settings->partner->title}»";
            return $this->render("../forms/_form_import_settings",
                                 ["component" => $this->getModule()->import]);
        } else {
            switch ($post["ImportSettings"]["stage"]) {
                case ImportSettings::STAGE_POST:
                    $settings = new ImportSettings($post["ImportSettings"]);
                    $this->getModule()->import->create($settings);
                    $this->getModule()->import->sequenceComponent->prepareTransfer($this->getModule()->import);
                    $this->getModule()->import->transfer->save();
                    \TLog::flush();
                    return $this->redirect(Url::toRoute(["transfer/view-import",
                                        "id" => $this->getModule()->import->transfer->id]));
                    break;
            }
        }
    }

    public function actionTransferList() {
        $model = new CedTransfertSearch;
        $uac   = SimpleUac::UAC("transfer_search");
        $get   = \yii::$app->request->get();
        try {
            $get["CedTransfertSearch"]["tr_type"] = new \yii\helpers\ReplaceArrayValue(array_unique($get["CedTransfertSearch"]["tr_type"]));
        } catch (\Exception $ex) {
//            $get["CedTransfertSearch"]["tr_type"] = new \yii\helpers\UnsetArrayValue();
        }
        $params = null;

        $params = ah::merge($uac->restore(
                                $params), $get, []);
        //prer($params);
        $uac->store($params);

        $_GET["page"]     = isset($params["page"]) ? $params["page"] : 1;
        $_GET["sort"]     = isset($params["sort"]) ? $params["sort"] : "id";
        $_GET["per-page"] = isset($params["per-page"]) ? $params["per-page"] : 20;
        //prer($_GET, 1);

        $provider = $model->search($params);
        return $this->render("transfers", compact("model", "provider"));
    }

    public function actionViewExport($id) {
        $model = CedTransfert::findOne($id);
        return $this->render("viewExport", compact("model"));
    }

    public function actionViewImport($id, $modal = null) {
        $model = CedTransfert::findOne($id);
        $model->getSettings(CedTransfert::TYPE_IMPORT);
        if($modal){
            $this->layout = "@backend/views/layouts/dummy";
            return $this->render("_popupImportSettings", compact("model"));
        }
        return $this->render("viewImport", compact("model"));
    }

    public function actionEditTransfer($id) {
        if (\yii::$app->request->isPost) {
            $post = \yii::$app->request->post();
            if (isset($post["ExportSettings"])) {
                /* Export */
                switch ($post["ExportSettings"]["stage"]) {
                    case ExportSettings::STAGE_CHANGE_VIEWMODE:
                        /* @var $component components\Export */
                        $model = CedTransfert::findOne($id);
                        $model->getSettings(CedTransfert::TYPE_EXPORT);
                        try {
                            $model->settings->exportCompaniesIds = $post["ExportSettings"]["exportCompaniesIds"];
                        } catch (\Exception $ex) {
                            $model->settings->exportCompaniesIds = [];
                        }
                        $model->settings->stage    = ExportSettings::STAGE_EDIT;
                        $model->settings->viewMode = $post["ExportSettings"]["viewMode"];
                        $model->settings->revertIds();
                        //prer($model, 0, 1);
                        return $this->render("export", compact("model"));
                        break;
                    case ExportSettings::STAGE_POST:
                        /* @var $component components\Export */
                        $component                 = $this->getModule()->export;
                        $component->setTransfer($post["CedTransfert"]["id"]);
                        \TLog::export($component->transfer->id, $this->module)::title("Редактирование");
                        /* @var $model CedTransfert */
                        $component->transfer->getSettings(CedTransfert::TYPE_EXPORT)
                                ->load($post, "ExportSettings");

                        $component->sequenceComponent->prepareTransfer($component);

                        foreach ($component->transfer->settings->getDirtyAttributes() as $key => $val) {
                            \TLog::info("Изменёна опция «{$component->transfer->settings->getAttributeLabel($key)}»");
                        }
                        if ($component->transfer->save()) {
                            \TLog::info("Изменён экспорт")::flush();
                        }
                        //$component->sequenceComponent->updateTransfer($component);
                        return $this->redirect(Url::toRoute(["view-export",
                                            "id" => $component->transfer->id]));
                        break;
                }
            }
            if (isset($post["ImportSettings"])) {
                /* Import */
                $component = $this->getModule()->import;
                $component->setTransfer($post["ImportSettings"]["transferID"]);
                \TLog::import($component->transfer->id, $this->module)::title("Редактирование");
                $component->transfer->getSettings(CedTransfert::TYPE_IMPORT)
                        ->load($post, "ImportSettings");

                $component->sequenceComponent->prepareTransfer($component);

                foreach ($component->transfer->settings->getDirtyAttributes() as $key => $val) {
                    \TLog::info("Изменёна опция «{$component->transfer->settings->getAttributeLabel($key)}»");
                }

                if ($component->transfer->save()) {
                    \TLog::info("Изменён импорт")::flush();
                }
                return $this->redirect(Url::toRoute(["transfer/view-import",
                                    "id" => $id]));
            }
        }

        $model = CedTransfert::findOne($id);
        switch ($model->tr_type) {
            case CedTransfert::TYPE_EXPORT:
                return $this->render("export", ["model" => $model]);
                break;
            case CedTransfert::TYPE_IMPORT:
                $component           = $this->getModule()->import;
                $component->transfer = $model;

                $component->getSettings(CedTransfert::TYPE_IMPORT)->stage = ImportSettings::STAGE_EDIT;
                return $this->render("import", ["component" => $component]);
                break;
        }
    }

    public function actionImportCompanies() {
        $model  = new CedPartnersSearch();
        $uac    = SimpleUac::UAC("partners_search");
        $params = null;
        $params = ah::mergeWithExclude($uac->restore(
                                $params), \yii::$app->request->get(), []);
        $uac->store($params);

        $_GET["page"] = isset($params["page"]) ? $params["page"] : 1;
        $_GET["sort"] = isset($params["sort"]) ? $params["sort"] : "id";

        $provider = $model->search($params);
        $provider->query->andWhere(["NOT", ["&", "_flags", CedPartners::FLAG_NO_IMPORT]]);
        $provider->query->andWhere(["&", "_flags", CedPartners::FLAG_ENABLED]);
        return $this->render("companies", compact("model", "provider"));
    }

    public function actionSequenceList() {
        //prer(ctsw::find()->one(),1,1);
        $provider             = new \yii\data\ActiveDataProvider();
        $provider->pagination = false;
        $provider->query      = ctsw::find();
        return $this->render("sequence", compact("provider"));
    }

    /**
     * Возвращает прогресс для грида очереди, все задания
     */
    public function actionSequenceProgress() {
        //prer(ctsw::findG()->createCommand()->rawSql);
        return $this->asJsonSuccess(null, ctsw::findG()->all());
    }

    /**
     * @return \common\modules\crossposting\Module
     */
    protected function getModule() {
        return $this->module;
    }

    /* POPUPS */

    public function actionLetSettingsPopup($id) {
        $model = CedTransfert::findOne($id);
        if ($model) {
            $view = "_popup" . ucfirst($model->tr_type) . "Settings";
            return $this->asJsonSuccess(
                            "Установки экспорта",
                            ["html" => $this->renderPartial(
                                $view, compact("model"))]);
        }
    }

    public function actionLetImportSettingsPopup($id) {
        $model = CedTransfert::findOne($id);
        if ($model) {
            $view = "_popup" . ucfirst($model->tr_type) . "Settings";
            return $this->asJsonSuccess(
                            "Установки импорта",
                            ["html" => $this->renderPartial(
                                $view, compact("model"))]);
        }
    }

    public function actionLetLinksPopup($id) {
        $module = $this->getModule();
        $view   = "_popupExportLinks";
        return $this->asJsonSuccess(
                        "Файлы экспорта",
                        $this->renderComponent($view, compact("id", "module")));
    }

    public function actionLetCompanyViewPopup($id) {
        $model = CedPartners::findOne($id);
        $view  = "_popupCompanyView";
        return $this->asJsonSuccess(
                        "Детали «{$model->title}»",
                        ["html" => $this->renderPartial(
                            $view, compact("model"))]);
    }

    public function actionLetPopupLog($id) {
        $models = \common\modules\crossposting\models\CedTransferLog::find()
                        ->where(["transfer_id" => $id])->orderBy("begin_at DESC");

        return $this->asJsonSuccess(
                        "Логи",
                        ["html" => $this->renderPartial(
                            "_log", compact("models"))]);
    }

    public function actionLetSequenceTransferPopup($id) {
        $model = CedTransfertSequence::findOne($id)->transfer;
        if ($model) {
            $view = "_popup" . ucfirst($model->tr_type) . "Settings";
            return $this->asJsonSuccess(
                            "Установки " . ($model->tr_type == CedTransfert::TYPE_EXPORT
                                ? "экспорта" : "импорта"),
                            ["html" => $this->renderPartial(
                                $view, compact("model"))]);
        }
    }

    public function actionTransferSequence() {
        set_time_limit(3600);
        \yii::$app->setModule("crosspost",
                              \common\modules\crossposting\Module::class);

        /* @var $module \common\modules\crossposting\Module */
        $module = \yii::$app->getModule("crosspost");
        $res    = $module->sequence->doItem();
        if ($res instanceof \common\modules\crossposting\models\CedTransfertSequence) {
            $outMess = sprintf("Воспроизведёна очередь трансфера %s",
                               $res->transfert_id);
            ServiceLog::Log(ServiceLog::TYPE_SERVICE,
                            \yii\helpers\VarDumper::dumpAsString([$outMess]));
            echo $outMess . PHP_EOL;
        }
    }

    public function actionTransferLog($id) {
        $models = \common\modules\crossposting\models\CedTransferLog::find()
                ->where(["transfer_id" => $id]);
        return $this->render("_log", compact("models"));
    }

    public function actionItemStat($id) {
        $filename = "{$this->getModule()->temporaryPath}/{$id}_stat";
        if (file_exists($filename)) {
            return $this->asJson(file_get_contents($filename));
        }
        return null;
    }

    public function actionLetSequenceItemChangeStartTimePopup($id) {
        $model = ctsw::findOne($id);
        if ($model) {
            return $this->asJsonSuccess("Установка времени запуска",
                                        $this->renderComponent("_popupSequenceItemStartTimeChange",
                                                               compact("model")));
        } else {
            return $this->asJsonError("Не удалось получить задание очереди",
                                      $model);
        }
    }

    public function actionSaveSequenceItemStartTime($item, $time) {
        $model = ctsw::findOne($item);
        if ($model) {
            /* @var $t \DateTime */
            if ($time == "now") {
                $model->setExtra(true);
                $mess = "Задание временно установлено в начало очереди";
                if ($model->save(false, ["extra_start"])) {
                    return $this->asJsonSuccess($mess, $model);
                }
            } else {
                $t                 = \DateTime::createFromFormat("H:i", $time);
                $d                 = new \DateTime("today");
                $model->begin_time = $t->getTimestamp() - $d->getTimestamp();
                $mess              = "Изменено время запуска задания";
                if ($model->save(false, ["begin_time"])) {
                    return $this->asJsonSuccess($mess, $model);
                }
            }
        } else {
            return $this->asJsonError("Не удалось получить задание очереди",
                                      $model);
        }
    }

    public function actionSummaryLog() {
        \common\modules\crossposting\models\CedTransferLog::RemoveOuttimeLogRecords();
        $models = \common\modules\crossposting\models\CedTransferLog::find()->orderBy("begin_at DESC");
        return $this->render("_logSummary", compact("models"));
    }

    public function actionRemoveTransfer() {
        $id = \yii::$app->request->post("id");
        if (!$id) {
            throw new CEDException("Не указан ID");
        }

        $model = CedTransfert::findOne($id);
        if (!$model) {
            throw new CEDException("Элемент не найден");
        }

        $sequence = $model->sequenceOne;
        if ($sequence) {
            $sequence->delete();
        }

        $logs = $model->log;
        if (!empty($logs)) {
            foreach ($logs as $log) {
                $log->delete();
            }
        }

        $model->delete();

        return $this->asJsonSuccess("", $model);
    }

}

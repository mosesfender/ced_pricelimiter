<?php

namespace common\modules\crossposting\components;

use \common\modules\crossposting\Module as cross;
use common\modules\crossposting\models\CedTransfert;
use common\modules\crossposting\CrossEvent;
use common\components\CEDException;
use common\models\CedObjects;
use common\models\CedObjectsFull;
use common\models\CedDictionaryItemsPartnerMap;
use common\modules\geobase\models\Linking;
use common\models\CedPartnersObjectMap;
use common\helpers\ArrayHelper as ah;
use yii\db\Expression as exp;
use common\models\CedObjectsDataQuery;
use common\models\dictionary\Helper;
use common\helpers\FileHelper;
use common\modules\crossposting\models\CedPartners as cp;

class Export extends BaseCross {

    /**
     * @var \common\modules\crossposting\models\ExportSettings
     */
    public $settings;

    /**
     * Термины словарей CED
     * @var array
     */
    public $dictionaryTerms;

    /**
     * Термины словарей для вендора
     * @var array
     */
    public $terms;

    /**
     * Карта геонеймов для вендора
     * @var array
     */
    public $geomap;

    /**
     * @var \common\models\CedObjects[]
     */
    public $objects;

    /**
     *
     * @var \common\models\CedObjectsQuery
     */
    public $objectsQuery;

    /**
     * Количество объектов для обработки в одном запросе
     * @var int
     */
    public $batchLimit = 1000;

    public function create($settings = null) {
        if (!$settings) {
            throw new CEDException("Для создания экспорта необходимы настройки ExportSettings.");
        }
        $this->settings = $settings;
        $this->partner  = $settings->getPartner();
        parent::create();

        if (!$this->vendor->hasExporter) {
            throw new CEDException("Вендор не обладает возможностью экспорта");
        }

        $this->transfer = CedTransfert::createExport(
                        \yii::$app->user->identity->id, $this->partner->id,
                        $this->settings);

        $this->transfer->getSettings(CedTransfert::TYPE_EXPORT)->transferID = $this->transfer->id;
        $this->transfer->getSettings(CedTransfert::TYPE_EXPORT)->outFiles[] = "{$this->transfer->id}.xml";
        \TLog::export($this->transfer->id, $this->module)::title("Новый экспорт")::info("Создан экспорт");

//        $ev         = new CrossEvent();
//        $ev->detail = &$this;
//        $this->module->trigger(cross::EV_EXPORT_CREATED, $ev);
    }

    public function prepareData() {
        $this->letDictionaryTerms();
        $this->letTerms();
        $this->letGeomap();
        $this->letObjects(true);
        \TLog::success(sprintf("Подготовлено к экспорту %d объектов",
                        $this->settings->itemsCount));
    }

    protected function letDictionaryTerms() {
        try {
            $this->dictionaryTerms = Helper::CedObjectTerms();
            \TLog::success("Получена карта свойств объектов.");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function letTerms() {
        try {
            $this->terms = ah::map(CedDictionaryItemsPartnerMap::findAll([
                                "partner_id" => $this->settings->vendorID]),
                            "ced_dict_item", "partner_dict_item");
            \TLog::success("Получена карта свойств вендора.");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function letGeomap() {
        try {
            $this->geomap = ah::map(Linking::findAll(["partner_id" => is_null($this->settings->vendor->geo_partner)
                                    ? $this->settings->vendor->id : $this->settings->vendor->geo_partner]),
                            "ced_geoname_id", "partner_geo_id");
            \TLog::success("Получена карта географии вендора.");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function letObjects($queryOnly = false) {
        try {
            $this->objectsQuery = (new CedObjectsDataQuery(["terms" => &$this->dictionaryTerms]))
                    ->leftJoin(["m" => CedPartnersObjectMap::tableName()],
                            ["co.id" => new exp("m.object_id")])
                    ->where(["AND",
                ["NOT", ["co.geonameid" => null]],
                ["co.status" => CedObjects::STATUS_PUBLISHED],
                    //["m.sub_partner_id" => $this->settings->exportCompaniesIds]
            ]);

            $ids   = $this->getSettings(CedTransfert::TYPE_EXPORT)->modedIds();
            $idsqq = ["OR"];
            if (count($ids[cp::CID_NO_IMPORTED])) {
                $idsqq[] = ["AND",
                    ["!=", "m.partner_id", $this->settings->vendorID],
                    ["m.sub_partner_id" => $ids[cp::CID_NO_IMPORTED]]
                ];
            }
            if (count($ids[cp::CID_IMPORTED])) {
                $idsqq[] = ["AND",
                    ["m.partner_id" => $this->settings->vendorID],
                    ["m.sub_partner_id" => $ids[cp::CID_IMPORTED]]
                ];
            }
            if (count($ids[cp::CID_ALL])) {
                $idsqq[] = ["AND",
                    ["m.sub_partner_id" => $ids[cp::CID_ALL]]
                ];
            }
            $this->objectsQuery->andWhere($idsqq);

            if ($this->settings->exportItemsLimit) {
                $this->objectsQuery->limit($this->settings->exportItemsLimit);
                $this->objectsQuery->offset($this->settings->exportItemsLimit * $this->settings->currentFile);
            }

            if ($queryOnly) {
                return $this->objectsQuery;
            }
            $this->objects = $qq->all();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getSettings($type = "export") {
        return parent::getSettings($type);
    }

    public function letParts() {
        if (!$this->dictionaryTerms) {
            $this->letDictionaryTerms();
        }
        $query = clone $this->letObjects(true);
        return (int) $query->select("COUNT(*)")->scalar();
    }

    /**
     * Генерирует http ссылки на файлы экспорта
     * @param type $id
     * @return \common\modules\crossposting\components\ExportFiles[]
     */
    public function generateFileLinks($id) {
        $ret      = [];
        $transfer = CedTransfert::findOne($id);
        foreach ($transfer->getSettings(CedTransfert::TYPE_EXPORT)->outFiles as $idx => $filename) {
            $_tmp            = new ExportFiles();
            $_tmp->zipFile   = $this->module->linkBasePath . $this->encodeFileLinkParans($id,
                            $idx, true);
            $_tmp->unZipFile = $this->module->linkBasePath . $this->encodeFileLinkParans($id,
                            $idx, false);
            $ret[$filename]  = $_tmp;
        }
        return $ret;
    }

    /**
     * Берёт и отправляет файл по указанным параметрам
     * @param string $params
     */
    public function sendFile($params) {
        $data     = $this->decodeFileLinkParans($params);
        $transfer = CedTransfert::findOne($data->id);
        if (!$transfer) {
            throw new \yii\base\UserException("Не найден экспорт {$data->id}");
        }
        $file = $this->module->getFullTransferFileName($transfer->getSettings(CedTransfert::TYPE_EXPORT)->outFiles[$data->idx]);
        $tmp  = $this->module->getFullTmpFileName($transfer->getSettings(CedTransfert::TYPE_EXPORT)->outFiles[$data->idx]);
        $zip  = FileHelper::replaceFileExtension($file, "zip");
        if (file_exists($zip)) {
            if (!$data->zip) {
                FileHelper::unzipFile($zip,
                        \yii::getAlias($this->module->temporaryPath));
                \yii::$app->response->sendFile($tmp);
            } else {
                \yii::$app->response->sendFile($zip);
            }
        } else {
            throw new \yii\base\UserException("Этого файла нет");
        }
    }

    protected function encodeFileLinkParans($exportID, $index, $zipFlag) {
        return base64_encode(json_encode(["id" => $exportID, "idx" => $index, "zip" => $zipFlag]));
    }

    protected function decodeFileLinkParans($paramStr) {
        return json_decode(base64_decode($paramStr));
    }

}

class ExportFiles {

    public $zipFile;
    public $unZipFile;

}

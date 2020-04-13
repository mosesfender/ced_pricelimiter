<?php

namespace common\modules\crossposting\models;

use yii\base\Model;
use common\models\CedPartners;
use common\helpers\ArrayHelper;

/**
 * @property \common\models\CedPartners[] $partners
 */
class ExportSettings extends TransferSettings {

    /**
     * Режимы выборки объектов
     */
    /* Объекты, которые не были импортированы */
    const CID_NO_IMPORTED = 0x1;
    /* Объекты, которые были импортированы */
    const CID_IMPORTED    = 0x2;
    /* Все объекты */
    const CID_ALL         = 0x4;

    /**
     * Идентификаторы компаний для экспорта
     * @var array
     */
    public $exportCompaniesIds = [];

    /**
     * Количество объектов
     * @var int
     */
    public $itemsCount = 0;

    /**
     * Лимит количества объектов, которые можно экспортировать в один файл
     * 
     * @var int
     * @default 0 Без ограничений
     */
    public $exportItemsLimit = 10000;

    public function rules() {
        return ArrayHelper::merge(parent::rules(),
                        [
                    [["exportItemsLimit", "itemsCount"], "integer"],
                    [["exportItemsLimit", "itemsCount"], "default", "value" => 0],
                    [["exportCompaniesIds"], "safe"]
        ]);
    }

    public function attributeLabels() {
        return ArrayHelper::merge(parent::attributeLabels(),
                        [
                    "exportItemsLimit" => "Лимит объектов в экспорте",
                    "itemsCount"       => "Объектов в экспорте",
                    "vendor.title"     => "Вендор экспорта",
                    "title"            => "Название",
        ]);
    }

    /**
     * @iheritdoc
     * @return \common\modules\crossposting\models\ExportSettings
     */
    public static function ModelFromVendor($vendorID) {
        $ret    = new ExportSettings([
            "vendorID" => $vendorID,
        ]);
        $vendor = CedPartners::findOne($vendorID);
        if ($vendor) {
            try {
                return $ret->fromJson($vendor->export_settings);
            } catch (\Exception $ex) {
                
            }
        }
        return $ret;
    }

    public static function ModelFromTransfer($transferID) {
        $ret      = new ExportSettings([
            "transferID" => $transferID,
        ]);
        $transfer = CedTransfert::findOne($transferID);
        if ($transfer) {
            try {
                return $ret->fromJson($transfer->settings);
            } catch (\Exception $ex) {
                
            }
        }
        return $ret;
    }

    public function getPartners() {
        return $this->getPartnersQuery()->all();
    }

    /**
     * @return \common\models\CedPartnersQuery
     */
    public function getPartnersQuery() {
        return CedPartners::find();
    }

    /**
     * Возвращаемое значение зависит от входных параметров.
     * 
     * Если $count is TRUE - возвращает количество published объектов, 
     *      указанных в self::$exportCompaniesIds
     * 
     * @param boolean $idsOnly
     * @param boolean $count
     * @param boolean $limitFrom
     * @return integer
     */
    public function letParts($idsOnly = false, $count = false, $limitFrom = null) {
        $partners = CedPartners::findAll(["id" => $this->exportCompaniesIds]);
        if ($count) {
            /* Счётчик объектов всех компаний из настроек */
            $ret = 0;
            foreach ($partners as $partner) {
                /* @var $partner \common\models\CedPartners */
                $ret += $partner->publishedObjectsCount;
            }
            return $ret;
        }
    }

    /**
     * Конвертирует ид компаний в соответствии с установленным viewMode.
     * Achtung! Сначала установи viewMode!
     */
    public function revertIds() {
        switch ($this->viewMode) {
            case self::LIST_MODE_SIMPLE:
                $this->convertIds(self::CID_ALL);
                break;
            case self::LIST_MODE_SEPARATED:
                $this->convertIds(self::CID_NO_IMPORTED | self::CID_IMPORTED);
                break;
        }
    }

    public function convertIds($toMode) {
        $_tmp = [];
        foreach ($this->exportCompaniesIds as &$id) {
            $_buff = explode(".", $id);
            if ($toMode & self::CID_ALL) {
                $_tmp[] = self::CID_ALL . "." . end($_buff);
            }
            if ($toMode & self::CID_NO_IMPORTED) {
                $_tmp[] = self::CID_NO_IMPORTED . "." . end($_buff);
            }
            if ($toMode & self::CID_IMPORTED) {
                $_tmp[] = self::CID_IMPORTED . "." . end($_buff);
            }
        }
        $_tmp = array_unique($_tmp);

        $this->exportCompaniesIds = $_tmp;
    }

    private $_modedIds;

    public function modedIds() {
        if (is_array($this->_modedIds)) {
            return $this->_modedIds;
        }
        $this->_modedIds = [self::CID_NO_IMPORTED => [], self::CID_IMPORTED => [], self::CID_ALL => []];
        foreach ($this->exportCompaniesIds as $id) {
            $_buff                                  = explode(".", $id);
            $this->_modedIds[(int) reset($_buff)][] = (int) end($_buff);
        }
        return $this->_modedIds;
    }

    /**
     * 
     * @param bool $sort
     * @return array
     * Array
     * (
     *     [Antarex] => Array
     *         (
     *             [title] => Antarex
     *             [mode] => 2
     *         )
     * 
     *     [Budapest Home] => Array
     *         (
     *             [title] => Budapest Home
     *             [mode] => 1
     *         )
     * 
     *     [Домире] => Array
     *         (
     *             [title] => Домире
     *             [mode] => 4
     *         )
     * 
     * )
     * 
     */
    public function utilCompanyWithExportMode($sort = true) {
        $values = function() {
            $ret = [];
            array_walk($this->_modedIds,
                    function(&$item) use (&$ret) {
                $ret = array_merge($ret, $item);
            });
            return array_unique($ret);
        };
        $ret = [];
        $this->modedIds();
        foreach ($values() as $cid) {
            $mode          = 0;
            $_ret          = ["mode" => $mode];
            $_ret["title"] = CedPartners::AllRecords()[$cid]->title;
            if (in_array($cid, $this->_modedIds[self::CID_IMPORTED])) {
                $_ret["mode"] = self::CID_IMPORTED;
            }
            if (in_array($cid, $this->_modedIds[self::CID_NO_IMPORTED])) {
                $_ret["mode"] = self::CID_NO_IMPORTED;
            }
            if (in_array($cid, $this->_modedIds[self::CID_IMPORTED]) && in_array($cid,
                            $this->_modedIds[self::CID_NO_IMPORTED])) {
                $_ret["mode"] = self::CID_ALL;
            }
            if (in_array($cid, $this->_modedIds[self::CID_ALL])) {
                $_ret["mode"] = self::CID_ALL;
            }
            $ret[$_ret["title"]] = $_ret;
        }
        if ($sort) {
            ksort($ret);
        }
        return $ret;
    }

}

<?php

namespace common\modules\crossposting\components\vendors;

use common\modules\crossposting\components\Export;
use common\modules\crossposting\components\CrossComponentIntf;
use common\modules\crossposting\models\xml\vendors\homesoverseas\Document as xml;
use common\helpers\CommonHelper;
use common\helpers\StringHelper;

class HomesoverseasExport extends Export implements CrossComponentIntf {

    const REF_TPL  = "https://domire.ru/estate/%s";
    const NT_CDATA = 0x1;

    private $_exportItemsLimit = 0;

    /**
     * @var \common\modules\crossposting\models\xml\vendors\homesoverseas\Document
     */
    protected $xml;

    public function work() {
        $this->prepareData();

        /* @var $result \common\modules\crossposting\models\xml\vendors\homesoverseas\Document */
        $result   = null;
        $filename = $this->module->getFullTransferFileName($this->module->sequence->model->filename);
        try {
            $this->xml = xml::create("{$this->module->vendorsNamespace}\\{$this->vendor->alias}",
                                     "", $filename, $result);
            $result->setRoot("root");
            $this->xml->setMeta();
            $result->setObjects("objects");
            //$result->setMeta();
            $offset    = 0;


            /* Актуализация счётчика объектов */
            $_countQuery                = clone $this->objectsQuery;
            //prer($_countQuery->createCommand()->rawSql, 0, 1);
            $this->settings->itemsCount = (int) $_countQuery->select("COUNT(*)")->scalar();
            $this->_exportItemsLimit    = $this->settings->exportItemsLimit;

            \TLog::Log()->data->getStatistic()->supposedNum = $this->settings->itemsCount;

            if ($this->settings->itemsCount < $this->_exportItemsLimit) {
                $this->_exportItemsLimit = $this->settings->itemsCount;
            }

            if ($this->batchLimit > $this->_exportItemsLimit) {
                $this->batchLimit = $this->_exportItemsLimit;
            }

            $globalCnt = 0;


            while ($offset < $this->_exportItemsLimit) {
                $this->objectsQuery->offset($offset);
                $this->objectsQuery->limit($this->batchLimit);
                \TLog::info("Получаем объекты экспорта следующие {$this->batchLimit} c {$offset} из {$this->_exportItemsLimit}.");
                $offset += $this->batchLimit;
                unset($objects);

                $objects = $this->objectsQuery->all();
                foreach ($objects as &$object) {
                    $objectNode = $result->doc->createElement("object", null);
                    try {
                        $this->addChildrens($objectNode,
                                            ["objectid", $object["id"]]);
                        $this->addChildrens($objectNode, ["type", "sale"]);
                        $this->addChildrens($objectNode,
                                            ["market", $this->_getPartnerTerm($object["propmarket"])]);

                        $this->addChildrens($objectNode,
                                            ["ref", $this->_getRef($object)]);

                        //$this->addChildrens($objectNode, ["responsible", $object["responsible"]]);
                        $this->addChildrens($objectNode,
                                            [
                            ["title", null],
                            ["ru", StringHelper::truncate($object["title"], 60,
                                                          ""), self::NT_CDATA],
                        ]);
                        if ($object["shortdescr"]) {
                            $this->addChildrens($objectNode,
                                                [
                                ["annotation", null],
                                ["ru", StringHelper::truncate($object["title"],
                                                              150, ""), self::NT_CDATA],
                                    ], false);
                        }
                        if ($object["descr"]) {
                            $this->addChildrens($objectNode,
                                                [
                                ["description", null],
                                ["ru", $object["descr"], self::NT_CDATA],
                            ]);
                        }
                        $this->_preparePrice($objectNode, $object);

                        try {
                            $this->addChildrens($objectNode,
                                                ["region", $this->geomap[$object["geonameid"]]]);
                        } catch (\Exception $exc) {
                            throw new \Exception("Не сопоставлен гео ID {$object["geonameid"]}");
                        }

                        $this->_preparePropertyType($objectNode, $object);

                        if ($object["area"]) {
                            $this->addChildrens($objectNode,
                                                ["size_house", $object["area"]],
                                                false);
                        }
                        if ($object["landarea"]) {
                            $this->addChildrens($objectNode,
                                                ["size_land", $object["landarea"]],
                                                false);
                        }
                        if ($object["bedroomnum"]) {
                            $this->addChildrens($objectNode,
                                                ["bedrooms", $object["bedroomnum"]],
                                                false);
                        }
                        if ($object["cnstryear"]) {
                            $this->addChildrens($objectNode,
                                                ["year", $object["cnstryear"]],
                                                false);
                        }
                        if ($object["noreadyyear"]) {
                            $this->addChildrens($objectNode,
                                                ["not_ready_year", $object["noreadyyear"]],
                                                false);
                        }
                        if ($object["noreadyquarter"]) {
                            $this->addChildrens($objectNode,
                                                ["not_ready_quarter", $object["noreadyquarter"]],
                                                false);
                        }
                        if ($object["floor"]) {
                            $this->addChildrens($objectNode,
                                                ["level", $object["floor"]],
                                                false);
                        }
                        if ($object["floors"]) {
                            $this->addChildrens($objectNode,
                                                ["levels", $object["floors"]],
                                                false);
                        }
                        if ($object["arpdist"]) {
                            $this->addChildrens($objectNode,
                                                ["distance_aero", $object["arpdist"]],
                                                false);
                        }
                        if ($object["seadist"]) {
                            $this->addChildrens($objectNode,
                                                ["distance_sea", $object["seadist"]],
                                                false);
                        }
                        if ($object["skiliftdist"]) {
                            $this->addChildrens($objectNode,
                                                ["distance_ski", $object["skiliftdist"]],
                                                false);
                        }
                        $this->_prepareOptions($objectNode, $object);
                        $this->_prepareMedia($objectNode, $object);


                        if ($object["lat"] && $object["lng"]) {
                            $this->addChildrens($objectNode,
                                                ["lat", $object["lat"]]);
                            $this->addChildrens($objectNode,
                                                ["lng", $object["lng"]]);
                            $this->addChildrens($objectNode,
                                                ["exact_coords", "Y"]);
                        } else {
                            $this->addChildrens($objectNode, ["lat", 0]);
                            $this->addChildrens($objectNode, ["lng", 0]);
                            $this->addChildrens($objectNode,
                                                ["exact_coords", "N"]);
                        }
//                if ($object->ytid) {
//                    $this->addChildrens($objectNode,
//                            ["ytid", $object->ytid]);
//                }
//                if ($object->fbid) {
//                    $this->addChildrens($objectNode,
//                            ["fbid", $object->fbid]);
//                }
                        $this->addChildrens($objectNode,
                                            ["developer", $object["builderobj"] ? "Y"
                                        : "N"]);
                    } catch (\Exception $ex) {
                        \TLog::error($ex->getMessage(), $ex->getFile(),
                                     $ex->getLine(), $object["id"]);
                        continue;
                    }
                    $result->getItemsRoot()->appendChild($objectNode);
                    \TLog::s_incCrossposted();
                    $globalCnt++;
                }
                //unset($objects);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }

        \TLog::info("Экспортировано {$globalCnt} объектов.");

        return $result->save();
    }

    /**
     * 
     * @param \DOMElement $parentNode
     * @param array $nodes
     * 
     * $nodes может быть:
     * 1. ["region", 645637] - добавляет в $parentNode узел region со значением 645637
     * 2. ["region", 645637, 0x1] - добавляет в $parentNode узел region, а в него CDATA со значением 645637
     * или массив этих массивов для создания дерева узлов.
     * 
     * @param bool $required Если true, то если значение пустое или null, то выдавать исключение,
     *          иначе просто не создавать узел.
     * 
     * @return \DOMNode
     * @throws Exception
     */
    protected function addChildrens(\DOMElement &$parentNode, array $nodes,
                                    $required = true) {
        $newNode = $parentNode;
        if (is_scalar($nodes[0])) {
            $nodes = [$nodes];
        }

        if (is_null(end($nodes)[1])) {
            if ($required) {
                throw new \Exception(sprintf("Значение узла %s пустое (%s)",
                                             end($nodes)[0], end($nodes)[1]));
            } else {
                return $required;
            }
        }
        foreach ($nodes as $node) {
            if (count($node) < 3) {
                $newNode = $newNode->appendChild(new \DOMElement($node[0],
                                                                 trim($node[1])));
            } else {
                if ($node[2] == self::NT_CDATA) {
                    $newNode = $newNode->appendChild(new \DOMElement($node[0],
                                                                     null));
                    $newNode->appendChild(new \DOMCdataSection(trim($node[1])));
                }
            }
        }
        return $newNode;
    }

    protected function _getRef(&$object) {
        return "https://domire.ru/estate/{$object["id"]}";
    }

    protected function _preparePropertyType(\DOMElement &$parentNode,
                                            array &$object) {
        $is = false;
        foreach (["propertyaptype", "propertyhousetype", "propertycommtype"] as $subproptype) {
            $is = $object[$subproptype];
        }
        if (!$is) {
            $is = $object["proptype"];
        }
        $this->addChildrens($parentNode,
                            ["realty_type", $this->_getPartnerTerm($is)]);
    }

    protected function _preparePrice(\DOMElement &$parentNode, array &$object) {
        $sale = $parentNode->appendChild(new \DOMElement("price", null));
        $sale->appendChild(new \DOMElement("sale", $object["price_orig"]));
        $parentNode->appendChild(new \DOMElement("currency",
                                                 $object["price_currency"]));
        $parentNode->appendChild(new \DOMElement("price_from",
                                                 $object["price_type"] == "ptmin"
                            ? "Y" : "N"));
    }

    protected function _prepareMedia(\DOMElement &$parentNode, array &$object) {
        $photos = $parentNode->appendChild(new \DOMElement("photos", null));
        $cnt    = 0;
        foreach (explode(",", $object["media"]) as $pic) {
            /* @var $pic \common\models\CedObjectsMedia */
            if ($cnt > 14) {
                break;
            }
            $photos->appendChild(new \DOMElement("photo",
                                                 "https://domire.ru/propmediastore/{$object["id"]}/{$pic}"));
            $cnt++;
        }
    }

    protected function _prepareOptions(\DOMElement &$parentNode, array &$object) {
        $attrs   = ["equipment", "finman", "floorplan", "infrastructure", "landparams"];
        $options = $parentNode->appendChild(new \DOMElement("options", null));
        $cnt     = 0;
        foreach ($attrs as $attr) {
            foreach (explode(",", $object[$attr]) as $opt) {
                if ($cnt > 63) {
                    break;
                }
                if (!empty(trim($opt))) {
                    $_tmp = $this->_getPartnerTerm($opt);
                    if (!empty($_tmp)) {
                        $options->appendChild(new \DOMElement("option",
                                                              $this->_getPartnerTerm($opt)));
                        $cnt++;
                    }
                }
            }
        }
        if ($cnt == 0) {
            $parentNode->removeChild($options);
        }
    }

    protected function _getPartnerTerm($cedTerm) {
        try {
            return $this->terms[$cedTerm];
        } catch (\Exception $ex) {
            return "";
        }
    }

}

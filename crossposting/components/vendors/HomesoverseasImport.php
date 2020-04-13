<?php

namespace common\modules\crossposting\components\vendors;

use common\modules\crossposting\components\Import;
use common\modules\crossposting\components\CrossComponentIntf;
use common\modules\crossposting\models\xml\vendors\homesoverseas\Document as xml;
use common\modules\crossposting\models\interfaces\ObjectItemIntf;
use common\models\CedObjects as co;
use common\components\Flags;
use common\models\CedWrongGeo;
use common\models\CedObjectsMedia as com;
use common\modules\pricelimiter\helpers\PriceLimit;
use common\models\CedObjectsUpdateLog as UpdateLog;
use common\helpers\CedHelper;

class HomesoverseasImport extends Import implements CrossComponentIntf {

    /**
     * @var \common\modules\crossposting\models\xml\vendors\homesoverseas\Document
     */
    protected $xml;
    private $_noLink;
    private $_cp;

    public function work() {
        $this->beginWork();

        for ($i = 0; $i < $this->xml->objectsLength; $i++) {
            $this->_noLink = null;
            $this->_cp     = null;
            /* @var $item \common\modules\crossposting\models\interfaces\ObjectItemIntf */
            try {

                $item = $this->xml->getItem($i);
                if ($item->type != self::ITEM_TYPE_SALE) {
                    \TLog::warning("Объект не для продажи", null, $item->id)::s_incNosale();
                    continue;
                }

                $_pom      = $this->findOwnerObject($item->id);
                $cedObject = null;
                if ($_pom) {
                    $cedObject = $_pom->propertyCO;
                }
                if (!$cedObject) {
                    $cedObject = new co([
                        "_flags" => 0
                    ]);
                }
                $cedObject->importID         = $this->transfer->id;
                $this->clearObjectPropAttributes($cedObject);
                $cedObject->partnerObjectMap = &$_pom;
                $cedObject->importID         = $this->transfer->id;
                $cedObject->subPartnerID     = $this->partner->id;
                $cedObject->partnerID        = $this->getVendorId();
                $cedObject->partnerObjectID  = $item->id;


                $cedObject->propmarket = $this->getVendorPropValue($item->propmarket);
                $cedObject->ref        = $item->ref;

                $this->prepareProptype($cedObject, $item);

                if ((!$cedObject->isNewRecord && ($this->settings->importFlags & Flags::COI_IMPORT_STATUS
                        || $this->settings->importFlags & Flags::COI_IMPORT_ALL)) || $cedObject->isNewRecord) {
                    $cedObject->status = $item->status;
                }

                if ((!$cedObject->isNewRecord && ($this->settings->importFlags & Flags::COI_IMPORT_PRICE
                        || $this->settings->importFlags & Flags::COI_IMPORT_ALL)) || $cedObject->isNewRecord) {
                    /* @var $price \common\modules\crossposting\models\xml\PriceType */
                    $price                     = $item->price;
                    $cedObject->price_orig     = $price->price_orig;
                    $cedObject->price_currency = $price->price_currency;
                    $cedObject->price_type     = $price->price_type;
                }

                if ((!$cedObject->isNewRecord && ($this->settings->importFlags & Flags::COI_IMPORT_TSD
                        || $this->settings->importFlags & Flags::COI_IMPORT_ALL)) || $cedObject->isNewRecord) {
                    $cedObject->title      = $item->title;
                    $cedObject->shortdescr = $item->shortDescr;
                    $cedObject->descr      = $item->descr;
                }

                if ((!$cedObject->isNewRecord && ($this->settings->importFlags & Flags::COI_IMPORT_PROPERTIES
                        || $this->settings->importFlags & Flags::COI_IMPORT_ALL)) || $cedObject->isNewRecord) {
                    $cedObject->area           = $item->area;
                    $cedObject->arpdist        = $item->arpdist;
                    $cedObject->bathroomsnum   = $item->bathroomsnum;
                    $cedObject->bedroomnum     = $item->bedroomnum;
                    $cedObject->builderobj     = $item->builderobj;
                    $cedObject->cnstrfine      = $item->cnstrfine;
                    $cedObject->cnstryear      = $item->cnstryear;
                    $cedObject->floor          = $item->floor;
                    $cedObject->floors         = $item->floors;
                    $cedObject->landarea       = $item->landarea;
                    $cedObject->noreadyquarter = $item->noreadyquarter;
                    $cedObject->noreadyyear    = $item->noreadyyear;
                    $cedObject->seadist        = $item->seadist;
                    $cedObject->skiliftdist    = $item->skiliftdist;

                    $this->prepareOptions($cedObject, $item);
                }
                $this->prepareGeoname($cedObject, $item);

                /* Резак бюджета */
                if ($cedObject->geonameid && $cedObject->price_orig > 0) {
                    $priceOrig = $cedObject->price_orig;
                    if ($cedObject->price_currency != "eur") {
                        $priceOrig = CedHelper::CurrencyExchange($cedObject->price_currency,
                                                                 "eur",
                                                                 $cedObject->price_orig);
                    }
                    if (PriceLimit::isLimit($cedObject->geonameid, $priceOrig,
                                            $cedObject->proptype)) {
                        if ($cedObject->isNewRecord) {
                            \TLog::warning("Объект по цене не проходит лимитатор.",
                                           null, $item->id);
                            \TLog::s_incPLLeft();
                            unset($cedObject);
                            continue;
                        } else {
                            if ($cedObject->status == "published") {
                                $cedObject->status = "saled";
                                \TLog::s_incPLSaled();
                                $this->_cp         = true;
                                UpdateLog::add($cedObject->id,
                                               [UpdateLog::REC_TYPE_STATUS => $cedObject->getOldAttribute("status")],
                                                                                                          [
                                    UpdateLog::REC_TYPE_STATUS => $cedObject->status],
                                                                                                          null,
                                                                                                          -4);
                            }
                        }
                    }
                }

                $res = $cedObject->save();

                if (!$res[0]) {
                    unset($cedObject);
                    continue;
                }

                if (!$this->_cp) {
                    \TLog::s_incCrossposted();
                }


                if (!is_null($this->_noLink)) {
                    $_nl = CedWrongGeo::find()->where(["obj_id" => $cedObject->id])
                            ->one();
                    if (is_null($_nl)) {
                        $_nl = new CedWrongGeo();
                    }
                    $_nl->obj_id        = $cedObject->id;
                    $_nl->partner_geoid = $this->_noLink["geoid"];
                    $_nl->descr         = $this->_noLink["message"];
                    $_nl->string_val    = $this->_noLink["strval"];
                    $_nl->save();
                    \TLog::warning($this->_noLink["message"], $cedObject->id,
                                   $cedObject->partnerObjectID);
                    $this->_noLink      = null;
                }

                $this->preparePhotos($cedObject, $item);

                unset($cedObject);
                //prer($res, 1, 1);
            } catch (\Exception $ex) {
                \TLog::error($ex->getMessage(), $ex->getFile(), $ex->getLine(),
                             @$cedObject->id, @$item->id);
            }
        }

        if ($this->settings->importFlags && Flags::COI_SALE_LEFT) {
            $this->saleUnavailables();
        }

        //CedWrongGeo::CleanWrongs();

        return $this->xml->filename;
    }

    public function preparePhotos(&$cedObject, &$item) {
        /* @var $cedObject co */
        /* @var $item \common\modules\crossposting\models\xml\vendors\homesoverseas\Item */
        $futureFiles = [];
        foreach ($item->photos as $photo) {
            $pi            = pathinfo($photo);
            $picDB         = com::letFileName($pi["filename"], "jpg");
            $futureFiles[] = $picDB;
            $issetPhoto    = com::find()->where(["AND",
                        ["filename" => $picDB],
                        ["object_id" => $cedObject->id],
                    ])->one();

            if (!$issetPhoto || ($issetPhoto && !$issetPhoto->fileExists)) {
                $this->uploadPhoto($photo, $cedObject->id);
            }
        }
        /* Убираем из БД объекта и директории файлы, которых нет в импорте */
        com::removeExcessMedia($futureFiles, $cedObject->id);
//        \TLog::success(implode(PHP_EOL,
//                               com::removeExcessMedia($futureFiles,
//                                                      $cedObject->id)),
//                                                      $cedObject->id, $item->id);
    }

    public function prepareOptions(&$cedObject, &$item) {
        /* @var $cedObject co */
        /* @var $item \common\modules\crossposting\models\xml\vendors\homesoverseas\Item */
        foreach ($item->options as $opt) {
            try {
                $_opt = $this->terms[$opt];
            } catch (\Exception $ex) {
                \TLog::error($ex->getMessage(), $ex->getFile(), $ex->getLine());
            }
            foreach ($this->dictionaryTerms as $dictKey => $dictVal) {
                if (in_array($_opt, $dictVal["terms"])) {
                    if ($dictVal["t"] & \common\models\dictionary\CedDictionaryItem::_FLAG_TERMIN_VALUE_IS_SET) {
                        $_old   = $cedObject->{$dictKey};
                        $_old[] = $_opt;
                        $_opt   = $_old;
                    }
                    $cedObject->{$dictKey} = $_opt;
                }
            }
        }
        return true;
    }

    public function prepareProptype(&$cedObject, &$item) {
        /* @var $cedObject co */
        /* @var $item \common\modules\crossposting\models\xml\vendors\homesoverseas\Item */
        $proptype = $this->proptypeTerms[$item->proptype];

        foreach (["proptype", "propertyaptype", "propertycommtype", "propertyhousetype"] as $pt) {
            if (in_array($proptype, $this->dictionaryTerms[$pt]["terms"])) {
                switch ($pt) {
                    case "propertyaptype":
                        $cedObject->proptype          = "apartments";
                        $cedObject->propertyaptype    = $proptype;
                        break;
                    case "propertycommtype":
                        $cedObject->proptype          = "propertycomm";
                        $cedObject->propertycommtype  = $proptype;
                        break;
                    case "propertyhousetype":
                        $cedObject->proptype          = "houses";
                        $cedObject->propertyhousetype = $proptype;
                        break;
                    case "proptype":
                        $cedObject->proptype          = $proptype;
                        break;
                    default:
                }
            }
        }
        return true;
    }

    public function prepareGeoname(&$cedObject, &$item) {
        /* @var $cedObject co */
        /* @var $item \common\modules\crossposting\models\xml\vendors\homesoverseas\Item */
        $str = [];

        $geonames = function() {
            if (!isset($GLOBALS["__vendorGeonames"])) {
                $GLOBALS["__vendorGeonames"] = \common\components\RegionsFull::find()
                        ->select(["id", "parentid", "title", "title_eng", "alias"])
                        ->indexBy("id")
                        ->asArray()
                        ->all();
            }
            return $GLOBALS["__vendorGeonames"];
        };

        $getgeostr = function($id) use (&$str, &$getgeostr, &$geonames) {
            $str[] = $geonames()[$id]["title"];
            if ((int) $geonames()[$id]["parentid"] !== 0) {
                $getgeostr((int) $geonames()[$id]["parentid"]);
            }
            return $str;
        };

        if (!$item->geoname) {
            \TLog::warning("Отсутствует география объекта", $cedObject->id,
                           $item->id);
            return true;
        }
//prer([key_exists($item->geoname, $this->geomap), $item->geoname, $this->geomap],1,1);
        if (key_exists($item->geoname, $this->geomap)) {
            /* Если есть сопоставленный geonameid, возвращаем его */
            $cedObject->undefined_geo = null;
            $cedObject->geonameid     = $this->geomap[$item->geoname];
            return true;
        } elseif (isset($geonames()[$item->geoname])) {
            $cedObject->geonameid     = null;
            $this->_noLink["geoid"]   = $item->geoname;
            $getgeostr($item->geoname);
            $this->_noLink["strval"]  = implode(" / ", $str);
            $this->_noLink["message"] = "Не сопоставленны геообъекты в CED и партнёра. {$this->_noLink["strval"]}";
            return true;
        } else {
            $cedObject->geonameid     = null;
            $this->_noLink["geoid"]   = $item->geoname;
            $this->_noLink["strval"]  = null;
            $this->_noLink["message"] = "Не сопоставленны геообъекты в CED и партнёра.";
            return true;
        }
    }

}

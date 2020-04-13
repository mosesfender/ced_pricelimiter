<?php

namespace common\modules\crossposting\models\xml\vendors\homesoverseas;

use common\modules\crossposting\models\xml\PriceType;

class Item extends \common\modules\crossposting\models\xml\Item implements \common\modules\crossposting\models\xml\CEDItemIntf {

    const BOOL_TRUE  = "Y";
    const BOOL_FALSE = "N";

    public function setId($val) {
        
    }

    public function getId() {
        return @$this->findNode("objectid")->nodeValue;
    }

    public function getPrice(): PriceType {
        $ret                 = new PriceType();
        $ret->price_orig     = @$this->findNode("price/sale")->nodeValue;
        $ret->price_currency = @$this->findNode("currency")->nodeValue;
        $ret->price_type     = @$this->findNode("price_from")->nodeValue == self::BOOL_TRUE
                    ? PriceType::PRICE_TYPE_MIN : PriceType::PRICE_TYPE_SINGLE;
        return $ret;
    }

    public function getGeoname() {
        return @$this->findNode("region")->nodeValue;
    }

    public function getStatus() {
        return "published";
    }

    public function getDescr() {
        return @$this->findNode("description/ru")->nodeValue;
    }

    public function getShortDescr() {
        return @$this->findNode("annotation/ru")->nodeValue;
    }

    public function getTitle() {
        return @$this->findNode("title/ru")->nodeValue;
    }

    public function getArea() {
        return @$this->findNode("size_house")->nodeValue;
    }

    public function getArpdist() {
        return @$this->findNode("distance_aero")->nodeValue;
    }

    public function getBathroomsnum() {
        return 0;
    }

    public function getBedroomnum() {
        return @$this->findNode("bedrooms")->nodeValue;
    }

    public function getBuilderobj() {
        return @$this->findNode("responsible")->nodeValue == self::BOOL_TRUE;
    }

    public function getCnstrfine() {
        return @$this->findNode("not_ready_year")->nodeValue || @$this->findNode("not_ready_quarter")->nodeValue;
    }

    public function getCnstryear() {
        return @$this->findNode("year")->nodeValue;
    }

    public function getFloor() {
        return @$this->findNode("level")->nodeValue;
    }

    public function getFloors() {
        return @$this->findNode("levels")->nodeValue;
    }

    public function getLandarea() {
        return @$this->findNode("size_land")->nodeValue;
    }

    public function getLat() {
        return @$this->findNode("lat")->nodeValue;
    }

    public function getLng() {
        return @$this->findNode("lng")->nodeValue;
    }

    public function getNoreadyquarter() {
        return @$this->findNode("not_ready_quarter")->nodeValue;
    }

    public function getNoreadyyear() {
        return @$this->findNode("not_ready_year")->nodeValue;
    }

    public function getPropmarket() {
        $ret = @$this->findNode("market")->nodeValue;
        if (!$ret) {
            $ret = "secondary";
        }
        return $ret;
    }

    public function getProptype() {
        return @$this->findNode("realty_type")->nodeValue;
    }

    public function getResponsible() {
        return @$this->findNode("responsible")->nodeValue;
    }

    public function getSeadist() {
        return @$this->findNode("distance_sea")->nodeValue;
    }

    public function getRef() {
        return @$this->findNode("ref")->nodeValue;
    }

    public function getSkiliftdist() {
        return @$this->findNode("distance_ski")->nodeValue;
    }

    public function getType() {
        return @$this->findNode("type")->nodeValue;
    }

    public function getOptions() {
        $ret   = [];
        $nodes = @$this->findNode("options")->childNodes;
        if ($nodes instanceof \DOMNodeList) {
            foreach ($nodes as $node) {
                /* @var $node \DOMElement */
                if ($node->nodeType == XML_ELEMENT_NODE) {
                    $ret[] = (int) trim($node->nodeValue);
                }
            }
        }
        return $ret;
    }

    public function getPhotos() {
        $ret   = [];
        $nodes = @$this->findNode("photos")->childNodes;
        if ($nodes instanceof \DOMNodeList) {
            foreach ($nodes as $node) {
                /* @var $node \DOMElement */
                if ($node->nodeType == XML_ELEMENT_NODE) {
                    $ret[] = trim($node->nodeValue);
                }
            }
        }
        return $ret;
    }

}

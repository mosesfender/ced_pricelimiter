<?php

namespace common\modules\crossposting\models\vendors;

use \common\modules\crossposting\models\XMLModel;
use common\modules\crossposting\models\interfaces\ObjectParamsIntf;
use common\modules\crossposting\models\interfaces\ObjectItemIntf;

/**
 * @property string $rootNodeName
 * @property int $objectsCount
 */
class homesoverseas extends XMLModel implements ObjectParamsIntf {

    public function getObjectsCount(): int {
        return $this->getObjectItems()->length;
    }

    public function getMeta(): \DOMElement {
        //return $this->root->
    }

    public function setMeta(): ObjectParamsIntf {
        $ret = $this->getRoot()->appendChild(new \DOMElement("meta", null));
        $ret->appendChild(new \DOMElement("version", 4));
        $ret->appendChild(new \DOMElement("timestamp", date("c")));
        return $this;
    }

    public function getObjectItemsRoot(): \DOMElement {
        return $this->getRoot()->getElementsByTagName($this->getObjectItemsRootNodeName())->item(0);
    }

    public function getObjectItemsRootNodeName(): string {
        return "objects";
    }

    public function setObjectItemsRoot(): \DOMElement {
        return $this->getRoot()->appendChild(new \DOMElement($this->getObjectItemsRootNodeName(),
                        null));
    }

    public function getObjectItems(): \DOMNodeList {
        return $this->getRoot()->getElementsByTagName($this->getObjectItemsNodeName());
    }

    public function getObjectItemsNodeName(): string {
        return "object";
    }

    public function getRootNodeName(): string {
        return "root";
    }

    public function getObjectItem($idx): \DOMElement {
        return $this->getObjectItems()->item($idx);
    }

    public function getObjectItemNodeName(): string {
        return "object";
    }

    public function setObjectItem(): \DOMElement {
        return $this->getObjectItemsRoot()->appendChild(new \DOMElement($this->getObjectItemNodeName(),
                        null));
    }

}

class ObjectItem implements ObjectItemIntf {

    public function getArea() {
        
    }

    public function getArpdist() {
        
    }

    public function getDescr() {
        
    }

    public function getId() {
        
    }

    public function getMarket() {
        
    }

    public function getObjectItem($idx): \DOMElement {
        
    }

    public function getObjectItemNodeName(): string {
        
    }

    public function getPrice() {
        
    }

    public function getRef() {
        
    }

    public function getShortDescr() {
        
    }

    public function getTitle() {
        
    }

    public function getType() {
        
    }

    public function setArea() {
        
    }

    public function setArpdist() {
        
    }

    public function setObjectItem(): \DOMElement {
        
    }

}

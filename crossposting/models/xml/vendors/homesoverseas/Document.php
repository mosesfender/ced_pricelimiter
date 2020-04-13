<?php

namespace common\modules\crossposting\models\xml\vendors\homesoverseas;

class Document extends \common\modules\crossposting\models\xml\Document implements \common\modules\crossposting\models\xml\DocumentIntf {

    protected $_itemClass = \common\modules\crossposting\models\xml\vendors\homesoverseas\Item::class;

    public function getObjects(): \DOMNodeList {
        $path = new \DOMXPath($this->_doc);
        return $path->query("*/object");
    }

    public function setRoot($nodeName) {
        parent::setRoot("root");
    }

    public function getItemsRoot() {
        $path = new \DOMXPath($this->_doc);
        return $path->query("//objects")->item(0);
    }

    public function getIds(): array {
        $ret  = [];
        $path = new \DOMXPath($this->_doc);
        foreach ($path->query("*/object/objectid") as $node) {
            $ret[] = $node->nodeValue;
        }
        return $ret;
    }

    public function setMeta() {
        $meta = $this->getRoot()->appendChild(new \DOMElement("meta", null));
        $meta->appendChild(new \DOMElement("version", 4));
        $meta->appendChild(new \DOMElement("timestamp", date("c")));
    }

}

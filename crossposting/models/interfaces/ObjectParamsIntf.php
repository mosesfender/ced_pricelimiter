<?php

namespace common\modules\crossposting\models\interfaces;

/**
 * @property \DOMNodeList $objectItems
 */
interface ObjectParamsIntf {

    public function getObjectsCount(): int;

    public function getRoot(): \DOMElement;

    public function setRoot($tagName): ObjectParamsIntf;

    public function getRootNodeName(): string;

    public function getMeta(): \DOMElement;

    public function setMeta(): ObjectParamsIntf;

    public function getObjectItemsRoot(): \DOMElement;

    public function getObjectItemsRootNodeName(): string;

    public function setObjectItemsRoot(): \DOMElement;

    public function getObjectItems(): \DOMNodeList;

    public function getObjectItemsNodeName(): string;
}

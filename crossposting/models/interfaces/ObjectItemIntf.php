<?php

namespace common\modules\crossposting\models\interfaces;

interface ObjectItemIntf {

    public function getObjectItem($idx): \DOMElement;

    public function getObjectItemNodeName(): string;

    public function setObjectItem(): \DOMElement;

    public function getId();

    public function getType();

    public function getMarket();

    public function getRef();

    public function getTitle();

    public function getShortDescr();

    public function getDescr();

    public function getPrice();

    public function getArea();

    public function setArea();

    public function getArpdist();

    public function setArpdist();
}

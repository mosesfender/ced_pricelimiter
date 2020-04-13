<?php

namespace common\modules\crossposting\models\xml;

interface DocumentIntf {

    /**
     * Возвращает коллекцию объектов
     */
    public function getObjects(): \DOMNodeList;

    public function getObjectsLength(): int;

    public function getIds(): array;
}

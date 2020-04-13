<?php

namespace common\modules\crossposting\models\xml;

/**
 * @property \DOMDocument   $doc        DOM Документ   
 * @property \DOMElement    $root       Корневой узел
 * @property \DOMNodeList   $objects    Коллекция объектов
 * @property int            $objectsLength    Количество объектов
 * @property string         $filename
 */
class Document extends \yii\base\BaseObject implements DocumentIntf {

    /**
     * @var \common\modules\crossposting\Module
     */
    static public $module;
    protected $_itemClass = \common\modules\crossposting\models\xml\Item::class;
    public $encoding      = "utf-8";
    public $version       = "1.0";

    /**
     * @var \DOMDocument
     */
    protected $_doc;

    /**
     * Текст XML
     * @var string
     */
    protected $_xml = "";

    /**
     * Имя файла
     * @var string
     */
    protected $_filename = "";

    public static function create($vendor, $text = null, $filename = null,
            &$result = null) {
        $class = static::getVendorClass($vendor);
        if (static::issetVendor($class)) {
            $result                     = new $class([
                "xml"      => $text,
                "filename" => $filename,
            ]);
            $result->_doc->formatOutput = true;
        }
        return $result;
    }

    static function issetVendor($namespace) {
        $ret = \yii::getAlias("@" .
                        str_replace("\\", "/", "{$namespace}.php"));
        return file_exists($ret);
    }

    static function getVendorClass($namespace) {
        return "{$namespace}\\Document";
    }

    protected function createDocument() {
        $this->_doc = new \DOMDocument($this->version, $this->encoding);
    }

    public function setXml($text) {
        $this->_xml = $text;
        $this->createDocument();
    }

    public function setFilename($filename) {
        $this->_filename = \common\helpers\FileHelper::normalizePath($filename);
        if (file_exists($this->_filename)) {
            $this->createDocument();
        }
    }

    public function getFilename() {
        return $this->_filename;
    }

    public function loadFile() {
        if ($this->_filename && $this->_doc) {
            $this->_doc->load($this->_filename);
        }
    }

    public function save() {
        try {
            if (file_exists($this->_filename)) {
                unlink($this->_filename);
            }

            if ($this->_doc->save($this->_filename)) {
                \TLog::success("Записан файл {$this->_filename}");
                return $this->_filename;
            }
            return false;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function setDoc($val) {
        $this->_doc = $val;
        return $this;
    }

    public function getDoc(): \DOMDocument {
        return $this->_doc;
    }

    public function getRoot(): \DOMElement {
        return $this->_doc->firstChild;
    }

    public function setRoot($nodeName) {
        $this->_doc->appendChild($this->_doc->createElement($nodeName));
    }

    public function getObjects(): \DOMNodeList {
        
    }

    public function setObjects($nodeName) {
        return $this->getRoot()->appendChild($this->_doc->createElement($nodeName));
    }

    public function getObjectsLength(): int {
        return $this->getObjects()->length;
    }

    public function getItemsRoot() {
        
    }

    /**
     * @param int $idx
     * @return \common\modules\crossposting\models\xml\Item
     */
    public function getItem($idx) {
        return new $this->_itemClass([
            "doc"     => $this->_doc,
            "element" => $this->getObjects()->item($idx)
        ]);
    }

    public function getIds(): array {
        
    }

}

class PriceType {

    const PRICE_TYPE_SINGLE = "ptsingle";
    const PRICE_TYPE_MIN    = "ptmin";

    public $price_orig;
    public $price_currency;
    public $price_type;

}

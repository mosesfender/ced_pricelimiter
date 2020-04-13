<?php

namespace common\modules\crossposting\models;

/**
 * @property string $xml        Текст документа
 * @property string $filename   Имя файла
 * 
 * @property \DOMDocument $document readonly
 * @property \DOMElement $root
 */
class XMLModel extends \yii\base\Model {

    /**
     *
     * @var \common\modules\crossposting\Module
     */
    static public $module;
    public $encoding = "utf-8";
    public $version  = "1.0";

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

    /**
     * Документ
     * @var \DOMDocument
     */
    protected $_doc;

    /**
     * Контекстный элемент
     * @var \DOMElement
     */
    protected $_context;

    /**
     * Имя модели 
     * @var string
     */
    protected $_vendor = "";

    public function init() {
        parent::init();
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

    public function setVendor($vendor) {
        $this->_vendor = $vendor;
    }

    public function loadFile() {
        if ($this->_filename && $this->_doc) {
            $this->_doc->load($this->_filename);
        }
    }

    /**
     * 
     * @param type $vendor
     * @param type $text
     * @param type $filename
     * @param \common\modules\crossposting\models\XMLModel $result
     * @return \common\modules\crossposting\models\XMLModel
     */
    public static function create($vendor, $text = null, $filename = null,
            &$result = null) {
        if (static::issetVendor($vendor)) {
            $class  = static::getVendorClass($vendor);
            $result = new $class([
                "vendor"   => $vendor,
                "xml"      => $text,
                "filename" => $filename,
            ]);

            $result->_doc->formatOutput = true;
        }
        return $result;
    }

    protected function createDocument() {
        $this->_doc = new \DOMDocument($this->version, $this->encoding);
//        if (!empty($this->_xml)) {
//            $this->_doc->loadXML($this->_xml);
//        }
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

    public static function Node() {
        
    }

    static function issetVendor($vendor) {
        $ret = \yii::getAlias("@" .
                        str_replace("\\", "/", static::getVendorClass($vendor)) . ".php");
        return file_exists($ret);
    }

    static function getVendorClass($vendor) {
        return self::$module->vendorsNamespace . "\\" . $vendor;
    }

    public function getDocument() {
        return $this->_doc;
    }

    public function setDocument(&$val) {
        $this->_doc = $val;
    }

    public function getContext() {
        return $this->_context;
    }

    public function setContext(&$val) {
        $this->_context = $val;
    }

    public function getRoot(): \DOMElement {
        return $this->_doc->firstChild;
    }

    public function setRoot($tagName): interfaces\ObjectParamsIntf {
        $this->_doc->appendChild($this->_doc->createElement($tagName));
        return $this;
    }

    /* Abstract methods */

    public function getObjectId(){}
}

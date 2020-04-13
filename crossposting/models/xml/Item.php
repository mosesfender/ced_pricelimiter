<?php

namespace common\modules\crossposting\models\xml;

interface ItemIntf {
    
}

interface CEDItemIntf {

    public function getId();

    public function getStatus();

    public function getPrice(): PriceType;

    public function getTitle();

    public function getShortDescr();

    public function getDescr();

    public function getArea();

    public function getArpdist();

    public function getBathroomsnum();

    public function getBedroomnum();

    public function getBuilderobj();

    public function getCnstrfine();

    public function getCnstryear();

    public function getFloor();

    public function getFloors();

    public function getLandarea();

    public function getLat();

    public function getLng();

    public function getNoreadyquarter();

    public function getNoreadyyear();

    public function getPropmarket();

    public function getProptype();

    public function getSeadist();

    public function getSkiliftdist();

    public function getResponsible();

    public function getRef();

    public function getType();

    public function getOptions();

    public function getPhotos();

    public function getGeoname();
}

/**
 * @property int $area
 * @property int $arpdist
 * @property int $bathroomsnum
 * @property int $bedroomnum
 * @property int $builderobj
 * @property int $cnstrfine
 * @property int $cnstryear
 * @property string $descr
 * @property int $floor
 * @property int $floors
 * @property string $id
 * @property int $landarea
 * @property string $lat
 * @property string $lng
 * @property int $noreadyquarter
 * @property int $noreadyyear
 * @property array $options
 * @property array $photos
 * @property \common\modules\crossposting\models\xml\PriceType $price
 * @property string $propmarket
 * @property string $proptype
 * @property string $ref
 * @property string $responsible
 * @property string $seadist
 * @property string $shortDescr
 * @property string $skiliftdist
 * @property string $status
 * @property string $title
 * @property string $type
 * @property string $geoname
 */
class Item extends \yii\base\BaseObject
        implements \common\modules\crossposting\models\xml\ItemIntf {

    /**
     * @var \DOMDocument
     */
    public $_doc;

    /**
     * @var \DOMElement
     */
    protected $_element;

    public function setDoc(\DOMDocument &$doc) {
        $this->_doc = $doc;
    }

    public function setElement(\DOMElement &$element) {
        $this->_element = $element;
    }

    /**
     * Ищет в DOM объекта узел с именем $name и возвращает его в случае успеха, иначе null.
     * @param string $name
     * @return \DOMElement
     */
    public function findNode($name) {
        $path = new \DOMXPath($this->_doc);
        $res  = $path->query($name, $this->_element);
        if ($res->length) {
            return $res->item(0);
        }
    }

}

<?php

namespace common\modules\pricelimiter\widgets;

use common\widgets\mfGridView\MFGridView as GridView;
use common\modules\pricelimiter\models\Geo;
use common\modules\pricelimiter\models\PropertyTypes;
use common\helpers\Html;

class LimiterGridView extends GridView {

    /**
     * @var \common\modules\pricelimiter\models\GeoQuery
     */
    public $geoQuery;

    /**
     * @var \common\modules\pricelimiter\models\PropertyTypesQuery
     */
    public $proptypesQuery;

    /**
     * @var array
     */
    public $values       = [];
    public $layout       = "{items}";
    public $tableOptions = ['class' => 'table table-bordered'];
    public $jsGridClass  = "CED.TPriceLimiter";

    /**
     * @var \common\modules\pricelimiter\models\PropertyTypes
     */
    private $_types;
    public $searchForm = [
        GridView::SRC_FLD_ID               => "pricelimiter_search",
        GridView::SRC_FLD_SUBMIT_ON_CHANGE => true,
    ];

    public function init() {

        parent::init();

        if ($this->proptypesQuery === null) {
            throw new InvalidConfigException('The "proptypesQuery" property must be set.');
        }
        $this->getView()->registerAssetBundle(LimiterGridViewAsset::class);
    }

    protected function initColumns() {
        $this->_types = $this->proptypesQuery->all();

        $this->columns[0] = \yii::createObject([
                    "class"     => GeoDataCell::class,
                    "grid"      => $this,
                    "attribute" => "geonameid",
                    "value"     => "label",
                    "label"     => ""
        ]);

        foreach ($this->_types as $i => $type) {
            $this->columns[] = \yii::createObject([
                        "class" => ProptypeDataCell::class,
                        "grid"  => $this,
                        "label" => $type->label,
                        "value" => function() {
                            return "";
                        },
                        "headerOptions"  => ["data-key" => $type->id]
            ]);
        }
    }

    public function renderTableRow($model, $key, $index) {
        $cells = [];
        /* @var $column Column */
        foreach ($this->columns as $idx => $column) {
            if ($column instanceof GeoDataCell) {
                $cells[] = $column->renderDataCell($model, $key, $index);
            } else {
                $cells[] = $column->renderDataCell($this->_types[$idx - 1],
                        $key, $index);
            }
        }
        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index,
                    $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;

        return Html::tag('tr', implode('', $cells), $options);
    }

    
    
}

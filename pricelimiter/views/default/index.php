<?php

use common\modules\pricelimiter\widgets\LimiterGridView;
use common\modules\pricelimiter\models\Geo;
use common\modules\pricelimiter\models\PropertyTypes;
use yii\helpers\Url;

/* @var $geoProvider \yii\db\ActiveDataProvider */
/* @var $proptypesQuery \common\modules\pricelimiter\models\PropertyTypesQuery */
/* @var $values array */
?>
<?php

echo LimiterGridView::widget([
    "dataProvider"   => $geoProvider,
    "proptypesQuery" => $proptypesQuery,
    "values"         => $values,
    "jsGridParams"   => [
        "saveUrl" => Url::toRoute("default/store-value"),
    ]
]);
?>

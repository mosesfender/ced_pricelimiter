<?php

use common\helpers\Html;
use common\modules\yeesoft\core\widgets\ActiveForm;
use common\modules\pricelimiter\models\GeoSearch;

/* @var $model \common\modules\pricelimiter\models\GeoSearch */
?>

<?php

$form = ActiveForm::begin([
            "id"             => "pricelimiter_search",
            "validateOnBlur" => false,
            "options"        => [
                "data-pjax" => 1
            ],
        ]);
?>

<?php

echo $form->field($model, "paramKeys")
        ->checkboxList(GeoSearch::filterParams(),
                [
            "class"       => "buttoned inline",
            "unsellect"   => null,
            "itemOptions" => [
                "wrapOptions"  => [
                    "class" => "checkbox"
                ],
                "addonOptions" => [
                    "class" => "back"
                ]
            ]
        ])->label(false);
?>

<?php $form->end(); ?>


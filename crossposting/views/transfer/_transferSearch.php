<?php

use common\modules\yeesoft\core\widgets\ActiveForm;
use common\modules\crossposting\models\CedTransfert;

//prer($model,1,1);
?>

<div class="search-box">
    <?php
    $form = ActiveForm::begin([
                "action"  => ["index"],
                "method"  => "get",
                "id"      => "transfer_search_form",
                "options" => [
                    "data-pjax" => 1
                ],
    ]);
    ?>

    <div class="col-md-12">
        <?=
                $form->field($model, "tr_type")
                ->checkboxList(CedTransfert::TransferTypes(),
                               [
                    "class"        => "buttoned inline",
                    "unselect" => null,
                    "itemOptions"  => [
                        "wrapOptions"  => [
                            "class" => "checkbox"
                        ],
                        "addonOptions" => [
                            "class" => "back"
                        ]
                    ]
                ])
                ->label(false);
        ?>
    </div>

<?php $form->end(); ?>
</div>
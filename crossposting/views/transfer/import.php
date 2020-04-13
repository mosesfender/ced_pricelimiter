<?php

use common\modules\crossposting\models\ImportSettings;

/* @var $component \common\modules\crossposting\components\Import */

$this->title                   = $component->settings->stage == ImportSettings::STAGE_EDIT
            ? "Редактирование импорта" : "Создание импорта";
$this->params['breadcrumbs'][] = ['label' => "Кросспостинг", 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php

echo $this->render("../forms/_form_import_settings", ["component" => $component]);
?>
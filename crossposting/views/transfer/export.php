<?php

/* @var $model \common\modules\crossposting\models\ExportSettings */

$this->title                   = "Создание экспорта";
$this->params['breadcrumbs'][] = ['label' => "Кросспостинг", 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render("../forms/_form_export_settings", ["settings" => $model]);
?>
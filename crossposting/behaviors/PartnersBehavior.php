<?php

namespace common\modules\crossposting\behaviors;

use common\modules\crossposting\models\ExportSettings;

/**
 * @property \common\models\CedPartners $owner
 */
class PartnersBehavior extends \yii\base\Behavior {

    public function getExportSettings() {
        return unserialize($this->owner->export_settings,
                ["allowed_classes" => ExportSettings::class]);
    }

    public function setExportSettings(ExportSettings $val) {
        if ($val instanceof ExportSettings) {
            $this->owner->export_settings = serialize($val);
        }
    }

}

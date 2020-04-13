<?php

namespace common\modules\crossposting\models;

use common\modules\crossposting\models\CedTransfert;

/**
 * @property int $importsNum
 * @property CedTransfert[] $imports
 */
class CedPartners extends \common\models\CedPartners {

    public function getImportsNum() {
        return $this->imports->count();
    }

    /**
     * @return CedTransfert[]
     */
    public function getImports() {
        return $this->hasMany(CedTransfert::class, ["partner_id" => "id"])
                        ->onCondition(["tr_type" => "import"]);
    }

}

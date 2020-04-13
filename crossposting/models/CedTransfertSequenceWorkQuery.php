<?php

namespace common\modules\crossposting\models;

use yii\db\Expression;

class CedTransfertSequenceWorkQuery extends \yii\db\ActiveQuery {

    public function init() {
        parent::init();
        $this->select(["*",
            "bt"   => "(@bt := UNIX_TIMESTAMP(DATE(FROM_UNIXTIME(IF(begin_at IS NULL, created_at, begin_at)))) + shedule_interval + begin_time)",
            "t"    => "IF(begin_at IS NULL, created_at + shedule_interval, begin_at + shedule_interval)",
            "diff" => "@bt - UNIX_TIMESTAMP()",
            new Expression("'' AS pcaption"),
            new Expression("'' AS __stat"),
        ]);
        $this->orderBy("extra_start DESC, diff ASC");
    }

    /**
     * @inheritdoc
     * @return CedTransfertSequenceWork[]|array
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CedTransfertSequenceWork|array|null
     */
    public function one($db = null) {
        return parent::one($db);
    }

}

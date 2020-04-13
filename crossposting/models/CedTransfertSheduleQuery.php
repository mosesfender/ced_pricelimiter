<?php

namespace common\modules\crossposting\models;

/**
 * This is the ActiveQuery class for [[CedTransfertShedule]].
 *
 * @see CedTransfertShedule
 */
class CedTransfertSheduleQuery extends \yii\db\ActiveQuery {
    /* public function active()
      {
      return $this->andWhere('[[status]]=1');
      } */

    /**
     * {@inheritdoc}
     * @return CedTransfertShedule[]|array
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CedTransfertShedule|array|null
     */
    public function one($db = null) {
        return parent::one($db);
    }

}

<?php

namespace common\modules\crossposting\models;

/**
 * This is the ActiveQuery class for [[CedTransfert]].
 *
 * @see CedTransfert
 */
class CedTransfertQuery extends \yii\db\ActiveQuery {
    /* public function active()
      {
      return $this->andWhere('[[status]]=1');
      } */

    /**
     * {@inheritdoc}
     * @return CedTransfert[]|array
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CedTransfert|array|null
     */
    public function one($db = null) {
        return parent::one($db);
    }

}

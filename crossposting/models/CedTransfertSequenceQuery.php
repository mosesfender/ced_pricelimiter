<?php

namespace common\modules\crossposting\models;

/**
 * This is the ActiveQuery class for [[CedTransfertSequence]].
 *
 * @see CedTransfertSequence
 */
class CedTransfertSequenceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CedTransfertSequence[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CedTransfertSequence|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

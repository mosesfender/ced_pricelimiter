<?php

namespace common\modules\crossposting\models;

/**
 * This is the ActiveQuery class for [[CedTransferLog]].
 *
 * @see CedTransferLog
 */
class CedTransferLogQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CedTransferLog[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CedTransferLog|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

<?php

namespace common\modules\pricelimiter\models;

use Yii;

/**
 * This is the model class for table "gn_geonames_hash".
 *
 * @property int $id
 * @property int $geonameid
 * @property string $hash_type
 * @property string $hash
 * @property string $bhash
 */
class GnGeonamesHash extends \common\modules\geobase\models\GnGeonamesHash {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'gn_geonames_hash';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['geonameid'], 'integer'],
            [['hash_type'], 'string'],
            [['hash', 'bhash'], 'string', 'max' => 255],
            [['geonameid', 'hash_type'], 'unique', 'targetAttribute' => ['geonameid', 'hash_type']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id'        => 'ID',
            'geonameid' => 'Geonameid',
            'hash_type' => 'Hash Type',
            'hash'      => 'Hash',
            'bhash'     => 'Bhash',
        ];
    }

    /**
     * {@inheritdoc}
     * @return GnGeonamesHashQuery the active query used by this AR class.
     */
    public static function find() {
        return new GnGeonamesHashQuery(get_called_class());
    }

}

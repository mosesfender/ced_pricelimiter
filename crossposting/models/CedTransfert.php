<?php

namespace common\modules\crossposting\models;

use Yii;
use common\modules\crossposting\models\CedTransfertSequence;
use common\modules\crossposting\models\CedTransfertShedule;

/**
 * This is the model class for table "{{%ced_transfert}}".
 *
 * @property string     $id
 * @property string     $tr_type
 * @property int        $partner_id
 * @property int        $user_id
 * @property int        $created_at
 * @property int        $finished_at
 * @property string     $filename
 * @property int        $_flags
 * 
 * @property \common\modules\crossposting\models\TransferSettings       $settings
 * @property \common\modules\crossposting\models\CedTransfertSequence[]   $sequence
 * @property \common\modules\crossposting\models\CedTransfertSequence   $sequenceOne
 * @property \common\modules\crossposting\models\CedTransfertShedule    $shedule
 * @property \common\modules\crossposting\models\CedTransfertLog[]        $log
 * @property \common\models\CedPartners     $partner
 * @property \backend\models\FormUser       $user
 */
class CedTransfert extends \yii\db\ActiveRecord {

    const TYPE_IMPORT = "import";
    const TYPE_EXPORT = "export";
    const NAME_TPL    = "YmdHis";

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%ced_transfert}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['tr_type', 'filename'], 'string'],
            [['user_id', 'partner_id', 'created_at', 'finished_at', '_flags'], 'integer'],
            ['_flags', 'default', 'value' => 0],
            [['id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id'            => Yii::t('ced', 'ID'),
            'tr_type'       => Yii::t('ced', 'Tr Type'),
            'user_id'       => Yii::t('ced', 'User ID'),
            'created_at'    => Yii::t('ced', 'Создан'),
            'finished_at'   => Yii::t('ced', 'Последний запуск'),
            'user.fullname' => Yii::t('ced', 'Сотрудник'),
            'filename'      => Yii::t('ced', 'Файлы'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return CedTransfertQuery the active query used by this AR class.
     */
    public static function find() {
        return new CedTransfertQuery(get_called_class());
    }

    /**
     * @param integer $userID
     * @param integer $partnerID
     * @param \common\modules\crossposting\models\TransferSettings $settings
     * @return \common\modules\crossposting\models\CedTransfert
     */
    public static function createImport($userID, $partnerID, $settings = null) {
        $suff          = date(self::NAME_TPL);
        $ret           = new CedTransfert([
            "id"         => "imp_{$suff}",
            "tr_type"    => self::TYPE_IMPORT,
            "user_id"    => $userID,
            "partner_id" => $partnerID,
            "settings"   => $settings->toJSON()
        ]);
        $partner       = $ret->partner;
        $ret->filename = "{$partner->prefix}_{$suff}.xml";
        $ret->save();
        return $ret;
    }

    /**
     * @param integer $userID
     * @param integer $partnerID
     * @param \common\modules\crossposting\models\TransferSettings $settings
     * @return \common\modules\crossposting\models\CedTransfert
     */
    public static function createExport($userID, $partnerID, $settings = null) {
        $suff                      = date(self::NAME_TPL);
        $ret                       = new CedTransfert([
            "id"         => "exp_{$suff}",
            "tr_type"    => self::TYPE_EXPORT,
            "user_id"    => $userID,
            "partner_id" => $partnerID,
            "settings"   => $settings,
        ]);
        $partner                   = $ret->partner;
        $ret->settings->transferID = $ret->id;
        $ret->filename             = "{$partner->prefix}_{$suff}.xml";
        $ret->save();
        return $ret;
    }

    public static function TransferTypes() {
        return [
            self::TYPE_EXPORT => "Экспорт",
            self::TYPE_IMPORT => "Импорт",
        ];
    }

    public static function loadImport($name) {
        return self::find()->where(["id" => $name])->one();
    }

    /**
     * 
     * @param  int $userID
     * @return CedTransfertQuery
     */
    public static function findByUser($userID) {
        return self::find()->where(["user_id" => $userID]);
    }

    /**
     * 
     * @param  int $partnerID
     * @return CedTransfertQuery
     */
    public static function findByPartner($partnerID) {
        return self::find()->where(["partner_id" => $partnerID]);
    }

    /**
     * @return \common\models\CedPartners
     */
    public function getPartner() {
        return $this->hasOne(\common\models\CedPartners::class,
                             ["id" => "partner_id"]);
    }

    /**
     * 
     * @param string $type 
     * @return \common\modules\crossposting\models\TransferSettings
     */
    public function getSettings($type) {
        if (!($this->settings instanceof \common\modules\crossposting\models\TransferSettings)) {
            $_tmp           = $this->settings;
            $className      = __NAMESPACE__ . "\\" . ucfirst($type) . "Settings";
            $this->settings = new $className([
                "transferID" => $this->id,
            ]);
            $this->settings->fromJson($_tmp);
        }
        return $this->settings;
    }

    /**
     * @return \backend\models\FormUser
     */
    public function getUser() {
        return $this->hasOne(\backend\models\FormUser::class,
                             ["id" => "user_id"]);
    }

    public function getSequence() {
        return $this->hasMany(CedTransfertSequence::class,
                              ["transfert_id" => "id"]);
    }

    public function getSequenceOne() {
        return $this->hasOne(CedTransfertSequence::class,
                             ["transfert_id" => "id"]);
    }

    public function getShedule() {
        return $this->hasOne(CedTransfertShedule::class, ["transfer_id" => "id"]);
    }

    /**
     * 
     * @return common\modules\crossposting\models\CedTransferLog[]
     */
    public function getLog() {
        return $this->hasMany(CedTransferLog::class, ["transfer_id" => "id"]);
    }

    /**
     * Устанавливает задание для регулярного исполнения
     * @param boolean $save True если нужно сохранить модель после установки значения
     */
    public function doShedule($save = false) {
        $this->getSettings(self::TYPE_EXPORT)->sheduleExport = 1;
        if ($save) {
            $this->save();
        }
    }

    /**
     * Снимает задание с регулярного исполнения
     * @param boolean $save True если нужно сохранить модель после установки значения
     */
    public function unShedule($save = false) {
        $this->getSettings(self::TYPE_EXPORT)->sheduleExport = 0;
        if ($save) {
            $this->save();
        }
    }

    public function finish() {
        $this->finished_at = time();
        $this->save();
    }

    public function beforeSave($insert) {
        if ($insert) {
            $this->created_at = time();
        }
        if ($this->settings instanceof TransferSettings) {
            $this->settings = $this->settings->toJson();
        }
        return parent::beforeSave($insert);
    }

    static function letFullImportFilename($fn) {
        return \yii::getAlias(self::IMPORT_FILES_STORAGE_ALIAS . "/" . $fn);
    }

    static function letFullExportFilename($fn) {
        return \yii::getAlias(self::EXPORT_FILES_STORAGE_ALIAS . "/" . $fn);
    }

}

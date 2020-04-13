<?php

namespace common\modules\crossposting\models;

use Yii;
use common\models\CedObjectsMedia as com;
use common\helpers\FileHelper as fh;
use common\components\CEDException;

/**
 * This is the model class for table "ced_objects_media_sequence".
 *
 * @property int $id
 * @property int $object_id
 * @property string $url
 * @property string $import_name
 * @property string $created_at
 */
class CedObjectsMediaSequence extends \yii\db\ActiveRecord {

    /**
     * Количество записей, загружаемых за один проход
     * @var integer
     */
    public static $sequencePartCount = 100;

    /**
     * Временная директория для загрузки файлов
     * @var string
     */
    public static $temporaryDir = "";

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'ced_objects_media_sequence';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['object_id', 'url'], 'required'],
            [['object_id'], 'integer'],
            [['import_name'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 600],
        ];
    }

    /**
     * {@inheritdoc}
     * @return CedObjectsMediaSequenceQuery the active query used by this AR class.
     */
    public static function find() {
        return new CedObjectsMediaSequenceQuery(get_called_class());
    }

    /**
     * Ставит в очередь адрес изображения
     * @param integer $objectID
     * @param string $url
     * @return integer | boolean
     */
    public static function add($objectID, $url, $importName) {
        $model = self::find()->where(["AND",
                    ["object_id" => $objectID],
                    ["url" => $url],
                ])->one();
        if (!$model) {
            $model = new CedObjectsMediaSequence([
                "object_id"   => $objectID,
                "url"         => $url,
                "import_name" => $importName,
            ]);
        }
        return $model->save();
    }

    /**
     * Возвращает порцию записей в количестве self::$sequencePartCount
     * @return CedObjectsMediaSequence[]
     */
    public static function letPart() {
        return self::find()->limit(self::$sequencePartCount)->orderBy("object_id DESC")->all();
    }

    public function uploadPhoto() {
        //$temporaryDir = sys_get_temp_dir();
        $origFilename = pathinfo($this->url);

        $fno = com::letFileName($origFilename["filename"]);
        $fn  = $fno . "." . $origFilename["extension"];

        $temporaryFilename = fh::normalizePath("{$this->getTemporaryDir()}/{$fn}");
        try {
            //$isCopy = copy($this->url, $temporaryFilename);
            $isCopy = file_put_contents($temporaryFilename,
                    file_get_contents($this->url));
        } catch (\Exception $ex) {
            throw new CEDException(sprintf("(%d. %s). %s.", $ex->getLine(),
                    $ex->getFile(), $ex->getMessage()));
        }
        if ($isCopy) {
            $comModel = com::findOrCreate($fn, $this->object_id);
            $newFile  = fh::normalizePath(\yii::getAlias($comModel->getFullPath() . DIRECTORY_SEPARATOR . $fno . ".jpg"));
            if (!is_dir(dirname($newFile))) {
                fh::createDirectory(dirname($newFile));
            }
            if (!file_exists($newFile)) {
                $img = $comModel->compressOrig($temporaryFilename, $newFile);
            }

            $pi       = pathinfo($newFile);
            $fs       = getimagesize($newFile);
            $filesize = filesize($newFile);

            $comModel->filename = $pi["basename"];
            $comModel->title    = "";
            $comModel->width    = $fs[0];
            $comModel->height   = $fs[1];
            $comModel->mime     = $fs["mime"];
            $comModel->size     = $filesize;
            if ($comModel->isNewRecord) {
                $comModel->created_at = time();
                if (!(\yii::$app instanceof \yii\console\Application)) {
                    $comModel->created_by = 1;
                } else {
                    $comModel->created_by = 1;
                    //$comModel->created_by = \yii::$app->getUser()->identity ? \yii::$app->user->identity->id : 1;
                }
            } else {
                $comModel->updated_at = time();
                if (!(\yii::$app instanceof \yii\console\Application)) {
                    //$comModel->updated_by = \yii::$app->getUser()->identity ? \yii::$app->user->identity->id : 1;
                    $comModel->updated_by = 1;
                } else {
                    null;
                }
            }
            $comModel->save();

            $thumb     = com::letThumbnailName($pi["filename"]);
            $thumbFull = fh::normalizePath(\yii::getAlias($comModel->getFullPath() . DIRECTORY_SEPARATOR . $thumb));

            if (!file_exists($thumbFull)) {
                try {
                    $isThumb = $comModel->createDefaultThumb($comModel->propMediaModule()->routes);
                } catch (\Exception $ex) {
                    throw new CEDException(sprintf("(%d. %s). %s.",
                            $ex->getLine(), $ex->getFile(), $ex->getMessage()));
                }
            }
            unset($comModel);
        } else {
            throw new CEDException(sprintf("Не удалось загрузить изображение %s для объекта %d.",
                    $this->url, $this->object_id));
        }
        unlink($temporaryFilename);
        return $isCopy;
    }

    public function getTemporaryDir() {
        return self::$temporaryDir;
    }

    public function beforeSave($insert) {
        $this->created_at = time();
        return parent::beforeSave($insert);
    }

    public static function createTable() {
        $qq = "CREATE TABLE " . self::tableName() . " (
                id int(11) NOT NULL AUTO_INCREMENT,
                object_id int(11) NOT NULL,
                url varchar(600) NOT NULL,
                created_at bigint(20) NOT NULL,
                PRIMARY KEY (id)
              )
              ENGINE = INNODB,
              CHARACTER SET utf8,
              COLLATE utf8_general_ci;
                COMMENT = 'Очередь загрузки изображений';";
        \yii::$app->db->createCommand($qq)->execute();
    }

}

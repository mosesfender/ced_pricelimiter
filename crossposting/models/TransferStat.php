<?php

/**
 * Класс для записи статистики
 */

namespace common\modules\crossposting\models;

class TransferStat extends \yii\base\Model {

    public $supposedNum = 0;    // Предполагаемое количество
    public $totalNum    = 0;    // Общее количество обработанных
    public $crossposted = 0;    // Импортированных/экспортированных
    public $plleft      = 0;    // Отклонены лимитатором
    public $plsaled     = 0;    // Проданы лимитатором
    public $saled       = 0;    // Проданы как отсутствующие в импорте
    public $nosale      = 0;    // Не для продажи
    public $beginAt     = 0;
    public $endAt       = 0;
    protected $outFileName;

    public function init() {
        parent::init();
        $this->beginAt = microtime();
    }

    public function attributeLabels(): array {
        return [
            "supposedNum" => "Предполагаемое количество",
            "totalNum"    => "Общее количество",
            "crossposted" => "Принятых",
            "plleft"      => "Отклонены лимитатором",
            "plsaled"     => "Проданы лимитатором",
            "saled"       => "Проданы как отсутствующие",
            "nosale"      => "Не для продажи",
        ];
    }

    /**
     * Сбрасывает данные в файл
     */
    public function flush() {
        file_put_contents(\yii::getAlias($this->outFileName), json_encode($this));
    }

    public function unlink() {
        $this->endAt = microtime();
        @unlink(\yii::getAlias($this->outFileName));
    }

    public function incCrossposted() {
        ++$this->crossposted;
        ++$this->totalNum;
        $this->flush();
    }

    public function incPLLeft() {
        ++$this->plleft;
        $this->flush();
    }

    public function incPLSaled() {
        ++$this->plsaled;
        ++$this->totalNum;
        $this->flush();
    }

    public function incSaled() {
        ++$this->saled;
        ++$this->totalNum;
        $this->flush();
    }

    public function incNosale() {
        ++$this->nosale;
        ++$this->totalNum;
        $this->flush();
    }

    public function setOutFileName($val) {
        $this->outFileName = $val;
        return $this;
    }

    public function load($data, $formName = null) {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

}

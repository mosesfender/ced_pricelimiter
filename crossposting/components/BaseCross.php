<?php

namespace common\modules\crossposting\components;

use common\components\CEDException;
use common\modules\crossposting\models\CedTransfert;

/**
 * @param Sequence $sequenceComponent
 */
class BaseCross extends \yii\base\Component {

    /**
     * @var \common\modules\crossposting\Module
     */
    public $module;

    /**
     * @var \common\modules\crossposting\models\CedPartners
     */
    public $partner;

    /**
     * @var \common\modules\crossposting\models\CedPartners
     */
    public $vendor;

    /**
     * @var \common\modules\crossposting\models\CedTransfert
     */
    public $transfer;

    /**
     * @var \common\modules\crossposting\models\ExportSettings | \common\modules\crossposting\models\ImportSettings
     */
    public $settings;

    /**
     *
     * @var \common\modules\crossposting\components\Sequence
     */
    public $sequence;

    /**
     * @param string|integer $partner
     * @return \common\modules\crossposting\components\Import
     */
    public function create() {
        if (!$this->settings) {
            throw new CEDException("Не подготовлены настройки!");
        }

        $this->partner = $this->module->letPartner($this->settings->partnerID);
        if (is_null($this->partner)) {
            throw new CEDException("Партнёр не существует");
        }

        $this->vendor = $this->module->letPartner($this->settings->vendorID);
        if (is_null($this->vendor)) {
            throw new CEDException("Вендор не существует");
        }
    }

    public function setTransfer($transfer) {
        if (is_scalar($transfer)) {
            $this->transfer = CedTransfert::findOne($transfer);
        }
        if ($transfer instanceof CedTransfert) {
            $this->transfer = $transfer;
        }
        return $this;
    }

    public function getSettings($type) {
        if ($this->transfer && !($this->settings instanceof \common\modules\crossposting\models\TransferSettings)) {
            $this->settings = $this->transfer->getSettings($type);
        }
        return $this->settings;
    }

    /**
     * 
     * @return CedTransfertSequence
     */
    public function getSequence() {
        if (!is_null($this->getSequenceComponent())) {
            if (is_null($this->sequence->model)) {
                $this->sequence->model = $this->transfer->sequence;
            }
            return $this->sequence->model;
        }
        return null;
    }

    /**
     * 
     * @return Sequence
     */
    public function getSequenceComponent() {
        if (is_null($this->sequence)) {
            $this->sequence = $this->module->sequence;
        }
        return $this->sequence;
    }

    /**
     * Возвращает ID источника импорта. 
     * Если у партнёра не совпадает хост с хостом процессора импорта, значит партнёр берёт файл из своего источника, 
     * и должен быть указан в карте объектов в поле partner_id.
     * Иначе возвращается идентификатор процессора импорта.
     * @return int
     */
    public function getVendorId() {
        $piVendor = parse_url($this->vendor->permanent_link);
        $piOwner  = parse_url($this->partner->permanent_link);
        return $piVendor["host"] == $piOwner["host"] ? $this->vendor->id : $this->partner->id;
    }

    /**
     * Удаляет трансфер из очереди
     */
    public function removeFromSequence() {
        
    }

    protected function setLogFile($name = "", $extension = ".log") {
        if (empty($name) && $this->transfer) {
            $name = $this->transfer->id;
        }
        $this->module->log->logFile = "{$name}{$extension}";
        $this->module->log->info("Лог инициализирован");
    }

}

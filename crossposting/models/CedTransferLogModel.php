<?php

namespace common\modules\crossposting\models;

use common\helpers\ArrayHelper;

class CedTransferLogModel extends \yii\base\Model {

    const ITEM_TYPE_INFO    = 1;
    const ITEM_TYPE_SUCCESS = 2;
    const ITEM_TYPE_WARNING = 3;
    const ITEM_TYPE_ERROR   = 4;

    /**
     *
     * @var CedTransferLog
     */
    private $_owner;

    /**
     * Заголовок действия
     * @var string
     */
    public $title;

    /**
     * Записи лога
     * @var LogItem[]
     */
    public $logItems = [];

    /**
     * Статистика импорта или экспорта
     * @var TransferStat
     */
    public $statistic;

    public function __construct($config = array()) {
        $this->_owner = ArrayHelper::remove($config, "owner");
        parent::__construct($config);
    }

    public function add($itemType, $file, $line, $cedObjectID, $partnerObjectID,
            $message) {
        $_tmp             = new LogItem([
            "type"            => $itemType,
            "file"            => $file,
            "line"            => $line,
            "cedObjectID"     => $cedObjectID,
            "partnerObjectID" => $partnerObjectID,
            "message"         => $message,
        ]);
        $this->logItems[] = sprintf($_tmp);
        return $this;
    }

    public function error($message, $file = null, $line = null,
            $cedObjectID = null, $partnerObjectID = null) {
        return $this->add(self::ITEM_TYPE_ERROR, $file, $line, $cedObjectID,
                        $partnerObjectID, $message);
    }

    public function info($message, $cedObjectID = null, $partnerObjectID = null) {
        return $this->add(self::ITEM_TYPE_INFO, null, null, $cedObjectID,
                        $partnerObjectID, $message);
    }

    public function success($message, $cedObjectID = null,
            $partnerObjectID = null) {
        return $this->add(self::ITEM_TYPE_SUCCESS, null, null, $cedObjectID,
                        $partnerObjectID, $message);
    }

    public function warning($message, $cedObjectID = null,
            $partnerObjectID = null) {
        return $this->add(self::ITEM_TYPE_WARNING, null, null, $cedObjectID,
                        $partnerObjectID, $message);
    }

    public function serialize() {
        return json_encode([
            "title"      => $this->title,
            "logItems"   => $this->logItems,
            "statistics" => $this->getStatistic()
        ]);
    }

    public function unserialize($str) {
        $_tmp = json_decode($str);
        try {
            $this->title = $_tmp->title;
            $this->restoreItems($_tmp->logItems);
            $this->getStatistic(false);
            //prer((array) $_tmp->statistics,1);
            $this->statistic->load((array) $_tmp->statistics);
        } catch (\Exception $ex) {
            
        }
    }

    protected function restoreItems($serialized) {
        foreach ($serialized as $item) {
            $this->logItems[] = LogItem::fromString($item);
        }
    }

    public function getStatistic($doTemp = true) {
        if (!$this->statistic) {
            $settings        = $doTemp ? ["outFileName"
                => "{$this->_owner->module->temporaryPath}/{$this->_owner->transfer_id}_stat"]
                        : [];
            $this->statistic = new TransferStat($settings);
        }
        return $this->statistic;
    }

    /**
     * Возвразает имена классов, соответствующих типу записи.
     * Если указан входящий параметр, возвращается тип, соответствующий ему.
     * @param int $needed
     * @return array|string
     */
    public static function typeCast($needed = null) {
        $types = [
            self::ITEM_TYPE_INFO    => LogItem::class,
            self::ITEM_TYPE_SUCCESS => LogItem::class,
            self::ITEM_TYPE_WARNING => LogItem::class,
            self::ITEM_TYPE_ERROR   => LogItem::class,
        ];
        if (is_null($needed)) {
            return $types;
        } else {
            return $types[$needed];
        }
    }

    public static function typeNames($needed = null) {
        $types = [
            self::ITEM_TYPE_INFO    => "info",
            self::ITEM_TYPE_SUCCESS => "success",
            self::ITEM_TYPE_WARNING => "warning",
            self::ITEM_TYPE_ERROR   => "error",
        ];
        if (is_null($needed)) {
            return $types;
        } else {
            return $types[$needed];
        }
    }

}

class LogItem {

    public $time;
    public $type;
    public $file;
    public $line;
    public $cedObjectID;
    public $partnerObjectID;
    public $message;

    public function __construct($params) {
        $this->time = microtime(true);
        foreach ($params as $key => $val) {
            $this->{$key} = $val;
        }
    }

    public static function fromString($str) {
        list($type, $time, $file, $line, $cedObjectID,
                $partnerObjectID, $message) = explode("|", $str);
        return new LogItem([
            "time"            => $time,
            "type"            => $type,
            "file"            => $file,
            "line"            => $line,
            "cedObjectID"     => $cedObjectID,
            "partnerObjectID" => $partnerObjectID,
            "message"         => $message,
        ]);
    }

    public function __toString() {
        return "{$this->type}|{$this->time}|{$this->file}|{$this->line}|{$this->cedObjectID}|{$this->partnerObjectID}|{$this->message}";
    }

}

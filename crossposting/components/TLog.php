<?php

use common\modules\crossposting\models\CedTransferLogModel as m;

class TLog extends \common\modules\crossposting\models\CedTransferLog {

    /**
     * @return \common\modules\crossposting\models\CedTransferLog
     */
    public static function Log() {
        if (!isset($GLOBALS["_ctl"])) {
            $GLOBALS["_ctl"] = new TLog([
                "begin_at" => time()
            ]);
        }
        return $GLOBALS["_ctl"];
    }

    public static function flush() {
        self::Log()->save(false);
    }

    public static function transferID($val) {
        self::Log()->transfer_id = $val;
        return self::Log();
    }

    public static function import($transferID, &$module) {
        self::Log()->setImport($transferID, $module);
        return self::Log();
    }

    public static function export($transferID, &$module) {
        self::Log()->setExport($transferID, $module);
        return self::Log();
    }

    public static function crosspost() {
        self::Log()->setCrosspostingAction();
        return self::Log();
    }

    public static function title($val) {
        self::Log()->data->title = $val;
        return self::Log();
    }

    /**
     * 
     * @param string $message
     * @param string $file
     * @param int $line
     * @param int $cedObjectID
     * @param string $partnerObjectID
     * @return type
     */
    public static function error($message, $file = null, $line = null,
            $cedObjectID = null, $partnerObjectID = null) {
        self::Log()->data->error($message, $file, $line, $cedObjectID,
                $partnerObjectID);
        return self::Log();
    }

    public static function success($message, $cedObjectID = null,
            $partnerObjectID = null) {
        self::Log()->data->success($message, $cedObjectID, $partnerObjectID);
        return self::Log();
    }

    public static function info($message, $cedObjectID = null,
            $partnerObjectID = null) {
        self::Log()->data->info($message, $cedObjectID, $partnerObjectID);
        return self::Log();
    }

    public static function warning($message, $cedObjectID = null,
            $partnerObjectID = null) {
        self::Log()->data->warning($message, $cedObjectID, $partnerObjectID);
        return self::Log();
    }

    public static function s_incCrossposted() {
        self::Log()->data->getStatistic()->incCrossposted();
        return self::Log();
    }

    public static function s_incPLLeft() {
        self::Log()->data->getStatistic()->incPLLeft();
        return self::Log();
    }

    public static function s_incPLSaled() {
        self::Log()->data->getStatistic()->incPLSaled();
        return self::Log();
    }

    public static function s_incSaled() {
        self::Log()->data->getStatistic()->incSaled();
        return self::Log();
    }

    public static function s_incNosale() {
        self::Log()->data->getStatistic()->incNosale();
        return self::Log();
    }

    public static function s_Unlink() {
        self::Log()->data->getStatistic()->unlink();
        return self::Log();
    }

}

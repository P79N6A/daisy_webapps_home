<?php
/**
 * description: 数据库日志类
 * author: lilong3@staff.sina.com.cn
 * createTime: 2016/6/12 09:39
 */

class Comm_Log {

    const LOG_LEVEL_FATAL = 1;
    const LOG_LEVEL_WARNING = 2;
    const LOG_LEVEL_NOTICE = 3;
    const LOG_LEVEL_TRACE = 4;
    const LOG_LEVEL_DEBUG = 5;

    public static $arrLogLevels = array(
        self::LOG_LEVEL_FATAL => 'FATAL',
        self::LOG_LEVEL_WARNING => 'WARNING',
        self::LOG_LEVEL_NOTICE => 'NOTICE',
        self::LOG_LEVEL_TRACE => 'TRACE',
        self::LOG_LEVEL_DEBUG => 'DEBUG',
    );
    private static $dbInstance = null;

    /**
     * 文件日志
     * @param $error
     * @param int $errno
     * @return int
     */
    public static function fileLog($error, $errno = 0) {
        $prefix = self::getLogPrefix();
        if ($_SERVER['SINASRV_CACHE_DIR']) {
            $logPath = $_SERVER['SINASRV_CACHE_DIR'].'/log/'.$prefix;
        } else {
            $logPath = APPLICATION_PATH.'/log/'.$prefix;
        }
        if (!is_dir($logPath)) {
            @mkdir($logPath,0777,true);
        }
        $logPath .= '/'.$prefix.'_log_'.date('Ymd').'.log';
        $ip = Comm_Ip::getClientIp();
        $logstr = "st[%s] ip[%s] msg[%s] ext[%s] \r\n";
        $logstr  = sprintf($logstr,date('Y-m-d H:i:s'), $ip, $error, $errno);

        return file_put_contents($logPath, $logstr, FILE_APPEND);
    }

    /**
     * 获取日志文件前缀
     * @return string
     */
    public static function getLogPrefix(){
        if(defined('MODULE')){
            return strtolower(MODULE);
        }else{
            return 'api';
        }
    }

    /**
     * 接口运行日志
     * @param string $url
     * @param string $msg
     */
    public static function apiLog($retCode, $url='', $msg='') {
        if (empty($url)) {
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        }
        //write log
        $info['ip'] = Comm_Ip::getClientIp();
        $info['url'] = $url;
        $info['param'] = $msg?$msg:http_build_query($_POST);
        $info['ret_code'] = $retCode;
        $info['time'] = time();

        $logApiModel = Mod_LogApiModel::getInstance();
        return $logApiModel->insert($info);
    }

}

?>
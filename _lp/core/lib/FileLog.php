<?php
/**
 * 简单的文件log类
 */
namespace _lp\core\lib;

class FileLog
{
    /**
     * 日志类型  notice
     */
    const LOG_LEVEL_NOTICE="notice";
    
    /**
     * 日志类型 debug
     */
    const LOG_LEVEL_DEBUG="debug";
    /**
     * 日志类型 error
     */
    const LOG_LEVEL_ERROR="error";
    /**
     * 日志类型 info
     */
    const LOG_LEVEL_INFO="info";
    /**
     * 日志类型 access
     *  访问异常，如DDOS攻击
     */
    const LOG_LEVEL_ACCESS="access";
    /**
     * 日志类型 api
     * 接口返回异常错误。
     */
    const LOG_LEVEL_API_ERROR="api";
    
    /**
     * @var string 日志文件名
     */
    private $_logName;
    
    /**
     * @var string 日志目录
     */
    private $_logDir;
    
    /**
     * @var string 日志全路径
     */
    private $_logFullPath;
    
    /**
     * @var array 日志句柄数组
     */
    private $_logHandle;

//    private $log_file;
//    private $fd;
//    private $logDir = "logs/";
//    private $logFile = "log.common.txt";


    public function __construct($logName = "", $logDir = "")
    {
        $this->_logName = $logName;
        $this->_logDir = $logDir;
    }
    /**
     *  设置日志目录
     * @param string $logPath
     */
    public function setLogDir($logPath = '')
    {
        $this->_logDir = $logPath;
    }
    
    /**
     * 设置日志文件名
     * @param string $fileName
     */
    public function setLogFile($fileName = '')
    {
        if (!empty( $fileName )) {
            $this->_logName = sprintf('app.%s.%s.log', $fileName, date("Ymd"));
        }
    }
    
    public function write($level, $msg = "", $fileName = "")
    {
        if (!empty($fileName)) {
            $this->setLogFile($fileName);
        }

        $rs = $this->log($level, $msg);
        //把自定义文件名置空
        $this->_logName = '';

        return $rs;
    }

    private function log($level, $msg)
    {
        
        $this->_logDir = empty($this->_logDir) ? WEB_ROOT . DS . 'logs' : $this->_logDir;
        if (!file_exists($this->_logDir)) {
            if (false ===  mkdir($this->_logDir)) {
                return false;
            }
        }
        
        if (empty($this->_logName)) {
            switch ($level) {
                case self::LOG_LEVEL_ACCESS:
                    $this->_logName = 'app.access_error.'.date('Ymd').'.log';
                    break;
                case self::LOG_LEVEL_API_ERROR:
                    $this->_logName = 'app.api_error.'.date('Ymd').'.log';
                    break;
                case self::LOG_LEVEL_ERROR:
                    $this->_logName = 'app.sys_error.' . date('Ymd') . '.log' ;
                    break;
                case self::LOG_LEVEL_DEBUG:
                case self::LOG_LEVEL_INFO:
                case self::LOG_LEVEL_NOTICE:
                    $this->_logName = 'app.' . $level . '.' . date('Ymd') . '.log';
                    break;
                default:
                    $this->_logName = 'app.common.' . date('Ymd') . '.log';
            }
        }

        $this->_logFullPath = $this->_logDir . DS . $this->_logName;
        $logKey = 'log_' . md5($this->_logName);
        if (!is_writable($this->_logDir)) {
            if ( DEBUG ) {
                echo 'failed to open stream: Permission denied : ' . $this->_logFullPath;
            }
            return false;
        }
        if (!$this->_logHandle[$logKey]) {
                $this->_logHandle[$logKey] = fopen($this->_logFullPath, 'a');
            if (!$this->_logHandle[$logKey]) {
                if ( DEBUG ) {
                    echo 'failed to open stream: Permission denied : ' . $this->_logFullPath;
                }
                return false;
            }
        }
                
        $ip = getip();
        $log = '';
        $log .= '[requestUrl]http://'. $_SERVER['SERVER_NAME'] . ':' .$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"] . "\n";
        $log .= sprintf("[%s][%s][%s] : %s\n", $level, date("Y-m-d H:i:s"), $ip, print_r($msg, 1));
        
        fwrite($this->_logHandle[$logKey], $log);
        @chown($this->_logFullPath, 'nobody');
        return true;
    }
    
    public function fatal($msg)
    {
        $this->log(self::LOG_LEVEL_ERROR, $msg);
    }
    public function error($msg)
    {
        $this->log(self::LOG_LEVEL_ERROR, $msg);
    }
    public function notice($msg)
    {
        $this->log(self::LOG_LEVEL_NOTICE, $msg);
    }
    public function info($msg)
    {
        $this->log(self::LOG_LEVEL_INFO, $msg);
    }
    public function debug($msg)
    {
        $this->log(self::LOG_LEVEL_DEBUG, $msg);
    }
    public function api($msg)
    {
        $this->log(self::LOG_LEVEL_API_ERROR, $msg);
    }
    public function access($msg)
    {
        $this->log(self::LOG_LEVEL_ACCESS, $msg);
    }
    
    public function __destruct()
    {
        if (!empty($this->_logHandle)) {
            foreach ($this->_logHandle as $logHandle) {
                fclose($logHandle);
            }
        }
    }
}

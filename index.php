<?php
/**
 * 应用唯一入口，统一处理业务请求 
 * 
 */

//目录分隔符
define('DS' , DIRECTORY_SEPARATOR);

//根路径
define('WEB_ROOT' , dirname( __FILE__ ) );

//框架内核路径
define('FRAEWORK_ROOT',  WEB_ROOT . DS . '_lp' );

//运营活动目录
define('APP_BASE_DIR', 'act_app');

//运营活动路径
define('APP_ROOT', WEB_ROOT . DS . APP_BASE_DIR);

//根日志目录路径
define('LOG_ROOT', WEB_ROOT . DS . 'logs');

//是否开启debug模式，开发环境设置为1，正式环境设置为0
define('DEBUG', 1);

date_default_timezone_set('Asia/Chongqing');

if( DEBUG ) { 
    error_reporting(E_ALL^E_NOTICE);
    ini_set('display_errors' , true);
} else { 
    ini_set('display_errors' , false);
}

if( substr(PHP_VERSION, 0,3) < 5.3 ) 
{ 
    header('Content-Type: text/html; charset=utf-8');
    exit('当前php版本低于5.3');
}

//加载框架核心应用类
require_once FRAEWORK_ROOT . DS . 'lp.init.php';

use _lp\lp;
use _lp\core\lib\FileLog;

//自动加载文件
spl_autoload_register(function($className){ 
    $fileName = ucwords(substr($className, strrpos($className, '\\') + 1)) . '.php';
    $filePath = WEB_ROOT . DS . str_replace('\\', DS  ,substr($className,0, strrpos($className, '\\'))) . DS . $fileName;
    if( file_exists($filePath) ) 
    { 
        require_once $filePath;
    } 
});
$configFile = WEB_ROOT . DS . 'config' . DS . 'common.config.php';
$configArr = array();
if( file_exists($configFile) ) {
    $_tmpConfig = require $configFile;
    if(is_array($_tmpConfig) ) { 
        $configArr = $_tmpConfig;
        unset($_tmpConfig);
    }
}
lp::createApplication($configArr)->run();

set_error_handler( function($errno, $error_msg, $error_file, $error_line, $error_context) { 
    $errInfo = array('error_file'=>$error_file,'error_line'=>$error_line,'error_msg'=>$error_msg, 'error_context'=>$error_context, 'error_no'=>$errno);
    switch ($errno) {
        case E_ERROR: 
        case E_USER_ERROR:
        case E_WARNING:
        case E_USER_WARNING:
        case E_PARSE:
//            log::writeFileLog($errInfo, log::LOG_LEVEL_ERROR);
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, $errInfo);
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            //排除 unserialize() 的错误提示
            if(!strstr($error_msg, 'unserialize()')){
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, $errInfo);
            }
            break;
        default:
//            log::writeFileLog($errInfo, log::LOG_LEVEL_INFO);
            lp::log()->write(FileLog::LOG_LEVEL_INFO, $errInfo);
            break;
    }
}, E_ALL );

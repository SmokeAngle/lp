<?php
/**
 * 单元测试入口
 *
 */
define('DS', DIRECTORY_SEPARATOR);
define('WEB_ROOT', dirname(__FILE__));
define('FRAEWORK_ROOT', WEB_ROOT . DS . '_lp');
define('APP_BASE_DIR', 'act_app');
define('APP_ROOT', WEB_ROOT . DS . APP_BASE_DIR);
define('LOG_ROOT', WEB_ROOT . DS . 'logs');
define('DEBUG', true);

date_default_timezone_set('Asia/Chongqing');
if (DEBUG) {
    error_reporting(E_ALL^E_NOTICE);
    ini_set('display_errors', true);
} else {
    ini_set('display_errors', false);
}
if (substr(PHP_VERSION, 0, 3) < 5.3) {
    header('Content-Type: text/html; charset=utf-8');
    exit('当前php版本低于5.3');
}
spl_autoload_register(function($className){
    $fileName = ucwords(substr($className, strrpos($className, '\\') + 1)) . '.php';
    $filePath = WEB_ROOT . DS . substr($className, 0, strrpos($className, '\\')) . DS . $fileName;
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

require_once WEB_ROOT . DS . 'config' . DS . 'common.config.php';
require_once FRAEWORK_ROOT . DS . 'st.init.php';

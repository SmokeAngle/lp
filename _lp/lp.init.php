<?php
/**
 * 核心应用类
 * 
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp;

use _lp\core\lib\FileLog;
use _lp\core\lib\DB;
use _lp\core\lib\MemcachedClient;
use _lp\core\lib\User;
use _lp\core\lib\Act;
use _lp\core\lib\Loader;
use _lp\core\lib\RedisClient;

/**
 *  应用类
 */ 
class lp { 
    
    /**
     * @var application 实例
     */
    private static $_app;
    /**
     *
     * @var 日志对实例 记录公共日志
     */
    private static $_logger;
    
    private static $_loader;

        /**
     *  创建应用实例
     * @param array $config
     * @return \_lp\application
     */
    public static function createApplication($config = array()) { 
        return new application($config);
    }

    /**
     * 设置日志
     * @param class $logger
     */
    public static function setLogger($logger) { 
        if( self::$_logger === NULL || $logger === NULL ) { 
           self::$_logger = $logger;
        }
    }
    /**
     *  日志静态方法
     */
    public static function log() { 
        return self::$_logger;
    }
    
    /**
     *  加载自定义函数或者配置
     */
    public static function loader() { 
        if (self::$_loader !== NULL && self::$_loader instanceof Loader) { 
            return self::$_loader;
        }
        if (FALSE === ( $configPath = lp::App()->getConfigItem('configPath') )) { 
            $configPath = WEB_ROOT . DS . 'config' . DS;
        }
        if( FALSE === ( $functionPath = lp::App()->getConfigItem('functionPath') ) ) { 
            $functionPath = WEB_ROOT . DS . 'function' . DS;
        }
        self::$_loader = new Loader($functionPath, $configPath);
        return self::$_loader;
    }

    /**
     *  设置应用实例
     * @param  $app application
     */
    public static function setApp($app) { 
        if( self::$_app === NULL || $app == NULL ) { 
            self::$_app = $app;   
        } 
    }

    /**
     *  返回当前应用实例
     * @return application
     */
    public static function App() { 
        return self::$_app;
    }
    
}

class application { 
    
    /**
     * @var DB 数据库 
     */
    public $db;
    
    /**
     * @var memcached memcached 实例
     */
    public $memcached;
    
    /**
     *
     * @var redisClient 
     */
    public $redis;
    /**
     * @var 控制器
     */
    public $controller;
    /**
     * @var 动作
     */
    public $action;
    
    /**
     * @var User 用户
     */
    public $user;
    /**
     * @var string 控制器目录
     */
    public $controlerDir = "";
    
    /**
     * @var array 配置数组
     */
    public $config = array();
    /**
     * @var Act 活动
     */
    public $act;

    /**
     * @var array 自动加载 函数库
     */
    private $_autoLoadFunction = array(
        'Core'
    );
    /**
     * @var array 自动加载类库
     */
    private $_autoLoadClass = array(
        
    );

    public function __construct($config = array()) {
        $this->_loadCoreFunction();
        $this->_loadCoreClass();
        $this->config = $config;
        $this->user = new User();
        $this->act = new Act();
    }
    
    public function getConfigItem( $itemName = "" ) { 
        return isset($this->config[$itemName]) ? $this->config[$itemName] : FALSE;
    }
    
    public function setConfig( $configArr = array() ) { 
        $this->config = array_merge($this->config, $configArr);
    }

        /**
     * @param string 控制器所在目录 y_2014/test
     * @return \_lp\application
     */
    public function setControllerDir( $controllerDir = "" ) { 
        if( !empty($controllerDir) ) { 
            $this->controlerDir = str_replace('/', '\\', $controllerDir);
        }
        return $this;
    }

    /**
     * @param string 控制器名字
     * @return \_lp\application
     */
    public function setController( $controllerName = "", $dir = "" ) { 
        if( !empty($controllerName) ) { 
            $className = ( !empty($this->controlerDir) ? ( $this->controlerDir . '\\' ) : '' ) 
                            . ( !empty($dir) ? ( $dir . '\\' ) : '' )
                            . $controllerName . 'Controller';
            $this->controller = $className;
        }
        return $this;
    }
    
    /**
     * 
     * @param string 动作名
     * @return \_lp\application
     */
    public function setAction( $actionName = "" ) { 
        if( !empty($actionName) ) { 
            $this->action = $actionName;
        }
        return $this;
    }
    
    public function setParams() {
        return $this;
    }
    
    
    /**
     *  加载核心函数库
     * @return void
     */
    private function _loadCoreFunction() { 
        $functionsDir = FRAEWORK_ROOT . DS . 'core' . DS . 'function';
        if( !empty($this->_autoLoadFunction) ) { 
            foreach ( $this->_autoLoadFunction as $functionFileNamePrefix ) { 
                $functionFilePath = $functionsDir . DS . $functionFileNamePrefix . '.function.php';
                if( file_exists($functionFilePath) ) { 
                    require_once $functionFilePath;
                }
            }
        }
    }
    
    
    public function _loadCoreClass() { 
                
    }

    /**
     * 应用初始化
     */
    private function _init() { 
        
        lp::setApp($this);
        //设置应用日志
        $logger = new FileLog();
        lp::setLogger($logger);
        
        if( FALSE !== ($memcachedConfig = $this->getConfigItem('memcached')) && !$this->memcached) { 
            $this->memcached = new MemcachedClient($memcachedConfig);
        }
        if( FALSE !== ($redisConfig = $this->getConfigItem('redis')) && !$this->redis ) { 
            $redis = new RedisClient($redisConfig['host'], $redisConfig['port'], $redisConfig['db'], $redisConfig['isCluster']);
            $this->redis = $redis->getConnection();
        }
        if( FALSE !== ( $dbconfig = $this->getConfigItem('dbconfig') ) && !$this->db ) { 
            //var_dump($dbconfig['host']);
            $host = isset($dbconfig['host']) ? $dbconfig['host'] : '';
            $port = isset($dbconfig['port']) ? $dbconfig['port'] : 3306;
            $user = isset($dbconfig['user']) ? $dbconfig['user'] : '';
            $password = isset($dbconfig['password']) ? $dbconfig['password'] : '';
            $dbname =  isset($dbconfig['dbname']) ? $dbconfig['dbname'] : '';
            $this->db = new DB($host, $port, $user, $password, $dbname);
        } else { 
            jsonpEcho(500, '数据库配置错误', array(), TRUE);
        }
        
        //路由设置
        $_tmpDir = "";
        if( FALSE !== ( $_p = v('_p') ) && !empty($_p) ) { 
            @list( $year, $actName ) = explode(':', $_p);
	    $controllerDir = sprintf('%s\\y_%s\\%s', APP_BASE_DIR, $year, $actName);
	    $controllerDir = str_replace('\\', DS, $controllerDir);
            $appConfigFile = WEB_ROOT . DS . $controllerDir . DS . 'act.config.php' ;
            
            
            $_tmpDir = 'controller';
            if( file_exists($appConfigFile) ) { 
                $_appConfig = require $appConfigFile;
                $this->setConfig($_appConfig);
            }
            
            $logPath = WEB_ROOT . DS . $controllerDir . DS . 'logs';
            if( !file_exists($logPath) ) { 
                if( !mkdir($logPath, 0777) ) { 
                    $logger->fatal('日志目录' . $logPath . '不存在！');
                }  else { 
                    $logPath = LOG_ROOT;
                }
            }
            $logger->setLogDir($logPath);
        } else { 
            $controllerDir = 'controllers';
        }
        
        if( !file_exists( WEB_ROOT . DS . $controllerDir ) ) { 
            forward('/err_404.html');
        }

        
     
        $this->setControllerDir($controllerDir);
        $defaultController = $this->config['defaultController'] == FALSE ? 'default' : $this->config['defaultController'];
        $defaultAction = $this->config['defaultAction'] == FALSE ? 'index' : $this->config['defaultAction'];
        $controllerName = v('c') != FALSE ? v('c') : $defaultController;
        $actionName  = v('a') != FALSE ? v('a') : $defaultAction;
        $controllerName = basename(z($controllerName));
        $actionName = basename(z($actionName));
        $this->setController($controllerName, $_tmpDir)->setAction($actionName);
        
        //初始化活动信息
        if( FALSE !== ( $activeConfig = $this->getConfigItem('active') ) ) { 
            $this->act->init(array(
                'actNo'  => isset($activeConfig['actNo']) ? $activeConfig['actNo'] : "",
                'gameId' => isset($activeConfig['gameId']) ? $activeConfig['gameId'] : "",
                'startTime' => isset($activeConfig['startTime']) ? $activeConfig['startTime'] : "",
                'endTime' => isset($activeConfig['endTime']) ? $activeConfig['endTime'] : ""
            ));
            
            if( $activeConfig['checkActValidFunc'] && gettype($activeConfig['checkActValidFunc'] === 'object')) { 
                $this->act->isValidAct($activeConfig['checkActValidFunc']);
            }
        }
    }
    
    public function run() { 
        $this->_init();
        $controllerName = $this->controller;
        if( class_exists($controllerName) ) { 
            $controller = new $controllerName();
            if( method_exists($controller, $this->action) ) { 
                $controller->{$this->action}();
            } else { 
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, '请求方法：' . $this->action . '不存在！');
                exit;
            }
        } else { 
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, '控制器' . $this->controller . '不存在');
                exit;
        }
    }
}




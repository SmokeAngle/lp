<?php
return array(
    'defaultController' => 'Default',
    'defaultAction' => 'index',

    //配置同一ip在1分钟内请求次数达到多少，即认为是异常访问, 设置为30次
    'accessFilter' => array(
        'checkDDos' => array(
            'checkFaildFunc' => function() { 
                // jsonpEcho($res = 0, $msg = '', $data = array(), $isExit = TRUE)
                jsonpEcho(100, 'Forbidden to access DDos');
            },
            'maxRequest' => 30, //每分钟最大请求数
            'allowRequest' => array(), // array('controller/action1','controller/action2')
        ),
        'checkLogin' => array(
            'checkFaildFunc' => function() { 
                jsonpEcho(101, '您的登录态失效，请尝试重新登录~');
            },
            'url' => array(), // array('controller/action1','controller/action2')
        ),
    ),
    //memcached 配置
    'memcached' => array(
        'servers' => array('10.10.15.10:11211'),
        'expire'=>'60',
        'debug'   => FALSE,
        'compress_threshold' => 10240,
        'persistant' => true
    ),

    //消息队列 $GLOBALS['config']['httpsqs'] = array('host'=>'127.0.0.1','port'=>1218,'auth'=>'');
    'httpsqs' => array('host'=>'10.10.15.10','port'=>1218,'auth'=>''),
                    
    'msgSignKey' => 'iwesdiosdflsdkl',
    'msgApi' => 'http://dy.niu.xunlei.com/customer/',

    //数据库配置
    'dbconfig' => array(
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => 'sd-9898w',
        'dbname' => 'niux_game_active'
    )
);

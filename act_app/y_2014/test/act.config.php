<?php
/**
 * 活动相关配置，如果配置的key跟/config/app.config.php配置相同，则覆盖之。
 * 使活动的配置更加灵活方便。
 */

$config = array();

$config['active']['gameId'] = '000180';
$config['active']['actNo'] = 'dskf';
$config['active']['startTime'] = '2014/12/26 00:00:00';
$config['active']['endTime'] = '2015/01/08 00:00:00';
$config['active']['checkActValidFunc'] = function ($gameId, $actNo, $startTime, $endTime) {
//            if( empty($gameId) || empty($actNo) || empty($startTime) || empty($endTime) ) { 
//                  jsonEcho(501, '活动配置错误', array(), TRUE);
//            }
//            if( strtotime($startTime) > time() ) { 
//                jsonEcho(502, '活动还未开始', array(), TRUE);
//            }
//            if( strtotime($endTime) < time() ) { 
//                jsonEcho(503, '活动已经结束', array(), TRUE);
//            }
            return true;
};

$config['accessFilter']['checkDDos']['maxRequest'] = 30;
$config['accessFilter']['checkDDos']['allowRequest'] = array();
$config['accessFilter']['checkDDos']['checkFaildFunc'] = function($config, $result) {
    jsonEcho(100, 'Forbidden to access DDos', array(), true);

};

$config['accessFilter']['checkLogin']['url'] = array('default/test');
$config['accessFilter']['checkLogin']['checkFaildFunc'] = function($config, $result) {
    jsonEcho(101, '您的登录态失效，请尝试重新登录~', array(), true);
};

$config['giftPacketRule'][1]['checkBindPhone']['checked'] = true;
//$config['giftPacketRule'][1]['checkBindPhone']['checkFunc'] = function( $config, $result ) { };
$config['giftPacketRule'][1]['checkBindPhone']['checkFaildFunc'] = function($config, $result) {
    jsonEcho(1, '手机绑定失败', array(), false);

};
$config['giftPacketRule'][1]['checkBindPhone']['checkSuccessFunc'] = function($config, $result) {
 
};


$config['giftPacketRule'][1]['checkUserGiftNum']['d18'] = 1000;
//$config['giftPacketRule'][1]['checkUserGiftNum']['checkFunc'] = function( $config, $result ) { };
$config['giftPacketRule'][1]['checkUserGiftNum']['checkFaildFunc'] = function($config, $result) {
    jsonEcho(2, '您已经领过礼包了', array(), true);
};
$config['giftPacketRule'][1]['checkUserGiftNum']['checkSuccessFunc'] = function($config, $result) {
    
};

$config['giftPacketRule'][1]['checkServerGiftNum']['d18'] = 4;
$config['giftPacketRule'][1]['checkServerGiftNum']['d30'] = 20;
//$config['giftPacketRule'][1]['checkServerGiftNum']['checkFunc'] = function( $config, $result ) { };
$config['giftPacketRule'][1]['checkServerGiftNum']['checkFaildFunc'] =  function($config, $result) {
    jsonEcho(4, 'checkServerGiftNum error', array(), true);
    var_dump($result);

};
$config['giftPacketRule'][1]['checkServerGiftNum']['checkSuccessFunc'] = function($config, $result) {
 /*  */
};


$config['giftPacketRule'][1]['checkTotalGiftNum']['num'] = 15;
//$config['giftPacketRule'][1]['checkTotalGiftNum']['checkFunc'] = function( $config, $result ) { };
$config['giftPacketRule'][1]['checkTotalGiftNum']['checkFaildFunc'] =  function($config, $result) {
    jsonEcho(5, 'checkTotalGiftNum error', array(), true);
    var_dump($result);

};
$config['giftPacketRule'][1]['checkTotalGiftNum']['checkSuccessFunc'] = function($config, $result) {
 /*  */
};


//$config['giftPacketRule'][1]['checkRoleLevel']['min'] = 1;
//$config['giftPacketRule'][1]['checkRoleLevel']['max'] = 54;
////$config['giftPacketRule'][1]['checkRoleLevel']['checkFunc'] = function( $config, $result ) { };
//$config['giftPacketRule'][1]['checkRoleLevel']['checkFaildFunc'] =  function($config, $result) {
//    jsonEcho(3, '你的级别不在范围内', array(), true);
//};
//$config['giftPacketRule'][1]['checkRoleLevel']['checkSuccessFunc'] = function($config, $result) {
// /*  */
//};



return $config;

<?php
/**
 * 活动相关配置，如果配置的key跟/config/common.config.php配置相同，则覆盖之。
 * 使活动的配置更加灵活方便。
 */

$config = array();

$config['active']['gameId'] = '000180';
$config['active']['actNo'] = 'dskf';
$config['active']['startTime'] = '2014/12/26 00:00:00';
$config['active']['endTime'] = '2015/04/08 00:00:00';

//配置检查活动的有效期
$config['active']['checkActValidFunc'] = function ($gameId, $actNo, $startTime, $endTime) {
	if( strtotime($startTime) > time() ) { 
		// jsonpEcho($res = 0, $msg = '', $data = array(), $isExit = TRUE)
	    jsonpEcho(502, '活动还未开始');
	}
	if( strtotime($endTime) < time() ) { 
	    jsonpEcho(503, '活动已经结束');
	}
};

//配置需要登录才能操作的action, array('controller/action1','controller/action2')
//默认启用，只要配置了，就会自动检查
$config['accessFilter']['checkLogin']['url'] = array('Default/handleGameApiDemo');
//未登录情况下，回调处理
$config['accessFilter']['checkLogin']['checkFaildFunc'] = function($config, $result) {
    jsonpEcho(101, '您的登录态失效，请尝试重新登录~');
};

//配置同一ip在1分钟内请求次数达到多少，即认为是异常访问, 设置为30次, 默认检查所有请求。
//默认启用，只要配置了，就会自动检查
$config['accessFilter']['checkDDos']['maxRequest'] = 30;
//允许的请求，不检查checkDDos，array('controller/action1','controller/action2')
$config['accessFilter']['checkDDos']['allowRequest'] = array('Default/handleGameApiDemo');
$config['accessFilter']['checkDDos']['checkFaildFunc'] = function($config, $result) {
    jsonpEcho(100, 'Forbidden to access DDos');
}; 

/// 下面是配置奖品的检查规则, 由 _lp\core\lib\rule\ActiveRule类实现
/// 如果ActiveRule类的检查项无法满足需求，可以在 lib\act\checkRule目录下创建检查规则类

//giftPacketRule下标为奖品id，奖品下有多个检查项，根据配置的先后顺序执行检查。
// $activeRule = new ActiveRule($config['giftPacketRule'][1], array('serverId' => intval($serverId)));
// AccessFilter::check($activeRule);
$config['giftPacketRule'] = array(
	'1'=>array(
		//检查手机绑定
		'checkBindPhone'=>array(
			'checked'=>true,
			//一般情况下，使用默认即可，如果有特殊需求，可重写检查方法
			//'checkFunc'=>function($config, $result) {},
			'checkFaildFunc'=>function($config, $result) {
    			jsonpEcho(1, '亲，您还没有绑定手机' );
			},
			'checkSuccessFunc'=>function($config, $result) {},
		),
		//检查礼包的最大限额
		'checkTotalGiftNum'=>array(
			'num'=>1000,
			'checkFaildFunc'=>function($config, $result) {
    			jsonpEcho(2, '奖品已经发完了', $result);
			},
			'checkSuccessFunc'=>function($config, $result) {},
		),
		//检查角色等级是否达到要求
		'checkRoleLevel'=>array(
			'min'=>10,  //最小等级，通常为达到x等级才能参与活动，最小等级即为x等级，必须配置
			'max'=>999, //最大等级，可以不配置
			'checkFaildFunc'=>function($config, $result) {
    			jsonpEcho(3, '亲，您还没有达到'. $config['min'].'等级，请继续游戏' , $result);
			},
			'checkSuccessFunc'=>function($config, $result) {},
		),
		//检查指定时间内奖品的限额，允许用户领取的数量
		'checkUserGiftNum'=>array(
			'd20'=>10, //dx,d代表天为单位，x为天数，d20=10,代表20天内，用户可领取此奖品的数量是10。
			'd1'=>1,  // 用户在1天内可领取1个奖品
			'checkFaildFunc'=>function($config, $result) {
    			jsonpEcho(4, '您已经领过该奖品了', $result);
			},
			'checkSuccessFunc'=>function($config, $result) {},
		),
		//检查指定时间内奖品在指定区服里的限额，指定区服，允许用户领取的数量
		//在调用时候传参指定区服，$activeRule = new ActiveRule($giftRule, array('serverId' => intval($serverId)));
        // AccessFilter::check($activeRule);
		'checkServerGiftNum'=>array(
			'd18'=>4,  // 18天内可领取4个奖品
			'd30'=>20, // 30天内可领取20个奖品
			'd1'=>1,   // 在1天内可领取1个奖品
			'checkFaildFunc'=>function($config, $result) {
    			jsonpEcho(5, '您已经领过该奖品了', $result);
			},
			'checkSuccessFunc'=>function($config, $result) {},
		),
		
	),
	'2'=>array()
);

return $config;

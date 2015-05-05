<?php
/**
 * @author caiwenxiong <caiwenxiong@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace act_app\y_2015\demo1\controller;

use _lp\lp;
use _lp\core\lib\FileLog;
use _lp\core\lib\AccessFilter;
use _lp\core\lib\rule\ActiveRule;
use _lp\core\lib\rule\AccessRule;
use _lp\core\lib\GameApi;

use lib\api\ActApi;

use model\AddressInfo;
use model\PhoneBind;

use controllers\BaseController;
use lib\act\giftPacket\GiftPacket;

/**
 * 活动业务控制器，处理业务逻辑
 */
class DefaultController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function doChongHook($data)
    {
         
    }

    public function doVipChongHook($data)
    {
         
    }

    /**
     * 基本常用操作，比如，获取用户信息，活动信息，配置信息，写日志
     * 
     * @return [type] [description]
     */
    public function baseInfo()
    {
        //lp::App()
        $lpApp = lp::App();
        //var_dump($lpApp);

        //读取user信息，参看 _lp\core\lib\User类
        $user = lp::App()->user;
        var_dump($user);
        
        //读取act活动相关信息, actNo, startTime, endTime, gameId, 参看 _lp\core\lib\Act类
        $act = lp::App()->act;
        var_dump($act);

        //获得memcached实例
        $memcached = lp::App()->memcached;
        var_dump($memcached);
        
        //读取配置信息,包含所有配置信息
        $config = lp::App()->config;
        //var_dump($config);

        //读取规则配置项
        lp::App()->getConfigItem('accessFilter');
        lp::App()->getConfigItem('giftPacketRule');


        //写日志操作 lp::log()->write($level, $msg = "", $fileName = "") 
        // $level 是预定义的常量，可参看 _lp\core\lib\FileLog类
        // 日志将写在活动目录下，act_app/y_2015/demo1/logs/
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, "message", "test");
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, array('abc'=>'1111'));
         
        //也可以按照下面方式写日志
        //lp::log()->notice(), lp::log()->info(), lp::log()->debug(), lp::log()->api(), lp::log()->access()
        lp::log()->setLogFile('abc'); //自定义日志文件名，文件名格式： app.abc.20150304.log
        lp::log()->error('message test 33');


        //输出, 参看 _lp\core\function\Core.function.php
        //jsonEcho($res = 0, $msg = '', $data = array(), $isExit = TRUE) 
        jsonEcho(1, 'json echo msg', array(), TRUE);

        //jsonpEcho($res = 0, $msg = '', $data = array(), $isExit = TRUE)
        jsonpEcho(1, '亲，您还没有绑定手机' );

    }


    /**
     * 处理数据demo, 包括数据的增删改查
     * 
     */
    public function handleDataDemo()
    {

        $addressInfo = new AddressInfo();

        //增加数据：
        //方法一：
        $rs = $addressInfo->insert(array(
          'userid'=>lp::App()->user->userId, 
          'name'=>lp::App()->user->userName,
          'act'=>lp::App()->act->actNo, 
          'mobile'=>15815582910, 
          'addtime'=>$addressInfo->addtime,
          'ip'=>$addressInfo->ip, 
          'address'=>'absc test'
        ));
    
        //获取插入的id
        $addressInfo->insertId();

        //方法二：
        $addressInfo->act = lp::App()->act->actNo;
        $addressInfo->userid = lp::App()->user->userId;
        $addressInfo->address = "";
        $addressInfo->mobile = '13534116242';
        $addressInfo->telephone = "";
        $addressInfo->zipcode = '123131';
        $addressInfo->addtime =  $addressInfo->addtime;
        $addressInfo->ip = $addressInfo->ip;
        $addressInfo->status = 1;
        //$addressInfo->save();
        $addressInfo->clearAttribules();//防止变量混乱
        
        //获取插入的id
        $addressInfo->insertId();

        //更新数据
        // 更新id=10的记录，把telephone字段值改为0755123131
        $addressInfo->update(array('id' => 10), array( 'telephone' => '0755123131'));

        //查询数据
        //获取所有数据
        $addressInfo->findAll();
        
        //获取指定数据, find($conditions = array(), $extSql = "")
        $addressInfo->find(array('act'=>lp::App()->act->actNo));

        //根据sql获取数据, findBySql($sql, $conditions = array(), $extSql = "")
        $sql = 'SELECT count(id) FROM '. $addressInfo->getTableName();
        $addressInfo->findBySql($sql, array('act'=>lp::App()->act->actNo) );

        //判断指定数据是否存在
        $addressInfo->exists(array('id' => 10));

        //获取匹配条件数据的条数
        $rs = $addressInfo->count(array('act' => lp::App()->act->actNo));


        //删除数据
        //方法一：
        $addressInfo->deleteAll(array('id' => 8));
        
        //方法二：
        $addressInfo->clearAttribules();
        $addressInfo->id = 9;
        $addressInfo->delete();


        //$addressInfo->getDbConnection() 将返回一个  _lp\core\lib\DB类的实例，也可以直接调用DB类方法。
 
        $db = $addressInfo->getDbConnection();

        $rs = $db->get(array('act'=>lp::App()->act->actNo));

        $rs = $db->getRow(array('act'=>lp::App()->act->actNo));

        $rs = $db->getBySql($sql, array('act'=>lp::App()->act->actNo) );

        $rs = $db->getRowBySql($sql, array('act'=>lp::App()->act->actNo) );

    }


    /**
     * 检查是否绑定手机demo
     *
     * 绑定手机检查项可以在奖品规则里配置
     * 
     */
    public function checkBindPhoneDemo()
    {
        // 针对某个奖品需要绑定手机的情况，可以在奖品的规则配置里，添加绑定手机的配置
        $giftid = v('giftid');

        //获取奖品规则的配置项
        $giftPacketRules = lp::App()->getConfigItem('giftPacketRule');

        if (!isset($giftPacketRules[$giftid])) 
        {
            //写日志
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '礼包规则为空!giftid=' . $giftid);
        } else {
            //获取奖品规则
            $giftRule = $giftPacketRules[$giftid];
            $activeRule = new ActiveRule($giftRule);
            AccessFilter::check($activeRule);
        }

        
        // 针对整个活动都需要绑定手机的情况，可以直接操作数据类，查询是否已经绑定
        $phoneBind = new PhoneBind();
        if( !$phoneBind->isBind() )
        {
          jsonpEcho(1, '亲，您还没有绑定手机' );
        }
        
    }

    /**
     * 调用GameApi接口的示例
     *
     * use _lp\core\lib\GameApi;
     * use lib\api\ActApi;
     *
     * GameApi接口将按原样返回，不对接口返回值做处理
     *
     * lib\api\ActApi类是对GameApi的二次封装，根据业务需求对GameApi接口返回值做特殊处理
     *
     * 注：GameApi接口都是对用户进行操作，_lp\core\lib\User类有相应的方法可调用。
     * 
     */
    public function handleGameApiDemo()
    {

      //查询登录日期
      $parms = array(
          'userid' => lp::App()->user->userId,
          'from' => '20140301',
          'to' => '20150301',
          'gameid' => lp::App()->act->gameId,
          'serverid' => 1,
          'noLogRtnArr' => array()
      );
      $rs = GameApi::getGameLoginDate($parms);
      $rs = ActApi::getGameLoginDate($parms);

      //查询游戏信息
      $parms = array(
          'userid' => lp::App()->user->userId,
          'gameid' => lp::App()->act->gameId,
          'serverid' => 1,
          'noLogRtnArr' => array()
      );
      $rs = GameApi::getGameInfo($parms);
      $rs = ActApi::getGameLevel($parms);
      $rs = lp::App()->user->getLevel( lp::App()->act->gameId, 1);

      //查询在线时长
      $parms = array(
          'username' => lp::App()->user->userName,
          'sessionid' => lp::App()->user->sessionId,
          'gameid' => lp::App()->act->gameId,
          'serverid' => 1,
          'noLogRtnArr' => array()
      );
      $rs = GameApi::getUserOnlineTime($parms);

      //查询连续登录游戏时间
      $parms = array(
          'userid' => lp::App()->user->userId,
          'serverid' => 1,
          'gameid' => lp::App()->act->gameId,
          'days' => 2,
          'noLogRtnArr' => array()
      );
      $rs = GameApi::isContinuousLogin($parms);
      $rs = lp::App()->user->isContinuousLoginDays(lp::App()->act->gameId, 1, 3);

      //发送礼包
      $parms = array(
          'userid' => lp::App()->user->userId,
          'username'=> lp::App()->user->userName,
          'serverid' => 1,
          'gameid' => '000186',
          'batid' => 1374,
          'noLogRtnArr' => array()
      );
      //$rs = GameApi::sendGameCard($parms);
      
      //查询牛x积分
      $parms = array(
          'userid' => lp::App()->user->userId,
          'noLogRtnArr' => array()
      );
      $rs = GameApi::searchJifen($parms);
      
      //判断是否为金钻用户
      $parms = array(
          'userid' => lp::App()->user->userId
      );
      $rs = ActApi::isNiuxVip($parms);
      $rs = lp::App()->user->getUserType();

      //获取用户的首次登录时间
      $rs = lp::App()->user->getFirstLoginTime(lp::App()->act->gameId);

      //获取用户的首次支付时间，包括平台，游戏，区服
      $rs = lp::App()->user->getFirstPayTime();

      //获取用户在某段时间内登录游戏的日期
      $rs = lp::App()->user->getLoginDate('20140101','20150101',lp::App()->act->gameId);

      var_dump($rs);
    }

  
    public function getGiftDemo()
    {

    }

    public function lotDemo()
    {

    }

    public function test()
    {
        
//        $a = lp::App()->act->isValidAct(function($gameId, $actNo, $startTime, $endTime) { 
//              if( empty($gameId) || empty($actNo) || empty($startTime) || empty($endTime) ) { 
//                  return FALSE;
//              }
//              return TRUE;
//        });
//       $serverId = 1;
//        
//       $data = array( 'userId' => lp::App()->user->userId, 
//                      'userName' => lp::App()->user->userName, 
//                      'actNo' => lp::App()->act->actNo,
//                      'gameId' => lp::App()->act->gameId,
//                      'serverId' => $serverId,
//                      'moudleId' => 1,
//                      'packetId' => 1272,   // gift_id 或者 bat_id
//                      'packetName' => '测试礼包',
//                      'getMore' => FALSE
//        );
//        
//        $giftPacket1 = GiftPacket::createGameCodePacket($data);
//        
//        lp::App()->act->addGiftPacket($giftPacket1);
//        
//        $results = lp::App()->act->sendGiftPacket(array($giftPacket1->packetKey));
//        var_dump($giftPacket1->packetName , $results[$giftPacket1->packetKey]);
        
        
        //$allPackets = lp::App()->act->getGiftPacket();
        //var_dump($results);
//        var_dump($a);
        
//        new AccessRule();
        
//        $serverId = 1;
//        $giftPackets = lp::App()->getConfigItem('giftPacket');
//        if( FALSE !==  ( $giftPackets_data_1 =  $giftPackets[$giftid](lp::App()->user->userId, lp::App()->user->userName, lp::App()->act->actNo, lp::App()->act->gameId, $serverId) ) ) { 
//            $giftPacket1 = GiftPacket::createGameCodePacket($giftPackets_data_1);
//            lp::App()->act->addGiftPacket($giftPacket1);
//        }
//        //$results = lp::App()->act->sendGiftPacket(array($giftPacket1->packetKey));
//        $data = lp::App()->act->getGiftPacket();
//        var_dump($data);
        
        
//        $activeRule = new AccessRule();
//        $result = AccessFilter::check($activeRule);
//        var_dump($result);
        
        //GiftPacket::createCommonPacket()
//        lp::loader()->loadFunction('common');
//        var_dump(pinyin("呵呵呵呵呵呵哈哈哈哈哈哈哈"));
//        
//        $a = lp::loader()->loadConfig("db");
//        var_dump($a);
        
        //lp::App()->memcached
        //lp::App()->db;
        

//        $addressInfo = new \model\AddressInfo;
        //$addressInfo->
        //$addressInfo->findAll();
        //$addressInfo->find(array('act' => 'test'));
        //$addressInfo->exists(array('id' => 1));
//        $addressInfo->update(array('id' => 1), array(
//            'telephone' => '0755123131'
//        ));
//        $addressInfo->count(array(
//            'act' => 'test'
//        ));
        $giftid = v('giftid');
        $serverId = 1;
        $giftPacketRules = lp::App()->getConfigItem('giftPacketRule');
        if (!isset($giftPacketRules[$giftid])) {
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '礼包规则为空!giftid=' . $giftid);
        } else {
            $giftRule = $giftPacketRules[$giftid];
            $activeRule = new TestCheck($giftRule, array('serverId' => intval($serverId)));
            AccessFilter::check($activeRule);
        }


        //assert();
        
//       $data = array( 'userId' => lp::App()->user->userId, 
//                      'userName' => lp::App()->user->userName, 
//                      'actNo' => lp::App()->act->actNo,
//                      'gameId' => lp::App()->act->gameId,
//                      'serverId' => $serverId,
//                      'id' => 1,
//                      'moudleId' => 1,
//                      'packetId' => 1272,   // gift_id 或者 bat_id
//                      'packetName' => '测试礼包',
//                      'getMore' => FALSE
//        );
//        $giftPacket1 = GiftPacket::createGameCodePacket($data);
//        lp::App()->act->addGiftPacket($giftPacket1);
//        $results = lp::App()->act->sendGiftPacket($giftPacket1->packetKey);
//        var_dump($giftPacket1->packetName , $results);
        
        $data = array(
            'userId' => lp::App()->user->userId,
            'id' => 1,
            'userName' => lp::App()->user->userName,
            'actNo' => lp::App()->act->actNo,
            'moudleId' => 1,
            'packetId' => 1634,
            'gameId' => lp::App()->act->gameId,
            'packetName' => '测试通用礼包',
            'serverId' => 1,
            'getMore' => true,
            'noLogRtnArr' => array()
        );
        //$packet1 = GiftPacket::createCommonPacket($data);
        $packet1 = GiftPacket::createPacket(GiftPacket::PACKET_TYPE_GAMECODE, $data);
        lp::App()->act->addGiftPacket($packet1);
        $result = lp::App()->act->sendGiftPacket($packet1->packetKey);
        
        var_dump($result[$packet1->packetKey]);
    }
    
    
    public function lottery()
    {
        
        $data = array( 'userId' => lp::App()->user->userId,
                      'userName' => lp::App()->user->userName,
                      'actNo' => lp::App()->act->actNo,
                      'gameId' => lp::App()->act->gameId,
                      'serverId' => 1,
                      'id' => 1,
                      'moudleId' => 1,
                      'packetId' => 1272,
                      'packetName' => '测试礼包',
                      'getMore' => false,
                      'rate' => 0.01
        );
        $giftPacket1 = GiftPacket::createGameCodePacket($data);
        lp::App()->act->addGiftPacket($giftPacket1);
        
        $data_2 = array( 'userId' => lp::App()->user->userId,
                      'userName' => lp::App()->user->userName,
                      'actNo' => lp::App()->act->actNo,
                      'gameId' => lp::App()->act->gameId,
                      'serverId' => 2,
                      'id' => 2,
                      'moudleId' => 1,
                      'packetId' => 1272,
                      'packetName' => '测试礼包2',
                      'getMore' => false,
                      'rate' => 0.03
        );
        $giftPacket2 = GiftPacket::createGameCodePacket($data_2);
        lp::App()->act->addGiftPacket($giftPacket2);
        
        $data_3 = array( 'userId' => lp::App()->user->userId,
                      'userName' => lp::App()->user->userName,
                      'actNo' => lp::App()->act->actNo,
                      'gameId' => lp::App()->act->gameId,
                      'serverId' => 3,
                      'id' => 3,
                      'moudleId' => 1,
                      'packetId' => 1272,
                      'packetName' => '测试礼包3',
                      'getMore' => false,
                      'rate' => 0.01
        );
        $giftPacket3 = GiftPacket::createGameCodePacket($data_3);
        lp::App()->act->addGiftPacket($giftPacket3);
        
        $data_4 = array( 'userId' => lp::App()->user->userId,
                      'userName' => lp::App()->user->userName,
                      'actNo' => lp::App()->act->actNo,
                      'gameId' => lp::App()->act->gameId,
                      'serverId' => 4,
                      'id' => 4,
                      'moudleId' => 1,
                      'packetId' => 1272,
                      'packetName' => '测试礼包4',
                      'getMore' => false,
                      'rate' => 0.02
        );
        $giftPacket4 = GiftPacket::createGameCodePacket($data_4);
        lp::App()->act->addGiftPacket($giftPacket4);
        
                $data_5 = array( 'userId' => lp::App()->user->userId,
                      'userName' => lp::App()->user->userName,
                      'actNo' => lp::App()->act->actNo,
                      'gameId' => lp::App()->act->gameId,
                      'serverId' => 5,
                      'id' => 5,
                      'moudleId' => 1,
                      'packetId' => 1272,
                      'packetName' => '测试礼包5',
                      'getMore' => false,
                      'rate' => 0.8
                );
                $giftPacket5 = GiftPacket::createGameCodePacket($data_5);
                lp::App()->act->addGiftPacket($giftPacket5);
        
        
        
                $data_6 = array( 'userId' => lp::App()->user->userId,
                      'userName' => lp::App()->user->userName,
                      'actNo' => lp::App()->act->actNo,
                      'gameId' => lp::App()->act->gameId,
                      'serverId' => 6,
                      'id' => 6,
                      'moudleId' => 1,
                      'packetId' => 1272,
                      'packetName' => '测试礼包6',
                      'getMore' => false,
                      'rate' => 0.02
                );
                $giftPacket6 = GiftPacket::createGameCodePacket($data_6);
                lp::App()->act->addGiftPacket($giftPacket6);
        
        
                $data_7 = array( 'userId' => lp::App()->user->userId,
                      'userName' => lp::App()->user->userName,
                      'actNo' => lp::App()->act->actNo,
                      'gameId' => lp::App()->act->gameId,
                      'serverId' => 7,
                      'id' => 7,
                      'moudleId' => 1,
                      'packetId' => 1272,
                      'packetName' => '测试礼包7',
                      'getMore' => false,
                      'rate' => 0.01
                );
                $giftPacket7 = GiftPacket::createGameCodePacket($data_7);
                lp::App()->act->addGiftPacket($giftPacket7);
        
                $data_8 = array( 'userId' => lp::App()->user->userId,
                      'userName' => lp::App()->user->userName,
                      'actNo' => lp::App()->act->actNo,
                      'gameId' => lp::App()->act->gameId,
                      'serverId' => 8,
                      'id' => 8,
                      'moudleId' => 1,
                      'packetId' => 1272,
                      'packetName' => '测试礼包8',
                      'getMore' => false,
                      'rate' => 0.1
                );
                $giftPacket8 = GiftPacket::createGameCodePacket($data_8);
                lp::App()->act->addGiftPacket($giftPacket8);
         
        
                $lottery = new Lottery();
                $lottery->setGiftPacket(array(
                $giftPacket1->getAttributes(),
                $giftPacket2->getAttributes(),
                $giftPacket3->getAttributes(),
                $giftPacket4->getAttributes(),
                $giftPacket5->getAttributes(),
                $giftPacket6->getAttributes(),
                $giftPacket7->getAttributes(),
                $giftPacket8->getAttributes(),
                ));
                $result = $lottery->doLottery();
                var_dump($result);
                var_dump(lp::App()->act->giftPackets[$result]->packetName);
        
                var_dump(lp::App()->act->sendGiftPacket($result));
        
        
    }
}

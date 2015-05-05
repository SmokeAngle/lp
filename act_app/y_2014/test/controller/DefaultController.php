<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 */
namespace act_app\y_2014\test\controller;

use _lp\lp;
use _lp\core\lib\FileLog;
use _lp\core\lib\Smtp;
use _lp\core\lib\HttpsqsClient;
use _lp\core\lib\AccessFilter;
use _lp\core\lib\rule\ActiveRule;
use _lp\core\lib\Lottery;
use lib\act\checkRule\TestCheck;
use _lp\core\lib\rule\AccessRule;
use model\AddressInfo;
use model\PhoneBind;
use controllers\BaseController;
use lib\act\giftPacket\GiftPacket;

class DefaultController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function doChongHook()
    {
            //{"_p":"2015:kyzqlot","a":"ChongHook","orderid":"20150116138020918438","actno":"kyzqlot","userid":"123540516","totalmoney":"1.0","time":"1421380253","gameid":"00193","serverid":"2","sign":"068f61acee9e482952c479f133d0420a","roleid":"","rolename":"","username":"youxiceshi","extparam":"","payMoney":"1.0","vipfate":"null"}
            
            $a = '{"_p":"2015:kyzqlot","a":"ChongHook","orderid":"20150116138020918438","actno":"kyzqlot","userid":"123540516","totalmoney":"1.0","time":"1421380253","gameid":"00193","serverid":"2","sign":"068f61acee9e482952c479f133d0420a","roleid":"","rolename":"","username":"youxiceshi","extparam":"","payMoney":"1.0","vipfate":"null"}';
            
            $b = json_decode($a, true);
              $totalmoney = $b['totalmoney'];
            $count = floor($totalmoney/1);
            print_r($count);
            die();
            
            xlog(json_encode($dataResult), 'notice', 'chongHook_test');
            $totalmoney = $dataResult['totalmoney'];
            $count = floor($totalmoney/10);
            xlog($count, 'notice', 'chongHook_test');
            require_once AROOT . DS . 'model' . DS . 'dbModel.lotinfo.php';
            $lotInfo = new Lotinfo();
            $data = $lotInfo->getRow(array(
                'userid' => $this->userid,
                'act' => $this->act
            ));
            xlog(json_encode($data), 'notice', 'chongHook_test');
            if (false !== $data) {
                $lotInfo->update(array('userid' => $this->userid, 'act' => $this->act), array(
                    'totaltimes' => $count + intval($data['totaltimes']),
                    'lastaddtime' => date('Y-m-d H:i:s', time())
                ));
            }
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
    
    public function testPacket() { 
        $giftPacketRules = lp::App()->getConfigItem('giftPacketRule');
        //$serverId = 10;
        $data = array( 'id' => 1, 'moudleId' => 1, 'packetId' => 1634, 'packetName' => '测试通用礼包', 'serverId' => 1, 'getMore' => TRUE, 'noLogRtnArr' => array() );
        $packet1 = GiftPacket::createPacket(GiftPacket::PACKET_TYPE_GAMECODE, $data)->addRule('lib\act\checkRule\TestCheck', $giftPacketRules[1], array('aaa' => intval(1)));
        
        //var_dump($packet1);
        lp::App()->act->addGiftPacket($packet1);
        $result = lp::App()->act->sendGiftPacket($packet1->packetKey);
        var_dump($result);
        
    }

    public function lottery()
    {
        
        $data = array( 'serverId' => 1, 'id' => 1, 'moudleId' => 1, 'packetId' => 1272, 'packetName' => '测试礼包', 'getMore' => false, 'rate' => 0.01);
        $giftPacket1 = GiftPacket::createGameCodePacket($data)->addRule('\lib\act\checkRule\TestCheck', array(
            'checkTotalGiftNum' => array( 'num' => 1,
                'checkFaildFunc' => function($config, $result){                    return TRUE; },
                'checkSuccessFunc' => function($config, $result) {                    return TRUE; }
            )
        ));
        lp::App()->act->addGiftPacket($giftPacket1);
        
        $data_2 = array( 'serverId' => 2, 'id' => 2, 'moudleId' => 1, 'packetId' => 1272, 'packetName' => '测试礼包2', 'getMore' => false, 'rate' => 0.03);
        $giftPacket2 = GiftPacket::createGameCodePacket($data_2);
        lp::App()->act->addGiftPacket($giftPacket2);
        GiftPacket::createPacket(GiftPacket::PACKET_TYPE_NIUX_CASH, $data_2);
        
        
        $data_3 = array( 'serverId' => 3, 'id' => 3, 'moudleId' => 1, 'packetId' => 1272, 'packetName' => '测试礼包3', 'getMore' => false, 'rate' => 0.01 );
        $giftPacket3 = GiftPacket::createGameCodePacket($data_3);
        lp::App()->act->addGiftPacket($giftPacket3);
        
        $data_4 = array( 'serverId' => 4, 'id' => 4, 'moudleId' => 1, 'packetId' => 1272, 'packetName' => '测试礼包4', 'getMore' => false, 'rate' => 0.02 );
        $giftPacket4 = GiftPacket::createGameCodePacket($data_4);
        lp::App()->act->addGiftPacket($giftPacket4);
        
        $data_5 = array( 'serverId' => 5, 'id' => 5, 'moudleId' => 1, 'packetId' => 1272, 'packetName' => '测试礼包5', 'getMore' => false, 'rate' => 0.8 );
        $giftPacket5 = GiftPacket::createGameCodePacket($data_5);
        lp::App()->act->addGiftPacket($giftPacket5);
        
        $data_6 = array( 'serverId' => 6, 'id' => 6, 'moudleId' => 1, 'packetId' => 1272, 'packetName' => '测试礼包6', 'getMore' => false, 'rate' => 0.02 );
        $giftPacket6 = GiftPacket::createGameCodePacket($data_6);
        lp::App()->act->addGiftPacket($giftPacket6);
        
        $data_7 = array( 'serverId' => 7, 'id' => 7, 'moudleId' => 1, 'packetId' => 1272, 'packetName' => '测试礼包7', 'getMore' => false, 'rate' => 0.01 );
        $giftPacket7 = GiftPacket::createGameCodePacket($data_7);
        lp::App()->act->addGiftPacket($giftPacket7);
        
        $data_8 = array('serverId' => 8, 'id' => 8, 'moudleId' => 1, 'packetId' => 1272, 'packetName' => '测试礼包8', 'getMore' => false, 'rate' => 0.1 );
        $giftPacket8 = GiftPacket::createGameCodePacket($data_8);
        lp::App()->act->addGiftPacket($giftPacket8);
         
        
        $lottery = new Lottery();
        $lottery->setGiftPacket(lp::App()->act->getGiftPacket());

        
        $result = $lottery->doLottery();
        if( FALSE !== $result ) { 
            var_dump(lp::App()->act->giftPackets[$result]->packetName);
            var_dump(lp::App()->act->sendGiftPacket($result));  
        } else { 
            var_dump($result);
        }

    }
    
    public function test2() { 

    }
}

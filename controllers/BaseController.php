<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 */
namespace controllers;

use model\PayCallBack;
use model\PhoneBind;
use _lp\core\lib\FileLog;
use _lp\core\controller\CoreController;
use _lp\lp;

class BaseController extends CoreController
{

    public function __construct()
    {
        parent::__construct();
    }
        
        
    /**
    *  充值回调
    */
    public function chongHook()
    {
            lp::log()->write(FileLog::LOG_LEVEL_INFO, $_SERVER['QUERY_STRING'], 'chongHook');
            
            $data = $_GET;
            
            
            $ipLimits = array('123.151.31.96','111.161.125.253','10.1.9.149','111.161.24.173','123.150.185.179','111.161.24.179','123.150.185.180',
                              '111.161.24.180','123.150.185.181','111.161.24.181','10.1.3.32', '123.151.31.232',
                              '125.39.36.232','10.1.3.33', '123.151.31.233', '125.39.36.233','192.168.91.210');
            $ip = getip();
        if (in_array($ip, $ipLimits)) {
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, '!!! IP Forbidden to access ChongHook : '. $ip, 'chongHook');
            jsonFormatEcho(
                array(
                        'msg' => '!!! IP Forbidden to access ChongHook : '. $ip ,
                        'code' => 0
                    ),
                true
            );
        }
            
        if (empty($data['orderid']) || empty($data['userid']) || empty($data['time'])) {
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, 'parms error: '. print_r($data, true), 'chongHook');
            jsonFormatEcho(array(
                'msg' => 'CallBack parms error',
                'code' => 0
                ), true);
        }
            
            $payMoney = isset($data['totalmoney'])?$data['totalmoney']:0;
            $payTime = isset($data['time'])?$data['time']:'';
            $payUid = isset($data['userid'])?$data['userid']:0;
            $payUserName = isset($data['username']) ? $data['username'] : '';
            $orderId = isset($data['orderid'])?$data['orderid']:'';
            $serverId = isset($data['serverid'])?$data['serverid']:0;
            $actNo = isset($data['actno']) ? $data['actno'] : '';
            $gameId = isset($data['gameid']) ? $data['gameid'] : '';
            $signCode = 'QOwqK0w3mmsr9AwT';
            
            $sign = isset($data['sign']) ? $data['sign'] : '';
            //检测参数跟sign签名
            $str = $actNo.$orderId.$payUid.$gameId.$serverId.$payMoney.$payTime.$signCode;
            
        if ($sign != md5($str) || empty($payTime) || empty($payUid)) {
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, '[ChongHook] sign param error : '. print_r($data, true), 'chongHook');
            jsonFormatEcho(array(
                'msg' => '[ChongHook] Sign param error : ' . print_r($data, true) ,
                'code' => 0
                ), true);
        }
            
            $payCallBack = new PayCallBack();
        if (false !== $payCallBack->exists(array( 'orderid' => $orderId ))) {
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'has payed, orderid :' . $data['orderid'], 'chongHook');
            jsonFormatEcho(array(
                'msg' => '[ChongHook] already proccessed, order exist, order id :' . $data['orderid'],
                'code' => 0
                ), true);
        } else {
                //插入充值记录数据
                $time = date('Y-m-d H:i:s', $payTime);
                $payCallBack->insert(
                    array(
                        'userid'=>$payUid,
                        'username'=>$payUserName,
                        'act'=>$actNo,
                        'gameid'=>$gameId,
                        'serverid'=>$serverId,
                        'money'=>$payMoney,
                        'orderid'=>$orderId,
                        'roleid'=>$data['roleid'],
                        'addtime'=>$time
                        )
                );
                    $this->doChongHook($data);
                    jsonFormatEcho(array(
                            'msg' => 'Rec success~',
                            'code' => 0
                        ), true);
        }
    }
    

//    public function doChongHook( $data = array() ) { 
//        
//    }

    /**
     * 金钻充值回调
     */
    public function vipChongHook()
    {
        lp::log()->write(FileLog::LOG_LEVEL_INFO, $_SERVER['QUERY_STRING'], 'vipChongHook');
        $data = $_GET;
        $ipLimits = array('10.1.9.149','111.161.24.173','123.150.185.179','111.161.24.179','123.150.185.180','111.161.24.180','123.150.185.181','111.161.24.181',
        '10.1.3.32', '123.151.31.232', '125.39.36.232',
        '10.1.3.33', '123.151.31.233', '125.39.36.233','112.80.23.165','61.155.183.165','112.80.23.250','61.155.183.250','112.80.23.167','61.155.183.167','112.80.23.250','61.155.183.250');

        $signCode = 'OGKosg2ORyJjbcyk';
        $ip = getip();
        if (!in_array($ip, $ipLimits)) {
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, '!!! IP Forbidden to access ChongHook : '. $ip, 'vipChongHook');
            jsonFormatEcho(
                array(
                            'msg' => '!!! IP Forbidden to access ChongHook : '. $ip ,
                            'code' => 0
                        ),
                true
            );
        }
            
        $totalMoney = isset($data['totalmoney'])?$data['totalmoney']:0;
        $payMoney = isset($data['payMoney'])?$data['payMoney']:0;
        $payTime = isset($data['time'])?$data['time']:'';
        $payUid = isset($data['userid'])?$data['userid']:0;
        $payUserName = isset($data['username']) ? $data['username'] : '';
        $orderId = isset($data['orderid'])?$data['orderid']:'';
        $timeType = isset($data['timeType'])?$data['timeType']:'';
        $numValue = isset($data['numValue'])?$data['numValue']:'';
        $extParam = isset($data['extparam'])?$data['extparam']:'';
        $actNo = isset($data['actno']) ? $data['actno'] : '';
        $signCode = 'OGKosg2ORyJjbcyk';
     
        //检测参数跟sign签名
        $str = $actNo.$orderId.$payUid.$totalMoney.$payMoney.$timeType.$numValue.$payTime.$signCode;
        if ($data['sign'] != md5($str) || $payTime=='' || $payUid==0) {
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, '[VipChongHook] sign param error : ' . json_encode($data), 'vipChongHook');
            jsonFormatEcho(
                array(
                            'msg' => '[VipChongHook] Sign param error :  '. json_encode($data),
                            'code' => 0
                        ),
                true
            );
        }
        
            //判断该订单是否记录
        $payCallBack = new PayCallBack();
        if (false !== $payCallBack->exists(array( 'orderid' => $orderId ))) {
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'has payed, orderid :' . $data['orderid'], 'chongHook');
            jsonFormatEcho(array(
                    'msg' => '[ChongHook] already proccessed, order exist, order id :' . $data['orderid'],
                    'code' => 0
            ), true);
        } else {
            $time = date('Y-m-d H:i:s', $data['time']);
            $payCallBack->insert(
                array(
                    'userid'=>$payUid,
                    'username'=>$payUserName,
                    'act'=>$actNo,
                    'money'=>$payMoney,
                    'orderid'=>$orderId,
                    'type'=>'niuxvip', //类型为金钻充值
                    'addtime'=>$time
                )
            );
            $this->doVipChongHook($data);
            jsonFormatEcho(array(
                                'msg' => 'Rec success~',
                                'code' => 0
            ), true);
        }
    }
    
//    public function doVipChongHook( $data = array() ) { 
//        
//    }
    
    public function sendPhoneVerifyCode()
    {
        
        if (empty(lp::App()->user->userId)) {
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, '[sendPhoneVerifyCode] 无法获取您的登录信息，请检查是否配置允许登录请求: ');
            jsonpEcho(-1, '无法获取您的登录信息');
        }
        $cell = v('phone');
        if (!is_numeric($cell)) {
                jsonpEcho(99, '亲，你的手机号码不符合要求哦~');
        }
        $verify = v('verifycode');
        if ($verify == '' || !checkVerify($verify)) {
            jsonpEcho(1001, '亲，您的验证码输入错误！');
        }        
        $phoneBind = new PhoneBind();
        if ($phoneBind->exists(array( 'userid' => lp::App()->user->userId, 'act' => lp::App()->act->actNo ))) {
            jsonpEcho(-1, '您已经绑定过手机了');
        }
        if ($phoneBind->exists(array('phone' => $cell, 'act' => lp::App()->act->actNo))) {
            jsonpOut(100, '该手机已经绑定过账号，请使用新手机号码重新绑定');
        }
        
        $msgSignKey = lp::App()->config['msgSignKey'];
        $msgApi = lp::App()->config['msgApi'];
        $signStr = lp::App()->user->userId . $msgSignKey;
        $sign = md5($signStr);
        
        $url = $msgApi . 'sendIdentityCode.do?userId='.lp::App()->user->userId.'&sign='.$sign.'&mobile='.$cell.'&game='.lp::App()->act->gameId;
            
        $retJson = curl($url);
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendPhoneVerifyCode');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendPhoneVerifyCode');

        $retArr = json_decode($retJson, true);
        $sendinfo =  $retArr['data'] ? $retArr['data'] : '';

        if (!isset($sendinfo['code'])) {
                //接口返回数据异常，比如 502 Bad Gateway
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url, 'sendPhoneVerifyCode');
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '!!! Send phone verify code return error: ' . $retJson, 'sendPhoneVerifyCode');
                jsonpOut(-1, '验证码发送失败，请返回重新绑定', $sendinfo);
        } elseif (isset($sendinfo['code']) && $sendinfo['code'] != '00') {
            if ($sendinfo['code'] != 13) {
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '!!! Send phone verify code return error: ' . $retJson, 'sendPhoneVerifyCode');
            }
                jsonpEcho(-1, '验证码发送失败，请返回重新绑定', $sendinfo, 'sendPhoneVerifyCode');
        }
        jsonpEcho(0, '', $sendinfo);
    }
    
    public function BindPhone()
    {
        
        if (empty(lp::App()->user->userId)) {
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, '[sendPhoneVerifyCode] 无法获取您的登录信息，请检查是否配置允许登录请求: ');
            jsonpEcho(-1, '无法获取您的登录信息');
        }
        $cell = v('phone');
        if (!is_numeric($cell)) {
                jsonpEcho(99, '亲，你的手机号码不符合要求哦~');
        }
        $phoneBind = new PhoneBind();
        if ($phoneBind->exists(array( 'userid' => lp::App()->user->userId, 'act' => lp::App()->act->actNo ))) {
            jsonpEcho(-1, '您已经绑定过手机了');
        }
        if ($phoneBind->exists(array('phone' => $cell, 'act' => lp::App()->act->actNo))) {
            jsonpOut(100, '该手机已经绑定过账号，请使用新手机号码重新绑定');
        }

        
        $phonecode = v('phonecode');
        $msgSignKey = lp::App()->config['msgSignKey'];
        $msgApi = lp::App()->config['msgApi'];
        $signStr = lp::App()->user->userId . $msgSignKey;
        $sign = md5($signStr);

        $url = $msgApi . 'checkMobileIdentifyCodeAndBind.do?userId='. lp::App()->user->userId .'&sign='.$sign.'&mobile='.$cell.'&identityCode='.$phonecode.'&game='.lp::App()->act->gameId;
        
        echo $url;
        $retJson = curl($url);

        //xlog($url, 'notice', 'BindPhone');
        //xlog($retJson, 'notice', 'BindPhone');
        
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'BindPhone');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'BindPhone');

        $retArr = json_decode(trim($retJson, 'callback()'), true);

        $sendinfo = $retArr['data'];
        if ($retArr['data']['code']=='00' || $retArr['data']['code']=='01') {
            //插入记录
            $phoneBind->insert(array(
                'userid'=>  lp::App()->user->userId,
                'username'=> lp::App()->user->userName,
                'gameid'=> lp::App()->act->gameId,
                'phone'=>$cell,
                'act'=> lp::App()->act->actNo,
                'ip'=> getip(),
                'addtime'=> date('Y-m-d H:i:s')
            ));

        } else {
            if ($retArr['data']['code'] !='99') {
                // code=99, 不好意思，您的验证码错误，请重新输入, 不写日志
//                xlog($url, 'api');
//                xlog('!!! Bind Phone return error: ' . $retJson, 'api');
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url, 'BindPhone');
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '!!! Bind Phone return error: ' . $retJson, 'BindPhone');
            }
            jsonpEcho(-1, '手机验证码错误，请返回重新绑定', $sendinfo);
        }
        jsonpEcho(0, '', $sendinfo);
        
    }
}

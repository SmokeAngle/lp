<?php
/**
 * 核心用户类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\lib;

use _lp\lp;
use _lp\core\lib\GameApi;
use SoapClient;

class User
{
    
    
    /**
     * @var string 用户新数字帐号
     */
    public $userNewNo;

    /**
     * @var string 用户内部id
     */
    public $userId;
    /**
     * @var string 用户名
     */
    public $userName;
    
    /**
     * @var string 用户昵称
     */
    public $userNickName;

    /**
     * @var int 用户类型  0 旧账号 1新账号
     */
    public $userType;
    
    /**
     * @var int 用户积分
     */
    public $score;
    
    /**
     * @var int 用户在线时间
     */
    public $onLineTime;

    /**
     * @var int 用户排名
     */
    public $order;

    /**
     * @var string 回话id
     */
    public $sessionId;
    
    /**
     * 跳转key
     */
    public $jumpKey;

    public function __construct()
    {
        $this->userId = isset($_COOKIE['userid']) ? intval($_COOKIE['userid']) : '';
        $this->sessionId = isset($_COOKIE['sessionid']) ? $_COOKIE['sessionid'] : '';
        $this->userName = isset($_COOKIE['usernick']) ? $_COOKIE['usrname'] : '';
        $this->userNickName = isset($_COOKIE['nickname']) ? $_COOKIE['nickname'] : '';
        $this->order = isset($_COOKIE['order']) ? intval($_COOKIE['order']) : '';
        $this->onLineTime = isset($_COOKIE['onlinetime']) ? intval($_COOKIE['onlinetime']) : '';
        $this->score = isset($_COOKIE['score']) ? intval($_COOKIE['score']) : '';
        $this->userType = isset($_COOKIE['usertype']) ? intval($_COOKIE['usertype']) : '';
        $this->userNewNo = isset($_COOKIE['usernewno']) ? intval($_COOKIE['usernewno']) : '';
        $this->jumpKey = isset($_COOKIE['jumpkey']) ? $_COOKIE['jumpkey'] : '';
    }
    
    //usrname usrname
    public function getUserInfo()
    {
        
        if (empty($this->userId) || empty($this->sessionId)) {
            return false;
        }
        $soapClient = new SoapClient(null, array(
            'location'=>'http://webservice.i.xunlei.com/icenter_service.php?m=user',
            'uri' => "http://test-uri/",
            'login' => 'icenter_*#$sdjfh2',
            'password' => '23231',
            'soap_version' => SOAP_1_1,
            //'cache_wsdl' => WSDL_CACHE_NONE
            //'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            //'connection_timeout' => 3,
        ));

        
        $result = $soapClient->checkSessionid($this->userId, $this->sessionId);

        //$result = '{"result":0,"data":{"usrid":"123540516","usrname":"youxiceshi","usrnewno":"822709400","vipstate":"","registertype":"","usrtype":"0","logintype":"","nickname":"\u6e38\u620f\u4eba\u751f","sex":"","account":"","totalorder":"","onlinetime":"","dlfilenum":"","dlbytes":"","savetime":"","deadlink":""}}';
        
        if (false != ( $resultArr = json_decode($result, true))) {
            if (isset($resultArr['result']) && ( $resultArr['result'] === 0 || $resultArr['result'] === 200 )) {
                if (isset( $resultArr['data']['usrid'] )  && intval($resultArr['data']['usrid']) === $this->userId) {
                    $this->userId = $resultArr['data']['usrid'];
                    $this->userName = $resultArr['data']['usrname'];
                    $this->userNickName = $resultArr['data']['nickname'];
                    $this->userType = $resultArr['data']['usrtype'];
                    $this->userNewNo = $resultArr['data']['usrnewno'];
                    return array(
                        'userId' => $this->userId,
                        'userName' => $this->userName,
                        'userNickName' => $this->userNickName,
                        'userType' => $this->userType,
                        'userNewNo' => $this->userNewNo
                    );
                }
            }
        }
        return false;
    }
    
    /**
     *  获取用户指定游戏区服的角色级别
     * @param string $gameId 游戏id
     * @param int $serverId  服务器id
     * @return mixed
     */
    public function getLevel($gameId = null, $serverId = null)
    {
        if (empty($gameId) || empty($serverId) || empty($this->userId)) {
            return false;
        }
        $info = array(
            'userid' => $this->userId,
            'gameid' => $gameId,
            'serverid' => $serverId
        );
         $levelResult = GameApi::getGameLevelWithoutLogin($info);
         return $levelResult;
    }
    
    
    /**
     *  获取当前用户类型
     * @return mixed   0      普通用户
     *                  1     金钻用户
     *                  2     金钻年费用户
     *                  false 接口错误
     */
    public function getUserType()
    {
        if (empty($this->userId)) {
            return false;
        }
        $info = array(
            'userid' => $this->userId
        );
        $userTypeResult = GameApi::checkNiuxVip($info);
        return $userTypeResult;
    }
    
    /**
     * 获取连续登陆的天数
     *
     * @param string $gameId 游戏id
     * @param int $serverId 服务器id
     * @param int $days 联系登陆天数
     * @return mixed
     */
    public function getContinuousLoginDays($gameId = null, $serverId = null, $days = null)
    {
        
        if (empty($this->userId) || empty($gameId) || empty($serverId) || empty($days)) {
            return false;
        }
        $info = array(
            'userid' => $this->userId,
            'gameid' => $gameId,
            'serverid' => $serverId,
            'days' => intval($days)
        );
        $loginResult = GameApi::getContinuousLogin($info);
        return $loginResult;
        
    }
    
    /**
     * 获取当前用户指定活动中获取的优惠券（京东购物卷）信息
     *
     * @param string $actno  活动编号
     * @return mixed
     *  callback({"rtn":0,"data":[{"userId":"123540516","seqId":824,"couponNo":"jdsdfs232d2jssdsfddd","couponPwd":"gfsd-sdf2-2222-sdaf","useTime":"2014-10-14 16:29:56","couponType":"jd","couponValue":50,"couponStatus":2,"useOrderNo":"201410141629563338_dtslot","useActNo":"dtslot"}]})
     */
    public function getCoupon($actno = "")
    {
        if (empty($actno) || empty($this->userId)) {
            return false;
        }
        $info = array(
            'userid' => $this->userId,
            'actno' => $actno
        );
        $couponInfoResult = GameApi::getCoupon($info);
        return $couponInfoResult;
    }
    
    /**
     * 获取用户第一次登陆游戏或者指定区服时间
     * <pre>
     *   lp:app()->user->getFirstLoginTime('00010') //获取第一次登陆游戏时间
     *   lp:app()->user->getFirstLoginTime('00010', 1) //获取第一次登陆区服时间
     * </pre>
     * @param type $gameId
     * @param type $serverId
     * @return mixed
     */
    public function getFirstLoginTime($gameId = null, $serverId = null)
    {
        
        if (empty($this->userId) || empty($gameId)) {
            return false;
        }
        $info = array(
            'userid' => $this->userId,
            'gameid' => $gameId,
        );
        if (!empty($serverId)) {
            $info['serverid'] = intval($serverId);
        }
        $loginTimeResult = GameApi::getFirstLoginTime($info);
        return $loginTimeResult;
        
    }
    
    /**
     * 获取用户第一次字符时间 若$gameId和$serverId 为空，则为查询平台支付时间
     *
     * <pre>
     * lp::app()->user->getFirstPayTime(); //获取平台第一次支付时间
     * lp::app()->user->getFirstPayTime('00100') //获取游戏第一次支付时间
     * lp:app()->user->getFirstPayTime('00100', 1) //获取区服第一次支付时间
     * </pre>
     * @param string $gameId 游戏id
     * @param int $serverId 服务器id
     * @return mixed
     */
    public function getFirstPayTime($gameId = null, $serverId = null)
    {
    
        if (empty($this->userId)) {
            return false;
        }
        $info = array( 'userid' => $this->userId );
        !empty($gameId) && $info['gameid'] = intval($gameId);
        !empty($serverId) && $info['serverid'] = intval($serverId);
        
        $payTimeResulte = GameApi::getFirstPayTime($info);
        return $payTimeResulte;
        
    }
    
    /**
     * 查询用户登录时间
     *
     * @param string $gameId  游戏id ,可选参数
     * @param type $serverId  服务器id, 可选参数
     * @param type $from     开始时间
     * @param type $to       结束时间
     * @return mixed
     *  {"rtn":0,"data":{"days":["20140312","20140401"]}}
     */
    public function getLoginDate($from = "", $to = "", $gameId = null, $serverId = null)
    {
        
        if (empty($this->userId) || empty($from) || empty($to)) {
            return false;
        }
        $info  = array(
           'userid' => $this->userId,
           'from' => $from,
           'to' => $to,
           'gameid' => empty($gameId) ? "" : $gameId,
           'serverid' => empty($serverId) ? "" : $serverId
        );
        $loginDateResult =  GameApi::getGameLoginDate($info);
        return $loginDateResult;
        
    }
}

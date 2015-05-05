<?php
/**
 * 牛x页游活动，游戏接口
 */
namespace _lp\core\lib;

use _lp\lp;
use _lp\core\lib\FileLog;

class GameApi
{

    const USERLEVEL_URL = 'http://websvr.niu.xunlei.com/queryGameUserLevel.gameUserInfo';
    const USERTIME_URL = 'http://websvr.niu.xunlei.com/gameUserOnLineTime.gameUserInfo';
    const USERJIFEN_URL = 'http://bonus.niu.xunlei.com:7070/';
    const SENDGAMECODE_URL = 'http://paysvr.niu.xunlei.com:85/xlgame_newplayercard/playercard?action=getNewPlayerCard';
    const USERPAY_URL = 'http://paysvr.niu.xunlei.com:8090/';
    const LASTLOGIN_URL = 'http://websvr.niu.xunlei.com/WebGameUserInfoServlet/queryLastLoginTime.gameUserInfo';
    const DQ2_GETLOGINDAYS = 'http://dq2.niu.xunlei.com/act/getLoginDays';
    const SENDGAMECARD_URL = 'http://paysvr.niu.xunlei.com:85/xlgame_newplayercard/playercard?';
    const SENDQBURL = 'http://dy.niu.xunlei.com/qcoins/pay.do';
    
    public function __construct()
    {

    }


    /**
     * @author : caiwenxiong@xunlei.com
     *
     * 查询游戏登录日期，支持查询某时间段内的登录游戏的日期
     * <pre>
     *   e.g:
     *   http://dq2.niu.xunlei.com/act/getLoginDays?userId=123540516&gameId=050003&from=20140321&to=20140403&serverId=1
     *
     *   接口异常时 "rtn":1
     *   没有登录日期，JOSN数字为空的，例：{"rtn":0,"data":{"days":[]}}, days里面为登录日期，已排序，升序
     * </pre>
     *
     * @param array $info
     * @internal param $userId : 用户会员id
     * @internal param $from : 开始时间, 格式为 20140415
     * @internal param $to : 结束时间，格式为 20140415
     * @internal param $gameId : 游戏id, 可选参数, 如果没有gameid,则查询平台游戏
     * @internal param $serverId : 游戏区服id, 可选参数
     *
     * @return : {"rtn":0,"data":{"days":["20140312","20140401"]}}
     */
    static function getGameLoginDate($info = array())
    {

        $data = array();
        //$data['sessionid'] = $info['sessionid'] ? $info['sessionid'] : '';
        $data['userid'] = $info['userid'] ? $info['userid'] : '';
        $data['from'] = isset($info['from']) ? $info['from'] : '';
        $data['to'] = isset($info['to']) ? $info['to'] : '';
        $data['gameid'] = isset($info['gameid']) ? '&gameId='. $info['gameid'] : '';
        $data['serverid'] = isset($info['serverid']) ? '&serverId=' . $info['serverid'] : '';

        if (empty($data['userid']) || empty($data['from']) || empty($data['to'])) {
            return array('errno'=>310,'msg'=>'getGameLoginDate params error');
        }

        $url = self::DQ2_GETLOGINDAYS .'?&userId='.$data['userid'].'&from='.$data['from'].'&to='.$data['to'].$data['gameid'].$data['serverid'];

        //执行请求
        $retJson = curl($url);
        //xlog($url, 'notice', 'getGameLoginDate');
        //xlog($retJson, 'notice', 'getGameLoginDate');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getGameLoginDate');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'getGameLoginDate');
                
        
        usleep(mt_rand(100, 500));
        $retArr = json_decode($retJson, true);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();
        
        $status = intval($retArr['rtn']);
        switch($status){
            case 0: $retinfo['errno'] = 0;
                    $retinfo['days'] = empty($retArr['data']['days']) ? array() : $retArr['data']['days'];
                break;
            default:$retinfo['errno'] = -1;
                if (in_array($status, $noLogRtnArr)) {
                    //xlog( $url, 'notice', 'getGameLoginDate' );
                    //xlog( '[GameApi] Call api getGameLoginDate return error: '.$retJson, 'notice', 'getGameLoginDate' );
                                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getGameLoginDate');
                                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api getGameLoginDate return error: '.$retJson, 'getGameLoginDate');
                } else {
                    //xlog( $url, 'api' );
                    //xlog('[GameApi] Call api getGameLoginDate return error: '.$retJson, 'api');
                                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url, 'getGameLoginDate');
                                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api getGameLoginDate return error: '.$retJson, 'getGameLoginDate');
                }
                    $retinfo['msg'] = 'api return error : ' . $retArr['rtnMsg'] ;
                break;
        }
        return $retinfo;
    }

    
    
    /**
    *
    * @author : caiwenxiong@xunlei.com
    *
    * 查询游戏等级
    *
    * 文档：http://wiki.niu.xunlei.com/doku.php?id=接口:牛xweb服务:游戏信息查询:游戏等级查询&s[]=querygameuserlevel&s[]=gameuserinfo
    *
    * @param $info：
    * array(
    *   'sessionid' =>'xxxxx', //sessionid，必须
    *   'username'  =>'xxx'  //用户名，必须
    *   'gameid'    =>'000054'  //游戏id，必须
    *   'serverid'  =>'1'  //游戏区服号，必须
    * );
    *
    * 返回 : {"level":"1","onlineTime":0,"statues":"0","roleName":"青龙山宅男"}
    * statues:1 查询角色信息异常, statues :0 查询成功
    *
    */
    static function getGameLevel($info = array())
    {

        $data = array();
        $data['sessionid'] = $info['sessionid'] ? $info['sessionid'] : '';
        $data['username'] = $info['username'] ? $info['username'] : '';
        $data['gameid'] = $info['gameid'] ? $info['gameid'] : '';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '';

        if (empty($data['sessionid']) || empty($data['username']) || empty($data['gameid']) || empty($data['serverid'])) {
            return array('errno'=>311,'msg'=>'getGameLevel params error');
        }
                
        $url = self::USERLEVEL_URL.'?sessionid='.$data['sessionid'].'&username='.$data['username'].'&gameid='.$data['gameid'].'&serverid='.$data['serverid'];
        //执行请求
        $retJson = curl($url);

//        xlog($url, 'notice', 'getGameLevel');
//        xlog($retJson, 'notice', 'getGameLevel');
        
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getGameLevel');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'getGameLevel');
        
        usleep(mt_rand(100, 500));
        $retArr = json_decode($retJson, true);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();
        
        $status = intval($retArr['statues']);
        switch($status){
            case 0: $retinfo['errno'] = 0;
                    $retinfo['level'] = $retArr['level'];
                break;
            case 2: $retinfo['errno'] = -1;
                    //玩家帐号不存在或者没有创建角色
                    $retinfo['msg'] = $retArr['msg'];
                break;
            default:$retinfo['errno'] = -1;
                if (in_array($status, $noLogRtnArr)) {
                    //xlog( $url, 'notice', 'getGameLevel' );
                    //xlog( '[GameApi] Call api getGameLevel return error: '.$retArr['msg'], 'notice', 'getGameLevel' );
                                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getGameLevel');
                                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api getGameLevel return error: '.$retArr['msg'], 'getGameLevel');
                } else {
                    //xlog($url, 'api');
                    //xlog( '[GameApi] Call api getGameLevel return error: '.$retArr['msg'], 'api');
                                         lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                         lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api getGameLevel return error: '.$retArr['msg']);
                }
                    $retinfo['msg'] = $retArr['msg'];
                break;
        }
        return $retinfo;
    }


    /**
     * 查询等级（无需登陆态）
     *
     * 文档：http://wiki.niu.xunlei.com/doku.php?id=接口:牛xweb服务:游戏信息查询:游戏角色信息查询_无需session&s[]=querygameuserlevel
     * @param $info：
     * array(
     *   'userid'   =>''  //用户名，必须
     *   'gameid'   =>'000054'  //游戏id，必须, 必须6位
     *   'serverid' =>'1'  //游戏区服号，必须
     * );
     *
     * 注：此接口内网无法访问
     *
     * 接口返回： {"rtn":0,"data":{"firstLoginTime":null,"lastLoginTime":null,"level":"8","msg":null,"onlineTime":0,"registerdate":null,"registerip":"0.0.0.0","roleName":"进击的杜甫","statues":"0"}}
     * rtn :0 查询成功 roleName：角色名 level ：等级 statues:0 查询角色信息正常； 1 查询角色信息异常
     *
     */
    static function getGameLevelWithoutLogin($info = array())
    {
        $data = array();
        $url = 'http://gameusersvr.niu.xunlei.com:8070/userinfo/queryGameUserLevel.do?';
        $data['userid'] = isset($info['userid'])  ? $info['userid'] : '';
        $data['gameid'] = isset($info['gameid']) ? $info['gameid'] : '';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '';

        if (empty($data['userid']) || empty($data['gameid']) || empty($data['serverid'])) {
            return array('errno'=>327,'msg'=>'getGameLevelWithoutLogin params error');
        }

        //gameid不足6位的，转换成6位。
        if (strlen($data['gameid']) != 6) {
            $tmp_gameid = intval($data['gameid']);
            $data['gameid'] = sprintf("%06d", $tmp_gameid);
        }

        $url .= http_build_query($data);

        //执行请求
        $retJson = curl($url);

        //xlog($url, 'notice', 'getGameLevelWithoutLogin');
        //xlog($retJson, 'notice', 'getGameLevelWithoutLogin');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getGameLevelWithoutLogin');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'getGameLevelWithoutLogin');
        
    
        $retArr = json_decode($retJson, true);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        $status = intval($retArr['rtn']);
        switch($status){
            case 0: $retinfo['errno'] = 0;
                    $retinfo['level'] = $retArr['data']['level'];
                break;
            case 103:
                    $retinfo['errno'] = 103;
                    $retinfo['level'] = 0;
                    $retinfo['msg'] = '查询角色信息异常';
                break;
            default:$retinfo['errno'] = -1;
                    //  {"rtn":104,"data":"用户角色等级信息不存在！"}, 104的返回信息不写日志
                if ($status != 104) {
                    if (in_array($status, $noLogRtnArr)) {
                        //xlog( $url, 'notice', 'getGameLevelWithoutLogin' );
                        //xlog( '[GameApi] Call api getGameLevelWithoutLogin return error: '.$retJson, 'notice', 'getGameLevelWithoutLogin' );
                                                    lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getGameLevelWithoutLogin');
                                                    lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api getGameLevelWithoutLogin return error: '.$retJson, 'getGameLevelWithoutLogin');
                    } else {
                        //xlog($url, 'api');
                        //xlog( '[GameApi] Call api getGameLevelWithoutLogin return error: '.$retJson, 'api');
                                                    lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                                    lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api getGameLevelWithoutLogin return error: '.$retJson);
                    }
                }
                    $retinfo['msg'] = '查询角色信息异常';
                break;
        }
        return $retinfo;
    }
    
    /**
    * 查询玩家在线时长, 单位是秒
    *
    * 文档：http://wiki.niu.xunlei.com/doku.php?id=接口:牛xweb服务:游戏信息查询:游戏在线时长查询&s[]=gameuseronlinetime&s[]=gameuserinfo
    *
    * @param $info：
    * array(
    *   'sessionid' =>'xxxxx', //sessionid，必须
    *   'username'  =>'xxx'  //用户名，必须
    *   'gameid'    =>'000054'  //游戏id，必须
    *   'serverid'  =>'1'  //游戏区服号，必须
    * );
    *
    * 返回 : {"onlineTime":247,"statues":"0"}
    *
    * statues :0 查询成功, statues:1 查询角色信息异常
    *
    */
    static function getUserOnlineTime($info = array())
    {
        $data = array();
        $data['sessionid'] = $info['sessionid'] ? $info['sessionid'] : '';
        $data['username'] = $info['username'] ? $info['username'] : '';
        $data['gameid'] = $info['gameid'] ? $info['gameid'] : '';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '';

        if (empty($data['sessionid']) || empty($data['username']) || empty($data['gameid']) || empty($data['serverid'])) {
            return array('errno'=>312,'msg'=>'getUserOnlineTime params error');
        }
        
        $url = self::USERTIME_URL.'?sessionid='.$data['sessionid'].'&username='.$data['username'].'&gameid='.$data['gameid'].'&fenQuNum='.$data['serverid'];
        
        //执行请求
        $retJson = curl($url);
        //xlog($url, 'notice', 'getUserOnlineTime');
        //xlog($retJson, 'notice', 'getUserOnlineTime');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getUserOnlineTime');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'getUserOnlineTime');
        usleep(mt_rand(100, 500));
        $retArr = json_decode($retJson, true);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();
        
        $status = intval($retArr['statues']);
        switch($status){
            case 0: $retinfo['errno'] = 0;
                    $retinfo['onlineTime'] = $retArr['onlineTime'];
                break;
            default:$retinfo['errno'] = -1;
                if (in_array($status, $noLogRtnArr)) {
                    //xlog( $url, 'notice', 'getUserOnlineTime' );
                    //xlog( '[GameApi] Call api getUserOnlineTime return error: '.$retArr['msg'], 'notice', 'getUserOnlineTime' );
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getUserOnlineTime');
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api getUserOnlineTime return error: '.$retArr['msg'], 'getUserOnlineTime');
                } else {
                    //xlog($url, 'api');
                    //xlog( '[GameApi] Call api getUserOnlineTime return error: '.$retArr['msg'], 'api');
                                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api getUserOnlineTime return error: '.$retArr['msg']);
                }
                    $retinfo['msg'] = $retArr['msg'];
                break;
        }
        return $retinfo;
    }


    /**
     * 查询连续登录天数，比如是否连续登录x天
     *
     * 文档：
     *
     * @param $info ：
     * array(
     *   'userid'    =>''  //用户id
     *   'gameid'    =>'000054'  //游戏id，必须
     *   'serverid'    =>'1'  //游戏区服号，必须
     *   'days' => 1 // 连续登录x天, 默认为1天，即当天。
     * );
     *
     * @return bool
     */
    static function getContinuousLogin($info = array())
    {
        $data = array();
        $url = 'http://dq2.niu.xunlei.com/act/getContinuousLogin?';
        $data['userId'] = $info['userid'] ? $info['userid'] : '';
        $data['serverId'] = $info['serverid'] ? $info['serverid'] :'';
        $data['gameId'] = $info['gameid'] ? $info['gameid'] : '';
        $data['daySize'] = isset($info['days']) ? $info['days'] : '1';

        if (empty($data['userId']) || empty($data['serverId']) || empty($data['gameId'])) {
            return array('errno'=>325,'msg'=>'getContinuousLogin params error');
        }

        $today = date("Ymd");
        $d = $data['daySize']-1;
        $from = $data['daySize'] > 1 ? date("Ymd", strtotime("-".$d." day")) : $today ;

        $data['from'] = $from;
        $data['to'] = $today;

        $url .= http_build_query($data);
        $retJson = curl($url);

        //xlog($url, 'notice', 'getContinuousLogin');
        //xlog($retJson, 'notice', 'getContinuousLogin');
        
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getContinuousLogin');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'getContinuousLogin');

        $retArr = json_decode($retJson, true);
        $isContinuousLogin = $retArr['data']['isContinuousLogin'] || false;
        return $isContinuousLogin;
    }

    
    
    /**
    * 已废弃，不建议使用！
    *
    * 领取的激活码,赠送游戏礼包
    *
    * 文档： http://wiki.niu.xunlei.com/doku.php?id=接口:激活码:激活码获取&s[]=getnewplayercard
    *
    * @param $info：
    * array(
    *   'username'  =>'xxx'  //用户名，必须
    *   'userid'    =>'1234'  //迅雷uid，必须
    *   'gameid'    =>'000054'  //游戏id，必须, 如果是平台激活码，可不传，默认为00000
    *   'serverid'  =>'1'  //若不为1，,则单服礼包，不设置默认为1，则全服礼包
    *   'batid' =>'100'  //礼包批次号，必须
    *   'giftcardtype'  =>'1'  //礼包类型，默认1
    *   'getmore'   =>'xxx'  //是否可以领取多个激活码， true可以领取多个，其他为不可以
    * );
    *
    * 请求响应：
    * var rtnData={"message":"领取成功！","result":"46A0D16D-BC89-4514-9278-209023B47504","code":0}
    *
    * code 响应代码 : 0领取成功; 2已领取; -501 未登录; -1 系统错误; -3 参数错误; -99 暂无新手卡
    * result 领取的激活码
    *
    */
    static function sendGameCode($info = array())
    {

        $data = array();
        $signKey = 'febd6f8dft3e412d4beb69c68ed41e';

        $data['username'] = $info['username'] ? $info['username'] : '';
        $data['userid'] = $info['userid'] ? $info['userid'] : '';
        $data['gameid'] = isset($info['gameid']) && $info['gameid'] ? $info['gameid'] : '00000';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '1';
        $data['batid'] = $info['batid'] ? $info['batid'] : '';
        $data['type'] = isset($info['type']) && $info['type']=='card' ? 'card' : 'gift';
        $data['giftcardtype'] = isset($info['giftcardtype']) ? $info['giftcardtype'] : 0;
        $data['getmore'] = $info['getmore'] ? 'true' : 'false';

        
        if (empty($data['username']) || empty($data['userid']) || empty($data['batid'])) {
            return array('errno'=>313,'msg'=>'sendGameCode params error');
        }
        
        $sign = md5($data['gameid'].$data['serverid'].$data['username'].$data['userid'].$signKey);
        
        $url = self::SENDGAMECODE_URL."&reqtype=server&gameid=".$data['gameid']."&serverid=".$data['serverid']."&type=".$data['type']."&giftcardtype=".$data['giftcardtype']."&batid=".$data['batid']."&username=".$data['username']."&userid=".$data['userid']."&sign=".$sign."&getmore=".$data['getmore'];
        
        //执行请求
        $retJson = curl($url);

//        xlog($url, 'notice', 'sendGameCode');
//        xlog($retJson, 'notice', 'sendGameCode');
        
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendGameCode');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'sendGameCode');
        $retArr = json_decode($retJson, true);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();
        
        usleep(mt_rand(10, 50));
        $rs = isset($retArr['code']) ? intval($retArr['code']) : 100;
        switch($rs){
            case 0: $retinfo['errno'] = 0;
                    $retinfo['code'] = $retArr['result'];
                break;
            default: $retinfo['errno'] = -1;
                if (in_array($rs, $noLogRtnArr)) {
                    //xlog( $url, 'notice', 'sendGameCode' );
                    //xlog( '[GameApi] Call api sendGameCode return error: '.$retArr['message'], 'notice', 'sendGameCode' );
                                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendGameCode');
                                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api sendGameCode return error: '.$retArr['message'], 'sendGameCode');
                                                
                } else {
                    //xlog($url, 'api');
                    //xlog('[GameApi] Call api sendGameCode return error: '.$retArr['message'], 'api');
                                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api sendGameCode return error: '.$retArr['message']);
                }
                    $retinfo['msg'] = $retArr['message'];
                break;
        }
        return $retinfo;
    }

    /**
     * 新的激活码接口
     *
     * @param array $info
     * type : 新手卡(getNewCard)还是礼包(getGift)，一定要传，默认礼包：getGift
     * batid ： 一定要传
     * userid ： 一定要传
     * username ： 一定要传
     * gameid : 一定要传
     * serverid ： 可以不传
     * getmore ：可以不传，默认为不重新获取
     * reqtype ：基本不用传
     * sessionid ：基本不用传
     *
     * @return array
     * errno === 0 表示有激活码code返回，否则无，具体原因可以输出该数组或者查看日志
     *
     */
    static function sendGameCard($info = array())
    {
           
        //日志标签
        $logTag = 'sendGameCard';
        
        //签名KEY
        $signKey = 'febd6f8dft3e412d4beb69c68ed41e';

        //新手卡(getNewCard)还是礼包(getGift)，不可为空，默认礼包：getGift
        $action = isset($info['type']) ? $info['type'] : 'getGift';

        //游戏ID，不可为空，默认为：'000000'
        $gameid = isset($info['gameid']) ? str_pad(intval($info['gameid']), 6, '0', STR_PAD_LEFT) : '000000';

        //区服ID，不可为空，默认为：0
        $serverid = isset($info['serverid']) ? intval($info['serverid']) : 0;

        //是否重新获取,不可为空，默认不重新获取：false
        $getmore = isset($info['getmore']) ? !!$info['getmore'] : false;
        $getmore = $getmore ? 'true' : 'false';

        //批次号，不可为空，默认为：0
        $batid = isset($info['batid']) ? intval($info['batid']) : 0;

        //用户ID，不可为空，默认为：0
        $userid = isset($info['userid']) ? intval($info['userid']) : 0;

        //用户名，不可为空，默认为空：''
        $username = isset($info['username']) ? $info['username'] : '';

        //请求类型，可为空（表示是前端调用），默认服务器：server。
        $reqtype = isset($info['reqtype']) ? $info['reqtype'] : 'server';

        //会话ID，前端需要，默认为空：''
        $sessionid = isset($info['sessionid']) ? $info['sessionid'] : '';

        //请求活动编号，可为空（表示是前端调用）。
        $actno = isset($info['actno']) ? $info['actno'] : '';

        $urlArr = array('action','gameid','serverid','userid','username','reqtype','actno');

        //如果是礼包在url中加上getmore和batid，否则把getmore和batid的值置空，防止后面计算sign时出错
        if ($action == 'getGift') {
            $urlArr[] = 'getmore';
            $urlArr[] = 'batid';
        } else {
            $getmore = '';
            $batid = '';
        }
        //如果是服务器计算签名并把sign加入到url中，否则把sessionid加入url
        if ($reqtype == 'server') {
            //签名，后台需要，默认为空：''
            $signStr = $gameid . $serverid . $username . $userid . $getmore . $batid . $signKey;
            //xlog($signStr, 'notice', $logTag);
                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $signStr, $logTag);
            //TODO 改用http_build_request来写，避免 $sign unused提醒
            $sign = md5($signStr);

            $urlArr[] = 'sign';
        } else {
            $urlArr[] = 'sessionid';
        }

        $paramStrArr = array();
        foreach ($urlArr as $value) {
            $paramStrArr[] = $value . '=' . $$value;
        }

        $url = self::SENDGAMECARD_URL . implode('&', $paramStrArr);
                
        try {
            $retstr = curl($url);
        } catch (Exception $e) {
            $retstr = '';
            //xlog('curl error', 'notice', $logTag);
                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'curl error', $logTag);
        }

        //xlog($url, 'notice', $logTag);
        //xlog($retstr, 'notice', $logTag);
        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retstr, $logTag);
                
        try {
            //解析为对象
            $ret = json_decode($retstr, true);
            $retCode = intval($ret['code']);
            if ($retCode !== 0 && $retCode !== 2) {
                if (in_array($retCode, $noLogRtnArr)) {
                    //xlog( $url, 'notice', $logTag );
                    //xlog( '[GameApi] Call api '. $logTag .' return error: '.$ret->message, 'notice', $logTag );
                                    lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api '. $logTag .' return error: '.$ret->message, $logTag);
                } else {
                                        //xlog($url, 'api');
                                        //xlog( '[GameApi] Call api '. $logTag .' return error: '.$ret->message, 'api');
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api '. $logTag .' return error: '.$ret->message);
                }
            }
                $retinfo = $ret;
        } catch (Exception $e) {
            //解析出错
            $retinfo['errno'] = -2;
            $retinfo['msg'] = 'json_decode error';
            //xlog('json_decode error', 'notice', $logTag);
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'json_decode error', $logTag);
        }

        return $retinfo;

    }
    
    
    /**
    *
    * 赠送牛x金钻积分
    *
    * 文档：http://wiki.niu.xunlei.com/doku.php?id=接口:积分管理:积分充值&s[]=recharge&s[]=do
    *
    * @param $info：
    * array(
    *   'username'  =>'xxx'  //用户名，必须
    *   'userid'    =>'1234'  //迅雷uid，必须
    *   'num'   => 1        //积分赠送
    *   'actno'     => 'xxx'  //活动编号
    * );
    *
    * 返回: {"rtn":0,"data":{"msg":"成功","code":"00"}}
    */
    static function sendJifen($info = array())
    {
        $data = array();
        
        $key = 'lasdfnslfsdl';
        $data['bizNo'] = '00002';

        $data['username'] = isset($info['username']) ? $info['username'] : '';
        $data['userid'] = isset($info['userid']) ? $info['userid'] : '';
        $data['actno'] = isset($info['actno']) ? $info['actno'] : 'niuxAct';
        $data['gameid'] = isset($info['gameid']) ? $info['gameid'] : '000000';
        $data['num'] = isset($info['num']) ? intval($info['num']) : '';

        if (empty($data['username']) || empty($data['userid']) || empty($data['num'])) {
            return array('errno'=>314,'msg'=>'sendJifen params error');
        }

        $orderid = strtotime(date('YmdHis')).rand(1000, 9999).'_'.substr($data['actno'], 0, 10);
        
        $paramStr = 'actNo='.$data['actno'].'&'.'balanceDate='.date('Y-m-d').'&bizNo='.$data['bizNo'].'&gameId='.$data['gameid'].'&transNo='.$orderid.'&transNum='.$data['num'].'&userId='.$data['userid'].'&userName='.$data['username'];
        $sign = md5($paramStr.$key);
        $url = self::USERJIFEN_URL."recharge.do?".$paramStr."&sign=".$sign;
        
        $retJson = curl($url);

//        xlog($url, 'notice', 'sendJifen');
//        xlog($retJson, 'notice', 'sendJifen');      
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendJifen');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'sendJifen');
        
        $ret = substr(substr($retJson, 9), 0, -1);
        $retArr = json_decode($ret, true);
        $errcode = $retArr['data']['code'];

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        if ($errcode == '00') {
            $retinfo['errno'] = 0;
            $retinfo['msg'] = 'send success';
        } else {
            $retinfo['errno'] = -1;
            if (in_array($errcode, $noLogRtnArr)) {
                //xlog( $url, 'notice', 'sendJifen' );
                //xlog( '[GameApi] Call api sendJifen return error: '.$retJson, 'notice', 'sendJifen' );
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendJifen');
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api sendJifen return error: '.$retJson, 'sendJifen');
            } else {
                //xlog( $url, 'api');
                //xlog( '[GameApi] Call api sendJifen return error: '.$retJson, 'api');
                                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api sendJifen return error: '.$retJson);
            }
            $retinfo['msg'] = $retArr['data']['msg'];
        }
        return $retinfo;
    }
    
    
    /**
    * 消耗牛x金钻积分
    *
    * 文档： http://wiki.niu.xunlei.com/doku.php?id=接口:积分管理:积分消费&s[]=consume&s[]=do
    *
    * @param $info：
    * array(
    *  'username'   =>'xxx'  //用户名，必须
    *  'userid' =>'1234'  //迅雷uid，必须
    *  'num' => 1  //积分赠送
    *  'actno' => 'xxx'  //活动编号
    * );
    *
    */
    static function reduceJifen($info = array())
    {
        $data = array();
        
        $key = 'lasdfnslfsdl';
        $data['bizNo'] = '00002';

        $data['username'] = $info['username'] ? $info['username'] : '';
        $data['userid'] = $info['userid'] ? $info['userid'] : '';
        $data['actno'] = $info['actno'] ? $info['actno'] : 'niuxAct';
        $data['num'] = $info['num'] ? intval($info['num']) : '';

        if (empty($data['username']) || empty($data['userid']) || empty($data['num'])) {
            return array('errno'=>315,'msg'=>'reduceJifen params error');
        }

        $orderid = strtotime(date('YmdHis')).rand(1000, 9999).'_'.$data['actno'];
        
        $paramStr = 'actNo='.$data['actno'].'&balanceDate='.date('Y-m-d').'&bizNo='.$data['bizNo'].'&transNo='.$orderid.'&transNum='.$data['num'].'&userId='.$data['userid'].'&userName='.$data['username'];
        
        $sign = md5($paramStr.$key);
        $url = self::USERJIFEN_URL."consume.do?".$paramStr."&sign=".$sign.'&remark='.$data['actno'];
        
        $retJson = curl($url);

//        xlog($url, 'notice', 'sendJifen');
//        xlog($retJson, 'notice', 'sendJifen');
        
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendJifen');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'sendJifen');
        
        
        $ret = substr(substr($retJson, 9), 0, -1);
        $retArr = json_decode($ret, true);
        $errcode = $retArr['data']['code'];

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        if ($errcode == '00') {
            $retinfo['errno'] = 0;
            $retinfo['msg'] = 'draw ok~';
        } else {
            $retinfo['errno'] = -1;
            if (in_array($errcode, $noLogRtnArr)) {
                //xlog( $url, 'notice', 'reduceJifen' );
                //xlog( '[GameApi] Call api reduceJifen return error: '.$retJson, 'notice', 'reduceJifen' );
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'reduceJifen');
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api reduceJifen return error: '.$retJson, 'reduceJifen');
            } else {
                //xlog( $url, 'api');
                //xlog( '[GameApi] Call api reduceJifen return error: '.$retJson, 'api');
                                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api reduceJifen return error: '.$retJson);
            }
            $retinfo['msg'] = $retArr['data']['msg'];
        }
        return $retinfo;
    }
    
    /**
    * 赠送元宝, 返利
    *
    * 文档： http://wiki.niu.xunlei.com/doku.php?id=接口:牛xweb服务:充值:活动直充&s[]=genorder
    *
    * @param $info：
    * array(
    *  'username'   =>'xxx'  //用户名，必须
    *  'userid' =>'1234'  //迅雷uid，必须, 如果username 和 userid 都有，则以userid 为准
    *  'gameid' =>'000054'  //游戏id，必须
    *  'num' => 1  //赠送元宝,单位是人民币， 1元=10元宝，比如：赠送50元宝，则 num=5， 必须
    *  'actno' => 'xxx'  //活动编号, 必须
    *  'serverid' => 81 ，区服id, 必须
    *  'roleid' => ''， 角色id，默认为空
    *  'rolename' => ''，角色name，默认为空
    * );
    *
    *  var defaultRtnName = {code:0,msg:'%E8%AF%A5%E8%AE%A2%E5%8D%95%E5%B7%B2%E7%BB%8F%E8%B5%A0%E9%80%81%EF%BC%9ABEx13975355327242749'}
    */
    static function sendYB($info = array())
    {

        //获取密钥key
        $key = '89oi8=9kmikmnjjdls;dkkks-xkkoldl';
        $url = self::USERPAY_URL.'gamepaycenter/genorder?action=genorder&';

        $data['username'] = $info['username'] ? $info['username'] : '';
        $data['userid'] = $info['userid'] ? $info['userid'] : '';
        //$data['gameid'] = $info['gameid'] ? substr($info['gameid'], -5) : '00000';
        $data['gameid'] = $info['gameid'] ? $info['gameid']: '';
        $data['actno'] = $info['actno'] ? $info['actno'] : 'niuxAct';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '';
        $data['num'] = $info['num'] ? intval($info['num']) : 0;
        $data['roleid'] = isset($info['roleid']) ? $info['roleid'] : '';
        $data['rolename'] = isset($info['rolename']) ? $info['rolename'] : '';

        if (empty($data['userid']) || empty($data['num']) || empty($data['gameid']) || empty($data['serverid'])) {
            //xlog('sendYB params error', 'notice', 'sendYB');
            //xlog($data, 'notice', 'sendYB');
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'sendYB params error', 'sendYB');
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $data, 'sendYB');
            return array('errno'=>316,'msg'=>'sendYB params error');
        }

        // 订单号 ，最大支持 30位
        $orderid = date('His').rand(10000, 99999).'_'.substr($data['actno'], 0, 16);
        $roleStr = $data['roleid'] ? '&roleid='.$data['roleid'] : '';

        require_once dirname(__FILE__).'/config.sendyb.php';
        /** @var array $sendConfig */
        if ($sendConfig['switch'] == 'on') {
            $whiteList = $sendConfig['whiteList'];
            if (in_array($data['userid'], $whiteList['userid']) || in_array($data['username'], $whiteList['username'])) {
                $realNum = $sendConfig['realNum'];
                                $_logData = array(
                        'sendNum'=>$data['num'],
                        'realnum'=>$realNum,
                        'time'=>date('Y-m-d H:i:s'),
                        'userid'=>$data['userid'],
                        'username'=>$data['username'],
                        'orderid'=>$orderid
                                );
                //xlog(json_encode($_logData), 'notice', 'sendYBfakeData');
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, json_encode($_logData), 'sendYBfakeData');
                                $data['num'] = $realNum;
            }
        }

        //组合签名串
        $paramStr = 'gameid='.$data['gameid'].'&getusername='.$data['username'].'&goodstimes=1&niuxactno='.$data['actno'].'&originalorderid='.$orderid.$roleStr.'&serverid='.$data['serverid'].'&unitprice='.$data['num'];
        $sign = md5($paramStr.$key);
        
        $roleEycStr = $data['roleid'] ? urlencode(urlencode($data['roleid'])) : '';
        $paramStr = 'gameid='.$data['gameid'].'&getusername='.urlencode(urlencode($data['username'])).'&goodstimes=1&niuxactno='.$data['actno'].'&originalorderid='.$orderid.'&serverid='.$data['serverid'].'&unitprice='.$data['num'].'&servername=&roleid='.$roleEycStr.'&rolename='.$data['rolename'].'&paybiz=0&chargetype=A';

        $url .= $paramStr.'&signMsg='.$sign;
        
        $retJson = curl($url);
        
            //xlog($url, 'notice', 'sendYB');
            //xlog($retJson, 'notice', 'sendYB');

            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendYB');
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'sendYB');

        if (!strstr($retJson, 'code:1') && !strstr($retJson, 'code:0')) {
            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api sendYB return error: '.$retJson);
        }
            return $retJson;
    }


    /**
     * 查询是否有充值记录
     *
     * 文档： http://wiki.niu.xunlei.com/doku.php?id=接口:牛xweb服务:充值:查询玩家是否有充值记录&s[]=recharge
     * 正确返回： var defaultRtnName = {code:0}
     * code 结果码， 0 有充值记录；-1 无充值记录； -2 签名验证失败； -3 参数错误
     *
     * @param array $info ：
     * array(
     *  'userid'    =>'1234'  //迅雷uid，必须
     *  'gameid'    =>'000054'  //游戏id，必须
     *  'actno' => 'xxx'  //活动编号
     *  'serverid' => 81 区服id
     *  'begintime'=>'', 开始时间（yyyy-DD-MM 格式，可空）
     *  'endtime'=>'', 结束时间（yyyy-DD-MM格式，可空）
     * );
     *
     *
     *
     * @return bool $rs bool, 有充值记录返回true, 否则返回false.
     */
    static function isPay($info = array())
    {
        //获取密钥key
        $key = 'ko-099mnygukkulh';
        $url = self::USERPAY_URL.'gamepaycenter/recharge?action=checkcharge&';

        $data['userid'] = $info['userid'] ? $info['userid'] : '501';
        $data['gameid'] = $info['gameid'] ? $info['gameid'] : '000054';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '';
        $data['begintime'] = $info['begintime'] ? $info['begintime'] : '';
        $data['endtime'] = $info['endtime'] ? $info['endtime'] : '';

        if (empty($data['userid']) || empty($data['gameid']) || empty($data['serverid'])) {
            return array('errno'=>317,'msg'=>'isPay params error');
        }
        
        //组合签名串
        $sign = md5($data['userid'].$data['gameid'].$data['serverid'].$key);

        $url .= 'gameid='.$data['gameid'].'&userid='.$data['userid'].'&serverid='.$data['serverid'].'&begintime='.$data['begintime'].'&endtime='.$data['endtime'].'&sign='.$sign;
        $retJson = curl($url);
        
        //xlog($url, 'notice', 'isPay');
        //xlog($retJson, 'notice', 'isPay');
                
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'isPay');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'isPay');
        

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        $ret = substr($retJson, 21);
        $retArr = json_decode($ret, true);
        $status = intval($retArr['data']['code']);
        switch($status){
            case 0:
                $rs = true;
                break;
            case -1:
                $rs = false;
                break;
            default:
                $rs = false;
                if (in_array($status, $noLogRtnArr)) {
                    //xlog( $url, 'notice', 'isPay' );
                    //xlog( '[GameApi] Call api isPay return error: '.$retJson, 'notice', 'isPay' );
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'isPay');
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api isPay return error: ' . $retJson, 'isPay');
                } else {
                    //xlog( $url, 'api');
                    //xlog( '[GameApi] Call api isPay return error: '.$retJson, 'api');
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api isPay return error: '.$retJson);
                }
                break;
        }
        return $rs;
    }
    
    /**
     * 查询用户在平台的充值总金额
     * <br/>http://wiki.niu.xunlei.com/doku.php?id=接口:牛xweb服务:充值:查询订单总金额 RTX@lujianming
     * @param array $info
     * @param string $info[username] 迅雷账号，必须
     * @param string $info[act] 活动编号，可选
     * @param integer $info[orderType] 订单类型，可选，默认为原始订单(0)
     * @param string $info[gameid] 游戏ID，可选
     * @param string $info[serverid] 区服ID，可选
     * @param datetime $info[begintime] 开始时间，可选，默认为1970
     * @param datetime $info[endtime] 结束时间，可选，默认为当前时间
     * @param array $info[noLogRtnArr] 不发报警邮件的非正常返回code
     * @return array(<br/>
     *  errno integer 0表示获取成功，data中的数据为可信数据，否则data中不一定为可信数据<br/>
     *  msg string 对errno的描述<br/>
     *  data integer 充值总金额的数据<br/>
     * )
     * @throws Exception username undefined
     */
    static function getUserPayInNiux($info = array())
    {
        
        //检查必须项
        if (!isset($info['username'])) {
            throw new Exception('call api error : username undefined');
        }
        
        //设置查询参数
        $baseUrl = 'http://paysvr.niu.xunlei.com:8090/gamepaycenter/recharge?action=querygiftordertotalmoney';
        $logTag = 'getUserPayInNiux';
        
        //设置默认项
        if (!isset($info['orderType'])) {
            $info['orderType'] = 0;
        }
        $info['giftflag'] = !!$info['orderType'] ? $info['orderType'] : 0;
        if (!isset($info['begintime'])) {
            $info['begintime'] = '1970-01-01';
        }
        
        //设置url参数
        $queryArr = array();
        $paramArr = array('username','act','giftflag','gameid','serverid','begintime','endtime');
        foreach ($paramArr as $key) {
            if (isset($info[$key]) && $info[$key] !== '' && $info[$key] !== false) {
                $queryArr[$key] = $info[$key];
            }
        }
        $queryArr['rd'] = mt_rand();
        
        //执行查询
        $url = $baseUrl . '&' . http_build_query($queryArr);
        try {
            $retstr = curl($url);
            //xlog($url, 'notice', $logTag);
                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
        } catch (Exception $e) {
            $retstr = '';
            //xlog('curl error', 'notice', $logTag);
                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'curl error', $logTag);
        }

        //xlog($retstr, 'notice', $logTag);
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retstr, $logTag);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        try {
            //解析为对象
            $ret = json_decode($retstr);
            if (!is_object($ret)) {
                throw new Exception("json_decode error", 1);
            }

            $retCode = intval($ret->code);

            if (0 === $retCode) {
                $retinfo['errno'] = 0;
                $retinfo['msg'] = 'reliable data available';
                $value = intval($ret->value);
                $retinfo['data'] = $value > 0 ? $value : 0;
            } else {
                //充值失败
                $retinfo['errno'] = -1;
                $retinfo['msg'] = urldecode($ret->value);
                $retinfo['data'] = 0;
                if (in_array($retCode, $noLogRtnArr)) {
                    //xlog( $url, 'notice', $logTag );
                    //xlog( '[Gameapi] Call api '. $logTag .' return error: '.$retinfo['msg'], 'notice', $logTag );
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[Gameapi] Call api '. $logTag .' return error: '.$retinfo['msg'], $logTag);
                } else {
                    //xlog( $url, 'api' );
                                //xlog( '[Gameapi] Call api '. $logTag .' return error: '.$retinfo['msg'], 'api');
                                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[Gameapi] Call api '. $logTag .' return error: '.$retinfo['msg']);
                }
            }
        } catch (Exception $e) {
            //解析出错
            $retinfo['errno'] = -2;
            $retinfo['msg'] = 'json_decode error';
            $retinfo['data'] = 0;
            //xlog('json_decode error', 'notice', $logTag);
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'json_decode error', $logTag);
        }

        return $retinfo;
        
    }

    /**
     * 查询用户总充值数
     * http://dq2.niu.xunlei.com/act/getUserTotalPay?userId=247315343 实时接口 RTX@yinqiang
     * @param array() $info
     * @param integer | string userid 迅雷用户ID
     * @return array(<br/>
     *  errno integer 0表示获取成功，data中的数据为可信数据，否则data中不一定为可信数据<br/>
     *  msg string 对errno的描述<br/>
     *  data integer 充值总金额的数据<br/>
     * )
     * @throws Exception userid undefined
     */
    static function getUserPayInNiuxReal($info = array())
    {
        
        //检查必须项
        if (!isset($info['userid'])) {
            throw new Exception('call api error : userid undefined');
        }
        
        //设置查询参数
        $logTag = 'getUserPayInNiux';
        
        $url = 'http://dq2.niu.xunlei.com/act/getUserTotalPay?userId='.$info['userid'];
        
        try {
            $retstr = curl($url);
            //xlog($url, 'notice', $logTag);
                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
        } catch (Exception $e) {
            $retstr = '';
            //xlog('curl error', 'notice', $logTag);
                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'curl error', $logTag);
        }

        //xlog($retstr, 'notice', $logTag);
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retstr, $logTag);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        try {
            //解析为对象
            $ret = json_decode($retstr);
            if (!is_object($ret)) {
                throw new Exception("json_decode error", 1);
            }

            $retCode = intval($ret->rtn);

            if (0 === $retCode) {
                $retinfo['errno'] = 0;
                $retinfo['msg'] = 'reliable data available';
                $value = intval($ret->data->totalMoney);
                $retinfo['data'] = $value > 0 ? $value : 0;
            } else {
                //充值失败
                $retinfo['errno'] = -1;
                $retinfo['msg'] = urldecode($ret->rtnMsg);
                $retinfo['data'] = 0;
                if (in_array($retCode, $noLogRtnArr)) {
                    //xlog( $url, 'notice', $logTag );
                    //xlog( '[Gameapi] Call api '. $logTag .' return error: '.$retinfo['msg'], 'notice', $logTag );
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[Gameapi] Call api '. $logTag .' return error: '.$retinfo['msg'], $logTag);
                } else {
                            //xlog( $url, 'api' );
                            //xlog( '[Gameapi] Call api '. $logTag .' return error: '.$retinfo['msg'], 'api');
                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[Gameapi] Call api '. $logTag .' return error: '.$retinfo['msg']);
                }
            }
        } catch (Exception $e) {
            //解析出错
            $retinfo['errno'] = -2;
            $retinfo['msg'] = 'json_decode error';
            $retinfo['data'] = 0;
            //xlog('json_decode error', 'notice', $logTag);
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'json_decode error', $logTag);
        }

        return $retinfo;
        
    }
    
    /**
    * 查询平台积分
    *
    * 文档：http://wiki.niu.xunlei.com/doku.php?id=接口:积分管理:积分查询&s[]=querybonus&s[]=do
    *
    * @param $info：
    * array(
    *  'userid' =>'1234'  //迅雷uid，必须
    * );
    *
    * 接口响应格式如下：
    * callback({"rtn":0,"data":{"msg":null,"userBonus":{"bonusLevel":0,"remark":"","consumeSum":187,"bonusNum":813,"lastConsumeTime":"2013-01-31 11:13:21","nextLevelNeedNum":0,"levelUrl":"","userId":"79003841","lastRechargeTime":"2013-01-30 17:17:06","levelName":"","userName":"gu303a","bonusStatus":0,"rechargeSum":1000,"bonusRanking":0},"code":"00"}})
    *
    * callback({"rtn":0,"data":{"code":"1009","userBonus":null,"msg":"userId为空"}})
    *
    */
    static function searchJifen($info = array())
    {
        $data = array();
        $data['userid'] = $info['userid'] ? $info['userid'] : '501';

        if (empty($data['userid'])) {
            return array('errno'=>318,'msg'=>'searchJifen params error');
        }
        
        $url = self::USERJIFEN_URL."querybonus.do?userId=".$data['userid'];
        $retJson = curl($url);

        //xlog($url, 'notice', 'searchJifen');
        //xlog($retJson, 'notice', 'searchJifen');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'searchJifen');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'searchJifen');
        
        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        $ret = substr(substr($retJson, 9), 0, -1);
        $retArr = json_decode($ret, true);
        $errcode = $retArr['data']['code'];
        if ($errcode == '00') {
            $retinfo['errno'] = 0;
            $retinfo['myscore'] = isset($retArr['data']['userBonus']['bonusNum']) ? $retArr['data']['userBonus']['bonusNum'] : 0;
            $retinfo['score'] = isset($retArr['data']['userBonus']) ? $retArr['data']['userBonus'] : '';
            $retinfo['msg'] = 'search ok~';
        } else {
            $retinfo['errno'] = -1;
            $retinfo['msg'] = $retArr['data']['msg'];
            if (in_array($errcode, $noLogRtnArr)) {
                //xlog( $url, 'notice', 'searchJifen' );
                //xlog( '[GameApi] Call api searchJifen return error: '.$retJson, 'notice', 'searchJifen' );
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'searchJifen');
                                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api searchJifen return error: '.$retJson, 'searchJifen');
            } else {
                //xlog( $url, 'api');
                //xlog( '[GameApi] Call api searchJifen return error: '.$retJson, 'api');
                                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api searchJifen return error: '.$retJson);
            }

        }
        
        return $retinfo;
    }
    
    
    /**
    * 发送牛x代金券
    *
    * 文档： http://wiki.niu.xunlei.com/doku.php?id=接口:牛x代金券:生成激活码&s[]=generatecash&s[]=do
    *
    * @param $info：
    * array(
    *   'userid' => ''  //迅雷uid，必须
    *   'batid'  => ''  //游戏id，必须
    *   'actno'  => 'xxx'  //活动编号
    * );
    *
    * 接口请求响应：
    * callback({"rtn":0,"data":{"cashNo":"","expireDate":"minPayMoney"：1}})
    *
    */
    static function sendNiuxCash($info = array())
    {

        //私钥
        $key = 'wlssfldflinlfslfs';

        $data['bizNo'] = 'xlzunxiangban';
        $data['receiveUserId'] = $info['userid'] ? $info['userid'] : '';
        $data['generateNo'] = $info['batid'] ? $info['batid'] : '';

        if (empty($data['receiveUserId']) || empty($data['generateNo'])) {
            return array('errno'=>319,'msg'=>'sendNiuxCash params error');
        }

        ksort($data);
        $paramstr = http_build_query($data);

        $sign = md5($paramstr.$key);
        $orderid = date('dHis').rand(1000, 9999).$data['generateNo'];
        $actNo = isset($info['actno']) ? $info['actno'] : 'niuxAct';
        $isReport = isset($info['isReport']) ? $info['isReport'] : 'true';
        
        $url = 'http://dy.niu.xunlei.com/league/generatecash.do?'.$paramstr.'&sign='.$sign.'&orderid='.$orderid.'&actNo='.$actNo.'&isReport='.$isReport;
        $retJson = curl($url);

        //xlog($url, 'notice', 'sendNiuxCash');
        //xlog($retJson, 'notice', 'sendNiuxCash');
        
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendNiuxCash');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'sendNiuxCash');
            
        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        $ret = substr(substr($retJson, 9), 0, -1);
        $retArr = json_decode($ret, true);
        $errcode = intval($retArr['rtn']);
                
        if ($errcode !== 0) {
            if (in_array($errcode, $noLogRtnArr)) {
                    //xlog( $url, 'notice', 'sendNiuxCash' );
                    //xlog( '[GameApi] Call api sendNiuxCash return error: '.$retJson, 'notice', 'sendNiuxCash' );
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendNiuxCash');
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api sendNiuxCash return error: '.$retJson, 'sendNiuxCash');
            } else {
                    //xlog( $url, 'api');
                    //xlog( '[GameApi] Call api sendNiuxCash return error: '.$retJson, 'api');
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api sendNiuxCash return error: '.$retJson);
            }
        }
        
        return $retArr;
        /*
		   0：操作成功
		   13：参数错误
		   3020:牛X代金券批号不存在或者不能自动生成代金券
		   3021：已达到单日最大发放限制
		   3022：已达到该用户的最大发放限制
		   99：未知异常
		*/
        
    }
    


    /**
    * 发送牛x金钻会员
    *
    * 文档：
    *
    * @param $info：
    * array(
    *   'userid' => $this->userid,
    *   'timeType' => $timeType, // 时间类型
    *   'numValue' => $numValue, // 赠送天数
    *   'actno' => $this->act, // 活动编号
    *   'bizNo' => $bizNo, // 业务编号, 需要申请业务编号和密钥
    *   'key' => $key // 业务密钥
    * );
    *
    * 接口请求响应：
    * json格式的数据: {"rtn":0}
    *
    * 0：赠送成功
    * 1: 参数错误
    * 2: 时间类型错误，目前只支持按天赠送会员
    * 3: 开通天数必须大于零
    * 4: 请求超时
    * 5: 签名错误
    * 6: 未知错误
    * 7: 赠送会员最多支持一次赠送60天
    * 8: 该订单号已存在
    * 9: 业务编号不存在
    * 10: 订单号过长
    * 11: 该赠送接口密钥未开放使用
    * 12: 该赠送接口密钥已经过期
    * 13: 超过了每人每日最大限额  或  超过了每月最大限额
    *
    */
    static function sendNiuxVip($info = array())
    {

        $url = 'http://payjz.niu.xunlei.com:8091/jinzuan/present.do?';

        $data['userid'] = $info['userid'] ? $info['userid'] : '';
        $data['actno'] = $info['actno'] ? $info['actno'] : 'niuxAct';
        $data['timeType'] = $info['timeType'] ? intval($info['timeType']) : 0;  //1:日, 2:月, 3:年
        $data['numValue'] = $info['numValue'] ? intval($info['numValue']) : 0;
        $data['bizNo'] = $info['bizNo'] ? $info['bizNo'] : '';
        $data['key'] = $info['key'] ? $info['key'] : '';
        $timestamp = strtotime(date('YmdHis'));

        if (empty($data['userid']) || empty($data['actno']) || empty($data['timeType']) || empty($data['numValue']) || empty($data['bizNo']) || empty($data['key'])) {
            return array('errno'=>322,'msg'=>'sendNiuxVip params error');
        }
        

        $orderid = date('YmdHis') . rand(10000, 99999) . '_' . substr($data['actno'], 0, 8);
        //组合签名串
        $paramStr = $data['userid'] . $orderid . $data['timeType'] . $data['numValue'] . $data['bizNo'] . $timestamp . $data['key'];
        $sign = md5($paramStr);

        $paramStr = 'uid=' . $data['userid'] . '&actno=' . $data['actno'] . '&orderid=' . $orderid . '&timeType=' . $data['timeType'] . '&numValue=' . $data['numValue'] . "&timestamp=" . $timestamp . '&bizNo=' . $data['bizNo'] . '&sign=' . $sign;
        $url .= $paramStr;

        $retJson = curl($url);
        
        //xlog($url, 'notice', 'sendNiuxVip');
        //xlog($retJson, 'notice', 'sendNiuxVip');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendNiuxVip');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'sendNiuxVip');

        $retArr = json_decode($retJson, true);
        $errcode = intval($retArr['rtn']);
        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        switch($errcode){
            case 0:
                $retinfo['errno'] = 0;
                $retinfo['msg'] = 'ok';
                break;
            default:
                $retinfo['errno'] = -1;
                $retinfo['msg'] = 'sendNiuxVip return : '.$errcode ;
                if (in_array($errcode, $noLogRtnArr)) {
                    //xlog( $url, 'notice', 'sendNiuxVip' );
                    //xlog( '[GameApi] Call api sendNiuxVip return error: '.$retJson, 'sendNiuxVip' );
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendNiuxVip');
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api sendNiuxVip return error: '.$retJson, 'sendNiuxVip');
                } else {
                    //xlog( $url, 'api');
                    //xlog( '[GameApi] Call api sendNiuxVip return error: '.$retJson, 'api');
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api sendNiuxVip return error: '.$retJson);
                }

                break;
        }
        return $retinfo;
    }


    /**
     * 查询最后登录游戏时间
     * http://dq2.niu.xunlei.com/act/getLastTime?userId=123540516
     * 成功返回： {"rtn":0,"data":{"lastLoginTime":"2012-11-19 18:08:53"}}
     * 失败返回:  {"rtnMsg":xxxx,"rtn":1}
     * @param array $info :
     * array(* $info：array(
     *     'userid'    => '1234'  //迅雷uid，必须
     *     'gameid'    => '000054'  //游戏id，可选
     *     'serverid'=>2, //可选
     *    )
     * @return array
     */
    static function getLastLogin($info = array())
    {
        $data = array();
        $data['userid'] = $info['userid'] ? $info['userid'] : '';
        $data['gameid'] = $info['gameid'] ? $info['gameid'] : '';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '';
        
        if (empty($data['userid'])) {
            return array('errno'=>323,'msg'=>'getLastLogin params error');
        }

        $api = 'http://dq2.niu.xunlei.com/act/getLastTime';
        $gameid = empty($data['gameid']) ? '' : '&gameId='. $data['gameid'] ;
        $serverid = empty($data['serverid']) ? '' : '&serverId='. $data['serverid'] ;
        
        $url = $api .'?userId='.$data['userid'] . $gameid . $serverid;

        $retJson = curl($url);
        
        //xlog($url, 'notice', 'getLastLogin');
        //xlog($retJson, 'notice', 'getLastLogin');
                
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getLastLogin');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'getLastLogin');

        $retArr = json_decode($retJson, true);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();
        
        $status = intval($retArr['rtn']);
        switch($status){
            case 0: $retinfo['errno'] = 0;
                    $retinfo['lastLoginTime'] = empty($retArr['data']['lastLoginTime']) ? '' : $retArr['data']['lastLoginTime'];
                break;
            default:$retinfo['errno'] = -1;
                    $retinfo['msg'] = $retArr['rtnMsg'];
                if ($retArr['rtnMsg'] && $retArr['rtnMsg'] != 'null') {
                    if (in_array($status, $noLogRtnArr)) {
                        //xlog( $url, 'notice', 'getLastLogin' );
                        //xlog( '[GameApi] Call api getLastLogin return error: '.$retJson, 'notice', 'getLastLogin' );
                                                    lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getLastLogin');
                                                    lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api getLastLogin return error: '.$retJson, 'getLastLogin');
                    } else {
                        //xlog( $url, 'api');
                        //xlog( '[GameApi] Call api getLastLogin return error: '.$retJson, 'api');
                                                    lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                                    lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api getLastLogin return error: '.$retJson);
                    }
                }

                break;
        }
        return  $retinfo;
    }
    
    
    
    /**
    * 查询首次登录游戏时间，可判断从某个时间起创建的用户
    *
    * 文档：http://wiki.niu.xunlei.com/doku.php?id=数据:dos接口&s[]=getfirstlogintime
    * @param array $info：array(
    *   'userid'    => '1234'  //迅雷uid，必须
    *   'gameid'    => '000054'  //游戏id，可选
    *   'serverid'=>2, //可选
    * );
    *
    * 调用接口：http://dq2.niu.xunlei.com/act/getFirstLoginTime?userId=123540516&gameId=000200
    *
    * 成功返回：{"rtn":0,"data":{"firstLoginTime":"2014-06-06 20:19:49"}}
    * 失败返回：{"rtn":1,"rtnMsg":"xxxx"}
    *
    */
    static function getFirstLoginTime($info = array())
    {
        $data = array();
        $data['userid'] = isset($info['userid']) ? $info['userid'] : '';
        $data['gameid'] = isset($info['gameid']) ? $info['gameid'] : '';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '';

        if (empty($data['userid'])) {
            return array('errno'=>321,'msg'=>'getFirstLoginTime params error');
        }
        
        $api = 'http://dq2.niu.xunlei.com/act/getFirstLoginTime';
        $gameid = empty($data['gameid']) ? '' : '&gameId='. $data['gameid'] ;
        $serverid = empty($data['serverid']) ? '' : '&serverId='. $data['serverid'] ;
        
        //$getUserInfo = $api .'?sessionid='.$data['sessionid'].'&userId='.$data['userid'] . $gameid . $serverid;
        $url = $api .'?userId='.$data['userid'] . $gameid . $serverid;

        //执行请求
        $retJson = curl($url);

        //xlog($url, 'notice', 'getFirstLoginTime');
        //xlog($retJson, 'notice', 'getFirstLoginTime');
                
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getFirstLoginTime');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'getFirstLoginTime');

        $retArr = json_decode($retJson, true);
        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();
        
        $status = intval($retArr['rtn']);
        switch($status){
            case 0:
                $retinfo['errno'] = 0;
                $retinfo['firstLoginTime'] = empty($retArr['data']['firstLoginTime']) ? '' : $retArr['data']['firstLoginTime'];
                break;
            default:
                $retinfo['errno'] = -1;
                $retinfo['msg'] = $retArr['rtnMsg'];
                // 忽略这样的错误，{"rtnMsg":null,"rtn":1}
                if ($retArr['rtnMsg'] != 'null') {
                    if (in_array($status, $noLogRtnArr)) {
                        //xlog( $url, 'notice', 'getFirstLoginTime' );
                        //xlog( '[GameApi] Call api getFirstLoginTime return error: '.$retJson, 'notice', 'getFirstLoginTime' );
                                                lp::log(FileLog::LOG_LEVEL_NOTICE, $url, 'getFirstLoginTime');
                                                lp::log(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api getFirstLoginTime return error: '.$retJson, 'getFirstLoginTime');
                    } else {
                        //xlog( $url, 'api');
                        //xlog( '[GameApi] Call api getFirstLoginTime return error: '.$retJson, 'api');
                                                lp::log(FileLog::LOG_LEVEL_API_ERROR, $url);
                                                lp::log(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api getFirstLoginTime return error: '.$retJson);
                    }
                }

                break;
        }
        return $retinfo;
    }
    

    /**
    * 查询用户金钻状态
    * @param
    * $info：array(
    *    'userid'   => '1234'  //迅雷uid，必须
    * );
    *
    * @return int, 2: 金钻年费用户; 1:金钻用户; 0:普通用户, 非金钻用户; -1: 参数错误，没有传userid
    */
    static function checkNiuxVip($info = array())
    {
        $data = array();
        $data['userid'] = $info['userid'];

        if (empty($data['userid'])) {
            return -1;
        }

        $url = 'http://jinzuan.niu.xunlei.com:9090/member/getmemberinfo.do?uid='.$data['userid'];

        $retJson = curl($url);

        //xlog($url, 'notice', 'checkNiuxVip');
        //xlog($retJson, 'notice', 'checkNiuxVip');
         
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'checkNiuxVip');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'checkNiuxVip');

        $retArr = json_decode($retJson, true);
        if (intval($retArr['rtn']) == 0 && intval($retArr['data']['memberStatus']) == 1) {
            $isAnnualMember = intval($retArr['data']['isAnnualMember']);
            if ($isAnnualMember) {
                return 2; //是金钻年费用户
            } else {
                return 1; //是金钻用户
            }
        }
        return 0; //普通用户, 非金钻用户
    }


    /**
    * niux发送奖品, 公共接口, 接口开发：jiaxin@xunlei.com
    *
    * 文档：
    * @param
    * $info：array(
    *   'actno' => 'xxx'  //活动编号（必填）
    *   'gid'=> ''  //必填，获取指定的奖品Id,由管理后台配置
    *   'userid'=>'', //可选
    *   'isjinzuan'=> 0, //可选
    *   'moduleid'=>1, //可选, 跟后台配置的模块编号一致，如果不需要区分模块可不传
    * );
    *
    * 调用接口返回：
    * {"rtn":0,"data":"奖品发放完成!"}
    *  0     成功
    *  2     活动状态－未开启
    *  3     活动状态－已关闭
    *  4     活动未开始
    *  5    活动已结束
    *  11   金卡vip等级不够
    *  12   参加活动总次数超过限制
    *  13   今天参加活动次数超过限制
    *  14   Ip次数超过限制
    *  15   活动奖品数据配置无效
    *  16   用户当天获得活动的某个奖品超过限制
    *  17   用户当前星期获得活动的某个奖品超过限制
    *  18   用户当月获得活动的某个奖品超过限制
    *  19   活动的奖品已经领完
    *  21   日期格式转换错误
    *  101  必须参数为空的错误
    *  103  活动数据不存在
    *  105  没有活动对应的Gift数据
    *  107  活动（模块）有多条gift配置数据
    *  109  活动（模块）没有gift配置数据
    *  201  发放奖品失败
    *  502  权限失效
    *  503  ip没有权限
    *  504  sign验证不通过
    *
    */
    static function sendGiftByCommonApi($info = array())
    {
        if (empty($info)) {
            exit(array('errno' => 1000, 'msg' => 'sendGiftByCommonApi params err'));
        }
        $data = array();
        $url = 'http://activity.niu.xunlei.com:8090/commonactivity/getGift_v2.do?';
        $key = 'sjq6n46r0dd4ikk';

        $data['actno'] = isset($info['actno']) ? $info['actno'] : 'niuxAct';
        $data['giftid'] = isset($info['gid']) ? $info['gid'] : 0;
        $data['userid'] = isset($info['userid']) ? $info['userid'] : '';
        $data['ip'] = getip();
        $data['isreissue'] = isset($info['isreissue']) ? $info['isreissue'] : 1; //选填，发放失败是否需要补发礼品
        $data['authid'] = isset($info['authid']) ? $info['authid'] : '00003';
        if (isset($info['moduleid']) && !empty($info['moduleid'])) {
            $data['moduleid'] =  $info['moduleid'] ;
        }
        $data['isjinzuan'] = isset($info['isjinzuan']) ? $info['isjinzuan'] : 0; // 选填，是否发放金钻奖品, 如果不传,奖品也能发，但只能在官网->我的奖品里展示，金钻官网里没有,1表示只有金钻才能领取


        $orderid = date('YmdHis') . rand(10000, 99999) . substr($data['actno'], 0, 20);
        $data['orderid'] = $orderid;

        $data['sign'] = s_sign_new($data, $key);
        $url .= http_build_query($data);

        //执行请求
        if (false === ( $retJson = curl($url) )) {
            return false;
        }
                
             
        //xlog($url, 'notice', 'sendGiftByCommonApi');
        //xlog($retJson, 'notice', 'sendGiftByCommonApi');
                
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendGiftByCommonApi');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'sendGiftByCommonApi');

        $retArr = json_decode($retJson, true);
                
        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();
        
        $status = intval($retArr['rtn']);
                
        if ($status != 0) {
            if (in_array($status, $noLogRtnArr)) {
                            //xlog( $url, 'notice', 'sendGiftByCommonApi' );
                            //xlog( '[GameApi] Call api sendGiftByCommonApi return error: '.$retJson, 'notice', 'sendGiftByCommonApi' );
                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendGiftByCommonApi');
                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api sendGiftByCommonApi return error: '.$retJson, 'sendGiftByCommonApi');
            } else {
                            //xlog( $url, 'api');
                            //xlog( '[GameApi] Call api sendGiftByCommonApi return error: '.$retJson, 'api');
                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api sendGiftByCommonApi return error: '.$retJson);
            }
        }
                
        return $retArr;
    }
    

    /**
    * 用户首次支付时间, 可判断是否在游戏支付过
    * @param
    * $info：array(
    *    'userid'   => '1234'  //迅雷uid，必须
    *    'gameid'   => '000054'  //游戏id，可选， 不填是平台数据，填了是游戏数据
    *    'serverid'=>2, //可选
    *   );
    *  http://dq2.niu.xunlei.com/act/getFirstPayTime?userId=123540516
    * 成功返回： {"rtn":0,"data":{"firstPayTime":"2012-11-19 18:08:53"}}
    * 失败返回:  {"rtnMsg":xxxx,"rtn":1}
    */
    static function getFirstPayTime($info = array())
    {
        $data = array();
        $data['userid'] = isset($info['userid']) ? $info['userid'] : '';
        $data['gameid'] = isset($info['gameid']) ? $info['gameid'] : '';
        $data['serverid'] = isset($info['serverid']) ? $info['serverid'] : '';
        
        if (empty($data['userid'])) {
            return array('errno'=>324,'msg'=>'getFirstPayTime params error');
        }

        $api = 'http://dq2.niu.xunlei.com/act/getFirstPayTime';
        $gameid = empty($data['gameid']) ? '' : '&gameId='. $data['gameid'] ;
        $serverid = empty($data['serverid']) ? '' : '&serverId='. $data['serverid'] ;
        
        $url = $api .'?userId='.$data['userid'] . $gameid . $serverid;

        $retJson = curl($url);
        
        //xlog($url, 'notice', 'getFirstPayTime');
        //xlog($retJson, 'notice', 'getFirstPayTime');
                
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getFirstPayTime');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'getFirstPayTime');
        

        $retArr = json_decode($retJson, true);
        
        $status = intval($retArr['rtn']);
        switch($status){
            case 0: $retinfo['errno'] = 0;
                    $retinfo['firstPayTime'] = empty($retArr['data']['firstPayTime']) ? '' : $retArr['data']['firstPayTime'];
                break;
            default:$retinfo['errno'] = -1;
                    $retinfo['msg'] = $retArr['rtnMsg'];
                break;
        }
        return  $retinfo;
    }

    /**
    * 发送第三方购物卡(使用劵)，如京东卡
    * 文档：http://wiki.niu.xunlei.com/doku.php?id=接口:牛xweb服务:优化券发放&s[]=giveoutcoupon
    * @param
    * $info：array(
    *    'actno' =>'', //活动编号
    *    'userid'   => '1234'  //迅雷uid，必须
    *    'couponType'=> 'jd'  //使用劵类型，京东使用劵： jd， 必须
    *    'couponValue'=>50, // 使用劵金额，必须
    *   );
    *
    * 请求响应：
    * callback({“rtn”:0,”msg”:“成功”})
    * rtn 0表示操作成功 11签名错误 13参数错误
    *
    */
    static function sendCoupon($info = array())
    {
        $key = 'polgfi0zyc';

        $data = array();
        $data['userId'] = isset($info['userid']) ? $info['userid'] : '';
        $data['couponType'] = isset($info['couponType']) ? $info['couponType'] : '';
        $data['couponValue'] = isset($info['couponValue']) ? $info['couponValue'] : '';
        $data['actNo'] = isset($info['actno']) ? $info['actno'] : 'niuxAct';
        $data['orderId'] = isset($info['orderId']) ? $info['orderId'] : date('YmdHis').rand(1000, 9999).'_'.$data['actNo'];
        
        if (empty($data['userId']) || empty($data['couponType']) || empty($data['couponValue'])) {
            return array('errno'=>330,'msg'=>'sendCoupon params error');
        }

        $api = 'http://dy.niu.xunlei.com/activity/giveOutCoupon.do?';

            $data['sign'] = s_sign_new($data, $key);
        
            $url = $api . http_build_query($data);

            $retJson = curl($url);
        
            //xlog($url, 'notice', 'sendCoupon');
            //xlog($retJson, 'notice', 'sendCoupon');

            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendCoupon');
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retJson, 'sendCoupon');

            $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

            //$retJson = 'callback({"data":{"userId":"123540516","seqId":260,"couponNo":"187d874ac849f525d6ec75107b17bfab24b01cdc36aedc01","couponPwd":"db8ccf59716a1546654bb0abbda66dd19af7c3576bb9981d","useTime":"2014-09-24 16:38:57","couponType":"jd","couponValue":50,"couponStatus":2,"useOrderNo":"201409241636436515_vippkg","useActNo":"vippkg"},"rtn":0,"rtnMsg":"成功"})';
            $retStr = trim($retJson, 'callback()');
            $retArr = json_decode($retStr, true);

            $status = intval($retArr['rtn']);

        if ($status !== 0) {
            if (in_array($status, $noLogRtnArr)) {
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendCoupon');
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api sendCoupon return error: '.$retJson, 'sendCoupon');
            } else {
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api sendCoupon return error: '.$retJson);
            }
        }
            return $retArr;
    }
    
    /**
    * 获取第三方购物卡(使用劵)，如京东卡的卡号和密码信息
    * 文档：
    * @param
    * $info：array(
    *    'actno' =>'', //活动编号
    *    'userid' =>'',
    *   );
    *
    * 请求响应，返回该用户在某个活动里获取的所有京东优惠券
    * callback({"rtn":0,"data":[{"userId":"123540516","seqId":824,"couponNo":"jdsdfs232d2jssdsfddd","couponPwd":"gfsd-sdf2-2222-sdaf","useTime":"2014-10-14 16:29:56","couponType":"jd","couponValue":50,"couponStatus":2,"useOrderNo":"201410141629563338_dtslot","useActNo":"dtslot"},{"userId":"123540516","seqId":512,"couponNo":"JDV2936126000490","couponPwd":"95CD-1779-A12E-6BDC","useTime":"2014-10-14 12:29:32","couponType":"jd","couponValue":50,"couponStatus":2,"useOrderNo":"201410141226539395_dtslot","useActNo":"dtslot"},{"userId":"123540516","seqId":511,"couponNo":"JDV2936126000489","couponPwd":"B155-A19D-C274-E46E","useTime":"2014-10-13 17:53:28","couponType":"jd","couponValue":50,"couponStatus":2,"useOrderNo":"201410131753281451_dtslot","useActNo":"dtslot"}]})
    *
    * rtn 0表示操作成功
    *
    */
    static function getCoupon($info = array())
    {

        $data = array();
        $url = 'http://dy.niu.xunlei.com/activity/getUserCouponsServer.do?';
        $key = 'sjq6n46r0dd4ikk';

        $data['actno'] = isset($info['actno']) ? $info['actno'] : '';
        $data['userid'] = isset($info['userid']) ? $info['userid'] : '';
        $data['authid'] = isset($info['authid']) ? $info['authid'] : '00003';
       
        if (empty($data['actno']) || empty($data['userid'])) {
            return array('errno'=>340,'msg'=>'getCoupon params error');
        }

        $data['sign'] = s_sign_new($data, $key);
        $url .= http_build_query($data);

        $retJson = curl($url);
        
        $retStr = trim($retJson, 'callback()');
        $retArr = json_decode($retStr, true);

        $status = intval($retArr['rtn']);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        switch($status){
            case 0: $retinfo['errno'] = 0;
                    $ret = array();
                foreach ($retArr['data'] as $item) {
                    $name = '';
                    if ($item['couponType'] == 'jd') {
                        $name = '京东'. $item['couponValue'] .'元购物卡';
                    }
                    $ret[] = array('couponNo'=>$item['couponNo'],'couponPwd'=>$item['couponPwd'],'name'=>$name, 'type'=>'coupon', 'addtime'=>$item['useTime']);
                }
                    $retinfo['coupon'] = $ret;
                break;
            default:$retinfo['errno'] = -1;
                if (in_array($status, $noLogRtnArr)) {
                                        //xlog( $url, 'notice', 'getCoupon' );
                                        //xlog( '[GameApi] Call api getCoupon return error: '.$retJson, 'notice', 'getCoupon' );
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'getCoupon');
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api getCoupon return error: '.$retJson, 'getCoupon');
                } else {
                                        //xlog($url, 'api');
                                        //xlog('[GameApi] Call api getCoupon return error: '.$retJson, 'api');
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api getCoupon return error: '.$retJson);
                }
                    $retinfo['msg'] = '没有购物卡信息';
                break;
        }
        return  $retinfo;
    }
    

    /**
    * 领取体验金钻接口
    * 文档：http://10.10.101.253/13_233_svn/XLNIUXGame/doc/技术方案文档/金钻体系接口文档/金钻体系接口文档V1.0.docx
    * @param
    * $info：array(
    *    'actno' =>'', //活动编号
    *    'userid'   => '1234'  //迅雷uid，必须
    *    'sendNum'=> 1  //赠送天数,目前支持 1、3、7天
    *   );
    *
    * 请求响应：
    * {“rtn”:0,”msg”:“成功”}
    *
    * 返回码rtn说明：
    *   0：赠送成功
    *   1: 参数错误
    *   2: 目前只能开通1、3、7天体验会员
    *   3: 开通天数必须大于零
    *   4: 请求超时
    *   5: 签名错误
    *   6: 未知错误
    *   8: 该订单号已存在
    *   9: 业务编号不存在
    *   10: 订单号过长
    *   11: 该赠送接口密钥未开放使用
    *   12: 该赠送接口密钥已经过期
    *   13: 正式会员不能开通体验会员 或 体验会员62天内不能连续开通
    *   14: 该ip在一天之内开通体验会员数较多
    *
    *
    */
    static function sendTrialNiuxVip($info = array())
    {
        $key = '117efdc7-6dc7-4ce8-ad13-eed0d2cd28c3';
        $bizNo = '200002'; //业务编号

        $data = array();
        $data['uid'] = isset($info['userid']) ? $info['userid'] : '';
        $data['actno'] = isset($info['actno']) ? $info['actno'] : 'niuxAct';
        $data['numValue'] = isset($info['sendNum']) ? $info['sendNum'] : 0;
        $data['orderid'] = isset($info['orderId']) ? $info['orderId'] : date('YmdHis').rand(100000, 999999).$bizNo;
        $data['bizNo'] = $bizNo;
        $data['timestamp'] = time();
        $data['ip'] = getip();
        
        if (empty($data['uid']) || empty($data['actno']) || empty($data['numValue'])) {
            return array('errno'=>360,'msg'=>'sendTrialNiuxVip params error');
        }

        $api = 'http://payjz.niu.xunlei.com:8091/jinzuan/experience.do?';

        $data['sign'] = md5($data['uid'] . $data['orderid'] . $data['actno'] . $data['numValue'] . $data['bizNo'] . $data['ip'] . $data['timestamp'] . $key);
        
        $url = $api . http_build_query($data);

        $retJson = curl($url);
        
        //xlog($url, 'notice', 'sendTrialNiuxVip');
        //xlog($retJson, 'notice', 'sendTrialNiuxVip');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendTrialNiuxVip');
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendTrialNiuxVip');
        
        $retArr = json_decode($retJson, true);
        
        $status = isset($retArr['rtn']) ? intval($retArr['rtn']) : -1;

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        switch($status){
            case 0: $retinfo['errno'] = 0;
                    $retinfo['msg'] = '恭喜，已开通体验金钻';
                break;
            default:$retinfo['errno'] = -1;
                if (in_array($status, $noLogRtnArr)) {
                                        //xlog( $url, 'notice', 'sendTrialNiuxVip' );
                                        //xlog( '[GameApi] Call api sendTrialNiuxVip return error: '.$retJson, 'notice', 'sendTrialNiuxVip' );
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, 'sendTrialNiuxVip');
                                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[GameApi] Call api sendTrialNiuxVip return error: '.$retJson, 'sendTrialNiuxVip');
                } else {
                                        //xlog( $url, 'api');
                                        //xlog( '[GameApi] Call api sendTrialNiuxVip return error: '.$retJson, 'api');
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[GameApi] Call api sendTrialNiuxVip return error: '.$retJson);
                }
                    $retinfo['msg'] = isset($retArr['data']) ? $retArr['data'] : '开通失败，请稍候再试。';
                break;
        }
        return  $retinfo;
    }

    /**
     * 发放QB直充接口 RTX@lujianming
     *
     * @param array $info
     * qq : 目标QQ号,必须
     * userid ： 迅雷用户ID，必须
     * username ： 迅雷用户名，必须
     * num : 数量，必须
     * actno ：活动编号，必须
     * noLogRtnArr : 不需要发日志错误邮件的接口返回码数组，可选
     *
     * @return array
     * errno === 0 表示有激活码code返回，否则无，具体原因可以输出该数组或者查看日志
     *
     */
    static function sendQB($info = array())
    {

        //========================TEST===============================
        // return array(
        // 	'errno' => 0,
        // 	'msg' => 'success'
        // );
        //===========================================================


        //日志标签
        $logTag = 'sendQB';
        
        //签名KEY
        $signKey = '24wetfge47rhgfr6876rftght';
        //getusername + userid + TargetAccount + BuyAmount + niuxactno

        $queryKeys = array('getusername','userid','TargetAccount','BuyAmount','niuxactno');
        $requireArr = array('username','userid','qq','num','actno');
        $queryData = array();
        $signStr = '';
        foreach ($requireArr as $index => $key) {
            $value = isset( $info[$key] ) ? $info[$key] : false;
            if ($value === false) {
                return array('errno'=>1,'msg'=>'param '.$key.' invalid');
            }
            $signStr .= $value;
            $queryData[$queryKeys[$index]] = $value;
        }
        
        $queryData['sign'] = md5($signStr . $signKey);

        $queryStr = http_build_query($queryData);

        $url = self::SENDQBURL . '?' . $queryStr;
        try {
            $retstr = curl($url);
            //xlog($url, 'notice', $logTag);
                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
        } catch (Exception $e) {
            $retstr = '';
            //xlog('curl error', 'notice', $logTag);
                        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'curl error', $logTag);
        }

        //xlog($retstr, 'notice', $logTag);
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retstr, $logTag);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        try {
            //解析为对象
            $ret = json_decode($retstr, true);
            if (!is_object($ret)) {
                throw new Exception("json_decode error", 1);
            }

            $retCode = intval($ret['rtn']);
                
            if ($retCode !== 0) {
                if ($retCode === 3) {
                    //充值中
                    lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[order in charging]', $logTag);
                } else {
                    if (in_array($retCode, $noLogRtnArr)) {
                    //xlog( $url, 'notice', $logTag );
                    //xlog( '[sendQB] Call api '. $logTag .' return error: '.$ret->rtnMsg, 'notice', $logTag );
                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
                            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[sendQB] Call api '. $logTag .' return error: '.$ret->rtnMsg, $logTag);
                    } else {
                        //xlog( $url, 'api' );
                        //xlog( '[sendQB] Call api '. $logTag .' return error: '.$ret->rtnMsg, 'api');
                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                        lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[sendQB] Call api '. $logTag .' return error: '.$ret->rtnMsg);
                    }
                }
            }
                $retinfo = $ret;
        } catch (Exception $e) {
            //解析出错
            $retinfo['errno'] = -2;
            $retinfo['msg'] = 'json_decode error';
            //xlog('json_decode error', 'notice', $logTag);
                lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'json_decode error', $logTag);
        }

        return $retinfo;

    }

    /**
     * 发手机短信接口 RTX@huangchunhui
     * @param array $info
     *  mobile : 目标手机号
     *  content : 需要发送的内容
     * @return array(
     *  errno : 0表示成功，-1表示失败
     *  msg : errno的描述
     *  data : true表示成功，false表示失败
     * )
     * @throws Exception mobile undefined
     * @throws Exception content undefined
     */
    static function sendShortMessage($info = array())
    {

        //检查必须项
        if (!isset($info['mobile'])) {
            throw new Exception('sendShortMessage param error : mobile undefined');
        }
        if (!isset($info['content'])) {
            throw new Exception('sendShortMessage param error : content undefined');
        }

        //设置查询参数
        $baseUrl = 'http://sms.pay.xunlei.com/sendsms';
        $logTag = 'sendShortMessage';

        $data = array();
        $key = 'jlewryiuyoqposkj';

        ksort($data);
        $query = urldecode(http_build_query($data));
        $data['signMsg'] = md5(iconv('utf-8', 'gbk', $query . $key));

        //设置默认项
        $data['bizNo'] = '000005';
        $data['version'] = 'v1.0';
        $data['pageCharset'] = 'GBK';
        if (isset($data['signMsg'])) {
            unset($data['signMsg']);
        }

        //设置url参数
        $data['phone'] = $info['phone'];
        $data['content'] = $info['content'];
        ksort($data);
        $query = urldecode(http_build_query($data));
        $data['signMsg'] = md5(iconv('utf-8', 'gbk', $query . $key));

        //执行查询
        $url = $baseUrl . '?' . http_build_query($data);
        try {
            $retstr = curl($url);
            //xlog($url, 'notice', $logTag);
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
        } catch (Exception $e) {
            $retstr = '';
            //xlog('curl error', 'notice', $logTag);
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'curl error', $logTag);
        }

        //xlog($retstr, 'notice', $logTag);
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retstr, $logTag);

        if (strpos($retstr, '<sendresult>00</sendresult>') === false) {
            //xlog('[smsApi]['.$data['mobile'].'] sendsms return error: '.$retstr, 'api');
            lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '[smsApi]['.$data['mobile'].'] sendsms return error: '.$retstr);
            $retinfo['errno'] = -1;
            $retinfo['msg'] = 'sendsms fail';
            $retinfo['data'] = false;
        } else {
            //xlog('[smsApi]['.$data['mobile'].'] sendsms success', 'notice', $logTag);
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '[smsApi]['.$data['mobile'].'] sendsms success', $logTag);
            $retinfo['errno'] = 0;
            $retinfo['msg'] = 'sendsms success';
            $retinfo['data'] = true;
        }

        return $retinfo;

    }

    /**
     * 通过游戏内信息获取用户信息 RTX@yinqiang
     * @param array $info
     */
    static function getUserInfoByGameRoleInfo($info = array())
    {

    }

    /**
     * 获取用户的手机号码 RTX@lisu
     * @param array $info：
     *  userid:必须，用户ID
     *  noLogRtnArr:可选，不要记录错误的返回值
     * @return array :
     *  errno : 0表示成功获取可靠数据
     *  msg : 对errno的描述
     *  data : 可能的数据
     * @throws Exception userid undefined
     */
    static function getUserPhone($info = array())
    {
        //检查必须项
        if (!isset($info['userid'])) {
            throw new Exception('getUserPhone param error : userid undefined');
        }

        //设置查询参数
        $baseUrl = 'http://dy.niu.xunlei.com/customer/getcustomerphone.do';
        $logTag = 'getUserPhone';

        $data = array();

        //设置默认项

        //设置url参数
        $data['uid'] = $info['userid'];

        //执行查询
        $url = $baseUrl . '?' . http_build_query($data);
        try {
            $retstr = curl($url);
           // xlog($url, 'notice', $logTag);
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
        } catch (Exception $e) {
            $retstr = '';
            //xlog('curl error', 'notice', $logTag);
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'curl error', $logTag);
        }

        //xlog($retstr, 'notice', $logTag);
        lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $retstr, $logTag);

        $noLogRtnArr = isset( $info['noLogRtnArr'] ) ? $info['noLogRtnArr'] : array();

        try {
            //解析为对象
            $ret = json_decode($retstr);
            if (!is_object($ret)) {
                throw new Exception("json_decode error", 1);
            }

            $retCode = intval($ret->rtn);

            if (0 === $retCode && isset($ret->data->phone) && $ret->data->phone != null) {
                $retinfo['errno'] = 0;
                $retinfo['msg'] = 'success';
                $retinfo['data'] = $ret->data->phone;
            } else {
                $retinfo['errno'] = -1;
                $retinfo['msg'] = $ret->data;
                $retinfo['data'] = 0;
                if (in_array($retCode, $noLogRtnArr) || $retCode == 3) {
                  //  xlog( $url, 'notice', $logTag );
                  //  xlog( '['.$logTag.'] Call api '. $logTag .' return error: '.$retinfo['msg'], 'notice', $logTag );
                    lp::log()->write(FileLog::LOG_LEVEL_NOTICE, $url, $logTag);
                    lp::log()->write(FileLog::LOG_LEVEL_NOTICE, '['.$logTag.'] Call api '. $logTag .' return error: '.$retinfo['msg'], $logTag);
                } else {
                    //xlog( $url, 'api' );
                    //xlog( '['.$logTag.'] Call api '. $logTag .' return error: '.$retinfo['msg'], 'api');
                    lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, $url);
                    lp::log()->write(FileLog::LOG_LEVEL_API_ERROR, '['.$logTag.'] Call api '. $logTag .' return error: '.$retinfo['msg']);
                }
            }
        } catch (Exception $e) {
            //解析出错
            $retinfo['errno'] = -2;
            $retinfo['msg'] = 'json_decode error';
            $retinfo['data'] = 0;
            //xlog('json_decode error', 'notice', $logTag);
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'json_decode error', $logTag);
        }

        return $retinfo;
    }
}

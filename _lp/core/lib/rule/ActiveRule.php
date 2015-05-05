<?php
/**
 * 活动访问控制类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\lib\rule;

use _lp\core\lib\rule\AccessRule;
use _lp\core\lib\GameApi;
use model\PhoneBind;
use model\Gift;
use _lp\lp;
use Exception;

class ActiveRule extends AccessRule
{
    
    /**
     * @var string 用户id
     */
    public $userId;
    
    /**
     * @var string 用户名
     */
    public $userName;
    /**
     * @var string 活动编号
     */
    public $actNo;
    /**
     * @var string 规则对应活动的游戏id
     */
    public $gameId;
    /**
     * @var array 额外参数
     */
    public $parms = array();


    public function __construct($rule = array(), $parms = array())
    {
        parent::__construct($rule, lp::App()->controller, lp::App()->action);
        $this->userId = lp::App()->user->userId;
        $this->userName = lp::App()->user->userName;
        $this->actNo = lp::App()->act->actNo;
        $this->gameId = lp::App()->act->gameId;
        $this->parms = $parms;
    }
    
    /**
     * 检测手机绑定情况
     * @param array $config
     * @return mixed -1  checked = false 不检测手机绑定
     *               true 手机绑定成功
     *               false 手机绑定失败
     */
    public function checkBindPhone($config)
    {
        if (!isset($config['checked'])) {
            throw new Exception('<pre>'. __METHOD__ .' error 必须配置checked参数 ：<br /> ' . print_r($config, true) . '</pre>');
        }
        if ($config['checked'] === true) {
            $phoneBind = new PhoneBind();
            $result  = $phoneBind->exists(array(
                'userid' => $this->userId,
                'act' => $this->actNo
            ));
            return $result;
        }
        return -1;
    }
    
    /**
     * 检测角色等级
     * @param array $config
     * @throws Exception
     */
    public function checkRoleLevel($config)
    {
        //if (!isset($config['max']) || !isset($config['min']) || !isset($this->parms['serverId'])) {
        if (!isset($config['max']) ||  !isset($this->parms['serverId'])) {
            throw new Exception('<pre>'. __METHOD__ .' error 必须配置max, ,以及额外参数serverId ：<br /> ' . print_r($config, true) . '</pre>');
        }
        $maxLevel = intval($config['max']);
        $minLevel = intval($config['min']);
        $serverId = intval($this->parms['serverId']);
        $data = array( 'username' => $this->userName, 'gameid' => $this->gameId, 'serverid' => $serverId, 'sessionid' => get_cookie('sessionid') );
        $result = GameApi::getGameLevel($data);
        if (isset($result['errno']) && $result['errno'] === 0) {
            if( isset($result['level'])  ) { 
                if( $result['level'] < $minLevel || ( !empty($maxLevel) && $maxLevel < $result['level']   )  ) { 
                    return array(
                        'errno' => -15,
                        'msg' => '级别不在要求等级内',
                        'data' => $result
                    );
                } else { 
                    return TRUE;
                }
            } 
        }
        return $result;
    }
    
    /**
     * 检测周期内用户的礼包数
     * @param array $config
     * @return mixed 
     */
    public function checkUserGiftNum($config)
    {
        if ( empty($config) ) {
             throw new Exception('<pre>'. __METHOD__ .' error  参数错误<br /> ' . print_r($config, true) . '</pre>');
        }
        $data = array();
        $gift = new Gift();
        $conditions = array(
            'userid' => $this->userId,
            'status' => 1,
            'act' => $this->actNo
        );
        $result = array();
        foreach ($config as $key => $giftNum) {
            preg_match("/^d(\d+)$/i", $key, $matchs);
            if (empty($matchs) || !isset($matchs[1])) {
                throw new Exception('<pre>'. __METHOD__ .' error  参数错误<br /> ' . print_r($config, true) . '</pre>');
            }
            $conditions['addtime'] = array('condition'=>'>=','value'=>date('Y-m-d', time() - (intval($matchs[1]) - 1) * 24 * 60 * 60));
            if (false !== ( $count = $gift->count($conditions) )) {
                if ($count < $giftNum) {
                    $result[$key] = true;
                } else {
                    $result[$key] = $count;
                }
            } else {
                $result[$key] = $count;
            }
        }
        while ($_result = current($result)) {
            if ($_result !== true) {
                return $result;
            }
              next($result);
        }
        return true;
    }
    /**
     *  检测周期内服务器可领取礼包量
     * @param array $config
     */
    public function checkServerGiftNum($config)
    {
        if (empty($config) || !isset($this->parms['serverId'])) {
             throw new Exception('<pre>'. __METHOD__ .' error  参数错误<br /> ' . print_r($config, true) . '</pre>');
        }
        $data = array();
        $gift = new Gift();
        $conditions = array(
            //'userid' => $this->userId,
            'serverid' => $this->parms['serverId'],
            'status' => 1,
            'act' => $this->actNo
        );
        $result = array();
        foreach ($config as $key => $giftNum) {
            preg_match("/^d(\d+)$/i", $key, $matchs);
            if (empty($matchs) || !isset($matchs[1])) {
                throw new Exception('<pre>'. __METHOD__ .' error  参数错误<br /> ' . print_r($config, true) . '</pre>');
            }
            $conditions['addtime'] = array('condition'=>'>=','value'=>date('Y-m-d', time() - (intval($matchs[1]) - 1) * 24 * 60 * 60));
            if (false !== ( $count = $gift->count($conditions) )) {
                if ($count < $giftNum) {
                    $result[$key] = true;
                } else {
                    $result[$key] = $count;
                }
            } else {
                $result[$key] = $count;
            }
        }
        while ($_result = current($result)) {
            if ($_result !== true) {
                return $result;
            }
              next($result);
        }
        return true;
    }
    /**
     * 检测总礼包数
     * @param array $config
     */
    public function checkTotalGiftNum($config)
    {
        if (empty($config) || !isset($config['num'])) {
             throw new Exception('<pre>'. __METHOD__ .' error  参数错误<br /> ' . print_r($config, true) . '</pre>');
        }
        $num = intval($config['num']);
        $gift = new Gift();
        $conditions = array(
            'act' => $this->actNo,
            'status' => 1,
        );
        if (false !== ( $count = $gift->count($conditions) )) {
            if ($count < $num) {
                return true;
            }
        }
        return $count;
    }
}

<?php
/**
 * 礼包核心类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\lib;

use _lp\lp;
use \ReflectionClass;

class CoreGiftPacket
{
    
    /**
     * @var string 礼包标示
     */
    public $packetKey = "";
    
    /**
     * @var string 礼包类型
     */
    public $packetType = "";
    
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
     * @var string 游戏id
     */
    public $gameId;
    
    /**
     * @var string 服务器id
     */
    public $serverId;
    
    /**
     * @var int 礼包名
     */
    public $packetName;
    
    /**
     * @var int 礼包id
     */
    public $packetId;
    /**
     * @var string 礼包模块id
     */
    public $moudleId = 1;
    /**
     * @var int 发送数量
     */
    public $sendNum = 0;
    
    /**
     * @var float 多奖品中被抽取的概率
     */
    public $rate = 1;

    /**
     * @var int  礼包标示
     */
    public $id;

    /**
     * @var bolean 是否发送礼包
     */
    public $sendModel = true;
    
    /**
     * @var object 检测规则
     */
    public $checkRule;

    public function __construct($data = array())
    {
        $this->userId = lp::App()->user->userId;
        $this->userName = lp::App()->user->userName;
        $this->actNo = lp::App()->act->actNo;
        $this->gameId = lp::App()->act->gameId;
        
        $this->packetKey = 'packet_' . $this->packetType . '_' . md5(implode('', array_keys($data)) .implode('', array_values($data)));
        if (!empty($data)) {
            foreach ($data as $attr => $value) {
                if (property_exists(__CLASS__, $attr)) {
                    $this->{$attr} = $value;
                }
            }
        }
    }
    /**
     * @return array 获取礼包属性设置
     */
    public function getAttributes()
    {
        $self = new ReflectionClass(__CLASS__);
        $propertiesArr = $self->getProperties();
        $attrs = array();
        while ($properties = current($propertiesArr)) {
            $attrs[$properties->name] = $this->{$properties->name};
            next($propertiesArr);
        }
        return $attrs;
    }
    
    /**
     * 添加礼包规则
     * 
     * @param string $ckeckClass 检测类名
     * @param type $rules 检测规则
     * @return  \_lp\core\lib\CoreGiftPacket  
     */
    public function addRule($checkClass = '', $rules = array(), $extraParms = array() ) { 
        $attributes = $this->getAttributes();
        unset($attributes['checkRule']);
        $parms = array_merge($extraParms, $attributes);
        if( class_exists($checkClass) ) { 
            $this->checkRule = new $checkClass($rules, $parms);
        }
        return $this;
    }

    /**
     *  发送礼包
     * @return mixed
     */
    public function doSend()
    {
        if( TRUE === $this->_checkRule() ) { 
            $data = array();
            $result = false;
            if ($this->sendModel === true) {
                $result = $this->send();
                if (isset($result['errno']) && $result['errno'] == 0) {
                    $data = array(
                        'status' => 1,
                        'code' => isset($result['code']) ? $result['code'] : '',
                        'coupon_no' => isset($result['couponNo']) ? $result['couponNo'] : '',
                        'coupon_pwd' => isset($result['couponPwd']) ? $result['couponPwd'] : ''
                    );
                } else {
                    $data['status'] = -2;
                }
            }
            if ($this->sendModel !== true || $this->packetType === 'cash' || !$result) {
                $data['status'] = -1;
            }
            $this->afterSend($data);
            return $result;
        }
        return FALSE;
    }
    
    /**
     * 检测礼包规则
     * 
     * @return boolean
     */
    private function _checkRule() { 
        if( is_object($this->checkRule) ) { 
            if( $this->checkRule instanceof rule\ActiveRule ) { 
                return $this->checkRule->onCheck();       
            } else { 
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, '礼包规则配置错误！');
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     *  发送礼包动作
     */
    protected function send()
    {
        
    }
    
    /**
     * @param array 额外存入db的数据
     * @return boolean
     */
    protected function afterSend($extraData = array())
    {
        
    }
}

<?php
/**
 * 通用礼包类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace lib\act\giftPacket;

use _lp\core\lib\GameApi;
use lib\act\giftPacket\BaseGiftPacket;

class CommonGiftPacket extends BaseGiftPacket
{
    
    /**
     * @var string 礼包类型
     */
    public $packetType = 'common';
    
    /**
     * @var int 发送失败是否补发奖品
     */
    public $isReissue = 1;
    /**
     * @var string
     */
    public $authId = '00003';
    
    /**
     * @var int 1 表示只有金钻才能领取
     */
    public $isJinzuan = 0;
    
    /**
     * @var array 日志选择数组
     */
    public $noLogRtnArr = array();

    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    
    /**
     *  发送通用礼包
     * @return mixed
     */
    public function send()
    {
        if (empty($this->actNo) || empty($this->packetId) || empty($this->userId) || empty($this->userName)) {
            return false;
        }
        $parms = array(
            'actno' => $this->actNo,
            'gid' => $this->packetId,
            'userid' => $this->userId,
            'isreissue' => $this->isReissue,
            'authid' => $this->authId,
            'moduleid' => $this->moudleId,
            'isjinzuan' => $this->isJinzuan,
            'noLogRtnArr' => $this->noLogRtnArr
        );
        $result = GameApi::sendGiftByCommonApi($parms);
        return $result;
    }
}

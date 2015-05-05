<?php
/**
 * niux现金劵类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace lib\act\giftPacket;

use _lp\core\lib\GameApi;
use lib\act\giftPacket\BaseGiftPacket;

class NiuxCashGiftPacket extends BaseGiftPacket
{
    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    
    /**
     *  发送代金券
     */
    public function send()
    {
        if (empty($this->userId) || empty($this->packetId) || empty($this->actNo)) {
            return false;
        }
        $parms = array(
            'userid' => $this->userId,
            'batid' =>  $this->packetId,
            'actno' => $this->actNo
        );
        $result = GameApi::sendNiuxCash($parms);
        return $result;
    }
}

<?php
/**
 * 优惠券礼包
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace lib\act\giftPacket;

use _lp\core\lib\GameApi;
use lib\act\giftPacket\BaseGiftPacket;

class CouponGiftPacket extends BaseGiftPacket
{
    /**
     * @param string 优惠价类型
     */
    
    public $couponType;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    
    public function send()
    {
        if (empty($this->actNo) || empty($this->userId) || empty($this->userName) ||
            empty($this->sendNum) || empty($this->couponType) ) {
            return false;
        }
        $parms = array(
            'actno' => $this->actNo,
            'userid' => $this->userId,
            'couponType' => $this->couponType,
            'couponValue' => $this->sendNum
        );
        $result = GameApi::sendCoupon($parms);
        return $result;
    }
}

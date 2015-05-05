<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace lib\act\giftPacket;

use lib\act\giftPacket\GameCodeGiftPacket;
use lib\act\giftPacket\CouponGiftPacket;
use lib\act\giftPacket\CommonGiftPacket;
use lib\act\giftPacket\NiuxCashGiftPacket;
use lib\act\giftPacket\QbGiftPacket;
use lib\act\giftPacket\YbGiftPacket;
use _lp\core\lib\FileLog;
use _lp\lp;

class GiftPacket
{
    /**
     * @var string 通用礼包类型
     */
    const PACKET_TYPE_COMMON='Common';
    /**
     * @var string 激活码类型
     */
    const PACKET_TYPE_GAMECODE='GameCode';
    /**
     * @var string 现金卷类型
     */
    const PACKET_TYPE_COUPON='Coupon';
    /**
     * @var string niux现金卷
     */
    const PACKET_TYPE_NIUX_CASH='NiuxCash';
    /**
     * @var string Q币
     */
    const PACKET_TYPE_QB='Qb';
    /**
     * @var string 元宝
     */
    const PACKET_TYPE_Yb='Yb';
    

    public static function createPacket($type, $data = array())
    {
        switch ($type) {
            case self::PACKET_TYPE_COMMON:
                return self::createCommonPacket($data);
            case self::PACKET_TYPE_GAMECODE:
                return self::createGameCodePacket($data);
            case self::PACKET_TYPE_COUPON:
                return self::createCouponPacket($data);
            case self::PACKET_TYPE_NIUX_CASH:
                return self::createNiuxCashPacket($data);
            case self::PACKET_TYPE_QB:
                return self::createQbPacket($data);
            case self::PACKET_TYPE_Yb:
                return self::createYbPacket($data);
            default:
                $className = ucwords($type) . 'GiftPacket';
                if (class_exists($className)) {
                    return new $className;
                }
                lp::App()->log->write(FileLog::LOG_LEVEL_ERROR, '礼包类型：' . $className . '不存在！');
                return false;
        }
    }
   
    public static function createCommonPacket($data = array())
    {
        return new CommonGiftPacket($data);
    }
    
    public static function createGameCodePacket($data = array())
    {
        return new GameCodeGiftPacket($data);
    }
    
    public static function createCouponPacket($data = array())
    {
        return new CouponGiftPacket($data);
    }
    
    public static function createNiuxCashPacket($data = array())
    {
        return new NiuxCashGiftPacket($data);
    }
    
    public static function createQbPacket($data = array())
    {
        return new QbGiftPacket($data);
    }
    public static function createYbPacket($data = array())
    {
        return new YbGiftPacket($data);
    }
}

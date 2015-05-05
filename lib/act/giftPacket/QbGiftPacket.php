<?php
/**
 * Q币礼包类
 *
 * @author chenmiao<chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace lib\act\giftPacket;

use _lp\core\lib\GameApi;
use lib\act\giftPacket\BaseGiftPacket;

class QbGiftPacket extends BaseGiftPacket
{
    
    /**
     * @var string qq号
     */
    public $uin;
    
    public function __construct($data = array())
    {
        parent::__construct($data);
    }

    /**
     *  发送Q币
     * @return mixd
     */
    public function send()
    {
        if (empty($this->userId) || empty($this->userName) || empty($this->uin) || empty($this->actNo)) {
            return false;
        }
        if (!preg_match('/\d{5,15}/', $this->uin)) {
            return false;
        }
        $parms = array(
            'username' => $this->userName,
            'userid' => $this->userId,
            'actno' => $this->actNo,
            'qq' => $this->uin,
            'num' => $this->sendNum
        );
        $result = GameApi::sendQB($parms);
        return $result;
    }
}

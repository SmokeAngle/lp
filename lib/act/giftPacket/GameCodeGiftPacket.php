<?php
/**
 * 激活码礼包
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace lib\act\giftPacket;

use _lp\core\lib\GameApi;
use lib\act\giftPacket\BaseGiftPacket;

class GameCodeGiftPacket extends BaseGiftPacket
{
    
    /**
     * @var string 礼包类型
     */
    public $packetType = 'gamecode';
    /**
     *
     * @var bolean 默认false为不重新获取
     */
    public $getMore = false;


    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    
    protected function send()
    {
        $parms = array( 'username' => $this->userName,
                        'userid' => $this->userId,
                        'gameid' => $this->gameId,
                        'batid' => $this->packetId,
                        'getmore' => $this->getMore,
                        'serverid' => $this->serverId,
                        'actno'=> $this->actNo
        );
        $result = GameApi::sendGameCard($parms);
        return $result;
    }
}

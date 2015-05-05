<?php
/**
 * 元宝礼包类
 *
 * @author chenmiao<chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace lib\act\giftPacket;

use _lp\core\lib\GameApi;
use lib\act\giftPacket\BaseGiftPacket;

class YbGiftPacket extends BaseGiftPacket
{
    
    /**
     * @param string 角色id
     */
    public $roleId;
    
    /**
     * @var string 角色名
     */
    public $roleName;

    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    /**
     *  元宝发送
     * @return mixed
     */
    public function send()
    {
        if (empty($this->userId) || empty($this->userName) || empty($this->actNo) ||
            empty($this->serverId) || empty($this->sendNum) || empty($this->gameId)  ) {
            return false;
        }
        $data = array(
                'username' => $this->userName,
                'userid' => $this->userId,
                'gameid' => $this->gameId,
                'actno' => $this->actNo, //活动编号
                'serverid' => $this->serverId,
                'num' => $this->sendNum,
                'roleid'=> $this->roleId,
                'rolename'=> $this->roleName
        );
        $result = GameApi::sendYB($data);
        return $result;
    }
}

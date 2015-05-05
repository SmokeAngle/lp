<?php
/**
 * 礼包类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace lib\act\giftPacket;

use _lp\lp;
use _lp\core\lib\FileLog;
use _lp\core\lib\CoreGiftPacket;
use model\Gift;

class BaseGiftPacket extends CoreGiftPacket
{
    
    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    
    /**
     * @param array 额外存入db的数据
     * @return boolean
     */
    public function afterSend($extraData = array())
    {
        if (empty($this->userId) || empty($this->userName) || empty($this->actNo) || empty($this->packetType)) {
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, __CLASS__ . '->' . __METHOD__ . '() 参数错误！');
            return false;
        }
        
        $data = array(
            'userid'=> $this->userId,
            'username'=> $this->userName,
            'gameid'=> $this->gameId,
            'act'=> $this->actNo,
            'serverid'=> $this->serverId,
            'giftid' => $this->id,
            'giftname'=> $this->packetName,
            'num' => $this->sendNum,
            'addtime' => date('Y-m-d H:i:s', time()),
            'type' => strtolower($this->packetType),
            'ip' => getip()
        );
        $data = array_merge($data, $extraData);
        $gift = new Gift();
        if (false === $gift->insert($data)) {
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, __CLASS__ . '->' . __METHOD__ . '写入数据失败');
            lp::log()->write(FileLog::LOG_LEVEL_NOTICE, 'data:\r\n' . print_r($data, true));
        }
    }
}

<?php
/**
 * @author chenmiao<chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;
use _lp\lp;

/**
 * 预约信息表对应的数据类
 *
 * 用法介绍请参看基类注释说明
 *
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致
 */
class Appointment extends BaseModel
{
    /**
     * @var int PRIKEY
     */
    public $id;

    /**
     * @var string 活动名
     */
    public $act;

    /**
     * @var int 用户id
     */
    public $userid;

    /**
     * @var string 游戏id
     */
    public $gameid;

    /**
     * @var int 服务器id
     */
    public $serverid;

    /**
     * @var string 客户端ip
     */
    public $ip;

    /**
     * @var datetime 预约时间
     */
    public $addtime;

    /**
     * @var int 状态
     */
    public $status;

    public function __construct()
    {
        parent::__construct();
        $this->addtime = date('Y-m-d H:i:s');
        $this->ip = getip();
    }

    /**
     *  判断用户是否已经预约
     * @param int $serverId 服务器id
     * @param string $gameId 游戏id
     * @param string $userId 用户id
     * @param string $actNo 活动编号
     * @return int|bolean  -1 , -2： 参数错误
     *                        false ： 未预约
     *                        true : 已预约
     */
    public function isAped($serverId = null, $gameId = null, $userId = null, $actNo = "")
    {
        if (empty($serverId)) {
            return -1;
        }
        $_gameId = !empty($gameId) ? $gameId : lp::App()->act->gameId;
        $_userId = !empty($userId) ? $userId : lp::App()->user->userId;
        $_actNo = !empty($actNo) ? $actNo : lp::App()->act->actNo;
        
        if (empty($_gameId) || empty($_userId) || empty($_actNo)) {
            return -2;
        }
        $conditions = array('act' => $_actNo, 'userid' => $_userId,'gameid' => $_gameId, 'serverid' => $serverId);
        if (false === ( $count = $this->count($conditions) )) {
            return -3;
        }
        return $count >= 1 ? true : false;
    }

    public function getTableName()
    {
        return 't_appointment';
    }
}

<?php
/**
 * @author chenmiao<chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 抽奖表对应的数据类
 *
 * 用法介绍请参看基类注释说明
 *
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致
 */
class Lot extends BaseModel
{
    
    /**
     * @var int 自增id
     */
    public $id;

    /**
     * @var int 迅雷数字账号
     */
    public $userid;

    /**
     * @var string 用户名
     */
    public $username;

    /**
     * @var string 游戏id
     */
    public $gameid;

    /**
     * @var string 活动名
     */
    public $act;

    /**
     * @var string 服务器id
     */
    public $serverid;

    /**
     * @var string 角色id
     */
    public $roleid;

    /**
     * @var int 礼包id
     */
    public $giftid;

    /**
     * @var string 新手卡类型卡号
     */
    public $code;

    /**
     * @var int 模块编号
     */
    public $module;

    /**
     * @var string 礼品类型
     */
    public $type;

    /**
     * @var string 抽奖用户ip
     */
    public $ip;

    /**
     * @var int 礼包状态
     */
    public $status;

    /**
     * @var string 时间戳 添加时间
     */
    public $addtime;

    public function __construct()
    {
        parent::__construct();
        $this->addtime = date('Y-m-d H:i:s');
        $this->ip = getip();
    }
    
    public function getTableName()
    {
        return 't_lot';
    }
}

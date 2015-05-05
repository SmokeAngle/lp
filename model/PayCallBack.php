<?php
/**
 * @author chenmiao<chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 充值回调表对应的数据类
 *
 * 用法介绍请参看基类注释说明
 *
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致
 */
class PayCallBack extends BaseModel
{
    
    /**
     * @var int PRIKEY
     */
    public $id;

    /**
     *
     * @var int 迅雷id
     */
    public $userid;

    /**
     * @var string 用户名
     */
    public $username;

    /**
     * @var int 充值金额
     */
    public $money;

    /**
     * @var string 游戏id
     */
    public $gameid;

    /**
     *
     * @var string 活动名
     */
    public $act;

    /**
     * @var int 服务器id
     */
    public $serverid;

    /**
     * @var string 角色id
     */
    public $roleid;

    /**
     * @var string, 金钻回调时，type=niuxvip,其他情况为空
     */
    public $type;

    /**
     * @var string 订单id
     */
    public $orderid;

    /**
     * @var int 状态
     */
    public $status;

    /**
     * @var int
     */
    public $ext;

    /**
     * @var string 订单生成时间（date 格式）
     */
    public $addtime;

    public function __construct()
    {
        parent::__construct();
        $this->addtime = date('Y-m-d H:i:s');
    }
    
    public function getTableName()
    {
        return 't_pay_callback';
    }
}

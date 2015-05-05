<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 礼包表对应的数据类
 *
 * 用法介绍请参看基类注释说明
 * 
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致
 */
class Gift extends BaseModel
{
    
    /**
     * @var int PRIKEY
     */
    public $id;

    /**
     * @var int 用户id
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
     * @var string 任务id
     */
    public $taskid;

    /**
     * @var int 服务器id
     */
    public $serverid;

    /**
     * @var int 礼包号
     */
    public $giftid;
    
    /**
     * @var string 礼包名
     */
    public $giftname;

    /**
     * @var int 发送数量
     */
    public $num;

    /**
     * @var string 兑换码
     */
    public $code;

    /**
     * @var string 兑换劵编号
     */
    public $coupon_no;

    /**
     * @var string 兑换券密码
     */
    public $coupon_pwd;

    /**
     *
     * @var string 礼包类型 cash gamecard  gift jifen niuxcash coupon
     */
    public $type;

    /**
     * @var string 来源ip
     */
    public $ip;

    /**
     * @var int 状态
     */
    public $status;

    /**
     * @var string 记录生成时间
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
        return 't_gift';
    }
}

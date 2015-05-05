<?php
/**
 * @author caiwenxiong <caiwenxiong@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 做任务的记录表对应的数据类
 *
 * 做任务获得分数(积分/点数等)，然后用获得的分数兑换奖品，通常用于这类型的活动
 *
 * 用法介绍请参看基类注释说明
 * 
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致
 */
class Task extends BaseModel
{

    /**
     * @var int PRIKEY
     */
    public $id;

    /**
     * @var int 用户迅雷帐号userid
     *
     */
    public $userid;

    /**
     * @var string 用户迅雷帐号名称
     *
     */
    public $username;

    /**
     * @var int 任务id
     *
     */
    public $taskid;

    /**
     * @var string 游戏id
     *
     */
    public $gameid;

    /**
     * @var string 游戏活动编号，在管理后台申请生成
     *
     */
    public $act; 

    /**
     * @var int 游戏区服
     *
     */
    public $serverid;

    /**
     * @var int 次数
     */
    public $num;
	
    /**
     * @var string 来源ip
     */
    public $ip;

    /**
     * @var int 状态
     */
    public $status;

    /**
     * @var string 插入时间, datetime格式
     *
     */
    public $addtime;
    
    function __construct()
    {
        parent::__construct();
        $this->addtime = date('Y-m-d H:i:s');
        $this->ip = getip();
    }

    /**
     *  声明对应的表名
     *
     */
    public function getTableName(){
        return 't_task';
    }

}
?>

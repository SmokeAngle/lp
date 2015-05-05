<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 抽奖信息对应的数据类
 *
 * 用法介绍请参看基类注释说明
 *
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致
 */
class LotInfo extends BaseModel
{
    /**
     * @var int  自增id
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
     * @var string 服务器id
     */
    public $serverid;

    /**
     * @var int 总次数
     */
    public $totaltimes;

    /**
     * @var int 已抽奖次数
     */
    public $lottimes;

    /**
     * @var string 最后抽奖时间
     */
    public $lastlottime;

    /**
     * @var string 最后添加时间
     */
    public $lastaddtime;
    
    /**
     * @var 最后抽奖ip
     */
    public $lastip;

    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     *  根据用户id获取总抽奖次数
     *
     * @param int $userId 用户id
     * @return boolan|int
     */
    public function getTotalTimesByUid($userId = null, $actNo = "")
    {
        if (empty($userId) || empty($actNo)) {
            return false;
        }
        $conditions = [ 'userid' => $userId, 'act' => $actNo ];
        if (false !== ( $dataResult = $this->find($conditions) )) {
            var_dump($dataResult);
        }
        return false;
    }


    public function getTableName()
    {
        return 't_lotinfo';
    }
}

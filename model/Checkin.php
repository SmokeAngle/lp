<?php
/**
 * @author caiwenxiong <caiwenxiong@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 签到日历版对应的数据类
 *
 * 用法介绍请参看基类注释说明
 *
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致  
 */
class Checkin extends BaseModel
{

    /**
     * @var int PRIKEY
     */
    public $id;

    /**
     * @var string 用户迅雷帐号userid
     *
     */
    public $userid;

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
     * @var int 签到次数
     *
     */
    public $signcount;

    /**
     * @var string 最后一次签到时间
     */
    public $lastmodify;
	
    /**
     *  @var int 总分数
     */
    public $totalscore;

    /**
     * @var string 签到历史记录，用 1110101 这样表示
     */
    public $history;

    /**
     * @var string 扩展字段
     */
    public $ext;

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
        return 't_checkin';
    }

}
?>

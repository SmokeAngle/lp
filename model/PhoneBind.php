<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 手机绑定对应的数据类
 *
 * 用法介绍请参看基类注释说明
 *
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致
 */
class PhoneBind extends BaseModel
{
    /**
     * @var int 自增id
     */
    public $id;

    /**
     * @var string 用户id
     */
    public $userid;

    /**
     * @var string 用户名
     */
    public $username;

    /**
     * @var  string 游戏id
     */
    public $gameid;

    /**
     * @var string 活动名
     */
    public $act;

    /**
     * @var string 电话号码
     */
    public $phone;

    /**
     * @var string 邮箱
     */
    public $email;

    /**
     * @var string 用户ip
     */
    public $ip;

    /**
     * @var int 状态
     */
    public $status;

    /**
     * @var string 增加时间
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
        return 't_phone_bind';
    }

    public function isBind()
    {
        return $this->exists(array(
          'userid' => lp::App()->user->userId,
          'act' => lp::App()->act->actNo
        ));
    }
}

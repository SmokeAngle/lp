<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 存放用户联系地址表对应的数据类
 *
 * 用法介绍请参看基类注释说明
 *
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致
 */
class AddressInfo extends BaseModel
{
    /**
     * @var int 主键 自增
     */
    public $id;

    /**
     * @var string 用户id
     */
    public $userid;

    /**
     * @var string 用户昵称
     */
    public $name;

    /**
     * @var string 活动编号
     */
    public $act;

    /**
     * @var string 手机号
     */
    public $mobile;

    /**
     * @var string 座机号
     */
    public $telephone;

    /**
     * @var string 邮政编码
     */
    public $zipcode;

    /**
     * @var string 地址
     */
    public $address;

    /**
     * @var string ip地址
     */
    public $ip;

    /**
     * @var int 状态
     */
    public $status;

    /**
     * @var string 添加时间
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
        return 't_address_info';
    }
}

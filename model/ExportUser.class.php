<?php
/**
 * @author caiwenxiong <caiwenxiong@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace model;

use _lp\core\model\BaseModel;

/**
 * 从后台提取的用户记录表对应的数据类
 *
 * 通常用于某些活动需要定向投放，从后台提取一批用户，只有这批用户才能参与活动
 *
 * 用法介绍请参看基类注释说明
 * 
 * 数据类必须继承BaseModel基类，声明的属性必须跟数据表定义的一致   
 */
class ExportUser extends BaseModel
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
     * @var string 游戏活动编号，在管理后台申请生成
     *
     */
    public $act;
    

    /**
     * @var string 插入时间, datetime格式
     *
     */
    public $addtime;
    
    function __construct()
    {
        parent::__construct();
        $this->addtime = date('Y-m-d H:i:s');
    }

    /**
     *  声明对应的表名
     *
     */
    public function getTableName(){
        return 't_export_user';
    }

}
?>

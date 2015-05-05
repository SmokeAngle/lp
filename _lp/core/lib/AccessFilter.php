<?php
/**
 * 过滤器前端控制类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\lib;

use _lp\core\lib\rule\AccessRule;

class AccessFilter
{
    
    public function __construct()
    {
        
    }
    
    public static function check($rule = null)
    {
        if ($rule instanceof AccessRule) {
            return $rule->onCheck();
        }
        return false;
    }
}

<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 */
namespace lib\act\checkRule;

use _lp\core\lib\rule\ActiveRule;

class TestCheck extends ActiveRule
{

    public function __construct($rule = array(), $parms = array())
    {
        parent::__construct($rule, $parms);
    }
    
    public function checkTest()
    {
        
    }
}

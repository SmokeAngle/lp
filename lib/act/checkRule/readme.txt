自定义规则检查器，继承自基类 _lp\core\lib\rule\ActiveRule;

当基类提供的检查规则无法满足业务需求，可以自定义

<?php

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

调用时示例：

<?php

namespace act_app\y_2014\test\controller;

use lib\act\checkRule\TestCheck;

class DefaultController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
	public function test()
    {
		$activeRule = new TestCheck($giftRule, array('serverId' => intval($serverId)));
        AccessFilter::check($activeRule);
	}
}
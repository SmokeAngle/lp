�Զ�������������̳��Ի��� _lp\core\lib\rule\ActiveRule;

�������ṩ�ļ������޷�����ҵ�����󣬿����Զ���

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

����ʱʾ����

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
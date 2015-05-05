<?php
use _lp\core\lib\AccessFilter;
use _lp\core\lib\AccessRule;
class TestLibAccessFilter extends WebTestCase {
    
    public function test_check() {
       $rule =  new AccessRule();
       $ret = AccessFilter::check($rule);
       $this->assertFalse(!$ret);
    }
    
}

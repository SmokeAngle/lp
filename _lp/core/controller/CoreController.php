<?php
/**
 * 核心控制器类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\controller;

use _lp\core\lib\AccessFilter;
use _lp\core\lib\rule\AccessRule;
use _lp\core\lib\FileLog;
use _lp\lp;

class CoreController
{
    /**
     *当前动作名
     * @var string
     */
    public $action;
    /**
     * 当前控制器名
     * @var string
     */
    public $controller;

    public function __construct()
    {
        $this->action = lp::App()->action;
        $this->controller = str_ireplace('Controller', '', substr(lp::App()->controller, strrpos(lp::App()->controller, '\\')+1));
        $this->__before();
    }
        
        /**
         *  执行控制器动作前执行动作
         */
        private function __before()
        {
            $accessFilterConfig = lp::App()->getConfigItem('accessFilter');
        if (!empty($accessFilterConfig)) {
            $accessRule = new AccessRule($accessFilterConfig, $this->controller, $this->action);
            AccessFilter::check($accessRule);
        }
            $this->before();
        }
        
        /**
         *  执行控制器后执行动作
         */
        private function __after()
        {
            $this->after();
        }
        
        /**
         *  执行控制器动作前执行用户动作
         */
        public function before()
        {
            
        }
        /**
         *  执行控制器后执行用户动作
         */
        public function after()
        {
            
        }
        
        /**
         *  充值回调
         */
        public function chongHook()
        {
            
        }
        
//        public function doChongHook($data = array())
//        {
//            
//        }
        /**
         * 金钻充值回调
         */
        public function vipChongHook()
        {
            
        }
        /**
         * 金钻回调用户动作
         * @param array $data 回调返回数组
         */
//        public function doVipChongHook($data) { 
//            
//        }

        public function __destruct()
        {
            $this->__after();
        }
}

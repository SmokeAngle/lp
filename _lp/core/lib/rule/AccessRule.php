<?php
/**
 *  访问控制过滤器核心类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\lib\rule;

use _lp\lp;
use _lp\core\lib\MemcachedClient;
use Exception;

class AccessRule
{
    
    /**
     * 规则
     * @var array
     */
    protected $_rules = array();
    
    /**
     * 请求控制器
     * @var string
     */
    private $_controller;
    
    /**
     *请求动作
     * @var string
     */
    private $_action;
    

    public function __construct($rule = array(), $controller = "", $action = "")
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->setRule($rule);
    }
    
    public function setRule($rule = array())
    {
        if (!empty( $rule )) {
            foreach ($rule as $ruleName => $ruleItem) {
                //$checkFuncName = sprintf('_%s', $ruleName);
                $checkFuncName = $ruleName;
                $this->_rules[$checkFuncName] = $ruleItem;
            }
        }
    }

    public function onCheck()
    {
        $isVaild  = false;
        if (!empty($this->_rules)) {
            foreach ($this->_rules as $checkFunc => $config) {
                $_checkFunc = isset($config['checkFunc']) &&  gettype($config['checkFunc']) === 'object' ? $config['checkFunc'] : false;
                $_checkFaildFunc = isset($config['checkFaildFunc']) &&  gettype($config['checkFaildFunc']) === 'object' ? $config['checkFaildFunc'] : false;
                $_checkSuccessFunc = isset($config['checkSuccessFunc']) &&  gettype($config['checkSuccessFunc']) === 'object' ? $config['checkSuccessFunc'] : false;
                unset($config['checkFunc'], $config['checkFaildFunc'], $config['checkSuccessFunc']);
                if (false !== $_checkFunc) {
                    $checkResult = $_checkFunc($config);
                } else {
                    if (method_exists($this, $checkFunc)) {
                        $checkResult = $this->{$checkFunc}($config);
                    } else {
                        throw new Exception('验证规则函数未定义：' . $checkFunc, 500);
                    }
                }
                if (true === $checkResult && $_checkSuccessFunc !== false) {
                    $_checkSuccessFunc($config, $checkResult);
                    $isVaild = true;
                }
                if (true !== $checkResult && $_checkFaildFunc !== false) {
                     $_checkFaildFunc($config, $checkResult);
                }
            }
        }
        return $isVaild;
    }
    
    /**
     *  检测访问频率
     * @param array $config 用户配置数组
     * @return boolean
     */
    private function checkDDos($config = array())
    {
        $memcached = lp::App()->memcached;
        if (!$memcached || !($memcached instanceof MemcachedClient)) {
            return false;
        }
        $_prefixKey = 'AF';
        $key = sprintf('%s_%s_%s', $_prefixKey, $this->_controller . 'Controller.' . $this->_action, getip());
        $default_value = "1_" . time();
        $second = 60;
        $maxCount = isset($config['maxRequest']) ? intval($config['maxRequest']) : 30;
        $checkValue = $memcached->get($key);
        if (false === $checkValue || !strstr($checkValue, '_')) {
            $memcached->add($key, $default_value, $second);
        } else {
            @list($count, $times) = explode('_', $checkValue);
            if (time() - $times > $second) {
                $memcached->add($key, $default_value, $second);
            } else {
                if ($count < $maxCount) {
                    $memcached->replace($key, ($count + 1) . '_' . $times, $second-(time()-$times));
                } else {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     *  检测用户登录
     * @param array $config  用户配置规则数组
     * @return boolean
     */
    private function checkLogin($config = array())
    {
        $urlList = isset($config['url']) ? (array)$config['url'] : array();
        if (in_array($this->_controller . '/' . $this->_action, $urlList)) {
            if (false === ( $userInfo = lp::App()->user->getUserInfo() )) {
                return false;
            }
        }
        return true;
    }
}

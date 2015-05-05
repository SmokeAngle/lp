<?php
/**
 * @author caiwenxiong <caiwenxiong@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace controllers;


/**
 * 活动业务控制器，处理业务逻辑
 */
class DefaultController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
   
    public function err_404( ) { 
        return FALSE;
    }
   
}

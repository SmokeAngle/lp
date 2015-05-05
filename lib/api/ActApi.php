<?php

/**
 *  根据业务需要对接口进行再次封装，方便调用
 *
 */
namespace lib\api;

use _lp\lp;
use _lp\core\lib\GameApi;

/**
 *  游戏接口业务层，对GameApi进行封装，方便业务层调用
 *
 */
class ActApi
{

    public function __construct()
    {
        parent::__construct();
    }
}

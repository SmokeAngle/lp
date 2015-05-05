<?php
/**
 * @author caiwenxiong <caiwenxiong@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace act_app\y_2015\demo1\controller;

use _lp\lp;
use _lp\core\lib\FileLog;
use _lp\core\lib\AccessFilter;
use _lp\core\lib\rule\ActiveRule;
use _lp\core\lib\rule\AccessRule;
use _lp\core\lib\GameApi;

use lib\api\ActApi;

use model\AddressInfo;
use model\PhoneBind;

use controllers\BaseController;
use lib\act\giftPacket\GiftPacket;

/**
 * 活动业务控制器，处理业务逻辑
 */
class DefaultController extends BaseController
{
    private $userid;

    private $username;

    public function __construct()
    {
        parent::__construct();
        $this->userid = lp::App()->user->userId;
        $this->username = lp::App()->user->userName;
    }

    public function getActInfo() {

        if( $this->checkUser() ) {
            $arr = array(
                '2014'=>array('act1','act2'),
                '2015'=>array('act3','act4')
            );
            jsonpEcho(0,'',$arr);
        } else jsonpEcho(111,'not login');

    }

    private function checkUser() {
        $permitIdArr = array(
            123540516
        );
        $permitNameArr = array(
            'qiucheng48'
        );
        if( in_array($this->userid, $permitIdArr) || in_array($this->username, $permitNameArr) ) return true;
        else return false;
    }
}

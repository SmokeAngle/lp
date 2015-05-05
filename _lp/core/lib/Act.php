<?php
/**
 * 活动核心类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\lib;

use _lp\lp;
use _lp\core\lib\FileLog;
use _lp\core\lib\HttpsqsClient;

class Act
{
    
    /**
     * @var string 活动编号
     */
    public $actNo;
    /**
     * @var string 活动开始时间
     */
    public $startTime;
    /**
     * @var string 活动结束时间
     */
    public $endTime;
    
    /**
     * @var string 活动关联游戏id
     */
    public $gameId;
    
    /**
     * @var array 游活动礼包
     */
    public $giftPackets = array();

    /**
     * construct
     */
    public function __construct($actNo = "", $gameId = "", $startTime = "", $endTime = "")
    {
        $this->actNo = $actNo;
        $this->gameId = $gameId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }
    
    /**
     *  活动配置数组
     * @param array $data
     */
    public function init($data = array())
    {
        if (!empty($data)) {
            foreach ((array)$data as $attr => $value) {
                if (property_exists(__CLASS__, $attr)) {
                    $this->{$attr} = $value;
                }
            }
        }
    }
    
    /**
     *  根据条件判断当前活动是否有效
     * @param array $conditions
     * @return int|boolean
     */
    public function isValidAct($callBack)
    {
        if (gettype($callBack) === 'object') {
            $return = $callBack($this->gameId, $this->actNo, $this->startTime, $this->endTime);
            return $return;
        }
        return false;
    }

    /**
     *  添加礼包
     */
    public function addGiftPacket($giftPacket)
    {
        $this->giftPackets[$giftPacket->packetKey] = $giftPacket;
    }
    /**
     *  获取礼包
     */
    public function getGiftPacket()
    {
        return $this->giftPackets;
    }
    /**
     * @param int 模块id
     * @return mixed
     */
    public function getGiftPacketByMid($moudleId = null)
    {
        if (is_null($moudleId)) {
            return $this->getGiftPacket();
        }
        $mid = intval($moudleId);
        $data = array();
        while ($giftPacket = current($this->giftPackets)) {
            if ($giftPacket->moudleId === $mid) {
                $data[] = $giftPacket;
            }
            next($this->giftPackets);
        }
        return $data;
    }
    /**
     *  发送礼包key
     * @param array $packetKey
     */
    public function sendGiftPacket($packetKey)
    {
        $result = array();
        $packetKeyArr = is_array($packetKey) ? $packetKey : array($packetKey);
        foreach ($packetKeyArr as $key) {
            if (array_key_exists($key, $this->giftPackets)) {
                if (false !== ( $data = $this->giftPackets[$key]->doSend() )) {
                    if (isset($data['errno']) && $data['errno'] != 0) {
                        $reportData = array(
                            'op'          => 'gift',
                            'xl_user_id'  => $this->giftPackets[$key]->userId,
                            'url'         => 'http://act.niu.xunlei.com/' . date('Y', time()) . '/' . $this->giftPackets[$key]->actNo,
                            'item_id'     => $this->giftPackets[$key]->packetType,
                            'gift_id'     => $this->giftPackets[$key]->packetId,
                            'game_id'     => $this->giftPackets[$key]->gameId,
                            'game_svr_id' => $this->giftPackets[$key]->serverId,
                            );
                            $this->reportGiftPacketStat($reportData);
                    }
                    $result[$key] = $data;
                } else {
                    $result[$key] = false;
                }
            } else {
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, '礼包：' . $key . '不存在');
            }
        }
        return $result;
    }
    
    /**
     *  礼包数据上报
     */
    public function reportGiftPacketStat($data = array())
    {
        
        if (empty($data['op']) || empty($data['xl_user_id'])) {
            return false;
        }
        $info = $data;
        
        //item_id: 奖品类型：活动后台奖品(gift)、礼包激活码(code)、元宝(coin)、牛X代金券(cash)、牛X积分(point),有则上报, gift 为活动后台管理的奖品，code为激活码管理的
        switch ($data['item_id']) {
            case 'cash': $info['item_id'] = 'coin';
                break;
            case 'jifen': $info['item_id'] = 'point';
                break;
            case 'gamecard':
            case 'gamecode': $info['item_id'] = 'code';
                break;
            case 'gift': $info['item_id'] = 'gift';
                break;
            case 'niuxcash': $info['item_id'] = 'cash';
                break;
            case 'coupon': $info['item_id'] = 'coupon';
                break;
            default: $info['item_id'] = '';
                break;
        }
        $info['sub_op'] = isset($data['sub_op']) ? $data['sub_op'] : 1;
        $info['adv_no'] = isset($data['adv_no']) ? $data['adv_no'] :  '';
        $guid = get_cookie('niux_report_guid');
        if (!$guid) {
            $guid = guid(32);
            set_cookie('niux_report_guid', $guid);
        }
        $info['guid'] = $guid;
        $url_param = http_build_query($info);
        $url = 'http://ct.niu.xunlei.com/r/act/'. $this->actNo .'/?'. $url_param ;
        $httpsqsConfig = lp::App()->getConfigItem('httpsqs');
        $host = isset($httpsqsConfig['host']) ? $httpsqsConfig['host'] : "127.0.0.1";
        $port = isset($httpsqsConfig['port']) ? $httpsqsConfig['port'] : 1218;
        $auth = isset($httpsqsConfig['auth']) ? $httpsqsConfig['auth'] : "";
        $httpsqsClient = new HttpsqsClient($host, $port, $auth);
        
        if (false === $httpsqsClient->put('reportStatForActByDyact2', urlencode($url))) {
             lp::log()->write(FileLog::LOG_LEVEL_ERROR, 'sendQueue faild :'. $data);
        }
    }
}

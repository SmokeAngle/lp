<?php
/**
 * 抽奖类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\lib;

class Lottery
{
    
    public $giftPackets = array();
    
    public function __construct($giftPackets = array())
    {
        $this->giftPackets = $giftPackets;
    }
    
    /**
     * @param array $giftPackets 礼包配置数组
     * @return \_lp\core\lib\Lottery
     */
    public function setGiftPacket($giftPackets = array())
    {
        $this->giftPackets = $giftPackets;
        return $this;
    }
    
    /**
     * 抽奖程序，返回礼包key
     */
    public function doLottery()
    {
        $rateSum = 0;
        $rateArr = array_map(function($giftPacket){
            return $giftPacket['rate'];
        }, $this->giftPackets);
        $realRateSum = array_sum($rateArr);
        $exp = 0;
        while ($rate = current($rateArr)) {
            list($_i, $_f) = explode('.', $rate);
            $exp = empty($exp) ? strlen($_f) : $exp;
            if (strlen($_f) > $exp) {
                $exp = strlen($_f);
            }
            next($rateArr);
        }
        $n = pow(10, $exp);
        $rateSum = $realRateSum * $n;
        $progressiveArr = array();
        $maxIndex  = 1;
        while ($giftPacket = current($this->giftPackets)) {
            if ($maxIndex >= $rateSum + 1) {
                break;
            }
            $_tmpRate = (isset($giftPacket['rate']) ? $giftPacket['rate'] : 0) * $n;
            if ($_tmpRate > 0) {
                $_tmpKeyArr = range($maxIndex, $maxIndex+$_tmpRate - 1);
                $progressiveArr = array_merge($progressiveArr, array_fill_keys($_tmpKeyArr, $giftPacket['packetKey']));
                $maxIndex += $_tmpRate;
            }
            next($this->giftPackets);
        }
        shuffle($progressiveArr);
        $randIndex = mt_rand(0, $rateSum - 1);
        
        return $progressiveArr[$randIndex];
    }
}

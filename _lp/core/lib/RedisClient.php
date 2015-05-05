<?php
/**
 * @author chenmiao@xunlei.com
 */

namespace _lp\core\lib;

use \Redis;

class RedisClient
{
    /**
     * @var int 默认db
     */
    public $db = 11;
    /**
     * @var string server host
     */
    public $host = '127.0.0.1';
    /**
     * @var int server port
     */
    public $port = 6379;
    /**
     * @var bolean 是否为集群
     */
    public $isCluster = false;
    
    private $_redis;


    public function __construct($host = '', $port = 6379, $db = 0, $isCluster = false)
    {
        $this->host = empty($host) ? $this->host : $host;
        $this->port = empty($port) ? $this->port : intval($port);
        $this->db = empty($db) && $db != 0 ? $this->db : intval($db);
        $this->isCluster = empty($isCluster) ? $this->isCluster : $isCluster;
        $this->_redis = new Redis();
    }
    
    private function _connection()
    {
        if (false !== $this->_redis->connect($this->host, $this->port)) {
            if (!$this->isCluster) {
                $this->_redis->select($this->db);
                return $this->_redis;
            }
        }
        return false;
    }
    
    public function getConnection()
    {
        return $this->_connection();
    }
}

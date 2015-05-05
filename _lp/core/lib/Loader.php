<?php
/**
 * 核心加载器类
 *
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */
namespace _lp\core\lib;

class Loader
{
    
    /**
     * @var string 用户函数库目录
     */
    public $functionPath;
    /**
     * @var string 用户配置文件目录
     */
    public $configPath;

    /**
     * @var array 加载的函数库
     */
    public $functions = array();
    
    /**
     * @var array 加载的配置文件
     */
    public $configs = array();

    public function __construct($functionPath = "", $configPath = "")
    {
        $this->functionPath = $functionPath;
        $this->configPath = $configPath;
    }
    
    public function loadFunction($fileName = "")
    {
        $filePath = $this->functionPath . $fileName . '.function.php';
        if (array_key_exists($fileName, $this->functions)) {
            return true;
        }
        if (file_exists($filePath)) {
            $this->functions[$fileName] = $filePath;
            require_once $filePath;
            return true;
        }
        return false;
    }
    
    public function loadConfig($fileName = "")
    {
        $filePath = $this->configPath . $fileName . '.config.php';
        if (array_key_exists($fileName, $this->configs)) {
            return $this->configs[$fileName];
        }
        if (file_exists($filePath)) {
            $config = require $filePath;
            if (is_array($config)) {
                $this->configs[$fileName] = $config;
                return $config;
            }
        }
        return false;
    }
}

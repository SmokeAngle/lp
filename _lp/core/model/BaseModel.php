<?php
/**
 * @author chenmiao <chenmiao@xunlei.com>
 * @copyright (c) 2015, xunlei.com
 */

namespace _lp\core\model;

use _lp\lp;
use _lp\core\lib\FileLog;
use _lp\core\lib\DB;
use ReflectionClass;
use ReflectionProperty;
use Exception;

/**
 * 核心model基类，所有对数据库操作，实现业务逻辑的，都需要继承此基类。
 *
 *  下面是用法介绍：
 *
 *  //创建实例
 *  $addressInfo = new AddressInfo;
 *  
 *  1、增加数据：
 *  方法一：
 *  $addressInfo->insert(array('field1'=>'value','field2'=>'value'));
 *  
 *  方法二：
 *  $addressInfo->clearAttribules();  //防止变量混乱
 *  $addressInfo->act = lp::App()->act->actNo;
 *  $addressInfo->userid = lp::App()->user->userId;
 *  $addressInfo->address = "";
 *  $addressInfo->mobile = '13534116242';
 *  $addressInfo->telephone = "";
 *  $addressInfo->zipcode = '123131';
 *  $addressInfo->addtime =  date("Y-m-d H:i:s");
 *  $addressInfo->ip = getip();
 *  $addressInfo->status = 1;
 *  $addressInfo->save();
 *  $addressInfo->clearAttribules();//防止变量混乱
 *  
 *  2、删除数据：
 *  方法一：
 *  $addressInfo->deleteAll(array('id' => 1));
 *  
 *  方法二：
 *  $addressInfo->clearAttribules();
 *  $addressInfo->id = 1;
 *  $addressInfo->delete();
 *  
 *  3、更新数据：
 *  // 更新id=1的记录，把telephone字段值改为0755123131
 *  $addressInfo->update(array('id' => 1), array( 'telephone' => '0755123131'));
 * 
 *  4、查询数据：
 *  获取所有数据：$addressInfo->findAll();
 *  获取指定数据：$addressInfo->find(array('act' => 'test'));
 *  
 *  5、判断指定数据是否存在：
 *  $addressInfo->exists(array('id' => 1));
 *  
 *  6、获取匹配条件数据的条数
 *  $addressInfo->count(array('act' => 'test'));
 *  
 *  
 *  $addressInfo->getDbConnection() 将返回一个  _lp\core\lib\DB类的实例，所以，也可以直接调用DB类方法
 *  
 *  $db = $addressInfo->getDbConnection();
 *  $db->get($data = array(), $extSql = '');
 *  $db->getRow($data);
 *  $db->getBySql($_sql, $data = array(), $extSql = '');
 *  $db->getRowBySql($_sql, $data = array(), $extSql = '');
 * 
 */
class BaseModel
{
    
    /**
     * @var DB
     */
    public static $db;
    
    /**
     * @var array 表属性
     */
    public $attribules = array();
    
    /**
     * @var int 表主键
     */
    public $primaryKey = '';

    public function __construct()
    {
        $this->getDbConnection()->setTable($this->getTableName());
        $this->_init();
    }

    /**
     *  获取数据库连接，_lp\core\lib\DB类的实例
     * @return 成功返回DB类的实例，否则抛出异常
     */
    public function getDbConnection()
    {
        if (self::$db !== null && self::$db instanceof DB) {
            return self::$db;
        } else {
            self::$db = lp::App()->db;
            if (self::$db instanceof DB) {
                return self::$db;
            } else {
                throw new Exception('数据库连接错误', 500);
            }
        }
    }

    /**
     *  初始化 表元数据
     */
    private function _init()
    {
        
        if (false !== ( $_dbHandle = $this->getDbConnection()->getConnection() )) {
            if (false !== ( $resultHandle = $_dbHandle->query('show columns from ' . $this->getTableName()) )) {
                while ($columsObject = $resultHandle->fetch_object()) {
                    $this->attribules[$columsObject->Field] = $columsObject;
                    if (strtoupper($columsObject->Key) === 'PRI') {
                        $this->primaryKey = $columsObject->Field;
                    }
                }
                $resultHandle->close();
            }
        }
        
        $calledClassName = get_called_class();
        $reflectionClass = new ReflectionClass($calledClassName);
        $calledClassPropertiesName = array_map(function($property) use ($calledClassName){
            if ($property->class === $calledClassName) {
                return $property->name;
            }
        }, $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC));
        $dbFields = array_keys($this->attribules);
        if (array_intersect($dbFields, $calledClassPropertiesName) !== $dbFields) {
            lp::log()->write(FileLog::LOG_LEVEL_ERROR, 'load model[' . $calledClassName . '] error: 字段定义错误！');
            throw new Exception('model：' . $calledClassName . ' 字段定义错误！');
        }
    }


    /**
     *  获取表所有记录
     */
    public function findAll()
    {
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== ( $data = $_dbHandle->get() )) {
                return $data;
            }
        }
        return false;
    }
    
    /**
     *
     * @param array $conditions 条件数组
     * @param string $extSql 排序分组聚合语句
     * @return boolean|array
     */
    public function find($conditions = array(), $extSql = "")
    {
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== ( $data = $_dbHandle->get($conditions, $extSql) )) {
                return $data;
            }
        }
        return false;
    }

    /**
     * 根据sql语句查询数据，sql是where之前的select语句。
     * 
     * @param string $sql 查询sql语句, where之前Select语句
     * @param array $conditions 条件数组
     * @param string $extSql 排序分组聚合语句
     * @return boolean|array
     */
    public function findBySql($sql, $conditions = array(), $extSql = "")
    {
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== ( $data = $_dbHandle->getBySql($sql, $conditions, $extSql) )) {
                return $data;
            }
        }
        return false;
    }

    /**
     *  根据条件 删除指定内容
     * @param array $conditions 条件数组
     * @return boolean
     */
    public function deleteAll($conditions = array())
    {
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== $_dbHandle->delete($conditions)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     *  ar 删除记录
     * @return boolean
     */
    public function delete()
    {
        $_attribules = $this->getAttribules();
        $validColumnArr = array_filter($_attribules, function($data){
                                        return !is_null($data) ;
        });
        $conditionArr = array();
        if (empty($validColumnArr)) {
            return false;
        }
        foreach ($validColumnArr as $field => $value) {
            $conditionArr[$field] = $value;
        }
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if ($_dbHandle->delete($conditionArr)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     *  insert 方式插入数据
     * @param array $data 插入数组
     */
    public function insert($data = array())
    {
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== ($_dbHandle->insert($data))) {
                return true;
            }
        }
        return false;
    }
    
    /**
     *  对象方式插入数据
     */
    public function save()
    {
        $data = $this->getAttribules();
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if ($_dbHandle->insert($data)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     *  根据条件更新指定字段
     * @param array $conditions 更新条件
     * @param array $data  更新内容
     * @return boolean
     */
    public function update($conditions = array(), $data = array())
    {
        if (empty($conditions) || empty($data)) {
            return false;
        }
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== $_dbHandle->update($conditions, $data)) {
                return true;
            }
        }
        return false;
    }

    /**
     *  判断记录是否存在
     */
    public function exists($conditions = array())
    {
        if (empty($conditions)) {
            return false;
        }
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== $_dbHandle->exists($conditions)) {
                return true;
            }
        }
        return false;
    }
    

    /**
     * 获取匹配条件数据的条数
     */
    public function count($conditions = array())
    {
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== ( $result = $_dbHandle->getRowBySql('SELECT count(*) as count FROM ' . $this->getTableName(), $conditions) )) {
                return isset($result['count']) ? intval($result['count']) : false;
            }
        }
        return false;
    }

    /**
     * 获取最新插入的记录id
     */
    public function insertId()
    {
        if (false !== ( $_dbHandle = $this->getDbConnection() )) {
            if (false !== ( $result = $_dbHandle->insertId() )) {
                return $result;
            }
        }
        return false;
    }

    /**
     *  获取表元数据
     */
    public function getMetaData()
    {
        return $this->attribules;
    }
    
    /**
     *  获取表属性
     */
    public function getAttribules()
    {
        $_attr = array();
        foreach ($this->attribules as $field => $value) {
            if (property_exists($this, $field)) {
                $_attr[$field] = $this->attribules[$field]->Value = $this->{$field};
            } else {
                $_attr[$field] = $this->attribules[$field]->Value = isset($this->attribules[$field]->Default) ? $this->attribules[$field]->Default : null;
            }
        }
        return $_attr;
    }
    
    /**
     *  清除属性
     */
    public function clearAttribules()
    {
        $_self = $this;
        $this->attribules = array_map(function($attribule) use ($_self){
            unset($attribule->Value);
            unset($_self->{$attribule->Field});
            return $attribule;
        }, $this->attribules);
    }

    /**
     *  获取表名
     */
    public function getTableName()
    {
        
    }
}

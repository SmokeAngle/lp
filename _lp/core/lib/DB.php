<?php
/**
 * 数据处理基类，采用mysqli安全的数据操作方式，提供数据查询、新增、更新、删除操作
 * 所有操作都经过预编译处理，防止sql注入
 * @author caiwenxiong@xunlei.com
 */
namespace _lp\core\lib;

use _lp\lp;
use _lp\core\lib\FileLog;
use Exception;

class DB
{

    private $conn = null;
    private $insertId = 0;
    private $affected_rows = 0;
    public $useCache = true;
    private $connected = false;
    
    public $host;
    public $port;
    public $user;
    public $password;
    public $dbName;
    public $charset='utf-8';
    
    public $table;

    public function __construct($host = '', $port = 3306, $user = '', $password = '', $dbName = '', $charset = 'utf8')
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->dbName = $dbName;
        $this->charset = !empty($charset) ? $charset : $this->charset;
        $this->_connection();
    }

    public function __destruct()
    {
        $this->_disConnection();
    }

    /**
     * 开始一个数据库连接，此方法不返回mysqli对象，用getConn方法获取可靠的数据库连接
     * @access private
     */
    private function _connection()
    {
        
        if ($this->connected == false) {
            $this->conn = mysqli_connect($this->host, $this->user, $this->password, $this->dbName, $this->port);
            $this->connected = !mysqli_connect_errno();
            
            if (!$this->connected) {
                throw new Exception("db connection error！");
            }
            if (!empty($this->charset)) {
                $this->conn->query('set names  ' . $this->charset);
            }
        }
    }

    /**
     * 用可靠的方式关闭一个数据库连接，不需要手动执行
     * @return true
     */
    private function _disConnection()
    {
        if ($this->connected === true) {
            mysqli_kill($this->conn, mysqli_thread_id($this->conn));
            mysqli_close($this->conn);
            $this->connected = false;
        }
        return true;
    }
    
    /**
     * 获取一个可用的数据库连接
     * @access protected
     */
    public function getConnection()
    {
        if ($this->connected == true) {
            return $this->conn;
        } else {
            $this->_connection();
            if ($this->connected == true) {
                return $this->conn;
            }
        }
        return false;
    }
    
    /**
     * 判断是否存在指定字段的值
     * @param $data array : array('id'=>10)
     *
     * e.g:
     * $c = new Comment();
     * $c->exists(array('id'=>'1'));
     *
     */
    public function exists($data, $conn = null)
    {
        
        if (empty($data)) {
            return false;
        }

        if ($conn == null) {
            $conn = $this->getConnection();
        }

        $condition = array();
        $params = array();
        $types = '';
        $rs = false;
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                // 'addtime'=>array('condition'=>'>=','value'=>date('Y-m-d'))
                $condit = isset($item['condition']) ? $item['condition'] : '=';
                $condition[] = $key.' '.$condit.' ?';
                $value = $item['value'];
            } else {
                $condition[] = $key.' = ?';
                $value = $item;
            }

            // 000166的数值不能认为是i, 否则会被转换成166数字，比如gameid
            if (is_numeric($value)  && strlen($value) == strlen(intval($value))) {
                $params[] = $value;
                $types .='i';
            } else {
                $types .='s';
                $value = mysqli_real_escape_string($conn, $value);
                $params[] = $value;
            }
        }
        $condit = implode(' and ', $condition);
        $sql =  "SELECT count(*) as count FROM ".$this->table()." WHERE " .$condit ;
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $this->refValues($params)));
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $rs);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_free_result($stmt);
            mysqli_stmt_close($stmt);
        }
        return $rs ? true : false;
    }

    /**
     * 根据指定字段的值，获取记录集
     * @param $data array : array('id'=>10)
     * @param $conn : mysql link
     *
     * e.g:
     * $c = new Comment();
     * $c->get(array('userid'=>'23223244'));
     *
     */
    public function get($data = array(), $extSql = '', $conn = null)
    {
        if ($conn == null) {
            $conn = $this->getConnection();
        }

        $condition = array();
        $params = array();
        $types = '';
        $rs = false;
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                // 'addtime'=>array('condition'=>'>=','value'=>date('Y-m-d'))
                $condit = isset($item['condition']) ? $item['condition'] : '=';
                $condition[] = $key.' '.$condit.' ?';
                $value = $item['value'];
            } else {
                 $condition[] = $key.' = ?';
                 $value = $item;
            }
            
            // 000166的数值不能认为是i, 否则会被转换成166数字，比如gameid
            if (is_numeric($value)  && strlen($value) == strlen(intval($value))) {
                $params[] = $value;
                $types .='i';
            } else {
                $types .='s';
                $value = mysqli_real_escape_string($conn, $value);
                $params[] = $value;
            }
        }
        $sql = "SELECT * FROM ". $this->table() . (!empty($condition) ?   " WHERE " . implode(' and ', $condition) : '') . ' ' . $extSql;
        $stmt = mysqli_prepare($conn, $sql) ;
        
        if ($stmt) {
            if (!empty( $params )) {
                call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $this->refValues($params)));
            }
            mysqli_stmt_execute($stmt);
            $meta = mysqli_stmt_result_metadata($stmt);
            while ($field = mysqli_fetch_field($meta)) {
                $fields[] = &$row[$field->name];
            }
            call_user_func_array('mysqli_stmt_bind_result', array_merge(array($stmt), $fields));
            while (mysqli_stmt_fetch($stmt)) {
                foreach ($row as $key => $val) {
                    $temp[$key] = $val;
                }
                $rs[] = $temp;
            }
            mysqli_stmt_free_result($stmt);
            mysqli_stmt_close($stmt);
        }
        return $rs;
    }


    /**
     * 根据指定字段的值，获取一条记录
     * @param $data array : where subsql array('id'=>10)
     * @param $conn : mysql link
     *
     * e.g:
     * $c = new Comment();
     * $c->get(array('userid'=>'23223244'));
     */
    public function getRow($data, $conn = null)
    {
        $rs = $this->get($data, $conn);
        return empty($rs) ? false : @reset($rs);
    }


    /**
     * 根据sql语句和指定字段的值，获取记录集
     * @param $_sql string : select sql
     * @param $data array : where subsql array('id'=>10)
     * @param $data string : like order by, group by , limit num, after where subsql.
     * @param $conn : mysql link
     *
     * e.g:
     * $sql = 'SELECT sum(money) as money FROM ' . $this->table();
     * $rs = $this->getBySql($sql, array('userid'=>$param['userid'], 'act'=>$param['act'], 'addtime'=>array('condition'=>'>=','value'=>date('Y-m-d')) ), $extSql );
     *
     */
    public function getBySql($_sql, $data = array(), $extSql = '', $conn = null)
    {
        if ($conn == null) {
            $conn = $this->getConnection();
        }
        $condition = array();
        $params = array();
        $types = '';
        $rs = false;
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                // 'addtime'=>array('condition'=>'>=','value'=>date('Y-m-d'))
                $condit = isset($item['condition']) ? $item['condition'] : '=';
                $condition[] = $key.' '.$condit.' ?';
                $value = $item['value'];
            } else {
                 $condition[] = $key.' = ?';
                 $value = $item;
            }
            
            // 000166的数值不能认为是i, 否则会被转换成166数字，比如gameid
            if (is_numeric($value)  && strlen($value) == strlen(intval($value))) {
                $params[] = $value;
                $types .='i';
            } else {
                $types .='s';
                $value = mysqli_real_escape_string($conn, $value);
                $params[] = $value;
            }
        }
        $condit = implode(' and ', $condition);
        $sql = $_sql . (!empty($condit) ? " WHERE " .$condit : '' )  . ' '. $extSql;
        
        $stmt = mysqli_prepare($conn, $sql) ;
        if ($stmt) {
            if (!empty($params)) {
                call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $this->refValues($params)));
            }
            mysqli_stmt_execute($stmt);
            $meta = mysqli_stmt_result_metadata($stmt);
            while ($field = mysqli_fetch_field($meta)) {
                $fields[] = &$row[$field->name];
            }
            call_user_func_array('mysqli_stmt_bind_result', array_merge(array($stmt), $fields));
            while (mysqli_stmt_fetch($stmt)) {
                foreach ($row as $key => $val) {
                    $temp[$key] = $val;
                }
                $rs[] = $temp;
            }
            $this->affected_rows = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_free_result($stmt);
            mysqli_stmt_close($stmt);
        }
        return $rs;
    }

    /**
     * 根据sql语句和指定字段的值，获取一条记录
     * @param $_sql string : select sql
     * @param $data array : where subsql array('id'=>10)
     * @param $data string : like order by, group by , limit num, after where subsql.
     * @param $conn : mysql link
     *
     * e.g:
     * $sql = 'SELECT sum(money) as money FROM ' . $this->table();
     * $rs = $this->getRowBySql($sql, array('userid'=>$param['userid'], 'act'=>$param['act'], 'addtime'=>array('condition'=>'>=','value'=>date('Y-m-d')) ), $extSql );
     *
     */
    public function getRowBySql($_sql, $data, $extSql = '', $conn = null)
    {
        $rs = $this->getBySql($_sql, $data, $extSql, $conn);
        return empty($rs) ? false : @reset($rs);
    }


    /**
     * 插入数据
     * @param $data array : array('name'=>'aa','tel'=>'11')
     *
     * 需要把数据库的字段声明为对象的属性，比如用户表，有name, password字段, User类就要声明name, password两个属性
     *
     * e.g: 插入数据
     * $c = new Comment();
     * $c->insert(array('userid'=>'23223244','username'=>'test','gameid'=>'000323','act'=>'xxxx','msg'=>'test comment sdflak'));
     *
     */
    public function insert($data, $conn = null)
    {

        $condition = array();
        $params = array();
        $holder = array();
        $types = '';
        $rs = false;
        
        if ($conn == null) {
            $conn = $this->getConnection();
        }
        
        foreach ($data as $key => $val) {
                $condition[] = '`'.$key.'`';
                $holder[] = '?';
                // 000166的数值不能认为是i, 否则会被转换成166数字，比如gameid
            if (is_numeric($val) && strlen($val) == strlen(intval($val)) && $val < 1000000000) {
                $types .='i';
                $params[] = $val;
            } else {
                $types .='s';
                // if (!isJson($val) && !isSerialized($val)) {
                //     $val = mysqli_real_escape_string($conn, $val);
                //     $val = htmlspecialchars($val);
                // }
                $params[] = $val;
            }
        }
        $condit = implode(',', $condition);
        $holders = implode(',', $holder);
        
        $sql = "INSERT INTO " . $this->table() . "(" . $condit . ")" . " VALUES( " . $holders . ")";
        $stmt = mysqli_prepare($conn, $sql) ;

        if ($stmt) {
            call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $this->refValues($params)));
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                //xlog('data insert success, sql:'.$sql.' , param: '.json_encode($params), 'notice','db_insert');
                lp::log()->write(FileLog::LOG_LEVEL_INFO, 'data insert success, sql:'.$sql.' , param: '.json_encode($params), 'db_insert');
                $rs = true;
                $this->insertId = mysqli_stmt_insert_id($stmt);
            } else {
              //  xlog('data insert 0 rows, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'notice');
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, 'data insert 0 rows, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'db_insert');
            }

            if (mysqli_errno($conn) != 0) {
                //xlog('data insert fail, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'error');
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, 'data insert fail, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'db_insert');
            }
            mysqli_stmt_close($stmt);
        }
        return $rs;

    }
  
    /**
     * 更新数据
     * @param $condition array : 更新数据的条件，可以有多个条件，被转换为where子句，array('name'=>'aa','tel'=>'11')
     * @param $data array :  需要更新的数据，被转换为set子句
     *
     * 需要把数据库的字段声明为对象的属性，比如用户表，有name, password字段, User类就要声明name, password两个属性
     *
     * e.g: 更新id=1的数据
     * $c = new Comment();
     * $c->update(array('id'=>'1'),array('username'=>"'s, 1=1 --ss",'msg'=>"psds' xldkjl"));
     *
     */
    public function update($condition, $data, $conn = null)
    {


        $params = array();
        $types = '';
        $rs = false;

        $where = $c =  array();

        if ($conn == null) {
            $conn = $this->getConnection();
        }

        foreach ($data as $key => $value) {
           // if(property_exists($this, $key)){
                $c[] = '`'.$key.'` = ?';

                // 000166的数值不能认为是i, 否则会被转换成166数字，比如gameid
            if (is_numeric($value)  && strlen($value) == strlen(intval($value))) {
                $types .='i';
                $params[] = $value;
            } else {
                $types .='s';
                // if (!isJson($value) && !isSerialized($value)) {
                //     $value = mysqli_real_escape_string($conn, $value);
                //     $value = htmlspecialchars($value);
                // }
                $params[] = $value;
            }
                
            //}
        }

        //如果对象的属性有值，则更新,比如时间属性，数据更新时，时间也要更新
        foreach ($this as $key => $val) {
            if ($val && in_array($key, array('addtime')) && $val != 'doNotForceUpdate') {
                $c[] = '`'.$key.'` = ?';
                $types .='s';
                $params[] = $val ;
            }
        }


        foreach ($condition as $key => $value) {
           // if(property_exists($this, $key)){
                $where[] = '`'.$key.'` = ?';

                // 000166的数值不能认为是i, 否则会被转换成166数字，比如gameid
            if (is_numeric($value)  && strlen($value) == strlen(intval($value)) && $value < 1000000000) {
                $types .='i';
                $params[] = $value;
            } else {
                $types .='s';
                // if (!isJson($value) && !isSerialized($value)) {
                //     $value = mysqli_real_escape_string($conn, $value);
                //     $value = htmlspecialchars($value);
                // }
                $params[] = $value;
            }
          //  }
        }

        $where_str = implode(' AND ', $where);
        $t = implode(',', $c);

        $sql = "UPDATE " . $this->table() . " SET " . $t ." WHERE " . $where_str ;
        $stmt = mysqli_prepare($conn, $sql) ;

        if ($stmt) {
            call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $this->refValues($params)));
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                //xlog('data update success, sql:'.$sql.' , param: '.json_encode($params), 'notice','db_update');
                lp::log()->write(FileLog::LOG_LEVEL_INFO, 'data update success, sql:'.$sql.' , param: '.json_encode($params), 'db_update');
                $rs = true;
            } else {
                //xlog('data update 0 rows, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'notice');
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, 'data update 0 rows, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'db_update');
            }
            if (mysqli_errno($conn) != 0) {
                //xlog('data update fail, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'error');
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, 'data update fail, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'db_update');
            }
            mysqli_stmt_close($stmt);

        }
        return $rs;

    }


    /**
     * 删除数据
     * @param $data array : array('name'=>'aa','tel'=>'11')
     *
     * 需要把数据库的字段声明为对象的属性，比如用户表，有id字段, User类就要声明id属性
     *
     * e.g:
     * $c = new Comment();
     * $c->delete(array('gameid'=>"323"));
     *
     */
    public function delete($data, $conn = null)
    {
        $params = array();
        $types = '';
        $rs = false;
        
        if ($conn == null) {
            $conn = $this->getConnection();
        }

        $where =  array();
        foreach ($data as $key => $value) {
            //if(property_exists($this, $key)){
                $where[] = '`'.$key.'` = ?';

                // 000166的数值不能认为是i, 否则会被转换成166数字，比如gameid
            if (is_numeric($value)  && strlen($value) == strlen(intval($value)) && $value < 1000000000) {
                $types .='i';
                $params[] = $value;
            } else {
                $types .='s';
                if (!isJson($value) && !isSerialized($value)) {
                    $value = mysqli_real_escape_string($conn, $value);
                    //$value = htmlspecialchars($value);
                }
                $params[] = $value;
            }
           // }
        }
        $where_str = implode(' AND ', $where);

        $sql = "DELETE FROM " . $this->table() . " WHERE " . $where_str ;
        $stmt = mysqli_prepare($conn, $sql) ;

        if ($stmt) {
            call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $this->refValues($params)));
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                //xlog('data delete success, sql:'.$sql.' , param: '.json_encode($params), 'notice','db_delete');
                lp::log()->write(FileLog::LOG_LEVEL_INFO, 'data delete success, sql:'.$sql.' , param: '.json_encode($params), 'db_delete');
                $rs = true;
            } else {
                //xlog('data delete 0 rows, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'notice');
                lp::log()->write(FileLog::LOG_LEVEL_ERROR, 'data delete 0 rows, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'db_delete');
            }

            if (mysqli_errno($conn) != 0) {
                //xlog('data delete fail, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'error');
                lp::App()->write(FileLog::LOG_LEVEL_ERROR, 'data delete fail, sql:'.$sql.' , param: '.json_encode($params).', error :'. mysqli_error($conn), 'db_delete');
            }
            mysqli_stmt_close($stmt);

        }
        return $rs;
    }
    public function setTable($tableName = "")
    {
        $this->table = $tableName;
    }
    public function table()
    {
        return $this->table;
    }

    public function affectedRows()
    {
        return $this->affected_rows;
    }
    
    public function insertId()
    {
        return $this->insertId;
    }

    private function refValues($arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
//Reference is required for PHP 5.3+
            $refs = array();
            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
}

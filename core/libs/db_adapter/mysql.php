<?php
/**
 * $Id: mysql.php 390 2012-07-17 17:08:22Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 meiu.cn lingter@gmail.com
 */

Class adapter_mysql{
    /**
     * 数据库请求次数
     *
     * @var Int
     */
    var $query_num = 0;
    /**
     * 数据库连接信息
     *
     * @var Array
     */
    var $dbinfo=null;
    /**
     * 数据库连接句柄
     *
     * @var resource
     */
    var $conn = null;
    /**
     * 最后一次数据库操作的错误信息
     *
     * @var mixed
     */

    var $lasterr = null;
    /**
     * 最后一次数据库操作的错误代码
     *
     * @var mixed
     */
    var $lasterrcode=null;
    /**
     * 指示事务是否启用了事务
     *
     * @var int
     */
    var $_transflag = false;
    /**
     * 启用事务处理情况下的错误
     *
     * @var Array
     */
    var $_transErrors = array();
            
    function adapter_mysql($dbinfo){
        if(is_array($dbinfo)){
            $this->dbinfo=$dbinfo;
        }else{
            exit(lang('db_config_error'));
        }
    }

    /**
     * 数据库连接
     *
     * @param Array $dbinfo
     * @return boolean
     */
    function connect($dbinfo=false) {
        
        if ($this->conn && $dbinfo == false) { return true; }
        
        if (!$dbinfo) {
            $dbinfo = $this->dbinfo;
        } else {
            $this->dbinfo = $dbinfo;
        }
        
        if (isset($dbinfo['port']) && $dbinfo['port'] != '') {
            $host = $dbinfo['host'] . ':' . $dbinfo['port'];
        } else {
            $host = $dbinfo['host'];
        }
        
        if (!isset($dbinfo['dbpass'])){ $dbinfo['dbpass'] = ''; }
        
        if(isset($dbinfo['pconnect']) && $dbinfo['pconnect']==true){
            $this->conn=@mysql_pconnect($host, $dbinfo['dbuser'],$dbinfo['dbpass']);
        }else{
            $this->conn=@mysql_connect($host, $dbinfo['dbuser'],$dbinfo['dbpass'],true);
        }
        
        if (!$this->conn){
            exit( lang('connect_mysql',$host,$dbinfo['dbuser']) );
        }
        
        if($dbinfo['dbname']) {
            if (!@mysql_select_db($dbinfo['dbname'],$this->conn)){
                exit( lang('can_not_use_db',$dbinfo['dbname']) );
            }
        }else{
                exit(lang('miss_dbname'));
        }
        
        if (isset($dbinfo['charset']) && $dbinfo['charset'] != '') {
            $charset = $dbinfo['charset'];
        } 
        
        if($this->version() > '4.1' && $charset != '') {
            mysql_query('SET NAMES "'.$charset.'"',$this->conn);
        }

        if($this->version() > '5.0') {
            mysql_query('SET sql_mode=""',$this->conn);
        }
        
        return true;
    }

    /**
     * 关闭数据库连接
     *
     */
    function close() {
        if ($this->conn) {
            mysql_close($this->conn);
        }
        $this->conn = null;
    }
    
    function q_field($tableName){
        if (substr($tableName, 0, 1) == '`') { return $tableName; }
        return '`' . $tableName . '`';
    }
    
    function q_str($value,$addquote=true){
        if(!$this->conn){
            $this->connect();
        }
        
        if (is_bool($value)) { return $value ? 1:0; }
        if (is_null($value)) { return 'NULL'; }
        
        //return "'".$value."'";
        
        //if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
        //}
        
        if(phpversion()>= '4.3.0'){
            $value =  mysql_real_escape_string($value,$this->conn);
        }elseif(phpversion()>='4.0.3'){
            $value =  mysql_escape_string($value);
        }
        return $addquote?"'".$value."'":$value;
    }
    /**
     * 直接查询Sql
     *
     * @param String $SQL
     * @return Mix
     */
    function query($SQL) {
        if(!$this->conn){
            $this->connect();
        }
        $query = @mysql_query($SQL,$this->conn);
        $this->query_num++;
        if (!$query){
            $this->lasterr = mysql_error($this->conn);
            $this->lasterrcode = mysql_errno($this->conn);
            if($this->_transflag){
                $this->_transErrors[]['sql'] = $SQL;
                $this->_transErrors[]['errcode'] = $this->lasterrcode;
                $this->_transErrors[]['err'] = $this->lasterr;
            }else{
                exit('SQL:' . $SQL .' ERROR_INFO:'.$this->lasterrcode.','.$this->lasterr);
            }
            return false;
        }else{
            $this->lasterr = null;
            $this->lasterrcode = null;
            return $query;
        }
    }
    
    function getAll($sql){
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->query($sql);
        }
        
        $data = array();
               while ($row = @mysql_fetch_assoc($res)) {
            $data[] = $row;
            }
           @mysql_free_result($res);
           
        return $data;
    }
    function getOne($sql)
    {
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->query($sql);
        }
        $row = @mysql_fetch_row($res);
        @mysql_free_result($res);
        return isset($row[0]) ? $row[0] : null;
    }

    /**
     * 执行查询，返回第一条记录
     *
     * @param string|resource $sql
     *
     * @return mixed
     */
    function getRow($sql)
    {
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->query($sql);
        }
        $row = @mysql_fetch_assoc($res);
        @mysql_free_result($res);
        return $row;
    }

    /**
     * 执行查询，返回结果集的指定列
     *
     * @param string|resource $sql
     * @param int $col 要返回的列，0 为第一列
     *
     * @return mixed
     */
    function getCol($sql, $col = 0)
    {
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->query($sql);
        }
        $data = array();
        while ($row = @mysql_fetch_row($res)) {
            $data[] = $row[$col];
        }
        @mysql_free_result($res);
        return $data;
    }
    
    function getAssoc($sql){
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->query($sql);
        }
        $data = array();
        while ($row = @mysql_fetch_row($res)) {
            $data[$row[0]] = $row[1];
        }
        @mysql_free_result($res);
        return $data;
    }
    /**
     * 加入边界的查询语句
     *
     * @param String $sql
     * @param Int $length
     * @param Int $offset
     * @return Resource
     */
    function selectLimit($sql, $length = null, $offset = null)
    {
        if ($offset !== null) {
            $sql .= " LIMIT " . (int)$offset;
            if ($length !== null) {
                $sql .= ', ' . (int)$length;
            } else {
                $sql .= ', 4294967294';
            }
        } elseif ($length !== null) {
            $sql .= " LIMIT " . (int)$length;
        }
        return $sql;
    }
    /**
     * 返回数组
     *
     * @param resouce $query
     * @return Array
     */
    function fetchArray($query) {
        return @mysql_fetch_array($query);
    }
    /**
     * 返回最近一次数据库操作受到影响的记录数
     *
     * @return int
     */
    function affectedRows() {
        return mysql_affected_rows();
    }
    /**
     * 从记录集中返回一行数据
     *
     * @param resouce $query
     *
     * @return array
     */
    function fetchRow($query) {
        return @mysql_fetch_row($query);
    }

    /**
     * Enter description here...
     *
     * @param resouce $query
     * @return Int
     */
    function numRows($query) {
        if(is_resource($query))
        $rows = @mysql_num_rows($query);
        else{
            $rows = @mysql_num_rows($this->query($query));
        }
        return $rows;
    }
    /*function numRows($sql) {
        return $this->getOne('select count(*) from ('.$sql.') as numtable');
    }*/
    /**
     * 获取当前mysql的版本号
     *
     * @return String
     */
    function version() {
        return @mysql_get_server_info();
    }
    
    function show_tables(){
        return $this->getCol('show tables');
    }

    function getColumns($table){
        return $this->getCol('desc `'.$table.'`');
    }
    /**
     * 获得刚插入数据的ID号
     *
     * @return Int
     */
    function insertId() {
        $id = mysql_insert_id($this->conn);
        return $id;
    }
     /**
     * 返回数据库可以接受的日期格式
     *
     * @param int $timestamp
     */
    function dbTimeStamp($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
    /**
     * 获得查询数据库的次数
     *
     * @return Int
     */
    function getQueryNum(){
        return $this->query_num;
    }
    function strRandom(){
        return 'RAND()';
    }
    /**
     * 启动事务
     */
    function startTrans()
    {
        $rs = $this->query('START TRANSACTION');
        $this->_transflag = true;
        $this->_transErrors = array();
        return $rs;
    }

    /**
     * 提交事务
     *
     */
    function commit()
    {
        $this->_transflag = false;
        $rs = $this->query('COMMIT');
        return $rs;
    }
    /**
     * 回滚事务
     *
     */
    function rollback(){
        $this->_transflag = false;
        $rs = $this->query('ROLLBACK');
        return $rs;
    }
    
    function transErrors(){
        $errors = $this->_transErrors;
        if(is_array($errors)){
            foreach($errors as $error){
                echo 'SQL:' . $error['sql'] .' ERROR_INFO:'.$error['errcode'].','.$error['err'];
            }
        }
        die();
    }
}

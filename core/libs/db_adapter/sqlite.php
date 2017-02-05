<?php
/**
 * $Id: sqlite.php 390 2012-07-17 17:08:22Z lingter@gmail.com $
 * 
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 meiu.cn lingter@gmail.com
 */

Class adapter_sqlite{
    /**
     * 数据库请求次数
     *
     * @var Int
     */
    var $query_num = 0;
    
    var $type = null;
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
    
    var $lastResult = null;
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

    function adapter_sqlite($dbinfo){
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
        
        $dbinfo = $dbinfo ? $dbinfo : $this->dbinfo;
        $this->conn=false;
        if(!file_exists($dbinfo['dbpath'])){
            if(file_exists(ROOTDIR.$dbinfo['dbpath'])){
                $dbinfo['dbpath'] = ROOTDIR.$dbinfo['dbpath'];
            }else{
                exit(lang('sqlite_not_exists'));
            }
        }
        
        $ver = $this->version();
        switch(true)
        {
            case class_exists("SQLite3") && ($ver==-1 || $ver==3):
                $this->conn = new SQLite3($dbinfo['dbpath']);
                if($this->conn!=NULL)
                {
                    $this->type = "SQLite3";
                    break;
                }
            case function_exists("pdo_drivers") && in_array('sqlite',pdo_drivers()) && ($ver==-1 || $ver==3):
                $this->conn = new PDO("sqlite:".$dbinfo['dbpath']);
                if($this->conn!=NULL)
                {
                    $this->type = "PDO";
                    $this->conn->query('PRAGMA read_uncommitted=1');
                    break;
                }
            case function_exists("sqlite_open") && ($ver==-1 || $ver==2):
                $this->conn = sqlite_open($dbinfo['dbpath']);
                if($this->conn!=NULL)
                {
                    $this->type = "SQLite2";
                    break;
                }
            default:
                exit('Sqlite not support!');
        }

        if($this->conn){
            return $this->conn;
        }
    }

    /**
     * 关闭数据库连接
     *
     */
    function close() {
        if ($this->conn) {
            switch($this->type){
                case 'SQLite2':
                    sqlite_close($this->conn);
                    break;
                case 'SQLite3':
                    $this->conn->close();
                    break;
                case 'PDO':
                    break;
            }
            
        }
        $this->conn = null;
        $this->lasterr = null;
        $this->lasterrcode = null;
        $this->_transCount = 0;
        $this->_transCommit = true;
    }
    
    function q_field($tableName){
        return $tableName;
    }
    
    function q_str($value,$addquote=true){
        if(!$this->conn){
            $this->connect();
        }
        
        if (is_bool($value)) { return $value ? 1:0; }
        if (is_null($value)) { return 'NULL'; }
        
        $value = stripslashes($value);
        
        if($this->type=="PDO")
        {
            if($addquote){
                return $this->conn->quote($value);
            }else{
                return $value;
            }
        }
        else if($this->type=="SQLite3")
        {
            $value = $this->conn->escapeString($value);
            return $addquote?"'".$value."'":$value;
        }
        else
        {
            $value = sqlite_escape_string($value);
            return $addquote?"'".$value."'":$value;
        }
        
        
    }
    /**
     * 直接查询Sql
     *
     * @param String $SQL
     * @return Mix
     */
    function query($sql) {
        if(!$this->conn){
            $this->connect();
        }
        
        if(strtolower(substr(ltrim($sql),0,5))=='alter') //this query is an ALTER query - call the necessary function
        {
            $queryparts = preg_split("/[\s]+/", $sql, 4, PREG_SPLIT_NO_EMPTY);
            $tablename = $queryparts[2];
            $alterdefs = $queryparts[3];
            $result = $this->alterTable($tablename, $alterdefs);
        }else{
            if($this->type=="SQLite2"){
                $result = sqlite_query($sql, $this->conn);
            }else{
                $result = $this->conn->query($sql);
            }
        }

        if(!$result){
            if($this->type=="SQLite2"){
                $this->lasterr = sqlite_last_error($this->conn);
                $this->lasterrcode = sqlite_error_string($this->lasterr);
            }elseif($this->type=="SQLite3"){
                $this->lasterr = $this->conn->lastErrorCode();
                $this->lasterrcode = $this->conn->lastErrorMsg();
                
            }elseif($this->type == 'PDO'){
                $this->lasterr = $this->conn->errorCode();
                $this->lasterrcode = implode(',',$this->conn->errorInfo());
            }
            if($this->_transflag){
                $this->_transErrors[]['sql'] = $sql;
                $this->_transErrors[]['errcode'] = $this->lasterrcode;
                $this->_transErrors[]['err'] = $this->lasterr;
            }else{
                exit('SQL:' . $sql .' ERROR_INFO:'.$this->lasterrcode.','.$this->lasterr);
                return false;
            }
        }else{
            $this->query_num++;
            $this->lastResult = $result;
            return $result;
        }
        
    }

    public function getColumns($table){
        $tempQuery = "SELECT sql,name,type FROM sqlite_master WHERE tbl_name = '".$table."' ORDER BY type DESC";
        $row = $this->getRow($tempQuery);
        if(sizeof($row)>0)
        {
            $origsql = trim(preg_replace("/[\s]+/", " ", str_replace(",", ", ",preg_replace("/[\(]/", "( ", $row['sql'], 1))));
            $oldcols = preg_split("/[,]+/", substr(trim($origsql), strpos(trim($origsql), '(')+1), -1, PREG_SPLIT_NO_EMPTY);
            for($i=0; $i<sizeof($oldcols); $i++)
            {
                $colparts = preg_split("/[\s]+/", $oldcols[$i], -1, PREG_SPLIT_NO_EMPTY);
                $oldcols[$i] = $colparts[0];
            }
            
            return $oldcols;
        }else{
            return array();
        }
    }

    public function alterTable($table, $alterdefs)
    {
        if($alterdefs == '')
        {
            return false;
        }

        $tempQuery = "SELECT sql,name,type FROM sqlite_master WHERE tbl_name = '".$table."' ORDER BY type DESC";
        $row = $this->getRow($tempQuery);

        if(sizeof($row)>0)
        {
            $tmpname = 't'.time();
            $origsql = trim(preg_replace("/[\s]+/", " ", str_replace(",", ", ",preg_replace("/[\(]/", "( ", $row['sql'], 1))));
            $createtemptableSQL = 'CREATE TEMPORARY '.substr(trim(preg_replace("'".$table."'", $tmpname, $origsql, 1)), 6);
            $createindexsql = array();
            $i = 0;
            $defs = preg_split("/[,]+/",$alterdefs, -1, PREG_SPLIT_NO_EMPTY);
            $prevword = $table;
            $oldcols = preg_split("/[,]+/", substr(trim($createtemptableSQL), strpos(trim($createtemptableSQL), '(')+1), -1, PREG_SPLIT_NO_EMPTY);
            $newcols = array();
            for($i=0; $i<sizeof($oldcols); $i++)
            {
                $colparts = preg_split("/[\s]+/", $oldcols[$i], -1, PREG_SPLIT_NO_EMPTY);
                $oldcols[$i] = $colparts[0];
                $newcols[$colparts[0]] = $colparts[0];
            }
            $newcolumns = '';
            $oldcolumns = '';
            reset($newcols);
            while(list($key, $val) = each($newcols))
            {
                $newcolumns .= ($newcolumns?', ':'').$val;
                $oldcolumns .= ($oldcolumns?', ':'').$key;
            }
            $copytotempsql = 'INSERT INTO '.$tmpname.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$table;
            $dropoldsql = 'DROP TABLE '.$table;
            $createtesttableSQL = $createtemptableSQL;
            foreach($defs as $def)
            {
                $defparts = preg_split("/[\s]+/", $def,-1, PREG_SPLIT_NO_EMPTY);
                $action = strtolower($defparts[0]);
                switch($action)
                {
                    case 'add':
                        if(sizeof($defparts) <= 2)
                            return false;
                        $createtesttableSQL = substr($createtesttableSQL, 0, strlen($createtesttableSQL)-1).',';
                        for($i=1;$i<sizeof($defparts);$i++)
                            $createtesttableSQL.=' '.$defparts[$i];
                        $createtesttableSQL.=')';
                        break;
                    case 'change':
                        if(sizeof($defparts) <= 3)
                        {
                            return false;
                        }
                        if($severpos = strpos($createtesttableSQL,' '.$defparts[1].' '))
                        {
                            if($newcols[$defparts[1]] != $defparts[1])
                                return false;
                            $newcols[$defparts[1]] = $defparts[2];
                            $nextcommapos = strpos($createtesttableSQL,',',$severpos);
                            $insertval = '';
                            for($i=2;$i<sizeof($defparts);$i++)
                                $insertval.=' '.$defparts[$i];
                            if($nextcommapos)
                                $createtesttableSQL = substr($createtesttableSQL,0,$severpos).$insertval.substr($createtesttableSQL,$nextcommapos);
                            else
                                $createtesttableSQL = substr($createtesttableSQL,0,$severpos-(strpos($createtesttableSQL,',')?0:1)).$insertval.')';
                        }
                        else
                            return false;
                        break;
                    case 'drop':
                        if(sizeof($defparts) < 2)
                            return false;
                        if($severpos = strpos($createtesttableSQL,' '.$defparts[1].' '))
                        {
                            $nextcommapos = strpos($createtesttableSQL,',',$severpos);
                            if($nextcommapos)
                                $createtesttableSQL = substr($createtesttableSQL,0,$severpos).substr($createtesttableSQL,$nextcommapos + 1);
                            else
                                $createtesttableSQL = substr($createtesttableSQL,0,$severpos-(strpos($createtesttableSQL,',')?0:1) - 1).')';
                            unset($newcols[$defparts[1]]);
                        }
                        else
                            return false;
                        break;
                    default:
                        return false;
                }
                $prevword = $defparts[sizeof($defparts)-1];
            }
            //this block of code generates a test table simply to verify that the columns specifed are valid in an sql statement
            //this ensures that no reserved words are used as columns, for example
            $tempResult = $this->query($createtesttableSQL);
            if(!$tempResult)
                return false;
            $droptempsql = 'DROP TABLE '.$tmpname;
            $tempResult = $this->query($droptempsql);
            //end block

            
            $createnewtableSQL = 'CREATE '.substr(trim(preg_replace("'".$tmpname."'", $table, $createtesttableSQL, 1)), 17);
            $newcolumns = '';
            $oldcolumns = '';
            reset($newcols);
            while(list($key,$val) = each($newcols))
            {
                $newcolumns .= ($newcolumns?', ':'').$val;
                $oldcolumns .= ($oldcolumns?', ':'').$key;
            }
            $copytonewsql = 'INSERT INTO '.$table.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$tmpname;

            $this->query($createtemptableSQL); //create temp table
            $this->query($copytotempsql); //copy to table
            $this->query($dropoldsql); //drop old table

            $this->query($createnewtableSQL); //recreate original table
            $this->query($copytonewsql); //copy back to original table
            $this->query($droptempsql); //drop temp table
        }
        return true;
	}
    
    function _free($result){
        if($this->type=="PDO"){
            $result->closeCursor();
        }
    }
    
    function getAll($sql){
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->query($sql);
        }
        
        $data = array();
        while ($row = $this->fetchArray($res,'assoc')) {
            $data[] = $row;
        }
        $this->_free($res);
        return $data;
    }
    
    function getOne($sql)
    {
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->query($sql);
        }
        $row = $this->fetchArray($res,'num');
        $this->_free($res);
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
        $row = $this->fetchArray($res,'assoc');
        
        $this->_free($res);
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
        while ($row = $this->fetchArray($res,'num')) {
            $data[] = $row[$col];
        }
        $this->_free($res);
        return $data;
    }
    
    function getAssoc($sql){
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->query($sql);
        }
        $data = array();
        while ($row = $this->fetchArray($res,'num')) {
            $data[$row[0]] = $row[1];
        }
        $this->_free($res);
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
    function fetchArray($result,$mode="both") {
        if($this->type=="PDO")
        {
            if($mode=="assoc")
                $mode = PDO::FETCH_ASSOC;
            else if($mode=="num")
                $mode = PDO::FETCH_NUM;
            else
                $mode = PDO::FETCH_BOTH;
            $data = $result->fetch($mode);
            return $data;
        }
        else if($this->type=="SQLite3")
        {
            if($mode=="assoc")
                $mode = SQLITE3_ASSOC;
            else if($mode=="num")
                $mode = SQLITE3_NUM;
            else
                $mode = SQLITE3_BOTH;
            return $result->fetchArray($mode);
        }else{
            if($mode=="assoc")
                $mode = SQLITE_ASSOC;
            else if($mode=="num")
                $mode = SQLITE_NUM;
            else
                $mode = SQLITE_BOTH;
            return @sqlite_fetch_array($result,$mode);
        }
    }
    /**
     * 返回最近一次数据库操作受到影响的记录数
     *
     * @return int
     */
    function affectedRows() {
        if($this->type=="PDO")
            return $this->lastResult->rowCount();
        else if($this->type=="SQLite3")
            return $this->conn->changes();
        else if($this->type=="SQLite2")
            return sqlite_changes($this->conn);
    }
    /**
     * 从记录集中返回一行数据
     *
     * @param resouce $query
     *
     * @return array
     */
    function fetchRow($query) {
        return $this->fetchArray($query,'assoc');
    }

    /**
     * Enter description here...
     *
     * @param resouce $query
     * @return Int
     */
    function numRows($sql) {
        if(!$this->conn){
            $this->connect();
        }
        
        if($this->type=="PDO"){
            $rows = $this->getOne('select count(*) from ('.$sql.')');
        }else if($this->type=="SQLite3"){
            $rows = $this->getOne('select count(*) from ('.$sql.')');
        }else if($this->type=="SQLite2"){
            $rows = @sqlite_num_rows($this->query($sql));
        }
        return $rows;
    }
    
    function show_tables(){
        return $this->getCol('select name from sqlite_master where type=\'table\' order by name');
    }
    /**
     * 获取当前Sqlite库的版本号
     *
     * @return String
     */
    function version() {
        //return sqlite_libversion();
        if(file_exists($this->dbinfo['dbpath'])) //make sure file exists before getting its contents
        {
            $content = strtolower(file_get_contents($this->dbinfo['dbpath'], NULL, NULL, 0, 40)); //get the first 40 characters of the database file
            $p = strpos($content, "** this file contains an sqlite 2"); //this text is at the beginning of every SQLite2 database
            if($p!==false) //the text is found - this is version 2
                return 2;
            else
                return 3;
        }
        else //return -1 to indicate that it does not exist and needs to be created
        {
            return -1;
        }
    }
    /**
     * 获得刚插入数据的ID号
     *
     * @return Int
     */
    function insertId() {
        if($this->type=="PDO")
            return $this->conn->lastInsertId();
        else if($this->type=="SQLite3")
            return $this->conn->lastInsertRowID();
        else if($this->type=="SQLite2")
            return sqlite_last_insert_rowid($this->conn);
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
        $rs = $this->query('BEGIN');
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

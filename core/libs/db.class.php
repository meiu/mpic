<?php
/**
 * $Id: db.class.php 390 2012-07-17 17:08:22Z lingter@gmail.com $
 *  
 * Database Class
 *
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

class db_cla{
    var $db;
    
    var $adapter;
    
    var $pre='';
    
    var $table;
    
    var $order;
    
    var $where;
    
    var $arr=array();
    
    var $sql=null;
    
    function init($db_config){

        if(is_array($db_config) && $db_config['adapter'])
            $adapterName='adapter_'.$db_config['adapter'];
        else{
            exit(lang('db_config_error'));
        }
        $this->adapter = strtolower($db_config['adapter']);
        
        if(isset($db_config['pre']))
              $this->pre=$db_config['pre'];
        
        require_once(LIBDIR.'db_adapter/'.strtolower($db_config['adapter']).'.php');

        $this->db= new $adapterName($db_config);
    }
    /**
     * 设定数据库表前缀
     *
     * @param 前缀 $pre
     */
    function setPre($pre){
        $this->pre=$pre;
    }
    
    /**
     * 可手工设置数据库 表名
     *
     * @param 表名 $table
     */
    function setTable($table)
    {
        if(!empty($table)){
            $this->table=str_replace('#@',$this->pre,$table);
        }
    }
    
    function stripTpre($str){
        return str_replace('#@',$this->pre,$str);
    }
    /**
     * 可手工设置sql 语句
     *
     * @param sql语句 $sql
     */
    function setSql($sql){
        if(!empty($sql)){
            $this->sql=str_replace('#@',$this->pre,$sql);
        }
    }
    
    //返回sql语句
    function getSql(){
        return $this->sql;
    }

    //设置排序
    function setOrder($order){
        if((bool)$order){
            $this->order=' order by '.$order;
        }else{
            $this->order='';
        }
    }

        //设置条件
    function setWhere($where){
        if(!(bool)$where){
            $where=' 1=1 ';
        }
        $this->where=$where;
    }

    //设置字段
    function setField($filed){
        $this->field=$filed;
    }

    //设置数组
    function setArr($arr){
        $this->arr=$arr; //$arr = array(0=>array('name'=>'ssss')); 这种形式
    }
    
    /**
     * string filter 
     *
     * @param string $str 
     * @param bool $addquote Example : true:'value',false:value
     * @return string
     * @author Lingter
     */
    function q_str($str,$addquote=true){
        return $this->db->q_str($str,$addquote);
    }
    /**
     * 数据库查询语句
     *
     * @param 表名 $table
     * @param 字段 $field
     * @param 条件 $if
     * @param 排序 $order
     */
    function select($table,$field,$where='',$order=''){
        $this->setTable($table);
        $this->setField($field);
        $this->setWhere($where);
        $this->setOrder($order);
        $this->_select();
        return $this->sql;
    }
    
    function _select(){
        $this->sql='select  '.$this->field.'  from   '.$this->table.'  where  '.$this->where.' '.$this->order;
        return $this->sql;
    }
    /**
     * 数据库删除语句
     *
     * @param 表名 $table
     * @param 条件 $if
     */
    function delete($table,$where){
        $this->setTable($table);
        $this->setWhere($where);
        return $this->_delete();
    }

    function _delete(){
        $this->sql='delete from '.$this->table.' where '.$this->where;
        return $this->sql;
    }

    /**
     * 分页函数
     *
     * @param 当前页 $no_p
     * @param 每页记录数 $title_rows
     * @param URL地址 $set_url
     * @param 定义分页SQL $sqlcount
     * @param 定义SQL $sql
     * @return array
     */
    function toPage($no_p,$title_rows,$sqlcount=null,$sql=null){
        if(!$sql)
        $sql = $this->sql;

        if(!$sqlcount){
          $total=$this->numRows($sql);
        }else{
          $total=$this->getOne($sqlcount);
        }
        
        $totalpage=ceil($total/$title_rows);

        if($no_p<1) $no_p=1;
        if($no_p>$totalpage) $no_p=$totalpage;

        if($total>0){
        $sql=$this->selectLimit($sql,$title_rows,($no_p-1)*$title_rows);

        $aa=$this->getAll($sql);

        }else{
            $aa=null;
        }

        $arr["ls"]= $aa; //记录内容

        $arr["total"]= $totalpage;//总数页数
        $arr['current']=$no_p; //当前页
        $arr['count']=$total;

        return $arr;
    }
    /**
     * 更新数据操作
     *
     * @param 表名称 $table
     * @param 条件 $if
     * @param 条件值 $arr
     */
    function update($table,$if,$arr){

        $this->setTable($table);
        $this->setWhere($if);
        $this->setArr($arr);
        return $this->_update();
    }
    
    function _update(){
        $row=$this->arr;
        $fieldValuePairs = array();
        foreach ($row as $fieldName => $value) {
            if(_is_obj($value,'DB_Expr')){
                $fieldValuePairs[] = $this->db->q_field($fieldName).
                    ' = ' . $value->get();
            }else{
                $fieldValuePairs[] = $this->db->q_field($fieldName).
                    ' = ' . $this->db->q_str($value);
            }
        }
       $fieldValuePairs = implode(', ', $fieldValuePairs);
       
       $this->sql='update '.$this->table.' set '. $fieldValuePairs.' where '.$this->where;
       return $this->sql;
    }
    
    /**
     * 插入数据操作
     *
     * @param 表名称 $table
     * @param 更新字段,值 $arr
     * 如:insert('users',array('xiao'=>'123456'))
     */
    function insert($table,$arr){
        $this->setTable($table);
        $this->setArr($arr);
        return $this->_insert();
    }
    /**
     * 获得刚刚插入数据的ID
     *
     *
     * @return Mixed
     */
    function insertId(){
        return $this->db->insertId();
    }

    function _insert(){
        $row=$this->arr;
        $fields = array();
        $values = array();
        foreach ($row as $fieldName => $value) {
            $fields[] = $this->db->q_field($fieldName);
            if(_is_obj($value,'DB_Expr')){
                $values[] = $value->get();
            }else{
                $values[] = $this->db->q_str($value);
            }
        }
        $fields=implode(',',$fields);
        $values=implode(',',$values);
        $this->sql='insert into '.$this->table.' ('. $fields.') values ( '.$values.') ';
        return $this->sql;
    }
    /**
     * 加入边界的查询语句
     *
     * @param 请求SQL $sql
     * @param 数据数  $length
     * @param Offset $offset
     * @return string $Sql
     */
    function selectLimit($sql=null, $length = null, $offset = null){
    
        if($sql!=null){
            $this->sql=$sql;
        }
        
        $this->sql=$this->db->selectLimit($this->sql,$length,$offset);
    }

    /**
     * 调试时候 用此函数可以输出 sql 语句是否ok
     *
     */
    function echo_sql(){
        echo $this->sql;
        exit();
    }
    /**
     * 执行查询
     *
     * @param string|resource $sql
     *
     * @return Mixed
     */
    function query($sql=false) {
        if(!$sql)
        $sql=$this->sql;
        else
        $this->setSql($sql);
        return $this->db->query($this->sql);
    }
    /**
     * 执行查询，返回所有的结果
     *
     * @param string|resource $sql
     *
     * @return mixed
     */
    function getAll($sql=false){
       if(!$sql)
        $sql=$this->sql;
        else 
        $this->setSql($sql);
        
        return $this->db->getAll($this->sql);
    }
    /**
     * 执行查询，返回第一条记录的第一个字段
     *
     * @param string|resource $sql
     *
     * @return mixed
     */
    function getOne($sql=false)
    {
        if(!$sql)
        $sql=$this->sql;
        else
        $this->setSql($sql);
        
        return $this->db->getOne($this->sql);
    }

    /**
     * 执行查询，返回第一条记录
     *
     * @param string|resource $sql
     *
     * @return mixed
     */
    function  getRow($sql=false)
    {
        if(!$sql)
        $sql=$this->sql;
        else
        $this->setSql($sql);

        return $this->db->getRow($this->sql);
    }

    /**
     * 执行查询，返回结果集的指定列
     *
     * @param string|resource $sql
     * @param int $col 要返回的列，0 为第一列
     *
     * @return mixed
     */
    function getCol($col = 0,$sql=false)
    {
        if(!$sql)
        $sql=$this->sql;
        else
        $this->setSql($sql);
        
        return $this->db->getCol($this->sql,$col);
    }
    /**
     * 执行查询，返回Key-Value的数组
     *
     * @param string|resource $sql
     * @return Int
     */
    function getAssoc($sql=false){
        if(!$sql)
        $sql=$this->sql;
        else
        $this->setSql($sql);
        
        return $this->db->getAssoc($this->sql);
    }

    function fetchArray($resource){
        return $this->db->fetchArray($resource);
    }
    /**
     * Sql语句返回处理的行数
     *
     * @return Int
     */
    function numRows($query){
        return $this->db->numRows($query);
    }
    /**
     * 影响的行数
     *
     * @return Int
     */
    function affectedRows(){
        return $this->db->affectedRows();
    }
    /**
     * 关闭数据库
     *
     */
    function close(){
        $this->db->close();
    }

    function strRandom(){
        return $this->db->strRandom();
    }
    /**
     * 开始事务
     *
     */
    function startTrans(){
        $this->db->startTrans();
    }
    /**
     * 事务递交
     *
     */
    function commit()
    {
       $this->db->commit();
    }
    /**
     * 事务回滚
     *
     */
    function rollback()
    {
       $this->db->rollback();
    }
    /**
     * 打印事务中的错误
     *
     */
    function transErrors()
    {
        $this->db->transErrors();
    }
    /**
     * 返回数据库请求次数
     *
     * @return Int
     */
    function getQueryNum(){
        return $this->db->getQueryNum();    
    }
    /**
     * 返回数据库可以接受的日期格式
     *
     * @param Int $time
     * @return unknown
     */
    function DBTime($time=''){
        if ($time=='') {
            $time=time();
        }
        return $this->db->dbTimeStamp($time);
    }
    
    function version(){
        return $this->db->version();
    }
    
    function show_tables(){
        return $this->db->show_tables();
    }

    function getColumns($table){
        return $this->db->getColumns($this->stripTpre($table));
    }
}

class DB_Expr{
    /**
     * Storage for the SQL expression.
     *
     * @var string
     */
    var $_expr;
    
    function DB_Expr($expr){
        $this->_expr = (string) $expr;
    }

    function get(){
        return $this->_expr;
    }
}

function _is_obj($v,$class_name){
    if(PHPVer == 4){
        return is_a($v,$class_name);
    }else{
        return $v instanceof $class_name;
    }
}
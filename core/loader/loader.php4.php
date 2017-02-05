<?php
/**
 * $Id: loader.php4.php 331 2012-01-20 09:53:21Z lingter@gmail.com $
 *
 * supper loader
 *
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */


define('SP_LOADER', '__SP__LOADER_CORE__');

$GLOBALS[SP_LOADER] = array(
    'SELF'                => null,
    'OBJECTS'             => array(),
    'CONFIGS'             => array(),
    'HELPERS'             => array(),
    'MODELS'              => array(),
    'DATABASES'           => array()
);
require_once(INCDIR.'modelfactory.php');

class loader{
    function &instance(){
        if(!isset($GLOBALS[SP_LOADER]['SELF'])){
            $GLOBALS[SP_LOADER]['SELF'] =& new Loader();
        }
        return $GLOBALS[SP_LOADER]['SELF'];
    }
        /**
         * 装载库文件
         */
    function &lib($class){
        if (!isset($GLOBALS[SP_LOADER]['OBJECTS'][$class])){
            if(file_exists(LIBDIR.$class.'.class.php')){
                require(LIBDIR.$class.'.class.php');
                $name = $class.'_cla';
                $GLOBALS[SP_LOADER]['OBJECTS'][$class] =& new $name();
            }else{
                exit(lang('load_lib_error',$class));
            }
        }
        return $GLOBALS[SP_LOADER]['OBJECTS'][$class];
    }
    /**
     * 装载模型
     */
    function &model($modelName){
        if(!isset($GLOBALS[SP_LOADER]['MODELS'][$modelName])){
            $modelPath = MODELDIR.$modelName.'.mdl.php';
            $modelClass = $modelName.'_mdl';
            if(file_exists($modelPath)) {
                require($modelPath);
            }else{ 
                exit(lang('load_model_error',$modelName));
            }
            $GLOBALS[SP_LOADER]['MODELS'][$modelName] =& new $modelClass;
        }
        return $GLOBALS[SP_LOADER]['MODELS'][$modelName];
    }
    /**
     * 装载数据库
     */
    function &database($dbstr='default',$config = ''){
        if (!isset($GLOBALS[SP_LOADER]['DATABASES'][$dbstr])){
            if(is_array($config)){
                $DB_config = $config;
            }else{
                $Config =& loader::config();
                $DB_config = $Config['database'][$dbstr];
            }
            $db_class =& loader::lib('db');
            
            $db_class->init($DB_config);
            $GLOBALS[SP_LOADER]['DATABASES'][$dbstr] =& $db_class;
        }
        return $GLOBALS[SP_LOADER]['DATABASES'][$dbstr];
    }
    /**
     * 装载视图
     */
    function view($tplFile,$isDisplay = true){
        $tpl =& loader::model('template');
        if($isDisplay){
            echo $tpl->fetch($tplFile);
        }else{
            return $tpl->fetch($tplFile);
        }
    }
    /**
     * 装载配置
     */
    function &config($name = 'config'){
        if ( !isset($GLOBALS[SP_LOADER]['CONFIGS'][$name])){
            if (!file_exists(ROOTDIR."conf/{$name}.php")){
                exit(lang('config_file_not_exists'));
            }
            require(ROOTDIR."conf/{$name}.php");

            if ( ! isset($CONFIG) || ! is_array($CONFIG)){
                exit(lang('config_file_error'));
            }

            $GLOBALS[SP_LOADER]['CONFIGS'][$name] =& $CONFIG;
        }
        return $GLOBALS[SP_LOADER]['CONFIGS'][$name];
    }

    /**
     * 
     * 装载辅助小部件
     * @param 名字 $name
     */
    function helper($name){
        $path = COREDIR.'helper/'.strtolower($name).'.php';
        if(file_exists($path)){
            require_once($path);
        }else{
            exit('Load helper error: file (helper/'.strtolower($name).'.php) does not exist!');
        }
    }
}

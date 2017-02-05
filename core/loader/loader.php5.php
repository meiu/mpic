<?php
/**
 * $Id: loader.php5.php 331 2012-01-20 09:53:21Z lingter@gmail.com $
 *
 * supper loader
 *
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */

require_once(INCDIR.'modelfactory.php');

class Loader{
    
    private static $_objects = array();
    private static $_configs = array();
    private static $_helpers = array();
    private static $_models  = array();
    private static $_databases = array();
    private static $_view    = null;
    
    static function &instance(){
        static $instance;
        if (is_null($instance)) $instance = new loader();
        return $instance;
    }
    /**
     * 装载库文件
     */
    static function &lib($class){
        //$class = ucfirst($class);
        if (!isset(self::$_objects[$class])){
            if(file_exists(LIBDIR.$class.'.class.php')){
                require(LIBDIR.$class.'.class.php');
                $name = $class.'_cla';
                self::$_objects[$class] = new $name();
            }else{
                exit(lang('load_lib_error',$class));
            }
        }
        return self::$_objects[$class];
    }
    /**
     * 装载模型
     */
    static function &model($modelName){
        if(!isset(self::$_models[$modelName])){
            $modelPath = MODELDIR.$modelName.'.mdl.php';
            $modelClass = $modelName.'_mdl';
            if(file_exists($modelPath)) {
                require($modelPath);
            }else{ 
                exit(lang('load_model_error',$modelName));
            }
            self::$_models[$modelName] = new $modelClass;
        }
        return self::$_models[$modelName];
    }
    /**
     * 装载数据库
     */
    static function &database($dbstr='default',$config = ''){
        if (!isset(self::$_databases[$dbstr])){
            if(is_array($config)){
                $DB_config = $config;
            }else{
                $Config =& loader::config();
                $DB_config = $Config['database'][$dbstr];
            }
            $db_class =& loader::lib('db');
            
            $db_class->init($DB_config);
            self::$_databases[$dbstr] = $db_class;
        }
        return self::$_databases[$dbstr];
    }
    /**
     * 装载视图
     */
    static function view($tplFile,$isDisplay = true){
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
    static function &config($name = 'config'){
        if ( !isset(self::$_configs[$name])){
            if (!file_exists(ROOTDIR."conf/{$name}.php")){
                exit(lang('config_file_not_exists'));
            }
            require(ROOTDIR."conf/{$name}.php");

            if ( ! isset($CONFIG) || ! is_array($CONFIG)){
                exit(lang('config_file_error'));
            }

            self::$_configs[$name] = $CONFIG;
        }
        return self::$_configs[$name];
    }

    /**
     * 
     * 装载辅助小部件
     * @param 名字 $name
     */
    static function helper($name){
        $path = COREDIR.'helper/'.strtolower($name).'.php';
        if(file_exists($path)){
            require_once($path);
        }else{
            exit('Load helper error: file (helper/'.strtolower($name).'.php) does not exist!');
        }
    }
}

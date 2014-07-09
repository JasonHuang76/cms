<?php

class Model_Modules {
    protected $_model;
    protected $_options;
    protected $_modules = array();
    protected $_modules_path;
    
    function __construct($model="",$options="") {
        $this->_model = $model;
        $this->_options = $options;
    }
    
    function __get($name){
         return $this->getModule($name);
    }
    
    function loadModule($name,$options){
        require_once $this->getModulesPath() . strtolower($name) . ".php";
        $class = get_class($this) . "_" . ucfirst($name);
        if(is_subclass_of($class, 'Model_Modules')){
            $module = new $class($this,$options);
            return $module;
        }else{
            throw new Exception($class . " is not a subclass of Model_Modules");
        }
    }
    
    function getModule($name){
        if(!isset($this->_modules[$name])){
            $module = $this->loadModule($name,$this->_options[$name]);
            if(is_subclass_of($module, 'Model_Modules')){
                $this->_modules[$name] = $module;
            }else{
                throw new Exception('get model modules error');
            }
        }
        return $this->_modules[$name];        
    }
    
    function getModulesPath(){
        if(empty($this->_modules_path)){
            $this->_modules_path = APP_ROOT_PATH . 'class' . DIRECTORY_SEPARATOR  . str_replace( '_' , DIRECTORY_SEPARATOR ,strtolower( get_class($this) ) ) . DIRECTORY_SEPARATOR ;
        }
        return $this->_modules_path;
    }
}

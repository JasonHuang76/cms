<?php
class App {
    //put your code here
    protected static $_helper = array();
    protected static $_configs;
    protected static $_defaults;
    
    static function addHelper($id,$obj){
        self::$_helper[$id] = $obj;
    }
    
    static function getHelper($id){
        if(isset(self::$_helper[$id])){
            return self::$_helper[$id];
        }
    }
    
    static function configs($values=null){
        if(is_null(self::$_configs)){
            self::$_configs = new Model_Arrayaccess();
        }
        if(is_array($values)){
            self::$_configs = self::fill_data(self::$_configs, $values);
        }
        return self::$_configs;
    }
    
    static function defaults($values=null){
        if(is_null(self::$_defaults)){
            self::$_defaults = new Model_Arrayaccess();
        }
        if(is_array($values)){
            self::$_defaults = self::fill_data(self::$_defaults, $values);
        }        
        return self::$_defaults;
    }
    
    static function fill_data($model,$values){
        foreach($values as $k => $v){
            if(is_array($v)){
                $submodel = new Model_Arrayaccess();
                $model[$k] = self::fill_data($submodel, $v);
            }else{
                $model[$k] = $v;
            }
        }
        return $model;
    }
    
}

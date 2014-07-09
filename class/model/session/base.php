<?php
abstract class Model_Session_Base extends Model_Modules {
    protected $_container;
    function __construct($model = "", $options = "") {
        parent::__construct($model, $options);
    }
    function &__get($name){
        return $this->_container[$name];
    }
    function __set($name,$value){
        $this->_container[$name] = $value;
    }
}

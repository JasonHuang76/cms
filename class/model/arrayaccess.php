<?php
class Model_Arrayaccess implements arrayaccess {
    protected $_storage;

    function &__get($name){
        if(!isset($this->_storage[$name])){
            $this->_storage[$name] = new self();
        }
        return $this->_storage[$name];
    }
    function __set($name,$value){
        $this->_storage[$name] = self::_fill_data($value);
    }
    function __isset($name) {
        return isset($this->_storage[$name]);
    }
    function __unset($name) {
        unset($this->_storage[$name]);
    }

    function isValueExists($name){
        return isset($this->_storage[$name]);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_storage[] = self::_fill_data($value);
        } else {
            $this->_storage[$offset] = self::_fill_data($value);
        }
    }
    public function offsetExists($offset) {
        return isset($this->_storage[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->_storage[$offset]);
    }
    public function offsetGet($offset) {
        if(!isset($this->_storage[$offset])){
            $this->_storage[$offset] = new self();
        }        
        return $this->_storage[$offset];
    }    
    static protected function _fill_data($values){
        if(is_array($values)){
            $model = new self();
            foreach($values as $k => $v){
                $model[$k] = self::_fill_data($v);
            }
            return $model;
        }else{
            return $values;
        }
    }
}

<?php
class Model_Arrayaccess implements arrayaccess {
    protected $_storage;

    function &__get($name){
        return $this->_storage[$name];
    }
    function __set($name,$value){
        $this->_storage[$name] = $value;
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
            $this->_storage[] = $value;
        } else {
            $this->_storage[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->_storage[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->_storage[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->_storage[$offset]) ? $this->_storage[$offset] : null;
    }    
}

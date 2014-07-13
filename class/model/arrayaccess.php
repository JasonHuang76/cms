<?php
class Model_Arrayaccess implements IteratorAggregate , ArrayAccess , Serializable , Countable  {
    protected $_storage;

    function __construct($array=null) {
        $this->_storage = array();
        if(!is_null($array)){
            $this->_storage = (array)$array;
        }
    }
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
    function __call($name, $arguments) {
        if(preg_match("/sort$/i", $name) && function_exists($name)){
            return call_user_func_array($name,array(&$this->_storage,$arguments[0]));
        }else{
            throw new Exception('method : '.$name." doesn't exists!");
        }
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
    
    public function getIterator() {
        return new ArrayIterator($this->_storage);
    }    
    
    public function serialize() {
        return serialize($this->_storage);
    }
    
    public function unserialize($data) {
        $this->_storage = unserialize($data);
    }  
    
    public function count() { 
        return count($this->_storage); 
    }     
    public function append($value) {
        $this->_storage[] = $value;
    }
    public function exchangeArray($input){
        $old_array = $this->_storage;
        $this->_storage = (array)$input;
        return $old_array;
    }
    //    sort series methods
//    public function sort($flags = SORT_REGULAR){
//        return sort($this->_storage,$flags);
//    }
//    public function rsort($flags = SORT_REGULAR){
//        return rsort($this->_storage,$flags);
//    }
//    public function ksort($flags = SORT_REGULAR){
//        return ksort($this->_storage,$flags);
//    }
//    public function krsort($flags = SORT_REGULAR){
//        return krsort($this->_storage,$flags);
//    }
//    public function asort($flags = SORT_REGULAR){
//        return asort($this->_storage,$flags);
//    }
//    public function arsort($flags = SORT_REGULAR){
//        return arsort($this->_storage,$flags);
//    }
}

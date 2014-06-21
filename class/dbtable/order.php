<?php
class Dbtable_Order extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "order";
    protected $pk = "o_id";

    //與items一起寫入資料
    function writeDataWithItems($post,$items){
        $this->insert($post);
        $pk = $post[$this->pk];
        if(empty($pk)){
            throw new Exception("should be ".$this->pk." value write with items");
        }
        foreach($items as $item){
            $item[$this->pk] = $pk;
            $this->items()->writeData($item);
        }
        
    }    
}
?>

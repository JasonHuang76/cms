<?php
abstract class Dbtable_Abstract {
    //可修改的欄位
    protected $prefix;
    protected $table;
    protected $db;
    protected $pk;
    protected $post_cols = array(); 
    protected $values = array();
    protected $con = array();
    protected $query_resource;
    protected $order_status = array();
    protected $sort_col;
    protected $status_col;
    
    public function __construct(DB $db,$prefix) {
        $this->db = $db;
        $this->prefix = $prefix;
    }
    
    public function __get($name) {
        return $this->values[$name];
    }
    protected function _query($sql){
        $this->query_resource = $this->db->query($sql,true);        
    }
    public function writeData($post){
        $this->_retrieve_cols($post); 
        if(!empty($this->values[$this->pk])){
            $this->con[] = sprintf("`%s`='%s'",$this->pk,$this->values[$this->pk]);            
            $sql = $this->_mk_update_sql();
        }else{
            $sql = $this->_mk_insert_sql();
        }
        $this->_query($sql);
    }
    
    public function getData($pk,$cols="*"){
        if(is_array($cols)){
            $cols = implode(',',$cols);
        }
        $con = sprintf("`%s`='%s'",$this->pk,$pk);
        $sql = $this->_mk_select_sql($con, $cols);
        $this->_query($sql);
        $row = $this->db->fetch_array($this->query_resource, 1);
        $this->values = $row;
        return $this;
    }
    
    public function getDataRow($cols="*"){
        if($cols!="*"){
            $cols = explode(",",$cols);
            $new_values = array();
            foreach($cols as $k=>$v){
                $v = trim($v);
                $new_values[$v] = $this->values[$v];
            }
            return $new_values;
        }else{
            return $this->values;    
        }
    }
    
    public function report(){
        return $this->db->report();
    }
    public function affected_rows(){
        return $this->db->affected_rows();
    }
    //取得post資料欄位
    protected function _retrieve_cols($post){
        $this->values = array();
        $this->con = array();
        foreach($post as $k=>$v){
            if(get_magic_quotes_gpc()){
                $v = stripslashes($v);
            }
            if(isset($this->post_cols[$k])){
                if(is_array($v)){
                    $this->values[$k] = implode(',',$v);
                }else{
                    $v = trim($v);
                    if(preg_match("/(seo_title|seo_keyword|seo_description|seo_filename|seo_h1)$/i", $k)){
                        $v = htmlspecialchars($v);
                    }
                    $this->values[$k] = $this->db->quote($v);
                }
            }
        }
        $keys = array_keys($this->post_cols);
        $md = preg_grep('/modifydate$/i',$keys);
        if(!empty($md)){
            foreach($md as $v){
                $this->values[$v] = date("Y-m-d H:i:s");
            }
        }
    }
    //製作新增sql
    protected function _mk_insert_sql(){
        $sql_tpl = "insert into ".$this->tablename()."(%s)values(%s)";
        foreach($this->values as $k=>$v){
            $columns[]=sprintf("`%s`",$k);
            $values[]=sprintf("'%s'",$v);
        }
        return sprintf($sql_tpl,implode(',',$columns),implode(',',$values));
        
    }
    
    //製作新增sql
    protected function _mk_update_sql(){
        $sql_tpl = "update ".$this->tablename()." set %s where %s";
        foreach($this->values as $k=>$v){
            if($k!=$this->pk){
                $updates[] = sprintf("`%s`='%s'",$k,$v);
            }
        }
        return sprintf($sql_tpl,implode(',',$updates),implode(' and ',$this->con));
    } 
    
    protected function _mk_select_sql($con="",$col="*",$order=null,$limit=null){
        $sql = "select ".$col." from ".$this->tablename();
        if($con)$sql.=" where ". $con;
        if($order)$sql.=" order by ".$order;
        if($limit)$sql.=" limit ".$limit;
        return $sql;
    }
    
    protected function _mk_delete_sql($con,$limit=null){
        if($con){
            $sql = "delete from ".$this->tablename() . " where ".$con;
            if($limit)$sql.=" limit ".$limit;
            return $sql;
        }
    }
    
    protected function tablename(){
        return $this->prefix."_".$this->table;
    }
    
    public function getDataNums($con){
        $sql = $this->_mk_select_sql($con);
        $this->_query($sql);
        return $this->db->numRows($this->query_resource);
    }
    public function getDataList($con="",$col='*',$order=null,$limit=null){
        $sql = $this->_mk_select_sql($con, $col, $order, $limit);
        $this->_query($sql);
        $this->values = array();
        if($this->db->numRows($this->query_resource)){
            while($row = $this->db->fetch_array($this->query_resource, 1)){
                $this->values[] = $row;
            }
        }
        if(!empty($this->values)){
            return $this->values;
        }
    }
    public function update($data,$con){
        $this->_retrieve_cols($data);
        if(is_string($con)){
            $this->con = (array)$con;
        }elseif(is_array($con)){
            foreach($con as $c=>$v){
                $this->con[]=sprintf("`%s`='%s'",$c,$v);
}
        }
        $sql = $this->_mk_update_sql();
        $this->_query($sql);
    }
    public function insert($data){
        $this->_retrieve_cols($data);
        $sql = $this->_mk_insert_sql();
        $this->_query($sql);
    }
    //由主鍵刪除
    public function delete($pk){
        $this->deleteByCon(sprintf("`%s`='%d'",$this->pk,$pk),1);
}
    //由指定條件刪除
    public function deleteByCon($con,$limit=null){
        $sql = $this->_mk_delete_sql($con,$limit);
        if($sql){
            $this->_query($sql);
        }
    }

    function get_insert_id(){
        return $this->db->get_insert_id();
    }
    
    function get_max_sort_value(){
        $sql = "select max(".$this->sort_col.") from ".$this->tablename();
        $this->_query($sql);
        list($sort) = $this->db->fetch_array($this->query_resource,0);
        return $sort+1;
    }
    //給後台批次刪除使用
    function del($id){
        $this->delete($id);
    }
    //後台變更狀態
    function status($pk,$value){
        if($this->status_col){
            $data[$this->status_col]=$value;
            $con[$this->pk]=$pk;
            $this->update($data,$con);
        }
    }
    //後台排序
    function sort($pk,$values){
        if($this->sort_col){
            $data[$this->sort_col]=$values[$pk];
            $con[$this->pk]=$pk;
            $this->update($data,$con);
        }
    }       
    //後台複製
    function copy($pk){
        global $main;
        $origin_row = $this->getData($pk)->getDataRow();
        unset($origin_row[$this->pk]);
        $keys = array_keys($origin_row);
        $unique = preg_grep("/seo_filename$/i", $keys);
        if($unique){
            foreach($unique as $u){
                unset($origin_row[$u]);
            }
        }
        $sort = preg_grep("/sort$/i", $keys);
        if(isset($origin_row[$this->sort_col])){
            $origin_row[$this->sort_col] = $this->get_max_sort_value();
        }
        $this->writeData($origin_row);
    }    
}

?>

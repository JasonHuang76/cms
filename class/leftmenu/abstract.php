<?php
abstract class Leftmenu_Abstract {
    public $tpl;
    public $currentRow;
    protected $menuItems;
    protected $subul1 = array(
        "CATE"    => "<ul class=\"menu_body\">",
        "SUBCATE" => "<ul id=\"\" class=\"menu_prod_body\">",
    );
    protected $subul2 = array(
        "CATE"    => "</ul>",
        "SUBCATE" => "</ul>",
    );    
    
    function __construct(TemplatePower $tpl) {
        $this->tpl = $tpl;
        $this->menuItems = $this->_getItems();
        $this->_init();
    }
    
    protected function _getItems(){

    }
    
    protected function _init(){

    }
    
    //增加左側主選單
    protected function _make_menu(array $menu_items,$blockname="CATE",$sub=false,$deep=""){
        $deep = $deep?"S".$deep:"SUB";
        $sub_cate_name = $deep."CATE";        
        foreach($menu_items as $itme){
            $this->tpl->newBlock( "LEFT_".$blockname."_LIST" );
            $this->tpl->assign( array( 
                "VALUE_".$blockname."_NAME" => $itme['name'],
                "VALUE_".$blockname."_LINK" => $itme['link'],
                "WRAPPER_CLASS"             => $itme['class'],
                "TAG_CURRENT_CLASS"         => $itme['tag_cur'],
            ));        
            if($itme['sub']){
                $this->tpl->assign("TAG_".$deep."_UL1",$this->subul1[$blockname]);
                $this->tpl->assign("TAG_".$deep."_UL2",$this->subul2[$blockname]);
                $this->_make_menu($itme['sub'],$sub_cate_name,true,$deep);
            }
        }
    }
    
    public function make(){
        $this->_make_menu($this->menuItems);
    }
}

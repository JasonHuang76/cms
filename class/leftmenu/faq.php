<?php
class Leftmenu_Faq extends Leftmenu_Contactus {
    //put your code here
    protected function _init(){
        $this->menuItems[1]['tag_cur'] = "current";
    }    
}

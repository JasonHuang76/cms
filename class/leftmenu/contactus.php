<?php
class Leftmenu_Contactus extends Leftmenu_Abstract {
    protected function _getItems(){
        global $cms_cfg,$ws_array;
        return array(
            array(
                'link' => $cms_cfg['base_root'].'contactus.htm',
                'name' => $ws_array['main']['contactus'],
            ),
            array(
                'link' => $cms_cfg['base_root'].'faq.htm',
                'name' => $ws_array['main']['faq'],
            ),
        );
    }
    
    protected function _init(){
        $this->menuItems[0]['tag_cur'] = "current";
    }
}

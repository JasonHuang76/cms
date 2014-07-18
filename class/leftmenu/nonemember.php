<?php
class Leftmenu_Nonemember extends Leftmenu_Abstract {
    protected function _getItems(){
        global $TPLMSG;
        return array(
            array(
                'link' => App::configs()->base_root."member.php?func=m_add",
                'name' => $TPLMSG['MEMBER_JOIN'],
            ),
            array(
                'link' => App::configs()->base_root."member.php?func=m_forget",
                'name' => $TPLMSG["FORGOT_PASSWORD"],
            ),
        );
    }
    protected function _init() {
        switch($_GET['func']){
            case "m_add":
                $this->menuItems[0]['tag_cur']="current";
                break;
            case "m_forget":
                $this->menuItems[1]['tag_cur']="current";
                break;
        }
    }
}

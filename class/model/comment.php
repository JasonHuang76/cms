<?php
require_once("libs/libs-security-image.php");
class Model_Comment {
    public $tpl;
    public $url;
    function __construct($tpl,$url) {
        $this->tpl=$tpl;
        $this->url=$url;
    }
    function load_form(){
        global $TPLMSG;
        $this->tpl->newBlock("COMMENT_FORM");
        $this->tpl->assign("MSG_ROLE",sprintf($TPLMSG['COMMENT_ROLE'], App::getHelper('session')->MEMBER_ID? App::getHelper('session')->MEMBER_NAME : $TPLMSG['COMMENT_GUEST'] ));
        $this->tpl->assign("MSG_SUBJECT",$TPLMSG['SUBJECT']);
        $this->tpl->assign("MSG_CONTENT",$TPLMSG['CONTENT']);
        $this->tpl->assign("MSG_VALID_TAGS",$TPLMSG['COMMENT_VALID_TAGS']);
        $this->tpl->assign("MSG_UPLOAD_DESC",$TPLMSG['COMMENT_UPLOAD_DESCRIPTION']);
        $this->tpl->assign("MSG_ATTACH",$TPLMSG['ATTACH']);
        $this->tpl->assign("MSG_MAX_UPLOAD_SIZE",sprintf($TPLMSG['MAX_UPLOAD_SIZE'],get_cfg_var('upload_max_filesize')));
        $this->tpl->assign("URL",$this->url);
        $this->tpl->assign("M_ID",App::getHelper('session')->MEMBER_ID? App::getHelper('session')->MEMBER_ID : 0 );
        $this->tpl->assign("MSG_FORM_VALID",$TPLMSG['COMMENT_FORM_VALID_MSG']);
        $this->tpl->assign("MSG_LIST_TITLE",$TPLMSG['COMMENT_LIST_TITLE']);
        $this->tpl->assign("MSG_LIST_DATE",$TPLMSG['COMMENT_LIST_PUBLISH_DATE']);
       
        $this->_security_zone();
        $comments = $this->get_comments();
        if($comments){
            foreach($comments as $row){
                $this->tpl->newblock("COMMENT_LIST");
                foreach($row as $k => $v){
                    if($k=='m_id'){
                        $member = App::getHelper('dbtable')->member->getData($v)->getDataRow();
                        if($member){
                            switch(App::configs()->ws_module->ws_contactus_s_style){
                                case "2":
                                    $role_name = $member['m_lname']."&nbsp;".$member['m_fname'];
                                    break;
                                case "1":
                                default:
                                    $role_name = $member['m_fname']."&nbsp;".$member['m_lname'];
                            }
                            $this->tpl->assign('ROLE_NAME',sprintf($TPLMSG['COMMENT_ROLE_WRAPPER2'],$role_name));
                        }else{
                            $this->tpl->assign('ROLE_NAME',sprintf($TPLMSG['COMMENT_ROLE_WRAPPER1'],$TPLMSG['COMMENT_GUEST']));
                        }
                    }elseif($k=='admin' && !empty($v)){
                        $this->tpl->assign('ROLE_NAME',sprintf($TPLMSG['COMMENT_ROLE_WRAPPER3'],$TPLMSG['COMMENT_ADMIN']));
                    }
                    $this->tpl->assign(strtoupper($k),$v);
                }
                $attachList = App::getHelper('dbtable')->comment_attach->getDataList("comment_id='".$row['id']."'",'*','id');
                if($attachList){
                    $this->tpl->newBlock("COMMENT_ATTACH");
                    foreach($attachList as $attach){
                        $this->tpl->newBlock("ATTACH_LIST");
                        $file = App::configs()->file_root.$attach['file'];
                        $dimension = App::getHelper('main')->resizeto($file,80,80);
                        $this->tpl->assign(array(
                            'IMAGE_FILE' => $file,
                            'IMAGE_FILE_W' => $dimension['width'],
                            'IMAGE_FILE_H' => $dimension['height'],
                        ));
                    }
                }
            }
        }
    }
    protected function _security_zone(){
        global $TPLMSG;
        $si = new securityImage();
        $si->setImageSize(90, 25);
        $this->tpl->assignGlobal( "MSG_LOGIN_SECURITY",$TPLMSG["LOGIN_SECURITY"]);
        $this->tpl->assignGlobal( "TAG_INPUT_SECURITY",$si->showFormInput());
        $this->tpl->assignGlobal( "TAG_IMAGE_SECURITY_IMAGE",$si->showFormImage());        
    }
    //取得comments
    function get_comments(){
        return App::getHelper('dbtable')->comment->getDataList("url='".$this->url."' and del='0'",'*','createdate');
    }
    
    static function isValid(){
        $si = new securityImage();
        return $si->isValid()?1:0;
    }
}

<?php
include_once("libs/libs-sysconfig.php");
$guestbook = new GUESTBOOK;
class GUESTBOOK{
    function GUESTBOOK(){
        global $db,$cms_cfg,$tpl;
        switch($_REQUEST["func"]){
            case "gb_list"://�d�����C��
                $this->ws_tpl_file = "templates/ws-guestbook-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_list();
                $this->ws_tpl_type=1;
                break;
            case "gb_add"://�d�����s�W
                $this->ws_tpl_file = "templates/ws-guestbook-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_form("add");
                $this->ws_tpl_type=1;
                break;
            case "gbr_add"://�d�����^�зs�W
                $this->ws_tpl_file = "templates/ws-guestbook-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_form("reply");
                $this->ws_tpl_type=1;
                break;
            case "gb_replace"://�d������s���(replace)
                $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_replace();
                $this->ws_tpl_type=1;
                break;
            default:    //�d�����C��
                $this->ws_tpl_file = "templates/ws-guestbook-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_list();
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $tpl->printToScreen();
        }
    }
    //���J�������˪O
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //�Y��title,meta,js,css
        //$tpl->assignInclude( "TOP_MENU", $cms_cfg['base_top_menu_tpl']);// �\��C��
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //����@����
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //�D�\����ܰ�
        //$tpl->assignInclude( "FOOTER", $cms_cfg['base_footer_tpl']); //���ɥ\��C��
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["GUESTBOOK"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["GUESTBOOK"]);
        $main->header_footer("");
        $main->google_code(); //google analystics code , google sitemap code
        $main->login_zone();
    }

//�d����--�C��================================================================
    function guestbook_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //�d�����C��
        $sql="select * from ".$cms_cfg['tb_prefix']."_guestbook  where gb_reply_type=0 order by gb_modifydate desc";
        //���o�`����
        $selectrs = $db->query($sql);
        $total_records = $db->numRows($selectrs);
        //���o�����s��
        $func_str="guestbook.php?func=gb_list";
        $page=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        //���s�զX�]�tlimit��sql�y�k
        $sql=$main->sqlstr_add_limit($cms_cfg["op_limit"],$_REQUEST["nowp"],$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "GUESTBOOK_LIST" );
            $tpl->assign( array("VALUE_GB_ID"  => $row["gb_id"],
                                "VALUE_GB_NAME" => $row["gb_name"],
                                "VALUE_GB_SEX" => ($row["gb_sex"]==0)?"w":"m",
                                "VALUE_GB_EMAIL" => $row["gb_email"],
                                "VALUE_GB_TEXTCOLOR" => $row["gb_textcolor"],
                                "VALUE_GB_IMG" => $row["gb_img"],
                                "VALUE_GB_HIDDEN" => $row["gb_hidden"],
                                "VALUE_GB_URL" => $row["gb_url"],
                                "VALUE_GB_CONTENT" => ($row["gb_hidden"])?$TPLMSG["GB_HIDDEN"]:$row["gb_content"],
                                "VALUE_GB_MODIFYDATE" => $row["gb_modifydate"],
                                "VALUE_GB_SERIAL" => $i,
            ));
            if(trim($row["gb_email"])!=""){
                $tpl->newBlock( "GUESTBOOK_EMAIL" );
                $tpl->gotoBlock( "GUESTBOOK_LIST" );
            }
            if(trim($row["gb_url"])!=""){
                $tpl->newBlock( "GUESTBOOK_URL" );
                $tpl->gotoBlock( "GUESTBOOK_LIST" );
            }
            $sql2="select * from ".$cms_cfg['tb_prefix']."_guestbook  where gb_reply_type !=0 and gb_parent='".$row["gb_id"]."' order by gb_id";
            $selectrs2 = $db->query($sql2);
            while ( $row2 = $db->fetch_array($selectrs2,1) ) {
                $tpl->newBlock( "GUESTBOOK_REPLY_LIST" );
                if($row2["gb_reply_type"]==1){
                    $gb_reply_type=$TPLMSG["GB_ADMIN"];
                    $color="red";
                }else{
                    $gb_reply_type=$TPLMSG["GB_GUEST"];
                    $color="blue";
                }
                $tpl->assign( array("VALUE_GB_R_NAME" => $row2["gb_name"],
                                    "VALUE_GB_R_SEX" => $row2["gb_sex"],
                                    "VALUE_GB_R_EMAIL" => $row2["gb_email"],
                                    "VALUE_GB_R_TEXTCOLOR" => $row2["gb_textcolor"],
                                    "VALUE_GB_R_IMG" => $row2["gb_img"],
                                    "VALUE_GB_R_URL" => $row2["gb_url"],
                                    "VALUE_GB_R_CONTENT" => ($row2["gb_hidden"])?$TPLMSG["GB_HIDDEN"]:$row2["gb_content"],
                                    "VALUE_GB_R_MODIFYDATE" => $row2["gb_modifydate"],
                                    "VALUE_GB_R_REPLY_TYPE" => $gb_reply_type,
                                    "VALUE_COLOR" => $color,
                ));
            }
            $tpl->gotoBlock( "GUESTBOOK_LIST" );
        }
        if($i==0){
            $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
            $tpl->assignGlobal( array("VALUE_TOTAL_RECORDS"  => 0,
                                      "VALUE_TOTAL_PAGES"  => 0,
                ));
        }else{
            $tpl->newBlock( "PAGE_DATA_SHOW" );
            $tpl->assignGlobal( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                      "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                      "VALUE_PAGES_STR"  => $page["pages_str"],
                                      "VALUE_PAGES_LIMIT"=>$cms_cfg["op_limit"]
            ));
        }
    }

//�d����--��Ƨ�s================================================================
    function guestbook_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if(!ereg($cms_cfg['base_url'],$_SERVER['HTTP_REFERER'])){
            exit;
        }
        if($cms_cfg["ws_module"]["ws_security"]==1){
            require_once("libs/libs-security-image.php");
            $si = new securityImage();
            $pass=(isset($_POST['callback']) && $si->isValid())?1:0;
        }else{
            $pass=1;
        }
        if($pass){
            $_REQUEST["gb_content"]=strip_tags($_REQUEST["gb_content"]);
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_guestbook (
                    gb_parent,
                    gb_name,
                    gb_sex,
                    gb_textcolor,
                    gb_img,
                    gb_email,
                    gb_reply_type,
                    gb_hidden,
                    gb_content,
                    gb_modifydate,
                    gb_url,
                    gb_ip
                ) values (
                    '".$_REQUEST["gb_parent"]."',
                    '".$_REQUEST["gb_name"]."',
                    '".$_REQUEST["gb_sex"]."',
                    '".$_REQUEST["gb_textcolor"]."',
                    '".$_REQUEST["gb_img"]."',
                    '".$_REQUEST["gb_email"]."',
                    '".$_REQUEST["gb_reply_type"]."',
                    '".$_REQUEST["gb_hidden"]."',
                    '".$_REQUEST["gb_content"]."',
                    '".date("Y-m-d H:i:s")."',
                    '".$_REQUEST["gb_url"]."',
                    '".$_REQUEST["gb_ip"]."'
                )";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                unset($_SESSION["guestbook"]);
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["base_url"]."guestbook.php?func=gb_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }else{
            foreach($_REQUEST as $key => $value){
                if(eregi("gb_",$key)){
                    $_SESSION["guestbook"]["$key"]=$value;
                }
            }
            $_SESSION["guestbook"]["security_error"]=1;
            header("location:guestbook.php?func=gb_add");
        }
    }

//�d�����^��--���================================================================
    function guestbook_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->security_zone();
        $tpl -> assignGlobal(array( "VALUE_GB_NAME" => $_SESSION["guestbook"]["gb_name"],
                                    "VALUE_GB_URL" => $_SESSION["guestbook"]["gb_url"],
                                    "VALUE_GB_CONTENT" => $_SESSION["guestbook"]["gb_content"],
                                    "VALUE_GB_EMAIL" => $_SESSION["guestbook"]["gb_email"],
        ));
        //���W��
        $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['NAME'],
                                  "MSG_SUBJECT"  => $TPLMSG['SUBJECT'],
                                  "MSG_STATUS" => $TPLMSG['STATUS'],
                                  "MSG_MODE" => $TPLMSG['ADD'],
                                  "STR_GB_STATUS_CK1" => "",
                                  "STR_GB_STATUS_CK0" => "checked",
                                  "VALUE_ACTION_MODE" => $action_mode,
                                  "VALUE_GB_REPLY_TYPE" => 0,
                                  "VALUE_GB_PARENT" => 0,
                                  "REMOTE_ADDR" =>  $_SERVER["REMOTE_ADDR"]
        ));
        //�a�J�n�^�Ъ��d�������
        if(!empty($_REQUEST["gb_id"]) && $action_mode=="reply"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_guestbook where gb_id='".$_REQUEST["gb_id"]."' or gb_parent='".$_REQUEST["gb_id"]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                while($row = $db->fetch_array($selectrs,1)){
                    $tpl->assignGlobal( array("VALUE_GB_ID"  => $row["gb_id"],
                                              "VALUE_GB_NAME" => $row["gb_name"],
                                              "VALUE_GB_SEX" => $row["gb_sex"],
                                              "VALUE_GB_EMAIL" => $row["gb_email"],
                                              "VALUE_GB_TEXTCOLOR" => $row["gb_textcolor"],
                                              "VALUE_GB_IMG" => $row["gb_img"],
                                              "VALUE_GB_HIDDEN" => $row["gb_hidden"],
                                              "VALUE_GB_URL" => $row["gb_url"],
                                              "VALUE_GB_CONTENT" => $row["gb_content"],
                                              "MSG_MODE" => $TPLMSG['MODIFY']
                    ));
                }
            }else{
                header("location : guestbook.php?func=gb_list");
            }
            $tpl->assignGlobal( array("VALUE_GB_REPLY_TYPE" => 2,
                                      "VALUE_GB_PARENT" => $_REQUEST["gb_id"],
            ));
        }
        //�ҥ�����X��ܿ��~�T��
        if($cms_cfg["ws_module"]["ws_security"]==1 && $_SESSION["guestbook"]["security_error"]==1){
            $tpl->assignGlobal("MSG_ERROR_MESSAGE",$TPLMSG['SECURITY_ERROR']);
        }
    }
    //��ܰT���í��s�ɦV
    function goto_target_page($url,$sec=2){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }

}
//ob_end_flush();
?>

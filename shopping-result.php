<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$cart = new CART;
class CART{
    function CART(){
        global $db,$cms_cfg,$tpl,$main;
        $this->m_id =$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        $this->ws_tpl_file = $cms_cfg['base_templates']."ws-cart-result-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $tpl->newBlock("JS_MAIN");
        $tpl->assignGlobal("CURRENT_STEP3","class='current'");
        $this->cart_result();
        $this->ws_tpl_type=1;
        if($this->ws_tpl_type){
            $main->layer_link();
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        if($this->m_id){
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_member_tpl']); //左方首頁表單
        }else{
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方首頁表單
        }
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", $cms_cfg['base_templates']."ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", $cms_cfg['base_templates']."ws-fn-ad-v-tpl.html"); //直式廣告模板
        $tpl->assignInclude( "CONTACT_S", $cms_cfg['base_templates']."ws-fn-contact-s-style".$this->contact_s_style."-tpl.html"); //稱呼樣版      
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_PRODUCTS_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["products"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-products"); //主要顯示區域的css設定
        $main->layer_link($TPLMSG["SHOPPING_RESULT"]);
        $main->header_footer("");
        $main->google_code(); //google analystics code , google sitemap code
//        $main->left_fix_cate_list();
    }
    function cart_result(){
        global $tpl,$cms_cfg,$db,$TPLMSG;
        $sessHandler = App::getHelper('session');
        if(isset($sessHandler['paymentType'])){
            $oid = $_GET['pno']?$_GET['pno']:$_POST["MerchantTradeNo"];
            $order = App::getHelper('dbtable')->order->getData($oid)->getDataRow();
            if($order){
                $tpl->newBlock("SHOPPPING_RESULT");
                $tpl->assign(array( 
                    "ORDER_ID" => $order['o_id'],
                    "ORDER_PRICE" => $order['o_total_price'],
                    "ORDER_LINK" => (empty($cms_cfg['ws_module']['ws_shopping_cart_module']))?$cms_cfg['base_root']."member.php?func=m_zone&mzt=order&type=detail&o_id=".$order['o_id']:$cms_cfg['base_root']."cart/?func=c_order_detial&o_id=".$order['o_id'],
                ));         
            }else{
                header("location:".$cms_cfg['base_root']);
                die();
            }
            switch($sessHandler['paymentType']){
                case 1:
                case 2:
                    if($_GET['status']=='OK'){
                        $mail=true;
                        $tpl->assignGlobal("MSG_ORDER_STATUS",$TPLMSG['ORDER_SUCCESS']);
                        App::getHelper('main')->header_footer("",$TPLMSG['ORDER_SUCCESS']);
                    }else{
                        $tpl->assignGlobal("MSG_ORDER_FAIL_DESC",$TPLMSG['ORDER_FAILED'] );
                        App::getHelper('main')->header_footer("",$TPLMSG['ORDER_FAILED']);
                    }
                    break;
                case 3:
                    if($_POST['final_result']){ //授權成功
                        $mail=true;
                    }else{  //授權失敗
                        $tpl->assignGlobal("MSG_ORDER_FAIL_DESC",$TPLMSG['ORDER_FAILED'] );
                        $tpl->assignGlobal("MSG_ORDER_FAIL_DESC",$TPLMSG['AUTHOZIED_FAILED_EXTEND_MSG']);
                        App::getHelper('main')->header_footer("",$TPLMSG['ORDER_FAILED']);
                    }
                    break;
                case "Credit": 
                    if($_POST['RtnCode']==1){
                        $mail=true;
                        $tpl->assignGlobal("MSG_ORDER_STATUS",$TPLMSG['ORDER_SUCCESS']);
                        App::getHelper('main')->header_footer("",$TPLMSG['ORDER_SUCCESS']);
                    }else{
                        $tpl->assignGlobal("MSG_ORDER_FAIL_DESC",$TPLMSG['ORDER_FAILED'] );
                        $tpl->assignGlobal("MSG_ORDER_FAIL_DESC",$TPLMSG['AUTHOZIED_FAILED_EXTEND_MSG']);
                        App::getHelper('main')->header_footer("",$TPLMSG['ORDER_FAILED']);
                    }
                    break;
                case "WebATM":
                    break;
                case "ATM":   
                    break;
                case "CVS":    
                    break;
                case "BARCODE":    
                    break;
                case "Alipay":   
                    break;
                case "Tenpay":    
                    break;                    
            }
            //寄發訂單通知信
            if($mail){
                //$mail_header = ($sessHandler->paymentType == 3)? 1 : 0;
                if($sessHandler['mailContent']){
                    $mail_content = $sessHandler->mailContent;
                    //ws_mail_send($from,$to,$mail_content,$mail_subject,$mail_type,$goto_url,$admin_subject=null,$none_header=0)
                    App::getHelper('main')->ws_mail_send($sessHandler['sc_email'],$order["o_email"],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"],"order","","",1);
//                    App::getHelper('main')->ws_mail_send_simple($sessHandler['sc_email'],$order["o_email"],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"]);
//                    App::getHelper('main')->ws_mail_send_simple($order["o_email"],$sessHandler['sc_email'],$mail_content,$TPLMSG["ORDER_MAIL_TITLE"]);
                }
            }
            unset($sessHandler['mailContent']);
        }
    }
}
?>
<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$ad = new SHIPPRICE;
class SHIPPRICE{
    function SHIPPRICE(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="SPL";
        $this->ws_tpl_file = "templates/ws-manage-shipprice-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $tpl->newBlock("JS_MAIN");
        $tpl->newBlock("JS_JQ_UI");
        $tpl->newBlock("JS_APPEND_GRID");
        $this->appendgrid_table();
        $tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$main;
        $tpl = new TemplatePower( $cms_cfg['manage_all_tpl'] );
        $tpl->assignInclude( "LEFT", $cms_cfg['manage_left_tpl']);
        $tpl->assignInclude( "TOP_MENU", $cms_cfg['manage_top_menu_tpl']);
        $tpl->assignInclude( "MAIN", $ws_tpl_file);
        $tpl->prepare();
        $tpl->assignGlobal("CSS_BLOCK_ORDER","style=\"display:block\"");
        $tpl->assignGlobal("TAG_".$this->current_class."_CURRENT","class='current'");
         //依權限顯示項目
        $main->mamage_authority();
    }

    function appendgrid_table(){
        global $tpl;
        if(App::getHelper('request')->isPost() && $_GET['save']){
            foreach($_POST as $k => $v){
                $nk = explode("_",$k);
                if($nk[2]){ //避免rowOrder也被當資料寫入
                    $new_data_array[$nk[2]][$nk[1]] = $v;
                }
                if($nk[1]=='id' && $v!=0){
                    $update_id[] = $v;
                }
            }
            //刪除不在$update_id裡的記錄
            if(!empty($update_id)){
                App::getHelper('dbtable')->shipprice->deleteByCon(" id not in(".implode(',',$update_id).") ");
            }
            $n_sort=1;
            if(!empty($new_data_array)){
                foreach($new_data_array as $record){
                    $record['sort']=$n_sort;
                    App::getHelper('dbtable')->shipprice->writeData($record);
                    $n_sort++;
                }
            }
            header("location:".$_SERVER['PHP_SELF']);
            die();
        }
        $shipPriceData = App::getHelper('dbtable')->shipprice->getDataList("","pricefloor,priceceil,shipprice,id"," sort ");
        $tpl->assignGlobal("TAG_DATA_JSON",  json_encode($shipPriceData));
    }

}
//ob_end_flush();
?>

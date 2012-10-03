<?php
$cms_cfg['debug'] = false;
/*
server name
*/
$cms_cfg['server_name']	= "ipsdemo";
$cms_cfg['index_page'] = "index.html";
$cms_cfg["manage_page"] = "index.php";

$cms_cfg['base_root']	= "/";
$cms_cfg['base_url']	= "http://".$cms_cfg['server_name'].$cms_cfg['base_root'];
$cms_cfg['base_css']	= $cms_cfg['base_root']."css/";
$cms_cfg['base_images']	= $cms_cfg['base_root']."images/";
$cms_cfg['base_templates']	= "templates/";

//共用檔案路徑
$cms_cfg['file_root']="/";
$cms_cfg['file_url']	= "http://".$cms_cfg['server_name'].$cms_cfg['file_root'];

/*
default header,footer,left templates/
*/
$cms_cfg['base_all_tpl']	= $cms_cfg['base_templates']."ws-fn-all-tpl.html";
$cms_cfg['base_header_tpl']	= $cms_cfg['base_templates']."ws-fn-header-tpl.html";
$cms_cfg['base_left_normal_tpl']	= $cms_cfg['base_templates']."ws-fn-left-normal-tpl.html";
$cms_cfg['base_left_member_tpl']	= $cms_cfg['base_templates']."ws-fn-left-member-tpl.html";
$cms_cfg['base_left_login_tpl']	= $cms_cfg['base_templates']."ws-fn-left-login-tpl.html";
/*
http://www.your-site.com/manage/
*/
$cms_cfg['manage_root']	= "/cmsadmin/";
$cms_cfg['manage_url']	= "http://".$_SERVER["SERVER_NAME"].$cms_cfg['manage_root'];
$cms_cfg['manage_css']	= $cms_cfg['manage_root']."css/";
$cms_cfg['manage_images']	= $cms_cfg['manage_root']."images/main/";
$cms_cfg['manage_templates']	= "templates/";
/*
default manage header,footer templates/
*/
//$cms_cfg['manage_header_tpl']	= $cms_cfg['manage_templates']."ws-manage-header-tpl.html";
//$cms_cfg['manage_footer_tpl']	= $cms_cfg['manage_templates']."ws-manage-footer-tpl.html";
$cms_cfg['manage_all_tpl']	= $cms_cfg['manage_templates']."ws-manage-fn-all-tpl.html";
$cms_cfg['manage_left_tpl']	= $cms_cfg['manage_templates']."ws-manage-fn-left-tpl.html";
$cms_cfg['manage_top_menu_tpl']	= $cms_cfg['manage_templates']."ws-manage-fn-top-menu-tpl.html";
/*
default image
*/
$cms_cfg['default_theme']	= $cms_cfg['base_images'];
$cms_cfg['default_preview_pic']	= $cms_cfg['default_theme']."ws-no-image.jpg";
$cms_cfg['default_text_pic'] = $cms_cfg['manage_images']."ws-text-file.gif";
$cms_cfg['default_img_pic'] = $cms_cfg['manage_images']."ws-img-file.gif";
$cms_cfg['default_status_on'] = "icon-on2.gif";
$cms_cfg['default_status_off'] = "icon-stop.gif";
$cms_cfg['default_key'] = "icon-key.gif";
$cms_cfg['default_lock'] = "icon-lock.gif";

require_once dirname(__FILE__)."database.php";

//default language
$cms_cfg['language'] = "eng";
$cms_cfg['tb_prefix'] = "eng";

//Products page limit
$cms_cfg['op_limit']=12;  //一頁筆數限制
$cms_cfg['jp_limit']=10;  //跳頁筆數限制
$cms_cfg["ws_products_row"]=3;
$cms_cfg['big_img_limit']=4;  //大圖筆數 /*資料表欄位有8個欄位 預設開4張圖即可 */
$cms_cfg['big_img_width'][1]="340";   //大圖圖框限制--寬
$cms_cfg['big_img_height'][1]="225";  //大圖圖框限制--高
$cms_cfg['big_img_width'][2]="620";   //大圖圖框限制--寬
$cms_cfg['big_img_height'][2]="400";  //大圖圖框限制--高
$cms_cfg['single_img_width']="340"; //單張大圖圖框限制--寬
$cms_cfg['single_img_height']="225";//單張大圖圖框限制--高
$cms_cfg['small_img_width']="167"; //小圖圖框限制--寬
$cms_cfg['small_img_height']="111";//小圖圖框限制--高


//News page limit
$cms_cfg['newsop_limit']=12;  //最新消息一頁筆數限制

//FAQ page limit
$cms_cfg['faqsop_limit']=8;  //FAQ一頁筆數限制

//Download page limit
$cms_cfg['dlop_limit']=12;  //Download一頁筆數限制

// AD limit
$cms_cfg['ad_up_banner_limit']=1;
$cms_cfg['ad_left_button_limit']=2;

// front security image
$cms_cfg['security_image_width'] = "90";
$cms_cfg['security_image_height'] = "25";

// related categories & products limit
$cms_cfg['related_limit']=15;

$cms_cfg['date_format'] = 'Y-m-d H:i:s';
$cms_cfg['encryption_key'] = "";
$cms_cfg['sess_cookie_name']		= 'ipsdemoeng';
//$cms_cfg['sess_expiration']		= 14400;

//Dump DB
$cms_cfg['mysql_dump'] = "/MySQL/bin/";   //mysqldump執行檔路徑
$cms_cfg['sql_dir'] = "/sql_dump/";    //SQL備份檔存放路徑

$cms_cfg['sort_pos'] = "asc";    //sort欄位的排序方法
$cms_cfg['path_separator'] = " > ";    //麵包屑(網站路徑的分隔符號)

include_once("config.auth.php");

?>
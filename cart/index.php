<?php
	### 請先查看  config.php 後再使用 ###
	
	include_once ("../libs/libs-sysconfig.php");
	$cart = new CART;
	
	class CART {
		function __construct() {
			global $cms_cfg, $tpl, $TPLMSG;
			include_once (dirname(__FILE__) . "/config.php");
			$session = App::getHelper('session');
			$this -> m_id = $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
			$this -> ws_seo = ($cms_cfg["ws_module"]["ws_seo"]) ? 1 : 0;
			$this -> order = ($cms_cfg["ws_module"]["ws_select_order"] == 1) ? "desc" : "asc";
	
			switch($_REQUEST["func"]) {
				case "c_add" :
					$this -> ws_tpl_file = "templates/ws-cart-tpl.html";
					$this -> ws_load_tp($this -> ws_tpl_file);
					$this -> cart_add();
					$this -> ws_tpl_type = 1;
					break;
				case "c_finish" :
					$this -> ws_tpl_file = "templates/ws-cart-finish-tpl.html";
					$this -> ws_load_tp($this -> ws_tpl_file);
					$tpl -> newBlock("JS_FORMVALID");
					App::getHelper('main') -> jQuery_init("date", "zone", "get");
					$this -> cart_finish();
					$this -> ws_tpl_type = 1;
					break;
				case "c_preview" :
					$this -> ws_tpl_file = "templates/ws-cart-preview-tpl.html";
					$this -> ws_load_tp($this -> ws_tpl_file);
					$this -> cart_preview();
					$this -> ws_tpl_type = 1;
					break;
				case "c_replace" :
					$this -> cart_replace();
					break;
				case "c_ajax" :
					$form = $this -> ajax_form();
	
					switch($form["ajax_act"]) {
						case "c_mod" :
							$this -> cart_mod($form);
							break;
						case "c_del" :
							$this -> cart_del($form);
							break;
					}
					echo $form["ajax_top"];
					break;
				case "c_order" :
                                        if(!$this->m_id){
                                            $this -> ws_tpl_file = "../templates/ws-login-form-tpl.html";
                                            $this -> ws_load_tp($this -> ws_tpl_file);
                                            $this -> member_login();
                                        }else{
                                            $this -> ws_tpl_file = "templates/ws-cart-order-tpl.html";
                                            $this -> ws_load_tp($this -> ws_tpl_file, 1);
                                            $this -> cart_order();
                                        }
					$this -> ws_tpl_type = 1;
					break;
				case "c_order_detial" :
                                        if(!$this->m_id){
                                            $this -> ws_tpl_file = "../templates/ws-login-form-tpl.html";
                                            $this -> ws_load_tp($this -> ws_tpl_file);
                                            $this -> member_login();                                            
                                        }else{
                                            $this -> ws_tpl_file = "templates/ws-cart-order-tpl.html";
                                            $this -> ws_load_tp($this -> ws_tpl_file, 1);
                                            $this -> cart_order_detail();
                                        }
					$this -> ws_tpl_type = 1;
					break;
				case "c_check" :
					$this -> cart_check();
					break;
				/*
				 case "c_clear":
				 session_unset();
				 break;
				 */
				default :
					$this -> ws_tpl_file = "templates/ws-cart-tpl.html";
					$this -> ws_load_tp($this -> ws_tpl_file);
					$this -> cart_list();
					$this -> ws_tpl_type = 1;
					break;
			}
	
			if ($this -> ws_tpl_type) {
				App::getHelper('main') -> layer_link();
				$tpl -> printToScreen();
			}
		}
	
		function ws_load_tp($ws_tpl_file, $member_left = 0) {
			global $tpl, $cms_cfg, $ws_array, $db, $TPLMSG, $main;
			$tpl = new TemplatePower('../' . $cms_cfg['base_all_tpl']);
			$tpl -> assignInclude("HEADER", '../' . $cms_cfg['base_header_tpl']);
			//頭檔title,meta,js,css
			if (empty($member_left)) {
				$tpl -> assignInclude("LEFT", '../' . $cms_cfg['base_left_normal_tpl']);
				//左方首頁表單
			} else {
				$tpl -> assignInclude("LEFT", '../' . $cms_cfg['base_left_member_tpl']);
				//左方首頁表單
			}
			$tpl -> assignInclude("TOP", $cms_cfg['base_top_tpl']);
			// 上方選單
			$tpl -> assignInclude("FOOTER", $cms_cfg['base_footer_tpl']);
			// 版權宣告
			$tpl -> assignInclude("MAIN", $ws_tpl_file);
			//主功能顯示區
			$tpl -> prepare();
			$tpl -> assignGlobal("TAG_CATE_TITLE", $ws_array["left"]["products"]);
			//左方menu title
			$tpl -> assignGlobal("TAG_PRODUCTS_CURRENT", "class='current'");
			//上方menu current
			$tpl -> assignGlobal("TAG_MAIN", $ws_array["main"]["products"]);
			//此頁面對應的flash及圖檔名稱
			$tpl -> assignGlobal("TAG_MAIN_CLASS", "main-products");
			//主要顯示區域的css設定
			$main -> layer_link(($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) ? $TPLMSG['CART_SHOPPING'] : $TPLMSG['CART_INQUIRY']);
			$main -> header_footer("");
			$main -> google_code();
			//google analystics code , google sitemap code
			$main -> left_fix_cate_list();
	
			$tpl -> newBlock("JS_MAIN");
			$tpl -> newBlock("JS_POP_IMG");
	
			// 共通使用程式
			//$main->share_function();
		}
	
		## 主要功能 ######################################################################################
	
		// 增加購買產品
		function cart_add() {
			global $cms_cfg;
			$_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'] = $_SERVER['HTTP_REFERER'];
	
			$option = "";
	
			if (is_array($_REQUEST["p_id"])) {
				foreach ($_REQUEST["p_id"] as $key => $id) {
					if (!empty($id)) {
						$sess = $this -> sess_code($id, $option);
	
						$_SESSION[$cms_cfg['sess_cookie_name']]["id"][$sess] = $id;
						$_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess] = (!empty($_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess])) ? $_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess] : $this -> c_num_set;
					}
				}
			} else {
				$sess = $this -> sess_code($_REQUEST["p_id"], $option);
	
				$_SESSION[$cms_cfg['sess_cookie_name']]["id"][$sess] = $_REQUEST["p_id"];
				$_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess] += ($_REQUEST['amount']) ? $_REQUEST['amount'] : $this -> c_num_set;
			}
			if (!App::getHelper('request') -> isAjax()) {
				if (!empty($_SESSION[$cms_cfg['sess_cookie_name']]["id"])) {
					$this -> cart_list();
				} else {
					$this -> error_handle();
				}
			} else {
                                $res['code'] = 1;
                                $res['cart_nums'] = count($_SESSION[$cms_cfg['sess_cookie_name']]["id"]);
                                echo json_encode($res);
				die();
			}
		}
	
		// 清單顯示
		function cart_list() {
			global $cms_cfg, $tpl, $TPLMSG, $ws_array;
			if (empty($_SESSION[$cms_cfg['sess_cookie_name']]["id"])) {//空購物車時，回到前一頁
				App::getHelper('main') -> js_notice($TPLMSG['CART_EMPTY'], $cms_cfg['base_root'] . "products.htm");
				die();
			}
			$tpl -> assignGlobal(array(
                            "MSG_ID" => ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) ? $TPLMSG["ORDER_ID"] : $TPLMSG["INQUIRY_ID"], 
                            "MSG_NAME" => $TPLMSG['MEMBER_NAME'], 
                            "MSG_CONTENT" => $TPLMSG['CONTENT'], 
                            "MSG_MODIFY" => $TPLMSG['MODIFY'], 
                            "MSG_DEL" => $TPLMSG['DEL'], 
                            "MSG_TOTAL" => $TPLMSG['CART_TOTAL'], 
                            "MSG_SHIPPING_PRICE" => $TPLMSG['SHIPPING_PRICE'], 
                            "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'], 
                            "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'], 
                            "MSG_PRODUCT" => $TPLMSG['PRODUCT'], 
                            "MSG_PRODUCT_IMAGE" => $TPLMSG['PRODUCT_IMG'], 
                            "VALUE_MODIFY_AMOUNT" => $TPLMSG['CART_MODIFY_AMOUNT'], 
                            "TAG_DELETE_CHECK_STR" => $TPLMSG['CART_DELETE_CHECK'], 
                            "TAG_MAIN_FUNC" => ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) ? $TPLMSG['CART_SHOPPING'] : $TPLMSG['CART_INQUIRY'], 
                            "TAG_SCROLL_TOP" => $_REQUEST["top"], 
                        ));
	
			if (count($_SESSION[$cms_cfg['sess_cookie_name']]["id"]) > 0) {
				$tpl -> newBlock("TAG_CART_ZONE");
				$tpl -> assign(array("MSG_CONTINUE_SHOPPING" => $TPLMSG['CART_CONTINUE_SHOPPING'], "MSG_FINISH_SHOPPING" => $TPLMSG['CART_FINISH_SHOPPING'], "LINK_CONTINUE" => $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'], ));
	
				foreach ($_SESSION[$cms_cfg['sess_cookie_name']]["id"] as $sess => $ID) {
					$row = $this -> products_detail($ID);
	
					$tpl -> newBlock("TAG_CART_LIST");
					$row["p_num"] = (!is_int($_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess])) ? round($_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess]) : $_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess];
					$row["p_name"] = $row["p_name"];
					$p_link = $this -> p_link_handle($row);
	
					$tpl -> assign(array(
                                            "VALUE_P_ID" => $ID, 
                                            "VALUE_P_NAME" => $row["p_name"], 
                                            "VALUE_P_SMALL_IMG" => (trim($row["p_small_img"]) == "") ? $cms_cfg['default_preview_pic'] : $cms_cfg["file_url"] . $row["p_small_img"], 
                                            "VALUE_P_AMOUNT" => $row["p_num"], 
                                            "VALUE_P_LINK" => $p_link, 
                                            "VALUE_P_SERIAL" => $i + 1, 
                                            "VALUE_P_SESS" => $sess, 
                                        ));
	
					for ($c_num = $this -> c_num_set; $c_num <= $this -> c_num; $c_num++) {
						$tpl -> newBlock("TAG_CART_NUM");
						$tpl -> assign(array(
                                                    "VALUE_CART_NUM" => $c_num, 
                                                    "VALUE_CART_CURRENT" => ($c_num == $row["p_num"]) ? 'selected' : '', 
                                                ));
					}
	
					if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
						$this -> price_counter($row);
					}
					$i++;
					$tpl -> gotoBlock("SHOPPING_CART_ZONE");
				}
                                //購物車時輸出服務條款
                                if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
                                        $this -> service_rule();
                                }
	
				if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"] && ($_REQUEST["func"] == "c_add" || empty($_REQUEST["func"]))) {
					// 顯示付款方式
					$tpl -> newBlock("PAYMENT_TYPE");
					$tpl -> assign("MSG_PAYMENT_TYPE", $TPLMSG["PAYMENT_TYPE"]);
					foreach ($ws_array["payment_type"] as $key => $payment) {
						$tpl -> newBlock("PAYMENT_TYPE_ITEMS");
						$tpl -> assign(array("VALUE_PAYMENT_TYPE" => $key, "VALUE_PAYMENT_TYPE_STR" => $payment, "VALUE_PAYMENT_CURRENT" => ($key == App::getHelper('session') -> o_payment_type) ? 'checked' : '', ));
					}
				}
			} else {
				$this -> error_handle();
			}
		}
	
		// 完成訂單清單
		function cart_finish() {
			global $tpl, $db, $cms_cfg, $TPLMSG, $main, $ws_array;
	
			if (empty($this -> m_id) && $cms_cfg["ws_module"]["ws_cart_login"] == 1 && empty($_REQUEST["first"])) {
				//驗証碼
				$_SESSION[$cms_cfg['sess_cookie_name']]["ERROR_MSG"] = "";
				//清空錯誤訊息
				$tpl -> newBlock("MEMBER_LOGIN_FORM");
				$tpl -> assignGlobal(array("MSG_LOGIN_ACCOUNT" => $TPLMSG["LOGIN_ACCOUNT"], "MSG_LOGIN_PASSWORD" => $TPLMSG["LOGIN_PASSWORD"], "MSG_LOGIN_BTN" => $TPLMSG["LOGIN_BUTTON"], "MSG_FIRST_BTN" => ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) ? $TPLMSG['FIRST_S_BTN'] : $TPLMSG['FIRST_I_BTN'], "MSG_MEMBER_LOGIN" => $TPLMSG["MEMBER_LOGIN"], "TAG_FS_SHOPPING" => $TPLMSG['FIRST_TIME_SHOPPING'], ));
	
				//載入驗証碼
				$main -> security_zone();
			} else {
				$this -> cart_list();
	
				$tpl -> newBlock("MEMBER_DATA_FORM");
				$tpl -> assign($this -> basic_lang);
	
				$this -> member_detail();
				$this -> taiwan_zone_select();
	
				if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
					// 顯示付款方式
					// 檢查是否選擇付款方式
					if (empty($_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"])) {
						$tpl -> assignGlobal("MSG_PAYMENT_ALERT", 'alert("' . $TPLMSG['NO_PAYMENT'] . '"); location.href = "' . $cms_cfg["base_root"] . 'cart/"');
					}
	
					$tpl -> newBlock("PAYMENT_TYPE");
					$tpl -> assign(array("MSG_PAYMENT_TYPE" => $TPLMSG["PAYMENT_TYPE"], "VALUE_PAYMENT_TYPE" => $ws_array["payment_type"][App::getHelper('session') -> o_payment_type], ));
	
					// 購物收件人表單
					$tpl -> newBlock("TAG_ADDRESSEE_BLOCK");
					$tpl -> assign($this -> adv_lang);
	
					// 到貨日期
					$tpl -> assignGlobal(array("VALUE_ARRIVAL_START" => $this -> arrival_start, "VALUE_ARRIVAL_RANGE" => $this -> arrival_range, ));
					//發票類型
					foreach ($ws_array['invoice_type'] as $type_id => $type_label) {
						$tpl -> newBlock("INVOICE_TYPE_LIST");
						$tpl -> assign(array("VALUE_INVOICE_ID" => $type_id, "VALUE_INVOICE_LABEL" => $type_label, ));
					}
				}
	
				if (empty($this -> m_id) && !empty($cms_cfg["ws_module"]["ws_cart_login"])) {
					$tpl -> newBlock("TAG_NEW_MEMBER_REGIST");
					$tpl -> assign(array("MSG_ACCOUNT" => $TPLMSG["LOGIN_ACCOUNT"], "MSG_PASSWORD" => $TPLMSG["LOGIN_PASSWORD"], "MSG_VALID_PASSWORD" => $TPLMSG['MEMBER_CHECK_PASSWORD'], ));
				}
			}
		}
	
		// 預覽訂單
		function cart_preview() {
			global $db, $tpl, $cms_cfg, $TPLMSG, $main, $allpay, $ws_array;
	
			$this -> cart_list();
	
			$tpl -> newBlock("MEMBER_DATA_FORM");
			$tpl -> assign($this -> basic_lang);
			$tpl -> assign(array("VALUE_O_ID" => $this -> o_id, "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
			//"VALUE_M_CONTACT_S" => $this->gender_list($_REQUEST["m_contact_s"],1),
			//"VALUE_M_NAME" => $_REQUEST["m_name"],
			"VALUE_M_NAME" => (empty($this -> gender_select)) ? $this -> gender_list($_REQUEST["m_contact_s"], 1) . '&nbsp;' . $_REQUEST["m_name"] : $_REQUEST["m_name"] . '&nbsp;' . $this -> gender_list($_REQUEST["m_contact_s"], 1), "VALUE_M_ZIP" => $_REQUEST["m_zip"], "VALUE_M_ADDRESS" => $_REQUEST["m_city"].$_REQUEST["m_area"].$_REQUEST["m_address"], "VALUE_M_TEL" => $_REQUEST["m_tel"], "VALUE_M_FAX" => $_REQUEST["m_fax"], "VALUE_M_EMAIL" => $_REQUEST["m_email"], "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"], "VALUE_CONTENT" => $_REQUEST["content"], ));
	
			if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"] == 1) {
				// 顯示付款方式
				$tpl -> newBlock("PAYMENT_TYPE");
				$tpl -> assign(array("MSG_PAYMENT_TYPE" => $TPLMSG["PAYMENT_TYPE"], "VALUE_PAYMENT_TYPE" => $ws_array["payment_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"]], ));
	
				// 收件人資訊
				$tpl -> newBlock("TAG_ADDRESSEE_BLOCK");
				$tpl -> assign($this -> adv_lang);
				$tpl -> assign(array("VALUE_ADD_NAME" => $_REQUEST["o_add_name"], "VALUE_ADD_TEL" => $_REQUEST["o_add_tel"],"VALUE_ADD_CELLPHONE" => $_REQUEST["o_add_cellphone"], "VALUE_ADD_ADDRESS" => $_REQUEST["o_add_address"], "VALUE_ADD_MAIL" => $_REQUEST["o_add_mail"], "VALUE_INVOICE_TYPE" => $ws_array['invoice_type'][$_REQUEST['o_invoice_type']], "VALUE_INVOICE_NAME" => $_REQUEST["o_invoice_name"], "VALUE_INVOICE_VAT" => $_REQUEST["o_invoice_vat"], "VALUE_INVOICE_TEXT" => $_REQUEST["o_invoice_text"], ));
			}
	
			// 到貨時間
			if (is_array($_REQUEST["o_arrival_time"])) {
				$o_arrival_time = implode("-", $_REQUEST["o_arrival_time"]);
				$tpl -> assign("VALUE_ARRIVAL_TIME", $o_arrival_time);
			}
	
			// 國家欄位
			if ($cms_cfg["ws_module"]["ws_country"] == 1) {
				$tpl -> newBlock("MEMBER_DATA_COUNTRY_ZONE");
				$tpl -> assign(array("MSG_COUNTRY" => $TPLMSG['COUNTRY'], "VALUE_M_COUNTRY" => $_REQUEST["m_country"]));
			}
			//輸出post暫存
			foreach ($_POST as $k => $v) {
				if(!is_array($v)){
					$tpl -> newBlock("TMP_POST_FIELD");
					$tpl -> assign(array("TAG_POST_KEY" => $k, "TAG_POST_VALUE" => $v, ));
				}else{
					foreach($v as $split_v){
						$tpl -> newBlock("TMP_POST_FIELD");
						$tpl->assign(array("TAG_POST_KEY" => $k.'[]', "TAG_POST_VALUE" => $split_v, ));
					}
				}
			}
	
		}
	
		// 送出訂單
		function cart_replace() {
			global $db, $tpl, $cms_cfg, $TPLMSG, $main, $allpay, $ws_array;
	
			$this -> o_id = $this -> o_id_generator();
	
			$this -> ws_tpl_file = "templates/ws-mail-tpl.html";
			$tpl = new TemplatePower($this -> ws_tpl_file);
			$tpl -> prepare();
			$tpl -> assignGlobal("TAG_BASE_CSS", $cms_cfg['base_mail_css']);
	
			$this -> cart_list();
	
			// 台灣區域選擇
			if (!empty($this -> taiwan_zone)) {
				if ($_REQUEST["m_city"] != "請選擇") {
					if ($_REQUEST["m_area"] != "無分區") {
						$o_address = $_REQUEST["m_city"] . $_REQUEST["m_area"] . $_REQUEST["m_address"];
					} else {
						$o_address = $_REQUEST["m_city"] . $_REQUEST["m_address"];
					}
				}
			}
	
			$tpl -> newBlock("MEMBER_DATA_FORM");
			$tpl -> assign($this -> basic_lang);
			$tpl -> assign(array("VALUE_O_ID" => $this -> o_id, "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
			//"VALUE_M_CONTACT_S" => $this->gender_list($_REQUEST["m_contact_s"],1),
			//"VALUE_M_NAME" => $_REQUEST["m_name"],
			"VALUE_M_NAME" => (empty($this -> gender_select)) ? $this -> gender_list($_REQUEST["m_contact_s"], 1) . '&nbsp;' . $_REQUEST["m_name"] : $_REQUEST["m_name"] . '&nbsp;' . $this -> gender_list($_REQUEST["m_contact_s"], 1), "VALUE_M_ZIP" => $_REQUEST["m_zip"], "VALUE_M_ADDRESS" => $o_address, "VALUE_M_TEL" => $_REQUEST["m_tel"], "VALUE_M_FAX" => $_REQUEST["m_fax"], "VALUE_M_EMAIL" => $_REQUEST["m_email"], "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"], "VALUE_CONTENT" => $_REQUEST["content"], ));
	
			// 新增會員
			$this -> new_member();
	
			if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"] == 1) {
				// 顯示付款方式
				$tpl -> newBlock("PAYMENT_TYPE");
				$tpl -> assign(array("MSG_PAYMENT_TYPE" => $TPLMSG["PAYMENT_TYPE"], "VALUE_PAYMENT_TYPE" => $ws_array["payment_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"]], ));
	
				// 收件人資訊
				$tpl -> newBlock("TAG_ADDRESSEE_BLOCK");
				$tpl -> assign($this -> adv_lang);
				$tpl -> assign(array("VALUE_ADD_NAME" => $_REQUEST["o_add_name"], "VALUE_ADD_TEL" => $_REQUEST["o_add_tel"],"VALUE_ADD_CELLPHONE" => $_REQUEST["o_add_cellphone"], "VALUE_ADD_ADDRESS" => $_REQUEST["o_add_address"], "VALUE_ADD_MAIL" => $_REQUEST["o_add_mail"], "VALUE_INVOICE_TYPE" => $ws_array['invoice_type'][$_REQUEST['o_invoice_type']], "VALUE_INVOICE_NAME" => $_REQUEST["o_invoice_name"], "VALUE_INVOICE_VAT" => $_REQUEST["o_invoice_vat"], "VALUE_INVOICE_TEXT" => $_REQUEST["o_invoice_text"], ));
			}
	
			// 到貨時間
			if (is_array($_REQUEST["o_arrival_time"])) {
				$o_arrival_time = implode("-", $_REQUEST["o_arrival_time"]);
				$tpl -> assign("VALUE_ARRIVAL_TIME", $o_arrival_time);
			}
	
			// 國家欄位
			if ($cms_cfg["ws_module"]["ws_country"] == 1) {
				$tpl -> newBlock("MEMBER_DATA_COUNTRY_ZONE");
				$tpl -> assign(array("MSG_COUNTRY" => $TPLMSG['COUNTRY'], "VALUE_M_COUNTRY" => $_REQUEST["m_country"]));
			}
	
			// 寫入訂單
			$sql = "
	            insert into " . $cms_cfg['tb_prefix'] . "_order (
					o_id,
	                m_id,
	                o_status,
	                o_createdate,
	                o_modifydate,
	                o_account,
	                o_company_name,
	                o_contact_s,
	                o_name,
	                o_zip,
	                o_address,
	                o_tel,
	                o_fax,
	                o_cellphone,
	                o_email,
	                o_fee_price,
	                o_ship_price,
	                o_subtotal_price,
	                o_total_price,
	                o_content,
	                o_payment_type,
	                o_add_name,
					o_add_tel,
					o_add_cellphone,
					o_add_address,
					o_add_mail,
					o_invoice_type,
					o_invoice_name,
					o_invoice_vat,
					o_invoice_text,
					o_arrival_time
				) values (
					'" . $this -> o_id . "',
					'" . $this -> m_id . "',
					'0',
					'" . date("Y-m-d H:i:s") . "',
					'" . date("Y-m-d H:i:s") . "',
					'" . $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"] . "',
					'" . $_REQUEST["m_company_name"] . "',
					'" . $_REQUEST["m_contact_s"] . "',
					'" . $_REQUEST["m_name"] . "',
					'" . $_REQUEST["m_zip"] . "',
					'" . $o_address . "',
					'" . $_REQUEST["m_tel"] . "',
					'" . $_REQUEST["m_fax"] . "',
					'" . $_REQUEST["m_cellphone"] . "',
					'" . $_REQUEST["m_email"] . "',
					'" . $this -> plus_fee . "',
					'" . $this -> shipping_price . "',
					'" . $this -> subtotal_money . "',
					'" . $this -> total_money . "',
					'" . $_REQUEST["content"] . "',
					'" . $_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] . "',
					'" . $_REQUEST["o_add_name"] . "',
					'" . $_REQUEST["o_add_tel"] . "',
					'" . $_REQUEST["o_add_cellphone"] . "',
					'" . $_REQUEST["o_add_address"] . "',
					'" . $_REQUEST["o_add_mail"] . "',
					'" . $_REQUEST["o_invoice_type"] . "',
					'" . $_REQUEST["o_invoice_name"] . "',
					'" . $_REQUEST["o_invoice_vat"] . "',
					'" . $_REQUEST["o_invoice_text"] . "',
					'" . $o_arrival_time . "'
			)";
			$rs = $db -> query($sql, true);
	
			// 寫入購買產品
			foreach ($_SESSION[$cms_cfg['sess_cookie_name']]["id"] as $sess => $ID) {
				$row = $this -> products_detail($ID);
	
				$row["p_num"] = (!is_int($_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess])) ? round($_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess]) : $_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess];
				$row["p_name"] = $row["p_name"];
	
				if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
					$special_price = $this -> price_counter($row, 1);
				}
	
				$row["p_price"] = $special_price;
	
				$sql = "
					insert into " . $cms_cfg['tb_prefix'] . "_order_items (
						m_id,
						o_id,
						p_id,
						p_name,
						p_sell_price,
						oi_amount
					) values (
						'" . $this -> m_id . "',
						'" . $this -> o_id . "',
						'" . $ID . "',
						'" . $row["p_name"] . "',
						'" . $special_price . "',
						'" . $row["p_num"] . "'
					)";
				$rs = $db -> query($sql, true);
	
				$all_row[$sess] = $row;
			}
			$this -> mail_handle($mail_goto);
			App::getHelper('session') -> paymentType = $_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"];
			switch(App::getHelper('session')->paymentType) {
				case 1 :
				//atm
				case 2 :
					//貨到付款
					$goto_url = $cms_cfg["base_url"] . "shopping-result.php?status=OK&pno=" . $this -> o_id;
					header("location:" . $goto_url);
					break;
			}
			// ALLPAY (歐付寶)
			if ($allpay -> allpay_switch && $_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
				foreach ($allpay->all_cfg["allpay_type"] as $type => $str) {
					if ($_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] == $type && !empty($_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"])) {
						$allpay_payment = $type;
					}
				}
	
				if (!empty($allpay_payment)) {
					$allpay -> allpay_send($this -> o_id, // 訂單編號
						$this -> total_money, // 交易總金額
						0, // 交易描述 (不可空值)
						$all_row, // 商品資訊 (array)
						$allpay_payment, // 交易方式
						0 // 選擇預設付款子項目
					);
	
					//$mail_goto = 1;
				}
			}
	
			$db_msg = $db -> report();
			if ($db_msg == "") {
				unset($_SESSION[$cms_cfg['sess_cookie_name']]["id"]);
				unset($_SESSION[$cms_cfg['sess_cookie_name']]["num"]);
				unset($_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"]);
			} else {
				$tpl -> assignGlobal("MSG_ACTION_TERM", "DB Error: $db_msg, please contact MIS");
			}
		}
	
		// 修改數量
		function cart_mod($form = 0) {
			global $cms_cfg;
	
			if (!empty($form)) {
				foreach ($form as $sess_str => $num) {
					unset($sess_array);
					//echo $pass_num = (!is_int($num))?round($num):$num;
					$sess_array = explode("|", $sess_str);
	
					$_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess_array[1]] = $num;
				}
	
				$_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] = $form["o_payment_type"];
			} else {
				$this -> error_handle();
			}
		}
	
		// 刪除產品
		function cart_del($form = 0) {
			global $cms_cfg;
	
			if (!empty($form)) {
				$sess_code = $form["ajax_del"];
				unset($_SESSION[$cms_cfg['sess_cookie_name']]["id"][$sess_code]);
				unset($_SESSION[$cms_cfg['sess_cookie_name']]["num"][$sess_code]);
			} else {
				$this -> error_handle();
			}
		}
	
		## 訂單紀錄顯示 ######################################################################################
	
		// 顯示訂單列表 , 取代原  member's orders
		function cart_order() {
			global $db, $tpl, $cms_cfg, $TPLMSG, $ws_array, $main;
	
			$sql = "select * from " . $cms_cfg['tb_prefix'] . "_order where m_id='" . $this -> m_id . "' and del!='1' order by o_createdate desc";
			//取得總筆數
			$selectrs = $db -> query($sql);
			$total_records = $db -> numRows($selectrs);
	
			//取得分頁連結
			$func_str = $cms_cfg["base_root"] . "cart/?func=c_order";
			$sql = $main -> pagination($cms_cfg["op_limit"], $cms_cfg["jp_limit"], $_REQUEST["nowp"], $_REQUEST["jp"], $func_str, $total_records, $sql);
	
			$selectrs = $db -> query($sql);
			$rsnum = $db -> numRows($selectrs);
	
			if (!empty($rsnum)) {
				$tpl -> newBlock("ORDER_LIST_ZONE");
				$tpl -> assign(array("MSG_NAME" => $TPLMSG['MEMBER_NAME'], "MSG_STATUS" => $TPLMSG['STATUS'], "MSG_ORDER_ID" => $TPLMSG['ORDER_ID'], "MSG_CREATEDATE" => $TPLMSG['CREATEDATE'], "MSG_MODIFYDATE" => $TPLMSG['MODIFYDATE'], "MSG_VIEWS" => $TPLMSG['VIEWS'], ));
	
				if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
					$tpl -> newBlock("TAG_PRICE_TH");
					$tpl -> assign("MSG_TOTAL_MONEY", $TPLMSG['ORDER_TOTAL_MONEY']);
					$main_func_str = $TPLMSG['MEMBER_ZONE_ORDER'];
				} else {
					$main_func_str = $TPLMSG['MEMBER_ZONE_INQUIRY'];
				}
	
				$tpl -> assignGlobal(array("TAG_MAIN_FUNC" => $main_func_str, "TAG_LAYER" => $main_func_str, ));
	
				while ($row = $db -> fetch_array($selectrs, 1)) {
					$i++;
	
					$tpl -> newBlock("ORDER_LIST");
					$tpl -> assign(array("VALUE_O_ID" => $row["o_id"], "VALUE_O_NAME" => $row["o_name"], "VALUE_O_CREATEDATE" => $row["o_createdate"], "VALUE_O_MODIFYDATE" => $row["o_modifydate"], "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]], "VALUE_O_SERIAL" => $i, "VALUE_O_DETAIL" => $TPLMSG['DETAIL'], ));
	
					if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
						$tpl -> newBlock("TAG_PRICE_TD");
						$tpl -> assign("VALUE_O_TOTAL_PRICE", $row["o_total_price"]);
					}
				}
	
				$tpl -> newBlock("PAGE_DATA_SHOW");
				$tpl -> assign(array("VALUE_TOTAL_RECORDS" => $page["total_records"], "VALUE_TOTAL_PAGES" => $page["total_pages"], "VALUE_PAGES_STR" => $page["pages_str"], "VALUE_PAGES_LIMIT" => $this -> op_limit));
				if ($page["bj_page"]) {
					$tpl -> newBlock("PAGE_BACK_SHOW");
					$tpl -> assign("VALUE_PAGES_BACK", $page["bj_page"]);
					$tpl -> gotoBlock("PAGE_DATA_SHOW");
				}
				if ($page["nj_page"]) {
					$tpl -> newBlock("PAGE_NEXT_SHOW");
					$tpl -> assign("VALUE_PAGES_NEXT", $page["nj_page"]);
					$tpl -> gotoBlock("PAGE_DATA_SHOW");
				}
			} else {
				$tpl -> assignGlobal("MSG_NO_DATA", $TPLMSG['NO_DATA']);
			}
		}
	
		// 顯示訂單詳細 , 取代原  member's orders
		function cart_order_detail() {
			global $db, $tpl, $cms_cfg, $TPLMSG, $ws_array;
	
			$sql = "select * from " . $cms_cfg['tb_prefix'] . "_order where m_id='" . $this -> m_id . "' and o_id='" . $_REQUEST["o_id"] . "'";
			$selectrs = $db -> query($sql);
			$rsnum = $db -> numRows($selectrs);
	
			if (!empty($rsnum)) {
				$tpl -> newBlock("ORDER_DETAIL_ZONE");
				$tpl -> assignGlobal( array(
                                    "MSG_ORDER_DETAIL" => $TPLMSG['ORDER_DETAIL'], 
                                    "MSG_ORDER_CONTENT" => $TPLMSG['ORDER_CONTENT'], 
                                    "MSG_NAME" => $TPLMSG['NAME'], 
                                    "MSG_STATUS" => $TPLMSG['STATUS'], 
                                    "MSG_ORDER_ID" => $TPLMSG['ORDER_ID'], 
                                    "MSG_CONTENT" => $TPLMSG['CONTENT'], 
                                    "MSG_PAYMENT_TYPE" => $TPLMSG['PAYMENT_TYPE'], 
                                    "MSG_TOTAL" => $TPLMSG['CART_TOTAL'], 
                                    "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'], 
                                    "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'], 
                                    "MSG_PRODUCT" => $TPLMSG['PRODUCT'], 
                                    "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_DISCOUNT_PRICE'], 
                                    "MSG_SHIPPING_PRICE" => $TPLMSG['SHIPPING_PRICE'], 
                                    "MSG_PLUS_FEE" => $TPLMSG["PLUS_FEE"],
                                    ) + $this -> basic_lang
                                );
	
				$row = $db -> fetch_array($selectrs, 1);
				foreach ($row as $key => $value) {
					if ($key == 'o_payment_type') {
						$value = $ws_array["payment_type"][$value];
                                        }else if($key == 'o_name'){
						$value = (empty($this -> gender_select)) ? $this -> gender_list($row["o_contact_s"], 1) . '&nbsp;' . $value : $value . '&nbsp;' . $this -> gender_list($row["o_contact_s"], 1);
                                        }
                                        $tpl -> assignGlobal("VALUE_" . strtoupper($key), $value);
				}
				$tpl -> assignGlobal("VALUE_O_STATUS_SUBJECT", $ws_array["order_status"][$row["o_status"]]);
	
				if (!empty($row["o_payment_type"])) {
					$tpl -> newBlock("TAG_PAYMENT_BLOCK");
					$tpl -> newBlock("TAG_ADV_BLOCK");
					$tpl -> assignGlobal($this -> adv_lang);
	
					$invoice_type = $ws_array['invoice_type'][$row["o_invoice_type"]];
	
					$tpl -> assignGlobal("VALUE_O_INVOICE_TYPE", $invoice_type);
	
					if ($row["o_invoice_type"] == 2) {
						$tpl -> newBlock("TAG_INVOICE_TRI");
					}
	
					$tpl -> newBlock("TAG_ADV_TH");
					$tpl -> newBlock("TAG_ADV_PRICE");
                                        // 顯示手續費
                                        if ($row['o_fee_price']) $tpl -> newBlock("TAG_PLUS_FEE");
					$main_func_str = $TPLMSG['MEMBER_ZONE_ORDER'];
				} else {
					$main_func_str = $TPLMSG['MEMBER_ZONE_INQUIRY'];
				}
	
				$tpl -> assignGlobal(array(
                                    "TAG_MAIN_FUNC" => $main_func_str, 
                                    "TAG_LAYER" => $main_func_str, 
                                ));
	
				$this -> cart_order_detail_item($row);
			}
		}
	
		// 讀取訂單產品紀錄 , 取代原  member's orders
		function cart_order_detail_item($detail = 0) {
			global $db, $tpl, $cms_cfg, $TPLMSG;
	
			$sql = "select * from " . $cms_cfg['tb_prefix'] . "_order_items where o_id='" . $detail["o_id"] . "'";
			$selectrs = $db -> query($sql);
			$rsnum = $db -> numRows($selectrs);
	
			if (!empty($rsnum)) {
				while ($row = $db -> fetch_array($selectrs, 1)) {
					$i++;
					$tpl -> newBlock("ORDER_ITEMS_LIST");
					$tpl -> assign(array(
                                            "VALUE_P_ID" => $row["p_id"], 
                                            "VALUE_P_NAME" => $row["p_name"], 
                                            "VALUE_P_AMOUNT" => $row["oi_amount"], 
                                            "VALUE_P_SERIAL" => $i, 
                                        ));
	
					if (!empty($detail["o_payment_type"])) {
						$tpl -> newBlock("TAG_ADV_TD");
						$tpl -> assign(array(
                                                    "VALUE_P_SELL_PRICE" => $row["p_sell_price"], 
                                                    "VALUE_P_SUBTOTAL_PRICE" => $row["p_sell_price"] * $row["oi_amount"], 
                                                ));
					}
				}
			}
		}
	
		## 附屬功能 ######################################################################################
	
		// 產品價格處理
		function price_counter($row = 0, $switch = 0) {
			global $cms_cfg, $tpl, $TPLMSG, $allpay;
	
			$p_price = (!empty($row["p_special_price"])) ? $row["p_special_price"] : $row["p_list_price"];
	
			// 會員折價
			if (!empty($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]) && $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"] != 100) {
				$msg_price = $TPLMSG['PRODUCT_DISCOUNT_PRICE'];
				$msg_discount = $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"] . "%";
				$special_price = floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"] / 100 * $p_price);
			} else {
				$msg_price = $TPLMSG['PRODUCT_SPECIAL_PRICE'];
				$msg_discount = "";
				$special_price = $p_price;
			}
	
			// 產品計費
			$sub_total_price = $special_price * $row["p_num"];
			$total_price = $total_price + $sub_total_price;
	
			// 顯示產品單價
			if (empty($switch)) {
				$tpl -> newBlock("TAG_PRICE_TD");
				$tpl -> assign(array(
                                    "VALUE_P_SPECIAL_PRICE" => $p_price, 
                                    "VALUE_P_SUBTOTAL_PRICE" => $sub_total_price, 
                                ));
			}
	
			// 累計價格
			$this -> subtotal_money = $this -> subtotal_money + $total_price;
	
			$this -> price_count++;
                        //跑完購物車項目才進行輸出
			if (count($_SESSION[$cms_cfg['sess_cookie_name']]["id"]) == $this -> price_count && empty($switch)){
					
				// 運費
                                $this->shipping_price = Model_Shipprice::calculate($this -> subtotal_money);
	
				// 手續費
				if ($allpay -> allpay_switch && ($_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] == '2' || $_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] == 'CVS' || $_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"] == 'BARCODE')) {
                                    if($_SESSION[$cms_cfg['sess_cookie_name']]["o_payment_type"]==2){
					$this -> plus_fee = Model_Chargefee::calculate($this -> subtotal_money);
                                    }else{
					$this -> plus_fee = 30;
                                    }
				}
	
				// 總價
				$this -> total_money = $this -> subtotal_money + $this -> shipping_price + $this -> plus_fee;
	
				// 顯示價格抬頭
				$tpl -> newBlock("TAG_PRICE_TH");
				$tpl -> assign(array(
                                    "MSG_PRODUCT_SPECIAL_PRICE" => $msg_price, 
                                    "VALUE_P_DISCOUNT" => $msg_discount, 
                                ));
	
				// 顯示手續費
				if (!empty($this -> plus_fee)) {
					$tpl -> newBlock("TAG_PLUS_FEE");
					$tpl -> assign(array(
                                            "MSG_PLUS_FEE" => $TPLMSG["PLUS_FEE"], 
                                            "VALUE_PLUS_FEE" => $this -> plus_fee, 
                                        ));
				}
	
				// 顯示總價
				$tpl -> newBlock("TAG_PRICE_TOTAL");
				$tpl -> assign(array(
                                    "VALUE_SHIPPING_PRICE" => $this -> shipping_price, 
                                    "VALUE_SUBTOTAL" => $this -> subtotal_money, 
                                    "VALUE_TOTAL" => $this -> total_money, 
                                ));
	
				$tpl -> gotoBlock("SHOPPING_CART_ZONE");
			}
	
			return $special_price;
		}
	
		// 讀取產品資料
		function products_detail($id = 0) {
			global $db, $cms_cfg;
	
			$sql = "select * from " . $cms_cfg['tb_prefix'] . "_products as p 
					left join " . $cms_cfg['tb_prefix'] . "_products_cate as pc on p.pc_id=pc.pc_id 
					where p.p_id = '" . $id . "'";
	
			$selectrs = $db -> query($sql);
			$rsnum = $db -> numRows($selectrs);
	
			if (!empty($rsnum)) {
				return $db -> fetch_array($selectrs, 1);
			} else {
				$this -> error_handle();
			}
		}
	
		// 讀取會員資料
		function member_detail() {
			global $db, $cms_cfg, $tpl, $main;
	
			$sql = "select * from " . $cms_cfg['tb_prefix'] . "_member where m_id='" . $this -> m_id . "'";
			$selectrs = $db -> query($sql);
			$rsnum = $db -> numRows($selectrs);
	
			if (!empty($rsnum)) {
				$row = $db -> fetch_array($selectrs, 1);
				$tpl -> assignGlobal(array("VALUE_M_NAME" => $row["m_lname"] . " " . $row["m_fname"],
				//"VALUE_M_CONTACT_S" => $row["m_contact_s"],
				"VALUE_M_COMPANY_NAME" => $row["m_company_name"], "VALUE_M_ZIP" => $row["m_zip"], "VALUE_M_ADDRESS" => $row["m_address"], "VALUE_M_TEL" => $row["m_tel"], "VALUE_M_FAX" => $row["m_fax"], "VALUE_M_EMAIL" => $row["m_email"], "VALUE_M_CELLPHONE" => $row["m_cellphone"]));
			}
	
			// 性別選單
			$this -> gender_list($row["m_contact_s"]);
	
			// 國家下拉選單
			if ($cms_cfg["ws_module"]["ws_country"] == 1) {
				$main -> country_select($row["m_country"]);
			}
	
			if (!empty($rsnum)) {
				$this -> member = $row;
			}
		}
	
		// 新增購物會員
		function new_member() {
			global $cms_cfg, $db;
	
			if (empty($this -> m_id) && !empty($cms_cfg["ws_module"]["ws_cart_login"])) {
				$sql = "
					insert into " . $cms_cfg['tb_prefix'] . "_member (
						mc_id,
						m_status,
						m_modifydate,
						m_account,
						m_password,
						m_company_name,
						m_contact_s,
						m_lname,
						m_birthday,
						m_sex,
						m_country,
						m_zip,
						m_city,
						m_area,
						m_address,
						m_tel,
						m_fax,
						m_cellphone,
						m_email,
						m_epaper_status
					) values (
						'1',
						'1',
						'" . date("Y-m-d H:i:s") . "',
						'" . $_REQUEST["m_account"] . "',
						'" . $_REQUEST["m_password"] . "',
						'" . $_REQUEST["m_company_name"] . "',
						'" . $_REQUEST["m_contact_s"] . "',
						'" . $_REQUEST["m_name"] . "',
						'" . $_REQUEST["m_birthday"] . "',
						'" . $_REQUEST["m_sex"] . "',
						'" . $_REQUEST["m_country"] . "',
						'" . $_REQUEST["m_zip"] . "',
						'" . $_REQUEST["m_city"] . "',
						'" . $_REQUEST["m_area"] . "',
						'" . $_REQUEST["m_address"] . "',
						'" . $_REQUEST["m_tel"] . "',
						'" . $_REQUEST["m_fax"] . "',
						'" . $_REQUEST["m_cellphone"] . "',
						'" . $_REQUEST["m_account"] . "',
						'" . $_REQUEST["m_epaper_status"] . "'
					)";
				$rs = $db -> query($sql);
				$this -> m_id = $db -> get_insert_id();
	
				$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"] = $_REQUEST["m_account"];
				$this -> mail_goto_url = $cms_cfg["base_root"];
			}
		}
	
		// 獨立辨識碼
		function sess_code($id = 0, $option = "") {
			global $cms_cfg;
	
			if (!empty($id)) {
				$sess_code_origin = $_SESSION[$cms_cfg['sess_cookie_name']] . $id;
				$sess_code_combin = $sess_code_origin . $option;
	
				return substr(md5($sess_code_combin), 8, 16);
			}
		}
	
		// O_ID 生成
		function o_id_generator() {
			$day_pass = date("z") - 1;
			$h_s = date("H") * 60 * 60;
			$m_s = date("i") * 60;
			$s = date("s");
	
			$pass_s = $day_pass * 24 * 60 * 60;
			$now_s = $h_s + $m_s + $s;
	
			$all_s = $pass_s + $now_s;
	
			$all_s = str_pad($all_s, 8, "0", STR_PAD_LEFT);
	
			return date("y") . $all_s;
		}
	
		// 產品連結
		function p_link_handle($row = 0) {
			global $cms_cfg;
			if ($this -> ws_seo && is_array($row)) {
				$dirname = (trim($row["pc_seo_filename"])) ? $row["pc_seo_filename"] : "products";
				if (trim($row["p_seo_filename"]) != "") {
					$p_link = $cms_cfg['base_url'] . $dirname . "/" . $row["p_seo_filename"] . ".html";
				} else {
					$p_link = $cms_cfg['base_url'] . $dirname . "/" . "products-" . $row["p_id"] . "-" . $row["pc_id"] . ".html";
				}
			} else {
				$p_link = $cms_cfg['base_url'] . "products.php?func=p_detail&p_id=" . $row["p_id"] . "&pc_parent=" . $row["pc_id"];
			}
	
			return $p_link;
		}
	
		// ajax 輸入處理
		function ajax_form() {
			if (is_array($_REQUEST["val"])) {
				foreach ($_REQUEST["val"] as $key => $array) {
					if (!empty($array["value"])) {
						if (empty($form[$array["name"]])) {
							$form[$array["name"]] = $array["value"];
						} else {
							if (!is_array($form[$array["name"]])) {
								$sub_array = $form[$array["name"]];
								unset($form[$array["name"]]);
	
								$form[$array["name"]][] = $sub_array;
								$form[$array["name"]][] = $array["value"];
							} else {
								$form[$array["name"]][] = $array["value"];
							}
						}
					}
				}
	
				return $form;
			} else {
				$this -> error_handle();
			}
		}
	
		// 性別選單
		function gender_list($get_key = 0, $switch = 0) {
			global $ws_array, $tpl, $TPLMSG;
	
			if (!empty($switch)) {
				return $ws_array["contactus_s"][$get_key];
			}
	
			if (!empty($ws_array["contactus_s"]) && is_array($ws_array["contactus_s"])) {
				$tpl -> newBlock("TAG_S_BLOCK_" . $this -> gender_select);
				$tpl -> assign("MSG_MEMBER_NAME", $TPLMSG['MEMBER_NAME']);
				foreach ($ws_array["contactus_s"] as $s_key => $s_val) {
					$tpl -> newBlock("TAG_S_OPTION_" . $this -> gender_select);
					$tpl -> assign(array("VALUE_S_KEY" => $s_key, "VALUE_S_STR" => $s_val, "VALUE_S_CURRENT" => ($get_key == $s_key && !empty($get_key)) ? 'selected' : ''));
				}
			}
		}
	
		// 台灣地區選單
		function taiwan_zone_select() {
			global $tpl, $cms_cfg;
	
			if (!empty($this -> taiwan_zone)) {
				$tpl -> newBlock("TAG_TAIWAN_ZONE");
				$tpl -> assignGlobal("VALUE_TAIWAN_ZIP", $this -> member["m_zip"]);
			}
		}
	
		// 寄送訊息
		function mail_handle($none_goto = 0) {
			global $db, $cms_cfg, $tpl, $main, $TPLMSG;
	
			if ($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]) {
				$sql = "select st_order_mail from " . $cms_cfg['tb_prefix'] . "_service_term where st_id='1'";
				$mail_title = $TPLMSG["ORDER_MAIL_TITLE"];
				$mail_func = "shopping";
				$selectrs = $db -> query($sql, true);
				$row = $db -> fetch_array($selectrs, 1);
				$tpl -> assignGlobal("VALUE_TERM", $row['st_order_mail']);
			} else {
				$sql = "select st_inquiry_mail from " . $cms_cfg['tb_prefix'] . "_service_term where st_id='1'";
				$mail_title = $TPLMSG["INQUIRY_MAIL_TITLE"];
				$mail_func = "inquiry";
				$selectrs = $db -> query($sql, true);
				$row = $db -> fetch_array($selectrs, 1);
				$tpl -> assignGlobal("VALUE_TERM", $row['st_inquiry_mail']);
			}
	
			//$mail_content=$tpl->getOutputContent();
	
			if ($cms_cfg["ws_module"]["ws_cart_login"] == 0) {
				$goto_url = $_SESSION[$cms_cfg['sess_cookie_name']]['CONTINUE_SHOPPING_URL'];
			} else {
				$goto_url = (!empty($this -> mail_goto_url)) ? $this -> mail_goto_url : $cms_cfg["base_url"] . "cart/?func=c_order_detial&o_id=" . $this -> o_id;
			}
			//$main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_email"],$mail_content,$mail_title,$mail_func,$goto_url,$none_goto);
			App::getHelper('session') -> mailContent = $tpl -> getOutputContent();
		}
	
		// 讀取各式服務條款
		function service_rule() {
			global $db, $tpl, $cms_cfg;
	
			$sql = "select * from " . $cms_cfg['tb_prefix'] . "_service_term";
			$selectrs = $db -> query($sql);
			$rsnum = $db -> numRows($selectrs);
	
			if (!empty($rsnum)) {
				$row = $db -> fetch_array($selectrs, 1);
				foreach ($row as $key => $value) {
					$tpl -> assignGlobal("VALUE_" . strtoupper($key), $value);
				}
			}
		}
	
		// 訂單狀態檢查 (防止瀏覽器上一頁導致流程失效)
		function cart_check() {
			global $cms_cfg;
	
			if (empty($_SESSION[$cms_cfg['sess_cookie_name']]["id"]) || count($_SESSION[$cms_cfg['sess_cookie_name']]["id"]) <= 0 || !is_array($_SESSION[$cms_cfg['sess_cookie_name']]["id"])) {
				echo false;
			} else {
				echo true;
			}
		}
	
		// 錯誤處理
		function error_handle($switch = 0) {
			global $tpl, $TPLMSG;
	
			switch($switch) {
				default :
					$tpl -> assignGlobal("MSG_CART_EMPTY", $TPLMSG['NO_DATA']);
				break;
			}
		}
                function member_login(){
                    global $main,$ws_array,$cms_cfg,$tpl,$TPLMSG;
                    $main->layer_link($ws_array["cart_type"][$_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]]);  
                    $tpl->assignGlobal("TAG_MAIN_FUNC",$TPLMSG['MEMBER_LOGIN']);        
                    $tpl->assignGlobal("TAG_RETURN_URL",$_SERVER['REQUEST_URI']);
                }                  
	
	}
?>

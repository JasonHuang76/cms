<?php

	set_error_handler(array(LANG,"error_redrict"),E_RECOVERABLE_ERROR);
	
	class LANG{
		private static $lang_array = array(
			"eng" => "eng|eng",
		);
		
		public static $templates = ''; // 紀錄樣板目錄
		public static $real_root = ''; // 紀錄實際 base_root
		public static $base_root = ''; // 靜態記憶處理後根目錄
		
		function __construct($base_root='/',array $lang_array){

			if(count($lang_array) > 0){
				$lang_array = $lang_array;
			}else{				
				$lang_array = self::$lang_array;
			}
			
			$uri_no_base = str_replace($base_root, '', $_SERVER["REQUEST_URI"]); // 去除根目錄
			$uri_array = explode("/",$uri_no_base); // 路徑陣列化
			$uri_lang = $uri_array[0]; // 首個路徑參數 (語系參數)
			
			// 語系設定
			if(empty($lang_array[$uri_lang])){
				$lang_keys = array_keys($lang_array);
				$lang_value_array = explode("|",$lang_array[$lang_keys[0]]);
				
				$templates_prefix = $lang_keys[0];
				$lang_prefix = $lang_value_array[0];
				$db_prefix = $lang_value_array[1];
				
				$base_uri = $base_root;
			}else{
				$lang_value_array = explode("|",$lang_array[$uri_lang]);
				
				$templates_prefix = $uri_lang;
				$lang_prefix = $lang_value_array[0];
				$db_prefix = $lang_value_array[1];
				
				$base_uri = $base_root.$uri_lang."/";
			}
			
			$this->language = $lang_prefix;
			$this->tb_prefix = $db_prefix;
			
			self::$base_root = $this->base_root = $base_uri;
			self::$templates = $this->base_templates = "templates-".$templates_prefix;
			self::$real_root = realpath(dirname(__FILE__).'/../') . DIRECTORY_SEPARATOR;
		}
		
		function error_redrict($error_no,$error_str){
			// log to console
			echo '<script> try{ console.log("('.$error_no.') '.$error_str.'"); }catch(e){ alert("'.$error_no.') '.$error_str.'"); } </script>';
		}
	}

?>
<?php

	class LANG{
		private static $lang_array = array(
			"eng" => "eng",
		);
		
		public static $templates = '';
		
		function __construct($base_root='/',array $lang_array){
			$lang_array = array_merge(self::$lang_array,$lang_array);
			
			$uri_no_base = str_replace($base_root, '', $_SERVER["REQUEST_URI"]);
			$uri_array = explode("/",$uri_no_base);
			$uri_lang = $uri_array[0];
			
			if(empty($lang_array[$uri_lang])){
				$uri_lang = "eng";
				$base_uri = $base_root;
			}else{
				$base_uri = $base_root.$uri_lang."/";
			}
			
			$this->language = $uri_lang;
			$this->tb_prefix = $lang_array[$uri_lang];
			
			$this->origin_root = $base_root;
			$this->base_root = $base_uri;
			
			self::$templates = $this->base_templates = "templates-".$uri_lang;
		}
	}

?>
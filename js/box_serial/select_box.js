// JavaScript Document

/* 使用方法

	$(".class or #id").select_box({
		OPTION : 20, // 單個選項顯示高度 (px)
		SHOW : 8, // 一次顯示選項數量
	},function(DATA){
		// 回傳選擇值
	});

	**********************************************************************
	INFO :
	需要配合 jquery.mousewheel.js 使用
*/

(function($){
	$.fn.select_box = function(OPTION,CALLBACK){
		var SELECT = jQuery.extend({
			OPTION : 20, // 單個選項顯示高度 (px)
			SHOW : 8, // 一次顯示選項數量
			
			//----
			TIME : "",
		}, OPTION);
		
		THIS = this;
		
		function log(OUTPUT){
			try{
				console.log(OUTPUT);
			}catch(e){
				alert(OUTPUT);
			}
		}
		
		// 啟動 
		function ACT(ID){
			// 滑鼠點擊展開
			$('#'+ ID).click(function(){
				$(this).find("ul.select_box_option").slideDown('fast');
				$(".select_box").not('#'+ ID).find("ul.select_box_option").slideUp('fast');
			});
			
			// 選擇項目
			$('#'+ ID).find("li").click(function(){
				var CLICK_NUM = $('#'+ ID).find("li").index(this);
				var CLICK_TEXT = $(this).html();
				var CLICK_VAL = $('#'+ ID).prev("select").find("option:eq("+ CLICK_NUM +")").val();
				
				$('#'+ ID).prev("select").find("option:eq("+ CLICK_NUM +")").attr("selected","selected");
				$('#'+ ID).find("ul.select_box_option").slideUp('fast');
				$('#'+ ID).children("span").html(CLICK_TEXT);
				
				CALLBACK(CLICK_VAL);
				return false;
			});
			
			// 滑鼠離開關閉
			$('#'+ ID).mouseleave(function(){
				SELECT.TIME = setTimeout(function(){
					$('#'+ ID).find("ul.select_box_option").slideUp('fast')
				},1000);
			});
			
			// 取消關閉計時
			$("#"+ ID).mouseenter(function(){
				clearTimeout(SELECT.TIME);
			});
			
			// 卷軸項目
	        $("#"+ ID).find("ul.select_box_option").bind('mousewheel', function(event){
	          event.preventDefault();
	          var scrollTop = this.scrollTop;
	          this.scrollTop = (scrollTop + ((event.deltaY * SELECT.OPTION) * -1));
	          //log(event.deltaY, event.deltaFactor, event.originalEvent.deltaMode, event.originalEvent.wheelDelta);
	        });
		}
		
		// 初始化
		function INIT(OBJ,KEY){
			OBJ.hide();
			
			var OPTION_STR = "";
			var INIT_TEXT = "";
			var OPTION_NUM = 0;
			
			// 讀取數據
			OBJ.find("option").each(function(KEY){
				var OPTION_VAL = $(this).val();
				var OPTION_TEXT = $(this).html();
				
				if(KEY == 0){
					INIT_TEXT = OPTION_TEXT;
				} 
				
				OPTION_STR += '<li rel="'+ OPTION_VAL +'">'+ OPTION_TEXT +'</li>';
				OPTION_NUM++;
			});
			
			// 載入標籤
			OBJ.after(
				'<div id="select_box_id_'+ KEY +'" class="select_box"><span>'+ INIT_TEXT +'</span><ul class="select_box_option">'+ OPTION_STR +'</ul></div>'
			);
			
			// 預設 CSS
			OBJ.next(".select_box").css({
				"position": "relative",
				"cursor": "pointer",
			});
			
			OBJ.next(".select_box").children("span").css({
				"width": "95%",
				"overflow": "hidden",
				"display": "block",
				"white-space": "nowrap",
				"padding-left": "5%",
			});
			
			var OPTION_REL_NUM = (OPTION_NUM < SELECT.SHOW)?OPTION_NUM:SELECT.SHOW;
			
			OBJ.next(".select_box").find(".select_box_option").css({
				"display": "none",
				"position": "absolute",
				"z-index": "99",
				"height": SELECT.OPTION * OPTION_REL_NUM +"px",
				"overflow-x": "hidden",
				"overflow-y": "scroll",
			});
			
			OBJ.next(".select_box").find(".select_box_option").find("li").css({
				"list-style": "none",
				"cursor": "pointer",
				"white-space": "nowrap",
				"line-height": SELECT.OPTION +"px",
			});
			
			// 啟動
			ACT('select_box_id_'+ KEY);
		}
		
		return this.each(function(KEY){
			INIT($(this),KEY);
		});
	};
})(jQuery);
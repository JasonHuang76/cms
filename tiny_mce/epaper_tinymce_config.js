tinyMCE.init({
	mode : "exact",
	theme : "advanced",
	skin : "o2k7",
	elements : "elm1,elm2,elm3,elm4,elm5",
    language : "zh-tw",
	convert_urls : false ,
    plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager", 
	// Theme options
	theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,insertimage,insertfile,iframe",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	extended_valid_elements : "iframe[src|width|height|name|align]",
	theme_advanced_resizing : true,
	cleanup : true,
	media_use_script : true,
	media_wmp6_compatible : true,
        convert_urls: false,
        paste_remove_styles : false,
        paste_strip_class_attributes : "none",  
        paste_auto_cleanup_on_paste : false,  
        paste_text_use_dialog : true,                  
        paste_force_cleanup_paste : false,        
        paste_remove_spans : false,               
        paste_retain_style_properties : "margin, padding, width, height, font-size, font-weight, font-family, color, text-align, ul, ol, li, text-decoration, border, background, float, display",
        template_replace_values: {
            username : "Jack Black",
            staffid : "991234",
            mybb: function(e){
                e.innerHTML = 'mybb';
            }
        },           
        template_templates : [
            {
                title: "edm01單欄",
                src: "templates/epaper/edm01-single-column.html",
                description: "edm01的單欄表格"
            },
            {
                title: "edm01雙欄",
                src: "templates/epaper/edm01-double-column.html",
                description: "edm01的雙欄表格"
            },
            {
                title: "edm02示範內容",
                src: "templates/epaper/edm02-template.html",
                description: "edm02的示範內容"
            },
            {
                title: "edm02空白區域",
                src: "templates/epaper/edm02-empty-block.html",
                description: "edm02的空白區域"
            },
            {
                title: "Editor Details",
                src: "templates/epaper/editor_details.htm",
                description: "Adds Editor Name and Staff ID"
            },
            {
                title: "Timestamp",
                src: "templates/epaper/time.htm",
                description: "Adds an editing timestamp."
            }
        ]        
});     
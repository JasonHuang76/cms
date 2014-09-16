<?php
class ContactfieldWithCourtesyTitle {
    //put your code here
    protected $view;
    protected $blockName;
    protected $fieldData;
    protected $tpl;
    function __construct($options) {
        $this->setOptions($options);
        $this->tpl = new TemplatePower($this->view);
        $this->tpl->prepare();
    }
    protected function setOptions($options){
        if(is_array($options)){
            foreach($options as $name => $value){
                if(property_exists($this, $name)){
                    if($name=="view"){
                        $value = APP_ROOT_PATH.LANG::$templates."/contactfield/".$value.".html";
                    }
                    $this->$name = $value;
                }
            }
        }
    }
    function get_html(){
        $blockName = $this->blockName."Style".App::configs()->ws_module->ws_contactus_s_style;                
        $courtesyBlockName = $blockName."CourtesyTitle";
        $this->tpl->newBlock($blockName);
        if(is_array($this->fieldData['contact'])){
            foreach($this->fieldData['contact'] as $k => $v){
                $this->tpl->assign(strtoupper($k),$v);
            }
        }
        App::getHelper('main')->multiple_select($courtesyBlockName,App::defaults()->contactus_s,$this->fieldData['courtesyTitle'],$this->tpl);
        return $this->tpl->getOutputContent();
    }
}

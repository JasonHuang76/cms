<?php
class Model_Sms_Emome extends Model_Sms_Abstract {
    //put your code here
     protected $data_fields = array(
        //帳號
        'account'  => "", 
        //密碼
        'password' => "",
        // **發送者號碼的種類**
        // 0 (代表from address是一個手機門號)，
        // 1 (代表from_addr是一個emome代碼)，
        // 2 (代表from_addr 是為帳號為開頭的字串)，長度最多為9。
        'from_addr_type' =>  0,
        //這通SMS訊息的發送者的號碼。由於此欄位關係到收費對象
        'from_addr' => '',
        //發送對象號碼 (to address) 的種類，
        //0 (代表to address是一個手機門號)
        //1 (代表to_addr是一個emome代碼)
        //若沒有傳送這個參數，系統自動內定此參數為0 (發送對象為手機門號)。
        'to_addr_type' =>  0,
        //這通SMS訊息的發送對象，必須符合以09開頭的格式，
        //若您有足夠的權限，此欄可以接受民營GSM業者的門號。
        //此欄可以包含一個以上的門號，以ASCII的逗號 (,) 隔開，最多接受20個獨立門號。
        'to_addr' =>  null,
        //這通SMS訊息的預約傳送時間，時間格式為: yymmddHHMM，
        //舉例若欲傳送的時間為2005年2月1日上午9時15分，則填入 0502010915。
        //請注意預約時間不得超過48小時。若不需要預約傳送功能, 此欄可不填。
        'msg_dlv_time' => null,
        //這通SMS訊息的失效時間，以分鐘為單位。
        //超過此失效時間，若訊息仍無法送出，則此通訊息將視為無效，
        //若不需設定失效時間，則將此欄設為0，或是不填值。
        'msg_expire_time' => null,
        //這通SMS訊息的訊息種類，如果該訊息為一般通用簡訊，此欄設為0﹔
        //如果該訊息為pop-up 簡訊，此欄設為1；
        //若該訊息為傳送至手機上的應用程式，資料型態為文字時，此欄設為2，
        //若為binary 型態，則此欄設為3
        'msg_type'   => 0,
        //這通SMS訊息的訊息編碼方式，
        //若此通SMS訊息純粹為文字訊息(中文或英文)的話，可以用default value=0送出，
        //IMSP SMS Server平台會自行轉換，
        //若是binary data的話，請自行填入正確的編碼值。
        'msg_dcs'  => 0,
        //這通SMS訊息的訊息GSM協定識別碼，通常設為0
        'msg_pclid'   => 0,
        //若需設UDHI，此欄設為1，反之，此欄設為0。
        'msg_udhi'  => 0,
        //這通SMS訊息的訊息內容，
        //若此通SMS訊息含有中文的話，請用Big5編碼送出 (適用於msg_type=0及1)，
        //當msg_type=2時，文字請用Unicode-Big或UTF-16BE來編碼，並將內容以HEX來表示，
        //當msg_type=3時，Binary的資料也須以Hex來表示 (請看底下範例)。
        //*當msg_type=2或3時，msg長度限制為132 bytes，約66個中文字。
        'msg'   => "",
        //當msg_type=2或3時, 訊息將送至手機上此參數所指定的port (msg_udhi將自動設為1)，
        //以HEX字串表示，如port = 1234，則dest_port=04D2。
        'dest_port'   => null,
    );
    //msg_type對應的msg長度限制
    protected $len_limit = array(160,160,132,132);
    //長簡訊單則msg長度
    protected $split_limit = 132;
    protected $send_mod = 'single';
    protected $multi_msg_template = "050003%02X%02d%02d%s";
    public $curl_error = '';
    public $curl_respose = array();
    
    function __construct($config) {
        $this->data_fields['account'] = $config['account'];
        $this->data_fields['password'] = $config['password'];
        $this->setFrom($config['from_addr'], $config['from_addr_type']);
    }
    //設定data_fields['from'] and data_fields['from_type]
    function setFrom($address,$type=0){
        $this->data_fields['from_addr'] = $address;
        $this->data_fields['from_addr_type'] = $type;
    }
    function setToType($type=0){
        if($type==0 || $type==1 ){
            $this->data_fields['to_addr_type'] = $type;
        }
    }
    function setToAddr($to_addr){
        if(is_string($to_addr)){
            $this->data_fields['to_addr'] = $to_addr;
        }elseif(is_array($to_addr)){
            $this->data_fields['to_addr'] = implode(',',$to_addr);
        }
    }
    function setMsgType($type=0){
        if($type >=0 && $type<=3){
            $this->data_fields['msg_type'] = $type;
        }
    }
    function setMsg($message){
        $message = mb_convert_encoding($message,'big5','utf8');
        if(($len = strlen($message)) <= $this->len_limit[$this->data_fields['msg_type']]){
            $this->data_fields['msg'] = $message;
        }else{
            $run=0;
            //留一些空間，避免中文字被擠掉，
            //-12是因為會切割數則後，每則都會額外加入12個前置序號
            $custom_limit = $this->split_limit-12-2;
            //分割簡訊則數
            $times=ceil($len/$custom_limit);
            $this->data_fields['msg'] = array();
            $this->data_fields['msg_udhi'] = 1;
            $this->data_fields['msg_dcs'] = 8;//設編碼為utf-16be
            $idx=0;
            while($run<$times){
                do{
                    if(!isset($this->data_fields['msg'][$run])){
                        $this->data_fields['msg'][$run]='';
                    }
                    $c = mb_substr($message, $idx, 1, 'big5');
                    $this->data_fields['msg'][$run] .= $c;
                    $idx++;
                }while(strlen($this->data_fields['msg'][$run])<$custom_limit && $c!='');
                $run++;
            }
        }
    }
    function send(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_URL, "https://imsp.emome.net/imsp/sms/servlet/SubmitSM");
        curl_setopt($ch, CURLOPT_PORT, "4443");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //SSL
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  //連線逾期
        $this->_send($ch);
        curl_close($ch);
        return $this->curl_respose;
    }
    function _send($ch){
        if(is_string($this->data_fields['msg'])){
            $post_fields = array();
            foreach($this->data_fields as $k=>$v){
                if(isset($v)){
                    $post_fields[] = sprintf('%s=%s',$k,  urlencode($v));
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode("&",$post_fields));
            $this->curl_respose[] = curl_exec($ch);             
        }else{
            //多則簡訊的前置訊息
            $serial = rand(0, 255);
            $total_msgs = count($this->data_fields['msg']);
            //先擷取固定欄位
            $post_fields = array();
            foreach($this->data_fields as $k=>$v){
                if($k!='msg' && isset($v)){
                    $post_fields[] = sprintf('%s=%s',$k,  urlencode($v));
                }
            }
            foreach($this->data_fields['msg'] as $k => $msg){
                $msg = mb_convert_encoding($msg, 'utf-16be', 'big5');
                $hexStr = '';
                for($i=0;$i<strlen($msg);$i++){
                    $hexStr .= sprintf("%02X",ord($msg[$i]));
                }
                $msg = sprintf($this->multi_msg_template,$serial,$total_msgs,$k+1,$hexStr);
                $post_fields['msg'] = sprintf('msg=%s',  urlencode($msg));
                curl_setopt($ch, CURLOPT_POSTFIELDS, implode("&",$post_fields));
                $this->curl_respose[] = curl_exec($ch);                       
            }
        }
        if(curl_errno($ch)){
            $this->curl_error .= curl_error($ch);
        }
    }   
}

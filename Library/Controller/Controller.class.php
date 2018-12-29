<?php
/**
*   author:周健东
*   QQ:395154675
*/
class Controller{
    public function __construct(){
        if(isset($_GET)){
            $this->assign('GET',$_GET);
        }
    }
    protected $data = array();
    protected function assign($name,$value){
        if(is_array($value)||is_string($value)||is_int($value)){
            $this->data[$name] = $value;
        }
    }
    protected function display($custom=''){
        if(!empty($custom)){
            if(strpos($custom,'/')){
                list($controller,$action) = explode('/',$custom);
            }else{
                $controller = CONTROLLER;
                $action = $custom;
            }
        }else{
            $controller = CONTROLLER;
            $action = ACTION;
        }
        $filePath = ENTRY_PATH.'/'.APPLICATION_NAME.'/Template/'.$controller.'/'.$action.'.html';
        if(is_file($filePath)){
            $contents = file_get_contents($filePath);
        }else{
            Base::abnormal('模板:'.$filePath.'不存在！错误码：100001');
        }
        
        $this->ubbData($contents);
    }
    private function ubbData($contents){
        preg_match_all('/<(include){1} +(.*?) *(name){1}=[\'\"]{1}([^\'\"]*)[\'\"]{1}([^>]*)>/',$contents,$matches,PREG_SET_ORDER);
        if(!empty($matches)){
            foreach($matches as $key=>$val){
                if($val[1]=='include'){
                    $contents = str_replace($val[0],file_get_contents(ENTRY_PATH.'/'.APPLICATION_NAME.'/Template/'.$val[4].'.html'),$contents);
                }else if(!strstr($val[4],'http://')&&!strstr($val[4],'https://')){
                    $contents = str_replace($val[0],'<'.$val[1].' '.$val[2].' '.$val[3].'="'.__APP__.'/Template/'.$val[4].'" '.$val[5].'>',$contents);
                }
            }
        }



        preg_match_all('/<foreach +name=[\'\"]{1}([a-zA-Z0-9_]+)[\'\"]{1} +id=[\'\"]{1}([a-zA-Z0-9_]+)[\'\"] *>(.*?)<\/foreach>/s',$contents,$matches,PREG_SET_ORDER);      
        if(!empty($matches)){
            foreach($matches as $val){
                $strForeach = '';
                if(is_array($this->data[$val[1]])){
                    foreach($this->data[$val[1]] as $valdata){
                        $strForeach .= $this->varData($val[3],$valdata,$val[2]);
                    }
                    $contents = str_replace($val[0],$strForeach,$contents);
                }     
            }
        }
        $contents = $this->varData($contents,$this->data);

        /**
         * html中常量替换
         */
        $contents = str_replace('__APP__',__APP__,$contents);
        $contents = str_replace('__URL__',__URL__,$contents);
        $contents = str_replace('__ROOT__',__ROOT__,$contents);
        $contents = str_replace('__TEMPLATE__',__TEMPLATE__,$contents);
        /**
         * html中插入javaScript的json变量数据
         */
        if(strstr($contents,'</title>')){
            $contents = str_replace('</title>',"</title>\n\t".$this->JavaScriptData(),$contents);
        }
        echo $contents;
    }
    protected function varData($contents,$dataArr,$item=''){
        preg_match_all('/\{\[\$([a-zA-Z0-9_.]+)\]\}/',$contents,$matches,PREG_SET_ORDER);
        if(!empty($matches)){
            foreach($matches as $val){
                $valarr = explode('.',$val[1]);
                if(!empty($item)&&$item==$valarr[0]){
                    array_shift($valarr);
                }
                $data = '';
                foreach($valarr as $varS){
                    $data .= '["'.$varS.'"]';
                }
                
                $data = '$dataArr'.$data;
                @eval('$newData = '.$data.';');
                $newData = is_array($newData)?'Array':$newData;
                $contents = str_replace($val[0],$newData,$contents);
            }
        }
        return $contents;
    }
    protected function JavaScriptData(){
        return '<script>var EntryData = '.json_encode($this->data).';var __TEMPLATE__ = "'.__TEMPLATE__.'"</script>';
    }
    public function __call($functionName,$argumentsArr){
        $this->display();
    }
    /**
     * 授权方法：
     * 参数：目录和数据表名称
     * 返回：用户信息，如果新授权用户返回用户ID和openID，如果是已经授权过用户还会返回用户所有信息
     */
    protected function Accredit($dirname,$database){
        $WeixinUserAccredit = new WeixinUserAccredit($dirname,$database);
        $userinfo = $WeixinUserAccredit->UserAccredit();
        return $userinfo;
    }
    /**
     * 判断用户是否授权过
     * 参数：数据表名称
     * 返回：如果没有授权返回false，授权过返回用户信息
     */
    protected function VerifyUser($database){
        $userinfo = false;
        if(empty($database)){
            Base::abnormal('微信授权验证数据参数不正确！方法是：VerifyUser错误码：100014');
        }else{
            if(!empty($_COOKIE['uid'])&&!empty($_COOKIE['openid'])){
                $user = M($database);
                $userWhere['id'] = $_COOKIE['uid'];
                $userWhere['openid'] = $_COOKIE['openid'];
                $userFind = $user->where($userWhere)->find();
                if(!empty($userFind)){
                    $userinfo = $userFind;
                }
            }else{
                Base::abnormal('COOKIE参数有误，uid='.$_COOKIE['uid'].'；openid='.$_COOKIE['openid']);
            }
        }
        return $userinfo;
    }
    protected function VerifyUserSession($database){
        $userinfo = false;
        if(empty($database)){
            Base::abnormal('微信授权验证数据参数不正确！方法是：VerifyUser错误码：100014');
        }else{
            if(!empty($_SESSION['uid'])&&!empty($_SESSION['openid'])){
                $user = M($database);
                $userWhere['id'] = $_SESSION['uid'];
                $userWhere['openid'] = $_SESSION['openid'];
                $userFind = $user->where($userWhere)->find();
                if(!empty($userFind)){
                    $userinfo = $userFind;
                }
            }else{
                Base::abnormal('SESSION参数有误，uid='.$_SESSION['uid'].'；openid='.$_SESSION['openid']);
            }
        }
        return $userinfo;
    }
    public function sinaUploadImage(){//新浪图床接口
        $message = 0;
        $url = '';
        $returnArr = array();
        $i = 0;
        $file = '';
        foreach($_FILES as $key=>$val){
            if($i===0){
                $file = $key;
            }
            ++$i;
        }
        if(!empty($_FILES[$file]['name'])){
            $sinaUpload = new s3_upload();
            $sinaUpload->show($_FILES[$file]);
            $md = md5(time().mt_rand(1000,9999));
            $responseData = $sinaUpload->save($md);
            $url = $responseData['src'];
            if(!empty($url)){
                $message = 1;
            }else{
                $message = -3;
            }	
        }else{
            $message = -2;
        }
        $returnArr['message'] = $message;
        $returnArr['url'] = $url;
        echo json_encode($returnArr);
    }
    /**
     * 微信支付,异步返回处理,案例:
     * 由于微信异步处理的路径不支持参数
     * 所以这个方法只能在index.php/Index/index这个方法里面
     * 使用方法,下面方法体内容可以支付复制只用到index方法里面:
     */
    // if(!empty($GLOBALS["HTTP_RAW_POST_DATA"])){
    //     $this->WxNotify();
    //     exit();
    // }
    public function WxNotify(){
        $simple = $GLOBALS["HTTP_RAW_POST_DATA"];
        $simpleobg = simplexml_load_string($simple,'SimpleXMLElement', LIBXML_NOCDATA);
        $user = M('hlj_pufasaomashenqin');
        $where['recommend'] = $simpleobg->out_trade_no;//商户订单号
        $data['city'] = $simpleobg->time_end;//支付完成时间
        $data['sort'] = $simpleobg->total_fee;//订单金额
        $data['title'] = $simpleobg->attach;//商家数据包
        $data['province'] = $simpleobg->transaction_id;//微信支付订单号
        $data['contacts'] = $simpleobg->openid;//用户的openid
        $data['status'] = 2;
        $data['describes'] = '商家数据包:'.$simpleobg->attach.'|用户的openid:'.$simpleobg->openid.'|商户订单号:'.$simpleobg->out_trade_no.'|支付完成时间:'.$simpleobg->time_end.'|订单金额:'.$simpleobg->total_fee.'|微信支付订单号:'.$simpleobg->transaction_id;
        $save = $user->data($data)->where($where)->save();
        if($save){
            echo 'success';
        }
        /**
         * 返回格式:
         */
        // $simple ='<xml><appid><![CDATA[wx5c485f648669db56]]></appid>
        // <attach><![CDATA[test]]></attach>
        // <bank_type><![CDATA[CFT]]></bank_type>
        // <cash_fee><![CDATA[1]]></cash_fee>
        // <fee_type><![CDATA[CNY]]></fee_type>
        // <is_subscribe><![CDATA[Y]]></is_subscribe>
        // <mch_id><![CDATA[1262502401]]></mch_id>
        // <nonce_str><![CDATA[xkntirkxa4m50pkq0ts00ix1zyae8xl2]]></nonce_str>
        // <openid><![CDATA[o3m_osikvNmp5Vm3l2XBWRyQ52GI]]></openid>
        // <out_trade_no><![CDATA[126250240120150814111617]]></out_trade_no>
        // <result_code><![CDATA[SUCCESS]]></result_code>
        // <return_code><![CDATA[SUCCESS]]></return_code>
        // <sign><![CDATA[85E31F4C9AEF30AE86ABDFF0546AF76B]]></sign>
        // <time_end><![CDATA[20150814111457]]></time_end>
        // <total_fee>1</total_fee>
        // <trade_type><![CDATA[JSAPI]]></trade_type>
        // <transaction_id><![CDATA[1010230397201508140617645345]]></transaction_id>
        // </xml>';
        /**
         * 参数解释：
         **/
        // $simple ='<xml><appid>公众账号ID：微信分配的公众账号ID（企业号corpid即为此appId）</appid>
        // <attach>商家数据包：商家数据包，原样返回（个人提交过来的备注）</attach>
        // <bank_type>付款银行：银行类型，采用字符串类型的银行标识，银行类型见银行列表</bank_type>
        // <cash_fee>现金支付金额：现金支付金额订单现金支付金额，详见支付金额</cash_fee>
        // <fee_type>货币种类：货币类型，符合ISO4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型</fee_type>
        // <is_subscribe>是否关注公众账号：用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效</is_subscribe>
        // <mch_id>商户号：微信支付分配的商户号</mch_id>
        // <nonce_str>随机字符串：随机字符串，不长于32位</nonce_str>
        // <openid>用户标识：用户在商户appid下的唯一标识</openid>
        // <out_trade_no>商户订单号：商户系统的订单号，与请求一致。</out_trade_no>
        // <result_code>业务结果： SUCCESS/FAIL</result_code>
        // <return_code>返回状态码：SUCCESS/FAIL，SUCCESS表示商户接收通知成功并校验成功</return_code>
        // <sign>签名：签名，详见签名算法</sign>
        // <time_end>支付完成时间：支付完成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则</time_end>
        // <total_fee>订单金额：订单总金额，单位为分</total_fee>
        // <trade_type>交易类型：JSAPI、NATIVE、APP</trade_type>
        // <transaction_id>微信支付订单号：微信支付订单号</transaction_id>
        // </xml>';
    }

    /**
     * PHPExcel扩展，实现读取Excel文件内容。
     * $filename    Excel文件
     * 返回数据内容
     */
    protected function getExcelData($filename){
        //导入PHPExcel类库
        $exts = ltrim(strchr($filename,'.'),'.');
        //不同类型的文件导入不同的类
        if ($exts == 'xls') {
            $PHPReader = new PHPExcel_Reader_Excel5();
        } else if ($exts == 'xlsx') {
            $PHPReader = new PHPExcel_Reader_Excel2007();
        }
        //载入文件
        $PHPExcel = $PHPReader->load($filename);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);
        //获取总列数
        $allColumn = $currentSheet->getHighestColumn();
        //获取总行数
        $allRow = $currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            //从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                //数据坐标
                $address = $currentColumn . $currentRow;
                //读取到的数据，保存到数组$arr中
                $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }
        }
        return $data;
    }
}
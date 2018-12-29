<?php
/**
*   author:周健东
*   QQ:395154675
*/
require_once ENTRYPHP_PATH.'/Library/Driver/Weixin/WeixinConf.php';
class WeixinHongbao{
    public function postXmlCurl($xml, $url, $useCert = false, $second = 30){        
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        
        curl_setopt($ch,CURLOPT_URL, $url);
    /*    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验 */
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验2
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            //curl_setopt($ch,CURLOPT_SSLCERT, './cert/apiclient_cert.pem');
            curl_setopt($ch,CURLOPT_SSLCERT, WX_SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            //curl_setopt($ch,CURLOPT_SSLKEY, './cert/apiclient_key.pem');//注意:cert文件夹必须是只读的
            curl_setopt($ch,CURLOPT_SSLKEY, WX_SSLKEY_PATH);//注意:cert文件夹必须是只读的
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }

/**
*    参数说明:
*    $re_openidUser                                        //是客户端的OpenID
*    $systemArguments['mch_billno'] = '123123123';        //用户的订单号(用来存放数据库里面的),不能重复
*    $systemArguments['mch_id'] = '1260555201';            //微信的商户号,例如:新浪黑龙江商户号,注意是28位
*    $systemArguments['wxappid'] = 'wxc75a2d8a1c6ba5d9';    //微信的APPID
*    $systemArguments['send_name'] = '新浪黑龙江';        //拆开红包的标题名称,也可以是公司名称,例如:新浪黑龙江
*    $systemArguments['total_amount'] = '100';            //金额单位是分,至少发1元,也就是这个数值最小是100
*    $systemArguments['total_num'] = '1';                //红包个数
*    $systemArguments['wishing'] = '恭喜发财';            //拆开红包标题下的描述,例如:恭喜发财
*    $systemArguments['client_ip'] = '123.125.105.138';    //可以连接的IP,就是白名单,一般是当前文件的服务器IP
*    $systemArguments['act_name'] = '关注红包';            //参与的活动名称
*    $systemArguments['remark'] = '关注红包';            //官方给出参数意思是描述,但是目前不知道在什么地方显示
*    $systemArguments['scene_id'] = 'PRODUCT_2';            //红包的形式,需要在后台设置
*/
    public function Hongbao($re_openidUser,$systemArguments=array()){
        $nonce_str = md5(mt_rand(1000,9999).time());//32位的随机数
        $mch_billno = !empty($systemArguments['mch_billno'])?$systemArguments['mch_billno']:$re_openidUser;//用户的订单号(用来存放数据库里面的)
        //$mch_id = !empty($systemArguments['mch_id'])?$systemArguments['mch_id']:"1260555201";//微信的商户号,
        $mch_id = WX_MCHID;//例如:新浪黑龙江商户号
        //$wxappid = !empty($systemArguments['wxappid'])?$systemArguments['wxappid']:"wxc75a2d8a1c6ba5d9";//微信的APPID,
        $wxappid = WX_APPID;//微信的APPID,
        $wxKey = WX_KEY;//key是API密钥,在商户平台里面设置.位置:登录商户平台->账户中心->API安全->设置API秘钥
        $send_name = !empty($systemArguments['send_name'])?$systemArguments['send_name']:"新浪黑龙江";//拆开红包的标题名称,也可以是公司名称,例如:新浪黑龙江
        $re_openid = $re_openidUser;//用户的OPENID
        $total_amount = !empty($systemArguments['total_amount'])?$systemArguments['total_amount']:'100';//金额单位是分,至少发1元,也就是这个数值最小是100
        $total_num = !empty($systemArguments['total_num'])?$systemArguments['total_num']:'1';//红包个数
        $wishing = !empty($systemArguments['wishing'])?$systemArguments['wishing']:'恭喜发财';//拆开红包标题下的描述,例如:恭喜发财
        $client_ip = !empty($systemArguments['client_ip'])?$systemArguments['client_ip']:'123.125.105.138';//可以连接的IP,就是白名单,一般是当前文件的服务器IP
        $act_name = !empty($systemArguments['act_name'])?$systemArguments['act_name']:'关注红包';//参与的活动名称
        $remark = !empty($systemArguments['remark'])?$systemArguments['remark']:'关注红包';//官方给出参数意思是描述,但是目前不知道在什么地方显示
        $scene_id = !empty($systemArguments['scene_id'])?$systemArguments['scene_id']:'PRODUCT_2';//红包的形式,需要在后台设置

        $stringSignTemp = "act_name={$act_name}&client_ip={$client_ip}&mch_billno={$mch_billno}&mch_id={$mch_id}&nonce_str={$nonce_str}&re_openid={$re_openid}&remark={$remark}&scene_id={$scene_id}&send_name={$send_name}&total_amount={$total_amount}&total_num={$total_num}&wishing={$wishing}&wxappid={$wxappid}&key={$wxKey}";//key是API密钥

        $sign = strtoupper(md5($stringSignTemp));
        $dataXMl = "<xml>
        <nonce_str><![CDATA[%s]]></nonce_str>
        <sign><![CDATA[%s]]></sign>
        <mch_billno><![CDATA[%d]]></mch_billno>
        <mch_id><![CDATA[%d]]></mch_id>
        <wxappid><![CDATA[%s]]></wxappid>
        <send_name><![CDATA[%s]]></send_name>
        <re_openid><![CDATA[%s]]></re_openid>
        <total_amount><![CDATA[%d]]></total_amount>
        <total_num><![CDATA[%d]]></total_num>
        <wishing><![CDATA[%s]]></wishing>
        <client_ip><![CDATA[%s]]></client_ip>
        <act_name><![CDATA[%s]]></act_name>
        <remark><![CDATA[%s]]></remark>
        <scene_id><![CDATA[%s]]></scene_id>
        </xml> ";
        $args = array($nonce_str,$sign,$mch_billno,$mch_id,$wxappid,$send_name,$re_openid,$total_amount,$total_num,$wishing,$client_ip,$act_name,$remark,$scene_id);
        $dataXMlstring = vsprintf($dataXMl, $args);
        $returnData = $this->postXmlCurl($dataXMlstring,'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack',true,6);
        return $returnData;
    }
}

/**
*    使用方法:
*/

// $hongbao = new WeixinHongbao();
// $systemArguments['mch_billno'] = time().mt_rand(10000,99999);        //用户的订单号(用来存放数据库里面的),不能重复
// $systemArguments['send_name'] = '新浪黑龙江';        //拆开红包的标题名称,也可以是公司名称,例如:新浪黑龙江
// $systemArguments['total_amount'] = '100';            //金额单位是分,至少发1元,也就是这个数值最小是100
// $systemArguments['total_num'] = '1';                //红包个数
// $systemArguments['wishing'] = '恭喜发财';            //拆开红包标题下的描述,例如:恭喜发财
// $systemArguments['client_ip'] = '123.125.105.138';    //可以连接的IP,就是白名单,一般是当前文件的服务器IP
// $systemArguments['act_name'] = '关注红包';            //参与的活动名称
// $systemArguments['remark'] = '关注红包';            //官方给出参数意思是描述,但是目前不知道在什么地方显示
// $systemArguments['scene_id'] = 'PRODUCT_2';            //红包的形式,需要在后台设置
// $returnData = $hongbao->Hongbao($userinfo['openid'],$systemArguments);
// $postObj = simplexml_load_string($returnData, 'SimpleXMLElement', LIBXML_NOCDATA);
// if($postObj->result_code=='SUCCESS'){
//     $status = 1;//成功
// }else{
// // print_r($data);
// // echo '<hr>';
// // echo  'return_code:'.$postObj->return_code;
// // echo '<br>';
// // echo  'return_msg:'.$postObj->return_msg;
// // echo '<br>';
// // echo  'result_code:'.$postObj->result_code;
// // echo '<br>';
// // echo  'err_code:'.$postObj->err_code;
// // echo '<br>';
// // echo  'err_code_des:'.$postObj->err_code_des;
// // echo '<br>';
// // echo  'mch_billno:'.$postObj->mch_billno;
// // echo '<br>';
// // echo  'mch_id:'.$postObj->mch_id;
// // echo '<br>';
// // echo  'wxappid:'.$postObj->wxappid;
// // echo '<br>';
// // echo  're_openid:'.$postObj->re_openid;
// // echo '<br>';
// // echo  'total_amount:'.$postObj->total_amount;
// // echo '<br>';
// // echo '<hr>';
// // exit();
//     $status = -2;//失败
// }

/**
*    成功返回:
*/
// o7DJfswfl-O1-bJHmDlO3Wi7akt0
// 100
// return_code:SUCCESS
// return_msg:发放成功
// result_code:SUCCESS
// err_code:SUCCESS
// err_code_des:发放成功
// mch_billno:701414958554261
// mch_id:1260555201
// wxappid:wxc75a2d8a1c6ba5d9
// re_openid:o7DJfswfl-O1-bJHmDlO3Wi7akt0
// total_amount:100
/**
*    失败返回:
*/
// o7DJfswfl-O1-bJHmDlO3Wi7akt0
// 100
// return_code:SUCCESS
// return_msg:该红包已经发放成功
// result_code:FAIL
// err_code:SUCCESS
// err_code_des:该红包已经发放成功
// mch_billno:701414958554261
// mch_id:1260555201
// wxappid:wxc75a2d8a1c6ba5d9
// re_openid:o7DJfswfl-O1-bJHmDlO3Wi7akt0
// total_amount:100

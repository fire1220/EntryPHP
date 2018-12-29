<?php
/**
*   author:周健东
*   QQ:395154675
*/
require_once ENTRYPHP_PATH.'/Library/Driver/Weixin/PayLibrary/WxPay.Api.php';
require_once ENTRYPHP_PATH.'/Library/Driver/Weixin/PayLibrary/WxPay.JsApiPay.php';
require_once ENTRYPHP_PATH.'/Library/Driver/Weixin/PayLibrary/log.php';
require_once ENTRYPHP_PATH.'/Library/Driver/Weixin/WeixinConf.php';
// $conf = Configure::Conf('WeixinPay');
// $systemConf = $conf['systemConf'];
// define('WX_APPID',$systemConf['APPID']);
// define('WX_MCHID',$systemConf['MCHID']);
// define('WX_KEY',$systemConf['KEY']);
// define('WX_APPSECRET',$systemConf['APPSECRET']);
// define('WX_NOTIFY_URL',$systemConf['NOTIFY_URL']);
// define('WX_SSLCERT_PATH',$systemConf['SSLCERT_PATH']);
// define('WX_SSLKEY_PATH',$systemConf['SSLKEY_PATH']);
// define('WX_CURL_PROXY_HOST',$systemConf['CURL_PROXY_HOST']);
// define('WX_CURL_PROXY_PORT',$systemConf['CURL_PROXY_PORT']);
// define('WX_REPORT_LEVENL',$systemConf['REPORT_LEVENL']);

class WeixinPay{
    protected $GetOpenid = null;
    protected $parameters = array();
    protected $JsApiPay = null;
    protected $payData = array();
    protected $successJavaScript = '';
    public function __construct(){
        //参数初始化
        $conf = Configure::Conf('WeixinPay');
        $this->parameters = $conf['goods'];
        //初始化日志
        ini_set('date.timezone','Asia/Shanghai');
        $logHandler= new CLogFileHandler(ENTRYPHP_PATH.'/Library/Driver/Weixin/logs/'.date('Y-m-d').'.log');
        $log = Log::Init($logHandler, 15);
        $this->JsApiPay = new JsApiPay();
        $this->GetOpenid = $this->JsApiPay->GetOpenid();//页面需要刷新,然后获取微信的openID
        if(!isset($this->GetOpenid)){//防止刷新获取不到$_GET['code']
            $baseUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&isReboot=1';
            header("Location: $baseUrl");
        }
    }
    /**
     * 参数：金额，int数据类型
     * $SetOutTradeNo是商品订单号
     */
    public function pay($Money=0,$orderNumber=''){
        $this->parameters['Money'] = empty($Money)?$this->parameters['Money']:intval($Money);
        $input = new WxPayUnifiedOrder();
        if(empty($orderNumber)){
            $orderNumber = WxPayConfig::MCHID.date("YmdHis");
        }
        $input->SetBody($this->parameters['Body']);//商品详情（商品名称）（这个信息会自动发给客户，可以在客户的交易记录里面看到）
        $input->SetAttach($this->parameters['Attach']);//jockbrightness 附加备注，这个是备注的传值，异步回调可以接收到
        $input->SetOut_trade_no($orderNumber);//商品订单号；//默认填写：WxPayConfig::MCHID.date("YmdHis")
        $input->SetTotal_fee($this->parameters['Money']);//钱数
        $input->SetTime_start(date("YmdHis"));//20160914175348date("YmdHis")
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($this->parameters['Tag']);
        $input->SetNotify_url($this->parameters['NotifyUrl']);//处理异步回调的文件
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($this->GetOpenid);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $this->JsApiPay->GetJsApiParameters($order);
        //获取共享收货地址js函数参数
        $editAddress = $this->JsApiPay->GetEditAddressParameters();
        $payData['jsApiParameters'] = $jsApiParameters;
        $payData['Money'] = $input->GetTotal_fee();
        $payData['Order'] = $orderNumber;
        $this->payData = $payData;
        return $payData;//返回数组,其中包括钱数和jssdk支付的jsApiParameters参数,确定钱数和参数以后就可以执行show()方法显示并且支付.
    }
    /**
     * 微信参数修改，如果没有执行则会调用配置文件里面的配置信息
     * 参数：
     * $parameters['Body']商品详情（商品名称）（这个信息会自动发给客户，可以在客户的交易记录里面看到）
     * $parameters['Attach']附加备注，这个是备注的传值，异步回调可以接收到
     * $parameters['Money']钱数int类型
     * $parameters['Tag']标签
     * $parameters['NotifyUrl']处理异步回调的文件
     */
    public function data($parameters=array()){
        $this->parameters['Body'] = empty($parameters['Body'])?$this->parameters['Body']:$parameters['Body'];
        $this->parameters['Attach'] = empty($parameters['Attach'])?$this->parameters['Attach']:$parameters['Attach'];
        $this->parameters['Money'] = empty($parameters['Money'])?$this->parameters['Money']:intval($parameters['Money']);
        $this->parameters['Tag'] = empty($parameters['Tag'])?$this->parameters['Tag']:$parameters['Tag'];
        $this->parameters['NotifyUrl'] = empty($parameters['NotifyUrl'])?$this->parameters['NotifyUrl']:$parameters['NotifyUrl'];
        return $this;
    }
    /**
     * 微信用户唯一标示的openID,注意：这个openID和微信授权的openID不是一个值
     */
    public function getOpenId(){
        return $this->GetOpenid;
    }
    public function successUrl($url){
        $this->successJavaScript = 'window.location.href = "'.$url.'";';
        return $this;
    }
    /**
     * 参数：金额，int数据类型
     * $SetOutTradeNo是商品订单号
     */
    public function show($Money=0,$orderNumber=''){
        if(!empty($Money)){
            //如果有金额会自动调取微信的参数和微信返回过来的钱数,然后准备支付.
            $this->pay($Money,$orderNumber);
        }
        $MoneyUser = sprintf('%0.2f',$this->payData['Money']*0.01);
        $jsApiParameters = $this->payData['jsApiParameters'];
        $successJavaScript = $this->successJavaScript;
        $data = <<<STR
        <html>
        <head>
            <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1"/> 
            <title>微信支付-支付</title>
            <script type="text/javascript">
            //调用微信JS api 支付
            function jsApiCall()
            {
                WeixinJSBridge.invoke(			
                    'getBrandWCPayRequest',
                    {$jsApiParameters},
                    function(res){
                        WeixinJSBridge.log(res.err_msg);
                        /**jockbrightness 新增 判断返回值 然后跳转 begin**/
                        if(res.err_msg == "get_brand_wcpay_request:cancel"){
                            //alert("已经取消！");
                        }
                        if(res.err_msg == "get_brand_wcpay_request:ok"){
                            alert("付款成功！");
                            {$successJavaScript}
                        }
                        /**jockbrightness 新增 判断返回值 然后跳转 end**/
                        //alert(res.err_msg);
                        //alert(res.err_code+res.err_desc+res.err_msg);//NaNget_brand_wcpay_request:cancel
                    }
                );
            }
        
            function callpay()
            {
                if (typeof WeixinJSBridge == "undefined"){
                    if( document.addEventListener ){
                        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                    }else if (document.attachEvent){
                        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
                        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                    }
                }else{
                    jsApiCall();
                }
            }
            </script>
        </head>
        
        <body>
            <br/>
            <font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px">{$MoneyUser} </span>元</b></font><br/><br/>
            <div align="center">
                <button style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
            </div>
        </body>
        </html>
STR;
        echo $data;
    }
}
/**
 * 需要服务器配置两个地方:
 * 1.配置商户号的支付授权目录.位置:产品中心->开发配置->支付授权目录.填写例如:common.hlj.sina.com.cn/yuanshenhongbao/index.php/Index/testPay/order/
 * 2.配置对应的公众号的授权地址.位置:公众号设置->功能设置->网页授权域名.填写例如:common.hlj.sina.com.cn
 */
// 使用方法：
// $pay = new WeixinPay();
// $pay->show(1);
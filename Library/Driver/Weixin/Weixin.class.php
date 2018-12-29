<?php
/**
*   author:周健东
*   QQ:395154675
*/
class Weixin{
    private $appid = 'wxc75a2d8a1c6ba5d9';//默认是黑龙江新浪服务号的appid
    private $secret = '4668d2b8346380309e5779917508f3d9';//默认是黑龙江新浪服务号的secret
    public $url = 'http://common.hlj.sina.com.cn/kaxio/index.php';//回调地址获取code值
    private $accessToken = '';
    private $jsapiTicket = '';
    public function __construct($arr=''){
        if(!empty($arr['appid'])){
            $this->appid = $arr['appid'];
        }
        if(!empty($arr['secret'])){
            $this->secret = $arr['secret'];
        }
        if(!empty($arr['url'])){
            $strposurl = strpos($arr['url'],'http');
            if($strposurl===0){
                $this->url = $arr['url'];
            }else{
                $strposurldir = strpos($arr['url'],'/');
                if($strposurldir==false){
                    $this->url = 'http://common.hlj.sina.com.cn/'.$arr['url'].'/index.php';
                }else{
                    $this->url = 'http://common.hlj.sina.com.cn/'.$arr['url'];
                }
            }
        }
    }
    /**
     * 授权跳转
     * 授权成功返回用户信息数组
     * 这个方法需要$_GET['code']
     */
    public function getuser($debug=false){//$code是通过授权页面跳过来的携带的code值
        if(!empty($_GET['code'])){
            $errorMag = '';
            $code = $_GET['code'];
            if(!empty($this->appid)&&!empty($this->secret)){
                //通过授权页面跳过来的携带的code值，获取用户的access_token和openid
                $openid_str = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->secret."&code=".$code."&grant_type=authorization_code");
                //把json转换成array类型
                $json_openid_arr = json_decode($openid_str,true);
            }else{
                $errorMag .= 'appid或secret不存在！';
            }
            if(!empty($json_openid_arr['access_token'])&&!empty($json_openid_arr['openid'])){
                //通过access_token和openid获取用户的头像地址和昵称
                $user_content = file_get_contents('https://api.weixin.qq.com/sns/userinfo?access_token='.$json_openid_arr['access_token'].'&openid='.$json_openid_arr['openid'].'&lang=zh_CN');

                //把json转换成array类型
                $user_content_arr = json_decode($user_content,true);
            }
            

            //如果出现错误就重新授权一次或返回错误信息
            if(!empty($json_openid_arr['errcode'])){//$json_openid_arr['errcode']==40029
                $errorMag .= '微信返回错误信息：errcode：'.$json_openid_arr['errcode'].'!';
                //header('Location:'.$this->url.'?location=reboot&error='.$json_openid_arr['errcode']);
                //exit();
            }else{
                //判断是否获取到昵称和openid
                //查询数据库里面是否存在授权过来的昵称和openID
                if(!empty($user_content_arr['openid'])&&!empty($user_content_arr['nickname'])){
                    $user_content_arr['access_token_user'] = $json_openid_arr['access_token'];
                    return $user_content_arr;//返回昵称和openid，类型是array()
                }else{
                    //如果没有获取到昵称和openID的话页面重定向，从新获取授权页面的code携带值
                    $errorMag .= '没有获取到openid或者nickname！';
                    //header('Location:'.$this->url.'?location=reboot&error=emptyOpenid');
                    //exit();
                }
            }
            if(!empty($errorMag)){
                if($debug){
                    header('content-type:text/html;charset=utf-8');
                    echo $errorMag;
                }else{
                    if(strpos($this->url,'?')){
                        header('Location:'.$this->url.'&location=reboot');
                    }else{
                        header('Location:'.$this->url.'?location=reboot');
                    }
                }
                exit();
            }
        }else{
            $this->weixin_put();
        }
    }
    public function weixin_put(){
        $url = urlencode($this->url);
        header('Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect');
        exit();
    }
    /**
     * 返回服务号或订阅号的access_token
     * 注意：如果服务可以有缓存写入的话用下面的getAccessToken()方法获取access_token
     * @param string $appid_str 服务号或订阅号的appid
     * @param string $secret_str 服务号或订阅号的secret
     */
    public function access_token($appid_str='',$secret_str=''){//服务号或订阅号的access_token
        if(empty($appid_str)||empty($secret_str)){
            $appid = $this->appid;
            $secret = $this->secret;
        }else{
            $appid = $appid_str;
            $secret = $secret_str;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
        //echo $url;
        $token_json = file_get_contents($url);
        $access_token = json_decode($token_json,true);
        return $access_token['access_token'];//有效时间是7200秒
    }
    /**
     * 返回是否关注 返回1是关注，返回0是未关注
     * @param unknown $access_token 服务号或订阅号的access_token
     * @param unknown $openid    用户授权后的openid
     */
    public function is_attention($access_token,$openid){//是否关注  $access_token是服务号的access_token，$openid是用户的openid
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $attention_json = file_get_contents($url);
        $attention = json_decode($attention_json,true);
        return $attention['subscribe'];//返回为0的时候是没有关注，为1的时候说明已经关注
    }
    /**
     * 生成永久的二维码，扫码是带参数的关注（永久的二维码上限个数是10万个）
     * @param int $scene_id 参数携带值
     * @param string $access_token 微信服务号或订阅号的access_token
     */
    public function attention_img($scene_id,$access_token=''){//返回是一个二维码图片jpeg格式的
        if(empty($access_token)){
            $access_token = $this->access_token();
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
        $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        $ce = $this->curl_php_communication($url, $data);
        $ticket = json_decode($ce,true);
        $img_jpeg = file_get_contents('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket['ticket']);
        return $img_jpeg;//返回是一个二维码图片jpeg格式的
    }
    /**
     * 生成永久的二维码，扫码是带参数的关注（永久的二维码上限个数是10万个）
     * @param string $scene_str 参数携带值
     * @param string $access_token 微信服务号或订阅号的access_token
     */
    public function attention_str_img($scene_str,$access_token=''){//返回是一个二维码图片jpeg格式的
        if(empty($access_token)){
            $access_token = $this->access_token();
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
        $data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$scene_str.'"}}}';
        $ce = $this->curl_php_communication($url, $data);
        $ticket = json_decode($ce,true);
        $img_jpeg = file_get_contents('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket['ticket']);
        return $img_jpeg;//返回是一个二维码图片jpeg格式的
    }
    /**
     * 生成临时的二维码，扫码是带参数的关注（没有上限，但是单个有效时间最长是604800秒【7天】）
     * 特点是访问以下就会生成一个新的二维码，超过过期时间，就不会携带任何值了
     * @param int $scene_id 参数携带值
     * @param string $access_token 微信服务号或订阅号的access_token
     * @param string $expire_seconds='604800' 二维码的有效时间 默认是604800秒（7天）
     */
    public function attention_temporary_img($scene_id,$access_token='',$expire_seconds='604800'){//返回是一个二维码图片jpeg格式的
        if(empty($access_token)){
            $access_token = $this->access_token();
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
        $data = '{"expire_seconds": '.$expire_seconds.',"action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        $ce = $this->curl_php_communication($url, $data);
        $ticket = json_decode($ce,true);
        $img_jpeg = file_get_contents('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket['ticket']);
        return $img_jpeg;//返回是一个二维码图片jpeg格式的
    }
    /**
     * JSSDK 方法开始********************************************************************************************
     * 注意：需要手动创建两个文件：
     * 文件access_token.php文件内容：
        <?php exit();?>
        {"access_token":"","expire_time":0}
     * 文件jsapi_ticket.php文件内容：
        <?php exit();?>
        {"jsapi_ticket":"","expire_time":0}
     * 对位方法getSignPackage();
     * @param Array $indatearr
     * $indatearr['ticket']     代表获取到的JsApiTicket   时间周期是7200
     * $indatearr['accessToken']  代表服务号或订阅号的accessToken     时间周期是7200
     */
    public function getSignPackage($indatearr="",$url="") {
        if(!empty($indatearr['ticket'])){
            $jsapiTicket = $indatearr['ticket'];
        }else{
            $jsapiTicket = $this->getJsApiTicket($indatearr);
        }
        // 注意 URL 一定要动态获取，不能 hardcode.
        if(empty($url)){
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";   
        }

    
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
    
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    
        $signature = sha1($string);
    
        $signPackage = array(
                "appId"     => $this->appid,
                "nonceStr"  => $nonceStr,
                "timestamp" => $timestamp,
                "url"       => $url,
                "signature" => $signature,
                "rawString" => $string,
                "jsapiTicket" => $this->jsapiTicket,
                "accessToken" => $this->accessToken
        );
        return $signPackage;
    }
    
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    private function getJsApiTicket($indatearr="") {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("jsapi_ticket.php"));
        if ($data->expire_time < time()) {
            if(!empty($indatearr['accessToken'])){
//              $this->accessToken = $indatearr['accessToken'];
                $accessToken = $indatearr['accessToken'];
            }else{
                $accessToken = $this->getAccessToken();
                
            }
            //echo $accessToken;
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;     
            if ($ticket) {
                $this->jsapiTicket = $ticket;
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->set_php_file("jsapi_ticket.php", json_encode($data));
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }
        return $ticket;
    }
    
    private function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("access_token.php"));
        if ($data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appid&corpsecret=$this->secret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->secret";
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $this->accessToken = $access_token;
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $this->set_php_file("access_token.php", json_encode($data));
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }
    
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);
    
        $res = curl_exec($curl);
        curl_close($curl);
    
        return $res;
    }
    
    private function get_php_file($filename) {
        return trim(substr(file_get_contents($filename), 15));
    }
    private function set_php_file($filename, $content) {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }
    /**
     * JSSDK 方法结束********************************************************************************************
     */
    /*
     * 设置自定义菜单
     */
    public function set_menu_define($data='',$access_token=''){
        if(empty($access_token)){
            $access_token = $this->access_token();
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        if(empty($data)){
            $data = '{
                 "button":[
                  {
                       "name":"品质服务",
                       "sub_button":[
                       {
                           "type":"click",
                           "name":"投诉平台",
                           "key":"v001_expect"
                        },
                        {
                           "type":"view",
                           "name":"网点查询",
                           "url":"http://m.cmbchina.com/Branch/CmbSubbranchSearch.aspx?source=weixin"
                        },
                        {
                           "type":"view",
                           "name":"生活缴费",
                           "url":"http://wap.007ka.cn/cmb/index.php?version=wap"
                        },
                        {
                           "type":"view",
                           "name":"理财计算器",
                           "url":"http://m.cmbchina.com/Menu/DefaultSubMenu.aspx?submenu=cal"
                        },
                        {
                           "type":"click",
                           "name":"理财咨询",
                           "key":"v001_expect"
                        }]
                   },
                   {
                       "name":"活动特惠",
                       "sub_button":[
                        {
                           "type":"view",
                           "name":"扒粽子",
                           "url":"http://common.hlj.sina.com.cn/zhaoshangzongzi/index.php"
                        }]
                   },
                   {
                       "name":"业务办理",
                       "sub_button":[
                       {
                           "type":"view",
                           "name":"申请一卡通",
                           "url":"http://e95555.cn/ZQIqzF"
                        },
                        {
                           "type":"view",
                           "name":"申请信用卡",
                           "url":"http://market.cmbchina.com/ccard/wap/xyxykwap/index.html?from=singlemessage&isappinstalled=0"
                        },
                        {
                           "type":"click",
                           "name":"申请pos",
                           "key":"v001_expect"
                        },
                        {
                           "type":"view",
                           "name":"申请生意贷",
                           "url":"http://95555.cmbchina.com/cmbO2O/LoanApply.aspx?loantype=1&citycode=1105803&fromweb=01130001000000010003"
                        },
                        {
                           "type":"view",
                           "name":"申请消费贷",
                           "url":"http://common.hlj.sina.com.cn/xiaofeidai/index.php"
                        }]
                   }]
             }';
        }
        //成功返回：{"errcode":0,"errmsg":"ok"} 错误时的返回JSON数据包如下（示例为无效菜单名长度）：{"errcode":40018,"errmsg":"invalid button name size"}
        $source = $this->curl_php_communication($url, $data);
        $opj = json_decode($source);
        if($opj->errmsg=='ok'){
            return true;
        }else{
            echo $source;
            return false;
        }
    }
    
    private function curl_php_communication($url,$data){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
        $source = curl_exec($ch);
        curl_close($ch);
        return $source;
    }
}
    // $weixinuser = new weixinuser();
    // header('content-type:image/jpeg');
    // echo $weixinuser->attention_img('1314');
    // $getuser = $weixinuser->getuser();
    // header("Content-type:text/html;charset=utf-8");
    // echo '<pre>';
    // print_r($getuser);
    // echo '</pre>';
    // $weixinuser = new weixinuser();
    // $access_token = $weixinuser->access_token();
    // echo '<pre>';
    // print_r($access_token);
    // echo '</pre>';
    // $a = $weixinuser->getSignPackage();
    // echo '<pre>';
    // print_r($a);
    // echo '</pre>';
    // $weixin = new weixin();
    // $weixin->set_menu_define();
    // echo '<pre>';
    // echo 'success!';
    // echo '</pre>';
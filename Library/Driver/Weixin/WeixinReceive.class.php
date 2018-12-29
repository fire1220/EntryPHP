<?php
/**
  * 微信接收消息功能
  * 注意：需要配置微信，登录以后点击，基本配置-》服务器配置-》修改配置
  *   需要填写：
  *   URL：该文件所在的服务器的地址，例如http://common.hlj.sina.com.cn/weixintest/wx_sample.php
  *   Token：weixin（填写的内容必须和define("TOKEN", "weixin");中第二个参数一致）
  *   EncodingAESKey：随机生成就可以，为了加密的时候用的
  *   消息加解密方式：明文模式（明文的时候就不需要考虑EncodingAESKey参数了）
  * 配置完以后需要执行valid()方法来验证数据是否成功
  */
//define your token
define("TOKEN", "weixin");
class WeixinReceive
{
    private $get_ToUserName;//[接收时候的]目的地账号（开发者微信号）
    public $get_FromUserName;//[接收时候的]发送账号（  发送方帐号（一个OpenID））
    public $openId;//用户的openID,订阅号里面的，和授权得到的openID是不一样的。但对于用户是唯一的
    private $postObject;
    public $textContentStr = '';
    protected $textArr = array();
    public function __construct(){
      $this->valid();
      $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
      if(!empty($postStr)){
        $this->postObject = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->get_ToUserName = $this->postObject->ToUserName;
        $this->get_FromUserName = $this->postObject->FromUserName;
        $this->openId = $this->postObject->FromUserName;
      }
    }


    /**
     * 打通数据，在配置文件的时候需要通过这个方法，来检查数据是否验证成功
     * 验证成功以后，这个方法可以不需要执行，只有第一次验证的时候执行此方法，不然则验证失败
     * 注意：验证通过以后这个方法一定要禁用（一定不要执行这个方法），否则数据库传输的时候被阻碍。
     */
    public function valid()
      {
      if(!empty($_GET["echostr"])){
        $echoStr = $_GET["echostr"];
            if($this->checkSignature()){
              echo $echoStr;
              exit;
            }
      }
    }
/**
 * 关注自动回复字符串
 * 参数是字符串
 */
    public function autoText($textContentStr=''){
      $this->textContentStr = $textContentStr;
      return $this;
    }

/**
 * 关键词回复：（文本回复）
 * 参数数组，键是关键词，值是回复的字符串
 */
    public function textReply($textArr=array()){
      if(is_arraY($textArr)){
        $this->textArr = $textArr;
      }
      return $this;
    }

    /**
     * 接收信息处理函数
     *  初始化信息
     */
    public function init(){
      if(!empty($this->postObject)){
        $postObj = $this->postObject;
        $MsgType = $postObj->MsgType;
        switch ($MsgType){
          //输入文本类型执行
          case 'text':
            $this->ms_text();
            break;
          //输入图片类型执行
          case 'image':
            
            break;
          //输入音频类型执行
          case 'voice':
            
            break;
          //输入视频类型执行
          case 'video':
            
            break;
          case 'shortvideo':
            
            break;
          case 'location':
            
            break;
          case 'link':
            
            break;
          //事件类型执行
          case 'event':
            $Event = $postObj->Event;
            switch ($Event){
              //成功关注事件以后执行：
              case 'subscribe':
                $source['EventKey'] = $this->ms_subscribe($postObj);
                return $source;
                break;
              //取消关注事件以后执行
              case 'unsubscribe':
                
                break;
              case 'SCAN':
                
                break;
              case 'LOCATION':
                
                break;
              case 'CLICK':
                $this->ms_click($postObj);
                break;
              case 'VIEW':
                
                break;
            }
            break;
        }
      }
    }
    /**
    ****************************************消息调用方法begin**********************************************************
    */
    /**
     * 关注后执行
     * 二维码关注后获取二维码携带的数值。返回扫码关注后的状态
     * 有两种情况：
     * 1、该用户未关注的时候：
     *    返回qrscene_加携带的值
     * 2、该用户目前已经关注过了，然后又扫二维码关注的时候：
     *    直接返回携带的值
     * 通过关注执行的方法。
     * @param opject $postObj 收到的资源对象
     */
    private function ms_subscribe($postObj){
      //$contentStr = '尊敬的客户您好，欢迎您关注招商银行哈尔滨分行微信公众号。招商银行因您而变！';
      //$contentStr = '激情夏日，招行大奖来袭！猛戳☞<a href="http://common.hlj.sina.com.cn/zhaohangchigua/index.php" >这里</a>即可前往参与游戏赢取好礼！[愉快]';
      if(!empty($this->textContentStr)){
        $contentStr = $this->textContentStr;
      }else{
        //$contentStr = '1136招商银行邀您超级抽大奖！猛戳☞<a href="http://common.hlj.sina.com.cn/zhaohangbaoxiang" >这里</a>即可前往参与游戏赢取好礼！[愉快]';
      }
      $this->reply_text($contentStr);
      if(!empty($postObj->EventKey)){
        $EventKey = $postObj->EventKey;
        return $EventKey;//返回的数据分连部分，前缀qrscene_加二维码携带的参数，例如qrscene_1321，其中1321就是在创建二维码的时候携带的参数
      }else{
        return false;
      }
    }
    /**
     * 通过自定义菜单点击事件，执行的方法
     * @param opject $postObj 收到的资源对象
     */
    private function ms_click($postObj){
      //判断是否存在返回关键词KEY
      if(!empty($postObj->EventKey)){
        $EventKey = $postObj->EventKey;
        //通过检测返回关键词KEY来分别对应操作
        if($EventKey=="v001_expect"){
          $contentStr = '微信功能即将推出,敬请期待...';
          $this->reply_text($contentStr);
        }elseif($EventKey=="v001_expect_img"){
          $this->reply_image();
          //$contentStr = '微信功能即将推出,敬请期待...';
          //$this->reply_text($contentStr);
        }
      }
    }
    /**
     * 输入文本后处理的方法
     * @param object $postObj 收到的资源对象
     */
    private function ms_text(){
      $postObj = $this->postObject;
      $textArr = $this->textArr;
      if(trim($postObj->Content)){//接收到的内容
        foreach($textArr as $key=>$val){
          if($postObj->Content==$key){
            $this->reply_text($val);
          }
        }
      }
//
//      if(trim($postObj->Content)){
//        $contentStr = $postObj->Content;//接收到的内容
//        $str = "尊敬的客户您好，欢迎您关注招商银行哈尔滨分行微信公众号。招商银行因您而变！";
//        if($contentStr=='余额'||$contentStr=='查询余额'||$contentStr=='余额查询'){
//          $str = '一卡通余额查询请点击☞<a href="https://mobile.cmbchina.com/MobileHtml/Login/LoginA.aspx?FJID=agWKjzmEbfA_" >这里</a>';
//        }elseif($contentStr=='理财咨询'||$contentStr=='理财查询'||$contentStr=='理财'){
//          $str = '招商银行最新理财咨询请点击☞<a href="http://common.hlj.sina.com.cn/zhaoshangerweima/image.html" >这里</a>';
//        }elseif($contentStr=='投诉'||$contentStr=='我要投诉'){
//          $str = '投诉平台请点击☞<a href="http://common.hlj.sina.com.cn/zhaoshangtousu/index.php" >这里</a>';
//        }elseif($contentStr=='办卡进度'||$contentStr=='进度'||$contentStr=='进度查询'){
//          $str = '进度查询请点击☞<a href="https://ccclub.cmbchina.com/mca/MQuery.aspx?WT.mc_id=Z1O00WXA055B412100CC&undefined=&WT.refp=%2Fcard%2Fqueryweixin%2Fquery%24" >这里</a>';
//        }
//
//        // if($contentStr=='aaa'){
//        //   $this->ms_image_text();
//        // }
//
//        //$this->reply_text($str);
//      }
    }
    private function ms_image_text(){
      $contentArray = array(
        '0' => array(
          'title1' => '图文消息标题c1', 
          'description1' => '图文消息描述c1', 
          'picurl' => 'http://common.hlj.sina.com.cn/zhaohangzhanpantwo/pic300.jpg', 
          'url' => 'http://www.baidu.com', 
          ),
        '1' => array(
          'title1' => '图文消息标题c2', 
          'description1' => '图文消息描述c2', 
          'picurl' => 'http://common.hlj.sina.com.cn/zhaohangzhanpantwo/pic300.jpg', 
          'url' => 'http://www.baidu.com', 
          ),
        '2' => array(
          'title1' => '图文消息标题c3', 
          'description1' => '图文消息描述c3', 
          'picurl' => 'http://common.hlj.sina.com.cn/zhaohangzhanpantwo/pic300.jpg', 
          'url' => 'http://www.baidu.com', 
          ),
        '3' => array(
          'title1' => '图文消息标题c4', 
          'description1' => '图文消息描述c4', 
          'picurl' => 'http://common.hlj.sina.com.cn/zhaohangzhanpantwo/pic300.jpg', 
          'url' => 'http://www.baidu.com', 
          ),
      );
      $this->reply_image_text($contentArray);
    }
    /**
    ****************************************消息调用方法end**********************************************************
    */

    /**
    ******************************************消息处理方法begin***************************************************
    */
    /**
     * 回复文本消息
     * @param string $contentStr 回复的文本内容
     */
    public function reply_text($contentStr){
      $fromUsername = $this->get_FromUserName;
      $toUsername = $this->get_ToUserName;
      $time = time();
      $textTpl = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <Content><![CDATA[%s]]></Content>
          </xml>";
      $msgType = "text";
      $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
      echo $resultStr;
    }
    
    /**
     * 回复图片消息
     * 
     */
    private function reply_image(){
      $fromUsername = $this->get_FromUserName;
      $toUsername = $this->get_ToUserName;
      $time = time();
      $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <MediaId><![CDATA[media_id]]></MediaId>
            <MsgId>%s</MsgId>
            </xml>";
      $msgType = "image";
      $picUrl = 'https://mmbiz.qlogo.cn/mmbiz/ibmeN1Ehibzop8uAgz4ic3nAyUBBNFVgncwHC7eWfHUP8dFhYSdBUYp8KiaIQ4oLAAAYKicgFpU1By07Y3jIQWS2oyw/0?wx_fmt=jpeg';
      $mediaId = '';
      $msgId = '123456789';
      $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType,$picUrl);
      echo $resultStr;
    }
    /**
     * 回复图文消息
     * $contentArray = array(
     *    'title1' => '图文消息标题',
     *    'description1' => '图文消息描述',
     *    'picurl' => '',//图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200
     *    'url' => '',//点击图文消息跳转链接
     *  );
     * $contentArray = array(
     *    0 => array(
     *          'title1' => '',
     *          'description1' => '',
     *          'picurl' => '',
     *          'url' => '',
     *        ),
     *    1 => array(
     *          'title1' => '',
     *          'description1' => '',
     *          'picurl' => '',
     *          'url' => '',
     *        ),
     *  );
     */
    private function reply_image_text($contentArray){
      $fromUsername = $this->get_FromUserName;
      $toUsername = $this->get_ToUserName;
      $time = time();
      $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <ArticleCount>%d</ArticleCount>
            <Articles>";
      $i = 0;
      if(!empty($contentArray['title1'])){
        $i++;
          $textTpl .= "<item>
              <Title><![CDATA[{$contentArray['title1']}]]></Title> 
              <Description><![CDATA[{$contentArray['description1']}]]></Description>
              <PicUrl><![CDATA[{$contentArray['picurl']}]]></PicUrl>
              <Url><![CDATA[{$contentArray['url']}]]></Url>
              </item>";
      }else{
        foreach ($contentArray as $val) {
          $i++;
          if(is_array($val)){ 
              $textTpl .= "<item>
                <Title><![CDATA[{$val['title1']}]]></Title> 
                <Description><![CDATA[{$val['description1']}]]></Description>
                <PicUrl><![CDATA[{$val['picurl']}]]></PicUrl>
                <Url><![CDATA[{$val['url']}]]></Url>
                </item>";
            }
        }
      }

      $textTpl .= "</Articles>
            </xml> ";    
      $message_arr = array();
      $message_arr[] = $fromUsername;//接收方帐号（收到的OpenID） 
      $message_arr[] = $toUsername;//   开发者微信号 
      $message_arr[] = $time;//   消息创建时间 （整型） 
      $message_arr[] = 'news';//  news 
      $message_arr[] = $i;//  图文消息个数，限制为10条以内  
      $resultStr = vsprintf($textTpl, $message_arr);
      echo $resultStr;
    }
    /**
    ******************************************消息处理方法end***************************************************
    */
  /**
   * 私有方法，判断是否被认证
   * @return boolean
   */ 
  private function checkSignature()
  {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];  
    $token = TOKEN;
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr);
    $tmpStr = implode( $tmpArr );
    $tmpStr = sha1( $tmpStr );
    if( $tmpStr == $signature ){
      return true;
    }else{
      return false;
    }
  }
}
/**
 * 使用方法：
 */
// define("TOKEN", "weixin");
// $WxSample = new WeixinReceive();
// $WxSample->autoText('欢迎关注浦发银行公众号，<a href="http://common.hlj.sina.com.cn/pufafenxianghongbao/index.php?c=Index&a=index&code='.$WxSample->openId.'">点击领取红包</a>');
// $WxSample->init(); //初始化消息，用来自动回复时候使用

/**
 * 案例（开始）：
 * 用二维码扫码关注，然后区分是通过哪个二维码关注过来的，并且写到数据库里面
 * 如果已经关注过了，则不做任何操作
 */
// if(!empty($source['EventKey'])){
//   $attention = $source['EventKey'];
//   if(strstr($attention, 'qrscene_')){
//     $attention_id = intval(ltrim($attention,'qrscene_'));
//     if(!empty($attention_id)){//获取二维码关注以后携带的值，并且将其写入数据库里面
//       $erweima = M('hlj_zhaohang_erweima');
//       $where_erweima['bank_id'] = $attention_id;
//       $find_erweima = $erweima->where($where_erweima)->find();
//       if(!empty($find_erweima)){
//         $data['status'] = "1";
//         $data['number'] = array('+','1');
//         $data['create_time'] = time();
//         $save = $erweima->data($data)->where($where_erweima)->save();
//       }else{
//         $data['bank_id'] = $attention_id;
//         $data['description'] = $attention;
//         $data['number'] = "1";
//         $data['status'] = "1";
//         $data['create_time'] = time();
//         $add = $erweima->data($data)->add();
//       }
//     }
//     echo 'success';
//   }
// }
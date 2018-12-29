<?php
/**
 * SinaLeopardService
 * 
 * @author: yifan2@staff.sina.com.cn
 * @date: 2011-06-28
 * @version: 0.3 Beta
 */


/**
 *require needed package
 */

$GLOBALS['THRIFT_ROOT'] = '/usr/local/sinasrv2/lib/php/thrift';
require_once($GLOBALS['THRIFT_ROOT'] . '/scribe.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TSocket.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TFramedTransport.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php');
require_once('SinaService/SinaService.php');


class SinaLeopardService extends SinaServiceException 
{

    /**
     * My AccessKey.
     * @const
    */
       const ENC_ACCESS_KEY = '100eb43d749fff295e96734eb188b429';

    /**
     * SinaService AccessKey
     *
     * @var mixed
     */
        var $_AccessKey = null;

    /**
     * SinaService SecretKey
     *
     * @var mixed
     */
        var $_SecretKey = null;

    /**
     * Constructor.
     *
     * @namespace string $params  Namespace prepend to key.
     * @cluster string $params    Cluster to use.
     */
        function __construct($accesskey, $secretkey='') {
                new SinaService(__CLASS__);
                if(md5($accesskey) !== self::ENC_ACCESS_KEY) {
                    //throw new SinaServiceException("AccessKey Invalid.");
                    $this->_AccessKey = null;
                }else{
                    $this->_AccessKey = $accesskey;
                    $this->_SecretKey = $secretkey;
                }
        }
        
        private function checkAccessKey(){
           if(md5($this->_AccessKey) !== self::ENC_ACCESS_KEY) {
               throw new SinaServiceException("AccessKey Invalid.");
           }                 
        }

    /**
     *check input string length: < 5000B
     */
    private function checkNum($inputdata) {
	$messbody=$inputdata;
        $number = strlen($messbody);
        if ($number > 5000) {
            throw new SinaServiceException('input string is more than 5000B');
        }else{
            return $messbody;
        }
        }



    public function sendMessage($category,$message) {
    /**
     *send message through scribe api
     */
        try {
            if ($category == array("share_win"=>"default")){
                $localip = $_SERVER["SERVER_ADDR"];
                $timestamp = date("Y-m-d H:i:s",time());
                $catego = array_keys($category);
                $category = $catego[0];
                $message = $this->checkNum($message);
                $msg1['category'] = $this->checkNum($category);
                $msg1['message'] = $timestamp . "\t" . $localip . "\t" . $message;
            }else{
                $this->checkAccessKey();
                $message = $this->checkNum($message);
                $msg1['category'] = $this->checkNum($category);
                $msg1['message'] = $message;
            }
            $entry1 = new LogEntry($msg1);
            $messages = array($entry1);
            $socket = new TSocket('localhost', 1464, true);
            $transport = new TFramedTransport($socket);
            $protocol = new TBinaryProtocol($transport, false, false);
            $scribe_client = new scribeClient($protocol, $protocol);
            $transport->open();
            $scribe_client->Log($messages);
            $transport->close();
            return $msg1['message'];
        }catch(Exception $e){
            return $e->getMessage();
        }
        }
}
?>

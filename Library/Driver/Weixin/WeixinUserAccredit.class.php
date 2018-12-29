<?php
/**
*   author:周健东
*   QQ:395154675
*/
class WeixinUserAccredit {
    private $appid = ''; // 微信授权的appid
    private $secret = ''; // 微信授权的秘钥secret
    private $DomainName = '';//微信授权域名
    private $DirName = ''; // 返回的地址的目录名称
    private $DbName = ''; // 数据库表名
    private $MRead = null;
    private $MWrite = null;
    private $weixinObj = null;
    private $DB_id = ''; // 对应数据库字段
    private $DB_openid = ''; // 对应数据库字段
    private $DB_nickname = ''; // 对应数据库字段
    private $DB_headimgurl = ''; // 对应数据库字段
    private $DB_create_time = ''; // 对应数据库字段
    private $DB_status = ''; // 对应数据库字段
    private $COOKIE_uid = ''; // 对应存储的COOKIE名称
    private $COOKIE_openid = ''; // 对应存储的COOKIE名称
    public $columns = array (); // 字段默认写入的值
    /**
     *
     * @param string $DirName
     *          返回的地址目录名称
     * @param string $DbName
     *          数据库表名
     * @param array $ExtendArr
     *          扩展参数数组例如：appid和secret还有数据库字段
     */
    public function __construct($DbName, $DirName, $ExtendArr = array()) {
        $this->init();
        $this->fieldData($ExtendArr);
        $this->DbName = $DbName;
        if(empty($DirName)){
            $this->DirName = PHP_SELF;
        }else{
            $this->DirName = $DirName;
        }
        $this->MRead = M($this->DbName);
        $this->MWrite = M($this->DbName);
        $weixinarr = array (
            'appid' => $this->appid, // 
            'secret' => $this->secret // 
        ) ;
        $dirname = $this->DirName; // 微信授权返回的目录
        if (! empty ( $_GET ['source'] )) {
            $weixinarr ['url'] = $this->DomainName.$dirname . '/index.php?source=' . $_GET ['source'];
        } else {
            $weixinarr ['url'] = $this->DomainName.$dirname;
        }
        $this->weixinObj = new Weixin ( $weixinarr );
    }
    private function init(){
        $configure = Configure::Conf('WeixinUserAccredit');
        $this->appid = $configure['WeixinConf']['appid'];
        $this->secret = $configure['WeixinConf']['secret'];
        $this->DomainName = $configure['WeixinConf']['DomainName'];
        $this->DB_id = $configure['DbField']['DB_id'];
        $this->DB_openid = $configure['DbField']['DB_openid'];
        $this->DB_nickname = $configure['DbField']['DB_nickname'];
        $this->DB_headimgurl = $configure['DbField']['DB_headimgurl'];
        $this->DB_create_time = $configure['DbField']['DB_create_time'];
        $this->DB_status = $configure['DbField']['DB_status'];
        $this->COOKIE_uid = $configure['CookieField']['COOKIE_uid'];
        $this->COOKIE_openid = $configure['CookieField']['COOKIE_openid'];
        $this->columns = $configure['DefaultValue'];
    }
    private function fieldData($ExtendArr){
        if (! empty ( $ExtendArr ['appid'] )) {
            $this->appid = $ExtendArr ['appid'];
            unset ( $ExtendArr ['appid'] );
        }
        if (! empty ( $ExtendArr ['secret'] )) {
            $this->secret = $ExtendArr ['secret'];
            unset ( $ExtendArr ['secret'] );
        }
        if (! empty ( $ExtendArr ['DB_id'] )) {
            $this->DB_id = $ExtendArr ['DB_id'];
            unset ( $ExtendArr ['DB_id'] );
        }
        if (! empty ( $ExtendArr ['DB_openid'] )) {
            $this->DB_openid = $ExtendArr ['DB_openid'];
            unset ( $ExtendArr ['DB_openid'] );
        }
        if (! empty ( $ExtendArr ['DB_nickname'] )) {
            $this->DB_nickname = $ExtendArr ['DB_nickname'];
            unset ( $ExtendArr ['DB_nickname'] );
        }
        if (! empty ( $ExtendArr ['DB_headimgurl'] )) {
            $this->DB_headimgurl = $ExtendArr ['DB_headimgurl'];
            unset ( $ExtendArr ['DB_headimgurl'] );
        }
        if (! empty ( $ExtendArr ['DB_create_time'] )) {
            $this->DB_create_time = $ExtendArr ['DB_create_time'];
            unset ( $ExtendArr ['DB_create_time'] );
        }
        if (! empty ( $ExtendArr ['DB_status'] )) {
            $this->DB_status = $ExtendArr ['DB_status'];
            unset ( $ExtendArr ['DB_status'] );
        }
        if (! empty ( $ExtendArr ['COOKIE_uid'] )) {
            $this->COOKIE_uid = $ExtendArr ['COOKIE_uid'];
            unset ( $ExtendArr ['COOKIE_uid'] );
        }
        if (! empty ( $ExtendArr ['COOKIE_openid'] )) {
            $this->COOKIE_openid = $ExtendArr ['COOKIE_openid'];
            unset ( $ExtendArr ['COOKIE_openid'] );
        }
        if(!empty($ExtendArr)){
            $this->columns = $ExtendArr;
        }
    }
    /**
     * 返回数组：$userMessage = array('uid'=>$uid,'openid'=>$openid);
     */
    public function UserAccredit($debug = false) {
        if ($debug && ! empty ( $_GET ['location'] )) {
            exit ( $_GET ['location'] );
        }
        if (! empty ( $this->columns [$this->DB_status] )) {
            $where [$this->DB_status] = $this->columns [$this->DB_status];
        } else {
            $where [$this->DB_status] = 1;
        }
        if (empty ( $_COOKIE [$this->COOKIE_uid] ) || empty ( $_COOKIE [$this->COOKIE_openid] )) {
            $getuser = $this->weixinObj->getuser ( $debug );
            $where [$this->DB_openid] = $getuser ['openid'];
            $find_user = $this->MRead->where ( $where )->find ();
            if (empty ( $find_user [$this->DB_id] )) {
                $data [$this->DB_create_time] = time ();
                $data [$this->DB_status] = 1;
                if (is_array ( $this->columns )) {
                    foreach ( $this->columns as $key => $val ) {
                        $data [$key] = $val;
                    }
                }
                $data [$this->DB_openid] = $getuser ['openid'];
                $data [$this->DB_nickname] = $getuser ['nickname'];
                $data [$this->DB_headimgurl] = $getuser ['headimgurl'];
                $add = $this->MWrite->data ( $data )->add ();
                if ($add) {
                    setcookie ( $this->COOKIE_uid, $add, time () + 3600 * 24 * 30 ); // 保存用户ID到cookie里面保存周期是一个月
                    $uid = $add;
                }
            } else {
                setcookie ( $this->COOKIE_uid, $find_user [$this->DB_id], time () + 3600 * 24 * 30 ); // 保存用户ID到cookie里面保存周期是一个月
                $uid = $find_user [$this->DB_id];
            }
            setcookie ( $this->COOKIE_openid, $getuser ['openid'], time () + 3600 * 24 * 30 ); // 保存用户openid到cookie里面保存周期是一个月
            $openid = $getuser ['openid'];
        } else {
            // 检测cookie里面的数据是否有效,如果数据库里面没有查询到cookie信息，需要重新授权（把cookie里面的值清除掉就会从新授权的），主要是为了做测试用
            $openid = $_COOKIE [$this->COOKIE_openid]; // 赋值openid
            $uid = $_COOKIE [$this->COOKIE_uid]; // 赋值uid
            $where [$this->DB_id] = $uid;
            $where [$this->DB_openid] = $openid;
            $find_user = $this->MRead->where ( $where )->find ();
            if (! $find_user [$this->DB_id]) {
                setcookie ( $this->COOKIE_uid, '', time () - 3600 ); // 保存用户ID到cookie里面保存周期是一个月
                setcookie ( $this->COOKIE_openid, '', time () - 3600 ); // 保存用户ID到cookie里面保存周期是一个月
                header ( 'Location:' . $this->weixinObj->url );
                exit ();
            }
        }
        $userMessage = array ();
        $userMessage ['uid'] = $uid;
        $userMessage ['openid'] = $openid;
        if(!empty($find_user)){
            $userMessage ['info'] = $find_user;
        }
        return $userMessage;
    }

    public function UserAccreditSession($debug = false) {
        if ($debug && ! empty ( $_GET ['location'] )) {
            exit ( $_GET ['location'] );
        }
        if (! empty ( $this->columns [$this->DB_status] )) {
            $where [$this->DB_status] = $this->columns [$this->DB_status];
        } else {
            $where [$this->DB_status] = 1;
        }
        if (empty ( $_SESSION[$this->COOKIE_uid] ) || empty ( $_SESSION[$this->COOKIE_openid] )) {
            $getuser = $this->weixinObj->getuser ( $debug );
            $where [$this->DB_openid] = $getuser ['openid'];
            $find_user = $this->MRead->where ( $where )->find ();
            if (empty ( $find_user [$this->DB_id] )) {
                $data [$this->DB_create_time] = time ();
                $data [$this->DB_status] = 1;
                if (is_array ( $this->columns )) {
                    foreach ( $this->columns as $key => $val ) {
                        $data [$key] = $val;
                    }
                }
                $data [$this->DB_openid] = $getuser ['openid'];
                $data [$this->DB_nickname] = $getuser ['nickname'];
                $data [$this->DB_headimgurl] = $getuser ['headimgurl'];
                $add = $this->MWrite->data ( $data )->add ();
                if ($add) {
                    //setcookie ( $this->COOKIE_uid, $add, time () + 3600 * 24 * 30 ); // 保存用户ID到cookie里面保存周期是一个月
                    $_SESSION[$this->COOKIE_uid] = $add;
                    $uid = $add;
                }
            } else {
                //setcookie ( $this->COOKIE_uid, $find_user [$this->DB_id], time () + 3600 * 24 * 30 ); // 保存用户ID到cookie里面保存周期是一个月
                $_SESSION[$this->COOKIE_uid] = $find_user[$this->DB_id];
                $uid = $find_user [$this->DB_id];
            }
            //setcookie ( $this->COOKIE_openid, $getuser ['openid'], time () + 3600 * 24 * 30 ); // 保存用户openid到cookie里面保存周期是一个月
            $_SESSION[$this->COOKIE_openid] = $getuser['openid'];
            $openid = $getuser ['openid'];
        } else {
            // 检测cookie里面的数据是否有效,如果数据库里面没有查询到cookie信息，需要重新授权（把cookie里面的值清除掉就会从新授权的），主要是为了做测试用
            //$openid = $_COOKIE [$this->COOKIE_openid]; // 赋值openid
            //$uid = $_COOKIE [$this->COOKIE_uid]; // 赋值uid
            $openid = $_SESSION[$this->COOKIE_openid]; // 赋值openid
            $uid = $_SESSION[$this->COOKIE_uid]; // 赋值uid
            $where [$this->DB_id] = $uid;
            $where [$this->DB_openid] = $openid;
            $find_user = $this->MRead->where ( $where )->find ();
            if (! $find_user [$this->DB_id]) {
                //setcookie ( $this->COOKIE_uid, '', time () - 3600 ); // 保存用户ID到cookie里面保存周期是一个月
                //setcookie ( $this->COOKIE_openid, '', time () - 3600 ); // 保存用户ID到cookie里面保存周期是一个月
                unset($_SESSION[$this->COOKIE_uid]);
                unset($_SESSION[$this->COOKIE_openid]);
                header ( 'Location:' . $this->weixinObj->url );
                exit ();
            }
        }
        $userMessage = array ();
        $userMessage ['uid'] = $uid;
        $userMessage ['openid'] = $openid;
        if(!empty($find_user)){
            $userMessage ['info'] = $find_user;
        }
        return $userMessage;
    }
}
/**
 * 案例：
 */
// header("Content-type:text/html;charset=utf-8");
// $WeixinUserAccredit = new WeixinUserAccredit('hlj_hanandianliang_user');
// $userinfo = $WeixinUserAccredit->UserAccredit();
// $uid = $userinfo['uid'];

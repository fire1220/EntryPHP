<?php
/**
*   author:周健东
*   QQ:395154675
*/
    return array(
    //     'db'    => array(
    //         'host'  =>  '127.0.0.1',
    //         'port'  =>  '3306',
    //         'username'  =>  'root',
    //         'password'  =>  '',
    //         'database'  =>  'work' 
    //     ),
    //     //读写分离中的读取的数据库
    //    'dbRead' => array(
    //         'host'  =>  '',
    //         'port'  =>  '',
    //         'username'  =>  '',
    //         'password'  =>  '',
    //         'database'  =>  ''
    //    ),
    //    //读写分离中的写入的数据库
    //    'dbWrite' => array(
    //         'host'  =>  '',
    //         'port'  =>  '',
    //         'username'  =>  '',
    //         'password'  =>  '',
    //         'database'  =>  ''
    //    ),
    //    'prefix' => '',//表前缀

        'WeixinUserAccredit'  =>    array(
            //新浪黑龙江服务号
            'WeixinConf'  =>  array(
                'appid'  =>  'wxc75a2d8a1c6ba5d9',//微信授权的AppId.位置:登录微信公众平台->开发->基本配置->公众号开发信息->开发者ID(AppID)
                'secret'  =>  '4668d2b8346380309e5779917508f3d9',//微信授权的秘钥secret.配置位置:登录微信公众平台->开发->基本配置->公众号开发信息->开发者密码(AppSecret)
                'DomainName'   =>  'http://common.hlj.sina.com.cn/',//微信授权域名，注意结尾一定要加/，例如：http://common.hlj.sina.com.cn/
            ),
            // 对应数据库字段
            'DbField'   =>  array(
                'DB_id' =>  'id',//自增的字段，例如id
                'DB_openid' =>  'openid',//存放openid的字段
                'DB_nickname' =>  'nickname',//存放nickname的字段
                'DB_headimgurl' =>  'headimgurl',//存放headimgurl的字段
                'DB_create_time' =>  'create_time',//存放create_time时间的字段
                'DB_status' =>  'status',//存放status状态的字段
            ),
            // 插入数据默认值'字段'=>'值'
            'DefaultValue'  =>  array(
                'status' =>  '1'
            ),
            // 对应存储的COOKIE名称
            'CookieField'  =>  array(
                'COOKIE_uid'    =>  'uid',//插入数据库后，返回的自增id值
                'COOKIE_openid'    =>  'openid',//openID值
            ),
        ),
        // 'WeixinPay'  =>  array(
        //     'goods' => array(    //微信支付的相关参数
        //         'Body' => '元申广电年会红包',//商品详情（商品名称）（这个信息会自动发给客户，可以在客户的交易记录里面看到）
        //         'Attach' => '元申广电年会红包',//附加备注，这个是备注的传值，异步回调可以接收到
        //         'Tag' => '元申广电年会',//标签
        //         'NotifyUrl' => 'http://common.hlj.sina.com.cn/yuanshenhongbao/index.php',//处理异步回调的文件,不支持参数
        //     ),
        //     'systemConf' => array(   //红包或微信支付的参数
        //         'APPID' => 'wxc75a2d8a1c6ba5d9',//微信授权的AppId.位置:登录微信公众平台->开发->基本配置->公众号开发信息->开发者ID(AppID)
        //         'MCHID' => '1260555201',//微信的商户号,例如:新浪黑龙江商户号: 1260555201 ,丑鱼是： 1442506202.位置:登录商户平台->账户中心->商户信息->微信支付商户号
        //         'KEY' => '23212619820401275X23212619820401',//在商户平台里面设置.位置:登录商户平台->账户中心->API安全->设置API秘钥
        //         'APPSECRET' => '4668d2b8346380309e5779917508f3d9',//公众平台的AppSecret，如果填写错误，支付时openID会返回空,配置位置:登录微信公众平台->开发->基本配置->公众号开发信息->开发者密码(AppSecret)
        //         'NOTIFY_URL' => '',//http://common.hlj.sina.com.cn/yuanshenhongbao/notify.php
        //         'CURL_PROXY_HOST' => '0.0.0.0',
        //         'CURL_PROXY_PORT' => 0,
        //         'REPORT_LEVENL' => 1,
        //     ),
        // ),
    );

<?php
$conf = Configure::Conf('WeixinPay');
$systemConf = $conf['systemConf'];
define('WX_APPID',$systemConf['APPID']);
define('WX_MCHID',$systemConf['MCHID']);
define('WX_KEY',$systemConf['KEY']);
define('WX_APPSECRET',$systemConf['APPSECRET']);
define('WX_NOTIFY_URL',$systemConf['NOTIFY_URL']);
define('WX_SSLCERT_PATH',ENTRYPHP_PATH.'/Library/Driver/Weixin/cert/apiclient_cert.pem');
define('WX_SSLKEY_PATH',ENTRYPHP_PATH.'/Library/Driver/Weixin/cert/apiclient_key.pem');
define('WX_CURL_PROXY_HOST',$systemConf['CURL_PROXY_HOST']);
define('WX_CURL_PROXY_PORT',$systemConf['CURL_PROXY_PORT']);
define('WX_REPORT_LEVENL',$systemConf['REPORT_LEVENL']);

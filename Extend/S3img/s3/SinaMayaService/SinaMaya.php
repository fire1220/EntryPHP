<?php
require_once('SinaService/SinaService.php');
class SinaMaya extends SinaServiceException
{
    const ACCESS_KEY = '666512df2eb917268b4ca5c6e8d755e8';
    
    private $host;
    private $port;
    private $timeout;
    private $err;
    function __construct($host,$port,$timeout,$accesskey, $secretkey='')
    {
    		new SinaService(__CLASS__);
        if($accesskey !== self::ACCESS_KEY) {
        		throw new SinaServiceException("AccessKey Invalid.\n");
        }
				//-------------------------------------------------------------------
        if(!extension_loaded ("fastmbclient"))
        {
            dl("fastmbclient.so");
        }
        $this->host=$host;
        $this->port=$port;
        $this->timeout=$timeout;
    }

    function Execute($insertsql, $tablename, $hashvalue, &$lastid,&$rows)
    {
        $this->err="";
        $ret=mbexecute($this->timeout,$this->host,$this->port,$insertsql, $tablename, $hashvalue);
        if($ret["success"])
        {
            $lastid=$ret["lastid"];
            $rows=$ret["affnum"];
            return true;
        }
        else
        {
            $this->err=$ret["error"];
            return false;
        }
    }
    
    function Query($selectsql, $tablename, $hashvalue, $cachetimeout, &$strresult)
    {
        $this->err="";
        $ret=mbquery($this->timeout,$this->host,$this->port,$selectsql, $tablename, $hashvalue);
        if($ret["success"])
        {
            $strresult=$ret["result"];
            return true;
        }
        else
        {
            $this->err=$ret["error"];
            return false;
        }
    }
    
    function BatchInsert($tablename, $data, $hashindex, &$rows)
    {
        $this->err="";
        $ret=mbbatchinsert($this->timeout,$this->host,$this->port,$tablename, $data, $hashindex);
        if($ret["success"])
        {
            $rows=$ret["affnum"];
            return true;
        }
        else
        {
            $this->err=$ret["error"];
            return false;
        }
    }
    
    function GetErr()
    {
        return $this->err;
    }
}
?>

<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements. See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership. The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

//error_reporting(E_ALL);
include_once("PEAR.php");
include_once("SinaService/SinaService.php");

class Node {
	var $ip = "";
	var $port = 0;
	var $role = "";
}


/**
 * SinaCombService API for abstracted distributed cache and access.
 *
 * Copyright 2011-2012 Han Fang <terryfe@gmail.com>
 *
 * See the enclosed file COPYING for license information (LGPL). If you did
 * not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Han Fang <terryfe@gmail.com>
 * @package SinaCombService
 */
class SinaCombService extends SinaService {
    /**
     * Operation options for set/get/add etc..
     *
     * @const 
     */
	const OPT_REPLICA = 1;
	const OPT_CONSISTENT = 2;
	const OPT_COMPRESS = 4;

    /**
     * Result Code.
     *
     * @const
     */
	const RES_SUCCESS = 1;
	const ERR_FAILURE = 2;
	const ERR_NOT_FOUND = 3;
	const ERR_NOT_STORED = 4;

    /**
     * My AccessKey.
     * @const
    */
       const ENC_ACCESS_KEY = 'f97d2d7e48472d0fdba35b85deff3967';

    /**
     * Distribute Cache Cluster 
     * 
     * @var string
     */
	var $_cluster = "Test";
	
    /**
     * Key's prefix namespace
     *
     * @var string
     */
	var $_namespace = "";

    /**
     * Metadata Cache server address
     *
     * @var string
     */
	var $_metad = "localhost";

    /**
     * Metad connection
     *
     * @var mixed
     */
	var $_client = null; // 
	
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
			throw new SinaServiceException("AccessKey Invalid.");
		}
		$this->_AccessKey = $accesskey;
		$this->_SecretKey = $secretkey;
	}

    /**
     * Destructor.
     *
     * @param array $params  A hash containing connection parameters.
     */
	function __destruct(){

	}

    /**
     * Get cache node by given key, not to be called directly by user.
     *
     * @param string $k  Key to be hashed.
     * @param integer $flag  options when getting nodes.
     * 
     * @return mixed a Result object contains cache nodes and result code.
     */
    function getNodes($k,$flag) {
		$key['k'] = $k;
		$key['is_replica'] = $flag & self::OPT_REPLICA?true:false;
		$key['cluster'] = $this->_cluster;
		
		$ch = curl_init("http://".$this->_metad.":7777");
        $data = json_encode($key);
        curl_setopt($ch,CURLOPT_POST,1);
	        $curlv = curl_version();
        if($curlv['version'] >= 7.16) {
        	curl_setopt($ch,CURLOPT_TIMEOUT_MS, 300);
        } else {
                curl_setopt($ch,CURLOPT_TIMEOUT, 1);
        }
        curl_setopt($ch,CURLOPT_POSTFIELDS,"data=".urlencode($data));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        $ret = json_decode(curl_exec($ch));
		$nodes = explode("|",$ret->nodes);
		$ret->nodes = array();
		$count = 0;
		foreach($nodes as $node) {
			$n = new Node();
			$t = explode(":",$node);
			$n->ip = $t[0];
			$n->port = intval($t[1]);
			$n->role = $count == 0 ? "master" : "slave";
			$ret->nodes[] = $n;
			$count += 1;
		}
		
		return $ret;
		
	}

    /**
     * Get cache node by given key, not to be called directly by user.
     *
     * @param string $ks  Keys to be hashed.
     * @param integer $flag  options when getting nodes.
     * 
     * @return mixed a Result object contains cache nodes and result code.
     */
    function getMultiNodes($ks,$flag) {
		$key['k'] = $ks;
		$key['is_replica'] = $flag & self::OPT_REPLICA?true:false;
		$key['cluster'] = $this->_cluster;
        $key['namespace'] = $this->_namespace;
		
		$ch = curl_init("http://".$this->_metad.":7777");
        $data = json_encode($key);
        curl_setopt($ch,CURLOPT_POST,1);
     	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT_MS, 500);
        curl_setopt($ch,CURLOPT_POSTFIELDS,"multi=1&data=".urlencode($data));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        $ret = json_decode(curl_exec($ch));
        $r = array();
        $c = 0;
        foreach($ret as $nodes => $keys) {
	      	$nodes = explode("|",$nodes);
			$r[$c] = array();
            $r[$c]['keys'] = $keys;
            
			$count = 0;
			foreach($nodes as $node) {
				$n = new Node();
				$t = explode(":",$node);
				$n->ip = $t[0];
				$n->port = intval($t[1]);
				$n->role = $count == 0 ? "master" : "slave";
				$r[$c]['nodes'][] = $n;
				$count += 1;
			}
            $c += 1;
        }
		
		return $r;
		
	}

	function get($key,$flag,$cas = null) {
		#return PEAR::raiseError(_("Not supported."));
	}

	function set($key,$val,$flag,$expire) {
		#return PEAR::raiseError(_("Not supported."));
	}

	function add($key,$val,$flag,$expire) {
		#return PEAR::raiseError(_("Not supported."));
	}

	function delete($key,$flag) {
		#return PEAR::raiseError(_("Not supported."));
	}

	function increment($key,$val = 1,$flag) {
		#return PEAR::raiseError(_("Not supported."));
	}

	function decrement($key,$val = 1,$flag) {
		#return PEAR::raiseError(_("Not supported."));
	}

	function close() {
		#return PEAR::raiseError(_("Not supported."));
	}

    function cas($cas, $key, $val ,$expire ,$flag){

	}
    
    function getMulti($keys,$flag) {
		#return PEAR::raiseError(_("Not supported."));
	}

	function setMulti($kvs,$flag,$expire) {
		#return PEAR::raiseError(_("Not supported."));
	}
    

     /**
     * Attempts to return a concrete SinaCombService instance based on $driver.
     *
     * @param mixed $driver  The type of concrete SinaCombService subclass to return. This
     *                       is based on the storage driver ($driver). The
     *                       code is dynamically included.
     * @param array $params  A hash containing any additional configuration or
     *                       connection parameters a subclass might need.
     *
     * @return SinaCombService  The newly created concrete SinaCombService instance, or a PEAR_Error
     *              on failure.
     */
    function &factory($driver = self::_driver, $params = array())
    {
        include_once 'Comb/' . $driver . '.php';
        $class = 'Comb_' . $driver;
        if (class_exists($class)) {
            $sdc = & new $class($params);
        } else { 
            $sdc = PEAR::raiseError(sprintf("Class definition of %s not found.", $class));
        }

        return $sdc;
    }


    /**
     * Attempts to return a reference to a concrete SinaCombService instance based on
     * $driver. It will only create a new instance if no SinaCombService instance with the
     * same parameters currently exists.
     *
     * This should be used if multiple types of file backends (and, thus,
     * multiple SinaCombService instances) are required.
     *
     * This method must be invoked as: $var = &VFS::singleton()
     *
     * @param mixed $driver  The type of concrete VFS subclass to return. This
     *                       is based on the storage driver ($driver). The
     *                       code is dynamically included.
     * @param array $params  A hash containing any additional configuration or
     *                       connection parameters a subclass might need.
     *
     * @return SinaCombService  The concrete SinaCombService reference, or a PEAR_Error on failure.
     */
    function setparams($namespace = "", $cluster = "Test") {
        $this->_namespace = $namespace;
        $this->_cluster = $cluster;
    }

    function &init($driver = "memcached", $params = array())
    {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }
	
	    $this->setparams($params['namespace'],$params['cluster']);
        $signature = serialize(array($driver, $params));
        if (!isset($instances[$signature])) {
            $instances[$signature] = &SinaCombService::factory($driver, $params);
        }

        return $instances[$signature];
    }
}


?>

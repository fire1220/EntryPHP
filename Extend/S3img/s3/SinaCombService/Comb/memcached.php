<?php
class Comb_memcached extends SinaCombService {
    
    function __construct($params = array())
    {
//        parent::__construct(parent::_AccessKey);
        
    }

    private function connect($node){
        $n = new Memcached();
        $n->addServer($node->ip,$node->port);
    	$n->setOption(Memcached::OPT_NO_BLOCK,true);
        $n->setOption(Memcached::OPT_TCP_NODELAY,true);
        return $n;
    }

    function get($key,$flag = 0, $cas = null) {
        $key = $key.$this->_namespace;
        $result = $this->getNodes($key,$flag);
        foreach($result->nodes as $node) {
            $n = $this->connect($node);
            $v = $n->get($key,null,$cas);
            $code = $n->getResultCode();
            switch($code) {
                case Memcached::RES_SUCCESS:
                    return $v;
                case Memcached::RES_NOTFOUND:
                        continue;
                default:
                    continue;
            }
        }
        return false;
        
    }

    function set($key,$val,$flag = 0,$expire = 0) {
        $key = $key.$this->_namespace;
        $result = $this->getNodes($key,$flag);
        foreach($result->nodes as $node) {
            $n = $this->connect($node);
            $r = $n->set($key,$val,$expire);
            $code = $n->getResultCode();

            switch($code) {
                case Memcached::RES_SUCCESS:
                    continue;
                default:
                    if($node->role == "master") {
                        return false;
                    } else if($flag & parent::OPT_CONSISTENT){
                        return false;
                    } else {
                        continue;
                    }
            }
            
        }
    return true;
    }

    function cas($cas,$key,$val,$expire = 0,$flag = 0) {
        $key = $key.$this->_namespace;
        $result = $this->getNodes($key,$flag);
        
        foreach($result->nodes as $node) {
            $n = $this->connect($node);
            $r = $n->cas($cas,$key,$val,$expire);
            $code = $n->getResultCode();

            switch($code) {
                case Memcached::RES_SUCCESS:
                    continue;
                case Memcached::RES_NOTSTORED:
                    return false;
                default:
                    if($node->role == "master") {
                        return false;
                    } else if($flag & parent::OPT_CONSISTENT){
                        return false;
                    } else {
                        continue;
                    }
            }
            
        }
        return true;

    }

    function add($key,$val,$flag = 0,$expire = 0) {
        $key = $key.$this->_namespace;
        $result = $this->getNodes($key,$flag);
        
        foreach($result->nodes as $node) {
            $n = $this->connect($node);
            $r = $n->add($key,$val,$expire);
            $code = $n->getResultCode();

            switch($code) {
                case Memcached::RES_SUCCESS:
                    continue;
                case Memcached::RES_NOTSTORED:
                    return false;
                default:
                    if($node->role == "master") {
                        return false;
                    } else if($flag & parent::OPT_CONSISTENT){
                        return false;
                    } else {
                        continue;
                    }
            }
            
        }
        return true;
    }

    function delete($key,$flag = 0) {
        $key = $key.$this->_namespace;
        $result = $this->getNodes($key,$flag);
        
        foreach($result->nodes as $node) {
            $n = $this->connect($node);
            $r = $n->delete($key);
            $code = $n->getResultCode();

            switch($code) {
                case Memcached::RES_SUCCESS:
                    continue;
                default:
                    if($node->role == "master") {
                        return false;
                    } else if($flag & parent::OPT_CONSISTENT){
                        return false;
                    } else {
                        continue;
                    }
            }
            
        }
        return true;
    }

    function increment($key,$val = 1,$flag = 0) {
        $key = $key.$this->_namespace;
        $result = $this->getNodes($key,$flag);
        
        foreach($result->nodes as $node) {
            $n = $this->connect($node);
            $r = $n->increment($key,$val);
            $code = $n->getResultCode();

            switch($code) {
                case Memcached::RES_SUCCESS:
                    continue;
                default:
                    if($node->role == "master") {
                        return false;
                    } else if($flag & parent::OPT_CONSISTENT){
                        return false;
                    } else {
                        continue;
                    }
            }
            
        }
        return true;
    }

    function decrement($key,$val = 1,$flag = 0) {
        $key = $key.$this->_namespace;
        $result = $this->getNodes($key,$flag);
        
        foreach($result->nodes as $node) {
            $n = $this->connect($node);
            $r = $n->decrement($key,$val);
            $code = $n->getResultCode();

            switch($code) {
                case Memcached::RES_SUCCESS:
                    continue;
                default:
                    if($node->role == "master") {
                        return false;
                    } else if($flag & parent::OPT_CONSISTENT){
                        return false;
                    } else {
                        continue;
                    }
            }
            
        }
        return true;
    }

    function getMulti($keys,$flag = 0,&$cas_tokens = null) {
        $ret = $this->getMultiNodes($keys,$flag);
        $result = array();
        foreach($ret as $shard) {
            foreach($shard['nodes'] as $node) {
                $n = $this->connect($node);
                $r = $n->getMulti($shard['keys']);
                if($r !== false) {
                    break;
                }
            }

            # set all key false on failure;
            if($r == false) {
                foreach($shard['keys'] as $key) {
                    $result[$key] = false;
                }
            } else {
                $result += $r;
            }
            
        }
 
        return $result;
    }

    function setMulti($kvs,$flag = 0,$expire = 0) {
        $ret = $this->getMultiNodes(array_keys($kvs),$flag);
        $result = true;
        foreach($ret as $shard) {
            foreach($shard['nodes'] as $node) {
                $pairs = array();
                foreach($shard['keys'] as $k) {
                    $pairs[$k] = $kvs[$k];
                }
                $n = $this->connect($node);
                $r = $n->setMulti($pairs,$expire);
                $code = $n->getResultCode();

                switch($code) {
                    case Memcached::RES_SUCCESS:
                        continue;
                    default:
                        if($node->role == "master") {
                            $result = false;
                        } else if($flag & parent::OPT_CONSISTENT){
                            $result = false;
                        } else {
                            continue;
                        }
                 }
            }
        }
 
        return $result;
    }
    

}

?>

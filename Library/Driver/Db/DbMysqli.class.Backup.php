<?php
/**
*   author:周健东
*   QQ:395154675
*/

class connectDb{
    private $connect = '';
    static public $dbobj = array();
    static public $database = '';//选择数据库
    static public function init($linkserver,$singleton=true){
        if(!empty($linkserver['database'])){
            self::$database = $linkserver['database'];
        }
        $dbMd5 = md5($linkserver['host'].$linkserver['port'].$linkserver['username'].$linkserver['password'].$linkserver['database']);//根据不同的链接句柄产生不同的对象
        if($singleton){//使用singleton（单态）模式
            if(empty(self::$dbobj[$dbMd5])){
                self::$dbobj[$dbMd5] = new self($linkserver);
            }
            return self::$dbobj[$dbMd5]->connect;
        }else{
            $link = new self($linkserver);
            return $link->connetc;
        }
    }
    private function __construct($linkserver=''){
        @$link = mysqli_connect($linkserver['host'],$linkserver['username'],$linkserver['password'],self::$database,$linkserver['port']);//
        if(!$link){
            Base::abnormal('MySQLi数据库连接失败,请确定用户名密码以及数据库名称！错误码：100012');
        }
        //设置数据库字符编码
        mysqli_query($link,"SET NAMES utf8");
        $this->connect = $link;
    }

}
class DbMysqli{
    public $table;
    private $where;
    private $columns = '*';
    private $order;
    private $table_user;
    private $limit;
    private $logic = 'AND';
    private $activeLogic;
    private $data;
    private $lastsql;
    private $connect;
    public function __construct($table,$linkserver,$singleton=true){
        $this->table = $table;
        $this->connect = connectDb::init($linkserver,$singleton);
    }
    public function test(){
        return $this->connect;
    }
    private function mysqlQuery($link,$sql){
        $source = mysqli_query($link,$sql);
        $this->responseSource($source);
        return $source;
    }
    private function mysqlInsertId($link){
        return mysqli_insert_id($this->connect);
    }
    private function mysqlFetchAssoc($source){
        return mysqli_fetch_assoc($source);
    }
    private function mysqlAffectedRows($link){
        return mysqli_affected_rows($link);
    }
    private function responseSource($source){
        if(is_bool($source)&&!$source){
            Base::abnormal('SQL语法错误：'.$this->getLastSql().',原因可以是语法错误或数据表或字段有误！错误码：100013');
        }
    }
    private function activeLogic($logic=''){//活动的逻辑
        $logic = strtoupper($logic);
        if($logic=='AND'){
            $active_logic = 'AND';
        }else if($logic=='OR'){
            $active_logic = 'OR';
        }else{
            $active_logic = $this->logic;
        }
        $this->activeLogic = $active_logic;
        return $active_logic;
    }
    private function clearopj(){//
        $this->columns = '*';
        $this->data = '';
        $this->limit = '';
        $this->logic = 'AND';
        $this->order = '';
        $this->where = '';
        $this->table_user = '';
    }
    public function logic($logic){
        $logic = strtoupper($logic);
        if($logic=='AND'){
            $this->logic = $logic;
        }elseif($logic=='OR'){
            $this->logic = $logic;
        }
        return $this;
    }
    public function columns($columns){
        $this->columns = $columns;
        return $this;
    }
    public function order($order){
        $this->order = ' ORDER BY '.$order;
        return $this;
    }
    public function table($table){
        $this->table_user = $table;
        return $this;
    }
    public function limit($limit,$listRows=''){
        if(!empty($listRows)){
            $this->limit = ' LIMIT '.$limit.','.$listRows;
        }else{
            $this->limit = ' LIMIT '.$limit;
        }
        return $this;
    }
    public function data($data){
        if(get_magic_quotes_gpc()){
            $this->data = $data;
        }else{
            foreach($data as $key=>$val){
                if(is_string($val)){
                    $this->data[$key] = addslashes($val);
                }else{
                    $this->data[$key] = $val;
                }
            } 
        }
        return $this;
    }
    public function where($where){
        if(is_array($where)){
            $where_sql = ' WHERE ';
            foreach ($where as $key=>$val){
                if(is_array($val)){
                    $val[2] = empty($val[2])?'':$val[2];
                    $val[3] = empty($val[3])?'':$val[3];
                    switch ($val[0]){
                        case 'like':
                            $where_sql .= ' `'.$key.'` LIKE "'.$val[1].'" '.$this->activeLogic($val[2]);
                        break;
                        case 'in':
                            if(is_array($val[1])){
                                $where_sql .= ' `'.$key.'` IN(';
                                foreach ($val[1] as $val2){
                                    $where_sql .= '"'.$val2.'",';
                                }
                                $where_sql = rtrim($where_sql,',');
                                $where_sql .= ') '.$this->activeLogic($val[2]);
                            }else{
                                $where_sql .= ' `'.$key.'` IN('.$val[1].') '.$this->activeLogic($val[2]);
                            }
                        break;
                        case 'gt':
                            $where_sql .= ' `'.$key.'` > "'.$val[1].'" '.$this->activeLogic($val[2]);
                        break;
                        case 'lt':
                            $where_sql .= ' `'.$key.'` < "'.$val[1].'" '.$this->activeLogic($val[2]);
                        break;
                        case 'egt':
                            $where_sql .= ' `'.$key.'` >= "'.$val[1].'" '.$this->activeLogic($val[2]);
                            break;
                        case 'elt':
                            $where_sql .= ' `'.$key.'` <= "'.$val[1].'" '.$this->activeLogic($val[2]);
                        break;
                        case 'eq':
                            $where_sql .= ' `'.$key.'` = "'.$val[1].'" '.$this->activeLogic($val[2]);
                        break;
                        case 'neq':
                            $where_sql .= ' `'.$key.'` <> "'.$val[1].'" '.$this->activeLogic($val[2]);
                        break;
                        case 'bt':
                            $where_sql .= ' (`'.$key.'` > "'.$val[1].'" AND `'.$key.'` < "'.$val[2].'") '.$this->activeLogic($val[3]);
                        break;
                        case 'nbt':
                            $where_sql .= ' (`'.$key.'` <= "'.$val[1].'" AND `'.$key.'` >= "'.$val[2].'") '.$this->activeLogic($val[3]);
                        break;
                        case 'ebt':
                            $where_sql .= ' (`'.$key.'` >= "'.$val[1].'" AND `'.$key.'` <= "'.$val[2].'") '.$this->activeLogic($val[3]);
                        break;
                        case 'nebt':
                            $where_sql .= ' (`'.$key.'` < "'.$val[1].'" AND `'.$key.'` > "'.$val[2].'") '.$this->activeLogic($val[3]);
                        break;
                    }
                }else{
                    $where_sql .= ' `'.$key.'` = "'.$val.'" '.$this->activeLogic();
                }
            }
            $where_sql = rtrim($where_sql,$this->activeLogic);
            $this->where = $where_sql;
        }else{
            if(!empty($where)){
                $this->where = ' WHERE '.$where;
            }else{
                $this->where = '';
            }
        }
        return $this;
    }
    public function add(){
        $table = $this->table;
        
        $insert_sql = 'INSERT INTO `'.$table.'`(';
        foreach ($this->data as $key=>$val){
            $insert_sql .= '`'.$key.'`,';
        }
        $insert_sql = rtrim($insert_sql,',');
        $insert_sql .= ') VALUES(';
        foreach ($this->data as $val){
            $insert_sql .= '"'.$val.'",';
        }
        $insert_sql = rtrim($insert_sql,',');
        $insert_sql .= ')';
        $this->lastsql = $insert_sql;
        $source = $this->mysqlQuery($this->connect,$insert_sql);
        $user_insert_id = $this->mysqlInsertId($this->connect);
        $this->clearopj();
        return $user_insert_id;
    }
    public function select(){
        $table = $this->table;
        $sql = 'SELECT '.$this->columns.' FROM `'.$table.'` '.$this->where.$this->order.$this->limit;
        $this->lastsql = $sql;
        $source = $this->mysqlQuery($this->connect,$sql);
        if(!empty($source)){
            while($row = $this->mysqlFetchAssoc($source)){
                $select[] = $row;
            }
        }
        if(!isset($select)){
            $select = array();
        }
        $this->clearopj();
        return $select;
    }
    public function find(){
        $table = $this->table;
        $sql = 'SELECT '.$this->columns.' FROM `'.$table.'` '.$this->where.$this->order.' LIMIT 1';
        $this->lastsql = $sql;
        $source = $this->mysqlQuery($this->connect,$sql);
        if(!empty($source)){
            $find = $this->mysqlFetchAssoc($source);
        }else{
            $find = false;
        }
        $this->clearopj();
        return $find;
    }
    public function save(){
        if(!empty($this->where)){
            $table = $this->table;
            $sql = 'UPDATE `'.$table.'` SET ';
            foreach ($this->data as $key=>$val){
                if(is_array($val)){
                    if($val[0]=='+'){
                        $sql .= '`'.$key.'`='.$key.'+"'.$val[1].'",';
                    }elseif($val[0]=='-'){
                        $sql .= '`'.$key.'`='.$key.'-"'.$val[1].'",';
                    }
                }else{
                    $sql .= '`'.$key.'`="'.$val.'",';
                }
            }
            $sql = rtrim($sql,',');
            $sql .= $this->where;
            $this->lastsql = $sql;
            $this->mysqlQuery($this->connect,$sql);
            $affected = $this->mysqlAffectedRows($this->connect);
            $this->clearopj();
            return $affected;
        }
    }
    public function delete(){
        if(!empty($this->where)){
            $table = $this->table;
            $sql = 'DELETE FROM `'.$table.'` '.$this->where;
            $this->lastsql = $sql;
            $source = $this->mysqlQuery($this->connect,$sql);
            $affected = $this->mysqlAffectedRows($this->connect);
            $this->clearopj();
            return $affected;
        }
    }
    public function count(){
        $table = $this->table;
        $sql = 'SELECT COUNT(*) as "count" FROM `'.$table.'` '.$this->where;
        $this->lastsql = $sql;
        $source = $this->mysqlQuery($this->connect,$sql);
        if(!empty($source)){
            $count = $this->mysqlFetchAssoc($source);
            $return_count = $count['count'];
        }else{
            $return_count = 0;
        }
        $this->clearopj();
        return $return_count;
    }
    public function sum($column){
        $table = $this->table;
        $sql = 'SELECT SUM(`'.$column.'`) as "sum" FROM `'.$table.'` '.$this->where;
        $this->lastsql = $sql;
        $source = $this->mysqlQuery($this->connect,$sql);
        if(!empty($source)){
            $sum = $this->mysqlFetchAssoc($source);
            $return_sum = $sum['sum'];
        }else{
            $return_sum = 0;
        }
        $this->clearopj();
        return $return_sum;
    }
    public function query($sql){
        $this->lastsql = $sql;
        $source = $this->mysqlQuery($this->connect,$sql);
        $query = array();
        while ($row = $this->mysqlFetchAssoc($source)){
            $query[] = $row;
        }
        return $query;
    }
    public function execute($sql){
        $this->lastsql = $sql;
        $this->mysqlQuery($this->connect,$sql);
        $insert_id = $this->mysqlInsertId($this->connect);
        $affected = $this->mysqlAffectedRows($this->connect);
        if($insert_id){
            return $insert_id;
        }else{
            return $affected;
        }
    }
    public function getLastSql(){
        return $this->lastsql;
    }
    public function __destruct(){
       
    }
}

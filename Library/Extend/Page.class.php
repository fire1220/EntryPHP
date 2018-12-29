<?php
/**
 * 分页类 Page.class.php --- V6.0
 * 分页
 *  V3.1添加一个方法returnStatus();
 *  V4.0 添加一个ajax的方法，实现异步调取分页
 *  V4.2 成员属性$totalPages更改成公共属性
 *  V4.3 添加$leftright初始化
 *  V5.0 添加方法limit()，返回SQL里limit的参数例如：0,10或者5,10
 *  V6.0 防止sql注入在$_GET做数据类型限制了
 */
class Page{
    public $listRows;// 默认列表每页显示行数
    public $firstRow;// 起始行数
    public $totalPages;// 分页总页面数
    private $nowPage;// 当前页数
    private $totalRows;// 总行数
    private $varPage;// 默认分页变量名
    private $leftright=1;//分页数组的左右链接数字1...45678...10中的45和78，这个参数设置为2时候。
    private $url;
    /**
     * @param int $totalRows 总行数
     * @param int $listRows  默认列表每页显示行数
     * @param string $url
     */
    public function __construct($totalRows,$listRows,$url='',$leftright=''){
        $this->totalRows = $totalRows;
        if(!empty($listRows)){
            $this->listRows = intval($listRows);
        }
        if(!empty($leftright)){
            $this->leftright = $leftright;
        }
        $this->totalPages = ceil($this->totalRows/$this->listRows);
        $this->varPage = 'p';
        $this->nowPage = !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if($this->nowPage<1){
            $this->nowPage  =   1;
        }elseif(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage  =   $this->totalPages;
        }
        $url_name = basename($_SERVER['SCRIPT_NAME']);
        $url_parameter = '?';
        foreach ($_GET as $key=>$val){
            if($key!=$this->varPage){
                $url_parameter .= $key.'='.$val.'&';
            }
        }
        $this->url = $url_name.$url_parameter.$this->varPage.'=';
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
    }
    /**
     *  string limit() 返回SQL里limit的参数例如：0,10或者5,10
     */
    public function limit(){
        return $this->firstRow.','.$this->listRows;
    }
    /**
     *  array $status 分页状态
     *  $status 数组中：
     *  isonepage   是否显示第一个的数字（默认显示）
     *  islastpage  是否显示最后一页的数字（默认显示）
     */
    public function show($status = array()){
        $status['isonepage'] = isset($status['isonepage'])?$status['isonepage']:1;
        $status['islastpage'] = isset($status['islastpage'])?$status['islastpage']:1;
        //上一页
        if($this->nowPage>1){
            echo '<a class="uppage" href="'.$this->url.($this->nowPage-1).'" >上一页</a>';
        }else{
            echo '<span class="stoppage">上一页</span>';
        }
        //前边数字
        if($this->nowPage>$this->leftright+1){
            //第一页
            if(!empty($status['isonepage'])){//是否显示第一页数字
                echo '<a class="numberpage" href="'.$this->url.'1" >1</a>';
            }
            echo '<span class="number_now" >...</span>';
        }
        //中间数组
        if($this->nowPage-$this->leftright>=1){
            $left_num = $this->nowPage-$this->leftright;    
        }else{
            $left_num = 1;
        }
        if($this->nowPage+$this->leftright<=$this->totalPages){
            $right = $this->nowPage+$this->leftright;
        }else{
            $right = $this->totalPages;
        }
        for($i=$left_num;$i<=$right;$i++){
            if($i==$this->nowPage){
                echo '<span class="current" >'.$i.'</span>';
            }else{
                echo '<a class="numberpage" href="'.$this->url.$i.'" >'.$i.'</a>';
            }
        }
        if($this->totalPages>$this->nowPage+$this->leftright){
            echo '<span class="number_now" >...</span>';
            //最后一页
            if(!empty($status['islastpage'])){//是否显示最后一页的数字
                echo '<a class="numberpage" href="'.$this->url.$this->totalPages.'" >'.$this->totalPages.'</a>';
            }   
        }
        //下一页
        if($this->nowPage<$this->totalPages){
            echo '<a class="downpage" href="'.$this->url.($this->nowPage+1).'" >下一页</a>';
        }else{
            echo '<span class="stoppage">下一页</span>';
        }
    }
    /**
    *   判断是否是最后一页
    *   如果当前页面是最后一页返回false,否则返回true
    **/
    public function returnStatus(){
        //下一页
        if($this->nowPage<$this->totalPages){
            return true;
        }else{
            return false;
        }
    }
    /**
    *   支持js点击事件,实现异步调取分页
    *   $jsFun是定义js的方法
    *   $jsParameter是js方法的参数，可以是字符串也可以是索引数组
    *   
    */
    public function scriptPage($jsFun="jsPage",$jsParameter='',$status = array()){
        $jsFun = !empty($jsFun)?$jsFun:"jsPage";
        $jsParameterString = '';
        if(isset($jsParameter)){
            if(is_array($jsParameter)){
                $jsParameterString = implode("','", $jsParameter);
                $jsParameterString = ",'".$jsParameterString."'";
            }else{
                $jsParameterString = ",'".$jsParameter."'";
            }
        }
        $status['isonepage'] = isset($status['isonepage'])?$status['isonepage']:1;
        $status['islastpage'] = isset($status['islastpage'])?$status['islastpage']:1;
        //上一页
        if($this->nowPage>1){
            echo '<a class="uppage" href="javascript:'.$jsFun.'('.($this->nowPage-1).$jsParameterString.');" >上一页</a>';
        }else{
            echo '<span class="stoppage">上一页</span>';
        }
        //前边数字
        if($this->nowPage>$this->leftright+1){
            //第一页
            if(!empty($status['isonepage'])){//是否显示第一页数字
                echo '<a class="numberpage" href="javascript:'.$jsFun.'(1'.$jsParameterString.');" >1</a>';
            }
            echo '<span class="number_now" >...</span>';
        }
        //中间数组
        if($this->nowPage-$this->leftright>=1){
            $left_num = $this->nowPage-$this->leftright;    
        }else{
            $left_num = 1;
        }
        if($this->nowPage+$this->leftright<=$this->totalPages){
            $right = $this->nowPage+$this->leftright;
        }else{
            $right = $this->totalPages;
        }
        for($i=$left_num;$i<=$right;$i++){
            if($i==$this->nowPage){
                echo '<span class="current" >'.$i.'</span>';
            }else{
                echo '<a class="numberpage" href="javascript:'.$jsFun.'('.$i.$jsParameterString.');" >'.$i.'</a>';
            }
        }
        if($this->totalPages>$this->nowPage+$this->leftright){
            echo '<span class="number_now" >...</span>';
            //最后一页
            if(!empty($status['islastpage'])){//是否显示最后一页的数字
                echo '<a class="numberpage" href="javascript:'.$jsFun.'('.$this->totalPages.$jsParameterString.');" >'.$this->totalPages.'</a>';
            }   
        }
        //下一页
        if($this->nowPage<$this->totalPages){
            echo '<a class="downpage" href="javascript:'.$jsFun.'('.($this->nowPage+1).$jsParameterString.');" >下一页</a>';
        }else{
            echo '<span class="stoppage">下一页</span>';
        }
    }

    
}
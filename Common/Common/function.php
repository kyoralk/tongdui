<?php
use Org\Net\Http;
/**
 * json返回
 * @param string $data
 * @param string $info
 * @param number $status
 */
function jsonReturn($data = '',$status='^~^'){
	echo json_encode(array('data'=>$data,'info'=>L($status),'status'=>$status),JSON_UNESCAPED_UNICODE);
	exit();
}


/**
 * 根据查询的IP地址或者域名，查询该IP所属的区域
 */
function getAddrByIp(){
	$client_ip = get_client_ip();
	$Ip = new Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
	$area = $Ip->getlocation($client_ip);
	return $area['country'];
}
/**
 * 发送HTTP请求方法，目前只支持CURL发送请求
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function http($url, $params, $method = 'POST', $cookie = false , $header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8"), $multi = false){
	$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER     => $header,
			CURLOPT_USERAGENT      => 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.76 Mobile Safari/537.36',
			//CURLOPT_HTTPHEADER	   =>array('X-FORWARDED-FOR:111.222.333.4', 'CLIENT-IP:111.222.333.4'),
			//CURLOPT_REFERER        => 'http://so.m.jd.com/category/all.html'
	);

	/* 根据请求类型设置特定参数 */
	switch(strtoupper($method)){
		case 'GET':
			$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
			break;
		case 'POST':
			//判断是否传输文件
			$params = $multi ? $params : http_build_query($params);
			$opts[CURLOPT_URL] = $url;
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = $params;
			break;
		default:
			throw new Exception('不支持的请求方式！');
	}
	
	if($cookie){
		$opts[CURLOPT_COOKIE] = $cookie;
	}
	/* 初始化并执行curl请求 */
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data  = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if($error) throw new Exception('请求发生错误：' . $error);
	return  $data;
}
/**
 * 分页
 * @param obj $obj 模型对象
 * @param array $condition 查询条件
 * @param int $num 每页显示条数
 * @param string $model 使用的模型relation view 默认 
 * @param string $order 排序
 * @param string $field 查询字段视图模型和不使用模型时起作用
 * @param string $group 视图模型有效
 * @param string $count 视图模型查询总记录数有问题，作为一个补充方法，但是不完美
 */
function page($obj,$condition,$num=10,$model='',$order='',$field='*',$group='',$count=0) {
	//总记录
	if(!$count){
		$count=$obj->where($condition)->group($group)->count();
	}
	$p= new \Think\Page($count, $num);
	$p -> setConfig('header','共%TOTAL_ROW%条');
	$p -> setConfig('first','首页');
	$p -> setConfig('last','尾页');
	$p -> setConfig('prev','上一页');
	$p -> setConfig('next','下一页');
	$p -> setConfig('theme','<li>%FIRST%</li><li><a>%HEADER%</a></li><li>%UP_PAGE%</li><li>%LINK_PAGE%</li><li>%DOWN_PAGE%</li><li>%END%</li>');
	//判断使用哪个模型处理
	switch ($model){
		case 'relation':
			$data['list']=$obj->where($condition)->relation(true)->limit($p->firstRow . ',' . $p->listRows)->order($order)->select();
			break;
		case 'view':
			$data['list']=$obj->where($condition)->field($field)->limit($p->firstRow . ',' . $p->listRows)->order($order)->group($group)->select();
			break;
		default:
			$data['list']=$obj->where($condition)->limit($p->firstRow . ',' . $p->listRows)->order($order)->field($field)->select();
	}
	//分页跳转的时候保证查询条件
	foreach($map as $key=>$val){    
		$p->parameter   .=   "$key=".urlencode($val).'&';
	}
	
	$data['page'] = $p->show (); // 分页的导航条的输出变量
	return $data;
}
/**
 * 
 * @param obj $obj 模型对象
 * @param array $condition 查询条件
 * @param int $num 每页显示条数
 * @param int $p 当前页
 * @param string $model 使用的模型relation ,view, 默认 
 * @param string $order 排序
 * @param string $field 查询字段视图模型和不使用模型时起作用
 * @param string $group 视图模型有效
 * @param string $count 视图模型查询总记录数有问题，作为一个补充方法，但是不完美
 */
function appPage($obj,$condition,$num,$p,$model='',$order='',$field='',$group='',$count=0){
	$p = empty($p)?1:$p;
	$num = empty($num)?10:$num;
	$field = empty($field)?'*':$field;
	//总记录
	if(!$count){
		$count=$obj->where($condition)->count();
	}
	//计算总页数
	if($count%$num==0){
		$data['pages'] = $count/$num;
	}else{
		$data['pages'] = intval($count/$num) + 1;
	}
	//判断是否是最后一页
	if($p>=$data['pages']){
		$p = $data['pages'];
		$data['end']=1;
	}else{
		$data['end']=0;
	}
	//计算开始索引
	$start=$p*$num-$num;
	$start = $start<0?0:$start;
	$data['start'] = $start;
	$data['count'] = $count;
	$data['p'] = $p;
	//判断使用哪个模型处理
	switch ($model){
		case 'relation':
			$data['list']=$obj->where($condition)->relation(true)->limit($start,$num)->order($order)->select();
			break;
		case 'view':
			$data['list']=$obj->where($condition)->field($field)->limit($start,$num)->order($order)->group($group)->select();
			break;
		default:
			$data['list']=$obj->where($condition)->limit($start,$num)->order($order)->field($field)->select();
	}
	return $data;
}



/**
 * 建立文件夹
 */
function createPath($path){
	if (!file_exists($path)){    //如果文件夹不存在
		/// createFolder(dirname($path)); //取得最后一个文件夹的全路径返回开始的地方
		if(mkdir($path, 0777)){
			return true;
		}
	}else{
		return false;
	}
}
/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array();
	if(is_array($list)) {
		// 创建基于主键的数组引用
		$refer = array();
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] =& $list[$key];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId =  $data[$pid];
			if ($root == $parentId) {
				$tree[] =& $list[$key];
			}else{
				if (isset($refer[$parentId])) {
					$parent =& $refer[$parentId];
					$parent[$child][] =& $list[$key];
				}
			}
		}
	}
	return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
function tree_to_list($tree, $child = '_child', $order='id', &$list = array()){
	if(is_array($tree)) {
		foreach ($tree as $key => $value) {
			$reffer = $value;
			if(isset($reffer[$child])){
				unset($reffer[$child]);
				tree_to_list($value[$child], $child, $order, $list);
			}
			$list[] = $reffer;
		}
		$list = list_sort_by($list, $order, $sortby='asc');
	}
	return $list;
}
/**
 * 随机字符串
 * @param number $length
 * @param boolen $int
 * @return string
 */
function randstr($length = 32, $int = false ){
	return '0000';
	if($int){
		$chars = '0123456789';
	}else{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	}
	$str = "";
	for ($i = 0; $i < $length; $i++) {
		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	return $str;
}
/**
 * 流水号
 */
function serialNumber(){
	return time().rand(1000,9999);
}
function msuid(){
	$base = microtime(true)*1000;
	return  $base%(256*256)+intval($base/(256*256))%256*100000;
}
/**
 * unicode 转 UTF-8
 * @param unknown $str
 */
function unicode_to_utf8($str) {
	$str = rawurldecode($str);
	preg_match_all("/(?:%u.{4})|&#x.{4};|&#\d+;|.+/U",$str,$r);
	$ar = $r[0];
	foreach($ar as $k=>$v) {
		if(substr($v,0,2) == "%u"){
			$ar[$k] = iconv("UCS-2BE","UTF-8",pack("H4",substr($v,-4)));
		}
		elseif(substr($v,0,3) == "&#x"){
			$ar[$k] = iconv("UCS-2BE","UTF-8",pack("H4",substr($v,3,-1)));
		}
		elseif(substr($v,0,2) == "&#") {

			$ar[$k] = iconv("UCS-2BE","UTF-8",pack("n",substr($v,2,-1)));
		}
	}
	return join("",$ar);
}


function writeTXT($txt,$file='./Static/a.txt'){
	$myfile = fopen($file, "a") or die("Unable to open file!");
	fwrite($myfile, $txt."\r\n");
	fclose($myfile);
}

function getWeekTime(){
	return array(
			'start_time'=>strtotime("last Sunday")+8*60*60,
			'end_time'=>strtotime ("Monday")+8*60*60
	);
}





?>
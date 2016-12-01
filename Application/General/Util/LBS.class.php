<?php
namespace General\Util;

class LBS{
	
	private static $ak = 'AsYFw03lddTCyydvPqbytG3NEn8GHFkH';//访问应用（AK）
	private static $geotable_url = 'http://api.map.baidu.com/geodata/v3/geotable/';//位置数据表（geotable）管理
	private static $column_url = 'http://api.map.baidu.com/geodata/v3/column/';//位置数据表字段（column）管理
	private static $poi_url = 'http://api.map.baidu.com/geodata/v3/poi/';//位置数据（poi）管理
	private static $job_url = 'http://api.map.baidu.com/geodata/v3/job/';//批量操作任务（JOB）接口
	/**
	 * 创建表
	 */
	public static function createGeotable($data){
		$params = array(
				'name'=>$data['name'],//Geotable的中文名称 必选
				'geotype'=>$data['geotype'],//Geotable持有数据的类型(1：点poi2：线poi3：面poi)
				'is_published'=>1,//是否发布到检索
				'timestamp'=>time(),
				'ak'=>self::$ak,
		);
		$res = http(self::$geotable_url.'create', $params);
		return json_decode($res,true);
	}
	/**
	 * 查询表
	 * @param string $name 表名
	 */
	public static function listGeotable($name){
		$params = array(
				'name'=>$name,
				'ak'=>self::$ak,
		);
		$res = http(self::$geotable_url.'list', $params,'GET');
		return json_decode($res,true);
	}
	/**
	 * 查询指定id表
	 * @param int $id 表的id
	 */
	public static function detailGeotable($id){
		$params = array(
				'id'=>$id,
				'ak'=>self::$ak,
		);
		$res = http(self::$geotable_url.'detail', $params,'GET');
		return json_decode($res,true);
	}
	/**
	 * 修改表
	 * @param int $id 表的id
	 * @param int $is_published 是否发布到检索 0：未自动发布到云检索，1：自动发布到云检索；
	 */
	public static function updateGeotable($id,$is_published = 1){
		$params = array(
				'id'=>$id,
				'is_published'=>$is_published,
				'ak'=>self::$ak,
		);
		$res = http(self::$geotable_url.'update', $params);
		return json_decode($res,true);
	}
	/**
	 * 删除表
	 * @param int $id 表的id
	 */
	public static function deleteGeotable($id){
		$params = array(
				'id'=>$id,
				'ak'=>self::$ak,
		);
		$res = http(self::$geotable_url.'delete', $params);
		return json_decode($res,true);
	}
	/**
	 * 创建列
	 */
	public static function createColumn(){
		$params = array(
				'name'=>$data['name'],
				'key'=>$data['key'],
				'type'=>$data['type'],//存储的值的类型 1： Int64, 2:double, 3,string，4，在线图片url
				'max_length'=>$data['max_length'],
				'default_value'=>$data['default_value'],
				'is_sortfilter_field'=>$data['is_sortfilter_field'],
				'is_search_field'=>$data['is_search_field'],
				'is_index_field'=>$data['is_index_field'],
				'is_unique_field'=>$data['is_unique_field'],
				'geotable_id'=>$data['geotable_id'],
				'ak'=>self::$ak,
		);
		$res = http(self::$column_url.'create', $params);
		return json_decode($res,true);
	}
	
	/**
	 * 查询列
	 * @param int $geotable_id 所属于的geotable_id
	 */
	public static function listColumn($geotable_id){
		$params = array(
				'geotable_id'=>$geotable_id,
				'ak'=>self::$ak,
		);
		$res = http(self::$column_url.'list', $params,'GET');
		return json_decode($res,true);
	}
	/**
	 * 查询指定id列
	 * @param int $id 列的id
	 * @param int $geotable_id 表的id
	 */
	public static function detailColumn($id,$geotable_id){
		$params = array(
				'id'=>$id,
				'geotable_id'=>$geotable_id,
				'ak'=>self::$ak,
		);
		$res = http(self::$column_url.'detail', $params,'GET');
		return json_decode($res,true);
	}
	/**
	 * 修改指定条件列（column）接口（批量条件修改）
	 */
	public static function updateColumn(){
		$params = array(
				'id'=>$data['id'],
				'geotable_id'=>$data['geotable_id'],
				'max_length'=>$data['max_length'],
				'is_sortfilter_field'=>$data['is_sortfilter_field'],
				'is_search_field'=>$data['is_search_field'],
				'is_index_field'=>$data['is_index_field'],
				'is_unique_field'=>$data['is_unique_field'],
				'ak'=>self::$ak,
		);
		$res = http(self::$column_url.'update', $params);
		return json_decode($res,true);
	}
	/**
	 * 删除指定条件列（column）接口（批量条件删除）
	 * @param unknown $id 列主键
	 * @param unknown $geotable_id 表主键
	 */
	public static function deleteColumn($id,$geotable_id){
		$params = array(
				'id'=>$id,
				'geotable_id'=>$geotable_id,
				'ak'=>self::$ak,
		);
		$res = http(self::$column_url.'delete', $params);
		return json_decode($res,true);
	}
	/**
	 * 创建数据
	 */
	public static function createPoi($data){
		$params = array(
				'title'=>$data['title'],
				'address'=>$data['address'],
				'tags'=>$data['tags'],
				'latitude'=>$data['latitude'],
				'longitude'=>$data['longitude'],
				'coord_type'=>$data['coord_type'],
				'geotable_id'=>$data['geotable_id'],
				'store_logo'=>$data['store_logo'],
				'store_id'=>$data['store_id'],
				'ak'=>self::$ak
		);	
		$res = http(self::$poi_url.'create', $params);
		return json_decode($res,true);
	}
	/**
	 * 查询指定条件的数据（poi）列表接口
	 */
	public static function listPoi($data){
		$page_index = empty($data['page_index']) ? 0 : $data['page_index'];
		$page_size = empty($data['page_size']) ? 10 : $data['page_size'];
		$page_size = $page_size>200 ? 200 : $page_size;
		$params = array(
				'title'=>$data['title'],
				'tags'=>$data['tags'],
				'bounds'=>$data['bounds'],
				'geotable_id'=>$data['geotable_id'],
				'page_index'=>$page_index,
				'page_size'=>$page_size,
				'ak'=>self::$ak,
		);
		$res = http(self::$poi_url.'list', $params,'GET');
		return json_decode($res,true);
	}
	/**
	 * 查询指定id的数据（poi）详情接口
	 */
	/**
	 * 
	 * @param int $id poi主键
	 * @param int $geotable_id 表主键
	 */
	public static function detailPoi($id,$geotable_id){
		$params = array(
				'id'=>$id,
				'geotable_id'=>$geotable_id,
				'ak'=>self::$ak,
		);
		$res = http(self::$poi_url.'detail', $params,'GET');
		return json_decode($res,true);
	}
	/**
	 * 修改数据（poi）接口
	 */
	public static function updatePoi($data){
		$params = array(
				'id'=>$data['id'],//当不存在唯一索引字段时必须，存在唯一索引字段可选
				'title'=>$data['title'],
				'address'=>$data['address'],
				'tags'=>$data['tags'],
				'latitude'=>$data['latitude'],
				'longitude'=>$data['longitude'],
				'coord_type'=>$data['coord_type'],
				'geotable_id'=>$data['geotable_id'],
				'store_logo'=>$data['store_logo'],
				'store_id'=>$data['store_id'],
				'ak'=>self::$ak,
		);
		$res = http(self::$poi_url.'update', $params);
		return json_decode($res,true);
	}
	/**
	 * 删除单个数据（poi）接口（支持批量）
	 */
	public static function deletePoi($data){
		$params = array(
				'id'=>$data['id'],
				'ids'=>$data['ids'],//以,分隔的id .最多1000个id,如果有这个条件,其它条件将被忽略.
				'bounds'=>$data['bounds'],//查询的矩形区,格式x1,y1;x2,y2分别代表矩形的左上角和右下角
				'geotable_id'=>$data['geotable_id'],
				'ak'=>self::$ak,
		);
		$res = http(self::$poi_url.'delete', $params);
		return json_decode($res,true);
	}
	/**
	 * 批量上传数据文件（post poi csv file）接口
	 */
	public static function uploadPoi(){
		$params = array(
				'geotable_id'=>$data['geotable_id'],
				'poi_list'=>$data['poi_list'],
				'ak'=>self::$ak,
		);
		$res = http(self::$poi_url.'upload', $params);
		return json_decode($res,true);
	}
	/**
	 * 批量上传进度查询接口（支持查询成功，失败poi）
	 */
	public static function listImportDataJob($data){
		$page_index = empty($data['page_index']) ? 0 : $data['page_index'];
		$page_size = empty($data['page_size']) ? 10 : $data['page_size'];
		$page_size = $page_size>100 ? 100 : $page_size;
		$params = array(
				'geotable_id'=>$data['geotable_id'],
				'job_id'=>$data['job_id'],
				'status'=>empty($data['status']) ? 0 : $data['status'],
				'page_index'=>$page_index,
				'page_size'=>$page_size,
				'ak'=>self::$ak,
		);
		$res = http(self::$job_url.'listimportdata', $params,'GET');
		return json_decode($res,true);
	}
	/**
	 * 批量操作任务查询（list job）接口
	 */
	public static function listJob(){
		$params = array(
				
		);
	}
	/**
	 * 根据id查询批量任务（detail job）接口
	 */
	public static function detailJob(){
		
	}
	
	public static function ipLoacation(){
		$params = array(
				'ak'=>self::$ak,
				'coor'=>'bd09ll',
		);
		$res = http('http://api.map.baidu.com/location/ip', $params,'GET');
		return json_decode($res,true);
	}
	
	
	
	
	
}
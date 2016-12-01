<?php
namespace Admin\Controller;
use General\Controller\GeneralController;
class CommonController extends GeneralController{
	protected $system_store_id = 1;
	protected function _initialize(){
		if(!session('?admin_id')){
			redirect('/Admin/Public/login');
		}
	}
	/**
	 * 获取服务器信息
	 */
	protected function getServerInfo(){
	
		if(F('server_info')){
			$server_info = F('server_info') ;
		}
		if($server_info['sysos']!=$_SERVER["SERVER_SOFTWARE"]){
			$sysos=$_SERVER["SERVER_SOFTWARE"];     //获取服务器标识字符串
	
			$sysversion=PHP_VERSION;        //获取php服务器版本
	
			//以下两条代码链接mysql数据库并获取mysql数据库版本信息
			mysql_connect("localhost","root","xiaobinge");
			$mysqlinfo=mysql_get_server_info();
	
			//从服务器中获取GD库的信息
			if(function_exists("gd_info")){
				$gd=gd_info();
				$gdinfo=$gd['GD Version'];
			}else{
				$gdinfo="未知";
			}
	
			//从GD库中查看是否支持FreeType字体
			$freetype=$gd["FreeTyep Support"]?"支持":"不支持";
	
			//从php配置文件中获得是否可以远程文件获取
			$allowurl=ini_get("file_uploads")?"支持":"不支持";
	
			//从php配置文件中获取最大上传限制
			$max_upload=ini_get("file_uploads")?ini_get("upload_max_filesize"):"Disabled";
	
			//从php配置文件中获取脚本的最大执行时间
			$max_ex_time=ini_get("max_execution_time")."秒";
	
			//以下两条获取服务器时间，中国大陆采用的是东八区的时间，设置时区为EtcGMT-8
			date_default_timezone_set("Etc/GMT-8");
				
	
	
			$server_info=array(
					'sysos'=>$sysos,
					'sysversion'=>$sysversion,
					'mysqlinfo'=>$mysqlinfo,
					'gdinfo'=>$gdinfo,
					'freetype'=>$freetype,
					'allowurl'=>$allowurl,
					'max_upload'=>$max_upload,
					'max_ex_time'=>$max_ex_time,
						
			);
			F('server_info',$server_info);
		}
		$server_info['systemtime']=date("Y-m-d H:i:s",time());
		return $server_info;
	
	}
}
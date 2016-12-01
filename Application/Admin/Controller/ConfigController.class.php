<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class ConfigController extends CommonController{
	
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	/**
	 * 配置列表
	 */
	public function clist(){
		$content_header = array(
				'pay'=>'支付方式',
				'sms'=>'短信配置',
		);
		$config = M('Config');
		$config_list = $config->where('type = "'.I('get.type').'"')->select();
		$this->assign('config_list',$config_list);
		$this->assign('content_header',$content_header[I('get.type')]);
		$this->display();
	}
	/**
	 * 配置信息
	 */
	public function info(){
		$config = M('Config');
		$info = $config->where('code = "'.I('get.code').'"')->find();
		$info['config']= unserialize($info['config']);
		$this->assign('ci',$info);
		$this->display();
	}
	/**
	 * 更新配置信息
	 */
	public function update(){
		$config = M('Config');
		$data = array(
				'type'=>I('post.type'),
				'code'=>I('post.code'),
				'enabled'=>I('post.enabled'),
		);
		unset($_POST['code']);
		unset($_POST['enabled']);
		unset($_POST['type']);
		$data['config']=serialize($_POST);
		if(is_numeric($config->save($data))){
			$this->createConfig($data['type']);
			$this->success('编辑成功');
		}else{
			$this->error('编辑失败');
		}
	}
	/**
	 * 生成支付配置文件
	 */
	private function createConfig($type){
		$list = M('Config')->where('enabled = 1 AND type = "'.$type.'"')->field('code,config')->select();
		foreach ($list as $item){
			$data[$item['code']] = unserialize($item['config']);
		}
		$string = '<?php return '.var_export($data,true).';';
		$myfile = fopen('./Common/Conf/'.$type.'.php', "w") or die("Unable to open file!");
		fwrite($myfile, $string);
		fclose($myfile);
	}
	
	
	
	private function createWxpay(){
		$config_str = '<?php class WxPayConfig{';
		foreach ($_POST as $k=>$v){
			$config_str .='const '. $k." = '".$v."';";
		}
		$config_str .='} ?>';
		$myfile = fopen('./ThinkPHP/Extend/Vendor/WxpayV3/WxPay.Config.php', "w") or die("Unable to open file!");
		fwrite($myfile, $config_str);
		fclose($myfile);
	}
}

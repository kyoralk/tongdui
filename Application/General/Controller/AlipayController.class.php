<?php
namespace General\Controller;
use General\Util\Alipay;

class AlipayController extends Alipay{
	protected $alipay_config;
	
	protected function setConfig(){
		$setting = require './Common/Conf/pay.php';
		$this->alipay_config['partner']		= $setting['alipay']['partner'];
		//安全检验码，以数字和字母组成的32位字符
		$this->alipay_config['key']			= $setting['alipay']['key'];
		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		//签名方式 不需修改
		$this->alipay_config['sign_type']    = strtoupper('MD5');
		//字符编码格式 目前支持 gbk 或 utf-8
		$this->alipay_config['input_charset']= strtolower('utf-8');
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$this->alipay_config['cacert']    = getcwd().'\\cacert.pem';

		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$this->alipay_config['transport']    = 'http';
		$this->alipay_config['email'] = $setting['alipay']['seller_email'];//付款账号
		$this->alipay_config['account_name'] = '湘西自治州和谐网络科技有限公司';//付款账户名
		
	}
	
	public function send($data, $notify_url=""){
		$this->setConfig();
		//服务器异步通知页面路径
		$notify_url = $notify_url?$notify_url:"http://www.tdsc18.com/batch_trans_notify-PHP-UTF-8/notify_url.php";
		//构造要请求的参数数组，无需改动
		$parameter = array(
				'service' => 'batch_trans_notify',
				'partner' => trim($this->alipay_config['partner']),
				'notify_url'	=> $notify_url,
				'email'	=> $this->alipay_config['email'],
				'account_name'	=> $this->alipay_config['account_name'],
				'pay_date'	=> $data['pay_date'],
				'batch_no'	=> $data['batch_no'],
				'batch_fee'	=> $data['batch_fee'],//付款总金额
				'batch_num'	=> $data['batch_num'],//必填，即参数detail_data的值中，“|”字符出现的数量加1，最大支持1000笔（即“|”字符出现的数量999个）
				'detail_data'	=> $data['detail_data'],//必填，格式：流水号1^收款方帐号1^真实姓名^付款金额1^备注说明1|流水号2^收款方帐号2^真实姓名^付款金额2^备注说明2....
				'_input_charset'	=> trim(strtolower($this->alipay_config['input_charset']))
		);
		echo $this->buildRequestForm($parameter,"get", "确认");
	}
}
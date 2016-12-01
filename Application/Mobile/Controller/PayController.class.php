<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;

class PayController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	/**
	 * 支付方式列表
	 */
	public function plist(){
		jsonReturn(M('Payment')->where('enabled = 1')->field('pay_id,pay_code,pay_name')->select());
	}
	/**
	 * 配置信息
	 */
	public function config(){
		jsonReturn($this->payConfig(I('get.pay_code','')));
	}
	
}
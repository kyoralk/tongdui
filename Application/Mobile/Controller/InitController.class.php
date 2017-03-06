<?php
namespace Mobile\Controller;
use General\Controller\GeneralController;

class InitController extends GeneralController{
	protected  $member_info;
	protected function _initialize(){
		$token = I('param.token');
		if(!empty($token)){
			$this->setMemberInfo(array('token'=>$token));
			if(empty($this->member_info)){
				jsonReturn('','01003');
			}
		}
		$this->setLang();
	}
	//设置语言
	protected function setLang(){
		$lang = F('lang');
		if(empty($lang[cookie('unique_id')])){
			C('VAR_LANGUAGE',1);
		}else{
			C('VAR_LANGUAGE',$lang[cookie('unique_id')]);
		}
	}
	/**
	 * 设置用户信息
	 * @param array $condition
	 */
	protected function setMemberInfo($condition){
		$this->member_info = M('Member',C('DB_PREFIX_C'))->where($condition)->find();
	}
	/**
	 * 检查用户名是否重复
	 * @param string $username
	 */
	protected function checkName($username){
		if(empty($username)){
			jsonReturn('','01004');
		}
		$count = M('Member')->where('binary username = "'.$username.'"')->count();
		if($count != 0){
			jsonReturn('','01005');
		}
	}
	/**
	 * 检测手机号是否重复
	 * @param string $mobile
	 */
	protected function checkMobile($mobile,$bind = false){
		if(empty($mobile)){
			jsonReturn(null,'01006');
		}
		$count = M('Member')->where('binary mobile = "'.$mobile.'"')->count();
		if($bind & $count == 0){
			jsonReturn(null,'01010');
		}
		if(!$bind &$count != 0){
			jsonReturn(null,'01007');
		}
	}
	/**
	 * 验证密码
	 * @param string $password
	 */
	protected function checkPassword($password){
		if(empty($password)){
			jsonReturn('','01013');
		}
	}
	/**
	 * 返回支付的异步通知地址
	 */
	protected function notifyURL(){
		return array(
				'notify_alipay'=>'Notify/alipay',
				'notify_wxpay'=>'Notify/wxpay',
				'notify_unionpay'=>'Notify/unionpay',
				'notify_client'=>'Notify/client'
		);
	}
	/**
	 * 捐产品收货地址
	 */
	protected function grantAddress(){
		$setting = M('Setting',C('DB_PREFIX_C'))->getField('item_key,item_value',true);
		return array('consignee'=>$setting['sitename'],'mobile'=>$setting['telphone'],'address'=>$setting['address']);
	}
}
<?php
namespace Mall\Controller;
use Mall\Controller\InitController;

class PublicController extends InitController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	public function checkLogin(){
		
	}
	public function register(){
		layout(false);
		$this->display();
	}
	/**
	 * 注册
	 */
	public function registerDo(){
		$username = I('post.username');
		$password = I('post.password');
// 		$mobile = I('post.mobile');
// 		$this->checkName($username);// 检测用户名
// 		$this->checkPassword($password);//检测密码
// 		$this->checkMobile($mobile);//检测手机号
		//6位随机数
		$salt = randstr(6);
		$data=array(
				"username"=>$username,
				'password' =>md5(md5($password).$salt),
				// 				'mobile' =>$mobile,
				'salt' =>$salt,
				'register_time'=>time(),
				'token'=>md5(md5(time()).$salt),
				'member_account'=>array(
						'YJT_FEE'=>0,
						'YJT_GIVE'=>0,
						'GWQ_FEE'=>0,
						'BDB_FEE'=>0,
						'DZB_FEE'=>0,
						'ZCB_FEE'=>0,
						'JF_SUM'=>0,
						'JF_FEE'=>0,
						'JF_FEEZE'=>0,
				),
		);
		$uid = D('Member')->relation('member_account')->add($data);
		if($uid){
			session('uid',$uid);
			session('username',$username);
			redirect('/Seller');
		}else{
			jsonReturn('','01008');
		}
	}
	/**
	 * 检验用户名是否存在
	 */
	public function checkName(){
		$count= M('Member')->where('username = "'.I('post.username').'"')->count();
		if($count == 0){
			$this->ajaxReturn(array('status'=>1),'JSON');
		}else{
			$this->ajaxReturn(array('status'=>0),'JSON');
		}
	}
	/**
	 * 检测验证码
	 */
	public function checkCode(){
		if($this->checkVerify(I('post.code'))){
			$this->ajaxReturn(array('status'=>1,'code'=>session('verify_code')),'JSON');
		}else{
			$this->ajaxReturn(array('status'=>0,'code'=>session('verify_code')),'JSON');
		}
	}
}
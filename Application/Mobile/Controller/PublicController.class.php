<?php
namespace Mobile\Controller;
use Mobile\Controller\InitController;
use General\Util\Image;
class PublicController extends InitController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	/**
	 * 启动
	 */
	public function run(){
		if(empty(cookie('unique_id'))){
			cookie('unique_id',md5(time()));
		}
		if(empty(cookie('unique_id'))){
			jsonReturn('','01001');
		}else{
			jsonReturn(cookie('unique_id'));
		}
	}
	/**
	 * 设置语言
	 */
	public function lang(){
		$default_lang = I('post.lang',C('DEFAULT_LANG'));
		$lang = F('lang');
		$lang[cookie('unique_id')] = $default_lang;
		F('lang',$lang);
		C('DEFAULT_LANG',$lang[cookie('unique_id')]);//设置语言包
		if(C('DEFAULT_LANG')==$default_lang){
			jsonReturn();
		}else{
			jsonReturn('','00001');
		}
	}
	/**
	 * 发送短信验证码
	 */
	public function sendCode(){
		if(I('get.skip')!=1){
			if(!$this->checkVerify(I('get.verify_code'))){
				jsonReturn('','01014');
			}
		}
		if($this->sendSMS(I('get.mobile'),I('get.sign'))){
			jsonReturn();
		}else{
			jsonReturn('','00000');
		}
	}
	/**
	 * 登陆检测
	 */
	public function checkLogin() {
		$username = I('post.username');
		if(is_numeric($username)){
			$res=M('Member')->where('binary mobile = "'.$username.'"')->find();
		}else{
			$res=M('Member')->where('binary username = "'.$username.'"')->find();
		}
		if($res){
			if(md5(md5(I('post.password')).$res['salt'])==$res['password'] && $res['user_status']){
				$data = array(
						'username'=>$res['username'],
						'mobile'=>$res['mobile'],
						'rank'=>$res['rank'],
						'real_name_auth'=>$res['real_name_auth'],
						'head_photo'=>$res['head_photo'],
						'token'=>$res['token'],
				);
				jsonReturn($data);
			}else{
				jsonReturn(null,'01002');
			}
		}else{
			jsonReturn('','01003');
		}
	}
	/**
	 * 注册
	 */
	public function registerDo(){
// 		if(!$this->checkSMSCode(I('post.sms_code'))){
// 			jsonReturn('','01011');
// 		}
		$username = 'u'.I('post.mobile');
		$password = I('post.password');
		$mobile = I('post.mobile');
		$referrer_id = empty(I('post.referrer_id')) ? 1 : I('post.referrer_id');
		$referrer_node_id = empty(I('post.referrer_node_id')) ? 1 : I('post.referrer_node_id');
		$position = empty(I('post.position')) ? 'left' : I('post.position');
// 		$this->checkName($username);// 检测用户名
		$this->checkPassword($password);//检测密码
		$this->checkMobile($mobile);//检测手机号
		//6位随机数
		$salt = randstr(6);
		$data=array(
				"username"=>$username,
				'password' =>md5(md5($password).$salt),
				'mobile' =>$mobile,
				'referrer_id' =>$referrer_id,
				'referrer_node_id'=>$referrer_node_id,
				'position'=>$position,
				'salt' =>$salt,
				'register_time'=>time(),
				'token'=>md5(md5(time()).$salt),
				'member_account'=>array(
						'YJT_FEE'=>0,
						'YJT_GIVE'=>0,
						'YJT_FEEZE'=>0,
						'GWQ_FEE'=>0,
						'BDB_FEE'=>0,
						'DZB_FEE'=>0,
						'ZCB_FEE'=>0,
						'JF_SUM'=>0,
						'JF_FEE'=>0,
						'JF_FEEZE'=>0,
				),
		);
		if(D('Member')->relation('member_account')->add($data)){
			$res = array(
					'username'=>$data['username'],
					'mobile'=>$data['mobile'],
					'token' =>$data['token'],
			);
			jsonReturn($res);
		}else{
			jsonReturn('','01008');
		}
	}
	/**
	 * 忘记密码
	 */
	public function forgetPassword(){
// 		if(!$this->checkVerify(I('get.verify_code'))){
// 			jsonReturn('','01014');
// 		}
		$mobile = I('get.mobile');
		$this->checkMobile($mobile,true);
		if($this->sendSMS($mobile, 0)){
			jsonReturn();
		}else{
			jsonReturn('','01009');
		}
	}
	/**
	 * 重置密码
	 */
	public function resetPassword(){
		if($this->checkSMSCode(I('post.sms_code'))){
			$mobile = I('post.mobile');
			$this->checkMobile($mobile,true);
			$password = I('post.password');
			$this->checkPassword($password);
			$salt = randstr(6);
			$data ['password'] = md5(md5(I('post.password')).$salt);
			$data ['salt'] = $salt;
			if(M('Member')->where ('mobile = "'.$mobile.'"')->save($data)){
				jsonReturn();
			}else{
				jsonReturn('','01012');
			}
		}else{
			jsonReturn('','01011');
		}
	}
	/**
	 * 二维码
	 */
	public function qrcode(){
		$data = I('param.data');
		$size = I('param.size',10);
		
		$res = Image::qrcode(urlencode($data),$size);
	}
	public function marketParams(){
		$code_type = I('get.code_type',0);
		if($code_type){
				
		}else{
			redirect('/Mobile/Public/download');
		}
	}
	public function getGrantAddress(){
		jsonReturn($this->grantAddress());
	}
	public function getset(){
		jsonReturn($this->setting());
	}
	
}
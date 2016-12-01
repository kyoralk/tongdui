<?php
namespace Seller\Controller;
use General\Util\XFile;
use General\Util\Image;
use Mall\Controller\InitController;

class IndexController extends InitController{
	protected function _initialize(){
		$set = InitController::setting();
		$this->assign('set',$set);
	}
	/**
	 * 首页
	 */
	public function index(){
		if(!empty(session('store_id'))){
			redirect(U('Center/index'));
		}else{
			layout(false);
			$this->display('Public/login');
		}
		exit();
		if(empty(session('store_id'))){
			$store_status = 'meiyou';
		}else{
			$store_status = M('Store')->where('store_id = '.session('store_id'))->getField('store_status');
		}
		session('store_status',$store_status);
		$this->display();
	}
	/**
	 * 登陆检测
	 */
	public function checkLogin(){
		if($this->checkVerify(I('post.verify_code'))){
			$info = M('Member',C('DB_PREFIX_C'))->where('username = "'.I('post.username').'"')->field('uid,username,password,salt,store_id')->find();
			if(md5(md5(I('post.password')).$info['salt']) == $info['password']){
				session('uid',$info['uid']);
				session('username',$info['username']);
				session('store_id',$info['store_id']);
				$this->ajaxReturn(array('status'=>1),'JSON');
			}else{
				$this->ajaxReturn(array('info'=>'用户名或密码错误','status'=>0),'JSON');
			}			
		}else{
			$this->ajaxReturn(array('info'=>'验证码不正确','status'=>0),'JSON');
		}
	}
	/**
	 * 检测店铺名称
	 */
	public function checkName(){
		if(M('Store')->where('store_name = "'.I('get.store_name').'"')->count()){
			$this->ajaxReturn(array('status'=>0),'JSON');
		}else{
			$this->ajaxReturn(array('status'=>1),'JSON');
		}
	}
	/**
	 * 入驻页面
	 */
	public function join(){
		if(session('uid')){
			if(session('store_id')){
				redirect('/Seller/Public/login');
			}else{
				$this->display();
			}
		}else{
			redirect('/Seller');
		}
	}
	/**
	 * 入驻
	 */
	public function joinDo(){
		$data=array(
				'store_name'=>I('post.store_name'),
				'username'=>session('username'),
				'uid'=>session('uid'),
				'company_name'=>I('post.company_name'),
				'company_location'=>I('post.company_location'),
				'company_address'=>I('post.company_address'),
				'company_phone'=>I('post.company_phone'),
				'employees_count'=>I('post.employees_count'),
				'registered_capital'=>I('post.registered_capital'),
				'contacts_name'=>I('post.contacts_name'),
				'contacts_phone'=>I('post.contacts_phone'),
				'contacts_email'=>I('post.contacts_email'),
				'business_licence_number'=>I('post.business_licence_number'),
				'business_scope'=>I('post.business_scope'),
				'join_time'=>time(),
				//'sc_id'=>$_POST['sc_id'],
		);
		$res = Image::upload('business_licence_pic', 'MEMBER');
		$data['business_licence_pic'] = $res['savename'];
		$store_id = M('Store')->add($data);
		if($store_id){
			M('Member',C('DB_PREFIX_C'))->where('uid = '.session('uid'))->setField('store_id',$store_id);
			session('store_id',$store_id);
			$seller_path = './Uploads/Mall/Seller/'.$store_id;
			XFile::createDir($seller_path);
			XFile::createDir($seller_path.'/Content');
			XFile::createDir($seller_path.'/Goods');
			XFile::createDir($seller_path.'/Goods/Thumb');
			XFile::createDir($seller_path.'/Store');
			$this->success('提交成功','/Seller/Center/Index/index');
		}else{
			$this->error('提交失败');
		}
	}
}
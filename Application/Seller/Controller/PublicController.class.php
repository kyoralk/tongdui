<?php
namespace Seller\Controller;
use General\Controller\GeneralController;

class PublicController extends GeneralController{
	public function login(){
		if(!empty(session('store_id'))){
			redirect(U('Center/index'));
		}else{
			layout(false);
			$this->display();
		}
		
	}
	/**
	 * 登陆检测
	 */
	public function checkLogin(){
		if($this->checkVerify(I('post.verify'))){
			$info = M('Member m',C('DB_PREFIX_C'))->join(C('DB_PREFIX_MALL').'store s on m.uid = s.uid ')->where('m.username = "'.I('post.username').'"')->field('m.uid,m.username,m.password,m.salt,s.store_id,s.store_status')->find();
			if(empty($info['store_id'])){
				$this->ajaxReturn(array('info'=>'你还不是卖家','status'=>0),'JSON');
				exit();
			}
			if($info['store_status']!=1){
				$store_status = array('店铺已关闭','','店铺审核中');
				$this->ajaxReturn(array('info'=>$store_status[$info['status']],'status'=>0),'JSON');
			}
			if(md5(md5(I('post.password')).$info['salt']) == $info['password']){
				session('store_id',$info['store_id']);
				session('uid',$info['uid']);
				session('username',$info['username']);
				$this->ajaxReturn(array('status'=>1),'JSON');
			}else{
				$this->ajaxReturn(array('info'=>'用户名或密码错误','status'=>0),'JSON');
			}			
		}else{
			$this->ajaxReturn(array('info'=>'验证码不正确','status'=>0),'JSON');
		}
	}
	/**
	 * 退出登录
	 */
	public function logout(){
		session(null);
		$this->success('退出成功',U('Public/login'));
	}
}
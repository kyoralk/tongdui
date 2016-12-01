<?php
namespace Admin\Controller;
use Think\Controller;
class PublicController extends Controller{
	public function login(){
		layout(false);
		$this->display();
	}
	/**
	 * 登陆检测
	 */
	public function checkLogin(){
		$Admin = M('Admin',C('DB_PREFIX_C'));
		$admin_info = $Admin->where('admin_name = "'.I('post.admin_name').'"')->field('admin_id,password,role_id')->find();
		if($admin_info['password']==md5(I('post.password'))){
			session('admin_name',I('post.admin_name'));
			session('admin_id',$admin_info['admin_id']);
			session('role_id',$admin_info['role_id']);
			redirect(U('/Admin/Index'));
		}else{
			$this->error('用户名或密码错误');
		}
	}
	/**
	 * 退出登录
	 */
	public function logout(){
		session(null);
		$this->success('退出成功',U('Admin/Public/login'));
	}
}
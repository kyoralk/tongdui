<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class AdminController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	
	/**
	 * 管理员列表
	 */
	public function adminList(){
		$condition['role_id'] = array('neq',0);
		$data = page(D('Admin'), $condition,20,'relation');
		$this->assign('admin_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header','管理员列表');
		$this->display('Admin/admin_list');
	}
	/**
	 * 添加管理员模板页
	 */
	public function adminView(){
		$this->assign('role_list',M('Role')->field('role_id,role_name')->select());
		$this->assign('content_header','添加管理员');
		$this->display('Admin/admin_view');
	}
	/**
	 * 添加管理员
	 */
	public function adminAdd(){
		if(I('post.password')==I('post.re_password')){
			$Admin = M('Admin');
			$count = $Admin->where('admin_name = "'.I('post.admin_name').'"')->count();
			if($count!=0){
				$this->error('用户名被占用');
				exit();
			}
			$count = $Admin->where('mobile = "'.I('post.mobile').'"')->count();
			if($count!=0){
				$this->error('手机号码被占用');
				exit();
			}
			$data = $Admin->create();
			$data['password'] = md5(I('post.password'));
			if($Admin->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}else{
			$this->error('两次密码输入不一致');
		}
	}
	/**
	 * 删除管理员
	 */
	public function adminDelete(){
		if(I('get.admin_id')==1){
			$this->error('超级管理员不允许删除');
			exit();
		}
		if(M('Admin')->where('admin_id = '.I('get.admin_id'))->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 编辑管理员
	 */
	public function adminEdit(){
		$admin_info = M('Admin')->where('admin_id = '.I('get.admin_id'))->find();
		$this->assign('role_list',M('Role')->field('role_id,role_name')->select());
		$this->assign('ai',$admin_info);
		$this->display('Admin/admin_edit');
	}
	/**
	 * 更新管理员
	 */
	public function adminUpdate(){
		$Admin = M('Admin');
		$data = $Admin->create();
		if(!empty(I('post.password'))){
			if(I('post.password')!=I('post.re_password')){
				$this->error('两次密码输入不一致');
				exit();
			}
			$data['password'] = md5(I('post.password'));
		}
		$count = $Admin->where('admin_id != '.I('post.admin_id').' AND admin_name = "'.I('post.admin_name').'"')->count();
		if($count!=0){
			$this->error('用户名被占用');
			exit();
		}
		$count = $Admin->where('admin_id != '.I('post.admin_id').' AND mobile = "'.I('post.mobile').'"')->count();
		if($count!=0){
			$this->error('手机号码被占用');
			exit();
		}
		if(is_numeric($Admin->save($data))){
			$this->success('更新成功');
		}else{
			$this->error('更新失败');
		}
	}
	/**
	 * 修改密码
	 */
	public function updatePassword(){
		$Admin = M('Admin');
		$password = $Admin->where('admin_id = '.session('admin_id'))->getField('password');
		if($password==md5(I('post.old_password'))){
			if(!empty(I('post.password'))){
				if(I('post.password')!=I('post.re_password')){
					$this->ajaxJsonReturn('','两次密码输入不一致',0);
					exit();
				}
				if($Admin->where('admin_id = '.session('admin_id'))->setField('password',md5(I('post.password'))) !== false){
					$this->ajaxJsonReturn('','修改成功',1);
				}else{
					$this->ajaxJsonReturn('','修改失败',0);
				}
			}
		}else{
			$this->ajaxJsonReturn('','原始密码不正确',0);
		}
		
	}
	/**
	 * 角色列表
	 */
	public function roleList(){
		$Role = M('Role');
		$role_list = $Role->select();
		$this->assign('role_list',$role_list);
		$this->assign('content_header','角色列表');
		$this->display('Admin/role_list');
	}
	/**
	 * 角色添加模板页
	 */
	public function roleView(){
		$menu_list = R('Menu/menuTree');
		$this->assign('menu_list',$menu_list);
		$this->assign('content_header','添加角色');
		$this->display('Admin/role_view');
	}
	
	/**
	 * 添加/更新角色
	 */
	public function roleHandler(){
		$Role = M('Role');
		$data['role_name'] = I('post.role_name');
		$data['menu_id_str'] = implode(',', I('post.menu_id'));
		if(empty(I('post.role_id'))){
			$role_id = $Role->add($data);
			if($role_id){
				R('Menu/createMenu',array($role_id,I('post.menu_id')));
				$this->success('添加成功',U('Admin/roleList'));
			}else{
				$this->error('添加失败');
			}
		}else{
			$data['role_id'] = I('post.role_id');
			if($Role->save($data)>=0){
				R('Menu/createMenu',array(I('post.role_id'),I('post.menu_id')));
				$this->success('保存成功');
			}else{
				$this->error('保存失败');
			}
		}
	}
	/**
	 * 编辑角色
	 */
	public function roleEdit(){
		$Role = M('Role');
		$role_info = $Role->where('role_id = '.I('get.role_id'))->find();
		$menu_id_array =  explode(',', $role_info['menu_id_str']);
		$menu_list = M('Menu')->select();
		$count = count($menu_list);
		for($i=0;$i<$count;$i++){
			if(in_array($menu_list[$i]['menu_id'],$menu_id_array)){
				$menu_list[$i]['checked'] = 1;
			}
		}
		$menu_list = list_to_tree($menu_list,'menu_id','menu_pid');
		$this->assign('menu_list',$menu_list);
		$this->assign('role_info',$role_info);
		$this->display('Admin/role_view');
	}
	/**
	 * 删除角色
	 */
	public function roleDelete(){
		$file = './Application/Admin/Common/menu_'.I('get.role_id').'.php';
		$error = 0;
		if(file_exists($file)){
			if(!unlink($file)){
				$error++;
			}
		}
		if($error!=0){
			$this->error('删除失败');
			exit();
		}
		if(M('Role')->where('role_id = '.I('get.role_id'))->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
}
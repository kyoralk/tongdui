<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;
class NavigationController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_MALL').'store_');
	}
	/**
	 * 导航列表
	 */
	public function navList(){
		$condition['store_id'] = session('store_id');
		$data = page(M("Navigation"), $condition,20);
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header','导航列表');
		$this->display('Navigation/nav_list');
	}
	/**
	 * 添加导航模板页
	 */
	public function view(){
		$this->assign('content_header','添加导航');
		$this->display();
	}
	/**
	 * 添加导航
	 */
	public function add(){
		$nav = M('Navigation');
		$data = $nav->create();
		$data['store_id'] = session('store_id');
		if($nav->add($data)){
			$this->success('添加成功',U('Navigation/navList'));
		}else{
			$this->error('添加失败');
		}
	}
	/**
	 * 编辑导航
	 */
	public function edit(){
		$nav_info= M('Navigation')->where('id = '.I('get.id'))->find();
		$this->assign('nav_info',$nav_info);
		$this->display();
	}
	/**
	 * 更新导航
	 */
	public function update(){
		$nav = M('Navigation');
		$data = $nav->create();
		if(is_numeric($nav->save($data))){
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}
	/**
	 * 删除导航
	 */
	public function delete(){
		if(M('Navigation')->where('id = '.I('get.id'))->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
}
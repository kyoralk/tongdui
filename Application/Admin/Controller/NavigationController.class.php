<?php
namespace Admin\Controller;
use Admin\Controller\MallController;
use General\Util\Image;

class NavigationController extends MallController{
	/**
	 * 导航列表
	 */
	public function navList(){
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
		if(!empty($_FILES['nav_thumb'])){
			$res = Image::upload('nav_thumb','MALL_NAV');
			$data['nav_thumb'] = $res['savename'];
		}
		if($nav->add($data)){
			$this->success('添加成功');
		}else{
			$this->error('添加失败');
		}
	}
	/**
	 * 编辑导航
	 */
	public function edit(){
		$nav_info= M('Navigation')->where('id = '.I('get.id'))->find();
		$nav_info['nav_thumb'] = '/Uploads/'.C('MALL_NAV').$nav_info['nav_thumb'];
		$this->assign('nav_info',$nav_info);
		$this->display();
	}
	/**
	 * 更新导航
	 */
	public function update(){
		$nav = M('Navigation');
		$data = $nav->create();
		if(!empty($_FILES['nav_thumb'])){
			$res = Image::upload('nav_thumb','MALL_NAV');
			$data['nav_thumb'] = $res['savename'];
		}
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
		$nav = M('Navigation');
		$nav_thumb = $nav->where('id = '.I('get.id'))->getField('nav_thumb');
		if(!empty($nav_thumb)){
			$nav_thumb = './Uploads/'.C('MALL_NAV').$nav_thumb;
			if(file_exists($nav_thumb)){
				unlink($nav_thumb);
			}
		}
		if($nav->where('id = '.I('get.id'))->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 删除缩略图
	 */
	public function navThumbDelete(){
		$file = I('get.path');
		$file = '.'.$file;
		if(file_exists($file)){
			if(unlink($file)){
				if(M('Navigation')->where('id = '.I('get.id'))->setField('nav_thumb', '')){
					$this->ajaxJsonReturn('','删除成功',1);
				}else{
					$this->ajaxJsonReturn('','删除失败',0);
				}
			}
		}else{
			$this->ajaxJsonReturn('','删除失败',0);
		}
	}
}
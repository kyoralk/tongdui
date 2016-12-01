<?php
namespace Admin\Controller;
use Admin\Controller\MallController;

class ClassController extends MallController{
	/**
	 * 商品分类列表
	 */
	public function classList(){
		$condition['gc_parent_id'] = I('get.id',0);
		$goods_class_list = M('GoodsClass')->where($condition)->select();
		$this->assign('content_header','商品分类');
		$this->assign('list',$goods_class_list);
		$this->display('Class/class_list');
	}
	/**
	 * 添加分类模板页
	 */
	public function view(){
		$GoodsClass = M('GoodsClass');
		$class_list = $GoodsClass->field('gc_id,gc_parent_id,gc_name')->select();//获取所有的分类
		$class_list = list_to_tree($class_list,'gc_id','gc_parent_id');//把所有的分类转换成树状结构
		$this->assign('class_list',$class_list);
		$this->assign('gc_parent_id',I('get.gc_id',0));
		$this->assign('model_list',ModelController::returnModel());
		$this->assign('content_header','添加分类');
		$this->display('Class/info');
	}
	/**
	 * 编辑分类
	 */
	public function edit(){
		$GoodsClass = M('GoodsClass');
		$class_list = $GoodsClass->field('gc_id,gc_parent_id,gc_name')->select();//获取所有的分类
		$class_list = list_to_tree($class_list,'gc_id','gc_parent_id');//把所有的分类转换成树状结构
		$class_info = $GoodsClass ->where('gc_id = '.I('get.gc_id'))->find();//获取当前分类的信息
		$this->assign('class_info',$class_info);
		$this->assign('class_list',$class_list);
		$this->assign('gc_parent_id',$class_info['gc_parent_id']);
		$this->assign('model_list',ModelController::returnModel());
		$this->assign('content_header','编辑分类');
		$this->display('Class/info');
	}
	/**
	 * 保存分类
	 */
	public function save(){
		$GoodsClass = M('GoodsClass');
		$data = array(
				'gc_name'=>I('post.gc_name'),
				'gc_parent_id'=>I('post.gc_parent_id'),
				'gc_sort'=>I('post.gc_sort'),
				'gc_keywords'=>I('post.gc_keywords'),
				'gc_description'=>I('post.gc_description'),
				'gc_icon'=>I('post.gc_icon'),
				'mid'=>I('post.mid'),
		);
		$gc_id = I('post.gc_id',0);
		if($gc_id){
			if(is_numeric($GoodsClass->where('gc_id = '.$gc_id)->save($data))){
				$this->success('保存成功');
			}else{
				$this->error('保存失败');
			}
		}else{
			if($GoodsClass->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
			
		}
	}
	public function _after_save(){
		$this->setGoodsClass();
	}
	/**
	 * 删除分类
	 */
	public function delete(){
		$gc_id = I('get.gc_id');
		$condition['gc_parent_id'] = array('IN',$gc_id);
		$GoodsClass = M('GoodsClass');
		$them_gc_id = $GoodsClass->where($condition)->getField('gc_id',true);
		$delete_id_array = $them_gc_id;
		if(!empty($them_gc_id)){
			$condition['gc_parent_id'] = array('IN',$them_gc_id);
			$them_gc_id = $GoodsClass->where($condition)->getField('gc_id',true);
			if(!empty($them_gc_id)){
				$delete_id_array = array_merge($delete_id_array,$them_gc_id);
			}
		}
		if(is_array($gc_id)){
			$delete_id_array = array_merge($delete_id_array,$gc_id);
		}else{
			$delete_id_array[] = $gc_id;
		}
		unset($condition);
		$condition['gc_id'] = array('IN',$delete_id_array);
		if($GoodsClass->where($condition)->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	public function _after_delete(){
		$this->setGoodsClass();
	}
	/**
	 * 获取所有分类并转换成树状结构
	 */
	public function classTree(){
		$goods_class_list = M('GoodsClass')->field('gc_id,gc_name,gc_parent_id')->select();
		return list_to_tree($goods_class_list,'gc_id','gc_parent_id');
	}
}
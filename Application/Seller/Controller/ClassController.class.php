<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;
class ClassController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_MALL').'store_');
	}
	/**
	 * 获取店铺分类
	 * @param string $sc_parent_id 取出某一种分类
	 * @param string $field 要查询的字段
	 * @param string $list_to_tree 是否转换成树状结构
	 */
	public function getClass($field = '*',$sc_parent_id = '',$list_to_tree = true){
		$condition['store_id'] = session('store_id');
		if(is_numeric($sc_parent_id)){
			$condition['sc_parent_id'] = $sc_parent_id;
		}
		$goods_class_list = M('GoodsClass',C('DB_PREFIX_MALL').'store_')->where($condition)->field($field)->select();
		if($list_to_tree){
			$goods_class_list = list_to_tree($goods_class_list,'sc_id','sc_parent_id');
		}
		return $goods_class_list;
	}
	/**
	 * 商品分类列表
	 */
	public function classList(){
		$this->assign('content_header','商品分类');
		$this->assign('list',$this->getClass());
		$this->display('Class/class_list');
	}
	/**
	 * 添加分类模板页
	 */
	public function view(){
		$this->assign('class_list',$this->getClass('sc_id,sc_parent_id,sc_name',0));
		$this->assign('sc_parent_id',I('get.sc_id',0));
		$this->assign('content_header','添加分类');
		$this->display('Class/info');
	}
	/**
	 * 编辑分类
	 */
	public function edit(){
		$class_info = M('GoodsClass') ->where('sc_id = '.I('get.sc_id'))->find();//获取当前分类的信息
		$this->assign('class_info',$class_info);
		$this->assign('class_list',$this->getClass('sc_id,sc_parent_id,sc_name',0));
		$this->assign('sc_parent_id',$class_info['sc_parent_id']);
		$this->assign('content_header','编辑分类');
		$this->display('Class/info');
	}
	/**
	 * 保存分类
	 */
	public function save(){
		$GoodsClass = M('GoodsClass');
		$data = array(
				'sc_name'=>I('post.sc_name'),
				'sc_parent_id'=>I('post.sc_parent_id'),
				'sc_sort'=>I('post.sc_sort'),
				'sc_keywords'=>I('post.sc_keywords'),
				'sc_description'=>I('post.sc_description'),
				'store_id'=>session('store_id'),
		);
		$sc_id = I('post.sc_id',0);
		if($sc_id){
			if(is_numeric($GoodsClass->where('sc_id = '.$sc_id)->save($data))){
				$this->success('保存成功');
			}else{
				$this->error('保存失败');
			}
		}else{
			if($GoodsClass->add($data)){
				$this->success('添加成功',U('Class/classList'));
			}else{
				$this->error('添加失败');
			}
			
		}
	}
	/**
	 * 批量保存
	 */
	public function saves(){
		if(empty(I('post.sc_id'))){
			$this->error('请选择要更新的数据');
			exit();
		}
		$data = array(
				'sc_name'=>I('post.sc_name'),
				'sc_keywords'=>I('post.sc_keywords'),
				'sc_description'=>I('post.sc_description')
		);
		$condition = array('sc_id'=>I('post.sc_id'));
		if(is_numeric($this->saveAll('__GOODS_CLASS__', $data, $condition))){
			$this->success('更新成功');
		}else{
			$this->error('更新失败');
		}
	}
	/**
	 * 删除分类
	 */
	public function delete(){
		if(empty(I('get.sc_id'))){
			$this->error('请选择要删除的数据');
			exit();
		}
		$sc_id = I('get.sc_id');
		$condition['sc_parent_id'] = array('IN',$sc_id);
		$GoodsClass = M('GoodsClass');
		$them_sc_id = $GoodsClass->where($condition)->getField('sc_id',true);
		$delete_id_array = $them_sc_id;
		if(!empty($them_sc_id)){
			$condition['sc_parent_id'] = array('IN',$them_sc_id);
			$them_sc_id = $GoodsClass->where($condition)->getField('sc_id',true);
			if(!empty($them_sc_id)){
				$delete_id_array = array_merge($delete_id_array,$them_sc_id);
			}
		}
		if(is_array($sc_id)){
			$delete_id_array = array_merge($delete_id_array,$sc_id);
		}else{
			$delete_id_array[] = $sc_id;
		}
		unset($condition);
		$condition['sc_id'] = array('IN',$delete_id_array);
		if($GoodsClass->where($condition)->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 获取所有分类并转换成树状结构
	 */
	public function classTree(){
		$goods_class_list = M('GoodsClass')->field('sc_id,sc_name,sc_parent_id')->select();
		return list_to_tree($goods_class_list,'sc_id','sc_parent_id');
	}
}
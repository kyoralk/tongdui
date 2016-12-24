<?php
namespace Admin\Controller;
use Admin\Controller\MallController;
use General\Util\Image;

class BrandController extends MallController{
	/**
	 * 品牌列表
	 */
	public function brandList(){
		$brand = M('Brand');
		$data = page($brand, $condition,20,'','brand_sort desc');
		$this->assign("page", $data['page']);
		$this->assign('brand_list',$data['list']);
		$this->assign('content_header','品牌列表');
		$this->assign('right_menu',array('url'=>U('Brand/view'),'icon'=>'fa-plus','text'=>'添加品牌'));
		$this->display('Brand/brand_list');
	}
	/**
	 * 添加品牌模板页
	 */
	public function view(){
		$class_list = M('GoodsClass')->where('gc_parent_id = 0')->field('gc_id,gc_name')->select();
		$this->assign('class_list',$class_list);
		$this->assign('content_header','添加品牌');
		$this->display('Brand/info');
	}
	/**
	 * 编辑品牌
	 */
	public function edit(){
		$class_list = M('GoodsClass')->where('gc_parent_id = 0')->field('gc_id,gc_name')->select();
		$brand_info = M('Brand')->where('brand_id = '.I('get.brand_id'))->find();
		$brand_info['brand_logo'] = '/Uploads/'.C('MALL_BRAND').$brand_info['brand_logo'];
		$this->assign('brand_info',$brand_info);
		$this->assign('class_list',$class_list);
		$this->assign('content_header','编辑品牌');
		$this->display('Brand/info');
	}
	/**
	 * 保存品牌
	 */
	public function save(){
		$Brand = M('Brand');
		$data = $Brand->create();

		$brand_id = I('post.brand_id',0);
		if($_FILES['brand_logo']){
			$res = Image::upload('brand_logo', 'MALL_BRAND');
			$data['brand_logo'] = $res['savename'];
		}
		if($brand_id){
			if(is_numeric($Brand->save($data))){
				$this->success('更新成功');
			}else{
				$this->error('更新失败');
			}
		}else{
			if($Brand->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
	}
	/**
	 * 删除品牌
	 */
	public function delete(){
		$Brand = M('Brand');
		$brand_logo = $Brand->where('brand_id = '.I('get.brand_id'))->getField('brand_logo');
		if(!empty($brand_logo)){
			$brand_logo = './Uploads/'.C('MALL_BRAND').$brand_logo;
			if(file_exists($brand_logo)){
				unlink($brand_logo);
			}
		}
		if($Brand->where('brand_id = '.I('get.brand_id'))->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 删除logo
	 */
	public function logoDelete(){
		$file = I('get.path');
		$file = '.'.$file;
		if(file_exists($file)){
			if(unlink($file)){
				if(M('Brand')->where('brand_id = '.I('get.id'))->setField('brand_logo', '')){
					$this->ajaxJsonReturn('','删除成功',1);
				}else{
					$this->ajaxJsonReturn('','删除失败',0);
				}
			}
		}else{
			$this->ajaxJsonReturn('','删除失败',0);
		}
	}	
	/**
	 * 获取品牌
	 */
	public function getBrand(){
		//查找父目录
		$GoodsClass = M('GoodsClass');
		$gc_id = I('get.gc_id',0);
		if($gc_id){
			do{
				$gc_parent_id = $GoodsClass->where('gc_id = '.$gc_id)->getField('gc_parent_id');
				if($gc_parent_id){
					$gc_id = $gc_parent_id;
				}
			}while($gc_parent_id);
			$brand_list = M('Brand')->where('gc_id = '.$gc_id)->field('brand_id,brand_name')->select();
			$this->ajaxJsonReturn($brand_list);
		}else{
			$this->ajaxJsonReturn('','',0);
		}
	}
	
}
<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;

class BrandController extends CommonController{
	/**
	 * 获取品牌
	 * @param string $field
	 * @param string $condition
	 */
	public static function getBrand($field = '*',$condition = '',$order = 'first_letter'){
		$brand_list = M('Brand')->where($condition)->field($field)->order($order)->select();
		return $brand_list;
	}
	/**
	 * ajax 返回品牌
	 */
	public function ajaxBrand(){
		if(!empty($_GET['first_letter'])){
			$condition['first_letter'] = I('get.first_letter');
		}
		if(!empty($_GET['keyword'])){
			$condition['brand_name'] = array('like','%'.I('get.keyword').'%');
		}
		$brand_list = self::getBrand('brand_id,brand_name,first_letter',$condition);
		$this->ajaxReturn($brand_list,'JSON');
	}
	/**
	 * 判断是否重复
	 */
	public function checkBrandName(){
		$count = M('Brand')->where('brand_name = "'.I('get.brand_name').'"')->count();
		if($count == 0){
			$this->ajaxReturn(1,'JSON');
		}else{
			$this->ajaxReturn(0,'JSON');
		}
	}
	/**
	 * 申请列表
	 */
	public function applyList(){
		$list = M('BrandApply')->where('store_id = '.session('store_id'))->select();
		$this->assign('list',$list);
		$this->display('Brand/apply_list');
	}
	/**
	 * 申请品牌
	 */
	public function apply(){
		if(IS_POST){
			$Brand = M('BrandApply');
			$data = array(
					'brand_name'=>I('post.brand_name'),
					'brand_desc'=>empty(I('post.brand_desc')) ? '' : I('post.brand_desc'),
					'gc_id'=>I('post.gc_id'),
					'first_letter'=>I('post.first_letter'),
					'store_id'=>session('store_id'),
			);
			if(I('post.store_logo')){
				$res = $Images->createImg(array(I('post.brand_logo')), 'MALL_BRAND');
				$data['brand_logo'] = $res[0];
			}
			if($Brand->add($data)){
				$this->success('申请成功');
			}else{
				$this->error('申请失败');
			}
				
				
		}else{
			$class_list = M('GoodsClass')->where('gc_parent_id = 0')->field('gc_id,gc_name')->select();
			$this->assign('class_list',$class_list);
			$this->display('Brand/brand_apply');
		}
	
	}
	
}
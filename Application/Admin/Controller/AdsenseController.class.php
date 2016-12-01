<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
use General\Util\Image;

class AdsenseController extends CommonController{
	/**
	 * 广告位列表
	 */
	public function positionList(){
		$data = page(M('AdsensePosition'), $condition,20);
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header','广告位列表');
		$this->assign('right_menu',array('url'=>U('Adsense/position'),'icon'=>'fa-plus','text'=>'添加广告位'));
		$this->display('Adsense/position_list');
	}
	/**
	 * 添加/编辑广告位
	 */
	public function position(){
		$position_id = I('get.position_id',0);
		if($position_id){
			$info = M('AdsensePosition')->where('position_id = '.$position_id)->find();
			$this->assign('info',$info);
			$this->assign('content_header','编辑广告位');
		}else{
			$this->assign('content_header','添加广告位');
		}
		$this->display();
	}
	/**
	 * 保存广告位
	 */
	public function savePosition(){
		$data = array(
				'position_name'=>I('post.position_name'),
				'position_width'=>$_POST['position_width'],
				'position_height'=>$_POST['position_height'],
				'enable'=>I('post.enable',0),
		);
		$position_id = I('post.position_id',0);
		if($position_id){
			//更新广告位
			if(is_numeric(M('AdsensePosition')->where('position_id = '.$position_id)->save($data))){
				$this->success('更新成功',U('Adsense/positionList'));
			}else{
				$this->error('更新失败');
			}
		}else{
			//添加广告位
			if(M('AdsensePosition')->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
		
	}
	/**
	 * 删除广告位
	 */
	public function deletePosition(){
		if(M('AdsensePosition')->where('position_id = '.I('get.position_id'))->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 广告列表
	 */
	public function adsenseList(){
		$data = page(D('Adsense'), $condition,20,'relation');
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header','广告列表');
		$this->assign('right_menu',array('url'=>U('Adsense/adsense'),'icon'=>'fa-plus','text'=>'添加广告'));
		$this->display('Adsense/adsense_list');
	}
	/**
	 * 添加/编辑广告
	 */
	public function adsense(){
		$ad_id = I('get.ad_id',0);
		if($ad_id){
			$info = D('Adsense')->relation(true)->where('ad_id = '.$ad_id)->find();
			$info['adsense'] = '/Uploads/'.$info['ad_path'].$info['ad_value'];
			$this->assign('info',$info);
			$this->assign('content_header','编辑广告');
		}else{
			$this->assign('content_header','添加广告');
		}
		$position_list = M('AdsensePosition')->select();
		$this->assign('position_list',$position_list);
		$this->display();
	}
	/**
	 * 保存广告
	 */
	public function adsenseSave(){
		$data = array(
				'ad_url'=>I('post.ad_url'),
				'is_target'=>I('post.is_target',0),
				'position_id'=>I('post.position_id'),
				'enabled'=>I('post.enabled',0),
				'goods_id'=>I('post.goods_id'),
		);
		if($_FILES['adsense']){
			$res = Image::upload('adsense', 'ADSENSE');
			$data['ad_value'] = $res['savename'];
			$data['ad_path'] = $res['savepath'];
		}
		$ad_id = I('post.ad_id');
		$Adsense = M('Adsense');
		if($ad_id){
			//更新广告
			$info = $Adsense->where('ad_id = '.$ad_id)->field('ad_value,ad_path')->find();
			unlink('./Uploads/'.$info['ad_path'].$info['ad_value']);
			if(is_numeric($Adsense->where('ad_id = '.$ad_id)->save($data))){
				$this->success('更新成功');
			}else{
				$this->error('更新失败');
			}
			
		}else{
			//添加广告
			if($Adsense->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}
	}
	/**
	 * 删除广告
	 */
	public function adsenseDelete(){
		$Adsense = M('Adsense');
		$info = $Adsense->where('ad_id = '.I('get.ad_id'))->field('ad_value,ad_path')->find();
		unlink('./Uploads/'.$info['ad_path'].$info['ad_value']);
		if($Adsense->where('ad_id = '.I('get.ad_id'))->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}	
	/**
	 * ajax 删除广告图片
	 */
	public function imgDelete(){
		$file = I('get.path');
		$file = '.'.$file;
		if(file_exists($file)){
			if(unlink($file)){
				if(M('Adsense')->where('ad_id = '.I('get.id'))->save(array('ad_value'=>'','ad_path'=>''))){
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
<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
use General\Util\Image;
class SlideController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	
	
	/**
	 * 幻灯列表
	 */
	public function slist(){
		
		$slide = M('Slide');
		$slide_list=$slide->select();
		$this->assign('slide_list',$slide_list);
		$this->assign('content_header','幻灯列表');
		$this->assign('right_menu',array('url'=>U('Slide/info'),'icon'=>'fa-plus','text'=>'添加幻灯'));
		$this->display();
	}
	/**
	 * 添加/编辑幻灯
	 */
	public function info(){
		if(I('get.id')){
			$slide = M('Slide');
			$info=$slide->where('id  = '.I('get.id'))->find();
			$info['img'] = '/Uploads/'.$info['save_path'].$info['save_name'];
			$this->assign('info',$info);
			$this->assign('content_header','编辑幻灯片');
		}else{
			$this->assign('content_header','添加幻灯片');
		}
		$this->assign('module_list',C('FRONT_MODULE_LIST'));
		$this->display();
	}
	/**
	 * 添加幻灯
	 */
	public function add(){
		$slide = M('Slide');
		$data['url'] = I('post.url');;
		$data['alt'] = I('post.alt');
		$data['sort'] = I('post.sort');
		$data['bg_color'] = I('post.bg_color');
		$data['enabled'] = empty(I('post.enabled'))?0:1;
		$data['position'] = I('post.position');
		$data['module'] = I('post.module');
		$data['goods_id'] = I('post.goods_id');
		if(!empty($_FILES['img'])){
			$res = Image::upload('img', 'SLIDE');
			$data['save_name'] = $res['savename'];
			$data['save_path'] = $res['savepath'];
		}
		if($slide->add($data)){
			F('slide',null);//清空缓存
			$this->success('添加成功',U('Slide/slist'));
		}else{
			$this->error('添加失败');
		}
	}
	/**
	 * 幻灯更新
	 */
	public function update(){
		$slide = M('Slide');
		$data['id']=I('post.id');
		$data['url'] = I('post.url');;
		$data['alt'] = I('post.alt');
		$data['sort'] = I('post.sort');
		$data['bg_color'] = I('post.bg_color');
		$data['enabled'] = empty(I('post.enabled'))?0:1;
		$data['position']= I('post.position');
		$data['module'] = I('post.module');
		$data['goods_id'] = I('post.goods_id');
		if(!empty($_FILES)){
			if(file_exists('./Uploads/'.C('SLIDE').I('post.oldimg')) & !empty(I('post.oldimg'))){
				$del=unlink('./Uploads/'.C('SLIDE').I('post.oldimg'));
			}else{
				$del=true;
			}
			if($del){
				$res = Image::upload('img', 'SLIDE');
				$data['save_name'] = $res['savename'];
				$data['save_path'] = $res['savepath'];
			}
		}
		if($slide->save($data)){
			F('slide',null);//清空缓存
			$this->success('更新成功');
		}else{
			$this->error('更新失败');
		}
	}
	/**
	 * 删除幻灯
	 */
	public function delete(){
		$slide = M('Slide');
		$img = $slide->where('id = '.I('get.id'))->getField('img');
		unlink('./Uploads/'.C('SLIDE').$img);
		if($slide->where('id = '.I('get.id'))->delete()){
			$this->success('删除成功',U('Slide/slist'));
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * ajax删除
	 */
	public function imgDelete(){
		$file = I('get.path');
		$file = '.'.$file;
		if(file_exists($file)){
			if(unlink($file)){
				if(M('Slide')->where('id = '.I('get.id'))->save(array('save_name'=>'','save_path'=>''))){
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
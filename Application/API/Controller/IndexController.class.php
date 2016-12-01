<?php
namespace API\Controller;
use Think\Controller;

class IndexController extends Controller{
	/**
	 * 首页
	 */
	public function index(){
		layout(false);
		$group = M('Group')->select();
		$list = M('List')->field('id,name,gid')->select();
		foreach ($list as $item){
			$api[$item['gid']][] = $item;
		}
		foreach ($group as $item){
			$catalog[] = array('name'=>$item['name'],'api'=>$api[$item['gid']]);
		}
		$this->assign('catalog',$catalog);
		$this->display();
	}
	/**
	 * 欢迎页
	 */
	public function home(){
		$this->display();
	}
	/**
	 * 接口展示页
	 */
	public function view(){
		$info = M('List')->where('id = '.I('get.id'))->find();
		$this->assign('info',$info);
		$this->assign('params',unserialize($info['params']));
		$this->display();
	}
	/**
	 * 新建/编辑接口
	 */
	public function info(){
		if(!empty(I('get.id'))){
			$info = M('List')->where('id = '.I('get.id'))->find();
			$this->assign('info',$info);
			$this->assign('params',unserialize($info['params']));
			$this->assign('box_title','编辑接口');
		}else{
			$this->assign('box_title','新建接口');
		}
		$group = M('Group')->select();
		$this->assign('group',$group);
		$this->display();
	}
	/**
	 * 添加/更新接口
	 */
	public function save(){
		$p_name = I('post.p_name');
		$p_type = I('post.p_type');
		$p_must = I('post.p_must');
		$p_default = I('post.p_default');
		$p_desc = I('post.p_desc');
		$count = count($p_name);
		for($i = 0;$i<$count;$i++){
			if(!empty($p_name[$i])){
				$params[$i] = array(
						'p_name'=>$p_name[$i],
						'p_type'=>$p_type[$i],
						'p_must'=>$p_must[$i],
						'p_default'=>$p_default[$i],
						'p_desc'=>$p_desc[$i],
				);
			}
		}
		
		$data = array(
				'name'=>I('post.name'),
				'url'=>I('post.url'),
				'type'=>I('post.type'),
				'params'=>serialize($params),
				'result'=>I('post.result'),
				'desc'=>$_POST['desc'],
				'gid'=>empty(I('post.gid'))? 0 : I('post.gid'),
		);
		
		$id = I('post.id');
		if($id){
			if(is_numeric(M('List')->where('id = '.$id)->save($data))){
				$this->success('更新成功',U('Index/view',array('id'=>$id)));
			}else{
				$this->error('更新失败');
			}
		}else{
			if(M('List')->add($data)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');	
			}
		}
	}
	/**
	 * 添加分组
	 */
	public function addGroup(){
		$gid = M('Group')->add(array('name'=>I('post.name')));
		if($gid){
			$this->ajaxReturn($gid,'JSON');
		}else{
			$this->ajaxReturn(0,'JSON');
		}
	}
	/**
	 * 搜索
	 */
	public function search(){
		$list = M('List')->where('`name` LIKE "%'.I('get.keyword').'%"')->field('id,name,gid')->select();
		$this->ajaxReturn($list,'JSON');
	}
}
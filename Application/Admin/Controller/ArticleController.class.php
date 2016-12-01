<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class ArticleController extends CommonController{
	public function clist(){
		$n = 3;
		$class=M('ArticleClass');
		$class_list=$class->where('pid = 0')->order('sort')->select();
		$pcount = count($class_list);
		if($n>1){
			for($i=0;$i<$pcount;$i++){
				$class_list[$i]['child_a']=$class->where('pid = '.$class_list[$i]['ac_id'])->order('sort')->select();
				if($n>2){
					$a = count($class_list[$i]['child_a']);
					for($j=0;$j<$a;$j++){
						$class_list[$i]['child_a'][$j]['child_b']=$class->where('pid = '.$class_list[$i]['child_a'][$j]['ac_id'])->order('sort')->select();
					}
				}
			}
		}
		$this->assign('class_list',$class_list);
		$this->display();
	
	}
	
	
	public function addClass(){
		$ac = M('ArticleClass');
		$ac_name = $_POST['ac_name'];
		$sort = $_POST['sort'];
		$pid=$_POST['pid'];
		$count = count($pid);
		for($i=0;$i<$count;$i++){
			$data[$i]=array(
					'ac_name'=>$ac_name[$i],
					'pid'=>$pid[$i],
					'sort'=>$sort[$i],
			);
		}
		if($ac->addAll($data)){
			$this->success('添加成功',U('Article/clist'));
		}else{
			$this->error('添加失败');
		}
	
	}
	
	
	public function ajaxupdate(){
		$ac = M('ArticleClass');
		$data=array(
				'ac_id'=>$_POST['ac_id'],
				'ac_name'=>$_POST['ac_name'],
				'sort'=>$_POST['sort'],
		);
		$res=$ac->save($data);
		if($res>=0){
			$data = $this->ajaxReturn(array('data' => '', 'status' => 1),'',1);
		}else{
			$this->ajaxReturn('','',0);
		}
	}
	
	
	public function view(){
	
		$class=M('ArticleClass');
		$class_list=$class->select();
		$this->assign('class_list',$class_list);
		$this->display();
	}
	
	
	public function article(){
		$article = D('Article');
		$order = 'article_id desc';
		$data = page($article ,'',10,'relation',$order);
		$this->assign('article_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->display();
	}
	
	
	public function add(){
	
		$article = M('Article');
		$data=array(
				'article_title'=>$_POST['article_title'],
				'article_content'=>stripslashes($_POST['content']),
				'ac_id'=>$_POST['ac_id'],
				'add_time'=>time(),
		);
		if($article->add($data)){
			$this->success('添加成功');
		}else{
			$this->error('添加失败');
		}
	}
	
	public function info(){
		$ac = M('ArticleClass');
		$ac_list=$ac->where('is_show = 1')->select();
		$article = D('Article');
		$article_info=$article->relation(true)->where('article_id = '.$_GET['article_id'])->select();
		$this->assign('article_info',$article_info);
		$this->assign('ac_list',$ac_list);
		$this->display();
	}
	
	public function update(){
		$article = M('Article');
		$data=array(
				'article_title'=>$_POST['article_title'],
				'article_content'=>stripslashes($_POST['content']),
				'ac_id'=>$_POST['ac_id'],
				'add_time'=>time(),
				'article_id'=>$_POST['article_id'],
		);
		if($article->save($data)){
			$this->success('更新成功','/Admin/Article/article');
		}else{
			$this->error('更新失败');
		}
	}
	
	public function delete(){
		$article = M('Article');
		if($article->where('article_id = '.$_GET['article_id'])->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}

}
<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;

class CollectController extends CommonController{
	private $collect;
	private $data;
	private $type;
	private $M;
	private $M_condition;
	protected function _initialize(){
		parent::_initialize();
		$this->type = empty($_POST['type']) ? 1 : I('post.type');
		$this->data['uid'] = $this->member_info['uid'];
		$collect_id = I('post.collect_id');
		if($this->type == 1){
			$this->collect = M('CollectGoods');
			$this->data['goods_id'] = $collect_id;
			$this->M = M('Goods');
			$this->M_condition['goods_id'] = $collect_id;
		}else{
			$this->collect = M('CollectStore');
			$this->data['store_id'] = $collect_id;
			$this->M = M('Store');
			$this->M_condition['store_id'] = $collect_id;
		}
	}
	/**
	 * 收藏商品/店铺
	 */
	public function set(){
		$count = $this->collect->where($this->data)->count();
		if(I('post.read_only') == 1){
			jsonReturn(array('count'=>$count));
		}
		if($count>0){
			if($this->collect->where($this->data)->delete()){
				$this->M->where($this->M_condition)->setDec('collect_times');
				jsonReturn();
			}else{
				jsonReturn('','00000');
			}
		}else{
			if($this->collect->add($this->data)){
				$this->M->where($this->M_condition)->setInc('collect_times');
				jsonReturn();
			}else{
				jsonReturn('','00000');
			}
		}
	}
	/**
	 * 收藏列表
	 */
	public function glist(){
		$condition['uid'] = $this->member_info['uid'];
		$count = M('CollectGoods')->where($condition)->count();
		$data = appPage(D('CollectGoodsView'), $condition, I('get.num'), I('get.p'),'view','id desc','*','id',$count);
		jsonReturn($data);
	}
	public function slist(){
		$condition['c.uid'] = $this->member_info['uid'];
		$data = appPage(D('CollectStoreView'), $condition, I('get.num'), I('get.p'),'view','id desc');
		jsonReturn($data);
	}
}
<?php
namespace Mall\Controller;
use Mall\Controller\InitController;

class GoodsController extends InitController{
	/**
	 * 处理分类id
	 */
	private function hgcid($gc_id){
		$condition['gc_parent_id'] = $gc_id;
		$data[] = $gc_id;
		$GoodsClass = M('GoodsClass');
		do{
			$gc_id_list =$GoodsClass->where($condition)->getField('gc_id',true);
			if(!empty($gc_id_list)){
				$data = array_merge($data,$gc_id_list);
				$condition['gc_parent_id'] = array('IN',$gc_id_list);
				$gc_id_list = $GoodsClass->where($condition)->getField('gc_id');
			}
		}while (!empty($gc_id_list));
		return $data;
	}
	/**
	 * 同店铺产品
	 */
	private function sglist($store_id,$limit){
		if(S('sglist_'.$store_id)){
			return S('sglist_'.$store_id);
		}else{
			$goods_list = D('GoodsView')->where('is_on_sale = 1 AND Goods.store_id = '.$store_id)->limit($limit)->order('rand()')->group('goods_id')
			->field('goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path')->select();
			S('sglist_'.$store_id,$goods_list,86400);
			return $goods_list;
		}
	}
	/**
	 * 同类产品
	 */
	private function tlglist($gc_id,$limit){
		if(S('tlglist_'.$gc_id)){
			return S('tlglist_'.$gc_id);
		}else{
			$goods_list = D('GoodsView')->where('is_on_sale = 1 AND gc_id = '.$gc_id)->limit($limit)->order('rand()')->group('goods_id')
			->field('goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path')->select();
			S('tlglist_'.$gc_id,$goods_list,86400);
			return $goods_list;
		}
	
	}
	/**
	 * 商品列表
	 */
	public function glist(){
		$gc_id = I('get.gc_id',0);
		if($gc_id){
			$condition['is_on_sale'] = array('IN',$this->hgcid($gc_id));
		}
		$condition['is_on_sale']=1;
		$count = M('Goods')->where($condition)->count();
		$Goods = D('GoodsView');
		$data = page($Goods, $condition,20,'view','goods_id desc','goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path,evaluate_count,click_count','goods_id',$count);
		
		$this->assign('list',$data['list']);
		$this->assign('page',$data['page']);
		$this->display();
	}
	/**
	 * 商品详情
	 */
	public function info(){
		$Goods = D('Goods');
		$Goods->where('goods_id = '.I('get.goods_id'))->setInc('click_count');//添加点击次数（关注度）
		$goods_info = $Goods->relation(true)->where('goods_id = '.I('get.goods_id'))->find();
		$this->assign('gi',$goods_info);
		
		//$this->assign('store_info',R('Store/info',array($goods_info['store_id'])));//店铺信息
		$this->assign('sglist',$this->sglist($goods_info['store_id'], 20));//同店铺产品
		$this->assign('tlglist',$this->tlglist($goods_info['gc_id'], 20));//同类产品
		$this->display();
	}
	
}
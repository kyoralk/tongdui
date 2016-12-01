<?php
namespace Mall\Controller;
use Mall\Controller\InitController;
use General\Util\Image;
class IndexController extends InitController{
	public function index(){
		$this->display();
		exit();
		$this->assign('slide_list',$this->getSlide());//首页幻灯
		//限时折扣
    	if(S('promote_list')){
    		$this->assign('promote_list',S('promote_list'));
    	}else{
    		$goods_list = $this->catGoods(1);
    		S('promote_list',$goods_list);
    		$this->assign('promote_list',$goods_list);
    	}
    	//商城热门
    	if(S('hot_list')){
    		$this->assign('hot_list',S('hot_list'));
    	}else{
    		$goods_list = $this->catGoods(2);
    		S('hot_list',$goods_list);
    		$this->assign('hot_list',$goods_list);
    	}
    	//新品
    	if(S('new_list')){
    		$this->assign('new_list',S('new_list'));
    	}else{
    		$goods_list = $this->catGoods(3);
    		S('new_list',$goods_list);
    		$this->assign('new_list',$goods_list);
    	}
    	//精品推荐
    	if(S('best_list')){
    		$this->assign('best_list',S('best_list'));
    	}else{
    		$goods_list = $this->catGoods(4);
    		S('best_list',$goods_list);
    		$this->assign('best_list',$goods_list);
    	}
    	//热销排行
    	if(S('sales_list')){
    		$this->assign('sales_list',S('sales_list'));
    	}else{
    		$goods_list = $this->catGoods(5);
    		S('sales_list',$goods_list);
    		$this->assign('sales_list',$goods_list);
    	}
    	$this->assign('show_left_menu',1);
		$this->display();
	}
	protected function hello1(){
		echo 'hello1';
	}
	public static function hello(){
		IndexController::hello1();
	}
	public function test(){
		self::hello();
		exit();
		$res1 = Image::upload('photo', 'MEMBER');
// 		$res2 = Image::upload('photo2', 'MEMBER');
		dump($res1);
// 		dump($res2);
	}
	
	/**
	 * 幻灯
	 */
	public function getSlide(){
		if(F('slide')){
			return F("slide");
		}else{
			$slide = M('Slide',C('DB_PREFIX_C'));
			$condition['position'] = 1;
			$condition['enabled'] = 1;
			$condition['module'] = C('DEFAULT_MODULE');
			$slide_list=$slide->where('position = 1 AND enabled = 1')->order('sort')->select();
			F("slide",$slide_list);
			return $slide_list;
		}
	}
	/**
	 * 分类产品
	 */
	public function catGoods($type,$num=5){
		
		$goods = D('GoodsView');
		switch ($type){
			case 1:
				$now_time = time();
				$condition['promote_price']=array('gt',0);
				$condition['promote_start_date'] = array('elt',$now_time);
				$condition['promote_end_date'] = array('egt',$now_time);
				$order = 'promote_end_date';
				break;
			case 2:
				//商城热门
				$condition['is_hot'] = 1;
				$order = 'goods_id desc';
				break;
			case 3:
				//新品
				$condition['is_new'] = 1;
				$order = 'goods_id desc';
				break;
			case 4:
				//精品推荐
				$condition['is_best'] = 1;
				$order = 'goods_id desc';
				break;
			case 5:
				//销量排行
				$order = 'sales desc';
			case 6:
				break;
		}
		$condition['is_on_sale']=1;
		$goods_list = $goods->where($condition)->limit($num)->order($order)->group('goods_id')
		->field('goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path')->select();
		return $goods_list;
	}
	/**
	 * 楼层产品
	 * @param unknown $condition
	 * @param unknown $limit
	 * @param unknown $order
	 */
	public function floorGoods($condition,$limit,$order){
		
		
		
		
		$condition['is_on_sale']=1;
		$goods = D('Goods');
		$goods_list=$goods->where($condition)->relation(true)->limit($limit)->order($order)->select();
		return $goods_list;
	}
	
	
}
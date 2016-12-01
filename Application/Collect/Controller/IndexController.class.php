<?php
namespace Collect\Controller;
use Think\Controller;
use General\Util\Image;
class IndexController extends CollectController{
	protected function _initialize(){
		set_time_limit(0);
		header("Content-Type:text/html;charset=utf-8");
	}
	
	public function index(){
		if(F('target_list')){
			$target_list = F('target_list');
		}else{
			$target_url = 'http://search.gome.com.cn/search?question=%E5%A5%B3%E8%A3%85';
			$rule = array(
					'target_url'=>$target_url,
					'content'=>array(
							'title_url'=>array('.item-name a','href'),
					),
			);
			$target_list = $this->getList($rule);
			F('target_list',$target_list);
		}
		$id = I('get.id',0);
		if(empty($target_list['title_url'][$id])){
			
			exit('end');
		}
		$this->getInfo($target_list['title_url'][$id]);
		$id++;
		$url = 'http://ms.dxeee.com/Collect/Index/index/id/'.$id;
		echo '<meta http-equiv="refresh" charset=utf-8" content="0;url='.$url.'">正在采集目标网址，当前为'.$id.'页．．．．．．';
		
	}
	
	public function getInfo($target_url){
// 		$target_url = 'http://item.gome.com.cn/A0005755262-pop8008419892.html';
	
		$rule = array(
				'target_url'=>$target_url,
				'content'=>array(
						'goods_name'=>array('.prdtit h1','text'),
				),
				
		);
		
		$info = $this->getContent($rule);
		
		$img_rule = array(
				'target_url'=>$target_url,
				'content'=>array(
						'image'=>array('.pic-small li a','img','',array(' '=>''),'','',array('/[img|IMG].*?src=[\'|\"](.*?(?:[.gif|.jpg]))[\'|\"].*?[\/]?>/')),
				),
		);
		
		$img_list = $this->getList($img_rule);
		$info['goods_img']  = $img_list['image'] ;
		$this->insertData($info);
	}

	
	
	public function insertData($info){
		//通用信息
		$data['goods_name'] = $info['goods_name'];
		$data['small_name'] = I('post.small_name');
		$data['goods_sn'] = 'MS_'.time().rand(1000,9999);//商品货号
		$data['gc_id'] = empty(I('post.gc_id')) ? 0 : I('post.gc_id');//商品分类id
		$data['brand_id'] = empty(I('post.brand_id')) ? 0 : I('post.brand_id');//商品分类id
		$data['market_price'] = empty(I('post.market_price')) ? 0: I('post.market_price');//市场价
		$data['shop_price'] = empty(I('post.shop_price')) ? rand(1000,999) : I('post.shop_price');//本店售价
		$data['max_buy'] = empty(I('post.max_buy')) ? 0 : I('post.max_buy');//最大购买量
		$data['goods_desc'] = I('post.goods_desc');//商品描述
		$data['goods_weight'] = empty(I('post.goods_weight')) ? 0 : I('post.goods_weight');//商品种类
		$data['is_on_sale'] = I('post.is_on_sale',1);//是否上架
		$data['freight_type'] = I('post.freight_type',1);//运费计算方式
		$data['keywords'] = I('post.keywords');//seo关键字
		$data['description'] = I('post.description');//seo描述
		$data['mid'] = empty(I('post.mid')) ? 0 : I('post.mid');//商品模型id
		$data['promote_price'] = empty(I('post.promote_price')) ? 0 : I('post.promote_price');//促销价格
		//处理促销时间
		$promote_start_date = empty(I('post.promote_start_date')) ? 0 : I('post.promote_start_date');
		$promote_end_date = empty(I('post.promote_end_date'))? 0 : I('post.promote_end_date');
		$data['promote_start_date'] = $promote_start_date == 0 ? 0 : strtotime($promote_start_date);//促销开始时间
		$data['promote_end_date'] = $promote_end_date == 0 ? 0 : strtotime($promote_end_date);//促销结束时间
		//处理上传图片
		//判断是否有网络图片
		if(!empty($info['goods_img'])){
			foreach ($info['goods_img'] as $item){
				$them_img = str_replace('_50.', '_800.', $item);
				$res = Iamge::downLoad($them_img,'MALL_SELLER',true,'0/Goods/');
				$data['goods_img'][] = array(
						'save_name' => $res['savename'],
						'save_path' => $res['savepath'],
				);
			}
		}
		//扩展信息
		$extend_id = I('post.extend_id');
		$atv_id = I('post.atv_id');
		$add_price = I('post.add_price');
		$atv_id_count = count($atv_id);
		for($i=0;$i<$atv_id_count;$i++){
			if($extend_id[$i]){
				$data['goods_extend'][$i]['extend_id'] = $extend_id[$i];
			}
			$data['goods_extend'][$i]['atv_id'] = $atv_id[$i];
			$data['goods_extend'][$i]['add_price'] = empty($add_price[$i]) ? 0 : $add_price[$i];
		}
		$Goods = D('Goods');
		//新增
		$Goods->relation(true)->add($data);
		
	
	}	
	
	
	
	protected function tmallEncoding($str){
		$str = mb_convert_encoding($str,'ISO-8859-1','utf-8');
		$str = mb_convert_encoding($str,'utf-8','GBK');
		return $str;
	}


	

	
}
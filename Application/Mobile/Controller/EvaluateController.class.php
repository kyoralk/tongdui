<?php
namespace Mobile\Controller;
use Mobile\Controller\InitController;
use General\Util\Image;

class EvaluateController extends InitController{
	/**
	 * 晒图
	 */
	private function up($goods_id){
		foreach ($goods_id as $item){
			$res[$item] = Image::upload('img_'.$item, 'MEMBER');
		}
		foreach ($res as $key=>$value){
			foreach ($value as $img){
				$data[$key][] = $img['savename'];
			}
		}
		return $data;
	}	

	/**
	 * 评论商品
	 * @param int $anonymous
	 * @param string $order_sn
	 * @param int $store_id
	 * @param array $goods_id
	 * @param array $score
	 * @param array $content
	 * @param string $now_time
	 * @param int $is_add
	 */
	private function goodsE($anonymous,$order_sn,$store_id,$goods_id,$score,$content,$now_time,$is_add=0){
		if(!empty($_FILES)){
			$image = $this->up($goods_id);
		}
		$count = count($score);
		for($i=0;$i<$count;$i++){
			$goods[$i] = array(
					'order_sn'=>$order_sn,
					'store_id'=>$store_id,
					'goods_id'=>$goods_id[$i],
					'score'=>$score[$i],//评分
					'content'=>urldecode($content[$i]),//评论内容
					'image'=>implode(',', $image[$goods_id[$i]]),
					'add_time'=>$now_time,
			);
			if($anonymous == 0){
				$goods[$i]['uid'] = $this->member_info['uid'];
				$goods[$i]['username'] = $this->member_info['username'];
			}else{
				$goods[$i]['anonymous'] = 1;
			}
			if($is_add){
				$goods[$i]['is_add'] = 1;
			}
		}
		$EG = M('EvaluateGoods');
		if($EG->addAll($goods)){
			if($is_add == 1){
				M('OrderInfo')->where('order_sn = "'.$order_sn.'"')->setField('evaluate_status',2);
			}else{
				M('OrderInfo')->where('order_sn = "'.$order_sn.'"')->setField('evaluate_status',1);
			}
			$this->goodsU($EG, $order_sn);
			return true;
		}else{
			E('商品评论失败');
		}
	}
	/**
	 * 
	 * @param int $anonymous
	 * @param string $order_sn
	 * @param int $store_id
	 * @param int $desccredit
	 * @param int $servicecredit
	 * @param int $deliverycredit
	 * @param string $now_time
	 */
	private function storeE($anonymous,$order_sn,$store_id,$desccredit,$servicecredit,$deliverycredit,$now_time){
		$store = array(
				'order_sn'=>$order_sn,
				'store_id'=>$store_id,
				'desccredit'=>$desccredit,
				'servicecredit'=>$servicecredit,
				'deliverycredit'=>$deliverycredit,
				'add_time'=>$now_time,
		);
		if($anonymous == 0){
			$store['uid'] = $this->member_info['uid'];
			$store['username'] = $this->member_info['username'];
		}else{
			$store['anonymous'] = 1;
		}
		$ES = M('EvaluateStore');
		if($ES->add($store)){
			$this->storeU($ES, $store_id);
			return true;
		}else{
			E('店铺评论失败');
		}
	}
	
	public function hello(){
		writeTXT(var_export($_POST,true));
		jsonReturn($_POST);
	}
	/**
	 * 更新商品评论相关数据
	 * @param obj $obj
	 * @param string $order_sn
	 */
	private function goodsU($obj,$order_sn){
		$score_list = $obj->where('order_sn = "'.$order_sn.'"')->field('goods_id,score')->select();
		$i = 0;
		$j = 0;
		do{
			if($j>0){
				if($temp_data[$i-1]['goods_id'] == $score_list[$j]['goods_id']){
					$i--;
				}
			}
			$score[$i] += $score_list[$j]['score'];
			if($score_list[$j]['score'] == 5){
				$hp[$i]+=$score_list[$j]['score'];
			}
			$temp_data[$i] = array(
					'goods_id'=>$score_list[$j]['goods_id'],
					'score'=>empty($score[$i])?:$score[$i],
					'hp'=>empty($hp[$i])? 0 : $hp[$i],
			);
			$temp_data[$i]['hpl'] = $temp_data[$i]['hp']/$temp_data[$i]['score'];
			$temp_data[$i]['evaluate_count'] = 'evaluate_count + 1 ';
			$i++;
			$j++;
		}while(!empty($score_list[$j]));
		$data['hpl'] = array_column($temp_data, 'hpl');
		$condition['goods_id'] = array_column($temp_data, 'goods_id');
		$this->saveAll('__GOODS__', $data ,$condition);
	}
	/**
	 * 更新店铺评论相关数据
	 * @param obj $obj
	 * @param int $store_id
	 */
	private function storeU($obj,$store_id){
		$res = $obj->where('store_id = '.$store_id)->field('desccredit,servicecredit,deliverycredit')->select();
		$desccredit = array_column($res, 'desccredit');
		$servicecredit = array_column($res, 'servicecredit');
		$deliverycredit = array_column($res, 'deliverycredit');
		$data = array(
				'desccredit'=>array_sum($desccredit)/count($desccredit),
				'servicecredit'=>array_sum($servicecredit)/count($servicecredit),
				'deliverycredit'=>array_sum($deliverycredit)/count($deliverycredit),
		);
		M('Store')->where('store_id = '.$store_id)->save($data);
	}
	
	/**
	 * 添加评论
	 */
	public function _before_add(){
		$token = I('param.token');
		if(!empty($token)){
			$this->member_info = M('Member',C('DB_PREFIX_C'))->where('token = "'.$token.'"')->find();
		}else{
			jsonReturn('','01020');
			exit();
		}
	}
	public function add(){
        file_put_contents('Evaluate_add', print_r($_POST, true), FILE_APPEND);
		//通用的
		$now_time = time();
		$anonymous = I('post.anonymous',0);
		$order_sn = I('post.order_sn');
		$store_id = I('post.store_id');
		//店铺的
		$desccredit = I('post.desccredit');
		$servicecredit = I('post.servicecredit');
		$deliverycredit = I('post.deliverycredit');
		//产品的
		$is_add = I('post.is_add',0);

		if(I('post.isandroid'))
        {
            $goods_id=explode(",",I('post.goods_id'));
            $score=explode(",",I('post.score'));
            $content=explode("^~^",urldecode(I('post.content')));

        }else{
            $goods_id = I('post.goods_id');
            $score = I('post.score');
            $content = I('post.content');
        }





		try {
			if($is_add != 1){
				$this->storeE($anonymous, $order_sn, $store_id, $desccredit, $servicecredit, $deliverycredit, $now_time);
			}
			$this->goodsE($anonymous, $order_sn, $store_id, $goods_id, $score, $content, $now_time,$is_add);
		} catch (Exception $e) {
			jsonReturn('','00000');
		}
		jsonReturn();
	}
	/**
	 * 评价列表
	 */
	public function elist(){
		$data = appPage(M('EvaluateGoods'), array('status'=>0,'goods_id'=>I('get.goods_id')), I('get.num'), I('get.p'),'','add_time desc','score,content,anonymous,uid,username,is_add,image');
		jsonReturn($data);
	}
}
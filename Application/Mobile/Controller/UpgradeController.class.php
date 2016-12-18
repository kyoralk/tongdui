<?php
namespace Mobile\Controller;
use Mobile\Controller\InitController;
/**
 * 
* @名称: UpgradeController
* @描述: 会员升级类
* @author hellobin zhangbin1126@126.com 
* @date 2016年8月8日 下午4:41:28
* @version V1.0
 */
class UpgradeController extends InitController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	/**
	 * 升级详情
	 */
	public function info(){
		if($this->member_info['rank']==1){
			$rc = $this->ruleConfig(4);//升级条件
			$rc = $rc['SJTJ'];
			$count_1 = $this->getMyMemberCount($this->member_info['uid']);
			$count_2 = $this->getMyGWQ($this->member_info['uid'], $rc['GWQGDCZ']);
			if($count_1>= $rc['TJGS']){
				$msg_1 = str_replace('%d', $rc['TJGS'], L('01025'));//已完成
			}else{
				$msg_1 = str_replace('%d', $rc['TJGS'] - $count_1, L('01026'));//还差几个
			}
			if($count_2){
				$msg_2 = str_replace('%d', $rc['GWQGDCZ'], L('01027'));//已完成
			}else{
				$msg_2 = str_replace('%d', $rc['GWQGDCZ'], L('01028'));//未完成
			}
			$data['upgrade_info'] = array($msg_1,$msg_2);
			$data['upgrade'] = 1;
		}else{
			$star_level_map = array(
					array('%一星%','%二星%','%三星%','%四星%'),
					array('%二星%','%三星%','%四星%'),
					array('%三星%','%四星%'),
					array('%四星%'),
			);
			if($this->member_info['star_level']<4 && $this->member_info['upgrade_times']<2){
				$condition['goods_tag'] = array('like',$star_level_map[$this->member_info['star_level']],'OR');
				$condition['store_id'] = 1;
				$condition['is_on_sale'] = 1;
				$field = 'goods_id,goods_name,market_price,shop_price,promote_price,promote_start_date,promote_end_date,save_name,save_path,store_id,evaluate_count,click_count,sales';
				C('DB_PREFIX',C('DB_PREFIX_MALL'));
				$list = D('GoodsView')->where($condition)->field($field)->group('goods_id')->select();
				$data['upgrade_info'] = $list;
				$data['upgrade'] = 1;
			}else {
				$data['upgrade_info'] = array();
			}
		}
		$data['rank'] = $this->member_info['rank'];
		$data['star_level'] = $this->member_info['star_level'];
		jsonReturn($data);
	}
	/**
	 * 预升级订单
	 */
	public function preUpgrade(){
		$params = array(
				'out_trade_no'=>serialNumber(),
				'trade_fee'=>I('post.trade_fee'),
				'trade_yjt'=>I('post.trade_yjt'),
				'upgrade_info'=>I('post.upgrade_info'),
				'token'=>I('post.token'),
		);
		if($this->verifySign($params, I('post.sign'))){
			//检测金额合法性
			$upgrade_info = json_decode($params['upgrade_info']);
			$fee = $params['trade_fee'] + $params['trade_yjt'];
			if($upgrade_info['config']['ZJE'] != $fee){
				jsonReturn(array(),'01029');
			}
			if($params['trade_fee'] == 0){
				if(!AccountController::minus($params['trade_fee'], 'YJT', 3)){
					jsonReturn(array(),'01030');
				}
				$this->hzs($uid,$upgrade_info);//升级为合作商
				$params['trade_status'] = 1;
				$response = array();
			}else{
				$response = $this->notifyURL();
				$response['out_trade_no'] = 'UPG_'.$params['out_trade_no'];
			}
			$params['uid'] = $this->member_info['uid'];
			unset($params['token']);
			if(M('Upgrade')->add($data)){
				jsonReturn($response);
			}
		}else{
			jsonReturn(array(),'01023');
		}
	}
	/**
	 * 升级为合作商并注册点位
	 */
	public function hzs($order_sn){
		$yj = M('Goods',C('DB_PREFIX_MALL'))->alias('g')->join(C('DB_PREFIX_MALL').'order_goods og on g.goods_id = og.goods_id')->where('og.order_sn = "'.$order_sn.'"')->getField('shop_price');
		$uid = $this->member_info['uid'];
		$star_level = $this->starLevel($order_sn);
		$Node = M('MemberNode');
		$count = $Node->where('uid = '.$uid)->count();
		//判断是否已有点位
		if($count == 0){
			$top_node_id = $this->leftNode($this->member_info['referrer_node_id'],$this->member_info['position']);//获取极左node_id
			$data = array(
					'uid'=>$uid,
					'star_level'=>$star_level,
			);
			$node_id = $Node->add($data);
			if($top_node_id == $this->member_info['referrer_node_id']){
				$temp_id = $Node->where('node_id = '.$top_node_id)->getField('left_node_id');
				if($temp_id){
					$add_yj['ryj'] = array('exp','ryj+'.$yj);
					$set_node['right_node_id'] = $node_id;
				}else{
					$add_yj['lyj'] = array('exp','lyj+'.$yj);
					$set_node['left_node_id'] = $node_id;
				}
			}else{
				$add_yj['lyj'] = array('exp','lyj+'.$yj);
				$set_node['left_node_id'] = $node_id;
			}
			
			
			if($top_node_id && $node_id!=$top_node_id){
				$Node->where('node_id = '.$top_node_id)->save($set_node);
				//取出可以增加业绩的人
				$top_list = $this->getTop($node_id);
				$add_yj_uid = array_column($top_list, 'uid');
				if(count($add_yj_uid)>1){
					$uid_list['uid'] = array('in',$add_yj_uid);
				}else{
					$uid_list['uid'] = $add_yj_uid[0];
				}
				$Node->where($uid_list)->save($add_yj);
			}
		}else{
			$Node->where('uid = '.$uid)->setField('star_level',$star_level);
		}
		M('Member')->where('uid = '.$uid)->save(array('rank'=>3,'star_level'=>$star_level));

		// // 增加对应的费用记录和次数记录【bug】
		// M('Member')->where('uid = '.$order_info['uid'])->save(array('upgrade_times'=> 'upgrade_times + 1',
		// 	'upgrade_fee' => 'upgrade_fee + '.$yj
		// 	));

		if($node_id){
			R('Reward/duipeng',array($node_id));//对碰奖
			R('Reward/jiandian',array($node_id,$yj));//见点奖
		}
		
	}
	/**
	 * 升级为合格的消费商
	 */
	public function hgxfs($buy = false){
		$Member = M('Member');
		if($buy){
			//如果是从购物环节触发，uid 就是推荐人的id
			$uid = $Member->where('uid = '.$this->member_info['uid'])->getField('referrer_id');
		}else{
			$uid = $this->member_info['uid'];
		}
		if($uid){
			$rank = $Member->where('uid = '.$uid)->getField('rank');
			if($rank<2){
				$rc = $this->ruleConfig(4);//升级条件
				$count = $this->getMyMemberCount($uid);//推荐了几个人
				if($count >= $rc['SJTJ']['TJGS']){
					if($this->getMyGWQ($uid, $rc['SJTJ']['GWQGDCZ'])){
						$Member->where('uid = '.$uid)->setField('rank',2);
					}
				}
			}
		}
		
	}
	/**
	 * 我推荐的人数
	 * @param unknown $uid
	 */
	private function getMyMemberCount($uid){
		return M('Member')->where('referrer_id = '.$uid)->count();//推荐了几个人
	}
	/**
	 * 累计充值购物券
	 */
	private function getMyGWQ($uid,$trade_fee){
		$where['trade_type'] = 1;
		$where['trade_code'] = 'GWQ';
		$where['uid'] = $uid;
		$sum_trade =  M('MemberAccountLog')->where($where)->sum('trade_fee');//累计充值
		if($sum_trade >= $trade_fee){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 返回极左id
	 */
	private function leftNode($node_id,$position){
		$Node = M('MemberNode');
		$temp_node_id = $Node->where('node_id = '.$node_id)->getField($position.'_node_id');
		if(empty($temp_node_id)){
			return $node_id;
			exit();
		}
		while (!empty($temp_node_id)){
			$left_node_id = $Node->where('node_id = '.$temp_node_id)->getField('left_node_id');
			if(!empty($left_node_id)){
				$temp_node_id = $left_node_id;
			}else{
				$left_node_id = $temp_node_id;
				$temp_node_id = null;
			}
		}
		return $left_node_id;
	}
	/**
	 * 计算星级
	 * @param string $order_sn
	 */
	private function starLevel($order_sn){
		$star_level_info = array('合作商一星套餐A'=>1,'合作商一星套餐B'=>1,'合作商二星套餐A'=>2,'合作商二星套餐B'=>2,'合作商三星套餐A'=>3,'合作商三星套餐B'=>3,'合作商四星套餐A'=>4,'合作商四星套餐B'=>4);
		$haystack = key($star_level_info);
		$goods_tag = M('Goods',C('DB_PREFIX_MALL'))->alias('g')->join(C('DB_PREFIX_MALL').'order_goods og on g.goods_id = og.goods_id')->where('og.order_sn = "'.$order_sn.'"')->getField('goods_tag');
		
		return $star_level_info[$goods_tag];
// 		$goods_tag_array = explode(',', $goods_tag);
// 		jsonReturn($goods_tag_array);
// 		foreach ($goods_tag_array as $needle){
// 			if (in_array($needle, $haystack)){
// 				$star_level = $star_level_info[$needle];
// 				break;
// 			}
// 		}
// 		return $star_level;
	}
	
	/**
	 * 获取双轨网络中上级
	 * @param int $uid
	 */
	private function getTop($node_id,$floor = 0){
		$Node = M('MemberNode',C('DB_PREFIX_C'));
		$top_list = array();
		// 		$i = 1;
		do{
			$node_info = $Node->where('left_node_id = '.$node_id.' OR right_node_id = '.$node_id)->field('node_id,uid,lyj,ryj,star_level')->find();
			if(!empty($node_info)){
				$node_id = $node_info['node_id'];
				$top_list[] = array(
						'uid'=>$node_info['uid'],
						'lyj'=>$node_info['lyj'],
						'ryj'=>$node_info['ryj'],
						'star_level'=>$node_info['star_level'],
						// 						'floor'=>$i,
				);
			}
			// 			$i++;
			// 			if($floor && $i > $floor){
			// 				$node_info = null;
			// 			}
		}while (!empty($node_info));
		return $top_list;
	}
}
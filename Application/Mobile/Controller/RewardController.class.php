<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;

class RewardController extends CommonController{
	/**
	 * 套餐包积分分配
	 */
	public function integral($integral,$goods_name){
		$rc = $this->ruleConfig(5);
		$rc = $rc[4]['JF'];
		$release = array();
		$now_time = time();
		$i = 0;
		while (!empty(floatval($integral[$i]))){
			$release[] = array(
					'uid'=>$this->member_info['uid'],
					'goods_name'=>$goods_name[$i],
					'base_integral'=>$integral[$i],
					'not_release'=> $integral[$i] * $rc['zzjf']/100,
					'release_proportion'=>$rc['dcsf']/100,
					'add_time'=>$now_time,
			);
			$temp_zcb = $integral[$i] * $rc['zcb']/100;//资产包
			$zcb[] = $temp_zcb;//资产包
			$uid_array[] = $this->member_info['uid'];
			$desc[] = '购买'.$goods_name[$i].'赠送'.$temp_zcb.'资产包';
			$i++;
		}
		if(!empty($uid_array)){
			$count = count($uid_array);
			if($count == 1){
				AccountController::change($this->member_info['uid'], $zcb[0], 'ZCB', 4,false,'购买'.$goods_name[0].'赠送'.$zcb[0].'资产包');//增加资产包
			}else{
				AccountController::changeALL($uid_array, $zcb, 'ZCB', 4, $desc, $reward_code);//批量增加资产包
			}
			//写入释放池
			M('Release',C('DB_PREFIX_C'))->addAll($release);
		}
		
		
	}
	/**
	 * 充值购物券赠送购物券
	 * @param float $trade_fee
	 */
	public function giveGWQ($trade_fee){
		$rc = $this->ruleConfig(1);
		$proportion = $rc['CZ']['CZ_GWQ_S_GWQ']/100;
		AccountController::change($this->member_info['uid'], $trade_fee*$proportion, 'GWQ', 4,false,'充值购物券赠送购物券');
	}

	/**
	 * 购物赠送购物券
	 * @param float $trade_fee
	 */
	public function sendGWQ($trade_fee, $order_sn=""){
		$rc = $this->ruleConfig(1);
		$proportion = $rc['CZ']['CZ_GWQ_S_GWQ']/100;
		AccountController::change($this->member_info['uid'], $trade_fee*$proportion, 'GWQ', 4,false,'购物赠送购物券,订单号：'.$order_sn);
	}
	/**
	 * 九代结算
	 * @param float $base 基数
	 * @param string $key 配置信息的key
	 */
	public function jdjs($base,$key){
		$info['CZGWQ'] = '充值购物券赠送';
		$info['XFYJT'] = '消费一卷通赠送';
		$rc = $this->ruleConfig(4);
		$rc = $rc[$key];
		$uid_array = $this->getReferrer($this->member_info['uid']);
		$i = 1;
		do{
			$yjt = $base * $rc['D'.$i][0]/100 * $rc['D'.$i][1]/100 * $rc['D'.$i][2]/100;
			$trade_fee_array[] = $yjt;
			$desc[] = $info[$key].$yjt.'一卷通';
			$i++;
		}while (!empty($uid_array[$i]));
		AccountController::changeALL(array_values($uid_array), $trade_fee_array, 'YJT', 4,$desc);//分红
	}
	/**
	 * 对碰奖
	 * @param int $node_id 双规网络节点号
	 */
	public function duipeng($node_id){
		C('DB_PREFIX',C('DB_PREFIX_C'));
		$rc = $this->ruleConfig(5);
		$dp = $rc[1]['DUIPENG'];
		if($dp['open']){
			$chongxiao = $rc[3]['CHONGXIAO']/100;//重消奖比例
			$user_list = $this->getTop($node_id);
			if(!empty($user_list)){
				foreach ($user_list as $user){
					$bi = intval($user['lyj']/$user['ryj']);
					$cap_type = null;
					$cap_fee = 0;
					if($user['lyj'] >= $rc[0][0]['min_fee'] && $user['lyj'] < $rc[0][0]['max_fee'] && $bi >= $rc[0][0]['bi']){
						$ljz = $user['ryj'] * $rc[0][0]['bi'];
						$rjz = $user['ryj'];
						$cap_type = $rc[0][0]['cap_type'];
						$cap_fee = $rc[0][0]['cap_fee'];
					}
					if($user['lyj'] >= $rc[0][1]['min_fee'] && $user['lyj'] < $rc[0][1]['max_fee'] && $bi >= $rc[0][1]['bi']){
						$ljz = $user['ryj'] * $rc[0][1]['bi'];
						$rjz = $user['ryj'];
						$cap_type = $rc[0][1]['cap_type'];
						$cap_fee = $rc[0][1]['cap_fee'];
					}
					if($user['lyj']>=$rc[0][2]['min_fee'] && $bi >= $rc[0][2]['bi']){
						$ljz = $user['ryj'] * $rc[0][2]['bi'];
						$rjz = $user['ryj'];
						$cap_type = $rc[0][2]['cap_type'];
						$cap_fee = $rc[0][2]['cap_fee'];
					}
					if($ljz>0){
						/***********组装封顶待检测数据-start***************/
						$cap_data[$cap_type][] = array(
								'uid'=>$user['uid'],
								'cap_fee'=>$cap_fee[$user['star_level']-1],
								'reward_fee'=>$ljz * $dp['bobi'][$user['star_level']]/100,//对碰人员的对碰奖金
						);
						/***********组装封顶待检测数据-end***************/
						$data['lyj'][] = 'lyj - '.$ljz;//减少第一市场业绩
						$data['ryj'][] = 'ryj - '.$rjz;//减少第二市场业绩
						$data['ljz'][] = 'ljz + '.$ljz;//增加第一市场结转
						$data['rjz'][] = 'rjz + '.$rjz;//增加第二市场结转
						$condition['uid'][] = $user['uid']; //参加对碰的用户id
					}
				}
				if(!empty($data)){
					$this->saveAll('__MEMBER_NODE__', $data, $condition,1);
					$res = $this->checkCap($cap_data, 'DPJ');//封顶检测
					$uid_array = array_column($res, 'uid');
					$desc_array = array_column($res, 'desc');
					$reward_fee_temp = array_column($res, 'reward_fee');
					foreach ($reward_fee_temp as $item){
						$chongxiao_fee = $item * $chongxiao;
						$reward_fee_array[] = $item - $chongxiao_fee;
						$chongxiao_fee_array[] = $chongxiao_fee;
						$desc_chongxiao[] = '对碰后重消奖';
					}
// 					writeTXT(var_export($uid_array,true));
// 					writeTXT(var_export($reward_fee_array,true));
					AccountController::changeALL($uid_array, $reward_fee_array, 'YJT', 4,$desc_array,'DPJ');
					AccountController::changeALL($uid_array, $chongxiao_fee_array, 'GWQ', 4,$desc_chongxiao,'CXJ');
				}
			}
		}
		
	}
	/**
	 * 见点奖
	 * @param unknown $node_id
	 * @param unknown $base_fee
	 */
	public function jiandian($node_id,$base_fee){
		$rc = $this->ruleConfig(5);
		$jiandian = $rc[2]['JIANDIAN'];
		$chongxiao = $rc[3]['CHONGXIAO']/100;//重消奖比例
		
		if($jiandian['open']){
			$Node = M('MemberNode',C('DB_PREFIX_C'));
			$star_level = $Node->where('node_id = '.$node_id)->getField('star_level');
			$start_floor = $star_level * 5 - 4;//开始层
			$end_floor = $star_level * 5;//结束层
			$user_list = $this->getTop($node_id,20);
			for($i = $star_level; $i<=$end_floor; $i++){
				if(!empty($user_list[$i]['uid'])){
					$cap_data[$jiandian['cap_type']][] = array(
							'uid'=>$user_list[$i]['uid'],
							'cap_fee'=>$jiandian['cap_fee'],
							'reward_fee'=>$base_fee * $jiandian['bobi']/100,//见点奖
					);
				}else{
					break;
				}
			}
			if(!empty($cap_data)){
				$res = $this->checkCap($cap_data, 'JDJ');//封顶检测
				$uid_array = array_column($res, 'uid');
				$desc_array = array_column($res, 'desc');
				$reward_fee_temp = array_column($res, 'reward_fee');
				foreach ($reward_fee_temp as $item){
					$chongxiao_fee = $item * $chongxiao;
					$reward_fee_array[] = $item - $chongxiao_fee;
					$chongxiao_fee_array[] = $chongxiao_fee;
					$desc_chongxiao[] = '见点后重消奖';
				}
				AccountController::changeALL($uid_array, $reward_fee_array, 'YJT', 4,$desc_array,'JDJ');
				AccountController::changeALL($uid_array, $chongxiao_fee_array, 'GWQ', 4,$desc_chongxiao,'CXJ');
			}
		}
	}
	/**
	 * 增加黑金
	 * @param unknown $trade_fee
	 * @param unknown $code
	 */
	public function heijin($trade_fee,$code){
		$Pond = M('Pond',C('DB_PREFIX_MALL'));
		$Pond->startTrans();
		$HJ_TOTAL = $Pond->where('code = "HEIJIN"')->lock(true)->getField('total');
		if($HJ_TOTAL > 0){
			$rc = $this->ruleConfig(1);
			$rc = $rc['HJ'];
			switch ($code){
				case 'XF':
					$base_fee = $rc['YJT_H'];
					$base_desc = '消费'.$trade_fee.'一卷通，赠送%h黑金';
					break;
				case 'CZ':
					$base_fee = $rc['GWQ_H'];
					$base_desc = '充值'.$trade_fee.'一卷通，赠送%h黑金';
					break;
			}
			$HJ = intval($trade_fee/$base_fee);//要赠送的黑金数
			if($HJ>0){
				//判断黑金数量是否足够
				if($HJ_TOTAL - $HJ < 0){
					$HJ = $HJ_TOTAL;
					$base_desc = str_replace('赠送%h黑金', '由于系统黑金达到上线，只能赠送%h黑金', $base_desc);
				}
				//减少黑金
				try {
					$Pond->where('code = "HEIJIN"')->setDec('total',$HJ);
				} catch (Exception $e) {
					$Pond->rollback();
					exit();
				}
				$Pond->commit();
				//赠送黑金
				$desc = str_replace('%h', $HJ, $base_desc);
				AccountController::change($this->member_info['uid'], $HJ, 'HJ', 4,false,$desc,'HJ');
			}
			
		}
		
	}
	/**
	 * 多个用户赠送黑金
	 */
	public function heijinMore($data){
		$Pond = M('Pond',C('DB_PREFIX_MALL'));
		$Pond->startTrans();
		$HJ_TOTAL = $Pond->where('code = "HEIJIN"')->lock(true)->getField('total');
		if($HJ_TOTAL > 0){
			$rc = $this->ruleConfig(1);
			$rc = $rc['HJ'];
			$base_fee = $rc['FXZSYJT_H'];
			if(!empty($data)){
				$give_count = 0;
				$temp_data = array();
				foreach ($data as $item){
					$temp_hj = intval($item['trade_fee']/$base_fee);//要赠送的黑金数
					$temp_data[] = array(
							'uid'=>$item['uid'],
							'HJ'=>$temp_hj,
							'YJT'=>$item['trade_fee'],
					);
					$give_count += $temp_hj;
				}
				
				if($HJ_TOTAL - $give_count < 0){
					$give_count = $HJ_TOTAL;
				}
				try {
					//减少黑金
					$Pond->where('code = "HEIJIN"')->setDec('total',$give_count);
				} catch (Exception $e) {
					$Pond->rollback();
					exit();
				}
				$Pond->commit();
				$i = 0;
				while ($give_count>0){
					
					if($give_count - $temp_data[$i]['HJ'] < 0){
						$trade_fee = $give_count;
						$desc[] = '获得分享赠送'.$temp_data['YJT'].'一卷通，由于系统黑金达到上线，只能赠送'.$trade_fee.'黑金';
					}else{
						$trade_fee = $temp_data[$i]['HJ'];
						$desc[] = '获得分享赠送'.$temp_data['YJT'].'一卷通，赠送'.$trade_fee.'黑金';
					}
					$give_count -= $trade_fee;
					$condition['uid'][] = $temp_data[$i]['uid'];
					$hj_data['HJ'][] = $temp_data[$i]['HJ'];
					$i++;
				}
				AccountController::changeALL($condition['uid'], $hj_data['HJ'], 'HJ', 4, $desc, 'HJ');
			}
			
			
		}
	}
	/**
	 * 奖励代理商/业务员
	 * @param array $order_goods
	 */
	public function agent($order_goods){
		$goods_id = array_column($order_goods, 'goods_id');
		$spec_id = array_filter(array_column($order_goods, 'spec_id'));
		$condition['g.goods_id'] = count($goods_id) > 1 ? array('in',$goods_id) : $goods_id[0];
		if(!empty($spec_id)){
			$condition['gs.spec_id'] = count($spec_id) > 1 ? array('in',$spec_id) : $spec_id[0];
			$field = 'g.goods_id,gs.cost_price,s.store_id,s.company_province,s.company_city,s.company_district,s.uid';
			$data = M('Goods',C('DB_PREFIX_MALL'))->alias('g')->join(C('DB_PREFIX_MALL').'goods_spec gs on g.goods_id = gs.goods_id')->join(C('DB_PREFIX_MALL').'store s on g.store_id=s.store_id')->where($condition)->field($field)->select();
		}else{
			$field = 'g.goods_id,g.cost_price,s.store_id,s.company_province,s.company_city,s.company_district,s.uid';
			$data = M('Goods',C('DB_PREFIX_MALL'))->alias('g')->join(C('DB_PREFIX_MALL').'store s on g.store_id=s.store_id')->where($condition)->field($field)->select();
			
		}
		//把原始数据按照uid分组 $data_group_by_uid
		$data_group_by_uid = array();
		foreach ($data as $item){
			$data_group_by_uid[$item['uid']][] = $item;
		}
		//根据uid查询推荐人id
		$uid = array_column($data, 'uid');
		$condition = null;
		$condition['uid'] = count($uid) > 1 ? array('in',$uid) : $uid[0]; 
		$condition['referrer_id'] = array('gt',0);
		$referrer_list_temp = M('Member',C('DB_PREFIX_C'))->where($condition)->field('uid,referrer_id')->select();
		if(!empty($referrer_list_temp)){
			$referrer_id = array_column($referrer_list_temp, 'referrer_id');
			$condition = null;
			$condition['uid'] = count($referrer_id) > 1 ? array('in',$referrer_id) : $referrer_id[0];
			$referrer_info = M('Member')->where($condition)->getField('uid,agent_level,agent_province,agent_city,agent_district',true);//查询直接推荐人的代理级别
			//整理有资格获得奖励的数据 $reward_temp
			if(!empty($referrer_info)){
				foreach ($order_goods as $og){
					$prosum[$og['goods_id']] = $og['prosum'];
				}
				$cost_price = 0;//初始化供货价
				$res = array();
				foreach ($referrer_list_temp as $item){
					//计算供货价
					foreach ($data_group_by_uid[$item['uid']] as $g){
						$cost_price += $g['cost_price'] * $prosum[$g['goods_id']];
					}
					$goods = $data_group_by_uid[$item['uid']][0];
					$referrer = $referrer_info[$item['referrer_id']];
					$res_onece = $this->outorin($referrer,$goods,$cost_price);
					if(!empty($res_onece)){
						$res = array_merge($res,$res_onece);
					}
				}
				if(!empty($res)){
					$uid_array = array_column($res, 'uid');
					$trade_fee_array = array_column($res, 'reward');
					$desc = array_column($res, 'desc');
					AccountController::changeALL($uid_array, $trade_fee_array, 'YJT', 4, $desc, 'GHJ');
				}
				
			}
		}
		
	}
	/**
	 * 判断内部还是外部
	 */
	private function outorin($referrer,$goods,$cost_price){
		$rc = $this->ruleConfig(6);
		$zhijie_nei_bi = 0;//直接并且是省/市/县 内部 推荐提成比例
		$similarity = 0;//相似度
		if($referrer['agent_province'] == $goods['company_province']){
			$similarity +=1;
			if($referrer['agent_city'] == $goods['company_city']){
				$similarity +=1;
				if($referrer['agent_district'] == $goods['company_district']){
					$similarity +=1;
				}
			}
		}
		switch ($referrer['agent_level']){
			case 1:
				//县代理
				//判断县内还是县外
				if($similarity == 3){
					$zhijie_nei_bi = $rc['1_V_N_G_I'];//县代里直接对接的企业/商家/代理商
				}else{
					$yewuyuanbi = $rc['1_V_N_G_O'];
				}
				$agent_city = $this->topRegionId($referrer['agent_district']);
				$agent_province = $this->topRegionId($agent_city);
				$zhijiebi[] = array('bi'=>$rc['2_V_1_G'],'agent_city'=>$agent_city);//本市内县代里直接对接的企业/商家/代理商奖励给市代理
				$zhijiebi[] = array('bi'=>$rc['3_V_1_G'],'agent_province'=>$agent_province);//本省内县代理直接对接的企业/商家/代理商奖励给省代理
				break;
			case 2:
				//市代理
				//判断市内还是市外
				if($similarity >= 2){
					$zhijie_nei_bi = $rc['2_V_N_G_I'];//市代里直接对接的企业/商家/代理商
				}else{
					$yewuyuanbi = $rc['2_V_N_G_O'];
				}
				$zhijiebi[] = array('bi'=>$rc['3_V_2_G'],'agent_city'=>$this->topRegionId($referrer['agent_city']));//本省内市代理直接对接的企业/商家/代理商奖励给省代理
				break;
			case 3:
				//省代理
				//判断省内还是县外
				if($similarity >= 1){
					$zhijie_nei_bi[] = $rc['3_V_N_G_I'];//省代里直接对接的企业/商家/代理商
				}else{
					$yewuyuanbi = $rc['3_V_N_G_O'];
				}
				break;
		}
		$data = $this->arrange($zhijiebi,$cost_price);
		if(!empty($zhijie_nei_bi)){
			if(!empty($referrer['uid'])){
				$data[] = array('reward'=>$cost_price * $zhijie_nei_bi/100,'uid'=>$referrer['uid'],'desc'=>'代理商提成');
			}
		}else{
			if(!empty($referrer['uid'])){
				$data[] = array('reward'=>$cost_price * $yewuyuanbi/100,'uid'=>$referrer['uid'],'desc'=>'业务员提成');
			}
		}
		return $data;
	}
	/**
	 * 整理待奖励的人(代理商/业务员)
	 * @param unknown $zhijiebi
	 */
	private function arrange($zhijiebi,$cost_price){
		$Member = M('Member',C('DB_PREFIX_C'));
		$data = array();
		foreach ($zhijiebi as $item){
			if(!empty($item['agent_city'])){
				$where['agent_city'] = $item['agent_city'];
				$where['agent_level'] = 2;
			}
			if(!empty($item['agent_province'])){
				$where['agent_province'] = $item['agent_province'];
				$where['agent_level'] = 3;
			}
			if(!empty($where)){
				$reward = $cost_price * $item['bi']/100;
				$uid_list = $Member->where($where)->getField('uid',true);
				if(!empty($uid_list)){
					foreach ($uid_list as $uid){
						$data[] =array(
								'reward'=>$reward,
								'uid'=>$uid,
								'desc'=>'代理商提成',
						);
					}
				}
				
			}
		}
		return $data;
	
	}
	
	/**
	 * 查询上级区域id
	 * @param unknown $region_id
	 */
	private function topRegionId($region_id){
		return  M('Region',C('DB_PREFIX_C'))->where('id = '.$region_id)->getField('pid');
	}
	/**
	 * 获取上级(推荐网络)
	 */
	private function getReferrer($uid){
		$uid_array = array();
		$Member = M('Member',C('DB_PREFIX_C'));
		$i = 1;
		do{
			$temp_uid = $Member->where('uid = '.$uid)->getField('referrer_id');
			if($temp_uid){
				$uid_array[$i] = $temp_uid;
				$uid = $temp_uid;
				$i++;
			}else{
				$i = 10;
			}
		}while ($i<=9);
		
		return $uid_array;
	}
	/**
	 * 获取双轨网络中上级
	 * @param int $uid
	 */
	private function getTop($node_id,$floor = 0){
		$Node = M('MemberNode',C('DB_PREFIX_C'));
		$top_list = array();
		$i = 1;
		do{
			$node_info = $Node->where('left_node_id = '.$node_id.' OR right_node_id = '.$node_id)->field('node_id,uid,lyj,ryj,star_level')->find();
			if(!empty($node_info)){
				$node_id = $node_info['node_id'];
				$top_list[] = array(
						'uid'=>$node_info['uid'],
						'lyj'=>$node_info['lyj'],
						'ryj'=>$node_info['ryj'],
						'star_level'=>$node_info['star_level'],
						'floor'=>$i,
				);
			}
			$i++;
			if($floor && $i > $floor){
				$node_info = null;
			}
		}while (!empty($node_info));
		return $top_list;
	}
	/**
	 * 封顶检测
	 */
	private function checkCap($cap_data,$reward_code){
		$desc_array = array('DPJ'=>'对碰奖','JDJ'=>'见点奖');
		$date = date('Y-m-d H:i:s');
		$firstday_in_month = date('Y-m-01', strtotime($date));
		$lastday_in_month = date('Y-m-d', strtotime("$firstday_in_month +1 month -1 day"));
		
		$time_slot = array(
				'day'=>array(mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1),
				'week'=>array(strtotime("last Sunday")+8*60*60,strtotime ("Monday")+8*60*60),
				'month'=>array(strtotime($firstday_in_month),strtotime($lastday_in_month)),
		);
		
		$condition['trade_type'] = 4;
		$condition['reward_code'] = $reward_code;
		if(!empty($cap_data['day'])){
			$condition['uid'] = array('in',array_column($cap_data['day'], 'uid'));
			$condition['time_start'] =  array('between',$time_slot['day']);
			$data_day = $this->rewardByCap($condition,$cap_data['day'],$desc_array[$reward_code]);
		}
		if(!empty($cap_data['week'])){
			$condition['uid'] = array('in',array_column($cap_data['week'], 'uid'));
			$condition['time_start'] =  array('between',$time_slot['week']);
			$data_week = $this->rewardByCap($condition,$cap_data['week'],$desc_array[$reward_code]);
		}
		if(!empty($cap_data['year'])){
			$condition['uid'] = array('in',array_column($cap_data['year'], 'uid'));
			$condition['time_start'] =  array('between',$time_slot['year']);
			$data_year = $this->rewardByCap($condition,$cap_data['year'],$desc_array[$reward_code]);
		}
		if(!empty($cap_data['permanent'])){
			$condition['uid'] = array('in',array_column($cap_data['permanent'], 'uid'));
			$data_permanent = $this->rewardByCap($condition,$cap_data['permanent'],$desc_array[$reward_code]);
		}
		$data_no_cap = array();
		if(!empty($cap_data['no_cap'])){
			foreach ($cap_data['no_cap'] as $item){
				$data_no_cap[] = array(
					'uid'=>$item['uid'],
					'reward_fee'=>$item['reward_fee'],
					'desc'=>$desc_array[$reward_code],
				);
			}
		}
		if(!empty($data_day)){
			$data[] = $data_day;
		}
		if(!empty($data_week)){
			$data[] = $data_week;
		}
		if(!empty($data_year)){
			$data[] = $data_year;
		}
		if(!empty($data_permanent)){
			$data[] = $data_permanent;
		}
		if(!empty($data_no_cap)){
			$data[] = $data_no_cap;
		}
		$res = array();
		foreach ($data as $item){
			$res = array_merge($res,$item);
		}
		return $res;
	}
	/**
	 * 封顶后奖励
	 * @param unknown $condition
	 * @param unknown $cap_data
	 */
	private function rewardByCap($condition,$cap_data,$desc){
		$log_list =  M('MemberAccountLog',C('DB_PREFIX_C'))->where($condition)->field('uid,trade_fee')->select();
		foreach($log_list as $k=>$v){
			$result[$v['uid']]    +=   $v['trade_fee'];
		}
		$data = array();
		foreach ($cap_data as $item){
			if($item['cap_fee'] - $result[$item['uid']] - $item['reward_fee'] < 0){
				$reward_fee = $item['cap_fee'] - $result[$item['uid']];
				$desc .= '[封顶]';
			}else{
				$reward_fee = $item['reward_fee'];
			}
			$data[] = array(
					'uid'=>$item['uid'],
					'reward_fee'=>$reward_fee,
					'desc'=>$desc,
			);
		}
		return $data;
	}
}
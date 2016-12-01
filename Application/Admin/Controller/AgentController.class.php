<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class AgentController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		C('DB_PREFIX',C('DB_PREFIX_C'));
	}
	/**
	 * 查询上级区域id
	 * @param unknown $region_id
	 */
	private function topRegionId($region_id){
		return  M('Region')->where('id = '.$region_id)->getField('pid');
	}
	/**
	 * 判断内部还是外部
	 */
	private function outorin($referrer_region,$apply_region){
		$similarity = 0;//相似度
		if($referrer_region['agent_province'] == $apply_region['agent_province']){
			$similarity +=1;
			if($referrer_region['agent_city'] == $apply_region['agent_city']){
				$similarity +=1;
				if($referrer_region['agent_district'] == $apply_region['agent_district']){
					$similarity +=1;
				}
			}
		}
		return $similarity;
	} 
	/**
	 * 整理待奖励的人
	 * @param unknown $zhijiebi
	 */
	private function arrange($zhijiebi){
		$Member = M('Member');
		$data = array();
		foreach ($zhijiebi as $item){
			if(!empty($item['agent_city'])){
				$where['agent_city'] = $item['agent_city'];
			}
			if(!empty($item['agent_province'])){
				$where['agent_province'] = $item['agent_province'];
			}
			if(!empty($where)){
				$data[]=array(
						'bi'=>$item['bi'],
						'uid'=>$Member->where($where)->getField('uid',true),
				);
			}
		}
		return $data;
		
	}
	/**
	 * 计算奖金
	 * @param array $data
	 * @param float $join_fee
	 */
	private function reckonReward($data,$join_fee){
		$res = array();
		foreach ($data as $item){
			if(is_array($item['uid'])){
				foreach ($item['uid'] as $uid){
					$res[]=array(
							'uid'=>$uid,
							'reward'=>$join_fee * $item['bi']/100,
							'desc'=>'加盟费提成'
					);
				}
			}else{
				$res[]=array(
						'uid'=>$item['uid'],
						'reward'=>$join_fee * $item['bi']/100,
						'desc'=>'加盟费提成'
				);
			}
		}
		return $res;
	}
	/**
	 * 奖励
	 * @param unknown $apply_no
	 */
	public function reward($apply_info){
		if(!empty($apply_info['referrer_id']) && $apply_info['join_fee']>0){
			$rc = $this->ruleConfig(6);
			$zhijie_nei_bi = 0;//直接并且是省/市/县 内部 推荐提成比例
			$Member = M('Member');
			$referrer_info = $Member->where('uid = '.$apply_info['referrer_id'])->field('uid,agent_level,agent_province,agent_city,agent_district')->find();//查询直接推荐人的代理级别
			$referrer_region = array($referrer_info['agent_province'],$referrer_info['agent_city'],$referrer_info['agent_district']);
			$apply_region = array($apply_info['agent_province'],$apply_info['agent_city'],$apply_info['agent_district']);
			$similarity = $this->outorin($referrer_region, $apply_region);//相似度
			switch ($referrer_info['agent_level']){
				case 1:
					//县代理
					//判断县内还是县外
					if($similarity == 3){
						$zhijie_nei_bi = $rc['1_V_N_J_I'];//县代里直接对接的企业/商家/代理商
					}else{
						$yewuyuanbi = $rc['1_V_N_J_O'];
					}
					$agent_city = $this->topRegionId($referrer_info['agent_district']);
					$agent_province = $this->topRegionId($agent_city);
					$zhijiebi[] = array('bi'=>$rc['2_V_1_J'],'agent_city'=>$agent_city);//本市内县代里直接对接的企业/商家/代理商奖励给市代理
					$zhijiebi[] = array('bi'=>$rc['3_V_1_J'],'agent_province'=>$agent_province);//本省内县代理直接对接的企业/商家/代理商奖励给省代理
					break;
				case 2:
					//市代理
					//判断市内还是市外
					if($similarity >= 2){
						$zhijie_nei_bi = $rc['2_V_N_J_I'];//市代里直接对接的企业/商家/代理商
					}else{
						$yewuyuanbi = $rc['2_V_N_J_O'];
					}
					$zhijiebi[] = array('bi'=>$rc['3_V_2_J'],'agent_city'=>$this->topRegionId($referrer_info['agent_city']));//本省内市代理直接对接的企业/商家/代理商奖励给省代理
					break;
				case 3:
					//省代理
					//判断省内还是县外
					if($similarity >= 1){
						$zhijie_nei_bi[] = $rc['3_V_N_J_I'];//省代里直接对接的企业/商家/代理商
					}else{
						$yewuyuanbi = $rc['3_V_N_J_O'];
					}
					break;
			}
			
			$data = $this->arrange($zhijiebi);
			if(!empty($zhijie_nei_bi)){
				$data[] = array('bi'=>$zhijie_nei_bi,'uid'=>$referrer_info['uid']);
			}else{
				$data[] = array('bi'=>$yewuyuanbi,'uid'=>$referrer_info['uid']);
			}
			$res = $this->reckonReward($data, $apply_info['join_fee']);//整理奖金
			$uid_array = array_column($res, 'uid');
			$trade_fee_array = array_column($res, 'reward');
			$desc = array_column($res, 'desc');
			AccountController::changeALL($uid_array, $trade_fee_array, 'YJT', 4, $desc, 'JMF');
		}
		
	}
	/**
	 * 审核
	 */
	public function examine(){
		$data = array(
				'join_fee'=>I('post.join_fee'),
				'status'=>I('post.status'),
				'examine_time'=>time(),
		);
		$apply_no = I('post.apply_no');
		$ApplyAgent = M('ApplyAgent');
		if($ApplyAgent->where('apply_no = "'.$apply_no.'"')->save($data)){
			$apply_info = $ApplyAgent->where('apply_no = "'.$apply_no.'"')->find();
			if($data['status'] == 1){
				$agent_data = array(
						'agent_level'=>$apply_info['apply_level'],
						'agent_province'=>$apply_info['agent_province'],
						'agent_city'=>$apply_info['agent_city'],
						'agent_district'=>$apply_info['agent_district'],
				);
				M('Member')->where('uid = '.$apply_info['uid'])->save($agent_data);
				$this->reward($apply_info);
			}
			$this->success('审核成功');
		}else{
			$this->error('审核失败');
		}
	}
	
}
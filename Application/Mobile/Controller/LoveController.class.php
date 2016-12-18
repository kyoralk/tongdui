<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;

class LoveController extends CommonController{
	/**
	 * 捐款
	 */
	public function grant(){
		$trade_code = array('','GWQ','YJT');
		$data = array(
				'out_trade_no'=>serialNumber(),
				'uid'=>$this->member_info['uid'],
				'fee'=>I('post.fee'),
				'type'=>I('post.type'),
				'grant_time'=>time(),
		);
		$M = M();
		$M->startTrans();
		try {
			M('Love')->add($data);
			AccountController::change($data['uid'], $data['fee'], $trade_code[$data['type']], 6,true,'','',false);
		} catch (Exception $e) {
			$M->rollback();
			jsonReturn('','00000');
		}
		$M->commit();
		jsonReturn();
	}
	/**
	 * 捐物
	 */
	public function grantgoods($condition){
		$condition['is_love'] = 1;
		$order_sn = M('OrderInfo',C('DB_PREFIX_MALL'))->where($condition)->getField('order_sn',true);
		if(!empty($order_sn)){
			$data = array(
					'out_trade_no'=>serialNumber(),
					'uid'=>$this->member_info['uid'],
					'fee'=>I('post.fee'),
					'type'=>3,
					'order_sn' =>implode(',', $order_sn),
					'grant_time'=>time(),
			);
			M('Love')->add($data);
		}
	}
	
	public function log(){
		$type = I('get.type',0);
		$condition['uid'] = $this->member_info['uid'];
		if($type){
			$condition['type'] = $type;
		}
		$Love = M('Love');
		$data = appPage($Love, $condition, I('get.num'), I('get.p'),'','grant_time desc');	
		$data['goods'] = 0;
		if(I('get.sum')){
			$condition['type'] = 1;
			$data['gwq'] = $Love->where($condition)->sum('fee');
			$condition['type'] = 2;
			$data['yjt'] = $Love->where($condition)->sum('fee');
			$condition['type'] = 3;
			$order_sn_list = $Love->where($condition)->getField('order_sn',true);
			if(!empty($order_sn_list)){
				foreach ($order_sn_list as $order_sn){
					$order_sn_str = $order_sn.',';
				}
				$order_sn_array = array_filter(explode(',',$order_sn_str));
				if(count($order_sn_array)>1){
					$where = 'WHERE order_sn = IN ('.implode(',', $order_sn_array).')';
				}else{
					$where = 'WHERE order_sn = "'.$order_sn_array[0].'"';
				}
				$res = $Love->query('SELECT SUM(yjt) as yjt,SUM(gwq) as gwq  ,SUM(total) as total FROM '.C('DB_PREFIX_MALL').'order_info '.$where);
				$data['goods']=$res['yjt']+$res['gwq']+$res['total'];
				
			}
			$data['all']=$data['yjt']+$data['gwq']+$data['goods'];
		}
		jsonReturn($data);
	}
 	
	// 获得捐献的购物卷总计
	public function totalgwq() {
		$model = new \Think\Model();
		if (I('post.current')) {
			$where = ' and uid ='.$this->member_info['uid'];
		}
		$res = $model->query('select sum(fee) as total from ms_mall_love where type = 1'.$where);

		jsonReturn(['total'=>$res[0]['total']]);
	}

	// 获得捐献的一卷通总计
	public function totalyqt() {
		$model = new \Think\Model();
		if (I('post.current')) {
			$where = ' and uid ='.$this->member_info['uid'];
		}
		$res = $model->query('select sum(fee) as total from ms_mall_love where type = 2'.$where);

		jsonReturn(['total'=>$res[0]['total']]);
	}
}
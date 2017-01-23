<?php
namespace Mobile\Controller;
use Mobile\Controller\CommonController;
use General\Util\Express;
use Think\Upload;

class OrderController extends CommonController{
	/**
	 * 检测订单是否可以删除
	 * @param string $order_sn
	 */
	private function checkDel($order_sn){
		$shipping_status = M('OrderInfo')->where('order_sn = "'.$order_sn.'"')->getField('shipping_status');
		if($shipping_status){
			jsonReturn('','01022');
		}
	}
	/**
	 * 处理产品信息
	 */
	private function handleGoods($goods_id,$prosum,$atv_id_str,$spec_id){
		$goods_info = $this->goods($goods_id);//查询商品信息
		if(empty($goods_info)){
			return false;
		}else{
			//处理商品价格
			$price = $this->promotePrice($goods_info);
			$sendGWJ = $this->sendGWJ($goods_info);

			//根据属性处理价格
			$add_price = $this->extendPrice($atv_id_str);
			$price += $add_price;
			//处理产品价格结束
			//组装订单数据
			$data = array(
					'uid' => $this->member_info['uid'],
					'order_ip' => get_client_ip(),
					'order_time' => time() ,
					'store_id'=>$goods_info['store_id'],
			);
			$data['order_goods'][]=array(
					'goods_id' => $goods_info['goods_id'],
					'goods_name' => $goods_info['goods_name'],
					'goods_img' => $goods_info['goods_img'],
					'prosum' => $prosum,
					'price' => $price,
					'market_price' => $goods_info['market_price'],
					'cost_price'=>$goods_info['cost_price'],
					'spec_id'=>$spec_id,
 					'send_gwj'=>$sendGWJ * $prosum,
			);
			$data['total'] = $price*$prosum;
			$data['settlement_total'] = $goods_info['cost_price']*$prosum;
			$data['settlement_already'] = 0;
			$data['settlement_no'] = $data['settlement_total'];
			$data['send_gwj'] = $sendGWJ * $prosum;


			//组装订单数据结束
			return $data;
		}
	}
	/**
	 * 立刻购买提交订单
	 */
	private function createFromBuyNow($goods_id,$prosum,$atv_id_str,$address,$pay_id,$spec_id,$yjt,$gwq,$total){
		$data = $this->handleGoods($goods_id,$prosum,$atv_id_str,$spec_id);
		if($data){
			$data['order_sn'] = time().randstr(4,true);
			$data['consignee'] = $address ['consignee'];
			$data['address'] = $address['address'];
			$data['mobile']  = $address['mobile'];
			$data['pay_id'] = $pay_id;
			$data['freight'] = 0;//暂时写0
			$data['is_love'] = $address['is_love'];
			$data['upgrade'] = I('post.upgrade',0);
            $data['yjt'] = I('post.upgrade',0);
			$PayTemporary['out_trade_no'] = serialNumber();
			$PayTemporary['order_sn'] = $data['order_sn'];
			$PayTemporary['yjt'] = $yjt;
			$PayTemporary['gwq'] = $gwq;
			$PayTemporary['total'] = $total;
			$PayTemporary['send_gwj'] = $data['send_gwj'];
			if(M('PayTemporary')->add($PayTemporary)){
				if(D('OrderInfo')->relation('order_goods')->add($data)){
					return $PayTemporary['out_trade_no'];
				}else{
					jsonReturn('','00000');
				}
			}
		}else{
			jsonReturn('','01018');
		}
	}
	/**
	 * 从购物车中提交订单
	 */
	private function createFromCart($cart_id,$address,$pay_id,$yjt,$gwq,$total){
		$Cart = new CartController();
		$cart_info = $Cart->groupByStore($cart_id);
		$i = 0;
		$order_ip = get_client_ip();
		$totalSendGwj = 0;
		foreach ($cart_info['store'] as $store){
			$data[$i] = array(
					'order_sn' => serialNumber(),
					'uid' => $this->member_info['uid'],
					'order_ip' => $order_ip,
					'order_time' => time() ,
					'store_id'=>$store['store_id'],
					'address' => $address['province'].'省'.$address['city'].'市'.$address['address'],
					'consignee' => $address ['consignee'],
					'mobile'=> $address['mobile'],
					'pay_id' => $pay_id,
					'freight' => 0,//暂时写0
			);
			$data[$i]['total'] = 0;
			foreach ($store['goods_list'] as $goods_info){
                $sendGWJ = $this->sendGWJ($goods_info);
				$data[$i]['total'] += $goods_info['price']*$goods_info['prosum'];
				$data[$i]['settlement_total'] += $goods_info['cost_price']*$goods_info['prosum'];
				$data[$i]['settlement_already'] = 0;
				$data[$i]['settlement_no'] = $data[$i]['settlement_total'];
				$data[$i]['send_gwj']+= $sendGWJ*$goods_info['prosum'];
				$totalSendGwj += $sendGWJ *$goods_info['prosum'];
				$order_goods[]=array(
						'order_sn'=> $data[$i]['order_sn'],
						'goods_id' => $goods_info['goods_id'],
						'goods_name' => $goods_info['goods_name'],
						'goods_img' => $goods_info['goods_img'],
						'prosum' => $goods_info['prosum'],
						'price' => $goods_info['price'],
						'cost_price'=>$goods_info['cost_price'],
						'market_price' => $goods_info['market_price'],
						'spec_id' => $goods_info['spec_id'],
						'send_gwj' => $goods_info['send_gwj'],
				);
			}
			$i++;
		}
		$PayTemporary['out_trade_no'] = serialNumber();
		$PayTemporary['order_sn'] = implode(',', array_column($data, 'order_sn'));
		$PayTemporary['yjt'] = $yjt;
		$PayTemporary['gwq'] = $gwq;
		$PayTemporary['total'] = $total;
		$PayTemporary['send_gwj'] = $totalSendGwj;
		if(M('PayTemporary')->add($PayTemporary)){
			$M = M();
			$M->startTrans();
			try {
				M('OrderInfo')->addAll($data);
				M('OrderGoods')->addAll($order_goods);
			} catch (Exception $e) {
				$M->rollback();
				jsonReturn(array(),'00000');
			}
			$M->commit();
			$Cart->delc($cart_id);//删除购物车中的产品
			return $PayTemporary['out_trade_no'];
			
		}
	}
	/**
	 * 创建订单
	 */
	public function create(){
	    file_put_contents('log', print_r($_POST, true), FILE_APPEND);
		$goods_id = I('post.goods_id');
		$prosum = I('post.prosum',1);
		$atv_id_str = I('post.atv_id_str');
		$address_id = I('post.address_id');
		$pay_id = I('post.pay_id');
		$cart_id = I('post.cart_id',false);
		$yjt = I('post.yjt',0);
		$gwq = I('post.gwq',0);
		$total = I('post.total',0); 
		if($address_id!=0){
			$address = R('Address/getAddress',array(address_id));//获取收获地址
			$address['address'] = $address['province'].'省'.$address['city'].'市'.$address['address'];
			$address['is_love'] = 0;
		}else{
			$address = $this->grantAddress();
			$address['is_love'] = 1;
		}
		if($cart_id){
			$out_trade_no = $this->createFromCart($cart_id,$address,$pay_id,$yjt,$gwq,$total);
		}else{
			$out_trade_no = $this->createFromBuyNow($goods_id,$prosum,$atv_id_str,$address,$pay_id,$spec_id,$yjt,$gwq,$total);
		}
		$response = $this->notifyURL();
		if(I('post.upgrade')==1){
			$response['out_trade_no'] = 'UPG_'.$out_trade_no;
		}else{
			$response['out_trade_no'] = 'BUY_'.$out_trade_no;
		}
		
		jsonReturn($response);
	}
	/**
	 * 从订单中支付
	 */
	public function createByOrder(){
		$PayTemporary['out_trade_no'] = serialNumber();
		$PayTemporary['order_sn'] = I('post.order_sn');
		$PayTemporary['yjt'] = I('post.yjt');
		$PayTemporary['gwq'] = I('post.gwq');
		$PayTemporary['total'] = I('post.total');
		if(M('PayTemporary')->add($PayTemporary)){
			$response = $this->notifyURL();
			if(I('post.upgrade')==1){
				$response['out_trade_no'] = 'UPG_'.$PayTemporary['out_trade_no'];
			}else{
				$response['out_trade_no'] = 'BUY_'.$PayTemporary['out_trade_no'];
			}
			jsonReturn($response);
		}else{
			jsonReturn(array(),'00000');
		}
	}
	/**
	 * 订单列表
	 */
	public function olist(){
		switch (I('get.type')){
			case 1:
				$condition ['pay_status'] = 0;
				$condition ['shipping_status'] = 0;
				$condition ['receipt_status'] = 0;
				$condition ['evaluate_status'] = 0;
				break;
			case 2:
				$condition ['pay_status'] = 1;
				$condition ['shipping_status'] = 0;
				$condition ['receipt_status'] = 0;
				$condition ['evaluate_status'] = 0;
				break;
			case 3:
				$condition ['pay_status'] = 1;
				$condition ['shipping_status'] = 1;
				$condition ['evaluate_status'] = 0;
				$condition ['receipt_status'] = 0;
				break;
			case 4:
				$condition ['pay_status'] = 1;
				$condition ['shipping_status'] = 1;
				$condition ['receipt_status'] = 1;
				$condition ['evaluate_status'] = 0;
				break;
			case 5:
				$condition ['pay_status'] = 1;
				$condition ['shipping_status'] = 1;
				$condition ['receipt_status'] = 1;
				$condition ['evaluate_status'] = 1;
				break;
		}
		$condition ['uid'] = $this->member_info['uid'];
		$data = appPage(D('OrderInfo'), $condition, I('get.num'), I('get.p'),'relation','order_time desc');

		if ($data['list']) {
		    foreach ($data['list'] as $kk=>$l) {

                foreach ($l['order_goods'] as $k=>$og) {
                    if ($og['spec_id']) {
                        $spec = M("GoodsSpec")->where("spec_id =".$og['spec_id'])->find();
                        if ($spec)
                            $data['list'][$kk]['order_goods'][$k]['spec_name'] = $spec['spec_name'];
                        else
                            $data['list'][$kk]['order_goods'][$k]['spec_name'] =  '';
                    } else {
                        $data['list'][$kk]['order_goods'][$k]['spec_name'] =  '';
                    }
                    $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'].' and is_cover = 1')->find();
                    if (!$goods) {
                        $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'])->find();
                    }
                    $link = "http://".$_SERVER['HTTP_HOST'].'/Uploads/'.$goods['save_path'].$goods['save_name'];
                    $data['list'][$kk]['order_goods'][$k]['goods_img'] =  $data['list'][$kk]['order_goods'][$k]['goods_img']?"http://".$_SERVER['HTTP_HOST'].'/Uploads/'.$data['list'][$kk]['order_goods'][$k]['goods_img']:$link;

                }
            }
        }


		jsonReturn($data);
	}
	/**
	 * 订单详情
	 */
	public function info(){
		$data = D('OrderInfo')->relation(true)->where('order_sn = "'.I('get.order_sn').'"')->find();

        if ($data) {

            foreach ($data['order_goods'] as $k=>$og) {
                if ($og['spec_id']) {
                    $spec = M("GoodsSpec")->where("spec_id =".$og['spec_id'])->find();
                    if ($spec)
                        $data['order_goods'][$k]['spec_name'] = $spec['spec_name'];
                    else
                        $data['order_goods'][$k]['spec_name'] =  '';
                } else {
                    $data['order_goods'][$k]['spec_name'] =  '';

                }
                $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'].' and is_cover = 1')->find();
                if (!$goods) {
                    $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'])->find();
                }
                $link = "http://".$_SERVER['HTTP_HOST'].'/Uploads/'.$goods['save_path'].$goods['save_name'];

                $data['order_goods'][$k]['goods_img'] =  $data['order_goods'][$k]['goods_img']?"http://".$_SERVER['HTTP_HOST'].'/Uploads/'.$data['order_goods'][$k]['goods_img']:$link;
            }

        }
		jsonReturn($data);
	}
	/**
	 * 删除订单
	 */
	public function delete(){
		$this->checkDel(I('post.order_sn'));
		if(D('OrderInfo')->relation('order_goods')->delete(I('post.order_sn'))){
			jsonReturn();
		}else{
			jsonReturn('','00000');
		}
	}
	/**
	 * 确认收货
	 */
	public function confirm(){
        $amount = 0;
		if(M('OrderInfo')->where('order_sn = "'.I('post.order_sn').'"')->setField('receipt_status',1) !== false){
            $orderGoods = M('OrderGoods')->where('order_sn = "'.I('post.order_sn').'"')->select();
            if ($orderGoods) {
                foreach ($orderGoods as $og) {
                    $goods = M("Goods")->where('goods_id ='.$og['goods_id'])->find();
                    if ($goods) {
                        // 一券通商品收货进行九代结算
                        if ($goods['consumption_type'] == 2) {
                            $amount+= $og['price'] * $og['prosum'];
                        }
                    }
                }


                if ($amount > 0) {
                    R('Reward/jdjs',array($amount,'XFYJT'));
                }

            }

			jsonReturn();
		}else{
			jsonReturn('','00000');
		}
	}
	/**
	 * 物流追踪
	 */
	public function track(){
		$shipping = M('Shipping')->where('shipping_id = '.I('get.shipping_id'))->field('shipping_code,shipping_name')->find();
		$Express = new Express();
		$result = $Express->getorder($shipping['shipping_code'], I('get.shipping_sn'));
		$count = count($result['data']);
		for($i = 0;$i<$count;$i++){
			$result['data'][$i]['nyr'] = explode(' ', $result['data'][$i]['time'])[0];
			$result['data'][$i]['sfm'] = explode(' ', $result['data'][$i]['time'])[1];
		}
		$this->assign('shipping_name',$shipping['shipping_name']);
		$this->assign('shipping_sn',I('get.shipping_sn'));
		$this->assign('result',$result);
		$this->display();
	}

	public function getGY(){
		$condition['goods_id'] = array('in',explode(',', I('get.goods_id')));
// 		M('Gooods')->where($condition)->getf
	}


	/**
     * 用户申请退换货
     */
	public function refund() {
        // 查找客户订单
        $uid = $this->member_info['uid'];
        $orderInfo = M("OrderInfo")->where("uid = $uid and order_sn =".I('post.order_sn'))->find();
        if ($orderInfo) {
            $refund = M('Refund')->where('order_sn='.I('post.order_sn').' and goods_id='.I('post.goods_id'))->find();
            if ($refund) {
                jsonReturn('','已存在该退货申请');
            }
            $refund = M('Refund');
            $data['order_sn'] = I('post.order_sn');
            $data['goods_id'] = I('post.goods_id');
            $orderGoods = M('OrderGoods')->where("order_sn = ".I('post.order_sn')." and goods_id = ".I('post.goods_id'))->find();
            if ($orderGoods) {
                $amount = $this->getRefundMax($orderInfo, $orderGoods);
                $data['amount'] = $amount['max'];
                $data['cash'] = $amount['max_cash'];
                $data['yqt'] = $amount['max_yjt'];
                $data['gwq'] = $amount['max_gwq'];
                $data['reason'] = I('post.reason');
                $data['store_id'] = $orderInfo['store_id'];
                $data['create_time'] = time();
                $data['type'] = I('post.type')=="换货"?"换货":"退货";
                $data['user_id'] = $this->member_info['uid'];
                if ($refund->create($data)) {
                    $upload = new Upload();// 实例化上传类
                    $upload->maxSize   =     3145728 ;// 设置附件上传大小
                    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
                    $upload->savePath  =      'Refund/'; // 设置附件上传（子）目录
                    // 上传文件
                    $info   =   $upload->upload();
                    if(!$info) {// 上传错误提示错误信息
                        var_dump($upload->getError());
                        exit;
                    }else{// 上传成功 获取上传文件信息
                        foreach($info as $file){
                            $data['refund_img'] =  $file['savepath'].$file['savename'];
                        }
                    }
                    $refund->add($data);

                    // order_goods表进行修改
                    $og = M('OrderGoods');
                    $og->is_refund = (I('post.type')=="换货")?2:1;
                    $og->where("order_sn = ".I('post.order_sn')." and goods_id = ".I('post.goods_id'))->save();
                    // order表进行操作
                    $or = M('OrderInfo');
                    $or->refund_status = 1;
                    $or->where("uid = $uid and order_sn =".I('post.order_sn'))->save();
                    jsonReturn();
                } else {
                    jsonReturn('', '00001');
                }
            } else {
                jsonReturn('', '00000');
            }
        } else {
            jsonReturn('', '00000');
        }
    }

    /**
     * 获得订单内某退货商品最大允许退款金额
     */
    public function maxrefund() {

        $uid = $this->member_info['uid'];
        $orderInfo = M("OrderInfo")->where("uid = $uid and order_sn =".I('get.order_sn'))->find();
        if ($orderInfo) {
            $orderGoods = M('OrderGoods')->where("order_sn = ".I('get.order_sn')." and goods_id = ".I('get.goods_id'))->find();
            if ($orderGoods) {
                $data = $this->getRefundMax($orderInfo, $orderGoods);
                jsonReturn($data);
            } else {
                jsonReturn('', '00000');
            }
         } else {
            jsonReturn('', '00000');
        }
    }


    protected function getRefundMax($orderInfo, $orderGoods) {
        $defaultMax = $orderGoods['price'] * $orderGoods['prosum'];
        $payTemp = M("PayTemporary")->where("order_sn = '".$orderInfo['order_sn']."'")->find();
        $goods = M("Goods")->where('goods_id ="'.$orderGoods['goods_id'].'"')->find();

//        $orderGoods = M("OrderGoods")->where("order_sn ='".$orderInfo['order_sn']."'")->select();
//        $gwqAmount = 0;
//        $otherAmount = 0;
//        if ($orderGoods) {
//            foreach ($orderGoods as $og) {
//                $goods = M("Goods")->where('goods_id ="'.$og['goods_id'].'"')->find();
//                if ($goods) {
//                    if ($goods['consumption_type'] == 3) {
//                        $gwqAmount+=$og['price'] * $og['prosum'];
//                    } else {
//                        $otherAmount+=$og['price'] * $og['prosum'];
//                    }
//                }
//            }
//        }
        
        $total = $orderInfo['total'];
        $cash = $payTemp['cash'];
        $orderInfo['yjt'] = $payTemp['yjt'];
        $orderInfo['gwq'] = $payTemp['gwq'];

        $data['max_cash'] = $defaultMax * $cash/ $total;
        $store = M('Store')->where('store_id ='.$orderInfo['store_id'])->find();
        if ($goods['consumption_type'] == 3) {
            $orderInfo['gwq']+=$orderInfo['yjt'];
            $data['max_yjt'] = 0;
        } else {
            $data['max_yjt'] = $defaultMax * $orderInfo['yjt'] / $orderInfo['total'] ;
        }
//        if ($store && $store['gwq_max']) {
//            $data['max_gwq'] = $defaultMax * $orderInfo['gwq'] / $total * $store['gwq_max'] / 100;
//        } else {
        $data['max_gwq'] = $defaultMax * $orderInfo['gwq'] / $total;
//        }


        $data = [
            'max_gwq' => 0,
            'max_yjt' => 0,
        ];

        if ($goods['consumption_type' ] == 3) {
            $data['max_gwq'] = $defaultMax - $this->sendGWJ($goods) * $orderGoods['prosum'];
        } else {
            $data['max_yjt'] = $defaultMax - $this->sendGWJ($goods) * $orderGoods['prosum'];
        }

        $data['max'] = $data['max_yjt'] + $data['max_gwq'] + $data['max_cash'];
        $data['send_gwq'] = $this->sendGWJ($goods)* $orderGoods['prosum'];
        return $data;
    }

    /**
     * 提交对应单号和快递公司
     */
    public function refundShip() {
        $refund_id = I('get.refund_id');
        if ($refund_id) {
            $refund = M('Refund');
            $refund->ship_time = time();
            $refund->ship_company = I('get.ship_company');
            $refund->ship_id = I('get.ship_id');
            $refund->status = 2;
            if ($refund->where('refund_id ='.$refund_id)->save()) {
                jsonReturn();
            } else {
                jsonReturn('', '00000');
            }
        } else {
            jsonReturn('', '00000');
        }
    }

    /**
     * 获得当前用户的退换货列表
     */
    public function refundList() {
        $where = '';
        $type = I('get.type');
        if ($type)
            $where.="type ='".$type."'";
        $uid = $this->member_info['uid'];
        if ($uid) {
            $where.=' and user_id ='.$uid;
        }

        $refund = D('Refund');
        $list = $refund->relation(true)->where($where)->select();
        jsonReturn($list);

    }

}
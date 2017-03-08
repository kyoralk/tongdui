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
    /**liaopeng 2017-01-26
     * 单规格效验库存（请在增加多规格后修改）
     */
    function hand_stock($goods_id,$prosum)
    {
        $stock=M("goods")->where(["goods_id"=>$goods_id])->find();
        if($prosum>$stock["stock"])
        {
            jsonReturn(null,"商品$stock[goods_name]库存不足",'02018');
        }
    }

	/**
	 * 立刻购买提交订单
	 */
	private function createFromBuyNow($goods_id,$prosum,$atv_id_str,$address,$pay_id,$spec_id,$yjt,$gwq,$total){
        //效验库存
	    $this->hand_stock($goods_id,$prosum);
        M("goods")->where(["goods_id"=>$goods_id])->setDec('stock',$prosum);

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
			//效验库存须在赠送购物劵之前进行效验
            foreach ($store['goods_list'] as $v) {
                $this->hand_stock($v["goods_id"],$v["prosum"]);
            }
            //所有库存效验完成后减去库存
            foreach ($store['goods_list'] as $v) {
                M("goods")->where(["goods_id"=>$v["goods_id"]])->setDec('stock',$v["prosum"]);
            }

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

		        // 加入二維碼，配送員可以掃，掃了之後就會進行狀態修改。
                $json['order_sn'] = $l['order_sn'];
                $json['api'] = 'order_custom_get';
                $data['list'][$kk]['barcode'] = 'http://qr.liantu.com/api.php?text='.json_encode($json);

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

    // 配送主管的列表
    public function deliverboss_list(){
        $condition ['deliver_status'] = I('get.type');
        $condition ['deliverboss_id'] = $this->member_info['uid'];
        $data = appPage(D('OrderInfo'), $condition, I('get.num'), I('get.p'),'relation','order_time desc');

        if ($data['list']) {
            foreach ($data['list'] as $kk=>$l) {
                // 新增二维码 3-5
                $json['order_sn'] = $l['order_sn'];
                $json['api'] = 'order_bossthere_get';
                $data['list'][$kk]['barcode'] = 'http://qr.liantu.com/api.php?text='.json_encode($json);

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

    // 配送员的列表
    public function deliver_list(){
        $condition = 'deliver_status ='.I('get.type').' and (deliver_id = '.$this->member_info['uid'].' or deliverboss_id = '.$this->member_info['uid'].')';
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
    public function deliver_info(){
        $data = D('OrderInfo')->relation(true)->where('order_sn = "'.I('get.order_sn').'"')->find();

        if ($data) {

            if ($this->member_info['deliver_level'] == 2) {
                $json['order_sn'] = $data['order_sn'];
                $json['api'] = 'order_bossthere_get';
                $data['barcode'] = 'http://qr.liantu.com/api.php?text='.json_encode($json);
            }

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
	 * 订单详情
	 */
	public function info(){
		$data = D('OrderInfo')->relation(true)->where('order_sn = "'.I('get.order_sn').'"')->find();

        if ($data) {

            $json['order_sn'] = $data['order_sn'];
            $json['api'] = 'order_custom_get';
            $data['barcode'] = 'http://qr.liantu.com/api.php?text='.json_encode($json);

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
		if(M('OrderInfo')->where('order_sn = "'.I('post.order_sn').'" and receipt_status = 0 and shipping_status = 1 and pay_status = 1')->setField('receipt_status',1) !== false){
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

                R('Reward/agent',array($orderGoods));//代理商奖励
            }

            OrderController::sureDeliver(I('post.order_sn'));

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
        $type = I('get.type')?"退货":"换货";
        if ($type)
            $where.="type ='".$type."'";
        $uid = $this->member_info['uid'];
        if ($uid) {
            $where.=' and user_id ='.$uid;
        }

        $refund = D('Refund');
        $list = $refund->relation(true)->where($where)->order("refund_id desc")->select();
        //2017-02-07 liaopeng  修改图片展示
        if($list){
            $goodsids="";
            foreach($list as $key=>$v)
            {
                $goodsids.=$v["goods_id"].",";
            }
            $goodsids=rtrim($goodsids,",");
            $imglist=M("goods_img")->where(["goods_id"=>["in",$goodsids]])->group("goods_id")->select();
            foreach ($list as $k=>$val)
            {
                foreach ($imglist as $imgval)
                {
                    if($val["goods_id"]==$imgval["goods_id"])
                    {
                        $list[$k]["order_goods"]["goods_img"]=$imgval["save_path"].$imgval["save_name"];
                    }
                }
            }
        }
       //修改结束


        jsonReturn($list);

    }

    // 配送员到配送主管处取到货物扫码
    public function deliverGet() {
        $order_sn = I('post.order_sn');
        $uid = $this->member_info['uid'];

        // 检测是不是配送员
        if ($this->member_info['deliver_level']) {
            $deliver = M('Deliver')->where(['order_sn'=>$order_sn])->find();
            if ($deliver) {
                if ($deliver['status'] > 2) {
                    jsonReturn('该单已经被取货', '00000');
                } else {
                    $deliver['uid'] = $uid;
                    $deliver['get_time'] = date('Y-m-d H:i:s', time());
                    $deliver['status'] = 3; // 配送员已收货
                    if (M("Deliver")->where(['order_sn'=>$order_sn])->save($deliver)) {
                        M("OrderInfo")->where(['order_sn'=>$order_sn])->save(['deliver_status'=>3, 'deliver_id'=>$uid]);
                        jsonReturn();
                    } else {
                        jsonReturn('', '00000');
                    }
                }
            } else {
                jsonReturn('不存在该配送单', '00000');
            }
        } else {
            jsonReturn('您还不是配送员', '00000');
        }
    }

    /**
     * 配送員到客戶那掃碼確認
     */
    public function deliverSend() {
        $order_sn = I('post.order_sn');
        $uid = $this->member_info['uid'];

        // 检测是不是配送员
        if ($this->member_info['deliver_level']) {
            $deliver = M('Deliver')->where(['order_sn'=>$order_sn])->find();
            if ($deliver && $deliver['status'] != 5) {

                    $deliver['deliver_id'] = $uid;
                    // 配送员如果没有去提货的话，只给自己分成
                    if ($deliver['status'] != 3) {
                        if ($deliver['deliverboss_id'] != $deliver['deliver_id']) {
                            $deliver['deliverboss_id'] = '';
                        }
                        $order = M('OrderInfo')->where(['order_sn'=>$order_sn])->find();
                        $order['settlement_total'] = $order['settlement_total'] + $deliver['deliverboss_fee'];
                        $order['settlement_no'] = $order['settlement_total'] + $deliver['deliverboss_fee'];
                        M('OrderInfo')->where(['order_sn'=>$order_sn])->save($order);
                    }
                    $deliver['status'] = 5;
                    $deliver['finish_time'] = date('Y-m-d H:i:s', time());
                    if (M("Deliver")->where(['order_sn'=>$order_sn])->save($deliver)) {
                        M("OrderInfo")->where(['order_sn'=>$order_sn])->save(['deliver_status'=>5]);
                        jsonReturn();
                    } else {
                        jsonReturn('', '00000');
                    }

            } else {
                jsonReturn('请勿重复扫码', '00000');
            }
        } else {
            jsonReturn('您还不是配送员', '00000');
        }
    }

    // 确认收货的时候进行的物流系统结算
    public static function sureDeliver($order_sn) {
        $deliver = M('Deliver')->where(['order_sn'=>$order_sn])->find();
        if ($deliver) {
            if ($deliver['status'] == 3 || $deliver['status'] == 5) {
                // 只发生在客户取货取货的时候
                $deliver['status'] = 4;
                // $deliver['finish_time'] = date('Y-m-d H:i:s', time());
                if (M("Deliver")->where(['order_sn'=>$order_sn])->save($deliver)) {
                    M("OrderInfo")->where(['order_sn'=>$order_sn])->save(['deliver_status'=>4]);

                    if ($deliver['deliver_id'] == '0')
                        $deliver['deliver_id'] = $deliver['deliveboss_id'];

                    if ($deliver['deliveboss_id'] == '0')
                        $deliver['deliveboss_id'] = $deliver['deliver_id'];

                    if ($deliver['deliverboss_fee'])
                        AccountController::change($deliver['deliverboss_id'], $deliver['deliverboss_fee'], 'YJT', 8, false, "订单配送：".$deliver['order_sn'].', 配送主管奖励');
                    if ($deliver['deliver_fee'])
                        AccountController::change($deliver['deliver_id'], $deliver['deliver_fee'], 'YJT', 8, false, "订单配送：".$deliver['order_sn'].', 配送员奖励');


                    // 生成提货单
                    $order = M("OrderInfo")->where(['order_sn'=>$order_sn])->find();
                    if ($order) {
                        $order['pick_sn'] = 'T'.time().randstr(4,true);
                        $store = M('Store')->where(['store_id'=>$order['store_id']])->find();
                        $order['address'] = $store['company_address'];
                        $deliverboss = M("Member")->where(['uid'=>$deliver['deliveboss_id']])->find();
                        $order['consignee'] = $deliverboss['real_name'];
                        $order['mobile'] = $deliverboss['mobile'];
                        $order['pick_uid'] = $deliverboss['uid'];
                        $order['pick_status'] = 1;
                        $order['pick_create_time'] = date('Y-m-d H:i:s', time());
                        M("PickInfo")->data($order)->add();
                    }
                }
            }
        }
    }

    // 配送员扫码提货
    public function pick() {
        $pick_sn = I('post.pick_sn');
        $pick = M("PickInfo")->where(['pick_sn'=>$pick_sn])->find();
        if ($pick) {
            $pick['pick_time'] =  date('Y-m-d H:i:s', time());
            $pick['pick_status'] = 2;
            if (M("PickInfo")->where(['pick_sn'=>$pick_sn])->save($pick)) {
                jsonReturn();
            } else {
                jsonReturn('', '00000');
            }
        } else {
            jsonReturn('没有该配送单', '00000');
        }
    }

    /**
     * 前台调用的提货单列表
     */
    public function pick_list() {
        $condition ['pick_status'] = I('get.type');
        $condition ['pick_uid'] = $this->member_info['uid'];
        $data = appPage(D('PickInfo'), $condition, I('get.num'), I('get.p'),'relation','order_time desc');

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

}
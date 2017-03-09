<?php
namespace Admin\Controller;
use Admin\Controller\MallController;

class OrderController extends MallController{
	/**
	 * 订单列表
	 */
	public function olist(){
		$content_header = '订单列表';
		switch (I('get.type')){
			case 1:
				$condition ['pay_status'] = 0;
				$condition ['shipping_status'] = 0;
				$condition ['receipt_status'] = 0;
				$condition ['evaluate_status'] = 0;
				$content_header = '待付款';
				break;
			case 2:
				$condition ['pay_status'] = 1;
				$condition ['shipping_status'] = 0;
				$condition ['receipt_status'] = 0;
				$condition ['evaluate_status'] = 0;
				$content_header = '待发货';
				break;
			case 3:
				$condition ['pay_status'] = 1;
				$condition ['shipping_status'] = 1;
				$condition ['evaluate_status'] = 0;
				$condition ['receipt_status'] = 0;
				$content_header = '待收货';
				break;
			case 4:
				$condition ['pay_status'] = 1;
				$condition ['shipping_status'] = 1;
				$condition ['receipt_status'] = 1;
				$condition ['evaluate_status'] = 0;
				$content_header = '待评价';
				break;
			case 5:
				$condition ['pay_status'] = 1;
				$condition ['shipping_status'] = 1;
				$condition ['receipt_status'] = 1;
				$condition ['evaluate_status'] = 1;
				$content_header = '已完成';
				break;
		}
		$condition ['store_id'] = 1;
		$data = page(D('OrderInfo'), $condition,16,'relation','order_time desc');
		$this->assign('order_list',$data['list']);
		$this->assign('page',$data['page']);
		$this->assign('content_header',$content_header);
		$this->display();
	}
	/**
	 * 订单详情
	 */
	public function info(){
		$shipping_list = M('Shipping')->where('enabled = 1')->field('shipping_name,shipping_id')->select();
		// 配送员和主管都可以直接被任命
		$deliverboss_list = M("ApplyDeliver", C('DB_PREFIX_C')  )->where('status = 1')->select();
        $this->assign('deliverboss_list',$deliverboss_list);
		$this->assign('shipping_list',$shipping_list);
		$info = D('OrderInfo')->relation(true)->where('order_sn = "'.I('get.order_sn').'"')->find();
		$this->assign('oi',$info);
		$this->display();
	}
    public function sellerlist()
    {
        $content_header = '订单列表';
        switch (I('get.type')){
            case 1:
                $condition ['pay_status'] = 0;
                $condition ['shipping_status'] = 0;
                $condition ['receipt_status'] = 0;
                $condition ['evaluate_status'] = 0;
                $content_header = '待付款';
                break;
            case 2:
                $condition ['pay_status'] = 1;
                $condition ['shipping_status'] = 0;
                $condition ['receipt_status'] = 0;
                $condition ['evaluate_status'] = 0;
                $content_header = '待发货';
                break;
            case 3:
                $condition ['pay_status'] = 1;
                $condition ['shipping_status'] = 1;
                $condition ['evaluate_status'] = 0;
                $condition ['receipt_status'] = 0;
                $content_header = '待收货';
                break;
            case 4:
                $condition ['pay_status'] = 1;
                $condition ['shipping_status'] = 1;
                $condition ['receipt_status'] = 1;
                $condition ['evaluate_status'] = 0;
                $content_header = '待评价';
                break;
            case 5:
                $condition ['pay_status'] = 1;
                $condition ['shipping_status'] = 1;
                $condition ['receipt_status'] = 1;
                $condition ['evaluate_status'] = 1;
                $content_header = '已完成';
                break;
        }
        if(I('get.order_sn'))
        {
            $condition ['order_sn'] = I('get.order_sn');
            $this->assign("order_sn",I('get.order_sn'));
        }
        I('get.type')?$this->assign("select",I('get.type')):$this->assign("type",0);
        $data = page(D('OrderInfo'), $condition,16,'relation','order_time desc');

        $userids='';
        $storeids="";
        if ($data['list']) {
            foreach ($data['list'] as $kk=>$l) {
                $userids.=$l["uid"].",";
                $storeids=$l["store_id"].",";
                foreach ($l['order_goods'] as $k=>$og) {
                    $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'].' and is_cover = 1')->find();
                    if (!$goods) {
                        $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'])->find();
                    }
                    $link = '/Uploads/'.$goods['save_path'].$goods['save_name'];
                    $data['list'][$kk]['order_goods'][$k]['goods_img'] =  $data['list'][$kk]['order_goods'][$k]['goods_img']?'/Uploads/'.$data['list'][$kk]['order_goods'][$k]['goods_img']:$link;

                }
            }
            $userids=rtrim($userids,",");
            $storeids=rtrim($storeids,",");
            $userlist=M("member","ms_common_")->where(['uid'=>["in",$userids]])->field("uid,username")->select();
            $storelist=M("store","ms_mall_")->where(['store_id'=>["in",$storeids]])->field("store_id,store_name")->select();

            foreach ($data['list'] as $ok=>$ov) {
                foreach ($userlist as $uv)
                {
                    if($ov["uid"]==$uv["uid"])
                    {
                        $data['list'][$ok]["username"]=$uv["username"];
                    }

                }
                foreach ($storelist as $sv)
                {
                    if($ov["store_id"]==$sv["store_id"])
                    {
                        $data['list'][$ok]["store_name"]=$sv["store_name"];
                    }

                }
            }

        }

        $this->assign('order_list',$data['list']);
        $this->assign('page',$data['page']);
        $this->assign('content_header',$content_header);
        $this->display();

    }
    /**
	 * 删除订单
	 */
	public function delete(){
		if(D('OrderInfo')->relation('order_goods')->delete(I('get.order_sn'))){
			$this->success('取消成功');
		}else{
			$this->error('取消失败');
		}
	}


    /**
	 * 发货
	 */
	public function deliver(){
		$data = array(
				'order_sn'=>I('post.order_sn'),
				'shipping_status'=>1,
				'shipping_id'=>I('post.shipping_id'),
				'shipping_sn'=>I('post.shipping_sn'),
            'deliverboss_id' =>I('post.deliverboss_id'),
		);

        if ($data['deliverboss_id']) {
            // 创建配送单
            $deliver = M('Deliver')->where(['order_sn' => $data['order_sn']])->find();
            if ($deliver) {
                if ($deliver['status'] > 2) {
                    $this->error('配送员已取货，无法更改');
                }
            }
        }

        $data['deliver_status'] = 2;
		if(M('OrderInfo')->save($data)){

		    if ($data['deliverboss_id']) {
                // 创建配送单
                if ($deliver) {
                    $deliver['deliverboss_id'] = $data['deliverboss_id'];
                    $deliver['shipping_time'] =  date('Y-m-d H:i:s', time());
                    $deliver['bossget_time'] =  date('Y-m-d H:i:s', time());
                    $deliver['status'] = 2; // 默认boss已经接受
                    M("Deliver")->where(['order_sn' => $data['order_sn']])->save($deliver);
                } else {
                    $_data['deliver_sn'] = "S".time().randstr(4,true);
                    $_data['order_sn'] = $data['order_sn'];
                    $_data['deliverboss_id'] = $data['deliverboss_id'];
                    $_data['shipping_time'] =  date('Y-m-d H:i:s', time());
                    $_data['bossget_time'] =  date('Y-m-d H:i:s', time());
                    $_data['status'] = 2; // 默认boss已经接受
                    // 保存状态后开始结算
                    $ordergoods = M('OrderGoods')->where(['order_sn'=>$data['order_sn']])->select();
                    $baseFee = 5; // 待设置
                    $addFee = 1; // 待设置
                    $totalWeight = 0;
                    if ($ordergoods) {
                        foreach ($ordergoods as $og) {
                            $goods = M("Goods")->where(['goods_id'=>$og['goods_id']])->find();
                            if ($goods) {
                                $totalWeight+=$goods['goods_weight'] * $og['prosum'];
                            }
                        }
                    }

                    if ($totalWeight > 1) {
                        $_data['deliverboss_fee'] = $baseFee + ($totalWeight - 1) * $addFee;
                        $_data['deliver_fee'] = $baseFee + ($totalWeight - 1) * $addFee;
                    } else {
                        $_data['deliverboss_fee'] = $baseFee;
                        $_data['deliver_fee'] = $baseFee;
                    }
                    // 主管可以拿两次费用，而配送员自己只能拿一次
                    $person = M('Member', "ms_common_")->where(['UserId'=>$data['deliverboss_id']])->find();
                    if ($person['deliver_level'] == 1) {
                        $_data['deliverboss_fee'] = 0;
                    }
                    // 和谐物流的费用
                    $_data['system_fee'] = 1;

                    if (M('Deliver')->data($_data)->add()) {
                        // 修改结算的金额
                        $data['settlement_total'] = $data['settlement_total']
                            - $_data['deliverboss_fee']
                            - $_data['deliver_fee']
                            - $_data['system_fee'];
                        $data['settlement_total'] =  $data['settlement_total'] > 0?  $data['settlement_total']:0;
                        $data['settlement_already'] = 0;
                        $data['settlement_no'] = $data['settlement_total'];
                        M('OrderInfo')->save($data);
                    }
                }
            }

			$this->success('发货成功');
		}else{
			$this->error('发货失败');
		}
	}


    public function pick_list()
    {
        $content_header = '提货单列表';
        switch (I('get.type')){
            case 1:
                $condition ['pick_status'] = 1;
                $content_header = '未提货';
                break;
            case 2:
                $condition ['pick_status'] = 2;
                $content_header = '已提货';
                break;
        }

        if (I('get.order_sn')) {
            $condition ['order_sn'] = I('get.order_sn');
            $this->assign("order_sn",I('get.order_sn'));
        }

        I('get.type')?$this->assign("select",I('get.type')):$this->assign("type",0);
        $data = page(D('PickInfo'), $condition,16,'relation','pick_create_time desc');

        $userids='';
        $storeids="";
        if ($data['list']) {
            foreach ($data['list'] as $kk=>$l) {
                $userids.=$l["uid"].",";
                $storeids=$l["store_id"].",";
                foreach ($l['order_goods'] as $k=>$og) {
                    $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'].' and is_cover = 1')->find();
                    if (!$goods) {
                        $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'])->find();
                    }
                    $link = '/Uploads/'.$goods['save_path'].$goods['save_name'];
                    $data['list'][$kk]['order_goods'][$k]['goods_img'] =  $data['list'][$kk]['order_goods'][$k]['goods_img']?'/Uploads/'.$data['list'][$kk]['order_goods'][$k]['goods_img']:$link;

                }
            }
            $userids=rtrim($userids,",");
            $storeids=rtrim($storeids,",");
            $userlist=M("member","ms_common_")->where(['uid'=>["in",$userids]])->field("uid,username")->select();
            $storelist=M("store","ms_mall_")->where(['store_id'=>["in",$storeids]])->field("store_id,store_name")->select();

            foreach ($data['list'] as $ok=>$ov) {
                foreach ($userlist as $uv)
                {
                    if($ov["uid"]==$uv["uid"])
                    {
                        $data['list'][$ok]["username"]=$uv["username"];
                    }

                }
                foreach ($storelist as $sv)
                {
                    if($ov["store_id"]==$sv["store_id"])
                    {
                        $data['list'][$ok]["store_name"]=$sv["store_name"];
                    }

                }
            }

        }

        $this->assign('order_list',$data['list']);
        $this->assign('page',$data['page']);
        $this->assign('content_header',$content_header);
        $this->display();

    }

    public function pickcode() {
        $pick_sn = I("get.pick_sn");

        $json['pick_sn'] = $pick_sn;
        $json['api'] = 'pick_order';
        $res = 'http://qr.liantu.com/api.php?text='.json_encode($json);
        echo $res;
    }

    public function deliver_water() {
        $data = page(M('Water'), $condition,20);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('deliver_water');
    }

    public function printWater() {
        echo '等待格式设计中...';
    }

}
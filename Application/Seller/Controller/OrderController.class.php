<?php
namespace Seller\Controller;
use Seller\Controller\CommonController;

class OrderController extends CommonController{
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
		$condition ['store_id'] = session('store_id');
		$data = page(D('OrderInfo'), $condition,16,'relation','order_time desc');
        if ($data['list']) {
            foreach ($data['list'] as $kk=>$l) {
                $freight_total=0;
                $total=0;
                foreach ($l['order_goods'] as $k=>$og) {
                    $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'].' and is_cover = 1')->find();
                    if (!$goods) {
                        $goods = M('GoodsImg')->where('goods_id ='.$og['goods_id'])->find();
                    }
                    $link = '/Uploads/'.$goods['save_path'].$goods['save_name'];
                    $data['list'][$kk]['order_goods'][$k]['goods_img'] =  $data['list'][$kk]['order_goods'][$k]['goods_img']?'/Uploads/'.$data['list'][$kk]['order_goods'][$k]['goods_img']:$link;
                    //2017-02-02 liaopeng添加运费显示和进货价显示
                    $freight=M("goods")->where(["goods_id"=>$og["goods_id"]])->find();

                    $data['list'][$kk]['order_goods'][$k]["new_cost_pride"]=$freight["cost_price"];
                    $freight_total+=$freight["freight"]*$og["prosum"];
                    $total+=$freight["cost_price"]*$og["prosum"];
                }
                $data['list'][$kk]["freight"]=$freight_total;
                $data['list'][$kk]["new_total"]=$total;
                //修改结束
            }
        }
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
		$this->assign('shipping_list',$shipping_list);
		$info = D('OrderInfo')->relation(true)->where('order_sn = "'.I('get.order_sn').'"')->find();
		$this->assign('oi',$info);
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
		);
		if(M('OrderInfo')->save($data)){
			$this->success('发货成功');
		}else{
			$this->error('发货失败');
		}
	}
}
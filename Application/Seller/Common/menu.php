<?php
function getMenu(){
	return array(
			'home'=>array(
					'menu'=>array(
	
					),
					'url'=>U('Center/index'),
					'lang'=>'首页'
	
			),
			'goods'=>array(
					'menu'=>array(
							'goods_list'=>array(
									'url'=>U('Goods/goodsList'),
									'lang'=>'商品列表',
									'current'=>1,
							),
							'goods_info'=>array(
									'url'=>U('Goods/selectClass'),
									'lang'=>'添加商品'
							),
							'goods_brand'=>array(
									'url'=>U('Brand/applyList'),
									'lang'=>'品牌申请'
							),
					),
					'url'=>U('Center/index?module=goods'),
					'lang'=>'商品'
			),
			'order'=>array(
					'menu'=>array(
							'order_list'=>array(
									'url'=>U('Order/olist'),
									'lang'=>'订单列表',
									'current'=>1,
							),
							'order_list_1'=>array(
									'url'=>U('Order/olist?type=1'),
									'lang'=>'待付款',
							),
							'order_list_2'=>array(
									'url'=>U('Order/olist?type=2'),
									'lang'=>'待发货',
							),
							'order_list_3'=>array(
									'url'=>U('Order/olist?type=3'),
									'lang'=>'待收货',
							),
							'order_list_4'=>array(
									'url'=>U('Order/olist?type=4'),
									'lang'=>'待评价',
							),
							'order_list_5'=>array(
									'url'=>U('Order/olist?type=5'),
									'lang'=>'已完成',
							),
					),
					'url'=>U('Center/index?module=order'),
					'lang'=>'订单',
			),
            'refund'=>array(
 					'menu'=>array(
                        'refund_list'=>array(
                            'url'=>U('Refund/index'),
                            'lang'=>'退换货列表',
                            'current'=>1,
                        ),
                        'refund_list_1'=>array(
                            'url'=>U('Refund/index?type=1'),
                            'lang'=>'退货',
                        ),
                        'refund_list_2'=>array(
                            'url'=>U('Refund/index?type=2'),
                            'lang'=>'换货',
                        ),
 					),
                    'url'=>U('Center/index?module=refund'),
 					'lang'=>'退换货',
 			),
// 			'promote'=>array(
// 					'menu'=>array(
							
// 					),
// 					'url'=>U(),
// 					'lang'=>'促销',
// 			),
			'store'=>array(
					'menu'=>array(
							'setting'=>array(
									'url'=>U('Store/setting'),
									'lang'=>'店铺设置',
									'current'=>1,
							),
							'slide'=>array(
									'url'=>U('Store/slide'),
									'lang'=>'店铺幻灯',
							),
							'nav_list'=>array(
									'url'=>U('Navigation/navList'),
									'lang'=>'店铺导航'
							),
							'nav_add'=>array(
									'url'=>U('Navigation/view'),
									'lang'=>'添加导航'
							),
							'class_list'=>array(
									'url'=>U('Class/classList'),
									'lang'=>'店铺分类'
							),
							'freight_templ'=>array(
									'url'=>U('Freight/templList'),
									'lang'=>'运费模板'
							),
					),
					'url'=>U('Center/index?module=store'),
					'lang'=>'店铺'
			),
// 			'customer_service'=>array(
// 					'menu'=>array(),
// 					'url'=>U(),
// 					'lang'=>'客服',
// 			),
// 			'refund'=>array(
// 					'menu'=>array(),
// 					'url'=>U(),
// 					'lang'=>'售后',
// 			),
// 			'settlement'=>array(
// 					'menu'=>array(),
// 					'url'=>U(),
// 					'lang'=>'结算'
// 			),
// 			'statistics'=>array(
// 					'menu'=>array(),
// 					'url'=>U(),
// 					'lang'=>'统计'
// 			),
			'account'=>array(
					'menu'=>array(
							'goods_list'=>array(
									'url'=>U('Account/info'),
									'lang'=>'收款信息',
									'current'=>1,
							),
							'goods_info'=>array(
									'url'=>U('Goods/selectClass'),
									'lang'=>'添加商品'
							),
							'goods_brand'=>array(
									'url'=>U('Brand/applyList'),
									'lang'=>'品牌申请'
							),
					),
					'url'=>U('Center/index?module=account'),
					'lang'=>'账户'
			),
			
			
	);	
}

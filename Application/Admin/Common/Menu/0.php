<?php
return array (
		//系统设置
		'system' => array (
				'menu' => array (
						// 'menu_manage' => array (
						// 'menu' => array (
						// 'one_key_build' => array (
						// 'url' => '/Admin/Menu/oneKeyBuild',
						// 'lang' => '一键生成',
						// 'icon' => '' ,
						// 'id' => '1'
						// )
						// ),
						// 'url' => '',
						// 'lang' => '菜单管理',
						// 'icon' => ''
						// ),
						'ad_manage' => array (
								'menu' => array (
										'slide_list' => array (
												'url' => U ( 'Slide/slist' ),
												'lang' => '幻灯列表',
												'icon' => '' 
										),
										'ad_position' => array (
												'url' => U ( 'Adsense/positionList' ),
												'lang' => '广告位置',
												'icon' => '' 
										),
										'ad_list' => array (
												'url' => U ( 'Adsense/AdsenseList' ),
												'lang' => '广告列表',
												'icon' => '' 
										) 
								),
								'url' => '',
								'lang' => '广告列表',
								'icon' => '' 
						),
						// 'db_manage' => array (
						// 'menu' => array (
						// 'db_backup' => array (
						// 'url' => '/Admin/Database/index/type/export.html',
						// 'lang' => '数据备份',
						// 'icon' => '' ,
						// 'id' => '5'
						// ),
						// 'db_restore' => array (
						// 'url' => '/Admin/Database/index/type/import.html',
						// 'lang' => '数据恢复',
						// 'icon' => '' ,
						// 'id' => '6'
						// )
						// ),
						// 'url' => '',
						// 'lang' => '数据恢复',
						// 'icon' => ''
						// ),
						'third_manage' => array (
								'menu' => array (
										'payment' => array (
												'url' => U ( 'Config/clist?type=pay' ),
												'lang' => '支付方式',
												'icon' => '' 
										),
										'sms_config' => array (
												'url' => U ( 'Config/clist?type=sms' ),
												'lang' => '短信配置',
												'icon' => '' 
										) 
								),
								'url' => '',
								'lang' => '第三方平台',
								'icon' => '' 
						) 
				),
				'url' => U ( 'Index/index?module=system' ),
				'lang' => '系统设置',
				'icon' => '' 
		),
		//商城管理
		'Mall' => array (
				'menu' => array (

						'navigation_manage' => array (
								'menu' => array (
										'nav_view' => array (
												'url' => U ( 'Navigation/view' ),
												'lang' => '添加导航',
												'icon' => '' 
										),
										'nav_list' => array (
												'url' => U ( 'Navigation/navList' ),
												'lang' => '导航列表',
												'icon' => '' 
										) 
								),
								'url' => '',
								'lang' => '导航管理',
								'icon' => '' 
						),
						'store_manage' => array (
								'menu' => array (
										'store_list' => array (
												'url' => U ( 'Store/storeList' ),
												'lang' => '店铺列表',
												'icon' => '' 
										) 
								)
								,
								'url' => '',
								'lang' => '店铺管理',
								'icon' => '' 
						),
						'goods_manage' => array (
								'menu' => array (
										'goods_list_seller' => array (
												'url' => U ( 'Goods/goodsList?seller=1&all_goods=1' ),
												'lang' => '商家产品',
												'icon' => '' 
										),
										'goods_list' => array(
												'url' => U ('Goods/goodsList' ) ,
												'lang' => '平台商品',
												'icon' => '' ,
										),
										'goods_view' => array(
												'url' => U ( 'Goods/view' ) ,
												'lang' => '添加商品',
												'icon' => '' ,
										),
										'goods_tag' => array(
												'url' => U ( 'Tag/info?module=Mall' ) ,
												'lang' => '商品标签',
												'icon' => '' ,
										),
										'goods_brand' => array (
												'url' => U ( 'Brand/brandList' ),
												'lang' => '商品品牌',
												'icon' => '' 
										),
										'goods_model' => array (
												'url' => U ( 'Model/modelList' ),
												'lang' => '商品模型',
												'icon' => '' 
										)
										,
										'goods_class' => array (
												'url' => U ( 'Class/classList' ),
												'lang' => '商品分类',
												'icon' => '' 
										) 
								)
								,
								'url' => '',
								'lang' => '商品管理',
								'icon' => '' 
						),
						'order_manage' => array (
								'menu' => array (
										'olist_1' => array (
												'url' => U ( 'Order/olist?type=1' ),
												'lang' => '待付款',
												'icon' => ''
										),
										'olist_2' => array (
												'url' => U ( 'Order/olist?type=2' ),
												'lang' => '待发货',
												'icon' => ''
										),
										'olist_3' => array (
												'url' => U ( 'Order/olist?type=3' ),
												'lang' => '待收货',
												'icon' => ''
										),
										'olist_4' => array (
												'url' => U ( 'Order/olist?type=4' ),
												'lang' => '待评价',
												'icon' => ''
										),
										'olist_5' => array (
												'url' => U ( 'Order/olist?type=5' ),
												'lang' => '已完成',
												'icon' => ''
										),
								),
								'url' => '',
								'lang' => '订单管理',
								'icon' => ''
						),
						'shipping_manage' => array (
								'menu' => array (
										'shipping_list' => array (
												'url' => U ( 'Shipping/shippingList' ),
												'lang' => '配送方式',
												'icon' => '' 
										),
										'shipping_templ' => array (
												'url' => U ( 'Shipping/templList' ),
												'lang' => '运费模板',
												'icon' => '' 
										) 
								),
								'url' => '',
								'lang' => '配送管理',
								'icon' => '' 
						),
						'mall_setting' => array (
								'menu' => array (
										'basic_info' => array (
												'url' => U ( 'Setting/info?item=1' ),
												'lang' => '基本信息',
												'icon' => '' 
										),
										'rule_setting' => array (
												'url' => U ('Rule/info?type=1'),
												'lang' => '通用规则',
												'icon' => '' 
										) ,
										'rule_4' => array (
												'url' => U ('Rule/info?type=4'),
												'lang' => '消费商规则',
												'icon' => ''
										) ,
										'rule_5' => array (
												'url' => U ('Rule/info?type=5'),
												'lang' => '合作商规则',
												'icon' => ''
										) ,
										'rule_6' => array (
												'url' => U ('Rule/info?type=6'),
												'lang' => '代理商规则',
												'icon' => ''
										) ,
								)
								,
								'url' => '',
								'lang' => '商城设置',
								'icon' => '' 
						),
                    'refund_manage' => array (
                        'menu' => array (
                            'nav_view' => array (
                                'url' => U ( 'Refund/tui' ),
                                'lang' => '退货',
                                'icon' => ''
                            ),
                            'nav_list' => array (
                                'url' => U ( 'Refund/huan' ),
                                'lang' => '换货',
                                'icon' => ''
                            )
                        ),
                        'url' => '',
                        'lang' => '退换货管理',
                        'icon' => ''
                    )
				)
				,
				'url' => U ( 'Index/index?module=Mall' ),
				'lang' => '商城管理',
				'icon' => '' 
		),
		'member' => array (
				'menu' => array (
						'admin_manage' => array (
								'menu' => array (
										'role_list' => array (
												'url' => '/Admin/Admin/roleList.html',
												'lang' => '角色列表',
												'icon' => '',
										),
										'role_add' => array (
												'url' => '/Admin/Admin/roleView.html',
												'lang' => '添加角色',
												'icon' => '',
										),
										'admin_list' => array (
												'url' => '/Admin/Admin/adminList.html',
												'lang' => '管理员列表',
												'icon' => '',
										),
										'admin_add' => array (
												'url' => '/Admin/Admin/adminView.html',
												'lang' => '添加管理员',
												'icon' => '',
										) 
								),
								'url' => '',
								'lang' => '管理员管理',
								'icon' => '' 
						),
						'member_manage' => array (
								'menu' => array (
										'member_list_1' => array (
												'url' => U ( 'Member/memberList', array (
														'rank' => 1 
												) ),
												'lang' => '消费商',
												'icon' => '' 
										),
										'member_list_2' => array (
												'url' => U ( 'Member/memberList', array (
														'rank' => 2 
												) ),
												'lang' => '合格的消费商',
												'icon' => '' 
										),
										'member_list_3' => array (
												'url' => U ( 'Member/memberList', array (
														'rank' => 3 
												) ),
												'lang' => '合作商',
												'icon' => '' 
										),
										'member_list_4' => array (
												'url' => U ( 'Member/memberList', array (
														'rank' => 4 
												) ),
												'lang' => '特殊合作商',
												'icon' => '' 
										),
										'member_list_5' => array (
												'url' => U ( 'Member/memberList', array (
														'seller' => 1 
												) ),
												'lang' => '商家',
												'icon' => '' 
										) ,
										'member_list_6' => array (
												'url' => U ( 'Member/realNameAUTH'),
												'lang' => '实名认证',
												'icon' => ''
										)
								),
								'url' => '',
								'lang' => '会员管理',
								'icon' => '' 
						),
						'agent_manage' => array (
								'menu' => array (
										'role_list' => array (
												'url' => U('Member/agentApply'),
												'lang' => '申请列表',
												'icon' => '',
										),
										'role_add' => array (
												'url' => U ( 'Member/memberList', array ('agent_level' => 3 ) ),
												'lang' => '省代理',
												'icon' => '',
										),
										'admin_list' => array (
												'url' => U ( 'Member/memberList', array ('agent_level' => 2 ) ),
												'lang' => '市代理',
												'icon' => '',
										),
										'admin_add' => array (
												'url' => U ( 'Member/memberList', array ('agent_level' => 1 ) ),
												'lang' => '县代理',
												'icon' => '',
										)
								),
								'url' => '',
								'lang' => '代理商管理',
								'icon' => ''
						),						
						'admin_manage' => array (
								'menu' => array (
										'role_list' => array (
												'url' => '/Admin/Admin/roleList.html',
												'lang' => '角色列表',
												'icon' => '',
										),
										'role_add' => array (
												'url' => '/Admin/Admin/roleView.html',
												'lang' => '添加角色',
												'icon' => '',
										),
										'admin_list' => array (
												'url' => '/Admin/Admin/adminList.html',
												'lang' => '管理员列表',
												'icon' => '',
										),
										'admin_add' => array (
												'url' => '/Admin/Admin/adminView.html',
												'lang' => '添加管理员',
												'icon' => '',
										)
								),
								'url' => '',
								'lang' => '管理员管理',
								'icon' => ''
						),
				),
				'url' => U ( 'Index/index?module=member' ),
				'lang' => '用户管理',
				'icon' => '' 
		),
		//文章管理
// 		'article' => array (
// 				'menu' => array (
// 						'article' => array (
// 								'menu' => array (
// 										'clist' => array (
// 												'url' => '/Admin/Article/clist.html',
// 												'lang' => '文章分类',
// 												'icon' => '',
// 										),
// 										'articlelist' => array (
// 												'url' => '/Admin/Article/article.html',
// 												'lang' => '文章列表',
// 												'icon' => '',
// 										),
// 										'view' => array (
// 												'url' => '/Admin/Article/view.html',
// 												'lang' => '添加文章',
// 												'icon' => '',
// 										) 
// 								),
// 								'url' => '',
// 								'lang' => '文章管理',
// 								'icon' => '' 
// 						) 
// 				),
// 				'url' => U ( 'Index/index?module=article' ),
// 				'lang' => '文章管理',
// 				'icon' => '' 
// 		),
		//论坛管理
		'forum' => array (
				'menu' => array (
						'article' => array (
								'menu' => array (
										'clist' => array (
												'url' => '/Admin/Article/clist.html',
												'lang' => '文章分类',
												'icon' => '',
										),
										'articlelist' => array (
												'url' => '/Admin/Article/article.html',
												'lang' => '文章列表',
												'icon' => '',
										),
										'view' => array (
												'url' => '/Admin/Article/view.html',
												'lang' => '添加文章',
												'icon' => '',
										) 
								),
								'url' => '',
								'lang' => '文章管理',
								'icon' => '' 
						) 
				),
				'url' => U ( 'Index/index?module=forum' ),
				'lang' => '论坛管理',
				'icon' => '' 
		) ,
		//财务管理
		'finance' => array (
				'menu' => array (
						'finance_manage' => array (
								'menu' => array (
										'glist' => array (
												'url' => U('Finance/glist'),
												'lang' => '商品列表',
												'icon' => '',
										),
										'delivery_detail' => array (
												'url' => U('Finance/deliveryDetail'),
												'lang' => '发货明细',
												'icon' => '',
										),

                                        'recharge' => array (
                                            'url' => U('Finance/rechargelist'),
                                            'lang' => '充值列表',
                                            'icon' => '',
                                        ),
                                        'withdraw' => array (
                                            'url' => U('Finance/withdrawlist'),
                                            'lang' => '提现列表',
                                            'icon' => '',
                                        ),
										'settlement_list' => array (
												'url' => U('Finance/settlementList'),
												'lang' => '结算统计',
												'icon' => '',
										),
										'account_list' => array (
											'url' => U('Finance/accountList'),
											'lang' => '会员资金明细',
											'icon' => '',
										),

										
								),
								'url' => '',
								'lang' => '财务管理',
								'icon' => ''
						)
				),
				'url' => U ( 'Index/index?module=finance' ),
				'lang' => '财务管理',
				'icon' => ''
		)
)
;
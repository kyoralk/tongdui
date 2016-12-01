<?php
function getMenu(){
	return array (
			'menu_manager' => array (
					'menu' => array (
							'info_edit' => array (
									'url' => U('Member/info'),
									'lang' => '资料修改',
									'icon' => ''
							)
					),
					'url' => '',
					'lang' => '资料管理',
					'icon' => ''
			),
			'relation_manager' => array (
					'menu' => array (
							'relation_network' => array (
									'url' => '/Mall/Relation/manage',
									'lang' => '管理网络',
									'icon' => ''
							),
// 							'referrer_network'=>array(
// 									'url' => '/Admin/Menu/oneKeyBuild',
// 									'lang' => '推荐网络',
// 									'icon' => ''
// 							),
					),
					'url' => '',
					'lang' => '网络管理',
					'icon' => ''
			),
			'business_centre' => array (
					'menu' => array (
							'member_activate' => array (
									'url' => U('BC/MemberList',array('type'=>1)),
									'lang' => '会员激活',
									'icon' => ''
							),
							'member_list' => array (
									'url' => U('BC/MemberList'),
									'lang' => '会员列表',
									'icon' => ''
							),
							'assist_buy' => array (
									'url' => U('BC/MemberList',array('type'=>2)),
									'lang' => '协助购买',
									'icon' => ''
							),
					
							
					),
					'url' => '',
					'lang' => '商务中心',
					'icon' => ''
			),
			'finance_manager' => array (
					'menu' => array (
							'account_log' => array (
									'url' => U('Account/accountLog'),
									'lang' => '账户流水',
									'icon' => ''
							),
							'withdraw_apply' => array (
									'url' => U('Account/withdraw'),
									'lang' => '申请提现',
									'icon' => ''
							),
							'withdraw_log' => array (
									'url' => U('Account/withdrawLog'),
									'lang' => '提现记录',
									'icon' => ''
							),
							'recharge_apply' => array (
									'url' => U('Account/recharge'),
									'lang' => '申请充值',
									'icon' => ''
							),
							'recharge_log' => array (
									'url' => U('Account/rechargeLog'),
									'lang' => '充值记录',
									'icon' => ''
							),
							
					),
					'url' => '',
					'lang' => '财务管理',
					'icon' => ''
			),
	);
}
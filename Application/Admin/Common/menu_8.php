<?php function getModule(){return array('member'=>'/Admin/Admin/roleHandler.html',);}function getMenu(){return array('member'=>array('menu'=>array('member_manager'=>array('menu'=>array('member_list'=>array('url'=>'/Admin/Member/memberList.html','lang'=>'会员列表','icon'=>'',),),'url'=>'','lang'=>'会员列表','icon'=>'',),'reward_setting'=>array('menu'=>array('reward_setting'=>array('url'=>'/Admin/Reward/setting.html','lang'=>'规则设置','icon'=>'',),),'url'=>'','lang'=>'规则设置','icon'=>'',),'finance_manager'=>array('menu'=>array('running_account'=>array('url'=>'/Admin/Finance/PlatformRunning.html','lang'=>'账户流水','icon'=>'',),'withdraw_apply'=>array('url'=>'/Admin/Finance/withdrawApply.html','lang'=>'提现申请','icon'=>'',),'recharge_apply'=>array('url'=>'/Admin/Finance/rechargeApply.html','lang'=>'充值申请','icon'=>'',),),'url'=>'','lang'=>'充值申请','icon'=>'',),'article'=>array('menu'=>array('clist'=>array('url'=>'/Admin/Article/clist.html','lang'=>'文章分类','icon'=>'',),'articlelist'=>array('url'=>'/Admin/Article/article.html','lang'=>'文章列表','icon'=>'',),'view'=>array('url'=>'/Admin/Article/view.html','lang'=>'添加文章','icon'=>'',),),'url'=>'','lang'=>'添加文章','icon'=>'',),'menu_manager'=>array('menu'=>array('one_key_build'=>array('url'=>'/Admin/Menu/oneKeyBuild','lang'=>'一键生成','icon'=>'',),),'url'=>'','lang'=>'一键生成','icon'=>'',),),'url'=>'','lang'=>'用户管理','icon'=>'',),);}
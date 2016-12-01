<?php
$basic_lang =  array(
		/* *新添加****/
		'GWQ'=>'购物券',
		'YJT'=>'一卷通',
		'BDB'=>'报单币',
		'DZB'=>'电子币',
		'ZCB'=>'资产包',
		'JF'=>'增值积分',
		'RANK'=>'等级',
		'ZJE'=>'总金额',
		'TJGS'=>'推广消费商个数',
		'GWQGDCZ'=>'购物券固定充值',
		'TXSXF'=>'提现手续费',
		'JFDJ'=>'积分冻结',
		'JFSF'=>'积分释放',
		'DCSF'=>'单次释放',
		'CZZS'=>'充值赠送',
		'XFZS'=>'消费赠送',
		'D1'=>'第一代',
		'D2'=>'第二代',
		'D3'=>'第三代',
		'D4'=>'第四代',
		'D5'=>'第五代',
		'D6'=>'第六代',
		'D7'=>'第七代',
		'D8'=>'第八代',
		'D9'=>'第九代',
)
;
$menu_lang = require_once 'zh-menu.php';
return  array_merge($menu_lang,$basic_lang);
?>
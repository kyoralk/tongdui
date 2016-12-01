<?php
return array (
		'DB_PREFIX'=>'ms_mall_',
		'TMPL_ACTION_ERROR' => 'Public:error',
		//默认成功跳转对应的模板文件
		'TMPL_ACTION_SUCCESS' => 'Public:success',
		'DEFAULT_THEME'=>'PC',//默认主题
		'THEME_LIST'=>'PC,WAP',//主题列表
		'TMPL_DETECT_THEME'=>true,//开启自动侦测模版主题
		'TMPL_PARSE_STRING' => array (
				'__PMALL__' => '/Static/Mall/PC'
		) ,
) 
;
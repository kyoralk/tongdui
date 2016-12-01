<?php
return array (
		'VERSION'=>'1.0.0',
		// 'SHOW_PAGE_TRACE' =>true,
		'URL_MODEL' => 2, // URL访问模式,可选参数0、1、2、3,代表以下四种模式：0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE 模式); 3 (兼容模式) 默认为PATHINFO 模式
		'SESSION_PREFIX' => 'ms_', // session 前缀
		/* 数据库设置 */
		'DB_TYPE' => 'mysqli', // 数据库类型
//		'DB_HOST' => '114.215.159.21', // 服务器地址
        'DB_HOST' => '127.0.0.1', // 服务器地址
		'DB_NAME' => 'tongdui', // 数据库名
		'DB_USER' => 'root', // 用户名
//		'DB_PWD' => 'jycf20150805@', // 密码'
        'DB_PWD' => 'kyo123',
		'DB_PREFIX' => 'ms_common_', // 数据库表前缀
		'DB_PREFIX_G' => 'ms_',
		'DB_PREFIX_C' => 'ms_common_',
		'DB_PREFIX_MALL' => 'ms_mall_',
		'LOAD_EXT_CONFIG' => 'path,img,bank,sms_tmpl',
		'LANG_SWITCH_ON' => true, // 开启语言包功能
		'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
		'LANG_LIST' => 'zh-cn', // 允许切换的语言列表 用逗号分隔
		'DEFAULT_LANG' => 'zh-cn', // 默认语言
		'VAR_LANGUAGE' => 'l', // 默认语言切换变量
		'MODULE_ALLOW_LIST' => array ('Admin','General','Mall','Seller','Collect','Mobile','API'),
		'DEFAULT_MODULE' => 'Seller',
		'FRONT_MODULE_LIST' =>array('Mall'=>'商城'),//前台模块
		'TMPL_ACTION_ERROR' => 'Public:error',
		//默认成功跳转对应的模板文件
		'TMPL_ACTION_SUCCESS' => 'Public:success',
		'SECRET_KEY'=>'0fc0284f447fe8802b0f6db0ae3fad19',
)
;
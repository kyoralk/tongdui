<?php function getModule(){return array('member'=>'/Admin/Admin/roleHandler.html',);}function getMenu(){return array('member'=>array('menu'=>array('admin_manager'=>array('menu'=>array('admin_list'=>array('url'=>'/Admin/Admin/adminList.html','lang'=>'管理员列表','icon'=>'',),'admin_add'=>array('url'=>'/Admin/Admin/adminView.html','lang'=>'添加管理员','icon'=>'',),'role_list'=>array('url'=>'/Admin/Admin/roleList.html','lang'=>'角色列表','icon'=>'',),'role_add'=>array('url'=>'/Admin/Admin/roleView.html','lang'=>'添加角色','icon'=>'',),),'url'=>'','lang'=>'添加角色','icon'=>'',),),'url'=>'','lang'=>'用户管理','icon'=>'',),);}
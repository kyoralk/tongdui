<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class IndexController extends CommonController {
    // 显示管理后台首页
    public function index(){
    	layout(false);
    	$module = I('get.module','Mall');
		$module_list = require_once MODULE_PATH.'Common/Menu/'.session('role_id').'.php';
    	$this->assign('module_list',$module_list);
    	$this->assign('menu_list',$module_list[$module]);
    	$this->assign('module',$module);
    	$this->display();
    }
    // 显示管理后台欢迎页
    public function home(){
    	$this->assign('server_info',$this->getServerInfo());
    	$this->assign('content_header','欢迎使用');
    	$this->display();
    }
}
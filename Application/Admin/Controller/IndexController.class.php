<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;

class IndexController extends CommonController {
    // 显示管理后台首页
    public function index(){
    	layout(false);
    	$module = I('get.module','Mall');
	//	$module_list = require_once MODULE_PATH.'Common/Menu/'.session('role_id').'.php';
        $role=M("role")->where('role_id ='.session('role_id'))->getField("menu_id_str");
        $menu=M('menu')->where('menu_id in ('.$role.')')->select();
        $module_list=$this->makemenu($menu,0);

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
  
    public function makemenu($data,$pId){
        $tree = '';
        foreach($data as $k => $v)
        {
            if($v['menu_pid'] == $pId)
            {         //父亲找到儿子
                $redata[$v['menu_key']]['menu'] = $this->makemenu($data, $v['menu_id']);
                $redata[$v['menu_key']]['url']=$v['menu_function'];
                $redata[$v['menu_key']]['lang']=$v['menu_name'];
                $redata[$v['menu_key']]['url']=$v['menu_function'];
                $tree = $redata;
                //unset($data[$k]);
            }
        }
        return $tree;

    }
}
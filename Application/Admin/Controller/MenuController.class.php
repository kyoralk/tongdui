<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
load('@.menu_0');
class MenuController extends CommonController{
	/**
	 * 一键生成
	 */
	public function oneKeyBuild(){
		$data = $this->createData();
		$menu = M('Menu',C('DB_PREFIX_C'));
		$menu->startTrans();
		try {
			$menu->execute('TRUNCATE TABLE '.C('DB_PREFIX_C').'menu');//清空表
			$menu->addAll($data);
		} catch (Exception $e) {
			$menu->rollback();
			$this->error('生成失败');
		}
		$menu->commit();
		redirect('/Admin/Menu/succ');
	}
	/**
	 * 菜单列表
	 */
	public function mlist(){
		$menu = M('Menu',C('DB_PREFIX_C'));
		$menu_list = $menu->where($condition)->select();
		$menu_list = list_to_tree($menu_list,'menu_id','menu_pid');
		$this->display();
	}
	/**
	 * 添加菜单
	 */
	public function add(){
		$menu = M('Menu',C('DB_PREFIX_C'));
		$menu_name = I('post.menu_name');
		$count = count($menu_name);
		for($i=0;$i<$count;$i++){
			$data[$i]=array(
					'menu_pid'=>$_POST['menu_pid'][$i],
					'menu_name'=>$_POST['menu_name'][$i],
					'menu_key'=>$_POST['menu_key'][$i],
					'menu_type'=>$_POST['menu_type'][$i],
					'menu_function'=>$_POST['menu_function'][$i],
					'icon'=>$_POST['icon'][$i],
			);
		}
		F('menu_data',$data);//把要保存的数据保存到缓存中
// 		$res = $menu->execute('TRUNCATE TABLE '.C('DB_PREFIX_C').'menu');//清空表
		if($res==0){
			if($menu->addAll(F('menu_data'))){
				$this->createLang(F('menu_data'));
				F('menu_data',null);//清空缓存
				F('menu_list',null);//清空菜单缓存
				$this->menuTree();//生成菜单缓存
				$this->success('添加成功',U('Menu/mlist'));
			}else{
				$this->error('添加失败');
			}
		}
	}
	
	/**
	 * 生成语言文件
	 */
	public function createLang(){
		$menu = M('Menu',C('DB_PREFIX_C'));
		$data = $menu->field('menu_key,menu_name')->select();
		$zh_menu='';
		$zh_menu .='<?php return array(';
		foreach ($data as $item){
			$zh_menu .="'{$item['menu_key']}'=>'{$item['menu_name']}',";
		}
		$zh_menu .=');?>';
		$myfile = fopen('./Application/Admin/Lang/zh-menu.php', "w") or die("Unable to open file!");
		fwrite($myfile, $zh_menu);
		fclose($myfile);
	}
	/**
	 *生成角色菜单
	 */
	public function createMenu($role_id,$menu_id_array){
		$condition['menu_id'] = array('IN',$menu_id_array);
		$menu = M('Menu',C('DB_PREFIX_C'));
		$menu_list = $menu->where($condition)->select();
		$menu_list = list_to_tree($menu_list,'menu_id','menu_pid');
		$common_str ='<?php '; 
		$module_str = 'function getModule(){return array(';
		$menu = "function getMenu(){return array(";
		foreach ($menu_list as $item_1){
			$module_str .="'{$item_1['menu_key']}'=>'".U($item_1['menu_function'])."',";
			$menu .="'".$item_1['menu_key']."'=>array(";
			$menu .="'menu'=>array(";
			foreach ($item_1['_child'] as $item_2){
				$menu .="'".$item_2['menu_key']."'=>array(";
				$menu .="'menu'=>array(";
				foreach ($item_2['_child'] as $item_3){
					$menu .="'".$item_3['menu_key']."'=>array(";
					$menu .="'url'=>'".$item_3['menu_function']."',";
					$menu .="'lang'=>'".$item_3['menu_name']."',";
					$menu .="'icon'=>'',";
					$menu .="),";
				}
					
				$menu .="),";
				$menu .="'url'=>'',";
				$menu .="'lang'=>'".$item_3['menu_name']."',";
				$menu .="'icon'=>'',";
				$menu .="),";
			}
		
			$menu .="),";
			$menu .="'url'=>'',";
			$menu .="'lang'=>'".$item_1['menu_name']."',";
			$menu .="'icon'=>'',";
			$menu .="),";
		}
		$menu .=");}";
		$module_str .=');}';
		$string = $common_str.$module_str.$menu;
		$myfile = fopen('./Application/Admin/Common/menu_'.$role_id.'.php', "w") or die("Unable to open file!");
		fwrite($myfile, $string);
		fclose($myfile);

		
		
	}
	/**
	 * 菜单树
	 */
	public function menuTree(){
		if(F('menu_list')){
			return F('menu_list');
		}else{
			$menu = M('Menu',C('DB_PREFIX_C'));
			$menu_list = $menu->select();
			$menu_list = list_to_tree($menu_list,'menu_id','menu_pid');
			return $menu_list;
		}
	}
	/**
	 * 生成平面数据
	 */
	public function createData(){
		$list = getMenu();
		$i = 1;
		foreach ($list as $key=>$value){
			$them_data_1[]=array(
					'menu_id'=>$i++,
					'menu_pid'=>0,
					'menu_key'=>$key,
					'menu_name'=>$value['lang'],
					'menu_function'=>'',
					'icon'=>'',
					'child'=>$value['menu']
			);
		}
		
		$i = count($them_data_1)+1;
		foreach ($them_data_1 as $them_key=>$them_value){
			$data_1[]=array(
					'menu_id'=>$them_value['menu_id'],
					'menu_pid'=>$them_value['menu_pid'],
					'menu_key'=>$them_value['menu_key'],
					'menu_name'=>$them_value['menu_name'],
					'menu_function'=>$them_value['url'],
					'icon'=>$them_value['icon'],
			);
			foreach ($them_value['child'] as $key=>$value){
				$them_data_2[]=array(
						'menu_id'=>$i++,
						'menu_pid'=>$them_value['menu_id'],
						'menu_key'=>$key,
						'menu_name'=>$them_value['lang'],
						'menu_function'=>$value['url'],
						'icon'=>$value['icon'],
						'child'=>$value['menu']
				);
			}
		}
		
		$i = count($them_data_1) + count($them_data_2)+1;
		foreach ($them_data_2 as $them_key=>$them_value){
			$data_2[]=array(
					'menu_id'=>$them_value['menu_id'],
					'menu_pid'=>$them_value['menu_pid'],
					'menu_key'=>$them_value['menu_key'],
					'menu_name'=>$them_value['menu_name'],
					'menu_function'=>$them_value['url'],
					'icon'=>$them_value['icon'],
			);
			foreach ($them_value['child'] as $key=>$value){
				$data_3[]=array(
						'menu_id'=>$i++,
						'menu_pid'=>$them_value['menu_id'],
						'menu_key'=>$key,
						'menu_name'=>$value['lang'],
						'menu_function'=>$value['url'],
						'icon'=>$value['icon'],
				);
			}
		}		
		return array_merge_recursive($data_1,$data_2,$data_3);
	}
	/**
	 * 生成php数组
	 * 工具方法，一般用不到
	 */
	public function createPHPArray(){
		$menu = M('Menu',C('DB_PREFIX_C'));
		$menu_list = $menu->where($condition)->select();
		$menu_list = list_to_tree($menu_list,'menu_id','menu_pid');
		$str = "<?php array(";
		foreach ($menu_list as $item_1){
			$str .="'".$item_1['menu_key']."'=>array(";
			$str .="'menu'=>array(";
			foreach ($item_1['_child'] as $item_2){
				$str .="'".$item_2['menu_key']."'=>array(";
				$str .="'menu'=>array(";
				foreach ($item_2['_child'] as $item_3){
					$str .="'".$item_3['menu_key']."'=>array(";
					$str .="'url'=>'".$item_3['menu_function']."',";
					$str .="'lang'=>'".$item_3['menu_name']."',";
					$str .="'icon'=>'',";
					$str .="),";
				}
					
				$str .="),";
				$str .="'url'=>'',";
				$str .="'lang'=>'".$item_3['menu_name']."',";
				$str .="'icon'=>'',";
				$str .="),";
			}
				
			$str .="),";
			$str .="'url'=>'',";
			$str .="'lang'=>'".$item_1['menu_name']."',";
			$str .="'icon'=>'',";
			$str .="),";
		}
		
		$str .=");";
		$myfile = fopen('./Application/Admin/Common/menu_hello.php', "w") or die("Unable to open file!");
		fwrite($myfile, $str);
		fclose($myfile);		
	}
	
	
}
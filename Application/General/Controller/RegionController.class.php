<?php
namespace General\Controller;
use Think\Controller;
class RegionController extends Controller{
	/**
	 * 获取城市数据
	 */
	public function getRegion(){
		if(F('region_list')){
			$region_list = F('region_list');
		}else{
			$Region = M('Region',C('DB_PREFIX_C'));
			$region_list = $Region->where('pid = 0')->select();
			$count_0 = count($region_list);
			for($i = 0;$i<$count_0;$i++){
				$region_list[$i]['child']=$Region->where('pid = '.$region_list[$i]['id'])->select();
				$count_1 = count($region_list[$i]['child']);
				for($j = 0;$j<$count_1;$j++){
					$region_list[$i]['child'][$j]['child']=$Region->where('pid = '.$region_list[$i]['child'][$j]['id'])->select();
				}
			}
			F('region_list',$region_list);
		}
		return $region_list;
	}
	public function jsonRegion(){
		jsonReturn($this->getRegion());
	}
	
	/**
	 * 根据城市名字获取地区id
	 */
	public function getRegionID($name){
		$Region = M('Region',C('DB_PREFIX_C'));
		$id = $Region->where('name = "'.$name.'"')->getField('id');
		return $id;
	}

	
	/**
	 * 地区联动
	 */
	public function link(){
		$Region = M('Region',C('DB_PREFIX_C'));
		$res = $Region->where('pid = '.I('get.id'))->select();
		if($res){
			jsonReturn($res);
		}else{
			jsonReturn('','',0);
		}
	}
	

	
}
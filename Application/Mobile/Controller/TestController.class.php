<?php
namespace Mobile\Controller;
use Think\Controller;

class TestController extends Controller{
	
public function index(){
		header('Content-type:text/json');
		$params = array(
				'token'=>'93542def8d7d6103898eef560a8499d8',
				'out_trade_no'=>'BUY_14787880282221',
				'sina'=>md5('93542def8d7d6103898eef560a8499d8,BUY_14787880282221'),
		);
		$url = 'http://tongdui.hulianwangdai.com/Mobile';
		$res = http($url.'/Notify/client', $params);
		dump(json_decode($res,true));
	}
	
	
	public function getTop(){
		
		$node_id = 10;
		$Node = M('MemberNode',C('DB_PREFIX_C'));
		$top_list = array();
// 		$i = 1;
		do{
			$node_info = $Node->where('left_node_id = '.$node_id.' OR right_node_id = '.$node_id)->field('node_id,uid,lyj,ryj,star_level')->find();
			if(!empty($node_info)){
				$node_id = $node_info['node_id'];
				$top_list[] = array(
						'uid'=>$node_info['uid'],
						'lyj'=>$node_info['lyj'],
						'ryj'=>$node_info['ryj'],
						'star_level'=>$node_info['star_level'],
// 						'floor'=>$i,
				);
			}
// 			$i++;
// 			if($floor && $i > $floor){
// 				$node_info = null;
// 			}
		}while (!empty($node_info));
		$add_yj['lyj'] = array('exp','lyj+1');
		
		$add_yj_uid = array_column($top_list, 'uid');
		if(count($add_yj_uid)>1){
			$uid_list['uid'] = array('in',$add_yj_uid);
			
		}else{
			$uid_list['uid'] = $add_yj_uid[0];
		}
		
		M('MemberNode',C('DB_PREFIX_C'))->where($uid_list)->save($add_yj);
// 		M('MemberNode')->where($add_yj_w)
		exit();
	}
	
	
	public function leftNode(){
		$node_id = 4;
		$position = 'right';
		$Node = M('MemberNode',C('DB_PREFIX_C'));
		$temp_node_id = $Node->where('node_id = '.$node_id)->getField($position.'_node_id');
		echo $temp_node_id;
		if(empty($temp_node_id)){
			dump($node_id) ;
			exit();
		}
		while (!empty($temp_node_id)){
			$left_node_id = $Node->where('node_id = '.$temp_node_id)->getField('left_node_id');
			if(!empty($left_node_id)){
				$temp_node_id = $left_node_id;
			}else{
				$left_node_id = $temp_node_id;
				$temp_node_id = null;
			}
		}
		dump($left_node_id);
	}
}
<?php
namespace Collect\Controller;
use Think\Controller;
class CollectController extends Controller {
	/**
	 * 采集列表
	 * @param array $rule_list
	 */
	protected function getList($rule_list = array()){
		import ("@.Org.phpQuery");
		\phpQuery::newDocumentFile($rule_list['target_url']);
		foreach($rule_list['content'] as $k=>$v){
			switch ($v[1]){
				case 'text':
					$obj = pq($v[0]);
					foreach ($obj as $item){
						$text = pq($item)->text();
						if($v[3]){//内容替换
							foreach ($v[3] as $search=>$replace){
								$text = trim(str_replace($search,$replace,$text));
							}
						}
						$data[$k][] = $text;
					}
					break;
				case 'html':
					break;
				case 'img':
					$obj = pq($v[0]);
					foreach ($obj as $item){
						$html = pq($item)->html();
						if($v[6]){//正则匹配
							foreach ($v[6] as $pattern){
								preg_match($pattern, $html, $match);
								$html=$match[1];
							}
						}
						$data[$k][] = $html;
							
					}
					break;
				default:
					$obj = pq($v[0]);
					foreach ($obj as $item){
						$res = pq($item)->attr($v[1]);
						$data[$k][] = $v[5].$res;
					}
			}
	
		}
		return $data;
	}
	/**
	 * 采集内容
	 * @param array $rule_list
	 */
	protected function getContent($rule_list = array()){
		import ("@.Org.phpQuery");
		\phpQuery::newDocumentFile($rule_list['target_url']);
		foreach($rule_list['content'] as $k=>$v){
			switch ($v[1]){
				case 'text':
					$data[$k] = pq($v[0])->text();
					if(is_numeric($v[2])){//使用eq
						$data[$k] = pq($v[0])->eq($v[2])->text();
					}
					break;
				case 'html':
					$data[$k] = pq($v[0])->html();
					if(is_numeric($v[2])){//使用eq
						$data[$k] = pq($v[0])->eq($v[2])->html();
					}
					break;
				default:
					$data[$k] = pq($v[0])->attr($v[1]);
					if(is_numeric($v[2])){//使用eq
						$data[$k] = pq($v[0])->eq($v[2])->attr($v[1]);
					}
			}
			if($v[3]){//内容替换
				foreach ($v[3] as $search=>$replace){
					$data[$k] = trim(str_replace($search,$replace,$data[$k]));
				}
			}
			if($v[5]){//网址补全
				$data[$k] = $v[5].$data[$k];
			}
			if($v[6]){//正则匹配
				foreach ($v[6] as $pattern){
					preg_match($pattern, $data[$k], $match);
// 					$data[$k]=$match[0];
					$data[$k]=trim($match[1]);
				}
			}
			$data[$k] = htmlspecialchars($data[$k]);
			$data[$k] = str_replace("\n", "", $data[$k]);
			
		}
		 
		return $data;
	}
	protected function getListByFind(){
		import ("@.Org.phpQuery");
		\phpQuery::newDocumentFile($rule_list['target_url']);
		foreach($rule_list['content'] as $k=>$v){
			switch ($v[1]){
				case 'text':
					$obj = pq($v[0]);
					foreach ($obj as $item){
						$data[$k][] = pq($item)->text();
					}
					break;
				case 'html':
					break;
				default:
					$obj = pq($v[0]);
					foreach ($obj as $item){
						$res = pq($item)->find($selectors)->attr($v[1]);
						$data[$k][] = $v[5].$res;
					}
			}
			 
		}
		return $data;
		 
	}	
}
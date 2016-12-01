<?php
/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
	function  jindu($has,$all){
		$data = (sprintf("%.2f", $has/$all))*100;
		return $data;
	}
	function getMoneyFormt($money){
		if($money>=100000 && $money<=100000000){
			$res = getFloatValue(($money/10000),2)."万";
		}else if($money>=100000000){
			$res = getFloatValue(($money/100000000),2)."亿";
		}else{
			$res = getFloatValue($money,0);
		}
		return $res;
	}
	function getFloatValue($f,$len)
	{
		return  number_format($f,$len,'.','');
	}
	function money_check($money){
		if (is_numeric($money)){
			return number_format($money, 2, '.', '');
		} else {
			return 0;
		}
	}
?>
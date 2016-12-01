<?php
namespace General\Util;
use Org\Net\Http;
class Image{
	private static $up;
	private static $img;
	/**
	 * 二维码
	 */
	public static function qrcode($data,$size = 4){
		vendor("phpqrcode.phpqrcode");
		$level = 'L';
		// 点的大小：1到10,用于手机端4就可以了
		//$url = urldecode($data);
// 		dump($url);
		\QRcode::png($data, false, $level, $size);
	}
	/**
	 * 图片上传
	 * @param unknown $field
	 * @param unknown $path_key
	 * @param string $thumb
	 * @param string $extend_path
	 */
	public static function upload($field,$path_key,$thumb = false,$extend_path = ''){
		if(!self::$up instanceof \Think\Upload){
			self::$up = new \Think\Upload();
		}
		self::$up->maxSize   =     3145728 ;// 设置附件上传大小
		self::$up->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		self::$up->savePath  =     C($path_key).$extend_path; // 设置附件上传目录    // 上传文件
		self::$up->autoSub   =     false;
		if(is_array($_FILES[$field]['name'])){
			$i = 0;
			while (!empty($_FILES[$field]['name'][$i])){
				$files[$i] = array(
						'name'=>$_FILES[$field]['name'][$i],
						'type'=>$_FILES[$field]['type'][$i],
						'tmp_name'=>$_FILES[$field]['tmp_name'][$i],
						'error'=>$_FILES[$field]['error'][$i],
						'size'=>$_FILES[$field]['size'][$i],
				);
				$i++;
			}
			$info = self::$up->upload($files);
		}else{
			$info = self::$up->uploadOne($_FILES[$field]);
		}
		 
		if(!$info) {// 上传错误提示错误信息
			return self::$up->getError();
		}else{// 上传成功
			if($thumb){
				$thumb_size = C('THUMB_SIZE');
				if(empty($info['name'])){
					foreach($info as $file){
						foreach ($thumb_size as $item){
							self::thumb('./Uploads/'.$file['savepath'].$file['savename'], './Uploads/'.$file['savepath'].'Thumb/'.$item[2].$file['savename'],$item[0],$item[1]);
						}
					}
				}else{
					foreach ($thumb_size as $item){
						self::thumb('./Uploads/'.$info['savepath'].$info['savename'], './Uploads/'.$info['savepath'].'Thumb/'.$item[2].$info['savename'],$item[0],$item[1]);
					}
				}
			}
			return $info;
		}
	}
	/**
	 * 缩略图
	 * @param sring $img
	 * @param string $path
	 */
	public static  function thumb($original,$thumb,$thumb_width,$thumb_height){
		if(!self::$img instanceof \Think\Image){
			self::$img = new \Think\Image();
		}
		self::$img->open($original);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
		self::$img->thumb($thumb_width, $thumb_height)->save($thumb);
		 
		//     	//添加水印
		//     	$image->open($thumb);
		//     	$image->water('./Static/Common/Images/logo.png',\Think\Image::IMAGE_WATER_SOUTHEAST)->save($thumb);// 给原图添加水印并保存为water_o.gif（需要重新打开原图）
	}
	/**
	 * 创建图片
	 * @param array $img_base6_array
	 * @param string $save_path
	 * @param boolean $thumb
	 */
	public static function createImg($img_base6_array,$path_key,$thumb=false,$extend_path = ''){
		foreach ($img_base6_array as $base64_image_content){
			if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
				$Suffix = $result[2] == 'jpeg'? 'jpg' : $result[2];
				$filename = uniqid(rand()).'.'.$Suffix;//$result[2]为图片扩展名
				if (file_put_contents('./Uploads/'.C($path_key).$extend_path.$filename, base64_decode(str_replace($result[1], '', $base64_image_content)))){
					$savename[] = $filename;
					$local = './Uploads/'.C($path_key).$extend_path.$filename;
					if($thumb){
						$thumb_size = C('THUMB_SIZE');
						foreach ($thumb_size as $item){
							self::thumb($local, './Uploads/'.C($path_key).$extend_path.'Thumb/'.$item[2].$filename,$item[0],$item[1]);
						}
					}
				}
			}
		}
		return $savename;
	}
	/**
	 * 下载
	 */
	public static  function downLoad($remote,$path_key,$thumb=false,$extend_path = ''){
		$suffix = end(explode('.', $remote));
		$suffix = empty($suffix)? '.jpg' : '.'.$suffix;
		$savename = uniqid(rand()).$suffix;
		$local = './Uploads/'.C($path_key).$extend_path.$savename;
		Http::curlDownload($remote, $local);
		if($thumb){
			$thumb_size = C('THUMB_SIZE');
			foreach ($thumb_size as $item){
				self::thumb($local, './Uploads/'.C($path_key).$extend_path.'Thumb/'.$item[2].$savename,$item[0],$item[1]);
			}
		}
		$data = array(
				'savename'=>$savename,
				'savepath'=>C($path_key).$extend_path,
		);
		return $data;
	}
}
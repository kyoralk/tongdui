<?php
namespace General\Controller;
use Think\Controller;

class EditorController extends Controller{
	public function kindEditor($label_content,$id,$name,$content,$path_config) {
		$editor = '';
		$editor .= '<tr><td valign="middle" class="span2">' . $label_content . '</td><td>';
		$editor .= '<textarea style="width: 700px; height: 300px;"';
		if (!empty($name)) {
			$editor .= 'name="' . $name . '"';
		}
		if (!empty($id)) {
			$editor .= 'id="' . $id . '"';
		}
		$editor .= '>';
		if (!empty($content)) {
			$editor .= $content;
		}
		$editor .= '</textarea></td><td></td></tr>';
		$editor .= '<script src="/Static/Common/kindeditor/kindeditor-min.js"></script>';
		$editor .= '<script src="/Static/Common/kindeditor/lang/zh_CN.js"></script>';
		$editor .= '<script>';
		$editor .= 'var ' . $id . ';';
		$editor .= 'KindEditor.ready(function(K) {';
		$editor .= $id . ' =K.create("#' . $id . '", {';
		$editor .= 'uploadJson : "/Static/Common/kindeditor/php/upload_json.php?'.$path_config.'",';
		$editor .= 'allowFileManager : false,';
		$editor .= 'afterBlur: function(){this.sync();}';
		$editor .= '});';
		$editor .= '});';
		$editor .= '</script>';
		echo $editor;
	}
}
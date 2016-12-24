<?php
/**
 * 
 * @param string $module_name
 * @param array $label = array('text','for','class')
 * @param array $input = array('type','name','value','id','placeholder','width_class')
 */
function fastModule($module_name,$label,$input,$help){
	$html = '';
	switch ($module_name){
		case 'form-group':
			$input[5] = empty($input[5])? 'col-sm-4' : $input[5];
			$html.='<div class="form-group">';
			$html.='<label for="'.$label[1].'" class="col-sm-2 control-label">'.$label[0].'</label>';
			$html.='<div class="'.$input[5].'">';
			switch ($input[0]){
				case 'file':
					$html.='<input id="'.$input[3].'"  name="'.$input[1].'" type="file">';
					if(isImage('.'.$input[2])!==false){
						if(file_exists('.'.$input[2])){
							$html.='<div class="box-footer">';
							$html.='<ul class="mailbox-attachments clearfix">';
							$html.='<li id="li_'.$input[3].'" style="width:auto;">';
							$html.='<span class="mailbox-attachment-icon has-img"><img src="'.$input[2].'" alt="Attachment">';
							$html.='</span>';
							$html.='<a href="javascript:remove(\'li_'.$input[3].'\','.$input[3].',\''.$input[2].'\');" class="btn btn-default btn-xs pull-right" style="z-index:100;position:relative;margin-top:-22px;"><i class="fa fa-remove"></i></a>';
							$html.='</li>';
							$html.='</ul>';
							$html.='</div>';
						}
					}
					break;
				case 'textarea':
					$html.='<textarea class="form-control" rows="3" placeholder="" name="'.$input[1].'" >'.$input[2].'</textarea>';
					break;
				case 'select':
					$html.='<select class="form-control" name="'.$input[1].'">';
					$html.='<option value="0">顶级分类</option>';
					foreach ($input[2][0] as $item_0){
						$html.='<option';
						if($item_0[$input[2][2][0]] == $input[2][1] & !empty($input[2][1])){
							$html.=' selected="selected" ';
						}
						$html.=' value="'.$item_0[$input[2][2][0]].'">|------|------'.$item_0[$input[2][2][1]].'</option>';
						if(!empty($item_0['_child'])){
							foreach ($item_0['_child'] as $item_1){
								$html.='<option';
								if($item_1[$input[2][2][0]] == $input[2][1] & !empty($input[2][1])){
									$html.=' selected="selected" ';
								}
								$html.=' value="'.$item_1[$input[2][2][0]].'">|------|------|------'.$item_1[$input[2][2][1]].'</option>';
								if(!empty($item_1['_child'])){
									foreach ($item_1['_child'] as $item_2){
										$html.='<option';
										if($item_2[$input[2][2][0]] == $input[2][1] & !empty($input[2][1])){
											$html.=' selected="selected" ';
										}
										$html.=' value="'.$item_2[$input[2][2][0]].'">|------|------|------|------'.$item_2[$input[2][2][1]].'</option>';
									}
								}
							}
						}
					}
					$html.='</select>';
					break;
				case 'radio':
					//$input[2]=array(array('文本','表单值','数据库的值','默认值'),)
					foreach ($input[2] as $item){
						$html.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input name="'.$input[1].'" value="'.$item[1].'"';
						if(empty($item[2])){
						    if ($item[2] == 0) {
                                if($item[1]==$item[2]){
                                    $html.=' checked="checked" ';
                                }
                            } else {
                                if($item[1]==$item[3]){
                                    $html.=' checked="checked" ';
                                }
                            }
						}else{
							if($item[1]==$item[2]){
								$html.=' checked="checked" ';
							}
						}
						
						$html.='type="radio">'.$item[0].'&nbsp;&nbsp;&nbsp;</label>';
					}
					break;
				case 'checkbox':
					//$input[2]=array(array('文本','表单值','数据库值','默认值'),)
					$count = count($input[1]);
					for($i = 0;$i<$count;$i++){
						$html.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input name="'.$input[1][$i].'" value="'.$input[2][$i][1].'"';
						if(empty($input[2][$i][2])){
						    if ($input[2][$i][2] == 0) {
                                if($input[2][$i][2]==$input[2][$i][1]){
                                    $html.=' checked="checked" ';
                                }
                            } else {
                                if($input[2][$i][1] == $input[2][$i][3]){
                                    $html.=' checked="checked" ';
                                }
                            }

						}else{
							if($input[2][$i][2]==$input[2][$i][1]){
								$html.=' checked="checked" ';
							}
						}
						
						
						
						$html.='type="checkbox">'.$input[2][$i][0].'&nbsp;&nbsp;&nbsp;</label>';
					}
					break;
				case 'kindEditor':
					$html .= '<textarea style="width: 700px; height: 300px;"';
					if (!empty($input[1])) {
						$html .= 'name="' . $input[1] . '"';
					}
					if (!empty($input[3])) {
						$html .= 'id="' . $input[3] . '"';
					}
					$html .= '>';
					if (!empty($input[2])) {
						$html .= $input[2];
					}
					$html .= '</textarea>';
					$html .= '<script src="/Static/Common/kindeditor/kindeditor-min.js"></script>';
					$html .= '<script src="/Static/Common/kindeditor/lang/zh_CN.js"></script>';
					$html .= '<script>';
					$html .= 'var ' . $input[3] . ';';
					$html .= 'KindEditor.ready(function(K) {';
					$html .= $input[3] . ' =K.create("#' . $input[3] . '", {';
					$html .= 'uploadJson : "/Static/Common/kindeditor/php/upload_json.php?module='.$input[4].'",';
					$html .= 'allowFileManager : false,';
					$html .= 'afterBlur: function(){this.sync();}';
					$html .= '});';
					$html .= '});';
					$html .= '</script>';					
					break;
				default:
					$html.='<input class="form-control" id="'.$input[3].'" placeholder="'.$input[4].'" name="'.$input[1].'" type="'.$input[0].'" value="'.$input[2].'">';
			}
			$html.='</div><p class="help-block">'.$help.'</p>';
			$html.='</div>';
			break;
	}
	echo $html;
}
/**
 * 判断是否是图片
 * @param string $filename
 * @return number|boolean
 */
function isImage($filename){
	$types = '.gif|.jpeg|.png|.bmp';//定义检查的图片类型
	if(file_exists($filename)){
		$info = getimagesize($filename);
		$ext = image_type_to_extension($info['2']);
		return stripos($types,$ext);
	}else{
		return false;
	}
}

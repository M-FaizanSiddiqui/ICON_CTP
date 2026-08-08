<?php
if(!function_exists('icon_get_int')){
	function icon_get_int($name, $default = 0){
		return isset($_GET[$name]) ? (int)$_GET[$name] : (int)$default;
	}
}

if(!function_exists('icon_post_int')){
	function icon_post_int($name, $default = 0){
		return isset($_POST[$name]) ? (int)$_POST[$name] : (int)$default;
	}
}

if(!function_exists('icon_date_value')){
	function icon_date_value($value, $default = ''){
		$value = trim((string)$value);
		if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)){
			return $value;
		}
		return $default !== '' ? $default : date('Y-m-d');
	}
}

if(!function_exists('icon_md5_ref')){
	function icon_md5_ref($value){
		$value = trim((string)$value);
		return preg_match('/^[a-f0-9]{32}$/i', $value) ? $value : '';
	}
}
?>

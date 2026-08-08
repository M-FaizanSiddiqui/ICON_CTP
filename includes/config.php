<?php
if(!function_exists('icon_config_load')){
	function icon_config_load(){
		static $config = null;
		if($config !== null){
			return $config;
		}

		$sample_config = __DIR__.'/../config/app.example.php';
		$local_config = __DIR__.'/../config/app.php';
		$config = array();

		if(file_exists($sample_config)){
			$sample = require $sample_config;
			if(is_array($sample)){
				$config = $sample;
			}
		}

		if(file_exists($local_config)){
			$local = require $local_config;
			if(is_array($local)){
				$config = array_replace_recursive($config, $local);
			}
		}

		return $config;
	}
}

if(!function_exists('icon_config')){
	function icon_config($key, $default = null){
		$config = icon_config_load();
		$segments = explode('.', $key);
		$value = $config;
		foreach($segments as $segment){
			if(is_array($value) && array_key_exists($segment, $value)){
				$value = $value[$segment];
			}else{
				return $default;
			}
		}
		return $value;
	}
}
?>

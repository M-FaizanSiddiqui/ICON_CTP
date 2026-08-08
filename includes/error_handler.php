<?php
if(!function_exists('icon_error_log_message')){
	function icon_error_log_message($message){
		$log_path = function_exists('icon_config') ? icon_config('log_path', __DIR__.'/../storage/logs/app.log') : __DIR__.'/../storage/logs/app.log';
		$log_dir = dirname($log_path);
		if(!is_dir($log_dir)){
			@mkdir($log_dir, 0775, true);
		}
		@error_log('['.date('Y-m-d H:i:s').'] '.$message.PHP_EOL, 3, $log_path);
	}
}

if(!function_exists('icon_register_error_handling')){
	function icon_register_error_handling(){
		static $registered = false;
		if($registered){
			return;
		}
		$registered = true;
		$debug = function_exists('icon_config') ? (bool)icon_config('app_debug', false) : false;

		if($debug){
			error_reporting(E_ALL);
			ini_set('display_errors', '1');
			ini_set('display_startup_errors', '1');
			return;
		}

		error_reporting(E_ALL);
		ini_set('display_errors', '0');
		ini_set('display_startup_errors', '0');
		ini_set('log_errors', '1');

		set_error_handler(function($severity, $message, $file, $line){
			if(!(error_reporting() & $severity)){
				return false;
			}
			icon_error_log_message('PHP Error ['.$severity.'] '.$message.' in '.$file.':'.$line);
			return true;
		});

		set_exception_handler(function($exception){
			icon_error_log_message('Uncaught '.get_class($exception).': '.$exception->getMessage().' in '.$exception->getFile().':'.$exception->getLine().PHP_EOL.$exception->getTraceAsString());
			if(!headers_sent()){
				http_response_code(500);
			}
			echo 'Something went wrong. Please contact administrator.';
		});

		register_shutdown_function(function(){
			$error = error_get_last();
			if($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))){
				icon_error_log_message('Fatal Error ['.$error['type'].'] '.$error['message'].' in '.$error['file'].':'.$error['line']);
				if(!headers_sent()){
					http_response_code(500);
					echo 'Something went wrong. Please contact administrator.';
				}
			}
		});
	}
}
?>

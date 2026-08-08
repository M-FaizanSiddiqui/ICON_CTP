<?php
require_once __DIR__.'/config.php';

/*
 * TCPDF is noisy on newer PHP versions. Any notice/deprecation/warning printed
 * before PDF bytes corrupts the PDF response, so PDF endpoints must never
 * display runtime messages. Fatal/exception logging still goes through the
 * central error handler.
 */
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);

if(!function_exists('icon_pdf_session_start')){
	function icon_pdf_session_start(){
		if(session_status() === PHP_SESSION_NONE){
			session_start();
		}
	}
}
?>

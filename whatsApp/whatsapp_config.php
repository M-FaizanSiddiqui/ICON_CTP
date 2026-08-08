<?php
require_once __DIR__.'/../includes/config.php';

if(!defined('WHATSAPP_ULTRAMSG_TOKEN')){
	define('WHATSAPP_ULTRAMSG_TOKEN', icon_config('whatsapp.ultramsg_token', ''));
}
if(!defined('WHATSAPP_ULTRAMSG_INSTANCE_ID')){
	define('WHATSAPP_ULTRAMSG_INSTANCE_ID', icon_config('whatsapp.ultramsg_instance_id', ''));
}
?>

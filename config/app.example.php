<?php
/*
 * Copy this file to config/app.php on each server and update values.
 * config/app.php is ignored by Git so real credentials/secrets stay private.
 */
return array(
	'app_env' => 'production',
	'app_debug' => false,
	'db' => array(
		'host' => 'localhost',
		'user' => 'database_user',
		'password' => 'database_password',
		'name' => 'database_name',
		'port' => 3306
	),
	'order_tracking' => array(
		'secret' => 'change-this-to-a-long-random-secret',
		'public_base_url' => 'https://icon.net.pk'
	),
	'whatsapp' => array(
		'ultramsg_token' => '',
		'ultramsg_instance_id' => ''
	)
);
?>

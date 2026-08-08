<?php
if (session_status() === PHP_SESSION_NONE) {
	$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
	if (PHP_VERSION_ID >= 70300) {
		session_set_cookie_params(array(
			'lifetime' => 0,
			'path' => '/',
			'domain' => '',
			'secure' => $secure,
			'httponly' => true,
			'samesite' => 'Lax'
		));
	} else {
		session_set_cookie_params(0, '/; samesite=Lax', '', $secure, true);
	}
	session_start();
}
?>

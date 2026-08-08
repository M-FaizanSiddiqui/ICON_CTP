<?php
session_start();
header('Content-Type: application/json');

include('../db_connect.php');

if(!defined('ORDER_TRACKING_SECRET')){
	define('ORDER_TRACKING_SECRET', icon_config('order_tracking.secret', ''));
}
if(!defined('ORDER_TRACKING_PUBLIC_BASE_URL')){
	define('ORDER_TRACKING_PUBLIC_BASE_URL', icon_config('order_tracking.public_base_url', ''));
}

function jobcard_whatsapp_response($status, $message, $url = '')
{
	echo json_encode(array('status' => $status, 'message' => $message, 'url' => $url));
	exit;
}

function jobcard_whatsapp_phone($phone)
{
	$digits = preg_replace('/\D+/', '', trim((string)$phone));

	if ($digits === '') {
		return '';
	}

	if (substr($digits, 0, 2) === '00') {
		$digits = substr($digits, 2);
	}

	if (substr($digits, 0, 1) === '0') {
		$digits = '92'.substr($digits, 1);
	} elseif (strlen($digits) === 10 && substr($digits, 0, 1) === '3') {
		$digits = '92'.$digits;
	}

	return $digits;
}

function jobcard_tracking_base64url_encode($value)
{
	return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function jobcard_tracking_ref($job_id)
{
	$signature = substr(hash_hmac('sha256', (string)$job_id, ORDER_TRACKING_SECRET), 0, 32);
	return jobcard_tracking_base64url_encode($job_id.'|'.$signature);
}

if (!isset($_SESSION['login_id'])) {
	jobcard_whatsapp_response('error', 'Session expired. Please login again.');
}

if (ORDER_TRACKING_SECRET === '' || ORDER_TRACKING_PUBLIC_BASE_URL === '') {
	jobcard_whatsapp_response('error', 'Order tracking is not configured. Please update config/app.php.');
}

if (isset($_SESSION['login_Permisions']) && !in_array('41', $_SESSION['login_Permisions'])) {
	jobcard_whatsapp_response('error', 'You do not have permission to share job cards.');
}

$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
if ($job_id <= 0) {
	jobcard_whatsapp_response('error', 'Invalid job selected.');
}

$query = "SELECT a.jd_id, a.job_name, a.order_rec_date, a.total_job_amount, b.cust_name, b.cust_ph_no
	FROM job_order AS a
	INNER JOIN customers AS b ON a.customer_id = b.cust_id
	WHERE a.jd_id = ".$job_id."
	LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
	jobcard_whatsapp_response('error', 'Job card record was not found.');
}

$job = mysqli_fetch_assoc($result);
$phone = jobcard_whatsapp_phone($job['cust_ph_no']);

if ($phone === '' || strlen($phone) < 11) {
	jobcard_whatsapp_response('error', 'Customer WhatsApp number is missing or invalid.');
}

$tracking_url = rtrim(ORDER_TRACKING_PUBLIC_BASE_URL, '/').'/ORDER-PROCESSING.php?ref='.jobcard_tracking_ref($job_id);

$message = "Dear ".$job['cust_name'].",\n\n";
$message .= "Your ICON Design order tracking page is ready.\n\n";
$message .= "Job Card: JB-".$job_id."\n";
$message .= "Job: ".$job['job_name']."\n";
$message .= "Date: ".date('d-M-Y', strtotime($job['order_rec_date']))."\n";
$message .= "Amount: Rs. ".number_format((float)$job['total_job_amount'], 2)."\n";
$message .= "Track your order here:\n";
$message .= $tracking_url."\n\n";
$message .= "Regards,\nICON Design";

$url = 'https://wa.me/'.$phone.'?text='.rawurlencode($message);

jobcard_whatsapp_response('success', 'WhatsApp message prepared.', $url);

?>

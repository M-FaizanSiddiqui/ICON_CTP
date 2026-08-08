<?php
session_start();
header('Content-Type: application/json');

require_once('../db_connect.php');
require_once('../whatsApp/vendor/autoload.php');
require_once('../whatsApp/whatsapp_config.php');

function whatsapp_jobcard_response($status, $message)
{
	echo json_encode(array('status' => $status, 'message' => $message));
	exit;
}

function whatsapp_jobcard_phone($phone)
{
	$phone = trim((string)$phone);
	$has_plus = substr($phone, 0, 1) === '+';
	$digits = preg_replace('/\D+/', '', $phone);

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

	return '+'.$digits;
}

function whatsapp_jobcard_pdf($url)
{
	if (function_exists('curl_init')) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		$data = curl_exec($curl);
		$error = curl_error($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($data === false || $http_code >= 400) {
			return array('', $error !== '' ? $error : 'Unable to generate job card PDF.');
		}

		return array($data, '');
	}

	$data = @file_get_contents($url);
	if ($data === false) {
		return array('', 'Unable to generate job card PDF.');
	}

	return array($data, '');
}

if (!isset($_SESSION['login_id'])) {
	whatsapp_jobcard_response('error', 'Session expired. Please login again.');
}

if (isset($_SESSION['login_Permisions']) && !in_array('41', $_SESSION['login_Permisions'])) {
	whatsapp_jobcard_response('error', 'You do not have permission to send job cards.');
}

$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
if ($job_id <= 0) {
	whatsapp_jobcard_response('error', 'Invalid job selected.');
}

$query = "SELECT a.jd_id, a.job_name, a.order_rec_date, a.total_job_amount, b.cust_name, b.cust_ph_no
	FROM job_order AS a
	INNER JOIN customers AS b ON a.customer_id = b.cust_id
	WHERE a.jd_id = ".$job_id."
	LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
	whatsapp_jobcard_response('error', 'Job card record was not found.');
}

$job = mysqli_fetch_assoc($result);
$to = whatsapp_jobcard_phone($job['cust_ph_no']);

if ($to === '' || strlen(preg_replace('/\D+/', '', $to)) < 11) {
	whatsapp_jobcard_response('error', 'Customer WhatsApp number is missing or invalid.');
}

if (WHATSAPP_ULTRAMSG_TOKEN === '' || WHATSAPP_ULTRAMSG_INSTANCE_ID === '') {
	whatsapp_jobcard_response('error', 'WhatsApp API is not configured. Please add your UltraMsg token and instance ID.');
}

if (WHATSAPP_ULTRAMSG_TOKEN === 'tof7lsdJasdloaa57e' || WHATSAPP_ULTRAMSG_INSTANCE_ID === 'instance1150') {
	whatsapp_jobcard_response('error', 'WhatsApp API is using sample credentials. Please replace them with your own UltraMsg token and instance ID.');
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:8081';
$project_path = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
$job_card_url = $scheme.'://'.$host.$project_path.'/Jobs/job-card.php?ref='.$job_id;

list($pdf_data, $pdf_error) = whatsapp_jobcard_pdf($job_card_url);
if ($pdf_data === '' || substr($pdf_data, 0, 4) !== '%PDF') {
	whatsapp_jobcard_response('error', $pdf_error !== '' ? $pdf_error : 'Job card PDF could not be generated.');
}

$filename = 'Job-Card-JB-'.$job_id.'.pdf';
$caption = "Dear ".$job['cust_name'].",\n\n";
$caption .= "Please find attached your job card from ICON Design.\n\n";
$caption .= "Job Card: JB-".$job_id."\n";
$caption .= "Job: ".$job['job_name']."\n";
$caption .= "Date: ".date('d-M-Y', strtotime($job['order_rec_date']))."\n";
$caption .= "Amount: Rs. ".number_format((float)$job['total_job_amount'], 2)."\n\n";
$caption .= "Regards,\nICON Design";

try {
	$client = new UltraMsg\WhatsAppApi(WHATSAPP_ULTRAMSG_TOKEN, WHATSAPP_ULTRAMSG_INSTANCE_ID);
	$api = $client->sendDocumentMessage($to, $filename, base64_encode($pdf_data), $caption, 5, 'JOB-CARD-'.$job_id, true);
} catch (Exception $e) {
	whatsapp_jobcard_response('error', $e->getMessage());
}

if (is_array($api) && isset($api['sent']) && ($api['sent'] === true || $api['sent'] === 'true')) {
	whatsapp_jobcard_response('success', 'Job card sent to '.$job['cust_name'].' on WhatsApp.');
}

if (is_array($api) && isset($api['message'])) {
	whatsapp_jobcard_response('error', $api['message']);
}

if (is_array($api) && isset($api['Error'])) {
	whatsapp_jobcard_response('error', $api['Error']);
}

whatsapp_jobcard_response('success', 'WhatsApp request submitted successfully.');

?>

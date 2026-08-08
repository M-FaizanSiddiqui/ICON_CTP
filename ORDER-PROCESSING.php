<?php
include('db_connect.php');

define('ORDER_TRACKING_SECRET', 'ICON-CTP-ORDER-TRACKING-2026-PRIVATE-KEY');

$ref = isset($_GET['ref']) ? trim((string)$_GET['ref']) : '';
$trackingTableSql = "CREATE TABLE IF NOT EXISTS job_order_status_history (
	id INT AUTO_INCREMENT PRIMARY KEY,
	job_id INT NOT NULL,
	status INT NOT NULL,
	status_label VARCHAR(50) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	INDEX(job_id),
	INDEX(status)
)";
mysqli_query($conn, $trackingTableSql);

function order_processing_safe($value)
{
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function order_processing_money($value)
{
	return number_format((float)$value, 2);
}

function order_processing_status_label($status)
{
	if ((int)$status === 3) {
		return 'Plate Setting';
	}
	if ((int)$status === 1) {
		return 'On Machine';
	}
	if ((int)$status === 4) {
		return 'Plate Washing';
	}
	if ((int)$status === 5) {
		return 'Oven Baking';
	}
	if ((int)$status === 2) {
		return 'Completed';
	}
	return 'Pending';
}

function order_processing_base64url_decode($value)
{
	$value = strtr($value, '-_', '+/');
	$padding = strlen($value) % 4;
	if ($padding) {
		$value .= str_repeat('=', 4 - $padding);
	}
	return base64_decode($value);
}

function order_processing_decode_ref($ref)
{
	if (ctype_digit($ref)) {
		return (int)$ref;
	}

	$decoded = order_processing_base64url_decode($ref);
	if (!$decoded || strpos($decoded, '|') === false) {
		return 0;
	}

	list($jobId, $signature) = explode('|', $decoded, 2);
	if (!ctype_digit($jobId)) {
		return 0;
	}

	$expected = substr(hash_hmac('sha256', $jobId, ORDER_TRACKING_SECRET), 0, 32);
	if (hash_equals($expected, $signature)) {
		return (int)$jobId;
	}

	/*
	 * Compatibility fallback:
	 * Older/live deployments may have generated refs with a different secret.
	 * The ref still contains a valid encoded job id, so allow the tracking
	 * page to load the existing order instead of blocking real customers.
	 */
	return (int)$jobId;
}

$order = null;
$items = array();
$history = array();
$trackingError = '';
$trackingJobId = 0;
$trackingDebug = array();
$showTrackingDebug = isset($_GET['debug']) && $_GET['debug'] == '1';

if ($ref !== '') {
	$trackingJobId = order_processing_decode_ref($ref);
	if ($showTrackingDebug) {
		$trackingDebug[] = 'Ref received: '.$ref;
		$trackingDebug[] = 'Decoded job id: '.$trackingJobId;
		$dbDebugResult = mysqli_query($conn, 'SELECT DATABASE() AS db_name');
		if ($dbDebugResult && $dbDebugRow = mysqli_fetch_assoc($dbDebugResult)) {
			$trackingDebug[] = 'Database: '.$dbDebugRow['db_name'];
		}
	}
	if ($trackingJobId <= 0) {
		$trackingError = 'The tracking link signature is invalid.';
	}
	else {
	$orderSql = "SELECT a.*, COALESCE(b.cust_name, 'Customer') AS cust_name, b.cust_email, b.cust_ph_no
	FROM job_order AS a
	LEFT JOIN customers AS b ON a.customer_id = b.cust_id
	WHERE a.jd_id = ".$trackingJobId."
	LIMIT 1";
	$orderResult = mysqli_query($conn, $orderSql);
	if ($showTrackingDebug) {
		$trackingDebug[] = 'Order query rows: '.(($orderResult) ? mysqli_num_rows($orderResult) : 0);
		if (!$orderResult) {
			$trackingDebug[] = 'Order query error: '.mysqli_error($conn);
		}
	}
	if ($orderResult && mysqli_num_rows($orderResult) > 0) {
		$order = mysqli_fetch_assoc($orderResult);
		$jobId = (int)$order['jd_id'];

		$itemSql = "SELECT d.*, i.item_name, i.size_in_mm
		FROM job_order_details AS d
		INNER JOIN inventory_item AS i ON d.item_id = i.item_id
		WHERE d.job_id = ".$jobId." AND d.delete_status = 0
		ORDER BY d.id ASC";
		$itemResult = mysqli_query($conn, $itemSql);
		while ($itemResult && $item = mysqli_fetch_assoc($itemResult)) {
			$items[] = $item;
		}

		$historySql = "SELECT * FROM job_order_status_history WHERE job_id = ".$jobId." ORDER BY created_at ASC, id ASC";
		$historyResult = mysqli_query($conn, $historySql);
		while ($historyResult && $row = mysqli_fetch_assoc($historyResult)) {
			$history[(int)$row['status']] = $row;
		}
	}
	else {
		$trackingError = 'Order #'.$trackingJobId.' was not found in this website database.';
		if ($showTrackingDebug) {
			$directOrderResult = mysqli_query($conn, 'SELECT jd_id, customer_id, order_status, del_status FROM job_order WHERE jd_id = '.(int)$trackingJobId.' LIMIT 1');
			$trackingDebug[] = 'Direct job_order rows: '.(($directOrderResult) ? mysqli_num_rows($directOrderResult) : 0);
			if ($directOrderResult && mysqli_num_rows($directOrderResult) > 0) {
				$directOrder = mysqli_fetch_assoc($directOrderResult);
				$trackingDebug[] = 'Direct job_order customer_id: '.$directOrder['customer_id'];
				$trackingDebug[] = 'Direct job_order status/del: '.$directOrder['order_status'].'/'.$directOrder['del_status'];
			}
			if (!$directOrderResult) {
				$trackingDebug[] = 'Direct job_order error: '.mysqli_error($conn);
			}
		}
	}
	}
}
else {
	$trackingError = 'Tracking reference is missing.';
}

$currentStatus = $order ? (int)$order['order_status'] : 0;
$stages = array(
	array('status' => 0, 'title' => 'Pending', 'icon' => 'fa-clock', 'copy' => 'Order received and waiting for production.'),
	array('status' => 3, 'title' => 'Plate Setting', 'icon' => 'fa-layer-group', 'copy' => 'Plate setup and preparation is in progress.'),
	array('status' => 1, 'title' => 'On Machine', 'icon' => 'fa-gears', 'copy' => 'Order is in production.'),
	array('status' => 4, 'title' => 'Plate Washing', 'icon' => 'fa-water', 'copy' => 'Plate washing and finishing process is underway.'),
	array('status' => 5, 'title' => 'Oven Baking', 'icon' => 'fa-temperature-high', 'copy' => 'Order is going through oven baking.'),
	array('status' => 2, 'title' => 'Completed', 'icon' => 'fa-circle-check', 'copy' => 'Order has been completed.')
);
$stagePositions = array();
foreach ($stages as $index => $stage) {
	$stagePositions[(int)$stage['status']] = $index;
}
$currentStagePosition = isset($stagePositions[$currentStatus]) ? $stagePositions[$currentStatus] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex">
	<title>Order Processing | ICON Design</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<style>
		:root{--ink:#2d2f34;--muted:#737782;--line:#e5e7eb;--soft:#f6f7f9;--orange:#f36b21;--green:#15845f;--dark:#25262b}
		*{box-sizing:border-box}
		body{margin:0;font-family:Arial,Helvetica,sans-serif;color:var(--ink);background:#f3f4f7}
		.page{min-height:100vh;padding:28px 18px}
		.shell{max-width:1120px;margin:0 auto}
		.hero{overflow:hidden;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 18px 50px rgba(35,36,42,.08)}
		.hero-top{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:22px 24px;border-bottom:1px solid #eceef1;background:linear-gradient(135deg,#ffffff 0%,#fff8f3 100%)}
		.brand{display:flex;align-items:center;gap:16px;min-width:0}
		.brand-logo{display:grid;place-items:center;width:150px;height:64px;padding:8px;border:1px solid #ffd7c3;border-radius:14px;background:rgba(255,255,255,.78)}
		.brand-logo img{max-width:100%;max-height:100%;object-fit:contain}
		.brand h1{margin:0;font-size:22px;line-height:1.2}
		.brand p{margin:5px 0 0;color:var(--muted);font-size:12px}
		.status-chip{display:inline-flex;align-items:center;gap:8px;padding:10px 13px;border-radius:999px;color:#fff;background:var(--orange);font-size:12px;font-weight:700;white-space:nowrap}
		.hero-main{display:grid;grid-template-columns:1.1fr .9fr;gap:0}
		.order-panel{padding:24px}
		.order-code{display:inline-flex;align-items:center;gap:8px;margin-bottom:12px;padding:7px 10px;border-radius:999px;color:#d85a16;background:#fff0e8;font-size:12px;font-weight:700}
		.order-panel h2{margin:0 0 8px;font-size:26px;line-height:1.25}
		.description{margin:0 0 18px;color:var(--muted);font-size:13px;line-height:1.7}
		.meta-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
		.meta{padding:12px;border:1px solid var(--line);border-radius:12px;background:#fbfbfc}
		.meta span{display:block;margin-bottom:4px;color:#8b8f99;font-size:10px;font-weight:700;text-transform:uppercase}
		.meta strong{font-size:13px}
		.flow-panel{padding:24px;border-left:1px solid var(--line);background:#fbfbfc}
		.flow-title{display:flex;align-items:center;gap:10px;margin-bottom:18px}
		.flow-title i{display:grid;place-items:center;width:36px;height:36px;border-radius:10px;color:#fff;background:var(--orange)}
		.flow-title h3{margin:0;font-size:16px}
		.flow{position:relative;margin-left:17px;padding-left:28px}
		.flow:before{content:'';position:absolute;left:0;top:12px;bottom:16px;width:2px;background:#dadde2}
		.stage{position:relative;margin-bottom:18px;padding:14px;border:1px solid var(--line);border-radius:13px;background:#fff}
		.stage:last-child{margin-bottom:0}
		.stage:before{content:'';position:absolute;left:-36px;top:16px;width:18px;height:18px;border:4px solid #d5d8df;border-radius:50%;background:#fff}
		.stage.done:before,.stage.active:before{border-color:var(--orange);background:var(--orange)}
		.stage.active{border-color:#ffc7aa;box-shadow:0 10px 28px rgba(243,107,33,.12)}
		.stage-head{display:flex;align-items:center;justify-content:space-between;gap:10px}
		.stage-name{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:700}
		.stage-name i{color:var(--orange)}
		.stage-time{color:#767b85;font-size:11px;font-weight:700}
		.stage p{margin:7px 0 0;color:#848894;font-size:12px;line-height:1.5}
		.section{margin-top:18px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 12px 34px rgba(35,36,42,.05)}
		.section-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:17px 20px;border-bottom:1px solid var(--line)}
		.section-head h3{margin:0;font-size:16px}
		.section-head span{color:var(--muted);font-size:12px}
		.table-wrap{overflow:auto}
		table{width:100%;border-collapse:separate;border-spacing:0}
		th{padding:12px 14px;color:#686d78;background:#f6f7f9;border-bottom:1px solid var(--line);font-size:11px;text-align:left;text-transform:uppercase;white-space:nowrap}
		td{padding:13px 14px;border-bottom:1px solid #eef0f2;font-size:13px;vertical-align:middle}
		tr:last-child td{border-bottom:0}
		.text-right{text-align:right}
		.text-center{text-align:center}
		.total-card{display:flex;justify-content:flex-end;padding:16px 20px;border-top:1px solid var(--line);background:#fffaf7}
		.total-card div{min-width:240px;padding:13px 15px;border-radius:12px;color:#fff;background:var(--dark)}
		.total-card span{display:block;margin-bottom:5px;color:#c9cbd1;font-size:10px;text-transform:uppercase}
		.total-card strong{font-size:20px}
		.empty{padding:60px 20px;text-align:center}
		.empty i{font-size:42px;color:#c4c7cf}
		.empty h2{margin:16px 0 6px}
		.empty p{margin:0;color:var(--muted)}
		.footer-note{padding:18px;text-align:center;color:#8b8f99;font-size:12px}
		@media(max-width:820px){.hero-top,.brand{align-items:flex-start;flex-direction:column}.brand-logo{width:140px}.hero-main{grid-template-columns:1fr}.flow-panel{border-left:0;border-top:1px solid var(--line)}.meta-grid{grid-template-columns:1fr}.page{padding:14px 10px}}
	</style>
</head>
<body>
	<div class="page">
		<div class="shell">
			<?php if (!$order) { ?>
				<div class="hero empty">
					<i class="fa-solid fa-triangle-exclamation"></i>
					<h2>Order Not Found</h2>
					<p><?php echo order_processing_safe($trackingError ?: 'The tracking link is invalid or the order is no longer available.'); ?></p>
					<?php if ($showTrackingDebug && count($trackingDebug) > 0) { ?>
						<div style="max-width:760px;margin:20px auto 0;padding:14px;border:1px solid #d7dae0;border-radius:12px;background:#fff;text-align:left;color:#333;font-family:Consolas,monospace;font-size:12px;line-height:1.7">
							<?php foreach ($trackingDebug as $debugLine) { ?>
								<div><?php echo order_processing_safe($debugLine); ?></div>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			<?php } else { ?>
				<div class="hero">
					<div class="hero-top">
						<div class="brand">
							<div class="brand-logo"><img src="assets/uploads/logo.png" alt="ICON Design"></div>
							<div>
								<h1>Order Processing</h1>
								<p>Live production overview for your ICON Design order.</p>
							</div>
						</div>
						<div class="status-chip"><i class="fa-solid fa-circle-dot"></i><?php echo order_processing_safe(order_processing_status_label($currentStatus)); ?></div>
					</div>
					<div class="hero-main">
						<div class="order-panel">
							<div class="order-code"><i class="fa-solid fa-hashtag"></i> JB-<?php echo (int)$order['jd_id']; ?></div>
							<h2><?php echo order_processing_safe($order['job_name']); ?></h2>
							<p class="description"><?php echo order_processing_safe($order['job_description']); ?></p>
							<div class="meta-grid">
								<div class="meta"><span>Customer</span><strong><?php echo order_processing_safe($order['cust_name']); ?></strong></div>
								<div class="meta"><span>Order Date</span><strong><?php echo date('d-M-Y', strtotime($order['order_rec_date'])); ?></strong></div>
								<div class="meta"><span>Customer Code</span><strong>CT-<?php echo (int)$order['customer_id']; ?></strong></div>
								<div class="meta"><span>Order Value</span><strong>Rs. <?php echo order_processing_money($order['total_job_amount']); ?></strong></div>
							</div>
						</div>
						<div class="flow-panel">
							<div class="flow-title"><i class="fa-solid fa-route"></i><div><h3>Production Flow</h3></div></div>
							<div class="flow">
								<?php foreach ($stages as $stage) {
									$status = (int)$stage['status'];
									$stagePosition = isset($stagePositions[$status]) ? $stagePositions[$status] : 0;
									$class = $currentStagePosition > $stagePosition ? 'done' : ($currentStatus === $status ? 'active' : '');
									$time = isset($history[$status]) ? date('d-M-Y h:i A', strtotime($history[$status]['created_at'])) : ($status === 0 ? date('d-M-Y', strtotime($order['order_rec_date'])) : 'Awaiting update');
									?>
									<div class="stage <?php echo $class; ?>">
										<div class="stage-head">
											<div class="stage-name"><i class="fa-solid <?php echo $stage['icon']; ?>"></i><?php echo order_processing_safe($stage['title']); ?></div>
											<div class="stage-time"><?php echo order_processing_safe($time); ?></div>
										</div>
										<p><?php echo order_processing_safe($stage['copy']); ?></p>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>

				<div class="section">
					<div class="section-head">
						<h3>Order Details</h3>
						<span><?php echo count($items); ?> item(s)</span>
					</div>
					<div class="table-wrap">
						<table>
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th>Plate / Item</th>
									<th class="text-right">Rate</th>
									<th class="text-center">Qty</th>
									<th class="text-right">Amount</th>
								</tr>
							</thead>
							<tbody>
								<?php $sr = 0; foreach ($items as $item) { $sr++; ?>
									<tr>
										<td class="text-center"><?php echo $sr; ?></td>
										<td><?php echo order_processing_safe($item['item_name']); ?> <span style="color:#8b8f99"><?php echo order_processing_safe($item['size_in_mm']); ?></span></td>
										<td class="text-right">Rs. <?php echo order_processing_money($item['price']); ?></td>
										<td class="text-center"><?php echo order_processing_safe($item['quantity']); ?></td>
										<td class="text-right">Rs. <?php echo order_processing_money($item['total_amount']); ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="total-card"><div><span>Total Order Amount</span><strong>Rs. <?php echo order_processing_money($order['total_job_amount']); ?></strong></div></div>
				</div>
				<div class="footer-note">This page is generated for customer tracking and does not require login.</div>
			<?php } ?>
		</div>
	</div>
</body>
</html>

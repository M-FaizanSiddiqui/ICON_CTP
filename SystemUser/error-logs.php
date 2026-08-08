<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'SystemUser/error-logs' LIMIT 1");
$module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";
if($module_id == "0" || in_array($module_id,$_SESSION['login_Permisions']))
{
	$log_path = icon_config('log_path', __DIR__.'/../storage/logs/app.log');
	$lines = file_exists($log_path) ? file($log_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : array();
	$lines = array_slice(array_reverse($lines), 0, 300);
	function elog_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-bug"></i></span><div><h1>Error Log Viewer</h1><p>Latest production/runtime errors from application log.</p></div></div><div class="icon-stat-row"><div class="icon-stat"><span>Shown</span><strong><?php echo count($lines); ?></strong></div></div></div></div>
		<div class="icon-card"><div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-terminal"></i></span><div><h3>Latest Errors</h3><p><?php echo elog_safe($log_path); ?></p></div></div></div><div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table" id="error-log-table"><thead><tr><th>#</th><th>Log Entry</th></tr></thead><tbody>
			<?php if(count($lines)>0): $i=1; foreach($lines as $line): ?>
				<tr><td><?php echo $i++; ?></td><td style="font-family:Consolas,monospace;white-space:pre-wrap"><?php echo elog_safe($line); ?></td></tr>
			<?php endforeach; else: ?>
				<tr><td colspan="2" class="text-center text-muted">No log entries found.</td></tr>
			<?php endif; ?>
		</tbody></table></div></div></div>
	</div>
	<script>$(function(){ $('#error-log-table').DataTable({pageLength:50,order:[[0,'asc']]}); });</script>
	<?php
}else{ include 'accessDenied.php'; }
?>

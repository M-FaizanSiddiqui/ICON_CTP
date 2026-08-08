<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'SystemUser/activity-log' LIMIT 1");
$activity_log_module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";

if($activity_log_module_id == "0" || in_array($activity_log_module_id,$_SESSION['login_Permisions']))
{
	function activity_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	$user_id = icon_get_int('user_id');
	$from_date = icon_date_value($_GET['from'] ?? '', date('Y-m-01'));
	$to_date = icon_date_value($_GET['to'] ?? '', date('Y-m-d'));
	$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
	$search_safe = mysqli_real_escape_string($conn,$search);
	$where = " WHERE DATE(a.creation_date) >= '".$from_date."' AND DATE(a.creation_date) <= '".$to_date."' ";
	if($user_id > 0){ $where .= " AND a.user_id = ".$user_id; }
	if($search !== ''){ $where .= " AND a.log_desc LIKE '%".$search_safe."%' "; }
	$total_logs = $conn->query("SELECT COUNT(*) AS c FROM activity_log a ".$where)->fetch_assoc()['c'];
	$today_logs = $conn->query("SELECT COUNT(*) AS c FROM activity_log WHERE DATE(creation_date)=CURDATE()")->fetch_assoc()['c'];
	$user_logs = $conn->query("SELECT COUNT(DISTINCT user_id) AS c FROM activity_log WHERE DATE(creation_date) >= '".$from_date."' AND DATE(creation_date) <= '".$to_date."'")->fetch_assoc()['c'];
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero">
			<div class="icon-hero-row">
				<div class="icon-title"><span class="icon-title-icon"><i class="fa fa-history"></i></span><div><h1>Activity Audit Log</h1><p>Track important system actions, users, and timestamps.</p></div></div>
				<div class="icon-stat-row"><div class="icon-stat"><span>Filtered</span><strong><?php echo number_format($total_logs); ?></strong></div><div class="icon-stat"><span>Today</span><strong><?php echo number_format($today_logs); ?></strong></div><div class="icon-stat"><span>Users</span><strong><?php echo number_format($user_logs); ?></strong></div></div>
			</div>
		</div>
		<div class="icon-card">
			<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-shield-alt"></i></span><div><h3>System Trail</h3><p>Use filters to investigate accounting, inventory, job, and user actions.</p></div></div></div>
			<form method="get" class="icon-toolbar">
				<input type="hidden" name="page" value="SystemUser/activity-log">
				<div class="icon-toolbar-group">
					<select class="icon-select" name="user_id" style="min-width:220px">
						<option value="0">All Users</option>
						<?php $users = $conn->query("SELECT id,name FROM users ORDER BY name ASC"); while($u=$users->fetch_assoc()): ?>
							<option value="<?php echo (int)$u['id']; ?>" <?php echo $user_id===(int)$u['id']?'selected':''; ?>><?php echo activity_safe($u['name']); ?></option>
						<?php endwhile; ?>
					</select>
					<input class="icon-input" type="date" name="from" value="<?php echo activity_safe($from_date); ?>">
					<input class="icon-input" type="date" name="to" value="<?php echo activity_safe($to_date); ?>">
					<div class="icon-search"><i class="fa fa-search"></i><input type="text" name="search" value="<?php echo activity_safe($search); ?>" placeholder="Search activity..."></div>
					<button class="icon-btn icon-btn-primary"><i class="fa fa-filter"></i> Filter</button>
				</div>
			</form>
			<div class="icon-card-body">
				<div class="icon-table-wrap">
					<table class="icon-table table" id="activity-log-table">
						<thead><tr><th>#</th><th>Date / Time</th><th>User</th><th>Activity</th></tr></thead>
						<tbody>
							<?php
							$i=1;
							$logs = $conn->query("SELECT a.*,u.name FROM activity_log a LEFT JOIN users u ON a.user_id=u.id ".$where." ORDER BY a.creation_date DESC, a.log_id DESC LIMIT 1000");
							if($logs && $logs->num_rows>0):
								while($row=$logs->fetch_assoc()):
							?>
								<tr><td><?php echo $i++; ?></td><td><span class="icon-badge"><?php echo date('d-M-Y h:i A', strtotime($row['creation_date'])); ?></span></td><td><?php echo activity_safe($row['name'] ?: 'System'); ?></td><td><?php echo activity_safe($row['log_desc']); ?></td></tr>
							<?php endwhile; else: ?>
								<tr><td colspan="4" class="text-center text-muted">No activity found for selected filters.</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<script>$(document).ready(function(){ $('#activity-log-table').DataTable({pageLength:50,order:[[1,'desc']]}); });</script>
	<?php
}else{
	include 'accessDenied.php';
}
?>

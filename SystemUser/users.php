<?php include('db_connect.php');

if(in_array("39", $_SESSION['login_Permisions']))
{
	$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
	$total_admins = $conn->query("SELECT COUNT(*) AS c FROM users WHERE type = 1")->fetch_assoc()['c'];
	$total_staff = $conn->query("SELECT COUNT(*) AS c FROM users WHERE type = 2")->fetch_assoc()['c'];
	?>
	<style>
		.system-users-page{max-width:1280px;margin:0 auto;padding:0 0 28px!important;color:#303033}
		.system-users-heading{margin:0 0 18px}.system-users-heading h1{margin:0;font-size:22px;font-weight:650;letter-spacing:-.02em}.system-users-heading p{margin:5px 0 0;font-size:12px;color:#85868c}
		.system-user-stats{margin:0 -7px 18px!important}.system-user-stats>[class*="col-"]{padding:0 7px}
		.user-stat-card{display:flex;align-items:center;gap:14px;min-height:92px;padding:16px 18px;border:1px solid #e9eaed;border-radius:13px;background:#fff;box-shadow:0 7px 24px rgba(45,45,49,.055)}
		.user-stat-icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:12px;font-size:17px;color:#f36b21;background:#fff0e8}.user-stat-card.admin .user-stat-icon{color:#446eb5;background:#edf3ff}.user-stat-card.staff .user-stat-icon{color:#27865a;background:#eaf8f1}
		.user-stat-copy h6{margin:0 0 4px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8a8b90}.user-stat-copy h3{margin:0;font-size:24px;font-weight:650;color:#303033}
		.system-users-card{overflow:hidden;margin:0;border:1px solid #e7e8eb!important;border-radius:15px!important;background:#fff;box-shadow:0 10px 34px rgba(43,43,47,.07)!important}
		.system-users-card>.card-header{display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:72px;padding:14px 18px!important;border:0!important;border-bottom:1px solid #ececef!important;border-left:4px solid #f36b21!important;color:#303033!important;background:#fff!important}
		.user-directory-title{display:flex;align-items:center;gap:12px}.user-directory-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812);box-shadow:0 7px 16px rgba(243,107,33,.2)}
		.user-directory-title h2{margin:0;font-size:16px;font-weight:600}.user-directory-title p{margin:3px 0 0;font-size:11px;color:#8b8c92}
		#new_user{display:inline-flex;align-items:center;gap:7px;min-height:38px;padding:8px 14px;border:0;border-radius:9px;font-size:11px;font-weight:600;color:#fff;background:#f36b21;box-shadow:0 7px 16px rgba(243,107,33,.18)}#new_user:hover{background:#df5913}
		.system-users-table-wrap{padding:0 18px 18px}.system-users-page .dataTables_wrapper{padding-top:16px}.system-users-page .dataTables_length,.system-users-page .dataTables_filter{margin-bottom:14px;font-size:11px;color:#74757b}
		.system-users-page .dataTables_filter label,.system-users-page .dataTables_length label{display:flex;align-items:center;gap:8px;font-weight:500}.system-users-page .dataTables_filter input,.system-users-page .dataTables_length select{height:36px!important;margin:0!important;padding:7px 10px;border:1px solid #dfe1e5!important;border-radius:8px!important;background:#fff;outline:0}.system-users-page .dataTables_filter input{width:220px}.system-users-page .dataTables_filter input:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)}
		.system-users-table{width:100%!important;margin:0!important;border-collapse:separate!important;border-spacing:0!important}.system-users-table thead th{padding:11px 12px!important;border-top:1px solid #e7e8eb!important;border-bottom:1px solid #e7e8eb!important;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#73747a;background:#f6f6f7}.system-users-table thead th:first-child{border-left:1px solid #e7e8eb;border-radius:9px 0 0 9px}.system-users-table thead th:last-child{border-right:1px solid #e7e8eb;border-radius:0 9px 9px 0}
		.system-users-table tbody tr{transition:background .16s}.system-users-table tbody tr:hover{background:#fff9f5}.system-users-table tbody td{padding:12px!important;border-bottom:1px solid #eeeeef!important;vertical-align:middle!important;font-size:11px;color:#505156}
		.user-code{display:inline-flex;padding:5px 9px;border-radius:7px;font-size:10px;font-weight:600;color:#df5913;background:#fff0e8}.user-profile{display:flex;align-items:center;gap:10px}.user-avatar{display:grid;place-items:center;width:34px;height:34px;border-radius:50%;font-size:12px;font-weight:700;color:#fff;background:linear-gradient(145deg,#4a4b50,#292a2d)}.user-name{font-size:12px;font-weight:600;color:#303033}.user-name small{display:block;margin-top:2px;font-size:9px;font-weight:400;color:#8a8b90}
		.username-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:7px;color:#55565b;background:#f3f4f5}.role-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:20px;font-size:10px;font-weight:600}.role-badge.admin{color:#3b64a7;background:#edf3ff}.role-badge.staff{color:#257b53;background:#eaf7f0}.role-badge.other{color:#775b31;background:#fff6e6}
		.user-actions{display:flex;justify-content:center;gap:6px}.user-action-btn{display:inline-grid;place-items:center;width:32px;height:32px;border:1px solid #e0e1e4;border-radius:8px;color:#5f6065;background:#fff;transition:.15s}.user-action-btn:hover{border-color:#f36b21;color:#f36b21;background:#fff7f2}.user-action-btn.delete:hover{border-color:#dc5b5b;color:#c74848;background:#fff2f2}
		.system-users-page .dataTables_info{padding-top:15px!important;font-size:10px;color:#85868b}.system-users-page .dataTables_paginate{padding-top:10px!important}.system-users-page .paginate_button{min-width:32px!important;height:32px;padding:7px 10px!important;border:0!important;border-radius:7px!important;font-size:10px}.system-users-page .paginate_button.current{color:#fff!important;background:#f36b21!important}
		@media(max-width:767px){.system-user-stats>[class*="col-"]{margin-bottom:10px}.system-users-card>.card-header{align-items:flex-start;flex-direction:column}.system-users-table-wrap{padding:0 12px 14px}.system-users-page .dataTables_filter{float:none;text-align:left}.system-users-page .dataTables_filter label{align-items:flex-start;flex-direction:column}.system-users-page .dataTables_filter input{width:100%}}
	</style>

	<div class="container-fluid system-users-page">
		<div class="system-users-heading"><h1>System Users</h1><p>Manage application access, usernames, and assigned user roles.</p></div>
		<div class="row system-user-stats">
			<div class="col-md-4"><div class="user-stat-card"><span class="user-stat-icon"><i class="fa fa-users"></i></span><div class="user-stat-copy"><h6>Total Users</h6><h3><?php echo (int)$total_users; ?></h3></div></div></div>
			<div class="col-md-4"><div class="user-stat-card admin"><span class="user-stat-icon"><i class="fa fa-user-circle"></i></span><div class="user-stat-copy"><h6>Administrators</h6><h3><?php echo (int)$total_admins; ?></h3></div></div></div>
			<div class="col-md-4"><div class="user-stat-card staff"><span class="user-stat-icon"><i class="fa fa-id-badge"></i></span><div class="user-stat-copy"><h6>Staff Users</h6><h3><?php echo (int)$total_staff; ?></h3></div></div></div>
		</div>

		<div class="card system-users-card">
			<div class="card-header">
				<div class="user-directory-title"><span class="user-directory-icon"><i class="fa fa-users"></i></span><div><h2>User Directory</h2><p>All system accounts and their assigned access roles.</p></div></div>
				<button type="button" id="new_user"><i class="fa fa-plus"></i><span>Add User</span></button>
			</div>
			<div class="table-responsive system-users-table-wrap">
				<table id="system-users-table" class="table system-users-table">
					<thead><tr><th>User ID</th><th>User</th><th>Username</th><th>Role</th><th class="text-center">Actions</th></tr></thead>
					<tbody>
					<?php
					$type = array('', 'Admin', 'Staff', 'Alumnus/Alumna');
					$users = $conn->query("SELECT * FROM users ORDER BY name ASC");
					while($row = $users->fetch_assoc()):
						$role = isset($type[$row['type']]) ? $type[$row['type']] : 'Other';
						$role_class = ((int)$row['type'] === 1) ? 'admin' : (((int)$row['type'] === 2) ? 'staff' : 'other');
						$name = ucwords($row['name']);
						$initial = strtoupper(substr(trim($name), 0, 1));
					?>
						<tr>
							<td><span class="user-code">USR-<?php echo str_pad((int)$row['id'], 4, '0', STR_PAD_LEFT); ?></span></td>
							<td><div class="user-profile"><span class="user-avatar"><?php echo htmlspecialchars($initial); ?></span><div class="user-name"><?php echo htmlspecialchars($name); ?><small>System account</small></div></div></td>
							<td><span class="username-chip"><i class="fa fa-user"></i><?php echo htmlspecialchars($row['username']); ?></span></td>
							<td><span class="role-badge <?php echo $role_class; ?>"><?php echo htmlspecialchars($role); ?></span></td>
							<td><div class="user-actions"><a href="javascript:void(0)" class="user-action-btn edit_user" data-id="<?php echo (int)$row['id']; ?>" title="Edit user" aria-label="Edit user"><i class="fa fa-edit"></i></a><a href="javascript:void(0)" class="user-action-btn delete delete_user" data-id="<?php echo (int)$row['id']; ?>" title="Delete user" aria-label="Delete user"><i class="fa fa-trash"></i></a></div></td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<script>
		$(document).ready(function(){
			$('#system-users-table').DataTable({order:[[1,'asc']], pageLength:10});
		});
		$('#new_user').click(function(){ uni_modal('New User','manage_user.php'); });
		$(document).on('click','.edit_user',function(){ uni_modal('Edit User','manage_user.php?id='+$(this).attr('data-id')); });
		$(document).on('click','.delete_user',function(){ _conf('Are you sure you want to delete this user?','delete_user',[$(this).attr('data-id')]); });
		function delete_user(id){
			start_load();
			$.ajax({url:'ajax.php?action=delete_user',method:'POST',data:{id:id},success:function(resp){if(resp==1){alert_toast('User successfully deleted','success');setTimeout(function(){location.reload();},1500);}else{end_load();}}});
		}
	</script>
	<?php
}else{
	include 'accessDenied.php';
}
?>

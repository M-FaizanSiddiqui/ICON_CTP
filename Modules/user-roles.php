<?php include('db_connect.php');

if(in_array("54",$_SESSION['login_Permisions']))
{
	function ur_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	$selected_user = icon_get_int('user_id');
	$user_roles = array();
	if($selected_user > 0){
		$ur_qry = $conn->query("SELECT role_id FROM user_roles WHERE user_id = ".$selected_user);
		while($ur_qry && $row = $ur_qry->fetch_assoc()){
			$user_roles[] = (int)$row['role_id'];
		}
	}
	?>
	<style>
		.user-role-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:12px}.user-role-card{display:flex;align-items:flex-start;gap:11px;padding:14px;border:1px solid #eceef3;border-radius:14px;background:#fff}.user-role-card input{margin-top:3px;width:18px;height:18px;accent-color:#f36b21}.user-role-card h4{margin:0;font-size:14px;color:#30323a}.user-role-card p{margin:4px 0 0;font-size:12px;color:#858891}.user-role-assigned{font-size:11px;color:#858891}
	</style>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-users-cog"></i></span><div><h1>User Role Assignment</h1><p>Assign one or multiple roles to each system user.</p></div></div><a class="icon-btn icon-btn-soft" href="index.php?page=Modules/permissions"><i class="fa fa-key"></i> Role Permissions</a></div></div>
		<div class="icon-card">
			<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-user-check"></i></span><div><h3>User Roles</h3><p>User access is now calculated from assigned roles plus legacy direct permissions.</p></div></div></div>
			<form id="manage-user-roles">
				<div class="icon-toolbar">
					<div class="icon-toolbar-group">
						<select class="icon-select" name="user_id" id="user_id" required style="min-width:280px">
							<option value="">Select User</option>
							<?php $users=$conn->query("SELECT id,name,username FROM users ORDER BY name ASC"); while($u=$users->fetch_assoc()): ?>
								<option value="<?php echo (int)$u['id']; ?>" <?php echo $selected_user===(int)$u['id']?'selected':''; ?>><?php echo ur_safe($u['name']).' - '.ur_safe($u['username']); ?></option>
							<?php endwhile; ?>
						</select>
						<button type="button" class="icon-btn icon-btn-primary" id="loadUser"><i class="fa fa-search"></i> Load</button>
					</div>
				</div>
				<div class="icon-card-body">
					<div class="user-role-grid">
						<?php $roles=$conn->query("SELECT * FROM roles WHERE status=0 ORDER BY role_name ASC"); while($role=$roles->fetch_assoc()): ?>
							<label class="user-role-card">
								<input type="checkbox" name="roles[]" value="<?php echo (int)$role['role_id']; ?>" <?php echo in_array((int)$role['role_id'],$user_roles,true)?'checked':''; ?>>
								<span><h4><?php echo ur_safe($role['role_name']); ?></h4><p><?php echo ur_safe($role['role_desc']); ?></p></span>
							</label>
						<?php endwhile; ?>
					</div>
					<div class="mt-3 d-flex justify-content-end" style="gap:8px">
						<button class="icon-btn icon-btn-primary"><i class="fa fa-save"></i> Save User Roles</button>
						<a href="index.php?page=Modules/user-roles" class="icon-btn icon-btn-soft"><i class="fa fa-undo"></i> Reset</a>
					</div>
				</div>
			</form>
		</div>
	</div>
	<script>
		$('#loadUser').click(function(){
			const user = $('#user_id').val();
			if(!user){ alert_toast('Please select user first','warning'); return; }
			location.replace('index.php?page=Modules/user-roles&user_id='+encodeURIComponent(user));
		});
		$('#manage-user-roles').submit(function(e){
			e.preventDefault();
			start_load();
			$.ajax({
				url:'ajax.php?action=save_user_roles',
				data:new FormData($(this)[0]),
				cache:false,
				contentType:false,
				processData:false,
				method:'POST',
				type:'POST',
				success:function(resp){
					if(resp==1){
						alert_toast('User roles updated successfully','success');
						setTimeout(function(){ location.reload(); },900);
					}else{
						end_load();
						alert_toast(resp || 'Error Occured','danger');
					}
				}
			});
		});
	</script>
	<?php
}else{
	include 'accessDenied.php';
}
?>

<?php
include('db_connect.php');
if(session_status() === PHP_SESSION_NONE){ session_start(); }
$meta = array();
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($user_id > 0){
	$user = $conn->query("SELECT * FROM users WHERE id = {$user_id} LIMIT 1");
	if($user && $user->num_rows){ $meta = $user->fetch_assoc(); }
}
$is_edit = !empty($meta['id']);
?>
<style>
	#uni_modal .modal-content{overflow:hidden;border:0;border-radius:15px;box-shadow:0 24px 70px rgba(28,29,32,.24)}#uni_modal .modal-header{min-height:64px;padding:16px 19px;border:0;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:#fff}#uni_modal .modal-title{font-size:16px;font-weight:650;color:#303033}#uni_modal .modal-body{padding:0;background:#f6f6f7}#uni_modal .modal-footer{gap:8px;padding:13px 18px;border:0;border-top:1px solid #e7e8eb;background:#fff}#uni_modal .modal-footer .btn{min-height:38px;margin:0;padding:8px 15px;border-radius:9px;font-size:11px;font-weight:600}#uni_modal .modal-footer #submit{border-color:#f36b21;background:#f36b21;box-shadow:0 7px 16px rgba(243,107,33,.18)}
	.manage-user-modal{margin:14px;padding:18px;border:1px solid #e5e6e9;border-radius:12px;background:#fff}.manage-user-intro{display:flex;align-items:center;gap:12px;margin-bottom:17px;padding-bottom:14px;border-bottom:1px solid #eeeeef}.manage-user-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812)}.manage-user-intro h3{margin:0;font-size:14px;font-weight:650;color:#303033}.manage-user-intro p{margin:3px 0 0;font-size:10px;color:#898a90}.user-modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px}.user-modal-field.full{grid-column:1/-1}.user-modal-field label{display:block;margin:0 0 6px;font-size:10px;font-weight:700;letter-spacing:.04em;color:#626369;text-transform:uppercase}.user-input-wrap{position:relative}.user-input-wrap>i{position:absolute;z-index:2;left:12px;top:50%;transform:translateY(-50%);font-size:11px;color:#f36b21}.user-input-wrap .form-control,.user-input-wrap .custom-select{height:43px!important;padding:8px 11px 8px 35px!important;border:1px solid #dfe1e5!important;border-radius:9px!important;font-size:11px;background:#fff}.user-input-wrap .form-control:focus,.user-input-wrap .custom-select:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)!important}.password-help{display:block;margin-top:6px;font-size:9px;color:#929398}.user-modal-alert .alert{margin:0 0 14px;padding:10px 12px;border:0;border-radius:8px;font-size:10px}
	@media(max-width:575px){.manage-user-modal{margin:8px;padding:14px}.user-modal-grid{grid-template-columns:1fr}.user-modal-field.full{grid-column:auto}}
</style>
<div class="manage-user-modal">
	<div id="msg" class="user-modal-alert"></div>
	<div class="manage-user-intro"><span class="manage-user-icon"><i class="fa fa-user"></i></span><div><h3><?php echo $is_edit ? 'Update User Account' : 'Create User Account'; ?></h3><p><?php echo $is_edit ? 'Edit identity, login, or access role.' : 'Add login credentials and an access role.'; ?></p></div></div>
	<form action="" id="manage-user">
		<input type="hidden" name="id" value="<?php echo $is_edit ? (int)$meta['id'] : ''; ?>">
		<div class="user-modal-grid">
			<div class="user-modal-field"><label for="name">Full Name</label><div class="user-input-wrap"><i class="fa fa-id-card"></i><input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" value="<?php echo isset($meta['name']) ? htmlspecialchars($meta['name'], ENT_QUOTES, 'UTF-8') : ''; ?>" required></div></div>
			<div class="user-modal-field"><label for="username">Username</label><div class="user-input-wrap"><i class="fa fa-user-circle"></i><input type="text" name="username" id="username" class="form-control" placeholder="Enter username" value="<?php echo isset($meta['username']) ? htmlspecialchars($meta['username'], ENT_QUOTES, 'UTF-8') : ''; ?>" required autocomplete="off"></div></div>
			<div class="user-modal-field <?php echo (!isset($_GET['mtype']) && (!isset($meta['type']) || (int)$meta['type'] !== 3)) ? '' : 'full'; ?>"><label for="password">Password</label><div class="user-input-wrap"><i class="fa fa-lock"></i><input type="password" name="password" id="password" class="form-control" placeholder="<?php echo $is_edit ? 'Enter only to change password' : 'Enter password'; ?>" autocomplete="new-password" <?php echo $is_edit ? '' : 'required'; ?>></div><?php if($is_edit): ?><small class="password-help"><i class="fa fa-info-circle"></i> Leave blank to retain the current password.</small><?php endif; ?></div>
			<?php if(isset($meta['type']) && (int)$meta['type'] === 3): ?>
				<input type="hidden" name="type" value="3">
			<?php elseif(!isset($_GET['mtype'])): ?>
			<div class="user-modal-field"><label for="type">User Role</label><div class="user-input-wrap"><i class="fa fa-users"></i><select name="type" id="type" class="custom-select"><option value="2" <?php echo isset($meta['type']) && (int)$meta['type'] === 2 ? 'selected' : ''; ?>>Staff</option><option value="1" <?php echo isset($meta['type']) && (int)$meta['type'] === 1 ? 'selected' : ''; ?>>Administrator</option></select></div></div>
			<?php endif; ?>
		</div>
	</form>
</div>
<script>
	$('#manage-user').off('submit.manageUser').on('submit.manageUser',function(e){
		e.preventDefault();start_load();
		$.ajax({url:'ajax.php?action=save_user',method:'POST',data:$(this).serialize(),success:function(resp){if(resp==1){alert_toast('User successfully saved','success');setTimeout(function(){location.reload();},1500);}else{$('#msg').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> This username already exists.</div>');end_load();}}});
	});
</script>

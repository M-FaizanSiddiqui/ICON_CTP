<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'SystemUser/policies' LIMIT 1");
$module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";
if($module_id == "0" || in_array($module_id,$_SESSION['login_Permisions']))
{
	function pol_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-clipboard"></i></span><div><h1>System Policies</h1><p>Add or update payroll and business policy values.</p></div></div></div></div>
		<div class="row">
			<div class="col-md-4">
				<form id="policy-form" class="icon-form">
					<div class="icon-card">
						<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-pen"></i></span><div><h3>Policy Form</h3><p>Create or edit a policy.</p></div></div></div>
						<div class="icon-card-body">
							<input type="hidden" name="policy_id">
							<div class="form-group"><label><b>Policy Name</b></label><input class="form-control" name="policy_name" required placeholder="e.g. Overtime Hour Salary"></div>
							<div class="form-group"><label><b>Policy Key</b></label><input class="form-control" name="policy_key" required maxlength="20" placeholder="e.g. OTHOURSAL"></div>
							<div class="form-group"><label><b>Policy Value</b></label><input class="form-control" name="policy_value" required maxlength="20" placeholder="e.g. 1.5"></div>
							<div class="d-flex justify-content-end" style="gap:8px"><button type="button" id="resetPolicy" class="icon-btn icon-btn-soft"><i class="fa fa-undo"></i> Reset</button><button class="icon-btn icon-btn-primary"><i class="fa fa-save"></i> Save</button></div>
						</div>
					</div>
				</form>
			</div>
			<div class="col-md-8">
				<div class="icon-card">
					<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-list"></i></span><div><h3>Policy List</h3><p>Current configured policies.</p></div></div></div>
					<div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table" id="policies-table"><thead><tr><th>ID</th><th>Name</th><th>Key</th><th>Value</th><th>Action</th></tr></thead><tbody>
						<?php $policies=$conn->query("SELECT * FROM policies ORDER BY policy_id ASC"); while($row=$policies->fetch_assoc()): ?>
						<tr>
							<td><span class="icon-badge">#<?php echo (int)$row['policy_id']; ?></span></td>
							<td><?php echo pol_safe($row['policy_name']); ?></td>
							<td><?php echo pol_safe($row['policy_key']); ?></td>
							<td><?php echo pol_safe($row['policy_value']); ?></td>
							<td><div class="icon-action-group"><button class="icon-action edit edit-policy" type="button" title="Edit" data-id="<?php echo (int)$row['policy_id']; ?>" data-name="<?php echo pol_safe($row['policy_name']); ?>" data-key="<?php echo pol_safe($row['policy_key']); ?>" data-value="<?php echo pol_safe($row['policy_value']); ?>"><i class="fa fa-edit"></i></button><button class="icon-action print delete-policy" type="button" title="Delete" data-id="<?php echo (int)$row['policy_id']; ?>"><i class="fa fa-trash"></i></button></div></td>
						</tr>
						<?php endwhile; ?>
					</tbody></table></div></div>
				</div>
			</div>
		</div>
	</div>
	<script>
		$(function(){
			$('#policies-table').DataTable({pageLength:25});
			$('.edit-policy').click(function(){
				$('[name=policy_id]').val($(this).data('id'));
				$('[name=policy_name]').val($(this).data('name'));
				$('[name=policy_key]').val($(this).data('key'));
				$('[name=policy_value]').val($(this).data('value'));
				window.scrollTo({top:0,behavior:'smooth'});
			});
			$('#resetPolicy').click(function(){ $('#policy-form').get(0).reset(); $('[name=policy_id]').val(''); });
			$('#policy-form').submit(function(e){
				e.preventDefault(); start_load();
				$.ajax({url:'ajax.php?action=save_policy',method:'POST',data:new FormData(this),contentType:false,processData:false,success:function(resp){
					if(resp==1){ alert_toast('Policy saved successfully','success'); setTimeout(function(){location.reload();},800); } else { end_load(); alert_toast(resp,'danger'); }
				}});
			});
			$('.delete-policy').click(function(){
				if(!confirm('Delete this policy?')) return;
				start_load();
				$.post('ajax.php?action=delete_policy',{policy_id:$(this).data('id')},function(resp){
					if(resp==1){ alert_toast('Policy deleted','success'); setTimeout(function(){location.reload();},800); } else { end_load(); alert_toast(resp,'danger'); }
				});
			});
		});
	</script>
	<?php
}else{ include 'accessDenied.php'; }
?>

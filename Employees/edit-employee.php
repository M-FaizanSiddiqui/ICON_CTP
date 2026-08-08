<?php include('db_connect.php');

if(in_array("64",$_SESSION['login_Permisions']))
{
	$emp_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
	$employee = null;
	if($emp_id > 0){
		$emp_qry = $conn->query("SELECT a.*, b.des_name FROM employee a LEFT JOIN designations b ON a.emp_designation_id = b.des_id WHERE a.emp_id = ".$emp_id." LIMIT 1");
		if($emp_qry && $emp_qry->num_rows > 0){
			$employee = $emp_qry->fetch_assoc();
		}
	}
	function emp_safe($value){
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}
	?>
	<div class="container-fluid icon-page">
		<div class="icon-hero">
			<div class="icon-hero-row">
				<div class="icon-title">
					<span class="icon-title-icon"><i class="fa fa-user-edit"></i></span>
					<div>
						<h1>Edit Employee</h1>
						<p>Update employee contact information and designation.</p>
					</div>
				</div>
				<a href="index.php?page=Employees/view-employee" class="icon-btn icon-btn-soft"><i class="fa fa-arrow-left"></i> Back</a>
			</div>
		</div>

		<?php if(!$employee): ?>
			<div class="icon-card">
				<div class="icon-card-body text-center">
					<p class="text-muted mb-3">Employee record was not found.</p>
					<a href="index.php?page=Employees/view-employee" class="icon-btn icon-btn-primary"><i class="fa fa-users"></i> View Employees</a>
				</div>
			</div>
		<?php else: ?>
			<form action="" id="manage-employee" class="icon-form">
				<div class="icon-card">
					<div class="icon-card-header">
						<div class="icon-card-title">
							<span><i class="fa fa-id-badge"></i></span>
							<div>
								<h3>EMP-<?php echo str_pad($employee['emp_id'],4,'0',STR_PAD_LEFT); ?></h3>
								<p><?php echo emp_safe($employee['emp_name']); ?> profile details</p>
							</div>
						</div>
						<span class="icon-badge <?php echo ((int)$employee['emp_status'] === 0 ? 'success' : ''); ?>">
							<?php echo ((int)$employee['emp_status'] === 0 ? 'Active' : 'Inactive'); ?>
						</span>
					</div>
					<div class="icon-card-body">
						<input type="hidden" name="emp_id" value="<?php echo (int)$employee['emp_id']; ?>">
						<div class="row">
							<div class="col-md-6 form-group">
								<label class="control-label"><b>Name</b></label>
								<input type="text" class="form-control" required name="emp_name" value="<?php echo emp_safe($employee['emp_name']); ?>" placeholder="Enter employee name">
							</div>
							<div class="col-md-6 form-group">
								<label class="control-label"><b>Email</b></label>
								<input type="email" class="form-control" name="emp_email" value="<?php echo emp_safe($employee['emp_email']); ?>" placeholder="name@company.com">
							</div>
							<div class="col-md-6 form-group">
								<label class="control-label"><b>Phone No</b></label>
								<input type="text" class="form-control" name="emp_ph_no" value="<?php echo emp_safe($employee['emp_ph_no']); ?>" placeholder="e.g. +92 300 1234567">
							</div>
							<div class="col-md-6 form-group">
								<label class="control-label"><b>Designation</b></label>
								<select class="form-control" required name="designation_id">
									<option value="">Select designation</option>
									<?php
									$designations = $conn->query("SELECT * FROM designations ORDER BY des_name ASC");
									while($des = $designations->fetch_assoc()):
										$selected = ((int)$employee['emp_designation_id'] === (int)$des['des_id']) ? 'selected' : '';
									?>
										<option value="<?php echo (int)$des['des_id']; ?>" <?php echo $selected; ?>><?php echo emp_safe($des['des_name']); ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="d-flex justify-content-end flex-wrap" style="gap:9px">
							<button class="icon-btn icon-btn-soft" type="button" onclick="$('#manage-employee').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
							<button class="icon-btn icon-btn-primary"><i class="fa fa-save"></i> Update Employee</button>
						</div>
					</div>
				</div>
			</form>
		<?php endif; ?>
	</div>
	<script>
		$('#manage-employee').submit(function(e){
			e.preventDefault();
			start_load();
			$.ajax({
				url:'ajax.php?action=save_employee',
				data:new FormData($(this)[0]),
				cache:false,
				contentType:false,
				processData:false,
				method:'POST',
				type:'POST',
				success:function(resp){
					if(resp==1){
						alert_toast("Employee updated successfully",'success');
						setTimeout(function(){ window.open('index.php?page=Employees/view-employee','_self'); },900);
					}else{
						end_load();
						alert_toast("Error Occured "+resp,'danger');
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

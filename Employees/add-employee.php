<?php include('db_connect.php');

if(in_array(61,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page form-page-narrow">
		
		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="manage-employee">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fa fa-id-badge"></i></span>
								<div class="form-title-copy"><h2>Add Employee</h2><p>Create an employee profile and assign a designation.</p></div>
							</div>
							<div class="card-body master-form-fields">
								<input type="hidden" name="emp_id">
								<div class="form-group">
									<label class="control-label"><b>Name:</b></label>
									<input type="text" class="form-control" name="emp_name" placeholder="Enter employee name">
								</div>
								<div class="form-group">
									<label class="control-label"><b>Email:</b></label>
									<input type="text" class="form-control" name="emp_email" placeholder="name@company.com">
								</div>
								<div class="form-group">
									<label class="control-label"><b>Phone No:</b></label>
									<input type="text" class="form-control" name="emp_ph_no" placeholder="e.g. +92 300 1234567">
								</div>
								<div class="form-group">
									<label class="control-label"><b>Designation:</b></label>
									<select class="form-control" name="designation_id">
										<option value="">Select designation</option>
										<?php
										$q1 = "SELECT * FROM designations";
										$r1 = mysqli_query($conn,$q1);
										while($d1 = mysqli_fetch_array($r1)){
											$des_id = $d1['des_id'];
											$des_name = $d1['des_name'];
											?>
											<option value="<?= $des_id ?>"><?= $des_name ?></option>
											<?php
										}
										?>
									</select>
								</div>
								
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#manage-employee').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Save Employee</button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
				<!-- FORM Panel -->
			</div>
		</div>	

	</div>
	<style>
		
		td{
			vertical-align: middle !important;
		}
		td p {
			margin:unset;
		}
	</style>
	<script>
		$('#manage-employee').on('reset',function(){
			$('input:hidden').val('')
		})
		
		$('#manage-employee').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_employee',
				data: new FormData($(this)[0]),
				cache: false,
				contentType: false,
				processData: false,
				method: 'POST',
				type: 'POST',
				success:function(resp){
					if(resp==1){
						alert_toast("Data successfully added",'success')
						setTimeout(function(){
							window.open('index.php?page=Employees/view-employee','_self');
						},1500)

					}else{
						alert_toast("Error Occured"+resp,'danger');
					}
				}
			})
		})
		
		$('table').dataTable()
	</script>


	<?php
}else{
	include 'accessDenied.php';
}
?>

<?php include('db_connect.php');

if(in_array(11,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page form-page-narrow">
		
		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="manage-customer">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fa fa-user-plus"></i></span>
								<div class="form-title-copy"><h2>Add New Customer</h2><p>Enter the customer’s primary contact information.</p></div>
							</div>
							<div class="card-body master-form-fields">
								<input type="hidden" name="cust_id">
								<div class="form-group">
									<label class="control-label">Name</label>
									<input type="text" class="form-control" name="cust_name" placeholder="Enter customer name">
								</div>
								<div class="form-group">
									<label class="control-label">Email</label>
									<input type="text" class="form-control" name="cust_email" placeholder="name@company.com">
								</div>
								<div class="form-group">
									<label class="control-label">Phone No</label>
									<input type="text" class="form-control" name="cust_ph_no" placeholder="e.g. +92 300 1234567">
								</div>
								<div class="form-group">
									<label class="control-label">Address</label>
									<input type="text" class="form-control" name="cust_address" placeholder="Enter complete address">
								</div>
								
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#manage-customer').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Save Customer</button>
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
		$('#manage-customer').on('reset',function(){
			$('input:hidden').val('')
		})
		
		$('#manage-customer').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_customer',
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
							window.open('index.php?page=Customer/view-customer','_self');
						},1500)

					}else{
						alert_toast("Error Occured",'danger');
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

<?php include('db_connect.php');

if(in_array("38",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page form-page-narrow">
		
		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="account_types">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fa fa-sitemap"></i></span><div class="form-title-copy"><h2>Add New Account Type</h2><p>Create a parent or nested accounting classification.</p></div>
							</div>
							<div class="card-body">
								<input type="hidden" name="acc_type_id">
								<div class="form-group">
									<label class="control-label"><b>Type Name:</b></label>
								<input type="text" class="form-control" required="true" name="type_name" placeholder="Enter account type name">
								</div>
								<div class="form-group">
									<label class="control-label"><b>Parent Type:</b></label>
									<select class="form-control" name="type_parent_id" required="true">
										<option value="">Please Select</option>
										<?php 
										$query_parent_type = "SELECT * FROM account_types WHERE type_parent_id = 0";
										$result_type = mysqli_query($conn,$query_parent_type);
										while($data_type = mysqli_fetch_array($result_type)){
											?>
											<option value="<?php echo $data_type['acc_type_id'] ?>"><?php echo $data_type['type_name'] ?></option>
											<?php
										}
										?>
									</select>
								</div>								
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#account_types').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Save Account Type</button>
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
		$('#account_types').on('reset',function(){
			$('input:hidden').val('')
		})
		
		$('#account_types').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_acc_type',
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
							window.open('index.php?page=account-types','_self');
						},1500)

					}
					else if(resp==2){
						alert_toast("Data successfully updated",'success')
						setTimeout(function(){
							location.reload()
						},1500)

					}else{
						alert_toast("Error Occured",'danger');
						alert(resp);						
					}
				}
			})
		})
		
	</script>


	<?php
}else{
	include 'accessDenied.php';
}
?>

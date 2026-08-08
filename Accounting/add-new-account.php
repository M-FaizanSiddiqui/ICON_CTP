<?php include('db_connect.php');

if(in_array("37",$_SESSION['login_Permisions']))
{
	?>
	<style>
		select option .abc { color: blue; }
	</style>
	<div class="container-fluid professional-form-page">
		
		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="account_types">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fas fa-university"></i></span>
								<div class="form-title-copy"><h2>Add New Account</h2><p>Create and classify an account in the chart of accounts.</p></div>
							</div>
							<div class="card-body">
								<input type="hidden" name="acc_id">
								<div class="row">

									<div class="form-group col-md-12">
										<label class="control-label"><b>Account Name:</b></label>
								<input type="text" placeholder="Enter account name" class="form-control" required="true" name="acc_name">
									</div>

									<div class="form-group col-md-3">
										<label class="control-label"><b>Company:</b></label>
										<select class="form-control" name="company_id" required="true">
											<option value="">Please Select</option>
											<?php
											$query_comp = "SELECT * FROM companies";
											$result_comp = mysqli_query($conn,$query_comp);
											while($data_comp = mysqli_fetch_array($result_comp)){
												?>
												<option value="<?= $data_comp['comp_id'] ?>"><?= $data_comp['comp_name'] ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="form-group col-md-3">
										<label class="control-label"><b>Account Type:</b></label>
										<select class="form-control" name="acc_type" required="true">
											<option value="">Please Select</option>
											<?php 
											$query_parent_type = "SELECT * FROM account_types WHERE del_status = 0 AND type_parent_id = 0";
											$result_type = mysqli_query($conn,$query_parent_type);
											while($data_type = mysqli_fetch_array($result_type)){

												?>
												<option class="abc" value="<?php echo $data_type['acc_type_id'] ?>"><b>[<?php echo $data_type['type_name'] ?>]</b></option>
												<?php

												$query_parent_type_sub = "SELECT * FROM account_types WHERE del_status = 0 AND type_parent_id = ".$data_type['acc_type_id'];
												$result_type_sub = mysqli_query($conn,$query_parent_type_sub);
												while($data_type_sub = mysqli_fetch_array($result_type_sub)){
													?>
													<option value="<?php echo $data_type_sub['acc_type_id'] ?>">------------> <?php echo $data_type_sub['type_name'] ?></option>
													<?php
												}
											}
											?>
										</select>
									</div>	

									


									<div class="form-group col-md-3">
										<label class="control-label"><b>Parent Account:</b></label>
										<select class="form-control" name="parrent_account" required="true">
											<option value="">Please Select</option>
											<?php
											$query_parent_type = "SELECT * FROM accounts WHERE del_status = 0";
											$result_type = mysqli_query($conn,$query_parent_type);
											while($data_type = mysqli_fetch_array($result_type)){
												?>
												<option value="<?= $data_type['account_no'] ?>"><?= $data_type['acc_name'] ?></option>
												<?php
											}
											?>
										</select>
									</div>


									<div class="form-group col-md-3">
										<label class="control-label"><b>Financial Statement:</b></label>
										<select class="form-control" name="fin_statement" required="true">
											<option value="">Please Select</option>
											<option value="1">Balance Sheet</option>
											<option value="2">Profit & Loss</option>
										</select>
									</div>
								</div>


							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#account_types').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Save Account</button>
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
				url:'ajax.php?action=save_new_acc',
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
							window.open('index.php?page=Accounting/chart-of-accounts','_self');
						},1500)

					}
					else if(resp==2){
						alert_toast("Data successfully updated",'success')
						setTimeout(function(){
							location.reload()
						},1500)

					}else{
						alert_toast("Error Occured: "+resp,'danger');
						// alert(resp);						
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

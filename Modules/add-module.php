<?php include('db_connect.php');

if(in_array(52,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid">
		
		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="manage-modules">
						<div class="card">
							<div class="card-header">
								Modules
							</div>
							<div class="card-body">
								<input type="hidden" name="m_id">
								<div class="row">
									<div class="form-group col-md-6">
										<label class="control-label"><b>Module Name:</b></label>
										<input type="text" placeholder="Module" class="form-control" required="true" name="m_name">
									</div>
									<div class="form-group col-md-6">
										<label class="control-label"><b>Parent Id:</b></label>
										<select class="form-control" required="true" name="m_parent_id">
											<option value="">Please Select</option>
											<option value="0">Parent Module</option>
											<?php 
											$query_mod = "SELECT * FROM modules_1";
											$result_mod = mysqli_query($conn,$query_mod);
											while($data_mod = mysqli_fetch_array($result_mod)){
												?>
												<option value="<?= $data_mod['m_id'] ?>"><?= $data_mod['m_name'] ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="form-group col-md-6">
										<label class="control-label"><b>URL:</b></label>
										<input type="text" placeholder="URL" class="form-control" required="true" name="m_url">
									</div>
									<div class="form-group col-md-6">
										<label class="control-label"><b>Fav Icon:</b></label>
										<input type="text" placeholder="Fav Icon" class="form-control" required="true" name="fav_icon">
									</div>
									<div class="form-group col-md-6">
										<label class="control-label"><b>Ordering:</b></label>
										<input type="text" placeholder="Ordering" class="form-control" required="true" name="ordering">
									</div>
									<div class="form-group col-md-6">
										<label class="control-label"><b>Heading:</b></label>
										<select class="form-control" required="true" name="heading">
											<option value="">Please Select</option>
											<option value="0">No</option>
											<option value="1">Yes</option>
										</select>
									</div>

									<div class="form-group col-md-6">
										<label class="control-label"><b>Show in menu:</b></label>
										<select class="form-control" required="true" name="show_in_menu">
											<option value="">Please Select</option>
											<option value="0">No</option>
											<option value="1">Yes</option>
										</select>
									</div>	
								</div>
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-primary "> Save</button>
										<button class="btn btn-default" type="button" onclick="$('#manage-modules').get(0).reset()"> Cancel</button>
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
		$('#manage-modules').on('reset',function(){
			$('input:hidden').val('')
		})

		$('#manage-modules').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_module',
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
							window.open('index.php?page=Modules/view-modules','_self');
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
<?php include('db_connect.php');

if(in_array(49,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page form-page-narrow">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="manage_waste_inventory">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fa fa-recycle"></i></span>
								<div class="form-title-copy"><h2>Waste Inventory</h2><p>Record damaged or consumed plate stock against a job.</p></div>
							</div>
							<div class="card-body">
								<input type="hidden" name="w_id">
								<div class="row">
									<div class="col-md-3 form-group">
										<label class="control-label"><b>Inventory:</b></label>
										<select  name="item_id" class="form-control" required="true">
											<option value="">Select inventory plate</option>
											<?php
											$query_supp = "SELECT * FROM inventory_item WHERE status = 0";
											$result_supp = mysqli_query($conn,$query_supp);
											while($data_supp = mysqli_fetch_array($result_supp)){
												?>
												<option value="<?php echo $data_supp['item_id'] ?>"><?php echo $data_supp["item_name"] ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="col-md-3 form-group">
										<label class="control-label"><b>Quantity:</b></label>
									<input type="number" value="0" required="true" class="form-control" name="qty" placeholder="Enter waste quantity">
									</div>

									<div class="col-md-3 form-group">
										<label class="control-label"><b>Tag Job:</b></label>
									<input placeholder="Enter job ID" type="text" class="form-control" name="job_id" required="true">
									</div>

									<div class="col-md-3 form-group">
										<label class="control-label"><b>Dated:</b></label>
										<input placeholder="Dated" type="date" class="form-control" name="dated" required="true">
									</div>

									<div class="col-md-12 form-group">
										<label class="control-label"><b>Remarks:</b></label>
									<textarea name="remarks" placeholder="Describe the reason for waste" class="form-control" required="true"></textarea>
									</div>
								</div>
								
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#manage_waste_inventory').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Save Waste Entry</button>
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
		$('#manage_waste_inventory').on('reset',function(){
			$('input:hidden').val('')
		})

		$('#manage_waste_inventory').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_waste_item',
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
							window.open('index.php?page=Stocks/view-waste-inventory','_self');
						},1500)

					}
					else{
						alert_toast(resp,'danger');
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

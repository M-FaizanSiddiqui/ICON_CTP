<?php include('db_connect.php');

if(in_array(23,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page form-page-narrow">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="manage_inventory_item">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fa fa-layer-group"></i></span>
								<div class="form-title-copy"><h2>Add New Plate</h2><p>Define the plate name and production dimensions.</p></div>
							</div>
							<div class="card-body master-form-fields">
								<input type="hidden" name="item_id">
								<div class="form-group">
									<label class="control-label">Name</label>
									<input type="text" class="form-control" name="item_name" placeholder="Enter plate name">
								</div>
								<div class="form-group">
									<label class="control-label">Size in MM</label>
									<input type="text" class="form-control" name="size_in_mm" placeholder="Enter size in millimetres">
								</div>
								<div class="form-group">
									<label class="control-label">HL Inches</label>
									<input type="text" class="form-control" name="hl_inches" placeholder="Enter HL measurement in inches">
								</div>
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#manage_inventory_item').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Save Plate</button>
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
		$('#manage_inventory_item').on('reset',function(){
			$('input:hidden').val('')
		})

		$('#manage_inventory_item').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_inventory_item',
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
							window.open('index.php?page=Stocks/view-items','_self');
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

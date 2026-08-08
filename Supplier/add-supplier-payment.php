<?php include('db_connect.php');

if(in_array(7,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid mt-3 px-3">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<div class="card border-0 shadow-sm mb-3">
						<div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
							<div>
								<h5 class="mb-0 fw-semibold" style="color: var(--accent);">💳 Supplier Payment Form</h5>
								<small class="text-muted">Input Details related to Supplier Payments.</small>
							</div>
						</div>
					</div>

					<form action="" id="supplier_payment">
						<div class="card">
							
							<div class="card-body">
								<input type="hidden" name="pay_id">

								
								<div class="row">
									<div class="form-group col-md-4">
										<label>Supplier Name</label>
										<select name="supplier_id" class="form-control" required>
											<option value="">Select Supplier</option>
											<?php
											$query_supp = "SELECT * FROM suppliers WHERE supp_status = 0";
											$result_supp = mysqli_query($conn,$query_supp);
											while($data_supp = mysqli_fetch_array($result_supp)){
												?>
												<option value="<?php echo $data_supp['supp_id'] ?>">
													<?php echo $data_supp["supp_name"] ?>
												</option>
											<?php } ?>
										</select>
									</div>

									<div class="form-group col-md-4">
										<label>Reference</label>
										<input type="text" class="form-control" name="reference" placeholder="Enter reference">
									</div>

									<div class="form-group col-md-4">
										<label>Consignee Name</label>
										<input type="text" class="form-control" name="consignee_name" placeholder="Enter consignee name">
									</div>
								</div>

								
								<div class="row">
									<div class="form-group col-md-4">
										<label>Payment Date</label>
										<input type="date" class="form-control" name="payment_date">
									</div>

									<div class="form-group col-md-4">
										<label>Payment Mode</label>
										<select name="payment_mode" id="payment_mode" class="form-control">
											<option value="">Select Mode</option>
											<option value="1">Cash</option>
											<option value="2">Cheque</option>
										</select>
									</div>

									<div class="form-group col-md-4">
										<label>Amount</label>
										<input type="number" class="form-control" name="amount" placeholder="Enter amount">
									</div>
								</div>

								
								<div class="row">
									<div class="form-group col-md-4">
										<label>Cheque No</label>
										<input type="number" class="form-control" name="cheque_no">
									</div>

									<div class="form-group col-md-4">
										<label>Cheque Date</label>
										<input type="date" class="form-control" name="cheque_date">
									</div>

									<div class="form-group col-md-4">
										<label>Paid From</label>
										<select class="form-control" name="paid_from_acc" required>
											<option value="">Select Account</option>
											<?php
											$query_acc = "SELECT * FROM accounts WHERE del_status = 0 AND acc_type = 1 AND (acc_cat = 3 OR acc_name LIKE '%cash%' OR acc_name LIKE '%bank%') ORDER BY account_no ASC";
											$result_acc = mysqli_query($conn,$query_acc);
											while($data_acc = mysqli_fetch_array($result_acc)){
												?>
												<option value="<?php echo $data_acc['account_no'] ?>">
													<?php echo $data_acc["account_no"]." - ".$data_acc["acc_name"] ?>
												</option>
											<?php } ?>
										</select>
									</div>
								</div>

								
								<div class="row">
									<div class="form-group col-md-12">
										<label>Remarks</label>
										<input type="text" class="form-control" name="remarks" placeholder="Write remarks...">
										<input type="hidden" value="<?php echo $_SESSION['login_id'] ?>" name="user_id">
									</div>
								</div>
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-primary"> Save</button>
										<button class="btn btn-default" type="button" onclick="$('#supplier_payment').get(0).reset()"> Cancel</button>
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
		$('#supplier_payment').on('reset',function(){
			$('input:hidden').val('')
		})

		$('#supplier_payment').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_supplier_payment',
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
							window.open('index.php?page=Supplier/supplier-payments','_self');
						},1500)
					}else{
						alert_toast(resp,'danger');
					}
				}
			})
		})
		$('.edit_supplier').click(function(){
			start_load()
			var cat = $('#supplier_payment')
			cat.get(0).reset()
			cat.find("[name='id']").val($(this).attr('data-id'))
			cat.find("[name='name']").val($(this).attr('data-name'))
			cat.find("[name='description']").val($(this).attr('data-description'))
			end_load()
		})
		$('.delete_supplier').click(function(){
			_conf("Are you sure to delete this supplier?","delete_supplier",[$(this).attr('data-id')])
		})
		function delete_supplier($id){
			start_load()
			$.ajax({
				url:'ajax.php?action=delete_supplier',
				method:'POST',
				data:{id:$id},
				success:function(resp){
					if(resp==1){
						alert_toast("Data successfully deleted",'success')
						setTimeout(function(){
							location.reload()
						},1500)

					}
				}
			})
		}
		$('table').dataTable()
	</script>

	<?php
}else{
	include 'accessDenied.php';
}
?>

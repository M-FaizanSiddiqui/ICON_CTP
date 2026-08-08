<?php include('db_connect.php');

if(in_array(13,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="customer_payment">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fas fa-hand-holding-usd"></i></span>
								<div class="form-title-copy"><h2>Add Customer Payment</h2><p>Record payment details and the receiving account.</p></div>
							</div>
							<div class="card-body">
								<input type="hidden" name="pay_id">
								<div class="row">
									<div class="form-group col-md-4">
										<label class="control-label"><b>Customer Name:</b></label>
										<select  name="customer_id"  class="form-control" >
											<option value="">Select customer</option>
											<?php
											$query_supp = "SELECT * FROM customers WHERE cust_status = 0";
											$result_supp = mysqli_query($conn,$query_supp);
											while($data_supp = mysqli_fetch_array($result_supp)){
												?>
												<option value="<?php echo $data_supp['cust_id'] ?>"><?php echo $data_supp["cust_name"] ?></option>
												<?php
											}
											?>
										</select>
									</div>


									<div class="form-group col-md-4">
										<label class="control-label"><b>Reference:</b></label>
									<input type="text" class="form-control" name="reference" placeholder="Enter payment reference">
									</div>

									<div class="form-group col-md-4">
										<label class="control-label"><b>Consignee Name:</b></label>
									<input type="text" class="form-control" name="consignee_name" placeholder="Enter consignee name">
									</div>
									<div class="form-group col-md-4">
										<label class="control-label"><b>Payment Date:</b></label>
										<input type="date" class="form-control" name="payment_date">
									</div>

									<div class="form-group col-md-4">
										<label class="control-label"><b>Payment Mode:</b></label>
										<select  name="payment_mode" id="payment_mode" class="form-control" >
											<option value="">Select payment mode</option>
											<option value="1">Cash</option>
											<option value="2">Cheque</option>
										</select>
									</div>


									<div class="form-group col-md-4">
										<label class="control-label"><b>Amount:</b></label>
									<input type="number" class="form-control" name="amount" placeholder="Enter amount">
									</div>
									<div class="form-group col-md-4">
										<label class="control-label"><b>Received By:</b></label>
									<input type="text" class="form-control" name="received_by" placeholder="Name of receiving person">
									</div>


									<div class="form-group col-md-4">
										<label class="control-label"><b>Cheque No:</b></label>
									<input type="number" class="form-control" name="cheque_no" placeholder="Enter cheque number">
									</div>
									<div class="form-group col-md-4">
										<label class="control-label"><b>Cheque Date:</b></label>
										<input type="date" class="form-control" name="cheque_date">
									</div>

									<div class="form-group col-md-3">
										<label class="control-label"><b>Received In:</b></label>
										<select class="form-control" name="receive_in_acc" required>
											<option value="">Select receiving account</option>
											<?php
											$query_acc = "SELECT * FROM accounts WHERE del_status = 0 AND acc_type = 1 AND (acc_cat = 3 OR acc_name LIKE '%cash%' OR acc_name LIKE '%bank%') ORDER BY account_no ASC";
											$result_acc = mysqli_query($conn,$query_acc);
											while($data_acc = mysqli_fetch_array($result_acc)){
												?>
												<option value="<?php echo $data_acc['account_no'] ?>"><?php echo $data_acc["account_no"]." - ".$data_acc["acc_name"] ?></option>
												<?php
											}
											?>
										</select>
									</div>

									<div class="form-group col-md-9">
										<label class="control-label"><b>Remarks:</b></label>
									<input type="text" class="form-control" name="remarks" placeholder="Add payment remarks">

										<input type="hidden" value="<?php echo $_SESSION['login_id'] ?>" class="form-control" name="user_id">
									</div>
								</div>


							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#customer_payment').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Save Payment</button>
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
		$('#customer_payment').on('reset',function(){
			$('input:hidden').val('')
		})

		$('#customer_payment').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_customer_payment',
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
							window.open('index.php?page=Customer/customer-payments','_self');
						},1500)

					}else{
						alert_toast(resp,'danger');
					}
				}
			})
		})
		$('.edit_supplier').click(function(){
			start_load()
			var cat = $('#customer_payment')
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

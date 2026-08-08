<?php include('db_connect.php');

if(in_array(5,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid">
		
		<div class="col-lg-12 px-3 mt-3">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="manage_inventory_item">

						<div class="card border-0 shadow-sm mb-3">
							<div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
								<div>
									<h5 class="mb-0 fw-semibold" style="color: var(--accent);">Purchase Order Receipt</h5>
									<small class="text-muted">Add details regarding Purchases.</small>
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-body">
								<input type="hidden" name="ird_id">

								<div class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Supplier:</b></label>
											<select name="supplier_id" id="supplier_id" class="form-control" required="true">
												<option value="">Please Select</option>
												<?php
												$query_supp = "SELECT * FROM suppliers WHERE supp_status = 0";
												$result_supp = mysqli_query($conn,$query_supp);
												while($data_supp = mysqli_fetch_array($result_supp)){
													?>
													<option value="<?php echo $data_supp['supp_id'] ?>"><?php echo $data_supp["supp_name"] ?></option>
													<?php
												}
												?>
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Requisition No:</b></label>
											<select name="requisition_no" id="requisition_no" class="form-control" required="true">
												<option value="">Please Select</option>

											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Received Date:</b></label>
											<input type="date" required="true" class="form-control" name="received_date">
										</div>
									</div>
									<!-- <div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Paid Amount:</b></label>
											<input type="number" placeholder="Amount" required="true" class="form-control" name="paid_amount">
										</div>
									</div> -->
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Document No:</b></label>
											<input type="number" placeholder="Document No" required="true" class="form-control" name="doc_no">
										</div>
									</div>
								</div>
								


								<div>
									<table class="table table-bordered" id="supplier_order_detail_table">
										<tr>
											<th>Plate</th>
											<th>Qty Ordered</th>
											<th>Qty Received</th>
											<th>Qty Remaining</th>
											<th>Qty</th>
											<th>Rate</th>
											<th>Amount</th>
										</tr>
									</table>
								</div>
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-primary "> Save</button>
										<button class="btn btn-default" type="button" onclick="$('#manage_inventory_item').get(0).reset()"> Cancel</button>
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

		$(document).ready(function(){
			$(document).on("keyup",".rate",function() {
				var qty = $(this).closest('tr').find('.quantity').val();
				var rate = $(this).closest('tr').find('.rate').val();
				var amt = 0;
				if(qty != "" && rate != ""){
					amt = qty * rate;
					$(this).closest('tr').find('.amount').val(amt);
				}
			})
			$(document).on("keyup",".quantity",function() {
				var qty = $(this).closest('tr').find('.quantity').val();
				var rate = $(this).closest('tr').find('.rate').val();
				var amt = 0;
				if(qty != "" && rate != ""){
					amt = qty * rate;
					$(this).closest('tr').find('.amount').val(amt);
				}
			})
		});

		$('#supplier_id').change(function(event) {        
			var supplier_id = $(this).val();
			$.ajax({
				url : "ajax-req/ajax_request.php",
				method : "POST",
				data : {supplier_id : supplier_id,req_no: 1},
				dataType : "text",
				success : function(data){
					$('#requisition_no').html(data);
				}
			});
		});

		$('#requisition_no').change(function(event) {        
			var requisition_id = $(this).val();
			$.ajax({
				url : "ajax-req/ajax_request.php",
				method : "POST",
				data : {requisition_id : requisition_id,req_no: 2},
				dataType : "text",
				success : function(data){
					$('.my_tr').remove();
					$('#supplier_order_detail_table').append(data);
				}
			});
		});
		
		
		$('#manage_inventory_item').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_receive_inventory',
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
							// location.reload();							
							window.open('index.php?page=Supplier/receive-inventory','_self');
						},1500)

					}
					else if(resp==2){
						alert_toast("Data successfully updated",'success')
						alert(resp)
						setTimeout(function(){
							// location.reload()
						},1500)

					}else{
						alert(resp)
						// alert_toast(resp,'danger')
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
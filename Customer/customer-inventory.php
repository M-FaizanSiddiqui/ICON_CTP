<?php include('db_connect.php');

if(in_array("15",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-view-page view-customer-inventory">

		<div class="col-lg-12">

			<div class="row">
				<div class="col-md-12">
					<div class="card professional-view-card">
						<div class="row" style="background-color:#f9f9f9">
							<div class="col-lg-6">
								<div class="view-heading"><span class="view-heading-icon"><i class="fa fa-cubes"></i></span><div><h2>Customer Inventory</h2><p>Review plate quantities held for each customer.</p></div></div>
							</div>
							<div class="col-lg-6">
								<a class="btn btn-primary btn-sm float-right m-3" href="index.php?page=Customer/add-cust-inventory" id="new_order">
									<i class="fa fa-plus"></i> New 
								</a>
							</div>
						</div>
						<hr>
						<div class="card-body table-responsive">
							<div class="col-md-3">
								<label><b>Customer:</b></label>
								<select name="customer_id" id="customer_id" class="form-control">
									<option value="">Please Select</option>
									<?php
									$query_cust = "SELECT * FROM customers";
									$result_cust = mysqli_query($conn,$query_cust);
									while($data_cust = mysqli_fetch_array($result_cust)){
										?>
										<option value="<?php echo $data_cust['cust_id'] ?>"><?php echo $data_cust["cust_name"] ?></option>
										<?php
									}
									?>								
								</select>
							</div>
							<br>
							<table class="table table-hover" id="inventory_details">
								<thead>
									<tr>
										<th>Item ID</th>
										<th>Name</th>
										<th>Size</th>
										<th>HL Inches</th>
										<th>Quantity</th>
										<th>Qty Booked</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<!-- Table Panel -->
			</div>
		</div>	


		<script>
			$(document).ready( function() {
				$('table').dataTable( {
					order: [[0, 'desc']]
				});

				$('#customer_id').change(function(event) {
					var cust_id = $(this).val();
					$.ajax({
						url : "ajax-req/ajax_request.php",
						method : "POST",
						data : {cust_id : cust_id,req_no: 3},
						dataType : "text",
						success : function(data){
							$('.my_tr').remove();
							$('.dataTables_empty').hide();
							$('#inventory_details').append(data);
						}
					});
				});
			});

		</script>


		<?php
	}else{
		include 'accessDenied.php';
	}
	?>

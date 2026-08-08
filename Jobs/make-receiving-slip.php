<?php include('db_connect.php');
include 'functions.php';

if(in_array(56,$_SESSION['login_Permisions']))
{
	?>
	<style>
		:root{
			--primary:#0b1324;
			--accent:#ff6a00;
			--bg:#f4f7fc;
			--text:#2b2b2b;
			--border:#e4e7ee;
		}

		body{
			background:var(--bg);
			font-family:'Segoe UI',sans-serif;
			color:var(--text);
		}

		.card{
			border:none;
			border-radius:14px;
			box-shadow:0 10px 30px rgba(0,0,0,0.07);
			overflow:hidden;
		}

		.card-header{
			background:linear-gradient(135deg,#0b1324,#1c2541);
			color:#fff;
			padding:16px 20px;
			font-size:16px;
			font-weight:600;
			letter-spacing:.5px;
		}

		.card-body{
			padding:25px;
		}

		.card-footer{
			background:#fff;
			border-top:1px solid var(--border);
			padding:18px;
		}

		label{
			font-size:13px;
			font-weight:600;
			margin-bottom:6px;
		}

		.form-control{
			height:42px;
			border-radius:10px;
			border:1px solid var(--border);
			font-size:13px;
			transition:.2s;
		}

		.form-control:focus{
			border-color:var(--accent);
			box-shadow:0 0 0 3px rgba(255,106,0,0.15);
		}

		textarea.form-control{
			height:90px;
		}

		#slip_table{
			width:100%;
			border-collapse:separate;
			border-spacing:0;
			margin-top:15px;
			border-radius:12px;
			overflow:hidden;
			background:#fff;
			box-shadow:0 6px 18px rgba(0,0,0,0.05);
		}

		#slip_table th{
			/*background:linear-gradient(135deg,#0b1324,#1c2541);*/
			background:linear-gradient(135deg,#ff6a00,#ff8c42);
			color:#fff;
			padding:5px;
			font-size:13px;
			text-align:center;
			font-weight:600;
		}

		#slip_table tr{
			transition:.2s;
		}

		#slip_table tr:nth-child(even){
			background:#f9fbff;
		}

		#slip_table tr:hover{
			background:#eef4ff;
		}

		#slip_table td{
			padding:2px;
			font-size:13px;
			/*text-align:center;*/
			border-bottom:1px solid var(--border);
		}

		#slip_table input[type="checkbox"]{
			transform:scale(1.2);
			cursor:pointer;
		}

		.btn-success{
			background:linear-gradient(135deg,#ff6a00,#ff8c42);
			border:none;
			padding:10px 25px;
			border-radius:10px;
			font-size:14px;
			font-weight:600;
			transition:.3s;
		}

		.btn-success:hover{
			transform:translateY(-1px);
			box-shadow:0 6px 15px rgba(255,106,0,0.3);
		}

		td{
			vertical-align:middle !important;
		}
	</style>

	<div class="container-fluid professional-form-page">
		<div class="col-lg-12">
			<div class="row">
				<div class="col-md-12">

					<?php 
					if(isset($_POST['make_slip'])){
						mysqli_query($conn,"START TRANSACTION");
						$error = 0;
						$error_message = "";

						$customer_id = mysqli_real_escape_string($conn,$_POST['customer_id']);
						$remarks = mysqli_real_escape_string($conn,$_POST['remarks']);
						$rec_date = mysqli_real_escape_string($conn,$_POST['rec_date']);

						if(trim($customer_id) == ""){
							$error++;
							$error_message .= '<li>Customer cannot be empty</li>';
						}
						if(trim($rec_date) == ""){
							$error++;
							$error_message .= '<li>Date cannot be empty</li>';
						}

						if(count($_POST['slip_select']) == 0){
							$error++;
							$error_message .= '<li>Select at least 1 Job</li>';
						}

						if($error >0){
							?>
							<div class="alert alert-danger">
								<ul><?php echo $error_message; ?></ul>
							</div>
							<?php
						}else{

							$query = "INSERT INTO receiving_slips SET 
							customer_id='$customer_id',
							remarks='$remarks',
							rec_date='$rec_date',
							created_by='".$_SESSION['login_id']."'";

							mysqli_query($conn,$query);
							$slip_id = $conn->insert_id;

							foreach($_POST['slip_select'] as $id){
								mysqli_query($conn,"INSERT INTO receiving_slip_details SET slip_id=$slip_id, job_order_detail_id=$id");
								mysqli_query($conn,"UPDATE job_order_details SET jd_slip_id=$slip_id WHERE id=$id");
							}

							mysqli_query($conn,"COMMIT");
							?>
							<script>
								alert("Saved Successfully");
								window.location='index.php?page=Jobs/receiving-slip';
							</script>
							<?php
						}
					}
					?>
					<div class="container-fluid p-0">
						<form method="POST">

							<div class="card professional-form-card">


								<div class="card-header">
					<span class="form-title-icon"><i class="fa fa-file-alt"></i></span>
									<div class="form-title-copy"><h2>Make Receiving Slip</h2><p>Select completed customer jobs and create a receiving record.</p></div>
								</div>
								<div class="card-body">

									<div class="row">

										<div class="col-md-4 mb-3">
											<label>Customer</label>
											<select name="customer_id" id="customer_id" class="form-control">
											<option value="">Select customer</option>
												<?php
												$res=mysqli_query($conn,"SELECT * FROM customers WHERE cust_status=0");
												while($r=mysqli_fetch_array($res)){
													?>
													<option value="<?= $r['cust_id']?>"><?= $r['cust_name']?></option>
												<?php } ?>
											</select>
										</div>

										<div class="col-md-4 mb-3">
											<label>Date</label>
											<input type="date" name="rec_date" class="form-control" value="<?= date('Y-m-d')?>">
										</div>

										<div class="col-md-12 mb-3">
											<label>Remarks</label>
										<textarea name="remarks" class="form-control" placeholder="Add receiving slip remarks"></textarea>
										</div>

									</div>

									<div class="table-responsive">
										<table id="slip_table">
											<thead>
												<tr>
													<th>Job #</th>
													<th>Job Name</th>
													<th>Qty</th>
													<th>Select</th>
												</tr>
											</thead>
											<tbody></tbody>
										</table>
									</div>

								</div>

								<div class="card-footer text-right">
									<button class="btn btn-success" name="make_slip"><i class="fa fa-save"></i> Save Receiving Slip</button>
								</div>

							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<style>
		.professional-form-page #slip_table{
			width:100%!important;
			margin:0!important;
			border:0!important;
			border-collapse:separate!important;
			border-spacing:0!important;
			border-radius:0!important;
			background:#fff!important;
			box-shadow:none!important;
		}
		.professional-form-page #slip_table thead th{
			padding:12px 14px!important;
			border:0!important;
			border-bottom:1px solid #dfe1e5!important;
			font-size:10px!important;
			font-weight:700!important;
			letter-spacing:.07em!important;
			text-align:left!important;
			text-transform:uppercase!important;
			color:#68696f!important;
			background:#f5f5f6!important;
		}
		.professional-form-page #slip_table thead th:last-child{text-align:center!important}
		.professional-form-page #slip_table tbody tr{background:#fff!important;transition:background .16s ease!important}
		.professional-form-page #slip_table tbody tr:nth-child(even){background:#fbfbfc!important}
		.professional-form-page #slip_table tbody tr:hover{background:#fff7f2!important}
		.professional-form-page #slip_table tbody td{
			padding:12px 14px!important;
			border:0!important;
			border-bottom:1px solid #ececef!important;
			font-size:11px!important;
			color:#4f5055!important;
			background:transparent!important;
		}
		.professional-form-page #slip_table tbody td:first-child{font-weight:600;color:#303033!important}
		.professional-form-page #slip_table tbody td:last-child{text-align:center}
		.professional-form-page #slip_table input[type="checkbox"]{
			width:17px!important;
			height:17px!important;
			margin:0!important;
			accent-color:#f36b21;
			transform:none!important;
			cursor:pointer;
		}
		.professional-form-page #slip_table tbody:empty::after{
			content:'Select a customer to load available jobs';
			display:table-cell;
			padding:28px 14px;
			font-size:11px;
			text-align:center;
			color:#929399;
		}
	</style>

	<script>
		$('#customer_id').change(function(){
			var id=$(this).val();

			$.ajax({
				url:"ajax-req/ajax_request.php",
				method:"POST",
				data:{customer_id:id,req_no:5},
				success:function(data){
					$('#slip_table tbody').html(data);
				}
			});
		});
	</script>

	<?php } else { include 'accessDenied.php'; } ?>

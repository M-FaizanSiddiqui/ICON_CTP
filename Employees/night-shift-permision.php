<?php include('db_connect.php');
?>
<div class="container-fluid professional-payroll-page">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-md-12">
			<div class="card payroll-card">

				<div class="payroll-header night-shift-header">
					<div class="payroll-title"><span class="payroll-title-icon"><i class="fas fa-moon"></i></span><div><h2>Night Shift</h2><p>Manage employee night-shift permissions.</p></div></div>
					<button class="btn btn-primary btn-sm night-shift-add" data-value="" data-toggle="modal" id="supplier_tagged" data-target="#add_perm_model"><i class="fas fa-plus"></i> Add Permission</button>
				</div>

					<div class="card-body night-shift-body">
						<div class="table-responsive night-shift-table-wrap">
						<table class="table table-hover" id="night_shift_permissions_table">
							<thead>
								<tr>
									<th>SR#</th>
									<th>Dated</th>
									<th>Employee</th>
									<th>Reason</th>
									<th>Added By</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$co=0;
								$category = $conn->query("SELECT a.*,b.emp_name,c.name as userName FROM night_shift_perm as a LEFT JOIN employee as b on a.employee_id = b.emp_id INNER JOIN users as c on a.added_by = c.id WHERE a.del_status = 0 order by a.id DESC");
								while($row=$category->fetch_assoc()):
									$employee_id = $row['employee_id'];
									$emp_name = $row['emp_name'];
									$dated = $row['dated'];
									$reason = $row['reason'];
									$userName = $row['userName'];
									$co++;
									?>
									<tr>
										<td class="text-center"><?= $co; ?></td>
										<td><?php echo date('d-M-Y',strtotime($dated)) ?></td>
										<td><?php echo $emp_name ?></td>
										<td><?php echo $reason ?></td>
										<td><?php echo $userName ?></td>
										<td>-</td>
									</tr>

								<?php endwhile; ?>
							</tbody>
						</table>
						</div>
					</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>	


	<style>
		.night-shift-header{display:flex!important;align-items:center;justify-content:space-between;gap:16px}
		.night-shift-add{white-space:nowrap;box-shadow:0 7px 16px rgba(243,107,33,.2)}
		.night-shift-body{padding-top:14px!important}
		.night-shift-table-wrap{overflow:hidden;border:1px solid #e5e6e9;border-radius:11px;background:#fff}
		#night_shift_permissions_table{margin:0!important;border-collapse:separate!important;border-spacing:0}
		#night_shift_permissions_table thead th{border-top:0!important;white-space:nowrap}
		#night_shift_permissions_table tbody td{border-left:0!important;border-right:0!important}
		#night_shift_permissions_table tbody tr:last-child td{border-bottom:0!important}
		#night_shift_permissions_table_wrapper .dataTables_length,#night_shift_permissions_table_wrapper .dataTables_filter{margin-bottom:12px;font-size:10px;color:#73747a}
		#night_shift_permissions_table_wrapper .dataTables_filter input,#night_shift_permissions_table_wrapper .dataTables_length select{height:34px!important;border:1px solid #dfe1e5!important;border-radius:8px!important;background:#fff}
		#night_shift_permissions_table_wrapper .dataTables_info,#night_shift_permissions_table_wrapper .dataTables_paginate{padding-top:12px!important;font-size:10px}
		@media(max-width:575px){.night-shift-header{align-items:flex-start;flex-direction:column}.night-shift-add{width:100%}}
		#add_perm_model .modal-dialog{max-width:520px;margin:7vh auto}#add_perm_model .modal-content{overflow:hidden;border:0;border-radius:15px;box-shadow:0 24px 70px rgba(28,29,32,.24)}#add_perm_model .modal-header{display:flex!important;align-items:center;justify-content:space-between;min-height:68px;padding:15px 18px;border:0;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:#fff!important}.night-modal-heading{display:flex;align-items:center;gap:12px}.night-modal-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812)}.night-modal-heading h4{margin:0;font-size:16px;font-weight:650;color:#303033}.night-modal-heading p{margin:3px 0 0;font-size:10px;color:#898a90}.night-modal-close{display:grid;place-items:center;width:32px;height:32px;padding:0;border:0;border-radius:8px;color:#6f7075;background:#f4f4f5}
		#add_perm_model .modal-body{padding:20px;background:#f7f7f8}.night-permission-form{display:grid;grid-template-columns:1fr 1fr;gap:14px}.night-permission-field.full{grid-column:1/-1}.night-permission-field label{display:block;margin:0 0 7px;font-size:10px;font-weight:700;color:#626369;text-transform:uppercase}.night-permission-field .form-control{height:44px!important;margin:0!important;padding:8px 11px!important;border:1px solid #dfe1e5!important;border-radius:9px!important;font-size:11px;background:#fff}.night-permission-field .form-control:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)!important}.night-permission-note{grid-column:1/-1;padding:10px 12px;border-radius:8px;font-size:9px;color:#7b633e;background:#fff7e8}
		#add_perm_model .modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:13px 18px;border:0;border-top:1px solid #e7e8eb;background:#fff}.night-modal-btn{display:inline-flex;align-items:center;gap:7px;min-height:38px;margin:0!important;padding:8px 14px;border-radius:9px;font-size:11px;font-weight:600}.night-modal-btn.cancel{border:1px solid #dadce0;color:#5f6065;background:#fff}.night-modal-btn.save{border:1px solid #f36b21;color:#fff;background:#f36b21;box-shadow:0 7px 16px rgba(243,107,33,.18)}@media(max-width:575px){#add_perm_model .modal-dialog{margin:12px}.night-permission-form{grid-template-columns:1fr}.night-permission-field.full,.night-permission-note{grid-column:auto}}
	</style>
	<div class="modal fade" id="add_perm_model" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form method="post">
					<div class="modal-header">
						<div class="night-modal-heading"><span class="night-modal-icon"><i class="fas fa-moon"></i></span><div><h4>Add Night Shift Permission</h4><p>Authorize an employee for a specific night shift.</p></div></div>
						<button type="button" class="night-modal-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
					</div>
					<div class="modal-body">
						<div class="night-permission-form"><div class="night-permission-field full"><label for="night_employee_id"><i class="fa fa-user"></i> Employee</label>
						<select id="night_employee_id" name="employee_id" class="form-control" required>
							<option value="">Choose an employee</option>
							<?php
							$query_emp = "SELECT * FROM employee WHERE sal_type_id=1";
							$result_emp = mysqli_query($conn,$query_emp);
							while($data_emp = mysqli_fetch_array($result_emp)){
								?>
								<option value="<?php echo $data_emp['emp_id'] ?>"><?php echo $data_emp["emp_name"] ?></option>
								<?php
							}
							?>
						</select></div><div class="night-permission-field"><label for="night_permission_date"><i class="fa fa-calendar"></i> Shift Date</label><input id="night_permission_date" type="date" class="form-control" name="dated" value="<?= date('Y-m-d') ?>" required></div><div class="night-permission-field"><label for="night_permission_reason"><i class="fa fa-comment"></i> Reason</label><input id="night_permission_reason" type="text" class="form-control" name="reason" placeholder="Enter permission reason" required></div><div class="night-permission-note"><i class="fa fa-info-circle"></i> Attendance adjustments will follow the existing night-shift rules after permission is saved.</div></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn night-modal-btn cancel" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
						<button type="submit" class="btn night-modal-btn save" id="give_permission_btn" name="give_permission_btn"><i class="fa fa-check"></i> Grant Permission</button>
					</div>
				</form>
			</div>
		</div>
	</div>



	<script>
		$(document).ready(function(){
			$('#night_shift_permissions_table').DataTable( {
				order: [[0, 'desc']]
			});
		});

		$(document).on("click","#perm_edit",function() {
			var data=$(this).attr('data-value'); 
			var ans=data.split('^');

			$("#requisition_id").val(ans[0]);
		});
	</script>


	<?php
	if(isset($_POST['give_permission_btn'])){
		mysqli_query($conn,"START TRANSACTION");


		$employee_id = mysqli_real_escape_string($conn,$_POST['employee_id']);
		$dated = mysqli_real_escape_string($conn,$_POST['dated']);
		$reason = mysqli_real_escape_string($conn,$_POST['reason']);

		$queryUpStatus = "INSERT INTO night_shift_perm SET employee_id = ".$employee_id.", dated ='".$dated."', reason = '".$reason."', added_by = ".$_SESSION['login_id'];
		$supp_added = mysqli_query($conn,$queryUpStatus);


		$mythisDate = date('Y-m-d', strtotime($dated. ' + 1 days')); 

		$queryAttCheckIn = "SELECT * FROM attendance where emp_id = ".$employee_id." AND dated = '".$mythisDate."' AND del_status = 0 AND status = 1 order by id DESC limit 1 ";
		$resultCheckIn = mysqli_query($conn,$queryAttCheckIn);
		if(mysqli_num_rows($resultCheckIn)>0){
			$dataChekcIn = mysqli_fetch_array($resultCheckIn);

			$datedOut = $dataChekcIn['dateTime'];
			$timeOut = $dataChekcIn['time'];

			if(date('h:i A',strtotime($datedOut)) >= date('h:i A',strtotime($mythisDate . '05:00:00'))){

				$checkOutTime = '23:59:00';
				$dateTimeCheckOutPre = $dated.' '.$checkOutTime;
				$dateTimeCheckInOv = date('Y-m-d H:i:s', strtotime("$dated $checkOutTime"));

				$reasonEdit = 'Night Shift';

				$queryDelPre = "UPDATE attendance set del_status = 1 WHERE emp_id = ".$employee_id." AND dated = '".$dated."' AND status = 1";
				$query1 = mysqli_query($conn,$queryDelPre);

				$studentQuery1 = "INSERT INTO attendance SET ";
				$studentQuery1 .= " emp_id = ".$employee_id;
				$studentQuery1 .= ", dated ='".$dated."' ";
				$studentQuery1 .= ", time ='".$checkOutTime."' ";
				$studentQuery1 .= ", dateTime ='".$dateTimeCheckOutPre."' ";
				$studentQuery1 .= ", status ='1' ";
				$studentQuery1 .= ", del_reason = ''";
				$studentQuery1 .= ", remarks ='".$reasonEdit."' ";

				$query2 = mysqli_query($conn, $studentQuery1);


				// overtime entry
				$checkInTime = '00:01:00';
				$dateTimeCheckInOv = date('Y-m-d H:i:s', strtotime("$dated $checkInTime"));


				$studentQuery2 = "INSERT INTO attendance SET ";
				$studentQuery2 .= " emp_id = ".$employee_id;
				$studentQuery2 .= ", dated ='".$dated."' ";
				$studentQuery2 .= ", time ='".$checkInTime."' ";
				$studentQuery2 .= ", dateTime ='".$dateTimeCheckInOv."' ";
				$studentQuery2 .= ", status ='4' ";
				$studentQuery2 .= ", del_reason = ''";
				$studentQuery2 .= ", remarks ='".$reasonEdit."' ";

				$query3 = mysqli_query($conn, $studentQuery2);


				$checkInTime = '06:01:00';
				$dateTimeCheckInOv = date('Y-m-d H:i:s', strtotime("$dated $checkInTime"));

				$studentQuery3 = "INSERT INTO attendance SET ";
				$studentQuery3 .= " emp_id = ".$employee_id;
				$studentQuery3 .= ", dated ='".$dated."' ";
				$studentQuery3 .= ", time ='".$checkInTime."' ";
				$studentQuery3 .= ", dateTime ='".$dateTimeCheckInOv."' ";
				$studentQuery3 .= ", status ='5' ";
				$studentQuery3 .= ", del_reason = ''";
				$studentQuery3 .= ", remarks ='".$reasonEdit."' ";

				$query4 = mysqli_query($conn, $studentQuery3);


				$queryDelPreOut = "UPDATE attendance set del_status = 1 WHERE emp_id = ".$employee_id." AND dated = '".$mythisDate."' AND status = 0";
				$query5 = mysqli_query($conn,$queryDelPreOut);

				$checkInTime = '10:01:00';				
				$dateTimeCheckInOv = date('Y-m-d H:i:s', strtotime("$mythisDate $checkInTime"));


				$studentQuery3 = "INSERT INTO attendance SET ";
				$studentQuery3 .= " emp_id = ".$employee_id;
				$studentQuery3 .= ", dated ='".$mythisDate."' ";
				$studentQuery3 .= ", time ='".$checkInTime."' ";
				$studentQuery3 .= ", dateTime ='".$dateTimeCheckInOv."' ";
				$studentQuery3 .= ", status ='0' ";
				$studentQuery3 .= ", del_reason = ''";
				$studentQuery3 .= ", remarks ='".$reasonEdit."' ";

				$query6 = mysqli_query($conn, $studentQuery3);

			}



		}else{
			?>
			<script>
				alert("CheckOut is Missing for dated: <?= $mythisDate ?>");
				window.open('index.php?page=Employees/night-shift-permision','_self');
			</script>
			<?php
		}


		if($supp_added && $query1 && $query2 && $query3 && $query4 && $query5 && $query6){
			mysqli_query($conn,"COMMIT");
			?>
			<script>
				alert("Permission Granted");
				window.open('index.php?page=Employees/night-shift-permision','_self');
			</script>
			<?php
		}else{
			mysqli_query($conn,"ROLLBACK");			
			?>
			<script>

				alert_toast("Error",'danger');
				</script
				<?php
				}
			}
		?>

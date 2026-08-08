<?php include('db_connect.php');

if(in_array("72",$_SESSION['login_Permisions']))
{
	$ref = $_GET['ref'];
	$fetchquery = "SELECT a.*,c.st_type_name FROM salary_slip as a INNER JOIN salary_type as c on a.sp_type_id = c.st_id WHERE md5(a.sp_id) = '".$ref."' ";
	$runQuery = mysqli_query($conn,$fetchquery);
	$i = 0;
	while($dataFetch = mysqli_fetch_array($runQuery)) 
	{
		$i++;
		$sp_type_id = $dataFetch['sp_type_id'];
		$sp_id = $dataFetch['sp_id'];
		$sp_month_year = $dataFetch['sp_month_year'];
		$sp_week_st = $dataFetch['sp_week_st'];
		$sp_week_end = $dataFetch['sp_week_end'];
		$st_type_name = $dataFetch['st_type_name'];
		$periodName = "";
		if($sp_type_id == 1){
			$periodName = date('M-Y',strtotime($sp_month_year));
		}
		else if($sp_type_id == 2){
			$periodName = date('d-M-Y',strtotime($sp_week_st)).' '.date('d-M-Y',strtotime($sp_week_end));
		}
		else if($sp_type_id == 3){
			$periodName = date('M-Y',strtotime($sp_month_year));
		}
	}
	?>
	
	<div class="container-fluid professional-payroll-page">

		<div class="col-lg-12">
			<div class="row">
				<div class="col-md-12">
					<div class="card payroll-card">
						<div class="card-header payroll-header">
							<div class="payroll-title"><span class="payroll-title-icon"><i class="fa fa-file"></i></span><div><h2>Salary Slip Details</h2><p>Employee earnings, deductions, and payable salary.</p></div></div>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-5">
									<h6>Salary Type: <span style="color:blue"><?= $st_type_name ?></span></h6>									
								</div>
								<div class="col-md-5">
									<h6>Salary Period: <span style="color:blue"><?= $periodName ?></span></h6>
								</div>
							</div>
							<table class="table table-bordered">
								<thead>
									<tr class="text-center">
										<th>Emp Name</th>
										<th>Present Days</th>
										<th>Absent Days</th>
										<th>Late/Early</th>
										<th>Overtime Hours</th>
										<th>NWD Hours</th>
										<th>Expected Salary</th>
										<th>Incentive Amt</th>
										<th>Gross Amt</th>
										<th>Slip</th>
									</tr>
								</thead>
								<tbody>
									<?php 
									$i = 1;
									$query_dt ="SELECT a.*,b.emp_name, c.st_type_name FROM salary_slip_info as a INNER JOIN employee as b on a.emp_id = b.emp_id INNER JOIN salary_type as c on a.sal_type_id = c.st_id WHERE a.status = 0 AND slip_id = ".$sp_id." order by a.slip_info_id ASC";
									$order = $conn->query($query_dt);

									$expected_salary_sum = 0;
									$incentive_amt_sum = 0;
									$gross_amt_sum = 0;
									while($row=$order->fetch_assoc()):
										$slip_info_id = $row['slip_info_id'];
										$slip_id = $row['slip_id'];
										$emp_id = $row['emp_id'];
										$emp_name = $row['emp_name'];
										$sal_type_id = $row['sal_type_id'];
										$month_year = $row['month_year'];
										$emp_salary = $row['emp_salary'];
										$total_month_days = $row['total_month_days'];
										$per_day_salary = $row['per_day_salary'];
										$per_hour_salary = $row['per_hour_salary'];
										$present_days = $row['present_days'];
										$absent_days = $row['absent_days'];
										$late_arrival_days = $row['late_arrival_days'];
										$early_departure_days = $row['early_departure_days'];
										$half_days = $row['half_days'];
										$overtime_hours_working_day = $row['overtime_hours_working_day'];
										$overtime_hours_non_working_day = $row['overtime_hours_non_working_day'];
										$overtime_hours_gazated_holiday = $row['overtime_hours_gazated_holiday'];
										$late_early_salary_days_deduct = $row['late_early_salary_days_deduct'];
										$late_early_deduction = $row['late_early_deduction'];
										$half_days_deduction_amt = $row['half_days_deduction_amt'];
										$overtime_amt_working_days = $row['overtime_amt_working_days'];
										$LESS = $row['LESS'];
										$ODS = $row['ODS'];
										$NWDS = $row['NWDS'];
										$expected_salary = $row['expected_salary'];
										$incentive_amt = $row['incentive_amt'];
										$month_gross_amt = $row['month_gross_amt'];
										$expected_salary_sum += $expected_salary;
										$incentive_amt_sum += $incentive_amt;
										$gross_amt_sum += $month_gross_amt;
										?>
										<tr>
											<td style="width: 130px"><?= $emp_name ?></td>
											<td class="text-center"><?= $present_days ?></td>
											<td class="text-center"><?= $absent_days ?></td>
											<td class="text-center"><?= $late_arrival_days + $early_departure_days ?></td>
											<td class="text-center"><?= $overtime_hours_working_day ?></td>
											<td class="text-center"><?= $overtime_hours_non_working_day + $overtime_hours_gazated_holiday ?></td>
											<td class="text-right"><?= number_format($expected_salary) ?></td>
											<td class="text-right"><?= number_format($incentive_amt) ?></td>
											<td class="text-right"><?= number_format($month_gross_amt) ?></td>
											<td class="text-center"><a style="font-size:20px" action="_blank" target="_blank" href="Employees/slip.php?ref=<?= $slip_info_id?>"><i class="fa fa-file"></i></a></td>
										</tr>
									<?php endwhile; ?>

									<tr>
										<td colspan="6" class="text-right"><b>Total</b></td>
										<td class="text-right"><b><?= number_format($expected_salary_sum) ?></b></td>
										<td class="text-right"><b><?= number_format($incentive_amt_sum) ?></b></td>
										<td class="text-right"><b><?= number_format($gross_amt_sum) ?></b></td>
										<td class="text-right"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<!-- Table Panel -->
			</div>
		</div>
	</div>
	<style>

		td{
			vertical-align: middle !important;
		}
		td p{
			margin: unset
		}
		img{
			max-width:100px;
			max-height: :150px;
		}
	</style>

	<script>
		$(document).on("click","#status_btn",function() {
			var data=$(this).attr('data-value'); 
			var ans=data.split('^');

			$("#job_id_TB_status").val(ans[0]);
			$("#job_status_TB").val(ans[1]);
		});


		$('.view_order').click(function(){
			uni_modal("Order  Details","view_order.php?id="+$(this).attr('data-id'),"mid-large")
			
		})
		$('.delete_order').click(function(){
			_conf("Are you sure to delete this order ?","delete_order",[$(this).attr('data-id')])
		})
		function delete_order($id){
			start_load()
			$.ajax({
				url:'ajax.php?action=delete_order',
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
	</script>


	<?php
}else{
	include 'accessDenied.php';
}
?>

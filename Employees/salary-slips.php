<?php include('db_connect.php');

if(in_array("70",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-payroll-page">

		<div class="col-lg-12">
			
			<div class="row">
				<div class="col-md-12">
					<div class="card payroll-card">
						<div class="card-header payroll-header">
							<div class="payroll-title"><span class="payroll-title-icon"><i class="fa fa-file"></i></span><div><h2>Salary Slips</h2><p>Review processed payroll periods and slip status.</p></div></div>
						</div>
						<div class="card-body">


							<div class="row">
								<div class="col-lg-12 portlets">
									<div class="panel"> 
										<div class="panel-content">
											<div class="filter-left">

												<div id="salary_slips_toolbar" class="salary-slips-toolbar">
													<a href="javascript:void(0)" id="salary_slips_export" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true"><span>Export Excel</span></a>
												</div>
												<div class="salary-slip-easyui-wrap">
												<table id="salary_slips_grid" style="width:100%;">
													<thead>
														<tr>
															<th data-options="field:'processed_no',width:110,align:'center'"><b>Processed No</b></th>
															<th data-options="field:'salary_type',width:180,align:'left'"><b>Salary Type</b></th>
															<th data-options="field:'period',width:220,align:'center'"><b>Month / Year</b></th>
															<th data-options="field:'action',width:110,align:'center'"><b>Action</b></th>
														</tr>
													</thead>
												</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>


						</div>
					</div>
				</div>
				<!-- Table Panel -->
			</div>
		</div>

		<style>
			.salary-slip-easyui-wrap{overflow:hidden;border:1px solid #e3e4e7;border-radius:11px;background:#fff}
			.salary-slip-easyui-wrap>.panel{margin:0!important;border:0!important}
			.salary-slip-easyui-wrap .panel-header{padding:10px 13px;border:0;border-bottom:1px solid #e4e5e8;background:#f5f5f6}
			.salary-slip-easyui-wrap .panel-title{color:#55565c;font-size:11px;font-weight:700}
			.salary-slip-easyui-wrap .datagrid-toolbar{padding:7px 9px;border:0;border-bottom:1px solid #e7e8eb;background:#fff}
			.salary-slip-easyui-wrap .datagrid,.salary-slip-easyui-wrap .datagrid-wrap{border:0!important}
			.salary-slip-easyui-wrap .datagrid table{width:auto!important;margin:0!important}
			.salary-slip-easyui-wrap .datagrid table td,.salary-slip-easyui-wrap .datagrid table th{padding:0!important;border-color:#e7e8eb!important;background:inherit!important}
			.salary-slip-easyui-wrap .datagrid-header{border-color:#e1e2e5;background:#f5f5f6!important}
			.salary-slip-easyui-wrap .datagrid-header .datagrid-cell{padding:9px 10px;color:#64656b;font-size:9px;font-weight:700;text-transform:uppercase}
			.salary-slip-easyui-wrap .datagrid-body .datagrid-cell{padding:8px 10px;color:#4e4f55;font-size:10px}
			.salary-slip-easyui-wrap .datagrid-row{height:39px}
			.salary-slip-easyui-wrap .datagrid-row-over,.salary-slip-easyui-wrap .datagrid-row-selected{background:#fff4ec!important}
			.salary-slip-easyui-wrap .pagination{display:flex;align-items:center;margin:0!important;padding:6px 8px;border-top:1px solid #e7e8eb;background:#fafafb}
			.salary-slip-easyui-wrap .pagination-info{color:#7d7e83;font-size:9px}
			.salary-slips-toolbar .l-btn{border:1px solid #f36b21;border-radius:7px;color:#fff;background:#f36b21}
			.salary-slips-toolbar .l-btn:hover{color:#fff;background:#df5b16}
			.modal-header {
				display: block !important;
			}
			.bg-warning{
				background-color: #e5812f !important;
			}
		</style>


		<div class="modal fade" id="status_modal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form method="post">
						<div class="modal-header bg-warning">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-window-close" aria-hidden="true"></i>
							</button>
							<h4 class="modal-title"><strong style="color: white">Status</strong></h4>
						</div>
						<div class="modal-body">
							<span><b>Status:</b></span>
							<select class="form-control" name="job_status_TB" id="job_status_TB">
								<option value="">Please Select</option>
								<option value="0">Pending</option>
								<option value="1">On Machine</option>
								<option value="2">Completed</option>							
							</select>
							<input type="hidden" name="job_id_TB_status" id="job_id_TB_status">
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary btn-embossed" id="change_status" name="change_status">Yes</button>
							<button type="button" class="btn btn-default btn-embossed" data-dismiss="modal">No</button>
						</div>
					</form>
				</div>
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

		$(document).ready(function() {
			var salaryGrid = $('#salary_slips_grid');
			salaryGrid.datagrid({
				title: 'Salary Processes',
				url: 'Employees/data_slips.php',
				method: 'post',
				toolbar: '#salary_slips_toolbar',
				singleSelect: true,
				fitColumns: true,
				rownumbers: false,
				remoteSort: true,
				nowrap: false,
				autoRowHeight: false,
				pagination: true,
				pageNumber: 1,
				pageSize: 10,
				pageList: [10,20,30,40,50,100,200],
				striped: true,
				emptyMsg: 'No salary slips found.',
				onLoadSuccess: function(data) {
					salaryGrid.datagrid('resize');
				},
				onLoadError: function() {
					alert_toast('Unable to load salary slips. Please try again.','danger');
				}
			});

			$('#salary_slips_export').off('click.salarySlips').on('click.salarySlips', function(){
				salaryGrid.datagrid('toExcel','salary_slips.xls');
			});
		});


		
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

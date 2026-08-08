<?php include('db_connect.php');

if(in_array("57",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid">

		<div class="col-lg-12">
			
			<div class="row mt-4">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header">
							<h5>Receiving Slip</h5>
							<small class="color-gray">All Receiving Slips aer listed Below</small>
						</div>
					</div>
					<br>
					<div class="card">
						<div class="card-body">


							<div class="row">
								<div class="col-lg-12 portlets">
									<div class="panel"> 
										<div class="panel-content">
											<div class="filter-left">

												<div id="tb">
													<a id="export_datagrid" class="easyui-linkbutton" data-options="iconCls:'icon-save',text:'Excel',plain:true"></a>
												</div>

												<?php $user_id = "1"; ?>

												<table id="dg" title="Receiving Slips" style="width: 100%;margin: auto;" data-options="singleSelect:true,fitColumns:true,rownumbers:false,remoteSort:true,remoteFilter:true,clientPaging:false,nowrap:false,autoRowHeight:false,method:'POST',url:'Jobs/data_slips.php'" pagination="true" pageSize="10" pageList="[10,20,30,40,50,100,200]">

													<thead>
														<tr>
															<th style="width: 90px" data-options="field:'slip_no',align:'center'"><b>Slip No</b></th>
															<th style="width: 150px" data-options="field:'customer',align:'left'"><b>Customer</b></th>
															<th style="width: 150px" data-options="field:'rec_date',align:'center'"><b>Rec Date</b></th>
															<th style="width: 150px" data-options="field:'job_no',align:'center'"><b>Job No</b></th>
															<th style="width: 150px" data-options="field:'action',align:'center'"><b>Action</b></th>
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
				<!-- Table Panel -->
			</div>
		</div>

		<style>
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
								<option value="3">Plate Setting</option>
								<option value="1">On Machine</option>
								<option value="4">Plate Washing</option>
								<option value="5">Oven Baking</option>
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

			var dg = $('#dg');
			dg.datagrid({
				toolbar: '#tb',
			});

			$('#export_datagrid').click(function(){
				$('#dg').datagrid('toExcel','receivable_report.xls');
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

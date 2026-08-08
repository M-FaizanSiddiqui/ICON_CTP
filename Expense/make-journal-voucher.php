<?php include('db_connect.php');

if(in_array(67,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page">
		
		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">

					<form id="make-journal-voucher" method="POST" enctype="multipart/form-data">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fa fa-book"></i></span>
								<div class="form-title-copy"><h2>Journal Voucher</h2><p>Record balanced debit and credit entries.</p></div>
							</div>
							<div class="card-body">
								<input type="hidden" name="cust_id">
								<div class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Trans Date:</b></label>
											<input type="date" class="form-control" name="trans_date" aria-label="Transaction date">
										</div>
									</div>

									<!-- <div class="col-md-12">
										<button type="button" class="btn btn-sm btn-primary addBtn">
											Add <i class="fa fa-plus"></i>
										</button>
									</div> -->

									<div class="col-md-12 mt-2 table-responsive">
										<table class="table table-bordered" id="voucher_table">
											<tr class="text-center">
												<th>Account</th>
												<th>Debit Amount</th>
												<th>Credit Amount</th>
												<th>Action</th>
											</tr>

											<?php
											function test($thi_id,$conn){
												$output = '';
												$acc2 = "SELECT * FROM accounts WHERE parent_id = '".$thi_id."'";
												$que2 = mysqli_query($conn,$acc2);
												while($data2 = mysqli_fetch_array($que2))
												{
													$output .= '<option value="'.$data2['account_no'].'">'.$data2['acc_name'].'</option>';
													$output .= test($data2['account_no'],$conn);
												}
												return $output;
											}
											$accountList = '<select class="form-control account_id my_select2" name="account_id[]">';
											$accountList .= '<option value="0">Select Account</option>';
											$acc1 = "SELECT * FROM accounts WHERE parent_id = 0 ";
											$que1 = mysqli_query($conn,$acc1);
											while($data1 = mysqli_fetch_array($que1))
											{
												$this_id = $data1['account_no'];
												$accountList .= '<optgroup label="'.$data1['acc_name'].'">';
												$accountList .= test($this_id,$conn);												
											}

											$accountList .= '</select>';

											$debitField = '<input placeholder="Debit Amt" value="0" class="form-control debit_amt" name="debit_amt[]">';
											$creditField = '<input placeholder="Credit Amt" value="0" class="form-control credit_amt" name="credit_amt[]">';
											$actionField = '<button type="button" class="btn btn-sm delBtn"> <i class="fa fa-trash"></i> </button>';
											?>

											<tr>
												<td><?= $accountList ?></td>
												<td><?= $debitField ?></td>
												<td><?= $creditField ?></td>
												<td class="text-center"></td>
											</tr>

											<tr>
												<td><?= $accountList ?></td>
												<td><?= $debitField ?></td>
												<td><?= $creditField ?></td>
												<td class="text-center"></td>
											</tr>
										</table>
									</div>

									<div class="col-md-12 mt-2">
										<label><b>Narration</b></label>
										<br>
										<textarea name="narration" id="narration" class="form-control" placeholder="Describe the purpose of this journal voucher"></textarea>
									</div>
								</div>								
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#make-journal-voucher').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Save Voucher</button>
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
		$('#make-journal-voucher').on('reset',function(){
			$('input:hidden').val('')
		})
		$(document).ready(function () { 
			$('.addBtn').click(function(event) {
				var acc = '<?= $accountList ?>';
				var deb = '<?= $debitField ?>';
				var cred = '<?= $creditField ?>';
				var action = '<?= $actionField ?>';

				var tab = '<tr>';
				tab += '<td>'+acc+'</td>';
				tab += '<td>'+deb+'</td>';
				tab += '<td>'+cred+'</td>';
				tab += '<td class="text-center">'+action+'</td>';
				tab += '</tr>';

				$('#voucher_table tr:last').after(tab);
			});


			$(document).on("click",".delBtn",function() {
				$(this).closest('tr').remove();
			});

		});



		$('#make-journal-voucher').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_journal_voucher',
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
							window.open('index.php?page=Expense/journal-voucher','_self');
						},1500)

					}else{
						alert_toast("Error Occured: "+resp,'danger');
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

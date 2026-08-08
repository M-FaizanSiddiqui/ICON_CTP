<?php include('db_connect.php');

$module_for_active = 2;
if(in_array("2",$_SESSION['login_Permisions']))
{
	?>
	<style>
		:root{
			--primary: #0b1324;
			--sidebar: #0f1b2d;
			--accent: #ff6a00;
			--bg: #f4f6fb;
			--text: #1f2937;
			--muted: #6b7280;
		}


		body{
			background: var(--bg);
		}


		.card{
			border: none;
			border-radius: 16px;
			box-shadow: 0 6px 18px rgba(0,0,0,0.06);
		}


		.card-header{
			background: var(--primary);
			color: #fff;
			font-weight: 600;
			border-left: 5px solid var(--accent);
		}


		.pro-table{
			width: 100%;
			border-collapse: separate;
			border-spacing: 0 10px;
		}


		.pro-table thead th{
			background: #fff;
			color: var(--muted);
			font-size: 12px;
			text-transform: uppercase;
			border: none;
			padding: 12px;
		}


		.pro-table tbody tr{
			background: #fff;
			border-radius: 12px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.04);
			transition: 0.2s;
		}

		.pro-table tbody tr:hover{
			transform: translateY(-2px);
			border-left: 4px solid var(--accent);
		}


		.pro-table td{
			padding: 12px;
			vertical-align: middle;
		}


		.code-badge{
			background: rgba(255,106,0,0.12);
			color: var(--accent);
			padding: 4px 10px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 600;
		}


		.status-active{
			color: #16a34a;
			font-weight: 600;
		}

		.status-inactive{
			color: #dc2626;
			font-weight: 600;
		}


		.btn-primary{
			background: var(--accent);
			border: none;
			border-radius: 10px;
			font-weight: 600;
		}

		.btn-primary:hover{
			background: #e85f00;
			transform: scale(1.02);
		}


		.text-muted{
			color: var(--muted) !important;
		}

		.info-card{
			background: white;
			border-radius: 15px;
			padding: 15px;
		}
	</style>
	<style>
		.supplier-directory-page{max-width:1280px;margin:0 auto;padding:0 0 28px!important}
		.supplier-page-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:18px}
		.supplier-page-heading h1{margin:0;font-size:22px;font-weight:650;letter-spacing:-.02em;color:#303033}
		.supplier-page-heading p{margin:5px 0 0;font-size:12px;color:#85868c}
		.supplier-stats{margin:0 -7px 18px!important}
		.supplier-stats>[class*="col-"]{padding:0 7px}
		.supplier-directory-page .info-card{display:flex;align-items:center;gap:14px;min-height:92px;padding:16px 18px;border:1px solid #e9eaed;border-radius:13px;background:#fff;box-shadow:0 7px 24px rgba(45,45,49,.055)}
		.stat-icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:12px;font-size:17px;color:#f36b21;background:#fff0e8}
		.info-card.success .stat-icon{color:#27865a;background:#eaf8f1}
		.info-card.danger .stat-icon{color:#cb5050;background:#fff0f0}
		.stat-copy{min-width:0}.stat-copy h6{margin:0 0 4px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8a8b90}.stat-copy h3{margin:0;font-size:24px;font-weight:650;color:#303033!important}
		.supplier-directory-card{overflow:hidden!important;margin:0!important;border:1px solid #e7e8eb!important;border-radius:15px!important;background:#fff;box-shadow:0 10px 34px rgba(43,43,47,.07)!important}
		.supplier-directory-card>.card-header{display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:72px;padding:14px 18px!important;border:0!important;border-bottom:1px solid #ececef!important;border-left:4px solid #f36b21!important;color:#303033!important;background:#fff!important}
		.directory-title{display:flex;align-items:center;gap:12px}.directory-title-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812);box-shadow:0 7px 16px rgba(243,107,33,.2)}
		.directory-title h2{margin:0;font-size:16px;font-weight:600;color:#303033}.directory-title p{margin:3px 0 0;font-size:11px;color:#8b8c92}
		.supplier-directory-card .btn-primary{display:inline-flex;align-items:center;gap:7px;min-height:38px;padding:8px 14px;border-radius:9px!important;font-size:11px;background:#f36b21!important;box-shadow:0 7px 16px rgba(243,107,33,.18)}
		.supplier-table-wrap{padding:0 18px 18px}
		.supplier-directory-page .dataTables_wrapper{padding-top:16px}
		.supplier-directory-page .dataTables_length,.supplier-directory-page .dataTables_filter{margin-bottom:14px;font-size:11px;color:#74757b}
		.supplier-directory-page .dataTables_filter label,.supplier-directory-page .dataTables_length label{display:flex;align-items:center;gap:8px;font-weight:500}
		.supplier-directory-page .dataTables_filter input,.supplier-directory-page .dataTables_length select{height:36px!important;margin:0!important;padding:7px 10px;border:1px solid #dfe1e5!important;border-radius:8px!important;outline:0;background:#fff}
		.supplier-directory-page .dataTables_filter input{width:220px}.supplier-directory-page .dataTables_filter input:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)}
		.supplier-directory-page .pro-table{width:100%!important;margin:0!important;border-collapse:separate;border-spacing:0}
		.supplier-directory-page .pro-table thead th{padding:11px 12px;border-top:1px solid #e7e8eb!important;border-bottom:1px solid #e7e8eb!important;font-size:9px;font-weight:700;letter-spacing:.08em;color:#73747a;background:#f6f6f7}
		.supplier-directory-page .pro-table thead th:first-child{border-left:1px solid #e7e8eb;border-radius:9px 0 0 9px}.supplier-directory-page .pro-table thead th:last-child{border-right:1px solid #e7e8eb;border-radius:0 9px 9px 0}
		.supplier-directory-page .pro-table tbody tr{box-shadow:none;transition:background .16s}.supplier-directory-page .pro-table tbody tr:hover{transform:none;background:#fff9f5}
		.supplier-directory-page .pro-table tbody td{padding:13px 12px;border-bottom:1px solid #eeeeef;font-size:11px;color:#505156;background:transparent}
		.supplier-name{font-size:12px;font-weight:600;color:#303033}.contact-primary{font-weight:500;color:#4a4b50}.location-cell{max-width:250px;color:#707177!important}
		.code-badge{display:inline-flex;padding:5px 9px;border-radius:7px;font-size:10px;color:#df5913;background:#fff0e8}
		.status-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:20px;font-size:10px;font-weight:600}.status-badge::before{content:'';width:6px;height:6px;border-radius:50%}.status-badge.active{color:#257b53;background:#eaf7f0}.status-badge.active::before{background:#32a56f}.status-badge.inactive{color:#b74949;background:#fff0f0}.status-badge.inactive::before{background:#dc5b5b}
		.supplier-directory-page .dataTables_info{padding-top:15px!important;font-size:10px;color:#85868b}.supplier-directory-page .dataTables_paginate{padding-top:10px!important}.supplier-directory-page .paginate_button{min-width:32px!important;height:32px;padding:7px 10px!important;border:0!important;border-radius:7px!important;font-size:10px}.supplier-directory-page .paginate_button.current{color:#fff!important;background:#f36b21!important}
		@media(max-width:767px){.supplier-directory-page{padding:0 0 20px!important}.supplier-page-heading{align-items:flex-start}.supplier-stats>[class*="col-"]{margin-bottom:10px}.supplier-directory-card>.card-header{align-items:flex-start;flex-direction:column}.supplier-table-wrap{padding:0 12px 14px}.supplier-directory-page .dataTables_filter{float:none;text-align:left}.supplier-directory-page .dataTables_filter label{align-items:flex-start;flex-direction:column}.supplier-directory-page .dataTables_filter input{width:100%}}
	</style>

	<div class="container-fluid supplier-directory-page">
		<div class="supplier-page-heading">
			<div><h1>Suppliers</h1><p>Manage supplier profiles, contact details, and account status.</p></div>
		</div>


		<div class="row supplier-stats">
			<div class="col-md-4">
				<div class="info-card">
					<span class="stat-icon"><i class="fa fa-truck"></i></span><div class="stat-copy"><h6>Total Suppliers</h6>
					<h3>
						<?php 
						echo $conn->query("SELECT COUNT(*) as c FROM suppliers")->fetch_assoc()['c'];
						?>
					</h3></div>
				</div>
			</div>

			<div class="col-md-4">
				<div class="info-card success">
					<span class="stat-icon"><i class="fa fa-check-circle"></i></span><div class="stat-copy"><h6>Active Suppliers</h6>
					<h3 class="text-info">
						<?php 
						echo $conn->query("SELECT COUNT(*) as c FROM suppliers WHERE supp_status=0")->fetch_assoc()['c'];
						?>
					</h3></div>
				</div>
			</div>

			<div class="col-md-4">
				<div class="info-card danger">
					<span class="stat-icon"><i class="fa fa-pause-circle"></i></span><div class="stat-copy"><h6>Inactive Suppliers</h6>
					<h3 class="text-danger">
						<?php 
						echo $conn->query("SELECT COUNT(*) as c FROM suppliers WHERE supp_status=1")->fetch_assoc()['c'];
						?>
					</h3></div>
				</div>
			</div>

		</div>



		<div class="card supplier-directory-card">
			<div class="card-header">
				<div class="directory-title">
					<span class="directory-title-icon"><i class="fa fa-address-book"></i></span>
					<div><h2>Supplier Directory</h2><p>All registered suppliers and their current status.</p></div>
				</div>

				<a href="index.php?page=Supplier/add-supplier" class="btn btn-primary btn-sm px-3">
					<i class="fa fa-plus"></i> New Supplier
				</a>
			</div>
			<div class="table-responsive supplier-table-wrap">
			<table class="table pro-table mb-0">
				<thead>
					<tr>
						<th>Code</th>
						<th>Supplier Info</th>
						<th>Contact</th>
						<th>Location</th>
						<th>Status</th>
						<th>Created</th>
					</tr>
				</thead>

				<tbody>

					<?php
					$category = $conn->query("SELECT * FROM suppliers ORDER BY supp_id DESC");

					while($row=$category->fetch_assoc()):
						?>
						<tr>
							<td>
								<span class="code-badge">
									SUP-<?php echo str_pad($row['supp_id'],4,'0',STR_PAD_LEFT); ?>
								</span>
							</td>

							<td>
								<div class="supplier-name">
									<?php echo $row['supp_name']; ?>
								</div>
								<small class="text-muted">
									Supplier ID: #<?php echo $row['supp_id']; ?>
								</small>
							</td>

							<td>
								<div class="contact-primary"><?php echo $row['supp_email'] ?: 'No email provided'; ?></div>
								<small class="text-muted">
									<?php echo $row['supp_ph_no']; ?>
								</small>
							</td>

							<td class="location-cell">
								<?php echo $row['supp_address']; ?>
							</td>

							<td>
								<?php if($row['supp_status']==0){ ?>
									<span class="status-badge active">Active</span>
								<?php } else { ?>
									<span class="status-badge inactive">Inactive</span>
								<?php } ?>
							</td>

							<td>
								<small class="text-muted">
									<?= date("d M Y", strtotime($row['supp_creation_time'])) ?>
								</small>
							</td>

						</tr>

					<?php endwhile; ?>
				</tbody>
			</table>
			</div>
		</div>
	</div>

<script>

	$(document).ready(function(){
		$('table').dataTable( {
			order: [[0, 'desc']]
		});
	})

	$('.edit_supplier').click(function(){
		start_load()
		var cat = $('#manage-supplier')
		cat.get(0).reset()
		cat.find("[name='supp_id']").val($(this).attr('data-id'))
		cat.find("[name='supp_name']").val($(this).attr('data-name'))
		cat.find("[name='supp_ph_no']").val($(this).attr('data-ph-no'))
		cat.find("[name='supp_email']").val($(this).attr('data-email'))
		cat.find("[name='supp_address']").val($(this).attr('data-address'))
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
			data:{supp_id:$id},
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

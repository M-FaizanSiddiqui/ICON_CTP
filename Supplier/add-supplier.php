<?php include('db_connect.php');

if(in_array(3,$_SESSION['login_Permisions']))
{
	?>
	<style>
		:root{
			--primary: #0b1324;     /* main dark */
			--sidebar: #0f1b2d;
			--accent: #ff6a00;      /* orange brand color */
			--text: #ffffff;
			--muted: #aab4c5;
			--bg: #f4f6fb;
		}

/* PAGE */
body{
	background: var(--bg);
}

/* CARD */
.supplier-card{
	border: none;
	border-radius: 16px;
	background: #fff;
	box-shadow: 0 6px 20px rgba(0,0,0,0.06);
}

/* HEADER (brand match) */
.supplier-card .card-header{
	background: linear-gradient(135deg, #0f172a, #1e293b);

	color: #fff;
	font-weight: 600;
	border-left: 5px solid var(--accent);
}

/* INPUT FOCUS */
.form-control:focus{
	border-color: var(--accent);
	box-shadow: 0 0 0 3px rgba(255,106,0,0.15);
}

/* LABELS */
.form-group label{
	font-weight: 600;
	font-size: 13px;
	color: #333;
}

/* PRIMARY BUTTON */
.btn-primary{
	background: var(--accent);
	border: none;
	border-radius: 10px;
	font-weight: 600;
}

.btn-primary:hover{
	background: #e85f00;
	transform: translateY(-1px);
}

/* CANCEL */
.btn-default{
	background: #e5e7eb;
	border-radius: 10px;
	border: none;
}
</style>
<div class="container-fluid professional-form-page supplier-form-page">
	<div class="row justify-content-center">
		<div class="col-lg-12">
			<form action="" id="manage-supplier">
				<div class="card supplier-card professional-form-card">
					<div class="card-header">
						<span class="form-title-icon"><i class="fa fa-truck"></i></span>
						<div class="form-title-copy"><h2>Add New Supplier</h2><p>Create a supplier profile and contact record.</p></div>
					</div>
					<div class="card-body master-form-fields">
						<input type="hidden" name="supp_id">
						<div class="form-group mb-3">
							<label>Supplier Name</label>
							<input type="text" class="form-control" name="supp_name" placeholder="Enter supplier name" required="true">
						</div>
						<div class="form-group mb-3">
							<label>Email Address</label>
							<input type="email" class="form-control" name="supp_email" placeholder="example@mail.com">
						</div>
						<div class="form-group mb-3">
							<label>Phone Number</label>
							<input type="text" class="form-control" name="supp_ph_no" placeholder="+92XXXXXXXXXX">
						</div>

						<div class="form-group mb-2">
							<label>Address</label>
							<input type="text" class="form-control" name="supp_address" placeholder="Full address">
						</div>
					</div>

					<div class="card-footer d-flex justify-content-end gap-2">
						<button class="btn btn-primary">
							<i class="fa fa-save"></i> Save Supplier
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	$('#manage-supplier').on('reset',function(){
		$('input:hidden').val('')
	})

	$('#manage-supplier').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_supplier',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			type: 'POST',
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully added",'success');
					setTimeout(function(){
							// location.reload()							
						window.open('index.php?page=Supplier/view-supplier','_self');
					},1500)

				}
				else if(resp==2){
					alert_toast("Data successfully updated",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}else{
					alert(resp);
				}
			}
		})
	})
	$('.edit_supplier').click(function(){
		start_load()
		var cat = $('#manage-supplier')
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
					alert_toast("Data successfully deleted",'success');
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
	$('table').dataTable()
</script>

<style>
	.supplier-form-page{max-width:1080px}
	.supplier-form-page .professional-form-card>.card-body{padding:28px!important}
	.supplier-form-page .master-form-fields{gap:2px 24px}
	@media(max-width:768px){
		.supplier-form-page .professional-form-card>.card-body{padding:18px!important}
	}
</style>


<?php
}else{
	include 'accessDenied.php';
}
?>

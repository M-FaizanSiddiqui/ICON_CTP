<?php include('db_connect.php');

if(in_array("54",$_SESSION['login_Permisions']))
{
	?>
	<style>
		.permission-role-bar{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0 16px}.role-chip{border:1px solid #ffd6c1;background:#fff4ed;color:#d95613;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer}.role-chip:hover,.role-chip.active{background:#f36b21;color:#fff}.perm-check{width:18px;height:18px;accent-color:#f36b21}.perm-parent-row td{background:#fff4ed!important;color:#d95613!important;font-weight:700}.perm-child-name{padding-left:26px!important}.perm-actions{display:flex;gap:9px;justify-content:flex-end;flex-wrap:wrap}
	</style>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-user-shield"></i></span><div><h1>Module Permissions</h1><p>Assign permissions quickly with role templates or fine tune modules manually.</p></div></div></div></div>

		<div class="col-lg-12">
			<form action="" id="manage-modules">
				<div class="row">
					<div class="col-md-12">
						<div class="card icon-card">
							<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-key"></i></span><div><h3>Permission Setup</h3><p>Select user, apply a role template, then save.</p></div></div></div>
							<div class="card-body icon-card-body">
								<div class="row">
									<?php
									$modUserPer = "0,";
									$ref="";
									if(isset($_GET['ref'])){
										$ref = icon_get_int('ref');
										$queryPer = "SELECT * FROM module_permision WHERE user_id = ".$ref;
										$resultPer = mysqli_query($conn,$queryPer);
										while($dataPer = mysqli_fetch_array($resultPer)){
											$modUserPer .= $dataPer['mod_id'].',';
										}
										$modUserPer = trim($modUserPer,',');
									}
									?>
									<div class="col-md-10">
										<label><b>Select User:</b></label>
										<select name="user_id" class="form-control" id="user_id" required="true">
											<option value="">Please Select</option>
											<?php 
											$query = "SELECT * FROM users";
											$result = mysqli_query($conn,$query);
											while($data = mysqli_fetch_array($result)){
												$selectedVal="";
												if($ref == $data['id']){
													$selectedVal = "selected";
												}
												?>
												<option <?php echo $selectedVal ?> value="<?= $data['id'] ?>"><?= $data['name'] ?></option>
												<?php
											}
											?>

										</select>
									</div>
									<div class="col-md-2">
										<button class="icon-btn icon-btn-primary mt-4" type="button" id="filterBtn"><i class="fa fa-search"></i> Search</button>
									</div>
								</div>

								<div class="permission-role-bar">
									<button type="button" class="role-chip" data-role="admin">Admin</button>
									<button type="button" class="role-chip" data-role="accounts">Accounts</button>
									<button type="button" class="role-chip" data-role="sales">Sales</button>
									<button type="button" class="role-chip" data-role="inventory">Inventory</button>
									<button type="button" class="role-chip" data-role="hr">HR</button>
									<button type="button" class="role-chip" data-role="clear">Clear All</button>
								</div>
								<div class="icon-table-wrap">
								<table class="table icon-table">
									<thead>
										<tr style="text-align: center;">
											<th>ID</th>
											<th>Parent Module</th>
											<th>Icon</th>
											<th>Ordering</th>
											<th>Permission</th>
										</tr>
									</thead>
									<tbody>
										<?php

										$category = $conn->query("SELECT * FROM modules_1 WHERE m_parent_id = 0 order by ordering asc");
										while($row=$category->fetch_assoc()){
											$mPer = 0;
											if(in_array($row['m_id'],explode(",",$modUserPer))){
												$mPer=1;
											}
											?>
											<tr class="perm-parent-row">
												<td class="text-center"><?php echo $row['m_id'] ?></td>
												<td class=""><?php echo $row['m_name'] ?></td>
												<td><?php echo $row['fav_icon'] ?></td>
												<td><?php echo $row['ordering'] ?></td>
												
												<?php
												if($mPer == 0){
													?>
													<td>
														<input type="hidden" name="module_id[<?php echo $row['m_id'] ?>][]" value="<?php echo $row['m_id'] ?>">
														<input type="checkbox" name="permission[<?php echo $row['m_id'] ?>][]" class="perm-check module-permission" data-module="<?php echo $row['m_id'] ?>">
													</td>
													<?php
												}else{
													?>
													<td>
														<input type="hidden" name="module_id[<?php echo $row['m_id'] ?>][]" value="<?php echo $row['m_id'] ?>">
														<input type="checkbox" checked name="permission[<?php echo $row['m_id'] ?>][]" class="perm-check module-permission" data-module="<?php echo $row['m_id'] ?>">
													</td>
													<?php
												}
												?>											
											</tr>

											<?php
											$sub_mod = $conn->query("SELECT * FROM modules_1 WHERE m_parent_id = ".$row['m_id']." order by ordering asc");
											while($row_sub=$sub_mod->fetch_assoc()){
												$mPer = 0;
												if(in_array($row_sub['m_id'],explode(",",$modUserPer))){
													$mPer=1;
												}
												?>
												<tr>
													<td class="text-center"><?php echo $row_sub['m_id'] ?></td>
													<td class="perm-child-name"><?php echo $row_sub['m_name'] ?></td>
													<td><?php echo $row_sub['fav_icon'] ?></td>
													<td><?php echo $row_sub['ordering'] ?></td>
													
													<?php
													if($mPer == 0){
														?>
														<td>
															<input type="hidden" name="module_id[<?php echo $row_sub['m_id'] ?>][]" value="<?php echo $row_sub['m_id'] ?>">
															<input type="checkbox" name="permission[<?php echo $row_sub['m_id'] ?>][]" class="perm-check module-permission" data-module="<?php echo $row_sub['m_id'] ?>">
														</td>
														<?php
													}else{
														?>
														<td>
															<input type="hidden" name="module_id[<?php echo $row_sub['m_id'] ?>][]" value="<?php echo $row_sub['m_id'] ?>">
															<input type="checkbox" checked name="permission[<?php echo $row_sub['m_id'] ?>][]" class="perm-check module-permission" data-module="<?php echo $row_sub['m_id'] ?>">
														</td>
														<?php
													}
													?>
												</tr>
												<?php
											}
										}
										?>
									</tbody>
								</table>
								</div>
							</div>
						</div>

						<div class="card-footer">
							<div class="row">
								<div class="col-md-12 perm-actions">
									<button class="icon-btn icon-btn-primary"><i class="fa fa-save"></i> Save Permissions</button>
									<button class="icon-btn icon-btn-soft" type="button" onclick="$('#manage-modules').get(0).reset()"><i class="fa fa-undo"></i> Cancel</button>
								</div>
							</div>
						</div>
					</div>
					<!-- Table Panel -->
				</div>

			</form>
		</div>	

		<script>
			$('#filterBtn').click(function(){
				var user = $('#user_id').val();
				location.replace('index.php?page=Modules/permissions&ref='+user)
			});
			const roleTemplates = {
				admin: 'all',
				accounts: [34,35,36,37,38,39,40,42,43,44,45,46,48,66,67,68,69,70,71,73,78,79,80],
				sales: [2,6,7,8,11,12,13,14,15,29,30,31,41,42,43,44,46,48],
				inventory: [3,4,5,11,12,13,20,21,22,23,24,25,26,27,28,74],
				hr: [59,60,61,62,63,64,65,72,75,76,77]
			};
			$('.role-chip').click(function(){
				const role = $(this).data('role');
				$('.role-chip').removeClass('active');
				$(this).addClass('active');
				$('.module-permission').prop('checked', false);
				if(role === 'clear'){ return; }
				if(roleTemplates[role] === 'all'){
					$('.module-permission').prop('checked', true);
					return;
				}
				(roleTemplates[role] || []).forEach(function(id){
					$('.module-permission[data-module="'+id+'"]').prop('checked', true);
				});
			});

			$('#manage-modules').submit(function(e){
				e.preventDefault()
				start_load()
				$.ajax({
					url:'ajax.php?action=save_module_permissions',
					data: new FormData($(this)[0]),
					cache: false,
					contentType: false,
					processData: false,
					method: 'POST',
					type: 'POST',
					success:function(resp){
						if(resp == 1){
							alert_toast("Data successfully added",'success')
							setTimeout(function(){
								window.open('index.php?page=Modules/permissions','_self');
							},1500)

						}else{
							alert_toast("Error Occured",'danger');
						}
					}
				});
			});
		</script>

		<?php
	}else{
		include 'accessDenied.php';
	}
	?>

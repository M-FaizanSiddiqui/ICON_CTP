<?php include('db_connect.php');

if(in_array("54",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid">

		<div class="col-lg-12">
			<form action="" id="manage-modules">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="row" style="background-color:#f9f9f9">
								<div class="col-lg-6">
									<h2 class="pt-2">Module Permission</h2>
								</div>
							</div>
							<hr>
							<div class="card-body">
								<div class="row">
									<?php
									$modUserPer = "0,";
									$ref="";
									if(isset($_GET['ref'])){
										$ref = $_GET['ref'];
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
										<button class="btn btn-sm btn-primary mt-4" type="button" id="filterBtn">Search</button>
									</div>
								</div>

								<br>
								<table class="table table-bordered">
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
											<tr style="color: blue; font-weight: bold">
												<td class="text-center"><?php echo $row['m_id'] ?></td>
												<td class=""><?php echo $row['m_name'] ?></td>
												<td><?php echo $row['fav_icon'] ?></td>
												<td><?php echo $row['ordering'] ?></td>
												
												<?php
												if($mPer == 0){
													?>
													<td>
														<input type="hidden" name="module_id[<?php echo $row['m_id'] ?>][]" value="<?php echo $row['m_id'] ?>">
														<input type="checkbox" name="permission[<?php echo $row['m_id'] ?>][]" class="form-control">
													</td>
													<?php
												}else{
													?>
													<td>
														<input type="hidden" name="module_id[<?php echo $row['m_id'] ?>][]" value="<?php echo $row['m_id'] ?>">
														<input type="checkbox" checked name="permission[<?php echo $row['m_id'] ?>][]" class="form-control">
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
													<td class=""><?php echo $row_sub['m_name'] ?></td>
													<td><?php echo $row_sub['fav_icon'] ?></td>
													<td><?php echo $row_sub['ordering'] ?></td>
													
													<?php
													if($mPer == 0){
														?>
														<td>
															<input type="hidden" name="module_id[<?php echo $row_sub['m_id'] ?>][]" value="<?php echo $row_sub['m_id'] ?>">
															<input type="checkbox" name="permission[<?php echo $row_sub['m_id'] ?>][]" class="form-control">
														</td>
														<?php
													}else{
														?>
														<td>
															<input type="hidden" name="module_id[<?php echo $row_sub['m_id'] ?>][]" value="<?php echo $row_sub['m_id'] ?>">
															<input type="checkbox" checked name="permission[<?php echo $row_sub['m_id'] ?>][]" class="form-control">
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

						<div class="card-footer">
							<div class="row">
								<div class="col-md-12">
									<button class="btn btn-primary "> Save</button>
									<button class="btn btn-default" type="button" onclick="$('#manage-modules').get(0).reset()"> Cancel</button>
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
						alert(resp);
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
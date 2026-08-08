<?php include('db_connect.php');

if(in_array("53",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-view-page view-modules">
		<div class="row view-summary-grid">
			<div class="col-md-4"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-th-large"></i></span><div class="view-summary-copy"><h6>Total Modules</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM modules_1")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-active"><span class="view-summary-icon"><i class="fa fa-folder"></i></span><div class="view-summary-copy"><h6>Parent Modules</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM modules_1 WHERE m_parent_id=0")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-list"></i></span><div class="view-summary-copy"><h6>Sub Modules</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM modules_1 WHERE m_parent_id<>0")->fetch_assoc()['c']; ?></h3></div></div></div>
		</div>

		<div class="col-lg-12">

			<div class="row">
				<div class="col-md-12">
					<div class="card professional-view-card">
						<div class="row" style="background-color:#f9f9f9">
							<div class="col-lg-6">
								<div class="view-heading"><span class="view-heading-icon"><i class="fa fa-th-large"></i></span><div><h2>System Modules</h2><p>Application modules and navigation order.</p></div></div>
							</div>
							<div class="col-lg-6">
								<a class="btn btn-primary btn-sm float-right m-3" href="index.php?page=Modules/add-module" id="new_order">
									<i class="fa fa-plus"></i> Add Module
								</a>
							</div>
						</div>
						<hr>
						<div class="card-body table-responsive">
							<table class="table table-bordered">
								<thead>
									<tr style="text-align: center;">
										<th>ID</th>
										<th>Parent Module</th>
										<th>Icon</th>
										<th>Ordering</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$category = $conn->query("SELECT * FROM modules_1 WHERE m_parent_id = 0 order by ordering asc");
									while($row=$category->fetch_assoc()){
										?>
										<tr style="color: blue; font-weight: bold">
											<td class="text-center"><?php echo $row['m_id'] ?></td>
											<td class=""><?php echo $row['m_name'] ?></td>
											<td><?php echo $row['fav_icon'] ?></td>
											<td><?php echo $row['ordering'] ?></td>
										</tr>

										<?php
										$sub_mod = $conn->query("SELECT * FROM modules_1 WHERE m_parent_id = ".$row['m_id']." order by ordering asc");
										while($row_sub=$sub_mod->fetch_assoc()){
											?>
											<tr>
												<td class="text-center"><?php echo $row_sub['m_id'] ?></td>
												<td class=""><?php echo $row_sub['m_name'] ?></td>
												<td><?php echo $row_sub['fav_icon'] ?></td>
												<td><?php echo $row_sub['ordering'] ?></td>
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
				<!-- Table Panel -->
			</div>
		</div>	


		<?php
	}else{
		include 'accessDenied.php';
	}
	?>

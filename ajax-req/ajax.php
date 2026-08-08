<?php
ob_start();
$action = $_GET['action'];
include 'admin_class.php';
$crud = new Action();
if($action == 'login'){
	$login = $crud->login();
	if($login)
		echo $login;
}
if($action == 'login2'){
	$login = $crud->login2();
	if($login)
		echo $login;
}
if($action == 'logout'){
	$logout = $crud->logout();
	if($logout)
		echo $logout;
}
if($action == 'logout2'){
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == 'update_account'){
	$save = $crud->update_account();
	if($save)
		echo $save;
}
if($action == "save_settings"){
	$save = $crud->save_settings();
	if($save)
		echo $save;
}
if($action == "save_category"){
	$save = $crud->save_category();
	if($save)
		echo $save;
}

if($action == "save_supplier"){
	$save = $crud->save_supplier();
	if($save)
		echo $save;
}
if($action == "delete_supplier"){
	$delete = $crud->delete_supplier();
	if($delete)
		echo $delete;
}

if($action == "save_customer"){
	$save = $crud->save_customer();
	if($save)
		echo $save;
}
if($action == "delete_customer"){
	$delete = $crud->delete_customer();
	if($delete)
		echo $delete;
}


if($action == "delete_category"){
	$delete = $crud->delete_category();
	if($delete)
		echo $delete;
}
if($action == "save_product"){
	$save = $crud->save_product();
	if($save)
		echo $save;
}
if($action == "delete_product"){
	$delete = $crud->delete_product();
	if($delete)
		echo $delete;
}

if($action == "save_order"){
	$save = $crud->save_order();
	if($save)
		echo $save;
}
if($action == "delete_order"){
	$delete = $crud->delete_order();
	if($delete)
		echo $delete;
}


//////////////////////////////////////
if($action == "save_inventory_item"){
	$save = $crud->save_inventory_item();
	if($save)
		echo $save;
}
if($action == "delete_items"){
	$delete = $crud->delete_items();
	if($delete)
		echo $delete;
}



if($action == "save_receive_inventory"){
	$save = $crud->save_receive_inventory();
	if($save)
		echo $save;
}
if($action == "delete_receive_inventory"){
	$delete = $crud->delete_receive_inventory();
	if($delete)
		echo $delete;
}


if($action == "save_job_order"){
	$save = $crud->save_job_order();
	if($save)
		echo $save;
}
if($action == "delete_job_order"){
	$delete = $crud->delete_job_order();
	if($delete)
		echo $delete;
}
if($action == "update_jobcard_status"){
	$save = $crud->update_jobcard_status();
	if($save)
		echo $save;
}
if($action == "edit_jobcard"){
	$save = $crud->edit_jobcard();
	if($save)
		echo $save;
}

if($action == "save_customer_payment"){
	$save = $crud->save_customer_payment();
	if($save)
		echo $save;
}
if($action == "delete_customer_payment"){
	$delete = $crud->delete_customer_payment();
	if($delete)
		echo $delete;
}


if($action == "save_acc_type"){
	$save = $crud->save_acc_type();
	if($save)
		echo $save;
}
if($action == "delete_acc_type"){
	$delete = $crud->delete_acc_type();
	if($delete)
		echo $delete;
}


if($action == "save_new_acc"){
	$save = $crud->save_new_acc();
	if($save)
		echo $save;
}
if($action == "delete_acc_type"){
	$delete = $crud->delete_acc_type();
	if($delete)
		echo $delete;
}


if($action == "save_receive_inventory_cust"){
	$save = $crud->save_receive_inventory_cust();
	if($save)
		echo $save;
}

if($action == "save_supplier_payment"){
	$save = $crud->save_supplier_payment();
	if($save)
		echo $save;
}


if($action == "save_transfer_inventory"){
	$save = $crud->save_transfer_inventory();
	if($save)
		echo $save;
}

if($action == "save_waste_item"){
	$save = $crud->save_waste_item();
	if($save)
		echo $save;
}

if($action == "save_module"){
	$save = $crud->save_module();
	if($save)
		echo $save;
}
if($action == "save_module_permissions"){
	$save = $crud->save_module_permissions();
	if($save)
		echo $save;
}



if($action == "save_employee"){
	$save = $crud->save_employee();
	if($save)
		echo $save;
}
if($action == "sync_attendance"){
	$save = $crud->sync_attendance();
	if($save)
		echo $save;
}

if($action == "process_salary"){
	$save = $crud->process_salary();
	if($save)
		echo $save;
}


if($action == "save_journal_voucher"){
	$save = $crud->save_journal_voucher();
	if($save)
		echo $save;
}





ob_end_flush();
?>

<?php 
function save_inventory_item(){
	extract($_POST);
	$data = "";
	mysqli_query($this->db,"START TRANSACTION");
	
	$table_id = $this->db->insert_id;
	$act_log = activityLog("New Item In Inventory Added. IT".$table_id." - ".$item_name.".",$_SESSION['login_id'],$this->db);

	if($save && $act_log)
	{
		mysqli_query($this->db,"COMMIT");
		return 1;
	}else{
		mysqli_query($this->db,"ROLLBACK");
		return $act_log;
	}
}
?>
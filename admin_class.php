<?php
include 'functions.php';
include_once 'secure_session.php';
if(icon_config('app_debug', false)){
	ini_set('display_errors', 1);
}
Class Action {
	private $db;

	public function __construct() {
		ob_start();
		include 'db_connect.php';
		
		$this->db = $conn;
	}
	function __destruct() {
		$this->db->close();
		ob_end_flush();
	}

	function login(){
		$username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
		$password = isset($_POST['password']) ? (string)$_POST['password'] : '';
		if($username === '' || $password === ''){
			return 3;
		}
		$username_safe = mysqli_real_escape_string($this->db,$username);
		$qry = $this->db->query("SELECT * FROM users where username = '".$username_safe."' LIMIT 1");
		if($qry->num_rows > 0){
			$user = $qry->fetch_array();
			$stored_password = isset($user['password']) ? (string)$user['password'] : '';
			$password_ok = false;
			if(strlen($stored_password) === 32 && ctype_xdigit($stored_password)){
				$password_ok = hash_equals($stored_password,md5($password));
				if($password_ok){
					$new_hash = password_hash($password,PASSWORD_DEFAULT);
					$new_hash_safe = mysqli_real_escape_string($this->db,$new_hash);
					$this->db->query("UPDATE users SET password = '".$new_hash_safe."' WHERE id = ".(int)$user['id']);
					$user['password'] = $new_hash;
				}
			}else{
				$password_ok = password_verify($password,$stored_password);
			}
			if(!$password_ok){
				return 3;
			}
			if(session_status() === PHP_SESSION_ACTIVE){
				session_regenerate_id(true);
			}
			foreach ($user as $key => $value) {
				if($key != 'password' && $key != 'passwors' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}

			$usr_mod_permisions=array("0");
			$qry_permisions = $this->db->query("SELECT mod_id FROM module_permision where user_id = ".$_SESSION['login_id']." UNION SELECT rp.mod_id FROM user_roles ur INNER JOIN role_permissions rp ON ur.role_id = rp.role_id INNER JOIN roles r ON ur.role_id = r.role_id WHERE ur.user_id = ".$_SESSION['login_id']." AND r.status = 0");
			if($qry_permisions->num_rows > 0){
				while($row=$qry_permisions->fetch_assoc()){
					array_push($usr_mod_permisions,$row['mod_id']);
				}				
			}
			$_SESSION['login_Permisions'] = array_values(array_unique(array_map('strval',$usr_mod_permisions)));

			$act_log = activityLog("User Logged In, User: ".$_SESSION['login_name']." ",$_SESSION['login_id'],$this->db);

			return 1;
		}else{
			return 3;
		}
	}

	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}

	function save_user(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");
		$name = mysqli_real_escape_string($this->db,trim((string)$name));
		$username = mysqli_real_escape_string($this->db,trim((string)$username));
		$type = (int)$type;
		$id = isset($id) ? (int)$id : 0;
		$data = " name = '$name' ";
		$data .= ", username = '$username' ";
		$act_data = " Emp Name = $name, Email = $username ";
		if(!empty($password))
			$data .= ", password = '".mysqli_real_escape_string($this->db,password_hash($password,PASSWORD_DEFAULT))."' ";
		$data .= ", type = '$type' ";
		$chk = $this->db->query("Select * from users where username = '$username' and id !='$id' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set ".$data);
		}else{
			$save = $this->db->query("UPDATE users set ".$data." where id = ".$id);
		}

		$table_id = $this->db->insert_id;
		$act_log = activityLog("New Employee Added.".$act_data.". ",$_SESSION['login_id'],$this->db);

		if($save && $act_log)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}
	function delete_user(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");
		$delete = $this->db->query("DELETE FROM users where id = ".$id);

		$act_log = activityLog("Employee Deleted, Emp Id: ".$id.". ",$_SESSION['login_id'],$this->db);

		if($delete && $act_log)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}


	function save_settings(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");

		$act_data = " name = ".str_replace("'","&#x2019;",$name)." ";
		$act_data .= ", email = $email ";
		$act_data .= ", contact = $contact ";

		$data = " name = '".str_replace("'","&#x2019;",$name)."' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
		if($_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", cover_img = '$fname' ";

		}

		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		if($save){
			$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
			foreach ($query as $key => $value) {
				if(!is_numeric($key))
					$_SESSION['system'][$key] = $value;
			}
		}
		$act_log = activityLog("Systems Setting updated (".$act_data.") .",$_SESSION['login_id'],$this->db);
		if($save && $act_log)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}


	function save_customer(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");
		$data = "";
		$data_act = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
					if($k != "cust_id"){
						$data_act .= " $k = $v ";
					}
					
				}else{
					$data .= ", $k='$v' ";
					if($k != "cust_id"){
						$data_act .= " $k = $v ";
					}
				}

				if($k == "cust_name"){
					$cust_name_acc = $v;
				}
			}
		}
		$check = $this->db->query("SELECT * FROM customers where cust_name ='$cust_name' ".(!empty($id) ? " and cust_id != {$cust_id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}


		$parrent_account = '200004';
		if($parrent_account != "" ){
			$querytest = "SELECT * FROM code_counters WHERE type = ".$parrent_account;
			$resultTest = mysqli_query($this->db,$querytest);
			$dataTes = mysqli_fetch_array($resultTest);

			$account_no = $dataTes['code'];
			$account_no_new = $account_no + 10;
		}

		$saveQuery = "INSERT INTO accounts SET ";
		$saveQuery .= " account_no = '".$account_no_new."' ";
		$saveQuery .= ", acc_name = '".$cust_name_acc."' ";
		$saveQuery .= ", acc_type = '1' ";
		$saveQuery .= ", fin_statement = '1' ";
		$saveQuery .= ", parent_id = '".$parrent_account."' ";
		$saveQuery .= ", acc_cat = '1' ";
		$save3 = $this->db->query($saveQuery);


		$UpQuery = "UPDATE code_counters SET ";
		$UpQuery .= " code = '".$account_no_new."' ";
		$UpQuery .= " WHERE type = '".$parrent_account."' ";
		$save2 = $this->db->query($UpQuery);


		// $save2 = $this->db->query($account_open);
		// $account_id = $this->db->insert_id;
		$data.=", acc_id = ".$account_no_new;

		if(empty($id)){
			$save = $this->db->query("INSERT INTO customers set $data");
		}else{
			$save = $this->db->query("UPDATE customers set $data where cust_id = $cust_id");
		}

		$act_log = activityLog("New Customer Added. (".$data_act."). ",$_SESSION['login_id'],$this->db);





		if($save && $act_log && $save2 && $save3)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}

	function delete_customer(){
		extract($_POST);
		mysqli_query($conn,"START TRANSACTION");
		$delete = $this->db->query("UPDATE customers set cust_status = 1 where cust_id = ".$cust_id);
		$act_log = activityLog("Customer Delete. (Customer Id: CUST-".$cust_id."). ",$_SESSION['login_id'],$this->db);

		if($delete && $act_log)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}





	function save_supplier(){
		extract($_POST);
		$data = "";
		$act_data = "";
		mysqli_query($this->db,"START TRANSACTION");

		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
					
				}else{
					$data .= ", $k='$v' ";
					
				}

				if($k == "supp_name"){
					$supp_name_acc = $v;
				}
			}
		}
		$check = $this->db->query("SELECT * FROM suppliers where supp_name ='$supp_name' ".(!empty($id) ? " and supp_id != {$supp_id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}


		$parrent_account = '400001';
		if($parrent_account != "" ){
			$querytest = "SELECT * FROM code_counters WHERE type = ".$parrent_account;
			$resultTest = mysqli_query($this->db,$querytest);
			$dataTes = mysqli_fetch_array($resultTest);

			$account_no = $dataTes['code'];
			$account_no_new = $account_no + 10;
		}

		$saveQuery = "INSERT INTO accounts SET ";
		$saveQuery .= " account_no = '".$account_no_new."' ";
		$saveQuery .= ", acc_name = '".$supp_name_acc."' ";
		$saveQuery .= ", acc_type = '2' ";
		$saveQuery .= ", fin_statement = '1' ";
		$saveQuery .= ", parent_id = '".$parrent_account."' ";
		$saveQuery .= ", acc_cat = '1' ";
		$save3 = $this->db->query($saveQuery);


		$UpQuery = "UPDATE code_counters SET ";
		$UpQuery .= " code = '".$account_no_new."' ";
		$UpQuery .= " WHERE type = '".$parrent_account."' ";
		$save2 = $this->db->query($UpQuery);



		$account_id = $this->db->insert_id;
		$data.=", acc_id = ".$account_no_new;
		if(empty($id)){
			$r = "INSERT INTO suppliers set $data";
			$save = $this->db->query($r);
		}else{
			$save = $this->db->query("UPDATE suppliers set $data where supp_id = $supp_id");
		}

		$table_id = $this->db->insert_id;
		$act_log = activityLog("New Supplier Added. Supplier No: SUPP-".$table_id." (".$act_data.") .",$_SESSION['login_id'],$this->db);
		

		if($save && $act_log && $save2 && $save3)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured";
		}
	}

	function delete_supplier(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");
		$delete = $this->db->query("UPDATE suppliers set supp_status = 1 where supp_id = ".$supp_id);
		
		$table_id = $this->db->insert_id;
		$act_log = activityLog("Supllier has been deleted. Supplier No: SUPP-".$supp_id." ",$_SESSION['login_id'],$this->db);

		if($delete && $act_log)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}


	//////////////////////////////
	function save_inventory_item(){
		extract($_POST);
		$data = "";

		mysqli_query($this->db,"START TRANSACTION");
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				$v = mysqli_real_escape_string($this->db,(string)$v);
				if(empty($data)){
					$data .= " $k='$v' ";					
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM inventory_item where item_name ='$item_name' ".(!empty($id) ? " and item_id != {$item_id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO inventory_item set $data");
		}else{
			$save = $this->db->query("UPDATE inventory_item set $data where item_id = $item_id");
		}
		$table_id = $this->db->insert_id;
		$act_log = activityLog("New Item In Inventory Added. IT".$table_id." - ".$item_name.".",$_SESSION['login_id'],$this->db);

		if($save && $act_log)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}

	function delete_items(){
		extract($_POST);
		
		mysqli_query($this->db,"START TRANSACTION");
		$delete = $this->db->query("UPDATE inventory_item set status = 1 where item_id = ".$item_id);
		
		$act_log = activityLog("Paper Item Deleted from Inventory. Item Code: IT-".$item_id.". ",$_SESSION['login_id'],$this->db);

		if($save && $act_log)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return $act_log;
		}
	}





	function save_receive_inventory(){
		extract($_POST);
		$data = "";
		$data_audit = "";
		$quantity = 0;
		$item_id = "";
		$mystr = "";
		$supplier_id = mysqli_real_escape_string($this->db,$_POST['supplier_id']);
		$requisition_no = mysqli_real_escape_string($this->db,$_POST['requisition_no']);
		$received_date = mysqli_real_escape_string($this->db,$_POST['received_date']);
		// $paid_amount = mysqli_real_escape_string($this->db,$_POST['paid_amount']);
		$doc_no = mysqli_real_escape_string($this->db,$_POST['doc_no']);


		$error_msg = "";
		if($supplier_id == ""){
			$error_msg = "Supplier cannot be empty.";
		}
		if($requisition_no == "" && $error_msg == ""){
			$error_msg = "Requisition No cannot be empty.";
		}
		if(($received_date == "" || $received_date == "00-00-0000") && $error_msg == ""){
			$error_msg = "Received Date cannot be empty.";
		}


		if($doc_no == "" && $error_msg == ""){
			$error_msg = "Document No cannot be empty.";
		}


		// quantity_remain
		$qty_count=0;
		if($error_msg == ""){

			for($i=0; $i<count($_POST['item_id']); $i++){
				$item_id = mysqli_real_escape_string($this->db,$_POST['item_id'][$i]);
				$quantity = mysqli_real_escape_string($this->db,$_POST['quantity'][$i]);
				$quantity_remain = mysqli_real_escape_string($this->db,$_POST['quantity_remain'][$i]);

				if($quantity> $quantity_remain){
					$error_msg ="Quantitty cannot be greater then Qty Remaining for Item: IT-".$item_id;
				}

				if($quantity < 0){
					$error_msg ="Quantitty cannot less then 0 for Item: IT-".$item_id;
				}

				if($quantity > 0){
					$qty_count++;
				}
			}
		}
		if($qty_count == 0 && $error_msg == ""){
			$error_msg ="Atleast 1 item should have Quantitty greater then 0.";
		}


		if($error_msg != ""){
			return $error_msg;
		}else{
			mysqli_query($this->db,"START TRANSACTION");
			$act_data = "";

			$save1 = $this->db->query("INSERT INTO inventory_received set supp_order_id = '".$supplier_id."', received_date = '".$received_date."', supplier_id = ".$supplier_id.", doc_no = '".$doc_no."' , user_id =".$_SESSION['login_id']);
			$last_id = $this->db->insert_id;


			$supp_details = $this->db->query("SELECT supp_name from  suppliers WHERE supp_id = ".$supplier_id);
			while($row=$supp_details->fetch_assoc()){
				$supp_name = $row['supp_name'];
			}


			$act_data .= "Supplier: ".$supp_name.", Receive Date: ".$received_date.", Doc No: ".$doc_no;

			$all_qty_count = 0;
			$total_purchase_amount = 0;
			for($i=0; $i<count($_POST['item_id']); $i++){

				// plate_rate_calculations

				$item_id = mysqli_real_escape_string($this->db,$_POST['item_id'][$i]);
				$quantity = mysqli_real_escape_string($this->db,$_POST['quantity'][$i]);
				$quantity_remain = mysqli_real_escape_string($this->db,$_POST['quantity_remain'][$i]);
				$rate = mysqli_real_escape_string($this->db,$_POST['rate'][$i]);
				$amount = mysqli_real_escape_string($this->db,$_POST['amount'][$i]);

				$act_data .= " [Item ID: ".$item_id.", Quantity: ".$quantity.", Rate: ".$rate.", Amount: ".$amount."],";

				if($quantity>0){
					$total_purchase_amount += (float)$amount;
					$save2 = $this->db->query("INSERT INTO inventoty_received_details set ir_id = ".$last_id.", supplier_id = ".$supplier_id.", item_id = ".$item_id.", quantity = ".$quantity.", received_date = '".$received_date."' , rate = '".$rate."' , amount = '".$amount."' , user_id = ".$_SESSION['login_id'].", sup_order_id = ".$requisition_no." ");
					$last_id_de = $this->db->insert_id;


					// $total_stock_amt = $quantity * $amount;
					$save7 = $this->db->query("INSERT INTO plate_rate_calculations set 	prc_inv_rec_id = ".$last_id_de.", prc_plate_id = ".$item_id.", prc_plate_rate = ".$rate.", prc_qty = ".$quantity.", total_stock_amt = '".$amount."' ");
					


					if($quantity != $quantity_remain){
						$all_qty_count++;
					}


					$save3 = $this->db->query("UPDATE requisition_details set qty_rec = qty_rec +".$quantity." WHERE req_id = ".$requisition_no." AND item_id = ".$item_id);

					$save4 = $this->db->query("INSERT INTO inventory_audit set item_id = ".$item_id.", quantity = ".$quantity.", remarks = 'Inventory Received!', ref_column = 'SUPPLIER_RECEIVED_INV', ref_id = ".$last_id_de.", user_id = ".$_SESSION['login_id'].", dated = '".$received_date."' ");

					// $save5 = $this->db->query("UPDATE inventory_item set quantity =  quantity + ".$quantity." WHERE item_id = ".$item_id." ");



					// rate calculation
					$rate_calc_query = "SELECT * FROM plate_rate_calculations WHERE prc_plate_id = ".$item_id." AND prc_qty != 0 ";
					$query_212 = mysqli_query( $this->db,$rate_calc_query);
					$t_qty = 0;
					$t_amount = 0;
					while($data_212 = mysqli_fetch_array($query_212)){
						$prc_inv_rec_id = $data_212['prc_inv_rec_id'];
						$prc_plate_id = $data_212['prc_plate_id'];
						$prc_plate_rate = $data_212['prc_plate_rate'];
						$prc_qty = $data_212['prc_qty'];
						$total_stock_amt = $data_212['total_stock_amt'];

						// $this_calc = $total_stock_amt;

						$t_amount += $total_stock_amt;
						$t_qty += $prc_qty;
					}

					$price_to_be = number_format($t_amount/$t_qty,2);


					$save5 = $this->db->query("UPDATE inventory_item set quantity =  quantity + ".$quantity.", avg_rate = '".$price_to_be."' WHERE item_id = ".$item_id." ");
				}




			}
			$act_data = trim($act_data,',');

			$req_status = "";
			if($all_qty_count == 0){
				$save6 = $this->db->query("UPDATE paper_requisition set req_status = '2' WHERE id = ".$requisition_no);
				$req_status = "Received";
			}else{
				$save6 = $this->db->query("UPDATE paper_requisition set req_status = '1' WHERE id = ".$requisition_no);
				$req_status = "Partial Received";
			}




			$table_id = $this->db->insert_id;
			$act_log = activityLog("Inventory Received against Requisition No: REQ- ".$requisition_no.", Details are:  ".$act_data.", Requisition Status ".$req_status." ",$_SESSION['login_id'],$this->db);
			$purchase_voucher = 1;
			if($total_purchase_amount > 0){
				$purchase_narration = mysqli_real_escape_string($this->db,"Purchase voucher generated on inventory receiving IR-".$last_id." against REQ-".$requisition_no);
				$purchase_voucher = generate_voucher(1,$supplier_id,0,$received_date,$total_purchase_amount,'inventory_received',$last_id,$purchase_narration,$_SESSION['login_id'],$this->db);
			}

			// $ab = $sasas;
			if($save1 && $save2 && $save3 && $save4 && $save5 && $save6 && $save7 && $act_log && $purchase_voucher === 1)
			{
				mysqli_query($this->db,"COMMIT");
				return 1;
			}else{
				mysqli_query($this->db,"ROLLBACK");
				return is_string($purchase_voucher) ? $purchase_voucher : "Error Occured!";
			}
		}		
	}



	function save_customer_payment(){
		extract($_POST);
		$data = "";
		$data_act = "";
		mysqli_query($this->db,"START TRANSACTION");
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				$v = mysqli_real_escape_string($this->db,(string)$v);

				$is_empty = false;
				if(empty($data)){
					$data .= " $k='$v' ";					
				}else{
					$data .= ", $k='$v' ";
				}

				if($k != "pay_id"){

					if($v == ""){
						$is_empty = true;
					}

					if($k == "customer_id"){
						$data_act .= "Customer No: ".$v.", ";
					}else if($k == "reference"){
						$data_act .= "Reference: ".$v.", ";
					}
					else if($k == "payment_mode"){
						if($v == 1){
							$v = "Cash";
						}else{
							$v = "Cheque";
						}
						$data_act .= "Pay Mode: ".$v.", ";
					}
					else if($k == "amount"){
						$data_act .= "Amount: ".$v.", ";
					}
					else if($k == "cheque_no"){
						if($v != ""){
							$data_act .= "Cheque No: ".$v.", ";
						}
					}
					else if($k == "cheque_date"){
						if($v != ""){
							$data_act .= "Cheque No: ".$v.", ";
						}
					}
					else if($k == "consignee_name"){
						if($v != ""){
							$data_act .= "Consignee Name: ".$v.", ";
						}							
					}
					else if($k == "payment_date"){
						$data_act .= "Payment Date: ".$v.", ";
					}
					else if($k == "payment_date"){
						$data_act .= "Payment Date: ".$v.", ";
					}
				}
			}
		}
		
		if(!$is_empty){
			if(empty($id)){
				$save_Date = "INSERT INTO customer_payment set $data";
				$save = $this->db->query($save_Date);
			}else{
				$save = $this->db->query("UPDATE customer_payment set $data where pay_id = $pay_id");
			}

			$table_id = empty($id) ? $this->db->insert_id : (int)$pay_id;

			$act_log = activityLog("Customer Payment has been made. Details are (".$data_act."). ",$_SESSION['login_id'],$this->db);		

			$target_account = (int)$customer_id;
			$sec_acc = (int)$receive_in_acc;
			$amount = (float)$amount;
			$payment_date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$payment_date) ? $payment_date : date('Y-m-d');
			$remarks = mysqli_real_escape_string($this->db,(string)$remarks);
			$make_voucher = ($target_account > 0 && $sec_acc > 0 && $amount > 0) ? generate_voucher(4,$target_account,$sec_acc,$payment_date,$amount,'customer_payment',$table_id,$remarks,$_SESSION['login_id'],$this->db) : false;

			if($save && $act_log && $make_voucher)
			{
				mysqli_query($this->db,"COMMIT");
				return 1;
			}else{
				mysqli_query($this->db,"ROLLBACK");
				return "Error Occured!";
			}
		}else{
			return "Please Fill mandatory Fields";
		}
		
	}



	function delete_customer_payment(){
		extract($_POST);
		$delete = $this->db->query("UPDATE customer_payment set pay_status = 1 where pay_id = ".$pay_id);
		if($delete){
			return 1;
		}
	}

	function delete_acc_type(){
		extract($_POST);

		mysqli_query($this->db,"START TRANSACTION");
		$delete = $this->db->query("UPDATE account_types set del_status = 1 where acc_type_id  = ".$acc_type_id);

		$act_log = activityLog("Account Type Deleted. Details are (Acc Type Id: ".$acc_type_id."). ",$_SESSION['login_id'],$this->db);

		if($delete && $act_log)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}



	function save_acc_type(){
		extract($_POST);
		$data = "";
		$act_data = "";
		mysqli_query($this->db,"START TRANSACTION");

		$error_msg = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";


				}else{
					$data .= ", $k='$v' ";

				}

				if($k != "acc_type_id "){
					$act_data .= " $k=$v ,";
				}

				if($v == "" && $k == "type_name"){
					$error_msg .= "Name cannot be empty.";
				}else if($v == "" && $k == "type_parent_id" && $error_msg == ""){
					$error_msg .= "Parent Id cannot be empty.";
				}
			}
		}
		if($error_msg != ""){
			return $error_msg;
		}else{

			$check = $this->db->query("SELECT * FROM account_types where type_name ='$type_name' ".(!empty($id) ? " and acc_type_id  != {$acc_type_id } " : ''))->num_rows;
			if($check > 0){
				return 2;
				exit;
			}
			if(empty($id)){
				$save = $this->db->query("INSERT INTO account_types set $data");
			}else{
				$save = $this->db->query("UPDATE account_types set $data where acc_type_id  = $acc_type_id ");
			}

			$table_id = $this->db->insert_id;
			$act_log = activityLog("New Account Type Added. Account Type No: CD-".$table_id." (".$act_data.") .",$_SESSION['login_id'],$this->db);

			if($save && $act_log)
			{
				mysqli_query($this->db,"COMMIT");
				return 1;
			}else{
				mysqli_query($this->db,"ROLLBACK");
				return "Error Occured";
			}
		}

	}



	function save_new_acc(){
		extract($_POST);
		$data = "";
		$act_data = "";
		mysqli_query($this->db,"START TRANSACTION");

		$error_msg = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}

				if($k != "acc_id"){
					if($k == "acc_name"){
						$act_data .= "Account Name: ".$v;
					}else if($k == "acc_type"){
						$act_data .= ", Account Type: ".$v;
					}
					else if($k == "fin_statement"){
						$act_data .= ", Financial Statement: ".$v;
					}
					else if($k == "acc_cat"){
						$act_data .= "Account Category: ".$v;
					}
				}

				if($v == "" && $k == "acc_name"){
					$error_msg .= "Account Name cannot be empty.";
				}else if($v == "" && $k == "acc_type" && $error_msg == ""){
					$error_msg .= "Account Type cannot be empty.";
				}
				else if($v == "" && $k == "fin_statement" && $error_msg == ""){
					$error_msg .= "Financila Statement Tag cannot be empty.";
				}
				else if($v == "" && $k == "acc_cat" && $error_msg == ""){
					$error_msg .= "Account Category cannot be empty.";
				}
			}
		}

		if($error_msg != ""){
			return $error_msg;
		}else{
			$data .= ", parent_id = 0, added_by = ".$_SESSION['login_id'];
			$check = $this->db->query("SELECT * FROM accounts where acc_name ='$acc_name' ".(!empty($id) ? " and acc_id   != {$acc_id  } " : ''))->num_rows;
			if($check > 0){
				return 2;
				exit;
			}
			if(empty($id)){

				$acc_name = mysqli_real_escape_string($this->db,$_POST['acc_name']);
				$acc_type = mysqli_real_escape_string($this->db,$_POST['acc_type']);
				$fin_statement = mysqli_real_escape_string($this->db,$_POST['fin_statement']);
				$parrent_account = mysqli_real_escape_string($this->db,$_POST['parrent_account']);
				// $acc_cat = mysqli_real_escape_string($this->db,$_POST['acc_cat']);
				if($parrent_account != "" ){
					$querytest = "SELECT * FROM code_counters WHERE type = ".$parrent_account;
					$resultTest = mysqli_query($this->db,$querytest);
					if(mysqli_num_rows($resultTest)>0){
						$dataTes = mysqli_fetch_array($resultTest);

						$account_no = $dataTes['code'];
						$account_no_new = $account_no + 1;
					}else{
						return "Code Counter Not Set.";
					}
				}

				$saveQuery = "INSERT INTO accounts SET ";
				$saveQuery .= " account_no = '".$account_no_new."' ";
				$saveQuery .= ", acc_name = '".$acc_name."' ";
				$saveQuery .= ", acc_type = '".$acc_type."' ";
				$saveQuery .= ", fin_statement = '".$fin_statement."' ";
				$saveQuery .= ", parent_id = '".$parrent_account."' ";
				$saveQuery .= ", acc_cat = '1' ";
				$save = $this->db->query($saveQuery);


				$UpQuery = "UPDATE code_counters SET ";
				$UpQuery .= " code = '".$account_no_new."' ";
				$UpQuery .= " WHERE type = '".$parrent_account."' ";
				$save2 = $this->db->query($UpQuery);

			}else{
				$save = $this->db->query("UPDATE accounts set $data where acc_id = $acc_id");
			}

			$table_id = $this->db->insert_id;
			$act_log = activityLog("New Account Added. Account No: ACC-".$table_id." (".$act_data.") .",$_SESSION['login_id'],$this->db);

			if($save && $act_log && $save2)
			{
				mysqli_query($this->db,"COMMIT");
				return 1;
			}else{
				mysqli_query($this->db,"ROLLBACK");
				return "Error Occured--".$UpQuery;
			}
		}

	}


	function save_receive_inventory_cust(){
		extract($_POST);
		$data = "";
		$data_audit = "";
		$quantity = 0;
		$item_id = "";
		$mystr = "";
		$cust_id = mysqli_real_escape_string($this->db,$_POST['cust_id']);


		$error_msg = "";
		if($cust_id == ""){
			$error_msg = "Customer cannot be empty.";
		}

		// quantity_remain
		$qty_count=0;
		if($error_msg == ""){

			for($i=0; $i<count($_POST['item_id']); $i++){
				$item_id = mysqli_real_escape_string($this->db,$_POST['item_id'][$i]);
				$quantity = mysqli_real_escape_string($this->db,$_POST['quantity'][$i]);


				if($quantity < 0){
					$error_msg ="Quantitty cannot less then 0 for Item: IT-".$item_id;
				}

				if($quantity > 0){
					$qty_count++;
				}
			}
		}
		if($qty_count == 0 && $error_msg == ""){
			$error_msg ="Atleast 1 item should have Quantitty greater then 0.";
		}


		if($error_msg != ""){
			return $error_msg;
		}else{
			mysqli_query($this->db,"START TRANSACTION");
			$act_data = " Inventory Received from ";


			$supp_details = $this->db->query("SELECT cust_name from  customers WHERE cust_id = ".$cust_id);
			while($row=$supp_details->fetch_assoc()){
				$cust_name = $row['cust_name'];
			}


			$act_data .= "Customer: ".$cust_name;

			$all_qty_count = 0;
			for($i=0; $i<count($_POST['item_id']); $i++){
				$item_id = mysqli_real_escape_string($this->db,$_POST['item_id'][$i]);
				$quantity = mysqli_real_escape_string($this->db,$_POST['quantity'][$i]);

				if($quantity != 0){

					$ci_id = 0;
					$query_check = "SELECT ci_id from customer_inventory WHERE cust_id = ".$cust_id." AND plate_id = ".$item_id;
					$resultCheck = mysqli_query($this->db,$query_check);
					while($dataCheck = mysqli_fetch_array($resultCheck)){
						$ci_id = $dataCheck['ci_id'];
					}
					
				// 	while($row=$supp_details->fetch_assoc()){
				// 		$ci_id = $row_check['ci_id'];
				// 	}

					if($ci_id != 0){
						$save1 = $this->db->query("UPDATE customer_inventory set quantity = quantity +".$quantity." WHERE ci_id = ".$ci_id);
					}else{
						$save1 = $this->db->query("INSERT INTO customer_inventory set cust_id = ".$cust_id.", plate_id = ".$item_id.", quantity = ".$quantity.", qty_booked=0");
					}
					$act_data .= " Details are = [Item ID: ".$item_id.", Quantity: ".$quantity."],";

					$save2 = $this->db->query("INSERT INTO external_inv_audit set item_id = ".$item_id.", quantity = ".$quantity.", remarks = 'Inventory Received!', ref_column = 'CUSTOMER_RECEIVED_INV', ref_id = 0, user_id = ".$_SESSION['login_id'].", cust_id = ".$cust_id);
				}
			}
			$act_data = trim($act_data,',');
			$act_log = activityLog($act_data,$_SESSION['login_id'],$this->db);

			if($save1 && $save2 && $act_log)
			{
				mysqli_query($this->db,"COMMIT");
				return 1;
			}else{
				mysqli_query($this->db,"ROLLBACK");
				return "Error Occured!";
			}
		}
	}







	function save_supplier_payment(){
		extract($_POST);
		$data = "";
		$data_act = "";
		mysqli_query($this->db,"START TRANSACTION");
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";					
				}else{
					$data .= ", $k='$v' ";
				}

				if($k != "pay_id"){
					if($k == "supplier_id"){
						$data_act .= "Supplier No: ".$v.", ";
					}else if($k == "reference"){
						$data_act .= "Reference: ".$v.", ";
					}
					else if($k == "payment_mode"){
						if($v == 1){
							$v = "Cash";
						}else{
							$v = "Cheque";
						}
						$data_act .= "Pay Mode: ".$v.", ";
					}
					else if($k == "amount"){
						$data_act .= "Amount: ".$v.", ";
					}
					else if($k == "cheque_no"){
						if($v != ""){
							$data_act .= "Cheque No: ".$v.", ";
						}
					}
					else if($k == "cheque_date"){
						if($v != ""){
							$data_act .= "Cheque No: ".$v.", ";
						}
					}
					else if($k == "consignee_name"){
						if($v != ""){
							$data_act .= "Consignee Name: ".$v.", ";
						}							
					}
					else if($k == "payment_date"){
						$data_act .= "Payment Date: ".$v.", ";
					}
					else if($k == "payment_date"){
						$data_act .= "Payment Date: ".$v.", ";
					}
				}
			}
		}

		if(empty($id)){
			$save_Date = "INSERT INTO supplier_payment set $data";
			$save = $this->db->query($save_Date);
		}else{
			$pay_id = (int)$pay_id;
			$save = $this->db->query("UPDATE supplier_payment set $data where pay_id = $pay_id");
		}

		$table_id = empty($id) ? $this->db->insert_id : (int)$pay_id;

		$act_log = activityLog("Supplier Payment has been made. Details are (".$data_act."). ",$_SESSION['login_id'],$this->db);		

		$target_account = (int)$supplier_id;
		$sec_acc = (int)$paid_from_acc;
		$amount = (float)$amount;
		$payment_date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$payment_date) ? $payment_date : date('Y-m-d');
		$remarks = mysqli_real_escape_string($this->db,(string)$remarks);
		$make_voucher = ($target_account > 0 && $sec_acc > 0 && $amount > 0) ? generate_voucher(2,$target_account,$sec_acc,$payment_date,$amount,'supplier_payment',$table_id,$remarks,$_SESSION['login_id'],$this->db) : false;

		if($save && $act_log && $make_voucher)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}








	function save_transfer_inventory(){
		extract($_POST);
		$data = "";
		$data_act = "";
		mysqli_query($this->db,"START TRANSACTION");
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				$$k= $v;
			}
		}

		if(empty($id)){
			$save_Date = "INSERT INTO inv_transfer_rec set irc_ref = ".$inventory_in_out;
			$save_Date .= ", customer_id = ".$customer_id;
			$save_Date .= ", plate_id = ".$plate_id;
			$save_Date .= ", quantity = ".$quantity;
			$save_Date .= ", remarks = '".$remarks."'";
			$save_Date .= ", transfer_date = '".$transfer_date."'";
			$save_Date .= ", user_id = ".$_SESSION['login_id'];
			$save = $this->db->query($save_Date);
		}

		$table_id = $this->db->insert_id;

		$query_cust_name = "SELECT * FROM customers where cust_id = ".$customer_id;
		$result_cust = mysqli_query($this->db,$query_cust_name);
		$data_cust = mysqli_fetch_array($result_cust);
		$cust_name = $data_cust['cust_name'];

		$query_plate_name = "SELECT * FROM inventory_item where item_id = ".$plate_id;
		$result_plate = mysqli_query($this->db,$query_plate_name);
		$data_plate = mysqli_fetch_array($result_plate);
		$plate_name = $data_plate['item_name'];

		$from_inv="";
		$to_inv="";
		if($inventory_in_out == 1){
			$from_inv = 'Customer Inventory: ['.$cust_name.']';
			$to_inv = 'ICON Inventory';

			$query_inv1 = "UPDATE inventory_item SET quantity = quantity + ".$quantity." WHERE item_id = ".$plate_id;
			$transfer1 = mysqli_query($this->db,$query_inv1);

			$query_inv2 = "UPDATE customer_inventory SET quantity = quantity - ".$quantity." WHERE plate_id = ".$plate_id." AND cust_id = ".$customer_id;
			$transfer2 = mysqli_query($this->db,$query_inv2);

			$query_save4 = "INSERT INTO inventory_audit set item_id = ".$plate_id.", quantity = ".$quantity.", remarks = 'Inventory Received From Customer: ".$cust_name."', ref_column = 'CUSTOMER_RECEIVED_INV', ref_id = ".$table_id.", user_id = ".$_SESSION['login_id'].", dated = '".$transfer_date."' "; 
			// $save4 = $this->db->query($query_save4);
			$audit1 = mysqli_query($this->db,$query_save4);


			$save5 = "INSERT INTO external_inv_audit set item_id = ".$plate_id.", quantity = -".$quantity.", remarks = 'Inventory Transfer To ICON!', ref_column = 'ICON_TRANSFER_INV', ref_id = ".$table_id.", user_id = ".$_SESSION['login_id'].", cust_id = ".$customer_id;

			$audit2 = mysqli_query($this->db,$save5);


		}else{
			$from_inv = 'ICON Inventory';
			$to_inv = 'Customer Inventory: ['.$cust_name.']';

			$query_inv1 = "UPDATE inventory_item SET quantity = quantity - ".$quantity." WHERE item_id = ".$plate_id;
			$transfer1 = mysqli_query($this->db,$query_inv1);

			$query_inv2 = "UPDATE customer_inventory SET quantity = quantity + ".$quantity." WHERE plate_id = ".$plate_id." AND cust_id =".$customer_id;
			$transfer2 = mysqli_query($this->db,$query_inv2);

			$save4 = "INSERT INTO inventory_audit set item_id = ".$plate_id.", quantity = -".$quantity.", remarks = 'Inventory Transfer To Customer: ".$cust_name."', ref_column = 'CUSTOMER_TRANSFER_INV', ref_id = ".$table_id.", user_id = ".$_SESSION['login_id'].", dated = '".$transfer_date."' ";
			$audit1 = mysqli_query($this->db,$save4);

			$save5 ="INSERT INTO external_inv_audit set item_id = ".$plate_id.", quantity = ".$quantity.", remarks = 'Inventory Received From ICON', ref_column = 'ICON_RECEIVE_INV', ref_id = ".$table_id.", user_id = ".$_SESSION['login_id'].", cust_id = ".$customer_id;

			$audit2 = mysqli_query($this->db,$save5);
		}

		$act_log = activityLog("Inventory has been transferd from ".$from_inv." To ".$to_inv.". Other Details are Plate: ".$plate_name." , Quantity: ".$quantity.". ",$_SESSION['login_id'],$this->db);		

		if($save && $act_log && $transfer1 && $transfer2 && $audit1 && $audit2)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}



	

	function save_waste_item(){
		extract($_POST);
		$data = "";
		$mystr = "";
		$item_id = mysqli_real_escape_string($this->db,$_POST['item_id']);
		$qty = mysqli_real_escape_string($this->db,$_POST['qty']);
		$job_id = mysqli_real_escape_string($this->db,$_POST['job_id']);
		$remarks = mysqli_real_escape_string($this->db,$_POST['remarks']);
		$dated = mysqli_real_escape_string($this->db,$_POST['dated']);

		$query_item = "SELECT * FROM inventory_item WHERE item_id = ".$item_id;
		$result_item = mysqli_query($this->db,$query_item);
		$data_item = mysqli_fetch_array($result_item);
		$item_name = $data_item['item_name'];

		$error_msg = "";
		if($item_id == ""){
			$error_msg = "Item cannot be empty.";
		}
		if($qty == "" || $qty == 0){
			$error_msg = "Quantity cannot be empty.";
		}
		if($job_id == ""){
			$error_msg = "Job Id cannot be empty.";
		}
		if($dated == ""){
			$error_msg = "Dated cannot be empty.";
		}
		if($remarks == ""){
			$error_msg = "Remakrs cannot be empty.";
		}

		if($error_msg != ""){
			return $error_msg;
		}else{
			mysqli_query($this->db,"START TRANSACTION");

			$save3 = $this->db->query("INSERT INTO waste_inventory set item_id = ".$item_id.", qty = ".$qty.", job_id = ".$job_id.", dated = '".$dated."', remarks = '".$remarks."'");

			$table_id = $this->db->insert_id;
			
			$act_data = "Inventory Wastage recorded. Details are [Item: ".$item_id." - ".$item_name.", Quantity: ".$qty.", Date: ".$dated.", Remarks: ".$remarks."]. ";

			$save1 = $this->db->query("UPDATE inventory_item set quantity = quantity -".$qty." WHERE item_id = ".$item_id);

			$save2 = $this->db->query("INSERT INTO inventory_audit set item_id = ".$item_id.", quantity = -".$qty.", remarks = 'Inventory Wasted!', ref_column = 'INVENTORY_WASTED', ref_id = ".$table_id.", user_id = ".$_SESSION['login_id'].", dated = '".$dated."' ");

			

			
			$act_log = activityLog($act_data,$_SESSION['login_id'],$this->db);

			if($save1 && $save2 && $save3 && $act_log)
			{
				mysqli_query($this->db,"COMMIT");
				return 1;
			}else{
				mysqli_query($this->db,"ROLLBACK");
				return "Error Occured!";
			}
		}		
	}



	

	function save_module(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");
		$data = "";
		$data_act = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
					if($k != "m_id"){
						$data_act .= " $k = $v ";
					}
					
				}else{
					$data .= ", $k='$v' ";
					if($k != "m_id"){
						$data_act .= " $k = $v ";
					}
				}

				if($k == "cust_name"){
					$cust_name_acc = $v;
				}
			}
		}
		$check = $this->db->query("SELECT * FROM modules_1 where m_name ='$m_name' ".(!empty($id) ? " and m_id != {$m_id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}

		if(empty($id)){
			$save = $this->db->query("INSERT INTO modules_1 set $data");
		}else{
			$save = $this->db->query("UPDATE modules_1 set $data where cust_id = $cust_id");
		}

		if($save)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}


	
	
	function save_module_permissions(){
		mysqli_query($this->db,"START TRANSACTION");

		$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
		if($user_id <= 0){
			mysqli_query($this->db,"ROLLBACK");
			return "Invalid user selected.";
		}
		$data_act = "";

		$del = "DELETE from module_permision WHERE user_id =".$user_id;
		$q1 = mysqli_query($this->db,$del);
		$q2_status = "";

		// print_r($_POST['permission']);

		if(isset($_POST['permission']) && is_array($_POST['permission'])){
			$stmt = $this->db->prepare("INSERT INTO module_permision (mod_id, user_id) VALUES (?, ?)");
			foreach($_POST['permission'] as $module_key => $permission_values){
				$module_id = (int)$module_key;
				if($module_id <= 0 || !is_array($permission_values) || !isset($permission_values[0]) || $permission_values[0] !== 'on'){
					continue;
				}
				$stmt->bind_param("ii",$module_id,$user_id);
				if(!$stmt->execute()){
					$q2_status = "failed";
					break;
				}
			}
			$stmt->close();
		}
		if($q1 && $q2_status != 'failed')
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}

	function save_role_permissions(){
		mysqli_query($this->db,"START TRANSACTION");
		$role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;
		if($role_id <= 0){
			mysqli_query($this->db,"ROLLBACK");
			return "Invalid role selected.";
		}
		$q1 = mysqli_query($this->db,"DELETE FROM role_permissions WHERE role_id = ".$role_id);
		$q2_status = "";
		if(isset($_POST['permission']) && is_array($_POST['permission'])){
			$stmt = $this->db->prepare("INSERT INTO role_permissions (role_id, mod_id) VALUES (?, ?)");
			foreach($_POST['permission'] as $module_key => $permission_values){
				$module_id = (int)$module_key;
				if($module_id <= 0 || !is_array($permission_values) || !isset($permission_values[0]) || $permission_values[0] !== 'on'){
					continue;
				}
				$stmt->bind_param("ii",$role_id,$module_id);
				if(!$stmt->execute()){
					$q2_status = "failed";
					break;
				}
			}
			$stmt->close();
		}
		$act_log = activityLog("Role permissions updated. Role Id: ".$role_id.".",$_SESSION['login_id'],$this->db);
		if($q1 && $q2_status != 'failed' && $act_log){
			mysqli_query($this->db,"COMMIT");
			return 1;
		}
		mysqli_query($this->db,"ROLLBACK");
		return "Error Occured!";
	}

	function save_user_roles(){
		mysqli_query($this->db,"START TRANSACTION");
		$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
		if($user_id <= 0){
			mysqli_query($this->db,"ROLLBACK");
			return "Invalid user selected.";
		}
		$q1 = mysqli_query($this->db,"DELETE FROM user_roles WHERE user_id = ".$user_id);
		$q2_status = "";
		if(isset($_POST['roles']) && is_array($_POST['roles'])){
			$stmt = $this->db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
			foreach($_POST['roles'] as $role_id){
				$role_id = (int)$role_id;
				if($role_id <= 0){ continue; }
				$stmt->bind_param("ii",$user_id,$role_id);
				if(!$stmt->execute()){
					$q2_status = "failed";
					break;
				}
			}
			$stmt->close();
		}
		$act_log = activityLog("User roles updated. User Id: ".$user_id.".",$_SESSION['login_id'],$this->db);
		if($q1 && $q2_status != 'failed' && $act_log){
			mysqli_query($this->db,"COMMIT");
			return 1;
		}
		mysqli_query($this->db,"ROLLBACK");
		return "Error Occured!";
	}




	// Employeee
	function save_employee(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");
		$emp_id = isset($_POST['emp_id']) ? (int)$_POST['emp_id'] : 0;
		$emp_name = mysqli_real_escape_string($this->db,$_POST['emp_name']);
		$emp_email = mysqli_real_escape_string($this->db,$_POST['emp_email']);
		$emp_ph_no = mysqli_real_escape_string($this->db,$_POST['emp_ph_no']);
		$designation_id = mysqli_real_escape_string($this->db,$_POST['designation_id']);

		if($emp_id > 0){
			$emp_update = "UPDATE employee SET ";
			$emp_update .= " emp_name = '".$emp_name."' ";
			$emp_update .= ", emp_email = '".$emp_email."' ";
			$emp_update .= ", emp_ph_no = '".$emp_ph_no."' ";
			$emp_update .= ", emp_designation_id = '".$designation_id."' ";
			$emp_update .= " WHERE emp_id = ".$emp_id;
			$save2 = $this->db->query($emp_update);
			if($save2){
				$emp_acc_qry = $this->db->query("SELECT emp_acc_no FROM employee WHERE emp_id = ".$emp_id." LIMIT 1");
				if($emp_acc_qry && $emp_acc_qry->num_rows > 0){
					$emp_acc = $emp_acc_qry->fetch_assoc();
					$emp_acc_no = (int)$emp_acc['emp_acc_no'];
					if($emp_acc_no > 0){
						$this->db->query("UPDATE accounts SET acc_name = '".$emp_name."' WHERE account_no = ".$emp_acc_no);
					}
				}
				mysqli_query($this->db,"COMMIT");
				return 1;
			}
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!".$emp_update;
		}

		$emp_added = "INSERT INTO employee SET";
		$emp_added .= " emp_name = '".$emp_name."' ";
		$emp_added .= ", emp_email = '".$emp_email."' ";
		$emp_added .= ", emp_ph_no = '".$emp_ph_no."' ";
		$emp_added .= ", emp_designation_id = '".$designation_id."' ";

		$save2 = $this->db->query($emp_added);
		$emp_id = $this->db->insert_id;
		
		$parrent_account = '600001';
		if($parrent_account != "" ){
			$querytest = "SELECT * FROM code_counters WHERE type = ".$parrent_account;
			$resultTest = mysqli_query($this->db,$querytest);
			$dataTes = mysqli_fetch_array($resultTest);

			$account_no = $dataTes['code'];
			$account_no_new = $account_no + 10;
		}

		$saveQuery = "INSERT INTO accounts SET ";
		$saveQuery .= " account_no = '".$account_no_new."' ";
		$saveQuery .= ", acc_name = '".$emp_name."' ";
		$saveQuery .= ", acc_type = '5' ";
		$saveQuery .= ", fin_statement = '2' ";
		$saveQuery .= ", parent_id = '".$parrent_account."' ";
		$saveQuery .= ", acc_cat = '1' ";
		$save3 = $this->db->query($saveQuery);
		$save4 = $this->db->query("UPDATE employee SET emp_acc_no = '".$account_no_new."' WHERE emp_id = ".$emp_id);


		$UpQuery = "UPDATE code_counters SET ";
		$UpQuery .= " code = '".$account_no_new."' ";
		$UpQuery .= " WHERE type = '".$parrent_account."' ";
		$save1 = $this->db->query($UpQuery);


		if($save2 && $save1 && $save3 && $save4)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!".$emp_added;
		}
	}


	function sync_attendance(){

		$machineConfig = "SELECT * FROM machine_config";
		$resultConfig = mysqli_query($this->db, $machineConfig);
		if (mysqli_num_rows($resultConfig) > 0) {

			$dataConfig =mysqli_fetch_array($resultConfig);
			$ip_address = $dataConfig['ip_address'];
			$port = $dataConfig['port'];
			$protocol = $dataConfig['protocol'];
			include "ZkTec/zklibrary.php";
			$zk = new ZKLibrary($ip_address, $port, $protocol);
			$zk->connect();
			$zk->disableDevice();

			$users = $zk->getUser();
			foreach($users as $key=>$user)
			{
				$uid=$key;
				$user_id=$user[0];
				$name=$user[1];
				$role=$user[2];
				$password=$user[3];
				$sql = "SELECT * FROM employee where emp_id='$user_id' ";
				$result = mysqli_query($this->db, $sql);
				if (mysqli_num_rows($result) == 0) {

					$sql2="insert into employee values('$user_id','$name','','','2','0')";
					mysqli_query($this->db,$sql2);
				}
			}

			$attendace = $zk->getAttendance();

			$result1 = 1;
			$result2 = 1;
			$result3 = 1;


			mysqli_query($this->db,"START TRANSACTION");
			foreach($attendace as $key=>$at)
			{
				$empId = $at[1];
				$dateTime = $at[3];
				$dated = date( "Y-m-d", strtotime($at[3]));
				$time = date( "H:i:s", strtotime($at[3]));
				$status = $at[2];

				$queryCheck = "SELECT * FROM attendance WHERE emp_id = ".$empId." AND dated = '".$dated."' AND status = '".$status."' ";
				$resultCheck = mysqli_query($this->db,$queryCheck);

				if($status == 0){
					if(mysqli_num_rows($resultCheck) == 0)
					{
						$studentQuery = "INSERT INTO attendance SET ";
						$studentQuery .= " emp_id = ".$empId;
						$studentQuery .= ", dated ='".$dated."' ";
						$studentQuery .= ", time ='".$time."' ";
						$studentQuery .= ", dateTime ='".$dateTime."' ";
						$studentQuery .= ", status ='".$status."' ";
						$result1 = mysqli_query($this->db, $studentQuery);
						$msg = true;
					}
				}
				else{
					$locked = 0;
					if(mysqli_num_rows($resultCheck) > 0)
					{
						while($dataRes = mysqli_fetch_array($resultCheck)){
							$locked = $dataRes['locked'];
						}
					}

					if($locked == 0){
						$upQuery = " UPDATE attendance SET ";
						$upQuery .= " del_status = '1' ";
						$upQuery .= ", del_reason ='Auto' ";

						$upQuery .= " WHERE emp_id = ".$empId;
						$upQuery .= " AND dated ='".$dated."' ";
						$upQuery .= " AND status ='".$status."' ";
						$result1 = mysqli_query($this->db, $upQuery);

						$studentQuery = "INSERT INTO attendance SET ";
						$studentQuery .= " emp_id = ".$empId;
						$studentQuery .= ", dated ='".$dated."' ";
						$studentQuery .= ", time ='".$time."' ";
						$studentQuery .= ", dateTime ='".$dateTime."' ";
						$studentQuery .= ", status ='".$status."' ";
						$result2 = mysqli_query($this->db, $studentQuery);
						$msg = true;
					}

				}
			}


			$upLockQuery = " UPDATE attendance SET ";
			$upLockQuery .= " locked = '1' ";
			$result3 = mysqli_query($this->db, $upLockQuery);

			if($result1 && $result2 && $result3)
			{
				mysqli_query($this->db,"COMMIT");
				return 1;
			}else{
				mysqli_query($this->db,"ROLLBACK");
				return "Error Occured!";
			}
		}else{
			return "Error Occured!";
		}
	}	





	public function insertSalarySlip($type_id,$monthYear,$weekSt,$weekEnd,$uId){
		$querySalarySlip = "INSERT INTO salary_slip SET ";
		$querySalarySlip .= " sp_type_id = ".$type_id;
		if($type_id == 1 || $type_id == 3){
			$querySalarySlip .= ", sp_month_year = '".$monthYear."'";
		}else{
			$querySalarySlip .= ", sp_week_st = '".$weekSt."'";
			$querySalarySlip .= ", sp_week_end = '".$weekEnd."'";
		}
		$querySalarySlip .= ", sp_created_by = '".$uId."'";
		$query1 = mysqli_query($this->db,$querySalarySlip);

		return $query1;
	}
	public function checkEntryExist($type_id,$monthYear,$weekSt,$weekEnd,$uId){
		if($type_id == 1 || $type_id == 3){			
			$qy1 = "SELECT * from salary_slip WHERE sp_type_id = ".$type_id." AND sp_month_year = '".$monthYear."' ";
			$res1 = mysqli_query($this->db,$qy1);
			if(mysqli_num_rows($res1)>0){
				return "-1";
			}else{
				return 1;
			}
		}else{
			$qy1 = "SELECT * from salary_slip WHERE sp_type_id = ".$type_id." AND sp_week_st = '".$weekSt."' AND sp_week_end = '".$weekEnd."' ";
			$res1 = mysqli_query($this->db,$qy1);
			if(mysqli_num_rows($res1)>0){
				return "-1";
			}else{
				return 1;
			}
		}
	}




























	


	function process_salary(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");
		$data = "";
		$data_act = "";
		
		

		$sal_type = mysqli_real_escape_string($this->db,$_POST['sal_type']);

		if($sal_type == 1){
			$salary_month = mysqli_real_escape_string($this->db,$_POST['salary_month']);
			$salary_month = date('Y-m-d',strtotime($salary_month));

			$exntryCheck = $this->checkEntryExist($sal_type,$salary_month,'','',$_SESSION['login_id']);
			if($exntryCheck != "-1"){
				$query1 =  $this->insertSalarySlip($sal_type,$salary_month,'','',$_SESSION['login_id']);
				$table_id = $this->db->insert_id;

				if(isset($_POST['emp_id_month'])){
					for($i=0; $i<count($_POST['emp_id_month']); $i++){
						$emp_id_month = mysqli_real_escape_string($this->db,$_POST['emp_id_month'][$i]);
						$MonthExpectedSalary = mysqli_real_escape_string($this->db,$_POST['MonthExpectedSalary'][$i]);
						$MonthIcentiveAmt = mysqli_real_escape_string($this->db,$_POST['MonthIcentiveAmt'][$i]);
						$MonthGrossAmt = mysqli_real_escape_string($this->db,$_POST['MonthGrossAmt'][$i]);


						$from_dt_this = date('Y-m-01',strtotime($salary_month)); 
						$to_dt_this = date('Y-m-t',strtotime($salary_month));

						$new_array = get_salary_employees_emp_wise($from_dt_this,$to_dt_this,1,$this->db,$emp_id_month);
						for($ii=0; $ii<count($new_array); $ii++){

							$emp_id = $new_array[$ii]['Employee ID'];
							$emp_name = $new_array[$ii]['Employee Name'];
							$total_month_days = $new_array[$ii]['Total Month Days'];
							$per_day_salary = $new_array[$ii]['Per Day Salary'];
							$per_hour_salary = $new_array[$ii]['Per Hour Salary'];
							$present_days = $new_array[$ii]['Present Days'];
							$absent_days = $new_array[$ii]['Absent Days'];
							$late_arrival_days = $new_array[$ii]['Late Arrival Days'];
							$early_departure_days = $new_array[$ii]['Early Departure Days'];
							$half_days = $new_array[$ii]['Half Days'];
							$overtime_hours_working_day = $new_array[$ii]['Overtime Hours Working Day'];
							$overtime_hours_non_working_day = $new_array[$ii]['Overtime Hours Non Working Day'];
							$overtime_hours_gazated_holiday_days = $new_array[$ii]['Overtime Hours Gazated Holiday Day'];
							$late_early_salary_days_deduct = $new_array[$ii]['Late Early Salary Days Deduct'];
							$late_early_deduction = $new_array[$ii]['Late Early Deduction'];
							$half_days_deduction_amt = $new_array[$ii]['Half Days Deduction Amount'];
							$overtime_amt_working_days = $new_array[$ii]['Overtime Amount Working Day'];
							$NWDA_amt = $new_array[$ii]['NWDA Amount'];
							$actual_salary = $new_array[$ii]['Actual Salary'];
							$expected_salary = $new_array[$ii]['Expected Salary'];
							$LESS = $new_array[$ii]['LESS'];
							$ODS = $new_array[$ii]['ODS'];
							$NWDS = $new_array[$ii]['NWDS'];
							$OTHOURSAL = $new_array[$ii]['OTHOURSAL'];
							$absent_days_deduction = $new_array[$ii]['Absent Days Deduction'];
							$NSPHS = $new_array[$ii]['NSPHS'];
							$night_shift_hours = $new_array[$ii]['Night Shift Hours'];
							$night_shift_amt = $new_array[$ii]['Night Shift Amount'];



							$AbsentDeduct = $new_array[$ii]['AbsentDeduct'];
							$LEHD = $new_array[$ii]['LEHD'];
							$OtherAllownces = $new_array[$ii]['OtherAllownces'];


						}

						$qu2 = "INSERT INTO salary_slip_info SET ";
						$qu2 .= "slip_id = '".$table_id."' ";
						$qu2 .= ",emp_id = '".$emp_id_month."'";
						$qu2 .= ",sal_type_id = '".$sal_type."'";
						$qu2 .= ",month_year = '".$salary_month."'";
						$qu2 .= ",emp_salary = '".$actual_salary."'";

						$qu2 .= ",total_month_days = '".$total_month_days."'";
						$qu2 .= ",per_day_salary = '".$per_day_salary."'";
						$qu2 .= ",per_hour_salary = '".$per_hour_salary."'";
						$qu2 .= ",present_days = '".$present_days."'";
						$qu2 .= ",absent_days = '".$absent_days."'";
						$qu2 .= ",late_arrival_days = '".$late_arrival_days."'";
						$qu2 .= ",early_departure_days = '".$early_departure_days."'";
						$qu2 .= ",half_days = '".$half_days."'";
						$qu2 .= ",overtime_hours_working_day = '".$overtime_hours_working_day."'";
						$qu2 .= ",overtime_hours_non_working_day = '".$overtime_hours_non_working_day."'";
						$qu2 .= ",overtime_hours_gazated_holiday = '".$overtime_hours_gazated_holiday_days."'";
						$qu2 .= ",late_early_salary_days_deduct = '".$late_early_salary_days_deduct."'";
						$qu2 .= ",late_early_deduction = '".$late_early_deduction."'";
						$qu2 .= ",absent_days_deduction = '".$absent_days_deduction."'";

						$qu2 .= ",half_days_deduction_amt = '".$half_days_deduction_amt."'";
						$qu2 .= ",overtime_amt_working_days = '".$overtime_amt_working_days."'";
						$qu2 .= ",NWDA_amt = '".$NWDA_amt."'";
						$qu2 .= ",LESS = '".$LESS."'";
						$qu2 .= ",ODS = '".$ODS."'";
						$qu2 .= ",NWDS = '".$NWDS."'";
						$qu2 .= ",OTHOURSAL = '".$OTHOURSAL."'";
						$qu2 .= ",NSPHS = '".$NSPHS."'";
						
						$qu2 .= ",AbsentDeduct = '".$AbsentDeduct."'";
						$qu2 .= ",LEHD = '".$LEHD."'";
						$qu2 .= ",OtherAllownces = '".$OtherAllownces."'";

						$qu2 .= ",night_shift_hours = '".$night_shift_hours."'";
						$qu2 .= ",night_shift_amt = '".$night_shift_amt."'";

						$qu2 .= ",expected_salary = '".$expected_salary."'";
						$qu2 .= ",incentive_amt = '".$MonthIcentiveAmt."'";
						$qu2 .= ",month_gross_amt = '".$MonthGrossAmt."'";

						$query2 = mysqli_query($this->db, $qu2);
					}
				}
			}else{
				return "Already Processed for This Month";
			}

		}

		if($sal_type == 2){
			$salary_week = mysqli_real_escape_string($this->db,$_POST['salary_week']);
			$week_start_st = date('Y-m-d',strtotime(trim(explode("**",$salary_week)[0])));
			$week_start_dt = date('Y-m-d',strtotime(trim(explode("**",$salary_week)[1])));

			$exntryCheck = $this->checkEntryExist($sal_type,'',$week_start_st,$week_start_dt,$_SESSION['login_id']);

			if($exntryCheck != "-1"){
				$query1 =  $this->insertSalarySlip($sal_type,'',$week_start_st,$week_start_dt,$_SESSION['login_id']);
				$table_id = $this->db->insert_id;

				if(isset($_POST['emp_id_week'])){
					for($i=0; $i<count($_POST['emp_id_week']); $i++){
						$emp_id_week = mysqli_real_escape_string($this->db,$_POST['emp_id_week'][$i]);
						$WeekSalary = mysqli_real_escape_string($this->db,$_POST['WeekSalary'][$i]);
						$WeekNoOfHours = mysqli_real_escape_string($this->db,$_POST['WeekNoOfHours'][$i]);
						$WeekExpectedSalary = mysqli_real_escape_string($this->db,$_POST['WeekExpectedSalary'][$i]);
						$WeekIncentiveAmt = mysqli_real_escape_string($this->db,$_POST['WeekIncentiveAmt'][$i]);
						$WeekGrossAmt = mysqli_real_escape_string($this->db,$_POST['WeekGrossAmt'][$i]);

						$NWDS = get_policy($this->db,'NWDS');
						$LESS = get_policy($this->db,'LESS');
						$ODS = get_policy($this->db,'ODS');
						$OTHOURSAL = get_policy($this->db,'OTHOURSAL');

						$qu2 = "INSERT INTO salary_slip_info SET ";
						$qu2 .= "slip_id = '".$table_id."' ";
						$qu2 .= ",emp_id = '".$emp_id_week."'";
						$qu2 .= ",sal_type_id = '".$sal_type."'";
						$qu2 .= ",week_start = '".$week_start_st."'";
						$qu2 .= ",week_end = '".$week_start_dt."'";
						$qu2 .= ",emp_salary = '".$WeekSalary."'";
						$qu2 .= ",no_of_hours = '".$WeekNoOfHours."'";
						$qu2 .= ",expected_salary = '".$WeekExpectedSalary."'";
						$qu2 .= ",incentive_amt = '".$WeekIncentiveAmt."'";
						$qu2 .= ",month_gross_amt = '".$WeekGrossAmt."'";
						$qu2 .= ",LESS = '".$LESS."'";
						$qu2 .= ",ODS = '".$ODS."'";
						$qu2 .= ",NWDS = '".$NWDS."'";
						$qu2 .= ",OTHOURSAL = '".$OTHOURSAL."'";


						$query2 = mysqli_query($this->db, $qu2);
					}
				}
			}else{
				return "Already Processed fo this week";
			}
		}

		if($sal_type == 3){
			$salary_impression_month = mysqli_real_escape_string($this->db,$_POST['salary_impression_month']);
			$salary_impression_month = date('Y-m-d',strtotime($salary_impression_month));

			$exntryCheck = $this->checkEntryExist($sal_type,$salary_impression_month,'','',$_SESSION['login_id']);
			if($exntryCheck != "-1"){
				$query1 =  $this->insertSalarySlip($sal_type,$salary_impression_month,'','',$_SESSION['login_id']);
				$table_id = $this->db->insert_id;

				if(isset($_POST['salary_impression_month'])){
					for($i=0; $i<count($_POST['emp_id_impression']); $i++){
						$emp_id_impression = mysqli_real_escape_string($this->db,$_POST['emp_id_impression'][$i]);
						$impressSalary = mysqli_real_escape_string($this->db,$_POST['impressSalary'][$i]);
						$noOfImpressions = mysqli_real_escape_string($this->db,$_POST['noOfImpressions'][$i]);
						$ImpExpectedSalary = mysqli_real_escape_string($this->db,$_POST['ImpExpectedSalary'][$i]);
						$ImpIncentiveAmt = mysqli_real_escape_string($this->db,$_POST['ImpIncentiveAmt'][$i]);
						$ImpGrossAmt = mysqli_real_escape_string($this->db,$_POST['ImpGrossAmt'][$i]);

						$NWDS = get_policy($this->db,'NWDS');
						$LESS = get_policy($this->db,'LESS');
						$ODS = get_policy($this->db,'ODS');
						$OTHOURSAL = get_policy($this->db,'OTHOURSAL');

						$qu2 = "INSERT INTO salary_slip_info SET ";
						$qu2 .= "slip_id = '".$table_id."' ";
						$qu2 .= ",emp_id = '".$emp_id_impression."'";
						$qu2 .= ",sal_type_id = '".$sal_type."'";
						$qu2 .= ",month_year = '".$salary_impression_month."'";
						$qu2 .= ",emp_salary = '".$impressSalary."'";
						$qu2 .= ",no_of_impressions = '".$noOfImpressions."'";
						$qu2 .= ",expected_salary = '".$ImpExpectedSalary."'";
						$qu2 .= ",incentive_amt = '".$ImpIncentiveAmt."'";
						$qu2 .= ",month_gross_amt = '".$ImpGrossAmt."'";
						$qu2 .= ",LESS = '".$LESS."'";
						$qu2 .= ",ODS = '".$ODS."'";
						$qu2 .= ",NWDS = '".$NWDS."'";
						$qu2 .= ",OTHOURSAL = '".$OTHOURSAL."'";

						$query2 = mysqli_query($this->db, $qu2);
					}
				}
			}
			else{
				return "Already Processed fo this Month";
			}
		}


		if($query1 && $query2)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!";
		}
	}


	function save_journal_voucher(){
		extract($_POST);
		mysqli_query($this->db,"START TRANSACTION");
		$data_act= '';

		$trans_date = mysqli_real_escape_string($this->db,$_POST['trans_date']);
		$narration = mysqli_real_escape_string($this->db,$_POST['narration']);
		// $table_id = $this->db->insert_id;
		$deb_amt = 0;
		$cred_amt = 0;
		if(isset($_POST['account_id'])){
			for($i=0; $i<count($_POST['account_id']); $i++){
				$account_id = mysqli_real_escape_string($this->db,$_POST['account_id'][$i]);
				$debit_amt = mysqli_real_escape_string($this->db,$_POST['debit_amt'][$i]);
				$credit_amt = mysqli_real_escape_string($this->db,$_POST['credit_amt'][$i]);

				$deb_amt += $debit_amt;
				$cred_amt += $credit_amt;

				if($account_id == 0){
					return "Account cannot be empty at Index. ".$i+1;
				}

				if($debit_amt == 0 && $credit_amt == 0){
					return "Please Enter Debit/Credit Amount at Index. ".$i+1;
				}

				if($debit_amt != 0 && $credit_amt !=0){
					return "Please Enter any one amount Debit Or Credit at Index. ".$i+1;
				}
			}

			if($deb_amt != $cred_amt){
				return "Debit and Credit Amount not matched";
			}
		}


		$query_get_voucher_no = "SELECT max(voucher_no) as voucher_no FROM vouchers WHERE v_type_id = 5";
		$result_voucher = mysqli_query($this->db,$query_get_voucher_no);
		$data_voucher = mysqli_fetch_array($result_voucher);
		$voucher_no = $data_voucher['voucher_no'];
		if($voucher_no == ""){
			$voucher_no = 10000;
		}
		$voucher_no++;
		$data_act.= 'Journal Voucher No: JV-'.$data_act.', Trans Date: '.$trans_date.' ';

		$query1_res = true;
		for($i=0; $i<count($_POST['account_id']); $i++){
			$account_id = mysqli_real_escape_string($this->db,$_POST['account_id'][$i]);
			$debit_amt = mysqli_real_escape_string($this->db,$_POST['debit_amt'][$i]);
			$credit_amt = mysqli_real_escape_string($this->db,$_POST['credit_amt'][$i]);


			$query_1 = "INSERT INTO vouchers SET ";
			$query_1 .= " voucher_no  = ".$voucher_no;
			$query_1 .= ", v_type_id  = 5";
			$query_1 .= ", account_id  = ".$account_id;
			$query_1 .= ", trans_dated  = '".$trans_date."'";
			if($debit_amt != 0){
				$query_1 .= ", debit_amount  = ".$debit_amt;
				$query_1 .= ", credit_amount  = 0";

				$data_act .= '(Debit Account: '.$account_id;
				$data_act .= ', Amount: '.$debit_amt.')';
			}
			else{
				$query_1 .= ", credit_amount  = ".$credit_amt;
				$query_1 .= ", debit_amount  = 0";

				$data_act .= '(Credit Account: '.$account_id;
				$data_act .= ', Amount: '.$credit_amt.')';
			}
			$query_1 .= ", ref_column  = 'Journal_Voucher'";
			$query_1 .= ", ref_id  = 0";
			$query_1 .= ", narration  = '".$narration."'";
			$query_1 .= ", created_by  = ".$_SESSION['login_id'];

			$query1 = $this->db->query($query_1);
			if(!$query1){
				$query1_res = false;
			}
		}

		$data_act .= '. Entry Added By '.$_SESSION['login_name'].' ';

		$act_log = activityLog("Journal Voucher has been made. Details are (".$data_act."). ",$_SESSION['login_id'],$this->db);		


		if($query1_res)
		{
			mysqli_query($this->db,"COMMIT");
			return 1;
		}else{
			mysqli_query($this->db,"ROLLBACK");
			return "Error Occured!".$emp_added;
		}
	}
}

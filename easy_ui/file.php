<link rel="stylesheet" type="text/css" href="easy_ui_datagrid/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="easy_ui_datagrid/themes/icon.css">
<script type="text/javascript" src="easy_ui_datagrid/jquery.easyui.min.js"></script>
<script type="text/javascript" src="easy_ui_datagrid/datagrid-export.js"></script>
<script type="text/javascript" src="easy_ui_datagrid/datagrid-filter.js"></script>

<script type="text/javascript">

	$(document).ready(function(){

		var $h2 = $('h2').filter(function() {
			return $(this).text() === "Complete Customer Records"; 
		});

		$h2.remove();

		var $h1 = $('h1').filter(function() {
			return $(this).text() === "Addons"; 

		});

		$h1.remove();
		$('#sidebar').remove();
		$('#contentarea').removeAttr('style');

		setInterval(function()
		{ 
			$('#message').remove();
		},10000);


		var dg = $('#dg');
		dg.datagrid({
		   toolbar: '#tb',
	    });
		dg.datagrid('enableFilter');
		
		$('#export_datagrid').click(function()
		 {
			$('#dg').datagrid('toExcel', 'datagrid.xls');
		});

		$('#print_datagrid').click(function()
		{
			$('#dg').datagrid('print', 'DataGrid');
		});

	});

</script>


<?php
include ('/var/www/html/gcs/gcs/classes/functions.php');

$company_ref=$_GET['cref'];
$query214="SELECT id from tbl_company where md5(id)='".$company_ref."'";
$result214=mysql_query($query214);
$data214=mysql_fetch_array($result214);
$company_id=$data214['id'];


if($company_id != '')
{
?>


<style type="text/css">
	.panel-htop
	{
		width: 100% !important;
	}
	.panel-header
	{
		width: 100% !important;
	}
	.panel-body
	{
		width: 100% !important;
	}
	.panel-title
	{
		text-align: center;
	}
</style>



<div class="row">
	<div class="col-md-2">
		<img src="../modules/admin/complete_customer_records/customer_record.png" border="0" style="width: 170px;float: right;">

	</div>
	<div class="col-md-8"> 
		<h1 style="text-align:center;padding-top:15px"><b></b></h1>
	</div>
	<div class="col-md-2">
		<img src="images/logos/<?php echo $company_id ?>.png" border="0" style="width: 170px;float: right;">
	</div>
</div>

<div id="tb">
	<a id="export_datagrid" class="easyui-linkbutton" data-options="iconCls:'icon-excel',text:'Export',plain:true"></a>
</div>

<table id="dg" title="Customer Records" style="width: 100%;margin: auto;" data-options="singleSelect:true,fitColumns:true,rownumbers:false,remoteSort:true,remoteFilter:true,clientPaging:false,nowrap:false,autoRowHeight:false,method:'post',url:'/gcs/gcs/modules/admin/complete_customer_records/data.php'" nowrap="false" pagination="true" pageSize="10" pageList="[10,20,30,40,50,100,200,500,1000]">
	<thead>
        <tr>
            <th style="width: 7%" data-options="field:'link_id',align:'center',sortable:true,"><b>Link ID</b></th>
            <th style="width: 8%" data-options="field:'billing_id',align:'center',sortable:true,"><b>Billing ID</b></th>
            <th style="width: 8%" data-options="field:'group_name',align:'center',sortable:true,"><b>Group</b></th>
            <th style="width: 11%" data-options="field:'vendor_name',align:'left',sortable:true,"><b>Vendor</b></th>
            <th style="width: 13%" data-options="field:'client_name',align:'left',sortable:true,"><b>Client Name</b></th>
            <th style="width: 12%" data-options="field:'address',align:'left',sortable:true,"><b>Address</b></th>
            <th style="width: 15%" data-options="field:'point_a',align:'left',sortable:true"><b>Point A</b></th>
            <th style="width: 15%" data-options="field:'point_b',align:'left',sortable:true"><b>Point B</b></th>
            <th style="width: 15%" data-options="field:'switch_port',align:'left',sortable:true"><b>Switch Port</b></th>
            <th style="width: 20%" data-options="field:'point_a_b_coordinate',align:'left',sortable:true"><b>Point A/B Co-Ordinate</b></th>
            <th style="width: 10%" data-options="field:'data_rate',align:'center',sortable:true"><b>Data Rate</b></th>
            <th style="width: 20%" data-options="field:'vlan',align:'center',sortable:true"><b>VLAN</b></th>
            <th style="width: 15%" data-options="field:'link_distance',align:'left',sortable:true"><b>Link Distance</b></th>
        </tr>
    </thead>
</table>

<?php 
}
else
{
	echo '<div class="errorbox"><strong><span class="title">Invalid URL!</span></strong><br>Please select a valid URL.</div>';
}
?>
















<!-- ////////////////////////// Data File //////////////////////////// -->

<?php
include('/var/www/html/gcs/gcs/classes/functions.php');
include('/var/www/html/gcs/gcs/modules/db.php');

$json_array = array();
$json_array_2 = array();
$json_array_3 = array();
////////////////////////////////////////////////////////////////////Pagination Rules
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$index = $i+($page-1)*$rows;


$sort_order_column = $_POST['sort'];
$sort_order = $_POST['order'];




$sort_order_column_value='order by link_id ASC';
if ($sort_order_column=='link_id') 
{
	$sort_order_column_value='order by link_id '.$sort_order.'';
}
if ($sort_order_column=='billing_id') 
{
	$sort_order_column_value='order by billing_id '.$sort_order.'';
}
if ($sort_order_column=='group_name') 
{
	$sort_order_column_value='order by group_name '.$sort_order.'';
}

if ($sort_order_column=='vendor_name') 
{
	$sort_order_column_value='order by vendor_name '.$sort_order.'';
}

if ($sort_order_column=='client_name') 
{
	$sort_order_column_value='order by client_name '.$sort_order.'';
}

if ($sort_order_column=='address') 
{
	$sort_order_column_value='order by address1_and_2 '.$sort_order.'';
}


if ($sort_order_column=='point_a') 
{
	$sort_order_column_value='order by point_a '.$sort_order.'';
}

if ($sort_order_column=='point_b') 
{
	$sort_order_column_value='order by point_b '.$sort_order.'';
}

if ($sort_order_column=='switch_port') 
{
	$sort_order_column_value='order by switch_port '.$sort_order.'';
}

if ($sort_order_column=='point_a_b_coordinate') 
{
	$sort_order_column_value='order by point_a_b_coordinate '.$sort_order.'';
}

if ($sort_order_column=='vlan') 
{
	$sort_order_column_value='order by vlan '.$sort_order.'';
}

if ($sort_order_column=='data_rate') 
{
	$sort_order_column_value='order by data_rate '.$sort_order.'';
}

if ($sort_order_column=='link_distance') 
{
	$sort_order_column_value='order by link_distance '.$sort_order.'';
}




$filterRules=$_POST['filterRules'];
$filterRules = json_decode($filterRules, true);
$filter_query=' WHERE 1 ';
for ($i=0; $i < 13 ; $i++) 
{ 
	$filter_field=$filterRules[$i]['field'];
	$filter_value=$filterRules[$i]['value'];

	if ($filter_field=='link_id')
	{
		$filter_query.='and link_id Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='billing_id')
	{
		$filter_query.='and billing_id Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='group_name')
	{
		$filter_query.='and group_name Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='vendor_name')
	{
		$filter_query.='and vendor_name Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='client_name')
	{
		$filter_query.='and client_name Like "%'.$filter_value.'%"';
	}

	if ($filter_field=='address')
	{
		$filter_query.='and address1_and_2 Like "%'.$filter_value.'%"';
	}


	if ($filter_field=='point_a')
	{
		$filter_query.='and point_a Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='point_b')
	{
		$filter_query.='and point_b Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='switch_port')
	{
		$filter_query.='and switch_port Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='point_a_b_coordinate')
	{
		$filter_query.='and point_a_b_coordinate Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='vlan')
	{
		$filter_query.='and vlan Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='data_rate')
	{
		$filter_query.='and data_rate Like "%'.$filter_value.'%"';
	}
	if ($filter_field=='link_distance')
	{
		$filter_query.='and link_distance Like "%'.$filter_value.'%"';
	}
}





$query82="SELECT count(*) as rows_total from complete_customer_record_view ".$filter_query." ".$sort_order_column_value." ";
$result82=mysql_query($query82);
$data82=mysql_fetch_array($result82);
$json_array['total']=$data82['rows_total'];



	$query211 = "SELECT * FROM complete_customer_record_view ".$filter_query." ".$sort_order_column_value." "Limit ".$index.",".$rows. ";
	$result211 = mysql_query($query211);
	while($data211 = mysql_fetch_array($result211))
	{
		$link_id = $data211['link_id'];
		$vendor_name = $data211['vendor_name'];
		$client_name = $data211['client_name'];
		$address1_and_2 = $data211['address1_and_2'];
		$group_name = $data211['group_name'];
		$point_a = $data211['point_a'];
		$point_b = $data211['point_b'];
		$vlan = $data211['vlan'];
		$switch_port = $data211['switch_port'];
		$billing_id = $data211['billing_id'];
		$data_rate = $data211['data_rate'];
		$link_distance = $data211['link_distance'];
		$point_a_b_cordinate = $data211['point_a_b_cordinate'];


		$vlan = str_replace(',',', ',$vlan);

		array_push($json_array_2,array(
			'link_id' => $link_id,
			'billing_id' => $billing_id,
			'group_name' => $group_name,
			'vendor_name' => $vendor_name,
			'client_name' => $client_name,
		    'address' => $address1_and_2,
		    'point_a' => $point_a,
		    'point_b' => $point_b,
		    'switch_port' => $switch_port,
		    'point_a_b_coordinate' => $point_a_b_cordinate,
		    'vlan' => $vlan,
		    'data_rate' => $data_rate,
		    'link_distance' => $link_distance
		));
	}

$json_array['rows']=$json_array_2;
echo json_encode($json_array);

?>
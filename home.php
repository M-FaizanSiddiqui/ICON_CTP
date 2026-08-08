<?php include 'db_connect.php';

if(in_array("40",$_SESSION['login_Permisions']))
{
    $chart_date = $conn->query("SELECT * from customers where cust_status = 0");
    $co=0;
    $customer_history=[];
    while($row_cust=$chart_date->fetch_assoc()):
        $cust_id = $row_cust['cust_id'];
        $cust_name = $row_cust['cust_name'];


        $chart_date_job = $conn->query("SELECT sum(b.quantity) as job_count from job_order as a INNER JOIN job_order_details as b on a.jd_id=b.job_id where a.customer_id = ".$cust_id);
        $row_cust_orders = $chart_date_job->fetch_assoc();

        $job_count = $row_cust_orders['job_count'];

        if($co == 0){
            $customer_history = [
                [
                    $cust_name => $job_count
                ]
            ];

        }else{
            $customer_history_new = 
            [
                $cust_name => $job_count
            ];
            array_push($customer_history, $customer_history_new);
        }
        $co++;
    endwhile;


    $jquery_data = "";
    foreach ($customer_history as $index => $user) {
        foreach ($user as $key => $value) {
            if($value == ""){
                $value = 0;
            }
            $jquery_data .= "['".$key."',".$value."],";
        }
    }
    $jquery_data = trim($jquery_data,",");

    $jquery_data;


    $month_summary = "";
    $monthWiseSummary = $conn->query("SELECT MONTHNAME(order_rec_date) AS Month,YEAR(order_rec_date) AS Year,count(jd_id) as total_order from job_order WHERE del_status = 0 group by MONTH(order_rec_date),YEAR(order_rec_date) order by order_rec_date DESC limit 12");
    $counter = 0;
    while($row_month_summary=$monthWiseSummary->fetch_assoc()){
        $counter++;

        $month_name = $row_month_summary['Month'].' - '.$row_month_summary['Year'];

        $color = 'black';
        if($counter == 1){
            $color = "green";
        }
        if($counter == 2){
            $color = "red";
        }
        if($counter == 3){
            $color = "blue";
        }
        if($counter == 4){
            $color = "grey";
        }
        if($counter == 5){
            $color = "purple";
        }
        if($counter == 6){
            $color = "pink";
        }
        if($counter == 7){
            $color = "brown";
        }
        if($counter == 8){
            $color = "orange";
        }
        if($counter == 9){
            $color = "lightgreeen";
        }
        if($counter == 10){
            $color = "lightblue";
        }

        if($counter == 11){
            $color = "black";
        }

        if($counter == 12){
            $color = "gray";
        }


        $month_summary .= '["'.$month_name.'",'.$row_month_summary['total_order'].',"'.$color.'"],';
    }

    $month_summary = trim($month_summary,",");





    // sales
    $monthlySalesData = "";
    $monthSales = $conn->query("SELECT MONTHNAME(order_rec_date) AS Month,YEAR(order_rec_date) AS Year,sum(total_job_amount) as total_jd_amount from job_order WHERE del_status = 0 group by MONTH(order_rec_date),YEAR(order_rec_date) order by order_rec_date DESC limit 12");
    $counter = 0;
    while($row_monthlySalesData=$monthSales->fetch_assoc()){
        $counter++;

        $month_name = $row_monthlySalesData['Month'].' - '.$row_monthlySalesData['Year'];
        $color = 'black';
        if($counter == 1){
            $color = "green";
        }
        if($counter == 2){
            $color = "red";
        }
        if($counter == 3){
            $color = "blue";
        }
        if($counter == 4){
            $color = "grey";
        }
        if($counter == 5){
            $color = "purple";
        }
        if($counter == 6){
            $color = "pink";
        }
        if($counter == 7){
            $color = "brown";
        }
        if($counter == 8){
            $color = "orange";
        }
        if($counter == 9){
            $color = "lightgreeen";
        }
        if($counter == 10){
            $color = "lightblue";
        }

        if($counter == 11){
            $color = "black";
        }

        if($counter == 12){
            $color = "gray";
        }

        $monthlySalesData .= '["'.$month_name.'",'.$row_monthlySalesData['total_jd_amount'].'],';
    }

    $monthlySalesData = trim($monthlySalesData,",");





    // Plates 
    $items_qty_data = "";
    $items_query = $conn->query("SELECT b.item_id,c.item_name,c.size_in_mm,sum(b.quantity) as total_qty from job_order as a INNER JOIN job_order_details as b on a.jd_id = b.job_id INNER JOIN inventory_item as c on b.item_id = c.item_id WHERE b.delete_status = 0 group by b.item_id order by b.item_id");
    $counter = 0;
    while($row_Items=$items_query->fetch_assoc()){
        $counter++;

        $item_name = $row_Items['item_name'];
        $color = 'black';
        if($counter == 1){
            $color = "green";
        }
        if($counter == 2){
            $color = "red";
        }
        if($counter == 3){
            $color = "blue";
        }
        if($counter == 4){
            $color = "grey";
        }
        if($counter == 5){
            $color = "purple";
        }
        if($counter == 6){
            $color = "pink";
        }
        if($counter == 7){
            $color = "brown";
        }
        if($counter == 8){
            $color = "orange";
        }
        if($counter == 9){
            $color = "lightgreeen";
        }
        if($counter == 10){
            $color = "lightblue";
        }

        if($counter == 11){
            $color = "black";
        }

        if($counter == 12){
            $color = "gray";
        }

        $items_qty_data .= '["'.$item_name.'",'.$row_Items['total_qty'].'],';
    }

    $items_qty_data = trim($items_qty_data,",");

    $monthlySalesData = rtrim($monthlySalesData, ",");
    $items_qty_data = rtrim($items_qty_data, ",");
    $month_summary = rtrim($month_summary, ",");

    // Decision-focused dashboard datasets. Use the latest available job date so
    // historical/demo databases still show a complete rolling twelve-month view.
    $top_customer_chart = [['Customer', 'Plate Volume']];
    $top_customer_query = $conn->query("SELECT c.cust_name, COALESCE(SUM(d.quantity),0) AS plate_volume
        FROM job_order j
        INNER JOIN job_order_details d ON d.job_id = j.jd_id AND d.delete_status = 0
        INNER JOIN customers c ON c.cust_id = j.customer_id
        WHERE j.del_status = 0
          AND j.order_rec_date >= DATE_SUB((SELECT MAX(order_rec_date) FROM job_order WHERE del_status = 0), INTERVAL 11 MONTH)
        GROUP BY c.cust_id, c.cust_name
        ORDER BY plate_volume DESC
        LIMIT 8");
    while($chart_row = $top_customer_query->fetch_assoc()) {
        $top_customer_chart[] = [$chart_row['cust_name'], (int)$chart_row['plate_volume']];
    }

    $monthly_performance_chart = [['Month', 'Jobs', 'Sales', 'Average Job Value']];
    $monthly_output_sales_chart = [['Month', 'Jobs', 'Sales']];
    $average_job_value_chart = [['Month', 'Average Job Value']];
    $monthly_performance_query = $conn->query("SELECT period_label, total_jobs, total_sales
        FROM (
            SELECT DATE_FORMAT(order_rec_date, '%b %Y') AS period_label,
                   DATE_FORMAT(order_rec_date, '%Y-%m') AS period_sort,
                   COUNT(jd_id) AS total_jobs,
                   COALESCE(SUM(total_job_amount),0) AS total_sales
            FROM job_order
            WHERE del_status = 0
            GROUP BY DATE_FORMAT(order_rec_date, '%Y-%m'), DATE_FORMAT(order_rec_date, '%b %Y')
            ORDER BY period_sort DESC
            LIMIT 12
        ) monthly_data
        ORDER BY period_sort ASC");
    while($chart_row = $monthly_performance_query->fetch_assoc()) {
        $jobs = (int)$chart_row['total_jobs'];
        $sales = (float)$chart_row['total_sales'];
        $average_value = $jobs > 0 ? round($sales / $jobs, 2) : 0;
        $monthly_performance_chart[] = [$chart_row['period_label'], $jobs, $sales, $average_value];
        $monthly_output_sales_chart[] = [$chart_row['period_label'], $jobs, $sales];
        $average_job_value_chart[] = [$chart_row['period_label'], $average_value];
    }

    $plate_demand_chart = [['Plate', 'Quantity']];
    $plate_demand_query = $conn->query("SELECT CONCAT(i.item_name, IF(i.size_in_mm = '', '', CONCAT(' - ', i.size_in_mm))) AS plate_name,
            COALESCE(SUM(d.quantity),0) AS total_quantity
        FROM job_order_details d
        INNER JOIN job_order j ON j.jd_id = d.job_id AND j.del_status = 0
        INNER JOIN inventory_item i ON i.item_id = d.item_id
        WHERE d.delete_status = 0
          AND j.order_rec_date >= DATE_SUB((SELECT MAX(order_rec_date) FROM job_order WHERE del_status = 0), INTERVAL 11 MONTH)
        GROUP BY i.item_id, i.item_name, i.size_in_mm
        ORDER BY total_quantity DESC
        LIMIT 8");
    while($chart_row = $plate_demand_query->fetch_assoc()) {
        $plate_demand_chart[] = [$chart_row['plate_name'], (int)$chart_row['total_quantity']];
    }

    $performance_rows = array_slice($monthly_performance_chart, 1);
    $latest_performance = count($performance_rows) ? $performance_rows[count($performance_rows) - 1] : ['No data', 0, 0, 0];
    $previous_performance = count($performance_rows) > 1 ? $performance_rows[count($performance_rows) - 2] : $latest_performance;
    $calculate_change = function($current, $previous) {
        if((float)$previous == 0) {
            return (float)$current > 0 ? 100 : 0;
        }
        return round((((float)$current - (float)$previous) / abs((float)$previous)) * 100, 1);
    };
    $jobs_change = $calculate_change($latest_performance[1], $previous_performance[1]);
    $sales_change = $calculate_change($latest_performance[2], $previous_performance[2]);
    $average_change = $calculate_change($latest_performance[3], $previous_performance[3]);

    $rolling_plate_total = 0;
    $rolling_plate_result = $conn->query("SELECT COALESCE(SUM(d.quantity),0) AS total_quantity
        FROM job_order j
        INNER JOIN job_order_details d ON d.job_id = j.jd_id AND d.delete_status = 0
        WHERE j.del_status = 0
          AND j.order_rec_date >= DATE_SUB((SELECT MAX(order_rec_date) FROM job_order WHERE del_status = 0), INTERVAL 11 MONTH)");
    if($rolling_plate_result && $rolling_plate_row = $rolling_plate_result->fetch_assoc()) {
        $rolling_plate_total = (int)$rolling_plate_row['total_quantity'];
    }
    $top_customer_name = isset($top_customer_chart[1]) ? $top_customer_chart[1][0] : 'No data';
    $top_customer_volume = isset($top_customer_chart[1]) ? (int)$top_customer_chart[1][1] : 0;
    $top_customer_share = $rolling_plate_total > 0 ? round(($top_customer_volume / $rolling_plate_total) * 100, 1) : 0;
    $top_plate_name = isset($plate_demand_chart[1]) ? $plate_demand_chart[1][0] : 'No data';
    $top_plate_volume = isset($plate_demand_chart[1]) ? (int)$plate_demand_chart[1][1] : 0;

    $current_month_start = date('Y-m-01');
    $current_month_end = date('Y-m-d');
    $sales_invoiced_month = 0;
    $collection_month = 0;
    $sales_vs_collection_pct = 0;
    $sales_invoice_qry = $conn->query("SELECT COALESCE(SUM(debit_amount),0) AS total_amount FROM vouchers WHERE cancel_flag=0 AND v_type_id=3 AND ref_column='job_order' AND debit_amount>0 AND trans_dated BETWEEN '".$current_month_start."' AND '".$current_month_end."'");
    if($sales_invoice_qry && $sales_invoice_row = $sales_invoice_qry->fetch_assoc()){
        $sales_invoiced_month = (float)$sales_invoice_row['total_amount'];
    }
    $collection_qry = $conn->query("SELECT COALESCE(SUM(debit_amount),0) AS total_amount FROM vouchers WHERE cancel_flag=0 AND v_type_id=4 AND ref_column='customer_payment' AND debit_amount>0 AND trans_dated BETWEEN '".$current_month_start."' AND '".$current_month_end."'");
    if($collection_qry && $collection_row = $collection_qry->fetch_assoc()){
        $collection_month = (float)$collection_row['total_amount'];
    }
    $sales_vs_collection_pct = $sales_invoiced_month > 0 ? round(($collection_month / $sales_invoiced_month) * 100, 1) : ($collection_month > 0 ? 100 : 0);
    $collection_gap = $sales_invoiced_month - $collection_month;

    $stock_item_count = 0;
    $low_stock_count = 0;
    $booked_stock_count = 0;
    $negative_stock_count = 0;
    $stock_alert_qry = $conn->query("SELECT COUNT(*) AS total_items,
            SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) AS low_stock,
            SUM(CASE WHEN qty_booked > 0 THEN 1 ELSE 0 END) AS booked_stock,
            SUM(CASE WHEN quantity < 0 THEN 1 ELSE 0 END) AS negative_stock
        FROM inventory_item
        WHERE status = 0");
    if($stock_alert_qry && $stock_alert_row = $stock_alert_qry->fetch_assoc()){
        $stock_item_count = (int)$stock_alert_row['total_items'];
        $low_stock_count = (int)$stock_alert_row['low_stock'];
        $booked_stock_count = (int)$stock_alert_row['booked_stock'];
        $negative_stock_count = (int)$stock_alert_row['negative_stock'];
    }
    $stock_alert_rows = [];
    $stock_rows_qry = $conn->query("SELECT item_id,item_name,size_in_mm,quantity,qty_booked
        FROM inventory_item
        WHERE status = 0 AND (quantity <= 0 OR qty_booked > 0)
        ORDER BY quantity ASC, qty_booked DESC
        LIMIT 5");
    while($stock_rows_qry && $stock_row = $stock_rows_qry->fetch_assoc()){
        $stock_alert_rows[] = $stock_row;
    }

    ?>


    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script type="text/javascript">
        google.charts.load('current', { packages: ['corechart'] });
        google.charts.setOnLoadCallback(drawAllCharts);

        function drawAllCharts() {
            drawPieChartSafe();
            drawJobsChartSafe();
            drawSalesChartSafe();
            drawPlatesChartSafe();
        }

/* ================= PIE ================= */
        function drawPieChartSafe() {
            try {
                var data = google.visualization.arrayToDataTable(<?php echo json_encode($top_customer_chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);

                new google.visualization.BarChart(document.getElementById('piechart'))
                .draw(data, {
                    height: 330,
                    colors: ['#f36b21'],
                    chartArea: { left: 125, top: 22, width: '66%', height: '76%' },
                    legend: { position: 'none' },
                    backgroundColor: 'transparent',
                    hAxis: { minValue: 0, textStyle: { color: '#8a8b90', fontSize: 10 }, gridlines: { color: '#eeeeef' } },
                    vAxis: { textStyle: { color: '#55565c', fontSize: 10 } },
                    animation: {
                        startup: true,
                        duration: 800,
                        easing: 'out'
                    }
                });

            } catch (e) {
                console.log("Pie error", e);
            }
        }

/* ================= JOBS ================= */
        function drawJobsChartSafe() {
            try {
                var data = google.visualization.arrayToDataTable(<?php echo json_encode($monthly_output_sales_chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);

                new google.visualization.ComboChart(document.getElementById("barchart_values"))
                .draw(data, {
                    height: 330,
                    seriesType: 'bars',
                    series: { 0: { targetAxisIndex: 0, color: '#343438' }, 1: { type: 'line', targetAxisIndex: 1, color: '#f36b21', lineWidth: 3, pointSize: 5 } },
                    chartArea: { left: 58, top: 28, width: '78%', height: '68%' },
                    legend: { position: 'bottom', textStyle: { color: '#66676c', fontSize: 10 } },
                    backgroundColor: 'transparent',
                    hAxis: { slantedText: true, slantedTextAngle: 35, textStyle: { color: '#85868c', fontSize: 9 } },
                    vAxes: {
                        0: { title: 'Jobs', minValue: 0, textStyle: { color: '#85868c', fontSize: 9 }, gridlines: { color: '#eeeeef' } },
                        1: { title: 'Sales', minValue: 0, format: 'short', textStyle: { color: '#85868c', fontSize: 9 }, gridlines: { color: 'transparent' } }
                    },
                    animation: {
                        startup: true,
                        duration: 800,
                        easing: 'out'
                    }
                });

            } catch (e) {
                console.log("Jobs error", e);
            }
        }

/* ================= SALES ================= */
        function drawSalesChartSafe() {
            try {
                var data = google.visualization.arrayToDataTable(<?php echo json_encode($average_job_value_chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);

                new google.visualization.AreaChart(document.getElementById("sales_vales"))
                .draw(data, {
                    curveType: 'function',
                    colors: ['#f36b21'],
                    height: 330,
                    chartArea: { left: 66, top: 24, width: '80%', height: '70%' },
                    legend: { position: 'none' },
                    backgroundColor: 'transparent',
                    areaOpacity: .12,
                    lineWidth: 3,
                    pointSize: 5,
                    hAxis: { slantedText: true, slantedTextAngle: 35, textStyle: { color: '#85868c', fontSize: 9 } },
                    vAxis: { minValue: 0, format: 'short', textStyle: { color: '#85868c', fontSize: 9 }, gridlines: { color: '#eeeeef' } },
                    animation: {
                        startup: true,
                        duration: 800,
                        easing: 'out'
                    }
                });

            } catch (e) {
                console.log("Sales error", e);
            }
        }

/* ================= PLATES ================= */
        function drawPlatesChartSafe() {

            var data = google.visualization.arrayToDataTable(<?php echo json_encode($plate_demand_chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);

            var options = {
                height: 330,
                colors: ['#343438'],
                chartArea: { left: 145, top: 22, width: '62%', height: '76%' },
                backgroundColor: 'transparent',
                hAxis: { minValue: 0, textStyle: { color: '#85868c', fontSize: 9 }, gridlines: { color: '#eeeeef' } },
                vAxis: { textStyle: { color: '#55565c', fontSize: 9 } },
                animation: {
                    startup: true,
                    duration: 800,
                    easing: 'out'
                },
                legend: { position: 'none' }
            };

            var chart = new google.visualization.BarChart(document.getElementById('plate_values'));
            chart.draw(data, options);
        }

/* ================= RESIZE ================= */
        window.addEventListener('resize', drawAllCharts);
    </script>
    <style>
       span.float-right.summary_icon {
        font-size: 3rem;
        position: absolute;
        right: 1rem;
        top: 0;
    }
    .imgs{
      margin: .5em;
      max-width: calc(100%);
      max-height: calc(100%);
  }
  .imgs img{
      max-width: calc(100%);
      max-height: calc(100%);
      cursor: pointer;
  }
  #imagesCarousel,#imagesCarousel .carousel-inner,#imagesCarousel .carousel-item{
      height: 60vh !important;background: black;
  }
  #imagesCarousel .carousel-item.active{
      display: flex !important;
  }
  #imagesCarousel .carousel-item-next{
      display: flex !important;
  }
  #imagesCarousel .carousel-item img{
      margin: auto;
  }
  #imagesCarousel img{
      width: auto!important;
      height: auto!important;
      max-height: calc(100%)!important;
      max-width: calc(100%)!important;
  }
  #content{
    margin-left:260px;
    transition:all 0.3s;
}

.dashboard-wrapper {
    padding-bottom: 20px;
}

/* welcome card */
.welcome-card {
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
}

/* dashboard cards */
.dash-card-link {
    text-decoration: none;
    color: inherit;
}

.dash-card {
    border-radius: 12px;
    transition: all 0.25s ease;
}

.dash-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.dash-card h3 {
    margin-top: 10px;
    font-weight: bold;
}

/* icon */
.dash-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    margin-bottom: 10px;
    font-size: 18px;
}

.dashboard-charts .chart-card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
    overflow:hidden;
    transition:all 0.3s ease;
    border:1px solid #f1f1f1;
}

.dashboard-charts .chart-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.chart-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 18px;
    border-bottom:1px solid #f3f3f3;
}

.chart-header h6{
    margin:0;
    font-weight:600;
    color:#333;
}

.chart-header small{
    color:#888;
    font-size:12px;
}

.chart-body{
    padding:10px 15px;
    animation:fadeIn 0.6s ease-in-out;
}

/* subtle fade animation */
@keyframes fadeIn{
    from {opacity:0; transform:translateY(10px);}
    to {opacity:1; transform:translateY(0);}
}

/* small status dots */
.badge-dot{
    width:10px;
    height:10px;
    border-radius:50%;
    display:inline-block;
}

.blue{background:#4e73df;}
.green{background:#1cc88a;}
.orange{background:#f6c23e;}
.purple{background:#6f42c1;}

.modern-table-card{
    border:0;
    border-radius:14px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
    overflow:hidden;
}

/* header */
.modern-table-header{
    background:#fff;
    border-bottom:1px solid #f1f1f1;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px 18px;
}

.modern-table-header h6{
    margin:0;
    font-weight:600;
}

.modern-table-header small{
    color:#888;
    font-size:12px;
}

/* table */
.modern-table thead{
    background:#f8f9fa;
}

.modern-table thead th{
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:0.5px;
    color:#666;
    border-bottom:1px solid #eee;
    padding:12px;
}

.modern-table tbody td{
    padding:14px 12px;
    font-size:14px;
    border-bottom:1px solid #f3f3f3;
}

/* hover effect */
.table-row-hover{
    transition:all 0.2s ease;
}

.table-row-hover:hover{
    background:#f9fbff;
    transform:scale(1.01);
}

/* order code pill */
.order-code{
    background:#eef2ff;
    color:#4e73df;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

/* STATUS PILLS */
.status-pill{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.status-pill.pending{
    background:#fff3cd;
    color:#856404;
}

.status-pill.progress{
    background:#cce5ff;
    color:#004085;
}

.status-pill.done{
    background:#d4edda;
    color:#155724;
}

.modern-table td,
.modern-table th{
    white-space: nowrap;      /* text wrap stop */
    vertical-align: middle;
}

.modern-table td{
    overflow: visible;
    text-overflow: ellipsis;
}
.table-responsive{
    overflow-x:auto;
}
.order-code{
    display:inline-block;
    white-space:nowrap;
}
.modern-table tbody tr:hover{
    background:#f5f9ff;
    transition:0.2s;
}
.modern-table td,
.modern-table th{
    white-space: normal !important;   /* wrap allow */
    word-wrap: break-word;
    vertical-align: middle;
}
.job-name-cell{
    max-width: 220px;       /* adjust as needed */
    white-space: normal;
    word-break: break-word;
    line-height: 1.4;
}




.dashboard-stats{
    margin-top:20px;
}

/* LINK */
.stat-link{
    text-decoration:none;
    color:inherit;
}

/* CARD */
.stat-card{
    position:relative;
    background:linear-gradient(145deg,#ffffff,#f8f9fc);
    border-radius:18px;
    padding:18px;
    display:flex;
    align-items:center;
    gap:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.05);
    transition:all 0.25s ease;
    overflow:hidden;
    border:1px solid #eef0f6;
}

/* hover */
.stat-card:hover{
    transform:translateY(-5px);
    box-shadow:0 12px 28px rgba(0,0,0,0.10);
}

/* ICON BASE */
.stat-icon{
    width:52px;
    height:52px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    flex-shrink:0;
    position:relative;
    z-index:2;
    color:#fff;
    box-shadow:0 6px 14px rgba(0,0,0,0.08);
}


/* soft glow behind icon */
/* remove heavy glow */
.stat-icon::after{
    display:none;
}


/* TEXT */
.stat-info h6{
    margin:0;
    font-size:13px;
    color:#6c757d;
    font-weight:600;
}

.stat-info h3{
    margin:4px 0 0 0;
    font-size:22px;
    font-weight:700;
    color:#222;
}

/* ===== COLORS (SOFT + MODERN) ===== */

/* INVENTORY */

/* INVENTORY (cool blue) */
.stat-card.blue .stat-icon{
    background:linear-gradient(135deg,#5b8def,#3f6ad8);
}
.stat-card.blue .stat-icon::after{
    background:#4e73df;
}

/* CUSTOMERS */
.stat-card.green .stat-icon{
    background:linear-gradient(135deg,#4dd4ac,#22b07d);
}
.stat-card.green .stat-icon::after{
    background:#1cc88a;
}

/* PENDING */
.stat-card.orange .stat-icon{
    background:linear-gradient(135deg,#f4c04d,#e0a52a);
}
.stat-card.orange .stat-icon::after{
    background:#f6c23e;
}

/* DONE */
.stat-card.dark .stat-icon{
    background:linear-gradient(135deg,#6c757d,#495057);
}
.stat-card.dark .stat-icon::after{
    background:#5a5c69;
}

/* subtle hover line */
.stat-card::before{
    content:"";
    position:absolute;
    bottom:0;
    left:0;
    height:3px;
    width:0%;
    background:linear-gradient(90deg,#4e73df,#1cc88a,#f6c23e);
    transition:0.3s;
}

.stat-card:hover::before{
    width:100%;
}
</style>

<style>
.dashboard-wrapper{max-width:1440px;margin:0 auto;padding:0 0 30px!important}.dashboard-wrapper>.row:first-child{margin-top:0!important}
.dashboard-wrapper .welcome-card{position:relative;overflow:hidden;border:0!important;border-radius:17px!important;color:#fff;background:linear-gradient(135deg,#29292c 0%,#3a3a3e 72%,#2b2b2e 100%)!important;box-shadow:0 14px 36px rgba(35,35,38,.16)!important}.dashboard-wrapper .welcome-card::before{content:'';position:absolute;top:-105px;right:-45px;width:245px;height:245px;border:42px solid rgba(243,107,33,.9);border-radius:50%}.dashboard-wrapper .welcome-card::after{content:'';position:absolute;right:165px;bottom:-85px;width:150px;height:150px;border:26px solid rgba(255,255,255,.05);border-radius:50%}
.dashboard-wrapper .welcome-card .card-body{position:relative;z-index:1;min-height:116px;padding:26px 30px!important}.dashboard-wrapper .welcome-title{display:flex;align-items:center;gap:14px}.dashboard-wrapper .welcome-icon{display:grid;place-items:center;width:46px;height:46px;border-radius:13px;font-size:18px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812);box-shadow:0 9px 20px rgba(243,107,33,.25)}.dashboard-wrapper .welcome-card h5{margin:0 0 5px!important;font-size:20px;font-weight:600;letter-spacing:-.02em;color:#fff}.dashboard-wrapper .welcome-card small{font-size:11px;color:rgba(255,255,255,.64)!important}
.dashboard-wrapper .dashboard-stats{margin:17px -7px 0!important}.dashboard-wrapper .dashboard-stats>[class*="col-"]{padding:0 7px;margin-bottom:14px}.dashboard-wrapper .stat-card{min-height:100px;padding:17px;border:1px solid #e8e9ec;border-radius:14px;background:#fff;box-shadow:0 8px 26px rgba(44,44,48,.06)}.dashboard-wrapper .stat-card:hover{transform:translateY(-3px);box-shadow:0 13px 30px rgba(44,44,48,.1)}.dashboard-wrapper .stat-card::before{height:3px;background:#f36b21}.dashboard-wrapper .stat-icon{width:46px;height:46px;border-radius:12px;font-size:16px;box-shadow:none!important}.dashboard-wrapper .stat-card.blue .stat-icon,.dashboard-wrapper .stat-card.green .stat-icon,.dashboard-wrapper .stat-card.orange .stat-icon,.dashboard-wrapper .stat-card.dark .stat-icon{color:#f36b21;background:#fff0e8}.dashboard-wrapper .stat-info h6{font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#898a90}.dashboard-wrapper .stat-info h3{margin-top:5px;font-size:25px;font-weight:650;color:#303033}
.dashboard-wrapper .dashboard-charts,.dashboard-wrapper>.container-fluid{padding:0!important}.dashboard-wrapper .dashboard-charts{margin-top:5px!important}.dashboard-wrapper .dashboard-charts>.row{margin:0 -7px!important}.dashboard-wrapper .dashboard-charts>[class*="row"]>[class*="col-"]{padding:0 7px;margin-bottom:14px}.dashboard-wrapper .dashboard-charts .chart-card{border:1px solid #e8e9ec;border-radius:15px;box-shadow:0 9px 28px rgba(44,44,48,.06)}.dashboard-wrapper .dashboard-charts .chart-card:hover{transform:none;box-shadow:0 12px 32px rgba(44,44,48,.09)}.dashboard-wrapper .chart-header{min-height:64px;padding:13px 17px;border-bottom:1px solid #ececef;background:linear-gradient(135deg,#fff,#fafafa)}.dashboard-wrapper .chart-header h6{font-size:13px;font-weight:600;color:#303033}.dashboard-wrapper .chart-header small{font-size:10px;color:#929399}.dashboard-wrapper .badge-dot{width:8px;height:8px;background:#f36b21!important}.dashboard-wrapper .chart-body{min-height:330px;padding:4px 10px 8px}
.dashboard-wrapper .chart-body>div{display:block;width:100%!important;height:320px!important;min-height:320px}
.dashboard-wrapper .dashboard-intelligence{align-items:flex-start;margin:2px -7px 13px!important}.dashboard-wrapper .dashboard-intelligence>[class*="col-"]{padding:0 7px;margin-bottom:14px}.dashboard-wrapper .insight-panel{height:auto!important;min-height:0!important;margin:0!important;padding:0!important;overflow:hidden;border:1px solid #e7e8eb;border-radius:15px;background:#fff;box-shadow:0 9px 28px rgba(44,44,48,.06)}.dashboard-wrapper .insight-panel-header{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:58px;padding:12px 16px;border-bottom:1px solid #ececef;background:linear-gradient(135deg,#fff,#fafafa)}.dashboard-wrapper .insight-panel-title{display:flex;align-items:center;gap:10px}.dashboard-wrapper .insight-panel-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:9px;color:#f36b21;background:#fff0e8}.dashboard-wrapper .insight-panel-header h6{margin:0;font-size:12px;font-weight:650;color:#343438}.dashboard-wrapper .insight-panel-header small{display:block;margin-top:2px;font-size:9px;color:#929399}.dashboard-wrapper .period-chip{padding:5px 8px;border-radius:20px;font-size:9px;font-weight:700;color:#dc5b17;background:#fff0e8}.dashboard-wrapper .performance-grid{display:grid;grid-template-columns:repeat(3,1fr);padding:13px}.dashboard-wrapper .performance-item{padding:5px 13px;border-right:1px solid #ececef}.dashboard-wrapper .performance-item:last-child{border-right:0}.dashboard-wrapper .performance-item>span{display:block;font-size:9px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#96979c}.dashboard-wrapper .performance-value{display:block;margin:5px 0 4px;font-size:19px;font-weight:650;color:#303033}.dashboard-wrapper .trend-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 6px;border-radius:6px;font-size:8px;font-weight:700}.dashboard-wrapper .trend-chip.is-up{color:#24805a;background:#eaf7f0}.dashboard-wrapper .trend-chip.is-down{color:#c95353;background:#fff0f0}.dashboard-wrapper .trend-chip.is-flat{color:#707177;background:#f0f0f2}.dashboard-wrapper .signal-list{margin:0;padding:8px 14px 11px;list-style:none}.dashboard-wrapper .signal-list li{display:flex;align-items:flex-start;gap:10px;padding:9px 2px;border-bottom:1px solid #f0f0f2}.dashboard-wrapper .signal-list li:last-child{border-bottom:0}.dashboard-wrapper .signal-marker{display:grid;place-items:center;flex:0 0 29px;width:29px;height:29px;border-radius:8px;color:#f36b21;background:#fff0e8;font-size:11px}.dashboard-wrapper .signal-copy{min-width:0}.dashboard-wrapper .signal-copy strong{display:block;overflow:hidden;font-size:10px;font-weight:650;text-overflow:ellipsis;white-space:nowrap;color:#45464b}.dashboard-wrapper .signal-copy span{display:block;margin-top:2px;font-size:9px;color:#909197}
.dashboard-wrapper .modern-table-card{margin-top:4px;border:1px solid #e7e8eb;border-radius:15px;box-shadow:0 10px 34px rgba(43,43,47,.07)}.dashboard-wrapper .modern-table-header{min-height:68px;padding:14px 18px;border-left:4px solid #f36b21;background:linear-gradient(135deg,#fff,#fafafa)!important}.dashboard-wrapper .modern-table-header h6{font-size:14px;color:#303033}.dashboard-wrapper .modern-table-header small{font-size:10px}.dashboard-wrapper .modern-table-header .badge{padding:6px 9px;border-radius:20px;font-size:9px;color:#258052!important;background:#eaf7f0!important}.dashboard-wrapper .modern-table thead th{padding:11px 12px;font-size:9px;font-weight:700;letter-spacing:.07em;color:#6d6e74;background:#f5f5f6}.dashboard-wrapper .modern-table tbody td{padding:12px;font-size:11px;color:#515258}.dashboard-wrapper .modern-table tbody tr:hover{transform:none;background:#fff8f4}.dashboard-wrapper .order-code{padding:5px 8px;border-radius:7px;font-size:10px;color:#df5913;background:#fff0e8}.dashboard-wrapper .status-pill{display:inline-flex;padding:5px 9px;font-size:9px}
.dashboard-wrapper .ops-insights{margin:0 -7px 14px!important}.dashboard-wrapper .ops-insights>[class*="col-"]{padding:0 7px;margin-bottom:14px}.dashboard-wrapper .ops-card{height:100%;overflow:hidden;border:1px solid #e7e8eb;border-radius:15px;background:#fff;box-shadow:0 9px 28px rgba(44,44,48,.06)}.dashboard-wrapper .ops-card-header{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:58px;padding:12px 16px;border-bottom:1px solid #ececef;background:linear-gradient(135deg,#fff,#fafafa)}.dashboard-wrapper .ops-card-title{display:flex;align-items:center;gap:10px}.dashboard-wrapper .ops-card-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:9px;color:#f36b21;background:#fff0e8}.dashboard-wrapper .ops-card h6{margin:0;font-size:12px;font-weight:650;color:#343438}.dashboard-wrapper .ops-card small{display:block;margin-top:2px;font-size:9px;color:#929399}.dashboard-wrapper .ops-card-body{padding:14px 16px}.dashboard-wrapper .collection-meter{height:10px;overflow:hidden;border-radius:999px;background:#f1f2f5}.dashboard-wrapper .collection-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#f36b21,#ff9d5c)}.dashboard-wrapper .collection-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:13px}.dashboard-wrapper .collection-metric{padding:10px;border:1px solid #edf0f4;border-radius:12px;background:#fbfcfd}.dashboard-wrapper .collection-metric span{display:block;font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#9698a1}.dashboard-wrapper .collection-metric strong{display:block;margin-top:5px;font-size:16px;font-weight:650;color:#252832}.dashboard-wrapper .stock-alert-list{margin:0;padding:0;list-style:none}.dashboard-wrapper .stock-alert-list li{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #f0f1f4}.dashboard-wrapper .stock-alert-list li:last-child{border-bottom:0}.dashboard-wrapper .stock-alert-name{min-width:0}.dashboard-wrapper .stock-alert-name strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;color:#343438}.dashboard-wrapper .stock-alert-name span{display:block;margin-top:2px;font-size:9px;color:#929399}.dashboard-wrapper .stock-pill{flex:0 0 auto;padding:5px 8px;border-radius:999px;font-size:9px;font-weight:800}.dashboard-wrapper .stock-pill.low{color:#c65353;background:#fff0f0}.dashboard-wrapper .stock-pill.booked{color:#b56b11;background:#fff6e6}.dashboard-wrapper .stock-summary{display:flex;gap:8px;flex-wrap:wrap}.dashboard-wrapper .stock-summary span{padding:5px 8px;border-radius:999px;font-size:9px;font-weight:800;background:#fff0e8;color:#df5913}
@media(max-width:767px){.dashboard-wrapper .welcome-card .card-body{min-height:105px;padding:21px!important}.dashboard-wrapper .welcome-card h5{font-size:17px}.dashboard-wrapper .welcome-card::before{right:-130px}.dashboard-wrapper .chart-body{min-height:300px}.dashboard-wrapper .performance-grid{grid-template-columns:1fr}.dashboard-wrapper .performance-item{padding:10px 6px;border-right:0;border-bottom:1px solid #ececef}.dashboard-wrapper .performance-item:last-child{border-bottom:0}}
</style>

<?php 

$summary = $conn->query("SELECT (SELECT count(jd_id) from job_order Where order_status = 0 AND del_status = 0) as pending_orders_count, (SELECT count(jd_id) from job_order) as order_count, (SELECT count(item_id) from inventory_item) as item_count,(SELECT count(cust_id) from customers) as customer_count");
while($row=$summary->fetch_assoc()):
    $pending_orders_count = $row['pending_orders_count'];
    $item_count = $row['item_count'];
    $order_count = $row['order_count'];
    $customer_count = $row['customer_count'];
endwhile;
?>


<div class="container-fluid px-3 dashboard-wrapper">

    <!-- WELCOME CARD -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm welcome-card">
                <div class="card-body d-flex align-items-center justify-content-between">

                    <div>
                        <div class="welcome-title"><span class="welcome-icon"><i class="fa fa-chart-line"></i></span><div><h5 class="mb-1">Welcome back, <?php echo $_SESSION['login_name']; ?></h5>
                        <small class="text-muted">Here is today’s business overview.</small></div></div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="row mt-3 g-4 dashboard-stats">

        <div class="col-lg-3 col-md-6">
            <a href="index.php?page=Stocks/view-items" class="stat-link">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fa fa-cubes"></i></div>
                    <div class="stat-info">
                        <h6>Inventory</h6>
                        <h3><?php echo $item_count ?></h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="index.php?page=Customer/view-customer" class="stat-link">
                <div class="stat-card green">
                    <div class="stat-icon"><i class="fa fa-users"></i></div>
                    <div class="stat-info">
                        <h6>Customers</h6>
                        <h3><?php echo $customer_count ?></h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="index.php?page=Jobs/orders" class="stat-link">
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fa fa-clock"></i></div>
                    <div class="stat-info">
                        <h6>Pending Jobs</h6>
                        <h3><?php echo $pending_orders_count ?></h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="index.php?page=Jobs/completed-orders" class="stat-link">
                <div class="stat-card dark">
                    <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h6>Jobs Done</h6>
                        <h3><?php echo $order_count ?></h3>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <div class="row dashboard-intelligence">
        <div class="col-12 col-xl-8">
            <div class="insight-panel" role="region" aria-labelledby="performance-snapshot-title">
                <div class="insight-panel-header">
                    <div class="insight-panel-title">
                        <span class="insight-panel-icon"><i class="fas fa-tachometer-alt"></i></span>
                        <div><h6 id="performance-snapshot-title">Latest Period Performance</h6><small>Compared with <?php echo htmlspecialchars($previous_performance[0], ENT_QUOTES, 'UTF-8'); ?></small></div>
                    </div>
                    <span class="period-chip"><?php echo htmlspecialchars($latest_performance[0], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="performance-grid">
                    <div class="performance-item">
                        <span>Jobs Processed</span>
                        <strong class="performance-value"><?php echo number_format($latest_performance[1]); ?></strong>
                        <em class="trend-chip <?php echo $jobs_change > 0 ? 'is-up' : ($jobs_change < 0 ? 'is-down' : 'is-flat'); ?>"><i class="fas <?php echo $jobs_change > 0 ? 'fa-arrow-up' : ($jobs_change < 0 ? 'fa-arrow-down' : 'fa-minus'); ?>"></i><?php echo abs($jobs_change); ?>%</em>
                    </div>
                    <div class="performance-item">
                        <span>Sales Value</span>
                        <strong class="performance-value"><?php echo number_format($latest_performance[2]); ?></strong>
                        <em class="trend-chip <?php echo $sales_change > 0 ? 'is-up' : ($sales_change < 0 ? 'is-down' : 'is-flat'); ?>"><i class="fas <?php echo $sales_change > 0 ? 'fa-arrow-up' : ($sales_change < 0 ? 'fa-arrow-down' : 'fa-minus'); ?>"></i><?php echo abs($sales_change); ?>%</em>
                    </div>
                    <div class="performance-item">
                        <span>Average Job Value</span>
                        <strong class="performance-value"><?php echo number_format($latest_performance[3], 0); ?></strong>
                        <em class="trend-chip <?php echo $average_change > 0 ? 'is-up' : ($average_change < 0 ? 'is-down' : 'is-flat'); ?>"><i class="fas <?php echo $average_change > 0 ? 'fa-arrow-up' : ($average_change < 0 ? 'fa-arrow-down' : 'fa-minus'); ?>"></i><?php echo abs($average_change); ?>%</em>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="insight-panel" role="region" aria-labelledby="business-signals-title">
                <div class="insight-panel-header">
                    <div class="insight-panel-title">
                        <span class="insight-panel-icon"><i class="fas fa-lightbulb"></i></span>
                        <div><h6 id="business-signals-title">Business Signals</h6><small>Highlights from the latest 12 active months</small></div>
                    </div>
                </div>
                <ul class="signal-list">
                    <li><span class="signal-marker"><i class="fas fa-crown"></i></span><div class="signal-copy"><strong><?php echo htmlspecialchars($top_customer_name, ENT_QUOTES, 'UTF-8'); ?></strong><span>Leading customer with <?php echo number_format($top_customer_volume); ?> plates (<?php echo $top_customer_share; ?>% share)</span></div></li>
                    <li><span class="signal-marker"><i class="fas fa-layer-group"></i></span><div class="signal-copy"><strong><?php echo htmlspecialchars($top_plate_name, ENT_QUOTES, 'UTF-8'); ?></strong><span>Highest-demand plate with <?php echo number_format($top_plate_volume); ?> uses</span></div></li>
                    <li><span class="signal-marker"><i class="fas fa-chart-line"></i></span><div class="signal-copy"><strong><?php echo $sales_change >= 0 ? 'Sales momentum is positive' : 'Sales momentum needs attention'; ?></strong><span><?php echo abs($sales_change); ?>% <?php echo $sales_change >= 0 ? 'increase' : 'decrease'; ?> from the previous active month</span></div></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row ops-insights">
        <div class="col-12 col-xl-6">
            <div class="ops-card">
                <div class="ops-card-header">
                    <div class="ops-card-title">
                        <span class="ops-card-icon"><i class="fas fa-hand-holding-usd"></i></span>
                        <div><h6>Sales vs Collection</h6><small>Current month invoice value compared with received payments</small></div>
                    </div>
                    <span class="period-chip"><?php echo date('M Y'); ?></span>
                </div>
                <div class="ops-card-body">
                    <div class="collection-meter" title="<?php echo $sales_vs_collection_pct; ?>% collected">
                        <div class="collection-fill" style="width:<?php echo min($sales_vs_collection_pct,100); ?>%"></div>
                    </div>
                    <div class="collection-grid">
                        <div class="collection-metric"><span>Invoiced</span><strong><?php echo number_format($sales_invoiced_month,0); ?></strong></div>
                        <div class="collection-metric"><span>Collected</span><strong><?php echo number_format($collection_month,0); ?></strong></div>
                        <div class="collection-metric"><span><?php echo $collection_gap >= 0 ? 'Gap' : 'Advance'; ?></span><strong><?php echo number_format(abs($collection_gap),0); ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="ops-card">
                <div class="ops-card-header">
                    <div class="ops-card-title">
                        <span class="ops-card-icon"><i class="fas fa-boxes"></i></span>
                        <div><h6>Stock / Inventory Alerts</h6><small>Items needing stock attention or already booked in jobs</small></div>
                    </div>
                    <div class="stock-summary">
                        <span><?php echo number_format($low_stock_count); ?> Low</span>
                        <span><?php echo number_format($booked_stock_count); ?> Booked</span>
                    </div>
                </div>
                <div class="ops-card-body">
                    <ul class="stock-alert-list">
                        <?php if(count($stock_alert_rows) > 0){ foreach($stock_alert_rows as $stock_row){ ?>
                            <li>
                                <div class="stock-alert-name">
                                    <strong><?php echo htmlspecialchars($stock_row['item_name']); ?></strong>
                                    <span><?php echo htmlspecialchars($stock_row['size_in_mm']); ?> · Available: <?php echo number_format((float)$stock_row['quantity'],2); ?> · Booked: <?php echo number_format((float)$stock_row['qty_booked'],2); ?></span>
                                </div>
                                <span class="stock-pill <?php echo ((float)$stock_row['quantity'] <= 0) ? 'low' : 'booked'; ?>"><?php echo ((float)$stock_row['quantity'] <= 0) ? 'Low Stock' : 'Booked'; ?></span>
                            </li>
                        <?php }}else{ ?>
                            <li><div class="stock-alert-name"><strong>Inventory is clear</strong><span>No low-stock or booked-stock alerts right now.</span></div><span class="stock-pill booked">OK</span></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-3 mt-4 dashboard-charts">

        <div class="row g-4">

            <!-- Customer Orders -->
            <div class="col-12 col-lg-6">
                <div class="chart-card">

                    <div class="chart-header">
                        <div>
                            <h6>Top Customers</h6>
                            <small>Plate volume across the latest 12 active months</small>
                        </div>
                        <span class="badge-dot blue"></span>
                    </div>

                    <div class="chart-body">
                        <div id="piechart"></div>
                    </div>

                </div>
            </div>

            <!-- Monthly Jobs -->
            <div class="col-12 col-lg-6">
                <div class="chart-card">

                    <div class="chart-header">
                        <div>
                            <h6>Monthly Output &amp; Sales</h6>
                            <small>Job volume compared with billed value</small>
                        </div>
                        <span class="badge-dot green"></span>
                    </div>

                    <div class="chart-body">
                        <div id="barchart_values"></div>
                    </div>

                </div>
            </div>

        </div>


        <div class="row g-4 mt-1">

            <div class="col-12 col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <div><h6>Average Job Value</h6><small>Monthly sales value earned per job</small></div>
                        <span class="badge-dot purple"></span>
                    </div>
                    <div class="chart-body">
                        <div id="sales_vales"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <div><h6>Top Plate Demand</h6><small>Most-used plate types in the latest 12 active months</small></div>
                        <span class="badge-dot orange"></span>
                    </div>
                    <div class="chart-body">
                        <div id="plate_values"></div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="container-fluid px-3 mt-4">

        <div class="card modern-table-card">

            <!-- HEADER -->
            <div class="card-header modern-table-header">
                <div>
                    <h6><b>Pending Orders</b></h6>
                    <small>Latest 15 active job orders</small>
                </div>

                <span class="badge bg-light text-dark">
                    Live Data
                </span>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table modern-table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Order Code</th>
                            <th>Date</th>
                            <th>Job Name</th>
                            <th>Customer</th>
                            <th>Received By</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php 
                        $order = $conn->query("SELECT a.*,b.cust_name, c.name as userName 
                            FROM job_order as a 
                            INNER JOIN customers as b on a.customer_id = b.cust_id 
                            INNER JOIN users as c on a.order_rec_by = c.id 
                            WHERE a.order_status != 2 
                            order by a.jd_id desc limit 15");

                        while($row=$order->fetch_assoc()):
                            ?>

                            <tr class="table-row-hover">

                                <td>
                                    <span class="order-code">ORD-<?php echo str_pad($row['jd_id'],5,"0",STR_PAD_LEFT); ?></span>
                                </td>

                                <td>
                                    <span class="text-muted">
                                        <?php echo date("d M Y",strtotime($row['order_rec_date'])) ?>
                                    </span>
                                </td>

                                <td class="job-name-cell">
                                    <?php echo $row['job_name'] ?>
                                </td>

                                <td>
                                    <?php echo $row['cust_name'] ?>
                                </td>

                                <td>
                                    <?php echo $row['userName'] ?>
                                </td>

                                <td>

                                    <?php if($row['order_status'] == 0){ ?>
                                        <span class="status-pill pending">Pending</span>

                                    <?php } else if($row['order_status'] == 1){ ?>
                                        <span class="status-pill progress">On Machine</span>

                                    <?php } else { ?>
                                        <span class="status-pill done">Completed</span>
                                    <?php } ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>


<script>
    $('#manage-records').submit(function(e){
        e.preventDefault()
        start_load()
        $.ajax({
            url:'ajax.php?action=save_track',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success:function(resp){
                resp=JSON.parse(resp)
                if(resp.status==1){
                    alert_toast("Data successfully saved",'success')
                    setTimeout(function(){
                        location.reload()
                    },800)

                }
                
            }
        })
    })
    $('#tracking_id').on('keypress',function(e){
        if(e.which == 13){
            get_person()
        }
    })
    $('#check').on('click',function(e){
        get_person()
    })
    function get_person(){
        start_load()
        $.ajax({
            url:'ajax.php?action=get_pdetails',
            method:"POST",
            data:{tracking_id : $('#tracking_id').val()},
            success:function(resp){
                if(resp){
                    resp = JSON.parse(resp)
                    if(resp.status == 1){
                        $('#name').html(resp.name)
                        $('#address').html(resp.address)
                        $('[name="person_id"]').val(resp.id)
                        $('#details').show()
                        end_load()

                    }else if(resp.status == 2){
                        alert_toast("Unknow tracking id.",'danger');
                        end_load();
                    }
                }
            }
        })
    }
</script>
<?php
}


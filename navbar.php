<?php include 'db_connect.php';

if(!function_exists('sidebar_module_icon')){
  function sidebar_module_icon($module_id, $fallback = ''){
    $icons = array(
      40 => 'fas fa-tachometer-alt',
      1 => 'fas fa-truck', 2 => 'fas fa-address-book', 3 => 'fas fa-user-plus', 4 => 'fas fa-warehouse', 5 => 'fas fa-dolly', 6 => 'fas fa-money-check-alt', 7 => 'fas fa-hand-holding-usd', 8 => 'fas fa-book-open',
      9 => 'fas fa-users', 10 => 'fas fa-address-card', 11 => 'fas fa-user-plus', 12 => 'fas fa-money-bill-wave', 13 => 'fas fa-cash-register', 14 => 'fas fa-book', 55 => 'fas fa-file-invoice-dollar', 15 => 'fas fa-boxes', 16 => 'fas fa-inbox',
      17 => 'fas fa-clipboard-list', 18 => 'fas fa-list-alt', 19 => 'fas fa-plus-square',
      21 => 'fas fa-layer-group', 22 => 'fas fa-chart-pie', 23 => 'fas fa-plus-circle', 24 => 'fas fa-exchange-alt', 25 => 'fas fa-random', 26 => 'fas fa-tags', 49 => 'fas fa-trash-alt', 50 => 'fas fa-recycle',
      27 => 'fas fa-briefcase', 28 => 'fas fa-hourglass-half', 29 => 'fas fa-check-circle', 30 => 'fas fa-plus-square', 56 => 'fas fa-file-signature', 57 => 'fas fa-receipt',
      34 => 'fas fa-calculator', 35 => 'fas fa-sitemap', 36 => 'fas fa-project-diagram', 37 => 'fas fa-folder-plus', 38 => 'fas fa-plus-circle', 77 => 'fas fa-balance-scale',
      39 => 'fas fa-user-shield',
      42 => 'fas fa-chart-bar', 43 => 'fas fa-chart-line', 44 => 'fas fa-money-check', 45 => 'fas fa-file-invoice-dollar', 46 => 'fas fa-hand-holding-usd', 48 => 'fas fa-file-invoice', 74 => 'fas fa-shopping-cart',
      51 => 'fas fa-puzzle-piece', 52 => 'fas fa-plus-square', 53 => 'fas fa-th-large', 54 => 'fas fa-user-lock', 801 => 'fas fa-history', 803 => 'fas fa-users-cog', 804 => 'fas fa-users',
      59 => 'fas fa-user-tie', 60 => 'fas fa-id-card', 61 => 'fas fa-user-plus', 62 => 'fas fa-calendar-check', 65 => 'fas fa-calendar-alt', 69 => 'fas fa-clipboard-list', 78 => 'fas fa-user-graduate', 70 => 'fas fa-file-invoice-dollar', 71 => 'fas fa-money-check-alt', 75 => 'fas fa-moon', 76 => 'fas fa-business-time',
      66 => 'fas fa-wallet', 67 => 'fas fa-file-invoice', 68 => 'fas fa-file-medical', 73 => 'fas fa-book'
    );

    if(isset($icons[(int)$module_id])){
      return $icons[(int)$module_id];
    }

    return trim($fallback) !== '' && trim($fallback) !== '-' ? $fallback : 'fas fa-circle';
  }
}
?>
<style>
  .collapse a{
    text-indent:10px;
  }
  nav#sidebar{
    /*background: url(assets/uploads/sidebar.jpg) !important*/
  }
  .link_report{
    margin: 1px;
    padding: 4px !important;
    color: gray;
    background: black;
    border: 0px;
    width: 100%;
    border-bottom: 1px white;
    text-align: left
  }
</style>

<!-- <nav id="sidebar" class='mx-lt-5 bg-white' > -->

  <div class="wrapper">
    <!-- Sidebar  -->
    <nav id="sidebar">
      <div class="sidebar-header">
        <a class="sidebar-brand" href="index.php?page=home" aria-label="ICON dashboard">
          <span class="sidebar-logo"><img src="assets/uploads/logo-sidebar-transparent.png" alt="ICON Brands and Beyond"></span>
        </a>
        <p class="menu-label">Main navigation</p>
      </div>

      <ul class="list-unstyled components">

        <?php
        if(isset($_SESSION['login_id'])){
          $fresh_permissions = array("0");
          $login_id_sidebar = (int)$_SESSION['login_id'];
          $perm_refresh = $conn->query("SELECT mod_id FROM module_permision WHERE user_id = ".$login_id_sidebar." UNION SELECT rp.mod_id FROM user_roles ur INNER JOIN role_permissions rp ON ur.role_id = rp.role_id INNER JOIN roles r ON ur.role_id = r.role_id WHERE ur.user_id = ".$login_id_sidebar." AND r.status = 0");
          while($perm_refresh && $perm_row = $perm_refresh->fetch_assoc()){
            $fresh_permissions[] = (string)$perm_row['mod_id'];
          }
          $_SESSION['login_Permisions'] = array_values(array_unique($fresh_permissions));
        }
        $permission_ids = isset($_SESSION['login_Permisions']) && is_array($_SESSION['login_Permisions']) ? array_map('intval', $_SESSION['login_Permisions']) : array(0);
        $permission_ids = array_values(array_unique($permission_ids));
        $permission_sql = count($permission_ids) > 0 ? implode(',', $permission_ids) : '0';
        $q = "SELECT * FROM modules_1 where m_parent_id = 0 and m_id IN (".$permission_sql.") AND show_in_menu = 1 order by ordering ASC";
        $query_modules = $conn->query($q);
        while($row=$query_modules->fetch_assoc())
        {

         $m_name = $row['m_name'];
         $m_id = $row['m_id'];
         $m_url = $row['m_url'];
         $fav_icon = sidebar_module_icon($m_id, $row['fav_icon']);
         $heading = $row['heading'];
         $show_in_menu = $row['show_in_menu'];

         if($heading == 0){
          ?>
          <li>
            <a href="index.php?page=<?= $m_url ?>" class="nav-item nav-<?= $m_id ?>">
              <span class='icon-field' ><i class="<?php echo $fav_icon ?> mr-2"></i></span> 
              <?php echo $m_name ?>
            </a>
          </li>
          <?php
        }

        else{
          ?>
          <li>
           <a href="#<?php echo $m_name ?>" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-<?php echo $m_id ?>"><span class='icon-field'><i class="<?php echo $fav_icon ?> mr-3"></i></span> <?php echo $m_name ?></a>

           <ul class="collapse list-unstyled" id="<?php echo $m_name ?>">
            <?php
            $query_modules_sub = $conn->query("SELECT * FROM modules_1 where m_id IN (".$permission_sql.") AND m_parent_id = ".$m_id." AND show_in_menu = 1 order by ordering ASC");
            while($row=$query_modules_sub->fetch_assoc()){

              $m_id_sub = $row['m_id'];
              $m_name_sub = $row['m_name'];
              $m_url_sub = $row['m_url'];
              $fav_icon_sub = sidebar_module_icon($m_id_sub, $row['fav_icon']);
              $parent_id = $m_id;

              if($parent_id == 42){
                ?>
                
                <li>
                  <button class="link_report report_model_btn" data-value="<?php echo $m_id_sub ?>^<?php echo $m_url_sub ?>" data-toggle="modal" id="report_model_btn" data-target="#report_modal" style="margin: 1px;padding: 4px !important;">
                    <span class='icon-field'>
                      <i class="<?php echo $fav_icon_sub ?> mr-3"></i>
                    </span> 
                    <?php echo $m_name_sub ?>
                  </button>
                </li>

                <?php
              }
              else if($m_id_sub == 65 || $m_id_sub == 69 || $m_id_sub == 78){
                ?>
                <li>
                  <button class="link_report report_model_btn" data-value="<?php echo $m_id_sub ?>^<?php echo $m_url_sub ?>" data-toggle="modal" id="report_model_btn" data-target="#report_modal" style="margin: 1px;padding: 4px !important;">
                    <span class='icon-field'>
                      <i class="<?php echo $fav_icon_sub ?> mr-3"></i>
                    </span> 
                    <?php echo $m_name_sub ?>
                  </button>
                </li>
                <?php
              }

              else{
                ?>
                <li>
                  <a href="index.php?page=<?= $m_url_sub ?>" class="nav-item nav-<?= $m_id_sub ?>">
                    <span class='icon-field'>
                      <i class="<?php echo $fav_icon_sub ?> mr-3"></i>
                    </span> 
                    <?php echo $m_name_sub ?>
                  </a>
                </li>
                <?php
              }
            }
            ?>
          </ul>
        </li>
        <?php
      }
    }
    ?>
  </ul>
</nav>

<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>




<style>
  #report_modal .modal-dialog{max-width:520px;margin:7vh auto}
  #report_modal .modal-content{overflow:hidden;border:0;border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(25,25,28,.25)}
  #report_modal .report-filter-header{display:flex!important;align-items:center;justify-content:space-between;min-height:82px;padding:17px 20px;border:0;border-left:5px solid #dc570f;background:linear-gradient(135deg,#f47722 0%,#e96016 100%)!important}
  #report_modal .report-filter-heading{display:flex;align-items:center;gap:13px}
  #report_modal .report-filter-icon{display:grid;place-items:center;width:43px;height:43px;border:1px solid rgba(255,255,255,.24);border-radius:12px;color:#fff;background:rgba(255,255,255,.15);font-size:17px}
  #report_modal .report-filter-title h4{margin:0;color:#fff;font-size:18px;font-weight:700;line-height:1.2}
  #report_modal .report-filter-title p{margin:4px 0 0;color:rgba(255,255,255,.82);font-size:10px;font-weight:400}
  #report_modal .report-filter-close{display:grid;place-items:center;width:34px;height:34px;margin:0;padding:0;border:0;border-radius:9px;color:#fff;background:rgba(77,31,7,.25);font-size:14px;line-height:1;opacity:1;text-shadow:none;transition:.2s ease}
  #report_modal .report-filter-close:hover{color:#fff;background:rgba(77,31,7,.42);transform:rotate(3deg)}
  #report_modal .modal-body{padding:22px 22px 8px;background:#fff}
  #report_modal .report-filter-field{position:relative;margin-bottom:16px}
  #report_modal .report-filter-field>span{display:block;margin:0 0 7px;color:#45464c;font-size:11px;font-weight:700}
  #report_modal .report-filter-field>span i{width:16px;margin-right:4px;color:#f36b21;text-align:center}
  #report_modal .form-control{width:100%;height:44px;padding:9px 12px;border:1px solid #dfe1e5;border-radius:9px;color:#3e3f44;background:#fbfbfc;font-size:12px;box-shadow:none;transition:.2s ease}
  #report_modal .form-control:hover{border-color:#c9cbd0;background:#fff}
  #report_modal .form-control:focus{border-color:#f36b21;background:#fff;box-shadow:0 0 0 3px rgba(243,107,33,.11)}
  #report_modal .report-filter-footer{display:flex;align-items:center;justify-content:flex-end;gap:9px;padding:15px 22px 20px;border:0;background:#fff}
  #report_modal .report-filter-footer .btn{min-width:105px;height:39px;margin:0;padding:8px 14px;border-radius:9px;font-size:11px;font-weight:700;box-shadow:none}
  #report_modal .report-filter-footer .btn-default{border:1px solid #dfe1e5;color:#66676c;background:#f7f7f8}
  #report_modal .report-filter-footer .btn-default:hover{border-color:#cfd1d5;color:#38393d;background:#ededee}
  #report_modal .report-filter-footer .btn-primary{min-width:145px;border-color:#f36b21;background:linear-gradient(135deg,#f47722,#e96016);box-shadow:0 8px 18px rgba(243,107,33,.22)}
  #report_modal .report-filter-footer .btn i{margin-right:6px}
  @media(max-width:575px){#report_modal .modal-dialog{margin:16px}#report_modal .report-filter-header{padding:15px}#report_modal .modal-body{padding:18px 16px 5px}#report_modal .report-filter-footer{padding:13px 16px 18px}#report_modal .report-filter-footer .btn{flex:1;min-width:0}}
</style>


<div class="modal fade" id="report_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="" target="_blank" id="report_form">
        <div class="modal-header report-filter-header">
          <div class="report-filter-heading">
            <span class="report-filter-icon"><i class="fas fa-sliders-h" aria-hidden="true"></i></span>
            <div class="report-filter-title">
              <h4 class="modal-title">Report Filters</h4>
              <p>Select the criteria required for your report.</p>
            </div>
          </div>
          <button type="button" class="close report-filter-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
          <div class="report-filter-field">
            <span class="from_dt_lb"><i class="fas fa-calendar-alt"></i> From Date</span>
            <input class="form-control" required="true" name="from_date" id="from_date" type="date"/>
          </div>
          <div class="report-filter-field">
            <span class="to_dt_lb"><i class="fas fa-calendar-check"></i> To Date</span>
            <input class="form-control" required="true" name="to_date" id="to_date" type="date"/>
          </div>

          <div class="report-filter-field">
            <span class="cust_id_lb"><i class="fas fa-user"></i> Customer</span>
            <select name="customer_id" class="form-control" id="cust_id">
              <option value="">Select customer</option>
              <?php
              $query_supp = "SELECT * FROM customers WHERE cust_status = 0";
              $result_supp = mysqli_query($conn,$query_supp);
              while($data_supp = mysqli_fetch_array($result_supp)){
                ?>
                <option value="<?php echo $data_supp['cust_id'] ?>"><?php echo $data_supp["cust_name"] ?></option>
                <?php
              }
              ?>
            </select>
          </div>

        </div>
        <div class="modal-footer report-filter-footer">
          <!-- <a href="" target="_blank" class="btn btn-primary btn-embossed" id="report_link" name="report_link">Yes</a> -->
          <button type="button" class="btn btn-default btn-embossed" data-dismiss="modal"><i class="fa fa-times"></i>Cancel</button>
          <button type="submit" class="btn btn-primary btn-embossed" id="open_rpt" name="open_rpt"><i class="fas fa-chart-bar"></i>Generate Report</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$fDt = date('Y-m-01');
$lDt = date('Y-m-d');
?>
<script>
  $(document).ready(function(){

    $('#from_date').val("<?= $fDt ?>");
    $('#to_date').val("<?= $lDt ?>");

    $('.report_model_btn').click(function(){
      var data=$(this).attr('data-value'); 
      var ans=data.split('^');
      link = ans[1];
      mod_id = ans[0];

      $('#report_modal .report-filter-field').hide();

      $('#from_date').prop('required',false);
      $('#to_date').prop('required',false);
      $('#cust_id').prop('required',false);

      // cust_id
      
      if(mod_id == '43' || mod_id == '44'  || mod_id == '65' || mod_id == '69' || mod_id == '74' || mod_id == '78'){
        $('.from_dt_lb, .to_dt_lb').closest('.report-filter-field').show();

        $('#from_date').prop('required',true);
        $('#to_date').prop('required',true);

      }else if(mod_id == '48'){
        $('.cust_id_lb, .from_dt_lb, .to_dt_lb').closest('.report-filter-field').show();

        $('#from_date').prop('required',true);
        $('#to_date').prop('required',true);
        $('#cust_id').prop('required',true);
      }

      $('#report_form').prop('action', link);
    })
  });
</script>



<style>

  @import "https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700";
  body {
   font-family: 'Poppins', sans-serif;
   background: #fafafa;
 }

 p {
   font-family: 'Poppins', sans-serif;
   font-size: 1.1em;
   font-weight: 300;
   line-height: 1.7em;
   color: #999;
 }

 a,
 a:hover,
 a:focus {
   color: inherit;
   text-decoration: none;
   transition: all 0.3s;
 }

 .navbar {
   /*padding: 15px 10px;*/
   border: none;
   border-radius: 0;
   /*margin-bottom: 40px;*/
   /*box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);*/
 }

 .navbar-btn {
   box-shadow: none;
   outline: none !important;
   border: none;
 }

 .line {
   width: 100%;
   height: 1px;
   border-bottom: 1px dashed #ddd;
   margin: 40px 0;
 }

/* ---------------------------------------------------
    SIDEBAR STYLE
    ----------------------------------------------------- */

    #sidebar{
      min-width:260px;
      max-width:260px;
      height:100vh;
      position:fixed;
      background:#0f172a;
      color:#fff;
      overflow-y:auto;
      box-shadow:4px 0 12px rgba(0,0,0,0.2);
    }

    #sidebar ul.components{
      padding:10px 0;
      margin:0;
    }

    #sidebar ul li a{
      display:flex;
      align-items:center;
      gap:10px;
      padding:8px 13px;
      color:#cbd5e1;
      border-radius:10px;
      margin:4px 10px;
      transition:0.2s;
      font-size:12px;
    }

    #sidebar ul li a:hover{
      background:#ff7a18;
      color:#fff;
    }

    #sidebar ul ul a{
      background:transparent !important;
      padding-left:5px !important;
      color:#94a3b8;
    }

    #sidebar ul ul a:hover{
      color:#fff;
      background:rgba(255,122,24,0.15) !important;
    }

    .link_report{
      background:transparent !important;
      color:#cbd5e1 !important;
      border:none;
      width:100%;
      text-align:left;
      padding:12px 18px;
      border-radius:10px;
    }

    .link_report:hover{
      background:#ff7a18 !important;
      color:#fff !important;
    }

    #content{
      margin-left:260px;
      transition:all 0.3s;
    }
    /* Scrollbar (optional) */
    #sidebar::-webkit-scrollbar{
      width:6px;
    }
    #sidebar::-webkit-scrollbar-thumb{
      background:#ff7a18;
      border-radius:10px;
    }
/* ---------------------------------------------------
    CONTENT STYLE
    ----------------------------------------------------- */

    /*#content {
      width: 100%;
      min-height: 100vh;
      transition: all 0.3s;
    }
*/
/* ---------------------------------------------------
    MEDIAQUERIES
    ----------------------------------------------------- */

    @media (max-width: 768px) {
      #sidebar {
        margin-left: -250px;
      }
      #sidebar.active {
        margin-left: 0;
      }
      #sidebarCollapse span {
        display: none;
      }
    }</style>
    <script>
      $('.nav_collapse').click(function(){
        console.log($(this).attr('href'))
        $($(this).attr('href')).collapse()
      });

      $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
          $('#sidebar').toggleClass('active');
        });
      });
    </script>

    <style>
      #sidebar{
        z-index:1040;
        top:0!important;
        bottom:0!important;
        left:0!important;
        min-width:220px;
        max-width:220px;
        height:100vh;
        padding:0 10px 18px;
        overflow-x:hidden;
        overflow-y:auto;
        color:#fff;
        background:linear-gradient(180deg,#18191d 0%,#222329 54%,#18191d 100%);
        border-right:1px solid rgba(255,255,255,.06);
        box-shadow:9px 0 34px rgba(22,22,25,.18);
        scrollbar-width:thin;
        scrollbar-color:rgba(243,107,33,.7) transparent;
        transition:transform .25s ease;
      }
      #sidebar::-webkit-scrollbar{width:5px}
      #sidebar::-webkit-scrollbar-track{background:transparent}
      #sidebar::-webkit-scrollbar-thumb{border-radius:10px;background:rgba(243,107,33,.72)}
      #sidebar .sidebar-header{position:sticky;top:0;z-index:2;margin:0 -10px 7px;padding:10px 15px 8px;background:linear-gradient(180deg,#18191d 88%,rgba(24,25,29,0))}
      #sidebar .sidebar-brand{display:block!important;margin:0!important;padding:0!important;border:0!important;background:transparent!important}
      #sidebar .sidebar-logo{position:relative;display:flex;align-items:center;justify-content:center;height:64px;padding:7px 4px 9px;border:0;border-bottom:1px solid rgba(255,255,255,.08);background:transparent;box-shadow:none}
      #sidebar .sidebar-logo::before{content:'';position:absolute;left:18%;right:18%;bottom:-1px;height:1px;background:linear-gradient(90deg,transparent,#f36b21,transparent);box-shadow:0 0 13px rgba(243,107,33,.55)}
      #sidebar .sidebar-logo img{display:block;width:100%;max-width:186px;height:auto;max-height:58px;object-fit:contain;filter:drop-shadow(0 5px 9px rgba(0,0,0,.3))}
      #sidebar .menu-label{display:flex;align-items:center;gap:9px;margin:14px 3px 8px;font-size:8px!important;font-weight:700!important;line-height:1.3!important;letter-spacing:.19em;text-transform:uppercase;color:rgba(255,255,255,.44)!important}
      #sidebar .menu-label::after{content:'';height:1px;flex:1;background:linear-gradient(90deg,rgba(255,255,255,.12),transparent)}
      #sidebar ul.components{padding:0;margin:0}
      #sidebar ul li{margin:1px 0}
      #sidebar ul li>a,#sidebar .link_report{
        position:relative;
        display:flex;
        align-items:center;
        gap:10px;
        width:100%;
        min-height:38px;
        margin:0;
        padding:8px 10px!important;
        overflow:hidden;
        border:0;
        border-radius:8px;
        outline:0;
        font-family:'Poppins',sans-serif;
        font-size:11px;
        font-weight:450;
        line-height:1.35;
        text-align:left;
        text-indent:0;
        color:rgba(255,255,255,.68)!important;
        background:transparent!important;
        transition:background .18s,color .18s,transform .18s;
      }
      #sidebar ul li>a .icon-field,#sidebar .link_report .icon-field{display:grid;place-items:center;flex:0 0 20px;width:20px;height:20px}
      #sidebar ul li>a .icon-field i,#sidebar .link_report .icon-field i{margin:0!important;font-size:12px;color:rgba(255,255,255,.4);transition:color .18s,transform .18s}
      #sidebar ul li>a:hover,#sidebar .link_report:hover{color:#fff!important;background:rgba(255,255,255,.055)!important;transform:none}
      #sidebar ul li>a:hover .icon-field i,#sidebar .link_report:hover .icon-field i{color:#ff8a4c;transform:scale(1.06)}
      #sidebar ul li>a.active,#sidebar ul li.active>a{font-weight:600;color:#fff!important;background:linear-gradient(90deg,rgba(243,107,33,.22),rgba(243,107,33,.07))!important;box-shadow:inset 3px 0 0 #f36b21,0 6px 16px rgba(0,0,0,.08)}
      #sidebar ul li>a.active .icon-field i,#sidebar ul li.active>a .icon-field i{color:#ff7b33}
      #sidebar a.dropdown-toggle::after{position:absolute;right:13px;top:50%;margin:0;border-width:4px;transform:translateY(-50%) rotate(0);transition:transform .2s}
      #sidebar a.dropdown-toggle[aria-expanded="true"]::after{transform:translateY(-50%) rotate(180deg)}
      #sidebar ul ul{position:relative;margin:3px 0 6px 20px;padding:3px 0 3px 11px!important;border-left:1px solid rgba(243,107,33,.23)}
      #sidebar ul ul li>a,#sidebar ul ul .link_report{min-height:34px;padding:7px 9px!important;border-radius:7px;font-size:10.5px;color:rgba(255,255,255,.54)!important}
      #sidebar ul ul li>a .icon-field,#sidebar ul ul .link_report .icon-field{display:none}
      #sidebar ul ul li>a:hover,#sidebar ul ul .link_report:hover{color:#fff!important;background:rgba(243,107,33,.13)!important}
      .sidebar-overlay{display:none;position:fixed;inset:0;z-index:1038;background:rgba(20,20,22,.52);backdrop-filter:blur(2px)}
      #content{margin-left:220px!important;padding:88px 24px 30px;width:calc(100% - 220px)!important;min-height:100vh;transition:margin-left .25s,width .25s}

      @media(max-width:768px){
        #sidebar{transform:translateX(-100%);margin-left:0!important}
        #sidebar.active{transform:translateX(0)}
        .sidebar-overlay.active{display:block}
        #content{margin-left:0!important;padding:84px 15px 24px;width:100%!important}
        body.sidebar-open{overflow:hidden}
      }
    </style>

    <script>
      $(function(){
        var $sidebar = $('#sidebar');
        var $overlay = $('#sidebarOverlay');
        var $toggle = $('#sidebarCollapse');

        function closeSidebar(){
          $sidebar.removeClass('active');
          $overlay.removeClass('active');
          $('body').removeClass('sidebar-open');
          $toggle.attr('aria-expanded','false').attr('aria-label','Open navigation');
        }

        $toggle.off('click').on('click.iconSidebar',function(){
          var willOpen = !$sidebar.hasClass('active');
          $sidebar.toggleClass('active',willOpen);
          $overlay.toggleClass('active',willOpen);
          $('body').toggleClass('sidebar-open',willOpen);
          $toggle.attr('aria-expanded',willOpen ? 'true' : 'false').attr('aria-label',willOpen ? 'Close navigation' : 'Open navigation');
        });

        $overlay.on('click',closeSidebar);
        $(document).on('keydown',function(e){if(e.key === 'Escape') closeSidebar();});

        var currentPage = new URLSearchParams(window.location.search).get('page') || 'home';
        $('#sidebar a.nav-item').each(function(){
          var hrefPage = new URL($(this).attr('href'),window.location.href).searchParams.get('page');
          if(hrefPage === currentPage){
            $(this).addClass('active').attr('aria-current','page');
            $(this).closest('ul.collapse').addClass('show').prev('a.dropdown-toggle').attr('aria-expanded','true');
          }
        });
      });
    </script>

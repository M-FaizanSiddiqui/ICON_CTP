<style>
  :root{
    --icon-orange:#f36b21;
    --icon-charcoal:#303033;
    --app-sidebar:220px;
    --app-header:72px;
  }

  .app-topbar{
    position:fixed;
    top:0;
    right:0;
    left:var(--app-sidebar);
    z-index:1035;
    height:var(--app-header);
    display:flex;
    align-items:center;
    padding:0 28px;
    border-bottom:1px solid #e9eaed;
    background:rgba(255,255,255,.94);
    backdrop-filter:blur(14px);
    box-shadow:0 4px 22px rgba(39,39,42,.05);
    transition:left .25s ease;
  }

  .topbar-inner{width:100%;display:flex;align-items:center;justify-content:space-between;gap:18px}
  .topbar-start,.topbar-actions{display:flex;align-items:center;gap:14px}

  .sidebar-toggle{
    display:none;
    align-items:center;
    justify-content:center;
    width:42px;
    height:42px;
    padding:0;
    border:1px solid #e7e8eb;
    border-radius:11px;
    color:#48484c;
    background:#fff;
    cursor:pointer;
  }

  .page-context small{display:block;margin-bottom:2px;font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--icon-orange)}
  .page-context strong{display:block;font-size:17px;font-weight:600;color:#303033}

  .topbar-account{position:relative}
  .account-trigger{
    display:flex;
    align-items:center;
    gap:11px;
    min-width:190px;
    padding:7px 10px 7px 8px;
    border:1px solid #e7e8eb;
    border-radius:13px;
    color:#343438;
    background:#fff;
    cursor:pointer;
    transition:border-color .2s,box-shadow .2s;
  }
  .account-trigger:hover,.account-trigger:focus{border-color:#f5b38f;box-shadow:0 5px 18px rgba(48,48,51,.08);outline:none}
  .user-avatar{width:38px;height:38px;display:grid;place-items:center;flex:0 0 38px;border-radius:11px;font-size:15px;font-weight:700;color:#fff;background:linear-gradient(145deg,#f36b21,#e35812);box-shadow:0 7px 16px rgba(243,107,33,.24)}
  .user-meta{min-width:0;flex:1;text-align:left}
  .user-meta strong{display:block;overflow:hidden;font-size:13px;font-weight:600;text-overflow:ellipsis;white-space:nowrap;color:#303033}
  .user-meta span{display:block;margin-top:1px;font-size:10px;color:#909197}
  .account-trigger .chevron{font-size:11px;color:#999aa0;transition:transform .2s}
  .account-trigger[aria-expanded="true"] .chevron{transform:rotate(180deg)}

  .topbar-account .dropdown-menu{right:0!important;left:auto!important;min-width:220px;margin-top:9px;padding:8px;border:1px solid #ebebed;border-radius:13px;box-shadow:0 16px 38px rgba(36,36,39,.14)}
  .topbar-account .dropdown-menu.show{display:block}
  .topbar-account .dropdown-item{display:flex;align-items:center;gap:11px;padding:10px 11px;border-radius:9px;font-size:13px;color:#4d4d52}
  .topbar-account .dropdown-item i{width:18px;text-align:center;color:#8a8b91}
  .topbar-account .dropdown-item:hover{color:#29292c;background:#f6f6f7}
  .topbar-account .dropdown-item.text-danger,.topbar-account .dropdown-item.text-danger i{color:#dc4b4b!important}
  .topbar-account .dropdown-divider{margin:6px 4px;border-color:#ececee}

  @media(max-width:768px){
    .app-topbar{left:0;height:64px;padding:0 16px}
    .sidebar-toggle{display:flex}
    .page-context small{display:none}
    .page-context strong{font-size:15px}
    .account-trigger{min-width:0;padding:6px;border:0;background:transparent}
    .user-meta,.account-trigger .chevron{display:none}
  }
</style>

<header class="app-topbar">
  <div class="topbar-inner">
    <div class="topbar-start">
      <button class="sidebar-toggle" id="sidebarCollapse" type="button" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false">
        <i class="fas fa-bars" aria-hidden="true"></i>
      </button>
      <div class="page-context">
        <small>ICON workspace</small>
        <strong>Business Management Portal</strong>
      </div>
    </div>

    <div class="topbar-actions">
      <div class="dropdown topbar-account">
        <button class="account-trigger" id="account_settings" type="button" aria-haspopup="true" aria-expanded="false">
          <span class="user-avatar"><?php echo strtoupper(substr($_SESSION['login_name'],0,1)); ?></span>
          <span class="user-meta">
            <strong><?php echo htmlspecialchars($_SESSION['login_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <span>Account settings</span>
          </span>
          <i class="fas fa-chevron-down chevron" aria-hidden="true"></i>
        </button>

        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="account_settings">
          <a class="dropdown-item" href="javascript:void(0)" id="manage_my_account">
            <i class="fas fa-user-cog" aria-hidden="true"></i><span>Manage account</span>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" href="ajax.php?action=logout">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i><span>Sign out</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<script>
  $('#manage_my_account').click(function(){
    $('#accountMenu').removeClass('show');
    $('#account_settings').attr('aria-expanded','false');
    uni_modal("Manage Account","manage_user.php?id=<?php echo $_SESSION['login_id'] ?>&mtype=own")
  });

  $(function(){
    var $accountButton = $('#account_settings');
    var $accountMenu = $accountButton.next('.dropdown-menu');
    $accountMenu.attr('id','accountMenu');

    $accountButton.off('click.accountMenu').on('click.accountMenu',function(e){
      e.preventDefault();
      e.stopPropagation();
      var isOpen = !$accountMenu.hasClass('show');
      $accountMenu.toggleClass('show',isOpen);
      $accountButton.attr('aria-expanded',isOpen ? 'true' : 'false');
    });

    $accountMenu.on('click',function(e){e.stopPropagation();});
    $(document).on('click.accountMenu',function(){
      $accountMenu.removeClass('show');
      $accountButton.attr('aria-expanded','false');
    });
    $(document).on('keydown.accountMenu',function(e){
      if(e.key === 'Escape'){
        $accountMenu.removeClass('show');
        $accountButton.attr('aria-expanded','false').focus();
      }
    });
  });
</script>

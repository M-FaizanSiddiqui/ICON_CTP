<?php include_once 'secure_session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php
include('./db_connect.php');
ob_start();
$system = $conn->query("SELECT * FROM system_settings limit 1")->fetch_array();
foreach($system as $k => $v){
	$_SESSION['system'][$k] = $v;
}
ob_end_flush();

if(isset($_SESSION['login_id']))
	header("location:index.php?page=home");
?>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#f36b21">

	<title><?php echo $_SESSION['system']['name'] ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

	<style>
		:root {
			--brand-orange: #f36b21;
			--brand-orange-dark: #da5713;
			--brand-charcoal: #343436;
			--muted: #6f7178;
			--line: #e4e5e8;
			--surface: #ffffff;
		}

		* { box-sizing: border-box; }

		html, body { min-height: 100%; }

		body {
			margin: 0;
			min-height: 100vh;
			font-family: 'Poppins', sans-serif;
			color: var(--brand-charcoal);
			background: #f5f4f2;
		}

		.login-shell {
			min-height: 100vh;
			display: grid;
			grid-template-columns: minmax(360px, 1.05fr) minmax(440px, .95fr);
		}

		.brand-panel {
			position: relative;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 64px;
			overflow: hidden;
			color: #fff;
			background: linear-gradient(145deg, #29292b 0%, #3b3b3e 52%, #272729 100%);
		}

		.brand-panel::before,
		.brand-panel::after {
			content: '';
			position: absolute;
			border-radius: 50%;
			pointer-events: none;
		}

		.brand-panel::before {
			width: 420px;
			height: 420px;
			top: -230px;
			right: -120px;
			border: 72px solid rgba(243, 107, 33, .92);
		}

		.brand-panel::after {
			width: 310px;
			height: 310px;
			bottom: -190px;
			left: -120px;
			border: 58px solid rgba(255, 255, 255, .06);
		}

		.brand-content {
			position: relative;
			z-index: 1;
			width: 100%;
			max-width: 620px;
		}

		.brand-mark {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: min(420px, 100%);
			padding: 22px 28px;
			margin-bottom: 46px;
			border-radius: 18px;
			background: #fff;
			box-shadow: 0 24px 60px rgba(0, 0, 0, .24);
		}

		.brand-mark img {
			display: block;
			width: 100%;
			height: auto;
		}

		.brand-kicker {
			display: flex;
			align-items: center;
			gap: 12px;
			margin: 0 0 16px;
			font-size: 12px;
			font-weight: 600;
			letter-spacing: .18em;
			text-transform: uppercase;
			color: #ff9a61;
		}

		.brand-kicker::before {
			content: '';
			width: 32px;
			height: 2px;
			background: var(--brand-orange);
		}

		.brand-content h1 {
			max-width: 540px;
			margin: 0 0 18px;
			font-size: clamp(34px, 4vw, 54px);
			line-height: 1.12;
			letter-spacing: -.035em;
			font-weight: 600;
		}

		.brand-copy {
			max-width: 500px;
			margin: 0;
			font-size: 15px;
			line-height: 1.8;
			color: rgba(255, 255, 255, .7);
		}

		.form-panel {
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 48px;
			background:
				linear-gradient(rgba(255,255,255,.78), rgba(255,255,255,.78)),
				radial-gradient(circle at 85% 10%, rgba(243,107,33,.18), transparent 34%);
		}

		.login-card {
			width: 100%;
			max-width: 440px;
		}

		.mobile-logo { display: none; }

		.form-eyebrow {
			margin: 0 0 10px;
			font-size: 13px;
			font-weight: 600;
			letter-spacing: .1em;
			text-transform: uppercase;
			color: var(--brand-orange);
		}

		.login-card h2 {
			margin: 0 0 10px;
			font-size: 34px;
			line-height: 1.2;
			letter-spacing: -.025em;
			font-weight: 600;
		}

		.form-intro {
			margin: 0 0 34px;
			font-size: 14px;
			line-height: 1.7;
			color: var(--muted);
		}

		.field { margin-bottom: 20px; }

		.field-label {
			display: block;
			margin-bottom: 8px;
			font-size: 13px;
			font-weight: 500;
			color: #414247;
		}

		.input-wrap { position: relative; }

		.input-icon {
			position: absolute;
			left: 17px;
			top: 50%;
			transform: translateY(-50%);
			font-size: 15px;
			color: #92949a;
			pointer-events: none;
		}

		.form-control {
			width: 100%;
			height: 54px;
			padding: 0 48px;
			border: 1px solid var(--line);
			border-radius: 12px;
			outline: none;
			font: inherit;
			font-size: 14px;
			color: var(--brand-charcoal);
			background: rgba(255, 255, 255, .9);
			transition: border-color .2s, box-shadow .2s, background .2s;
		}

		.form-control::placeholder { color: #a3a4a9; }

		.form-control:focus {
			border-color: var(--brand-orange);
			background: #fff;
			box-shadow: 0 0 0 4px rgba(243, 107, 33, .12);
		}

		.password-toggle {
			position: absolute;
			right: 8px;
			top: 50%;
			transform: translateY(-50%);
			width: 38px;
			height: 38px;
			border: 0;
			border-radius: 8px;
			color: #85868b;
			background: transparent;
			cursor: pointer;
		}

		.password-toggle:hover { color: var(--brand-orange); background: #fff2eb; }

		.form-options {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin: 2px 0 26px;
		}

		.remember {
			display: inline-flex;
			align-items: center;
			gap: 9px;
			font-size: 13px;
			color: var(--muted);
			cursor: pointer;
		}

		.remember input {
			width: 16px;
			height: 16px;
			margin: 0;
			accent-color: var(--brand-orange);
		}

		.btn-login {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			width: 100%;
			height: 54px;
			border: 0;
			border-radius: 12px;
			font: inherit;
			font-size: 14px;
			font-weight: 600;
			color: #fff;
			background: linear-gradient(135deg, var(--brand-orange), #f47c39);
			box-shadow: 0 14px 28px rgba(243, 107, 33, .25);
			cursor: pointer;
			transition: transform .2s, box-shadow .2s, background .2s;
		}

		.btn-login:hover:not(:disabled) {
			transform: translateY(-1px);
			background: linear-gradient(135deg, var(--brand-orange-dark), var(--brand-orange));
			box-shadow: 0 17px 32px rgba(243, 107, 33, .32);
		}

		.btn-login:disabled { opacity: .72; cursor: wait; }

		.alert {
			margin-bottom: 20px;
			padding: 13px 15px;
			border: 1px solid #f3c3c3;
			border-radius: 10px;
			font-size: 13px;
			color: #8b2525;
			background: #fff0f0;
		}

		.login-footer {
			margin: 28px 0 0;
			font-size: 12px;
			text-align: center;
			color: #98999e;
		}

		@media (max-width: 900px) {
			.login-shell { grid-template-columns: 1fr; }
			.brand-panel { display: none; }
			.form-panel { min-height: 100vh; padding: 38px 24px; }
			.mobile-logo {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 220px;
				min-height: 96px;
				padding: 12px 16px;
				margin: 0 auto 34px;
				border-radius: 14px;
				background: #fff;
				box-shadow: 0 12px 34px rgba(50, 50, 52, .1);
			}
			.mobile-logo img { display: block; width: 100%; height: auto; }
		}

		@media (max-width: 480px) {
			.form-panel { padding: 28px 20px; }
			.login-card h2 { font-size: 29px; }
			.form-intro { margin-bottom: 28px; }
		}
	</style>
</head>

<body>
	<main class="login-shell">
		<section class="brand-panel" aria-label="ICON brand introduction">
			<div class="brand-content">
				<div class="brand-mark">
					<img src="assets/uploads/logo.png" alt="ICON Brands and Beyond">
				</div>
				<p class="brand-kicker">Brands &amp; Beyond</p>
				<h1>Welcome to your business workspace.</h1>
				<p class="brand-copy">Access your ICON management portal securely and keep your day moving with everything you need in one place.</p>
			</div>
		</section>

		<section class="form-panel">
			<div class="login-card">
				<div class="mobile-logo">
					<img src="assets/uploads/logo.png" alt="ICON Brands and Beyond">
				</div>

				<p class="form-eyebrow">Secure portal</p>
				<h2>Sign in to continue</h2>
				<p class="form-intro">Enter your registered email address and password to access your account.</p>

				<form id="login-form">
					<div class="field">
						<label class="field-label" for="username">Email address</label>
						<div class="input-wrap">
							<i class="far fa-envelope input-icon" aria-hidden="true"></i>
							<input type="email" id="username" name="username" class="form-control" placeholder="name@company.com" autocomplete="username" required>
						</div>
					</div>

					<div class="field">
						<label class="field-label" for="password">Password</label>
						<div class="input-wrap">
							<i class="fas fa-lock input-icon" aria-hidden="true"></i>
							<input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
							<button type="button" class="password-toggle" id="password-toggle" aria-label="Show password" aria-pressed="false">
								<i class="far fa-eye" aria-hidden="true"></i>
							</button>
						</div>
					</div>

					<div class="form-options">
						<label class="remember" for="remember">
							<input type="checkbox" id="remember">
							<span>Remember me</span>
						</label>
					</div>

					<button type="submit" class="btn-login">
						<span>Sign in</span>
						<i class="fas fa-arrow-right" aria-hidden="true"></i>
					</button>
				</form>

				<p class="login-footer">Authorized personnel only &nbsp;&bull;&nbsp; ICON Brands &amp; Beyond</p>
			</div>
		</section>
	</main>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
		$('#password-toggle').on('click', function(){
			const password = $('#password');
			const isVisible = password.attr('type') === 'text';
			password.attr('type', isVisible ? 'password' : 'text');
			$(this).attr('aria-label', isVisible ? 'Show password' : 'Hide password');
			$(this).attr('aria-pressed', !isVisible);
			$(this).find('i').toggleClass('fa-eye fa-eye-slash');
		});

		$('#login-form').submit(function(e){
			e.preventDefault();
			const button = $('#login-form button[type="submit"]');
			button.attr('disabled', true).html('<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i><span>Signing in...</span>');

			if($(this).find('.alert').length > 0)
				$(this).find('.alert').remove();

			$.ajax({
				url: 'ajax.php?action=login',
				method: 'POST',
				data: $(this).serialize(),
				error: err => {
					console.log(err);
					button.removeAttr('disabled').html('<span>Sign in</span><i class="fas fa-arrow-right" aria-hidden="true"></i>');
				},
				success: function(resp){
					if(resp == 1){
						location.href = 'index.php?page=home';
					}else{
						$('#login-form').prepend('<div class="alert" role="alert"><i class="fas fa-exclamation-circle" aria-hidden="true"></i>&nbsp; Invalid email or password. Please try again.</div>');
						button.removeAttr('disabled').html('<span>Sign in</span><i class="fas fa-arrow-right" aria-hidden="true"></i>');
					}
				}
			});
		});
	</script>
</body>
</html>

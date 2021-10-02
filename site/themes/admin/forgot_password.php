<?php
if($_config['noFront']){
    if(HAS_USER('admin')){
        header('location:'.($_GET['next'] ? urldecode($_GET['next']) : url('')));
        exit();
    }
}
else{
    if(HAS_USER('admin')){
        header('location:'.($_GET['next'] ? urldecode($_GET['next']) : url('admin')));
        exit();
    }
    elseif(HAS_USER('public')){
        header('location:'.($_GET['next'] ? urldecode($_GET['next']) : url('')));
        exit();
    }
}
$redirect = null;
if(isset($_POST['password_reset_request'])){
    $authManager = jack_obj('dev_authentication_manager');
	$ret = $authManager->perform_password_reset_request($_POST);
	if($ret['error']){
		foreach($ret['error'] as $e){
			add_notification($e,'error');
			}
		}
	else{
		add_notification('A reset link has been sent to your email address. Please visit that link to furthur reset your password.','success');
		//header('location:'.$_SERVER['REQUEST_URI']);
		//exit();
        //$redirect = $_GET['next'] ? $_GET['next'] : url();
		}
	}
?>
<!DOCTYPE html>
<!--[if IE 8]>         <html class="ie8"> <![endif]-->
<!--[if IE 9]>         <html class="ie9 gt-ie8"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="gt-ie8 gt-ie9 not-ie"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Forgot Password - <?php echo processToRender($_config['site_name']); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">

        <!-- Open Sans font from Google CDN -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,700,300&subset=latin" rel="stylesheet" type="text/css">

        <!-- Pixel Admin's stylesheets -->
        <link href="<?php echo _path('admin'); ?>/assets/stylesheets/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo _path('admin'); ?>/assets/stylesheets/pixel-admin.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo _path('admin'); ?>/assets/stylesheets/pages.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo _path('admin'); ?>/assets/stylesheets/rtl.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo _path('admin'); ?>/assets/stylesheets/themes.min.css" rel="stylesheet" type="text/css">

        <!--[if lt IE 9]>
        <script src="<?php echo _path('admin'); ?>/assets/javascripts/ie.min.js"></script>
        <![endif]-->
        <style type="text/css">
            .background_holder{
                background-size: cover;
            <?php
            if($_config['admin_page_bg_image']){
                ?>
                background-image: url('<?php echo image_url($_config['admin_page_bg_image'])?>');
            <?php
            }
        ?>
            }
            .background_holder, .the_background {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: -2;
            }
            .the_background{
            <?php echo $_config['admin_page_bg_color'] ? 'background-color: '.$_config['admin_page_bg_color'].';' : ''?>
            <?php echo $_config['admin_page_bg_color_opacity'] ? 'opacity:'.$_config['admin_page_bg_color_opacity'].';' : ''?>
                z-index: -1;
            }
            .loginFormBG {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: -1;
                background-color: <?php echo $_config['admin_page_login_form_bg_color'] ? $_config['admin_page_login_form_bg_color'] : '#fff'?>;
                opacity: <?php echo $_config['admin_page_login_form_bg_color_opacity'] ? $_config['admin_page_login_form_bg_color_opacity'] : '1'?>;
            }
            .login_form{
                background:none;
                border:0 none;
                margin-top: 30px !important;
            <?php
            if($_config['login_form_position']){
                if($_config['login_form_position'] == 'left'){
                    ?>
                float:left;
                margin-left: 30px !important;
            <?php
            }
        elseif($_config['login_form_position'] == 'right'){
            ?>
                float:right;
                margin-right: 30px !important;
            <?php
            }
        }
    ?>
            }
            .theme-default .demo-logo{
                background-color: transparent;
            }
            .signin-header .logo{
            <?php
             if($_config['admin_page_header_text_color']){
                ?>
                color: <?php echo $_config['admin_page_header_text_color']?> !important;
            <?php
             }
         ?>
            }
            .signin-header{
            <?php
             if($_config['admin_page_header_bg_color']){
                ?>
                background-color: <?php echo $_config['admin_page_header_bg_color']?> !important;
            <?php
             }
         ?>

            }
        </style>
    </head>
    <body class="theme-default page-signin-alt">
        <div class="background_holder">
            <div class="the_background"></div>
        </div>
        <div class="signin-header">
            <a href="javascript:" class="logo">
                <?php
                if($_config['admin_page_logo']){
                    ?>
                    <div class="demo-logo"><img src="<?php echo image_url($_config['admin_page_logo']) ?>" alt="" style="margin-top: -4px;height: 40px;"></div>&nbsp;-&nbsp;
                    <?php
                }
                ?>
                <?php
                if($_config['admin_page_heading']) echo $_config['admin_page_heading'];
                else echo 'Forgot Password';
                ?>
            </a> <!-- / .logo -->
            <!--a href="pages-signup-alt.html" class="btn btn-primary">Sign Up</a-->
        </div> <!-- / .header -->
        <!-- Form -->
        <form action="" id="registration-form_id" class="panel login_form" method="post">
            <div class="loginFormBG"></div>
            <h1 class="form-header" style="color:<?php echo $_config['admin_login_prompt_text_color'] ? $_config['admin_login_prompt_text_color'] : '#555555'?>;margin-top: 0">Forgot Password</h1>
            <?php echo $notify_user->get_notification(); ?>
            <div style="margin-bottom: 10px;"></div>
            <div class="note note-info">
                <h4 class="note-title">Please Note</h4>
                A password reset link will be sent to your email address. After receiving the email, click on that link and confirm that you want to reset your password.
            </div>
            <div class="form-group">
                <input type="text" name="user_email" id="user_email" class="form-control input-lg" placeholder="Email">
            </div> <!-- / Username -->

            <div class="form-actions">
                <input type="submit" value="Reset Password" name="password_reset_request" class="btn btn-primary btn-block btn-lg">
            </div> <!-- / .form-actions -->
            <br />
            Already registered? <a href="<?php echo url('login'); ?>">Login Now</a>
        </form>
        <!-- / Form -->

        <!-- Get jQuery from Google CDN -->
        <!--[if !IE]> -->
        <script type="text/javascript"> window.jQuery || document.write('<script src="<?php echo theme_path().'/assets/javascripts/jquery-2.0.3.min.js'?>">'+"<"+"/script>"); </script>
        <!-- <![endif]-->
        <!--[if lte IE 9]>

        <script type="text/javascript"> window.jQuery || document.write('<script src="<?php echo theme_path().'/assets/javascripts/jquery-1.8.3.min.js'?>">'+"<"+"/script>"); </script>
        <![endif]-->


        <!-- Pixel Admin's javascripts -->
        <script src="<?php echo _path('admin'); ?>/assets/javascripts/bootstrap.min.js"></script>
        <script src="<?php echo _path('admin'); ?>/assets/javascripts/pixel-admin.min.js"></script>
    </body>
</html>
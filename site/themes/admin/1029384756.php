<?php
reRouteLoggedInUser();

$authManager = jack_obj('dev_authentication_manager');

$separator = '%3DEVS_SEPARATOR%';
$encryptKey = '343898+3DEVS_SEPARATOR';

if(isset($_POST['login_request'])){
    $ret = $authManager->perform_login($_POST);
    if($ret['error']){
        foreach($ret['error'] as $e){
            add_notification($e,'error');
            }
        }
    else{
        $_SESSION['admin_loggedin'] = 1;
        add_notification('Login Successful.','success');
        //TODO: Conditional Remember Me Functions
        if($_POST['remember_me']){
            $userAuthData = $_config['user']['pk_user_id'].$separator.$_config['user']['user_name'].$separator.$_config['user']['user_email'].$separator.$_config['user']['user_password'];
            $e_userAuthData = encryptData($userAuthData, $encryptKey);

            _setCookie('siteAuth',$e_userAuthData,'30d');
            }
        if($_config['noFront'])
            header('location:'.($_GET['next'] ? urldecode($_GET['next']) : url('')));
        else
            header('location:'.($_GET['next'] ? urldecode($_GET['next']) : url('admin')));
        exit();
        }
    }

$PAGE_NAME = $_config['admin_page_heading'] ? $_config['admin_page_heading'] : 'Admin Login';

include('outerHeader.php')
?>
	<form action="" style="" id="signin-form_id" class="panel login_form" method="post">
        <div class="loginFormBG"></div>
        <h1 class="form-header" style="margin-top: 0"><?php echo $_config['admin_login_prompt_text'] ? $_config['admin_login_prompt_text'] : 'Sign in to your Account'?></h1>
        <?php echo $notify_user->get_notification(); ?>
        <div style="margin-bottom: 10px;"></div>
		<div class="form-group">
			<input type="text" name="user_email" id="user_email" class="form-control input-lg" placeholder="Email">
		</div>

		<div class="form-group signin-password">
			<input type="password" name="user_password" id="user_password" class="form-control input-lg" placeholder="Password">
			<!-- href="<?php echo url('admin/forgot_password'); ?>" class="forgot">Forgot Password?</a-->
		</div>
		<div class="form-actions">
			<input type="submit" value="Sign In" name="login_request" class="btn btn-primary btn-block btn-lg">
		</div>
	</form>
<?php include('outerFooter.php') ?>
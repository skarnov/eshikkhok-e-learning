<?php
reRouteLoggedInUser();

$authManager = jack_obj('dev_authentication_manager');

if(isset($_POST['login_request'])){
    $ret = $authManager->perform_login($_POST);
    if($ret['error']){
        foreach($ret['error'] as $e){
            add_notification($e,'error');
        }
    }
    else{
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
?>
<form method="post" action="">
    <input type="text" name="user_email" id="user_email" class="form-control input-lg" placeholder="User Name / Email">
    <input type="password" name="user_password" id="user_password" class="form-control input-lg" placeholder="Password">
    <input type="submit" name="login_request" value="Login" />
</form>

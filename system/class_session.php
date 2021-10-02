<?php
if(isset($_SESSION['user_id'])){
    $cacheID = 'user_'.$_SESSION['user_id'];
    $user_data = getCache($cacheID);
    if(!$user_data){
	    $user_data = $devdb->get_row("SELECT * FROM dev_users WHERE dev_users.`pk_user_id` = '".$_SESSION['user_id']."'");
	    $user_data['roles_list'] = $user_data && strlen($user_data['user_roles']) ? explode(',',$user_data['user_roles']) : array();
        setCache($user_data,$cacheID);
        }
	if($user_data){
		$_config['user'] = $user_data;
        $_config['user']['rel_user_picture'] = user_picture($_config['user']['user_picture']);
        $cacheID = 'user_meta_'.$_SESSION['user_id'];
        $user_meta_data = getCache($cacheID);
        if(!$user_meta_data){
            $sql = "SELECT * FROM dev_metas WHERE fk_id = '".$_SESSION['user_id']."' AND fk_type = 'user'";
            $user_meta_data = $devdb->get_results($sql, 'meta_name');
            setCache($user_meta_data, $cacheID);
            }
        $_config['user']['meta'] = $user_meta_data;
        }
	else{
		$_config['user'] = NULL;
        $_SESSION['user_id']= NULL;
        $_SESSION['admin_loggedin'] = 0;
        }
	}
elseif(isset($_config['allow_facebook_login'])){
    if(
        (isset($_config['app_id']) && $_config['app_id'])
        && (isset($_config['app_secret']) && $_config['app_secret'])
        ){

        }
    }
function get_relative_user_picture(){
    global $_config;

    if($_config['user']['user_picture']) $_config['user']['rel_user_picture'] = image_url($_config['user']['user_picture']);
    else $_config['user']['rel_user_picture'] = _default_user();
    }
function LOGIN_THE_USER($user, $allow_admin_login = false){
    session_start();
	global $_config;
    if($user['user_status'] == 'active'){
        $_SESSION['user_id'] = $user['pk_user_id'];
        $_config['user'] = $user;
        if($allow_admin_login)
            if($_config['user']['user_type'] == 'admin') $_SESSION['admin_loggedin'] = 1;
        get_relative_user_picture();
        user_activity::add_activity($_config['user']['user_fullname'].' has logged in.', 'success', 'login');
        return 1;
        }
    return 0;
	}
function LOGOUT_THE_USER($reRouteToHome = false){
    global $_config;

    session_start();
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
            );
        }
	session_destroy();

    user_activity::add_activity($_config['user']['user_fullname'].' has logged out.', 'success', 'logout');

    _deleteCookie('siteAuth');
    if($reRouteToHome){
        header('location:'.url(''));
        exit();
        }
	}
function GET_LOGOUT_LINK(){
	return url('logout?next='.current_url());
	}
function GET_LOGIN_LINK(){
    return url('login?next='.current_url());
    }
function HAS_USER($type = null){
    //HAS_USER() : will return any type of user
    //HAS_USER('public') : will check for public user only
    //HAS_USER('admin') : will check for admin user only
	global $_config;

	if($type){
        if($type == 'public'){
            if($_SESSION['user_id'] && $_config['user']['user_type'] == 'public') return true;
            else return false;
            }
        elseif($type == 'sadmin'){
            if($_SESSION['user_id'] && $_config['user']['user_type'] == 'admin' && in_array('-1', $_config['user']['roles_list']) !== false && $_SESSION['admin_loggedin']) return true;
            else return false;
            }
        elseif($type == 'admin'){
            if($_SESSION['user_id'] && $_config['user']['user_type'] == 'admin' && $_SESSION['admin_loggedin']) return true;
            else return false;
            }
		}
    elseif($_SESSION['user_id']) return true;
	else return false;
	}
function IS_SADMIN($user_role){
    if($user_role == -1) return true;
    else return false;
    }
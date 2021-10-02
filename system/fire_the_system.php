<?php
$JUMP_TO_EVENT = null;

//boot_loader event
if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'boot_loader'){
    doAction('before_boot_loader_event');
    }
if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'boot_loader'){
    doAction('after_boot_loader_event');
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'boot'){
    //boot event
    doAction('before_boot_event');

    if (isset($_POST['system_config_form_submitted']))
        include(_path('system_setup','absolute').'/system_setup.php');

    if ($DB_CONFIG_EXISTS) {
        //checking if DB settings are available
        if (!$devdb) {
            include(_path('system_setup', 'absolute') . '/missing_db.php');
            }
        else {
            $_GET = $devdb->deep_escape($_GET);
            $gMan = new GateMan($_GET['DEV_URL_PARAM']);
            }
        }
    else include(_path('system_setup','absolute').'/system_setup.php');
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'boot'){
    doAction('after_boot_event');
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'authentication'){
    //authentication event
    doAction('before_authentication_event');

    $ret = array();
    //if call is admin, then check if admin open
    //pre($_config);
    if($gMan->call_type == 'admin' && $_config['lock_admin'] == 'true'){
        //admin is locked, check if has sAdmin logged in
        if(!HAS_USER('sadmin')){
            $ret['error'][] = 'Admin is locked, please contact a Super Admin.';
            $JUMP_TO_EVENT = 'terminate';
            }
        }
    elseif($gMan->call_type == 'admin'){
        //if call is admin, then check if admin user logged in
        if(!HAS_USER('admin')){
            if($gMan->url_obj[0] == 'admin'){
                add_notification('You are not logged in or your session has been expired. Please login.', 'error');
                header('location:'.url($_config['admin_login_page']));
                exit();
                }
            else{
                add_notification('Only admins can log into admin panel.', 'error');
                header('location:'.url($_config['admin_login_page']));
                exit();
                }
            }
        }

    if(isset($ret['error'])){
        foreach($ret['error'] as $e){
            ?>
            <p><?php echo $e?></p>
            <?php
            }
        $JUMP_TO_EVENT = 'terminate';
        }
    }
if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'authentication'){
    doAction('after_authentication_event');
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'execute_plugins'){
    //execute_plugins event
    doAction('before_execute_plugins_event');

    $jacker->discover_jacks();

    }
if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'execute_plugins'){
    doAction('after_execute_plugins_event');
    }

//Everything has been loaded, just check if it is an api call or not
//If API, then execute and leave :)

if($gMan->call_type == 'api'){
    $api_return = null;

    $_u = $gMan->url_obj;
    $plugin = $_u[1] ? $_u[1] : NULL;
    $function = $_u[2] ? $_u[2] : NULL;

    if(!$plugin || !$function){
        $api_return = array('error' => 'Invalid API');
    }
    else{
        if(isset($apis->all_apis[$plugin]) && isset($apis->all_apis[$plugin][$function])){
            $param = array(
                'post' => $_POST,
                'get' => $_GET,
            );
            $api_return = api_call($plugin, $function, $param);
            //$api_return = $apis->all_apis[$plugin][$function]['jack']->$function($param);
        }
        else{
            $api_return = array('error' => 'Wrong API');
        }
    }

    if($_GET['callback'])
        $gMan->API_data =  $_GET['callback']."(".json_encode($api_return).");";
    else
        $gMan->API_data = json_encode($api_return);

    $JUMP_TO_EVENT = 'gapa';
}

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'take_decision'){
    //take_decision event
    doAction('before_take_decision_event');
    $gMan->take_decision();
    if($gMan->redirect_url){
        if($gMan->status_code) http_response_code($gMan->status_code);
        $replaceUrl = array();
        if($gMan->redirect_next) $replaceUrl['next'] = urlencode($gMan->redirect_next);
        $toUrl = build_url($replaceUrl, array(), $gMan->redirect_url);
        header('location: '.$toUrl);
        exit();
        }
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'take_decision'){
    doAction('after_take_decision_event');
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'gapa'){
    doAction('before_gapa_event');
    if($gMan->call_type == 'api'){
        if($gMan->API_data) echo $gMan->API_data;
        $JUMP_TO_EVENT = 'terminate';
        }
    }
if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'gapa'){
    doAction('after_gapa_event');
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'load_theme'){
    //load_theme event
    doAction('before_load_theme_event');
    if($SYSTEM_DEBUG) $system_load->_stop();
    if($gMan->redirect_url !== NULL){
        header('location:'.$gMan->redirect_url);
        exit();
        }
    if($SYSTEM_DEBUG)$theme_part = new Timer('Theme');
    if($gMan->is_404){
        include(theme_path('absolute').'/404.php');
        }
    elseif($gMan->load_file !== NULL){
        if($gMan->call_type == 'admin'){
            if($gMan->admin_dynamic == true){
                $jacker->get_jack_content($gMan->current_jack,$gMan->current_jack_function);
                if(!$_config['header_rendered'])
                    include(_path('admin','absolute').'/null_doc.php');
                include(_path('admin','absolute').'/footer.php');
                }
            else if(strlen($gMan->load_file)) include($gMan->load_file);
            }
        else if(strlen($gMan->load_file)) include($gMan->load_file);

        if($gMan->publicRouter['404']){
            load404($gMan->publicRouter['load_page']);
            }
        elseif($gMan->publicRouter['maintenance']){
            load503($gMan->publicRouter['load_page']);
            }
        elseif($gMan->publicRouter['valid_call']){
            include($gMan->publicRouter['load_page']);
            }
        }
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'load_theme'){
    doAction('after_load_theme_event');
    }

if(!$JUMP_TO_EVENT || $JUMP_TO_EVENT == 'terminate'){
    //terminate event
    doAction('before_terminate_event');
    if($SYSTEM_DEBUG){
        $theme_part->_stop();
        register_shutdown_function('allTimers');
        register_shutdown_function('db_dump');
        }
    exit();
    }
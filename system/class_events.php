<?php
class system_events{
    public $events = array(
        'boot_loader' => array(
            'pre_calls' => array(),
            'post_calls' => array(),
            'status' => 'pending'
            ),
        'boot' => array(
            'pre_calls' => array(),
            'post_calls' => array(),
            'status' => 'pending'//pending, running, done
            ),
        'authentication' => array(
            'pre_calls' => array(),
            'post_calls' => array(),
            'status' => 'pending'//pending, running, done
            ),
        'execute_plugins' => array(
            'pre_calls' => array(),
            'post_calls' => array(),
            'status' => 'pending'//pending, running, done
            ),
        'take_decision' => array(
            'pre_calls' => array(),
            'post_calls' => array(),
            'status' => 'pending'//pending, running, done
            ),
        'gapa' => array(
            'pre_calls' => array(),
            'post_calls' => array(),
            'status' => 'pending'//pending, running, done
            ),
        'load_theme' => array(
            'pre_calls' => array(),
            'post_calls' => array(),
            'status' => 'pending'//pending, running, done
            ),
        'terminate' => array(
            'pre_calls' => array(),
            'post_calls' => array(),
            'status' => 'pending'//pending, running, done
            ),
        );
    function __construct(){}
    function _start_events(){}
    function execute_bound_functions($event,$moment){
        if($this->events[$event][$moment.'_calls']){
            $calls = $this->events[$event][$moment.'_calls'];
            foreach($calls as $i=>$v){
                if($v['object']){
                    $theObj = $v['object'];
                    $theFunc = $v['function'];
                    $theParam = $v['params'];
                    if($theParam) $theObj->$theFunc($theParam);
                    else $theObj->$theFunc();
                    }
                else{
                    if($v['params']) $v['function']($v['params']);
                    else $v['function']();
                    }
                }
            }
        }
    function exec_event_boot_loader(){}
    function exec_event_boot(){}
    function exec_event_authentication(){
        global $_config, $gMan;
        $ret = array();
        //if call is admin, then check if admin open
        //pre($_config);
        if($gMan->call_type == 'admin' && $_config['lock_admin'] == 'true'){
            //admin is locked, check if has sAdmin logged in
            if(!HAS_USER('sadmin')){
                $ret['error'][] = 'Admin is locked, please contact the Super Admin.';
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
                    $gMan->load_file = theme_path('absolute').'/1029384756.php';
                    $gMan->decision_taken = true;
                    $ret['error'][] = 'Only admins can log into admin panel.';
                    }
                }
            }
        elseif($gMan->call_type == 'api'){

            global $apis, $jacker;

            $api_return = null;

            $_u = $gMan->url_obj;
            $plugin = $_u[1] ? $_u[1] : NULL;
            $function = $_u[2] ? $_u[2] : NULL;

            if(!$plugin || !$function){
                echo json_encode(array('error' => 'Invalid API'));
                exit();
                }

            $dependencyError = array();
            $order = array();
            $pre_processing = array();

            if(isset($jacker->jack_dependecy[$plugin]))
                $tempDependency = _process_toposort($plugin, $jacker->jack_dependecy, $order, $pre_processing, $dependencyError);
            else $tempDependency = array();

            if($tempDependency === false){
                $api_return = null;
                }
            else{
                if($order){
                    foreach($order as $i=>$v){
                        if($jacker->jack_status[$v]['status'] == 'active' && $jacker->jack_status[$v]['executed'] == 'no'){
                            $jacker->jack_execute($v);
                            }
                        }
                    }
                else{
                    if($jacker->jack_status[$plugin]['status'] == 'active' && $jacker->jack_status[$plugin]['executed'] == 'no'){
                        $jacker->jack_execute($plugin);
                        }
                    }

                if(isset($apis->all_apis[$plugin]) && isset($apis->all_apis[$plugin][$function])){
                    $param = array(
                        'post' => $_POST,
                        'get' => $_GET,
                        );
                    $api_return = $apis->all_apis[$plugin][$function]['jack']->$function($param);
                    echo json_encode($api_return);
                    exit();
                    }
                else{
                    echo json_encode(array('error' => array('Wrong API')));
                    exit();
                    }
                }
            //TODO: investigate this. Why $gMan->API_data is used, why echoing output (above) directly and exiting

            if($_GET['callback'])
                $gMan->API_data =  $_GET['callback']."(".json_encode($api_return).");";
            else
                $gMan->API_data = json_encode($api_return);

            global $JUMP_TO_EVENT;

            $JUMP_TO_EVENT = 'gapa';
            }

        return $ret;
        }
    function exec_event_execute_plugins(){}
    function exec_event_take_decision(){}
    function exec_event_gapa(){
        global $gMan;
        if($gMan->call_type == 'api'){
            if($gMan->API_data) echo $gMan->API_data;
            exit();
            }
        }
    function exec_event_load_theme(){}
    function exec_event_terminate(){
        //exit();
        }

    }

function register__($event,$moment,$action,$params = array(),$opt = array()){
    global $SYSTEM_EVENTS;
    $next_event = count($SYSTEM_EVENTS->events[$event][$moment.'_calls']);
    if(is_array($action)){
        $SYSTEM_EVENTS->events[$event][$moment.'_calls'][$next_event]['object'] = $action['obj'];
        $SYSTEM_EVENTS->events[$event][$moment.'_calls'][$next_event]['function'] = $action['func'];
        }
    else $SYSTEM_EVENTS->events[$event][$moment.'_calls'][$next_event]['function'] = $action;

    $SYSTEM_EVENTS->events[$event][$moment.'_calls'][$next_event]['params'] = $params;

    if($opt){
        $SYSTEM_EVENTS->events[$event][$moment.'_calls'][$next_event]['opt'] = $opt;
        }
    }
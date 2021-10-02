<?php
/*
1. DOMAIN/api/<plugin_name>/<function_name>
2. DOMAIN/<page_name>
4. DOMAIN/<page_name>/<content_name>
5. DOMAIN/admin
6. DOMAIN/admin/<plugin_name>
7. DOMAIN/admin/<plugin_name>/<function_name>


Default public home page : index.php
Default static page: static.php
Default dynamic page: dynamic.php
Default single page: single.php
Default single page by content type: single_<content_type>.php
Default 404 page: 404.php
Default search page: search.php
*/
$gMan = null;
class GateMan{
    private static $instance = 0;
    //defines of current url is valid or not
    //if not valid 404 will be called.
    var $is_valid = false;
    //defines one of the three main portions of the system
    //for current request
    var $call_type = NULL;	//PUBLIC | ADMIN | API
    //the current url array
    var $url_obj = NULL;
    //the file to load if everything is alright
    var $load_file = NULL;
    //if redirect url exist, index.php should redirect to this url without
    //doing anything else
    var $redirect_url = NULL;
    //this is the content of the admin panel for the requested URL
    //this is used in the dynamic.php of admin panel
    var $status_code = null;
    var $redirect_next = null;
    var $admin_output = NULL;
    var $current_jack = NULL;
    var $current_jack_function = NULL;
    var $is_404 = false;
    var $decision_taken = false;
    var $API_data = null;
    var $publicRouter = array();
    var $admin_dynamic = false;
    var $public_routers = array();

    function __construct($url_obj){
        global $_config;
        if(self::$instance)
            throw new Exception('Can not create another instance of this class.');

        self::$instance++;

        $this->url_obj = $url_obj ? explode('/',trim($url_obj,'/')) : array();

        if($_config['noFront']){
            if($this->url_obj[0] == 'admin'){
                unset($_GET['DEV_URL_PARAM']);
                $nextUrl = implode('/',$this->url_obj).($_GET ? '?'.http_build_query($_GET) : '');
                header('Location:'.url($nextUrl));
                exit();
                }
            elseif($this->url_obj[0] == 'api'){
                $this->call_type = 'api';
                }
            elseif($this->url_obj[0] == 'post'){
                $this->call_type = 'post';
                }
            elseif($this->url_obj[0] == 'get'){
                $this->call_type = 'get';
                }
            elseif($this->url_obj[0] == 'ajax'){
                $this->call_type = 'ajax';
                }
            elseif(!HAS_USER('admin')){
                if(file_exists(theme_path('absolute').'/'.$this->url_obj[0].'.php'))
                    $this->load_file = theme_path('absolute').'/'.$this->url_obj[0].'.php';
                else
                    $this->load_file = theme_path('absolute').'/1029384756.php';
                $this->decision_taken = true;
                }
            elseif(HAS_USER('admin')){
                if($this->url_obj[0] != 'api'){
                    array_unshift($this->url_obj, "admin");
                    $this->call_type = 'admin';
                    }
                else{
                    $this->call_type = 'api';
                    }
                }
            else{
                if(HAS_USER()) LOGOUT_THE_USER();
                $this->load_file = theme_path('absolute').'/1029384756.php';
                $this->decision_taken = true;
                }
            }
        else{
            $_u = $this->url_obj;
            if($_u[0] == 'cron'){
                $this->load_file = theme_path('absolute').'/cron.php';
                $this->decision_taken = true;
                }
            elseif($_u[0] == $_config['admin_login_page']){
                $this->load_file = theme_path('absolute').'/1029384756.php';
                $this->decision_taken = true;
                }
            else{
                $this->call_type = 'public';
                if($_u[0] == 'api') $this->call_type = 'api';
                elseif($_u[0] == 'admin') $this->call_type = 'admin';
                }
            }

		}
    function register_public_routers($routerName, public_routers $router){
        $this->public_routers[$routerName] = $router;
        }
	function take_decision(){
        if($this->decision_taken) return;
		global $_config,$jacker;
		$_u = $this->url_obj;

		if($this->call_type == 'public'){
            $start_index = 0;
            if(count($_config['langs']) > 1){
                $currentLanguage = $_config['slang'];
                $defaultLanguage = $_config['dlang'];
                $currentUrlLanguage = isset($_u[0]) && isset($_config['langs'][$_u[0]]) ? $_u[0] : null;
                $query = $_GET;
                unset($query['DEV_URL_PARAM']);
                if(!strlen($currentUrlLanguage)){
                    if($currentLanguage != $defaultLanguage){
                        /*
                         * No language in URL, also the session language is not the default one
                         * So changing URL to accommodate current session language
                         * We could have leave it as it is if the session language was default language
                         * */
                        header('location: '.current_url());
                        exit();
                        }
                    }
                elseif($currentUrlLanguage != $currentLanguage){
                    if($currentUrlLanguage == $defaultLanguage){
                        /*
                         * The language in the URL is the default language
                         * So we should remove the language from the URl along with setting it as the current system language
                         * */
                        unset($_u[0]);
                        $query['change_language'] = $currentUrlLanguage;
                        $theUrl = _path('root').(count($_u) ? '/'.implode('/',$_u) : '').(count($query) ? '?'.http_build_query($query) : '');
                        header('location: '.$theUrl);
                        exit();
                        }
                    else{
                        /*
                         * The language in the URL is NOT the default language so putting it back
                         * along with requesting to change system language to URL language
                         * */
                        $query['change_language'] = $currentUrlLanguage;
                        $theUrl = _path('root').(count($_u) ? '/'.implode('/',$_u) : '').(count($query) ? '?'.http_build_query($query) : '');
                        header('location: '.$theUrl);
                        exit();
                        }
                    }
                elseif($currentUrlLanguage == $currentLanguage && $currentUrlLanguage == $defaultLanguage){
                    /*
                     * The language in the URL is the current system language and also the Default language
                     * So we should remove the language from the URL
                     * */
                    unset($_u[0]);
                    $theUrl = _path('root').(count($_u) ? '/'.implode('/',$_u) : '').(count($query) ? '?'.http_build_query($query) : '');
                    header('location: '.$theUrl);
                    exit();
                    }
                else $start_index = 1;
                }

            $_config['url_parameters']['page'] = $_u[$start_index] ? $_u[$start_index] : '';
            $_config['url_parameters']['content_type'] = $_u[$start_index+1] ? $_u[$start_index+1] : '';
            $_config['url_parameters']['content_id'] = $_u[$start_index+2] ? $_u[$start_index+2] : '';
            $_config['url_parameters']['content_slug'] = $_u[$start_index+3] ? $_u[$start_index+3] : '';

            if(DO_NOT_USE_CONTENT_TYPE_IN_URL){
                $_config['url_parameters']['content_slug'] = $_config['url_parameters']['content_id'];
                $_config['url_parameters']['content_id'] = $_config['url_parameters']['content_type'];
                $_config['url_parameters']['content_type'] = null;
                }

            $url = $_config['url_parameters'];

            if($_u[5]){
                $this->is_404 = true;
                }
            else{
                if($url['page']){
                    if($url['page'] && isset($this->public_routers[$url['page']])){
                        $publicRouter = $this->public_routers[$url['page']];
                        if($publicRouter->hasAccess()){
                            $this->load_file = theme_path('absolute').'/'.$url['page'].'/index.php';
                            }
                        else{
                            $this->status_code = 403;
                            if(!HAS_USER()){
                                $publicSignIn = getProjectSettings('public_theme,public_login_page');
                                $this->redirect_next = current_url();
                                $this->redirect_url = $publicSignIn ? url($publicSignIn) : url();
                                }
                            else{
                                $this->redirect_next = null;
                                $this->redirect_url = url();
                                }
                            }
                        }
                    else{
                        if(file_exists(theme_path('absolute').'/index.php'))
                            $this->load_file = theme_path('absolute').'/index.php';
                        else $this->is_404 = true;
                    }
                }
                else{

                    $customHomePage = getProjectSettings('public_theme,public_home_page');
                    if($customHomePage && $customHomePage != 'home'){
                        $_config['url_parameters']['page'] = $customHomePage;
                        }
                    else $_config['url_parameters']['page'] = 'home';

                    $this->load_file = theme_path('absolute').'/index.php';
                }
            }
        }
		elseif($this->call_type == 'admin'){
            if(file_exists(theme_path('absolute').'/'.$_u[1].'.php')){
                $this->load_file = theme_path('absolute').'/'.$_u[1].'.php';
                return;
                }

            if(HAS_USER('admin')){
                if($_u[1] && $jacker->jack_exist($_u[1])){
                    $this->current_jack = $_u[1];
                    if($_u[2] && $jacker->jack_function_exist($_u[1],$_u[2]))
                        $this->current_jack_function = $_u[2];
                    elseif($jacker->jack_function_exist($_u[1],'dashboard'))
                        $this->current_jack_function = 'dashboard';

                    if($this->current_jack && $this->current_jack_function){
                        $this->admin_dynamic = true;
                        $this->load_file = theme_path('absolute').'/dynamic.php';
                        }
                    else $this->load_file = theme_path('absolute').'/404.php';
                    }
                else $this->load_file = theme_path('absolute').'/index.php';
                }
            else $this->load_file = theme_path('absolute').'/1029384756.php';
            }
        }
    }

if(!isset($_GET['DEV_URL_PARAM'])) $_GET['DEV_URL_PARAM'] = '';

function _4OH4(){
    header('location:'. url('404'));
    exit();
    }

$_config['header_rendered'] = false;

function beforeRenderStart(){
    global $notify_user, $devdb, $_config, $SAFEGUARD, $jacker, $adminmenu;
    include(_path('admin','absolute').'/header.php');
    echo $notify_user->get_notification();
    $_config['header_rendered'] = true;
    }

function isPublic(){
    global $gMan;
    if($gMan->call_type == 'public') return true;
    else return false;
    }

addAction('render_start', 'beforeRenderStart');
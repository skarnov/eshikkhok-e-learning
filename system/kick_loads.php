<?php
session_start();
mb_internal_encoding("UTF-8");

function pre($content,$exit=true,$varDump=false){
    echo '<div style="padding:20px; border:1px solid #f00"><pre>';
    $varDump ? var_dump($content) : print_r($content);
    echo '</pre></div>';
    if($exit) exit();
    }

$hiddenPreContents = array();

function hiddenPre($content){
    global $hiddenPreContents;
    $hiddenPreContents[] = $content;
    }

function print_hidden_pre(){
    global $hiddenPreContents;
	if(!$hiddenPreContents)return null;
    echo '<!--';
    foreach($hiddenPreContents as $v){
        pre($v, 0);
        }
    echo '-->';
    }

register_shutdown_function('print_hidden_pre');

function isMobile(){
    if(!isset($_SESSION['isMobile'])){
        $useragent=$_SERVER['HTTP_USER_AGENT'];

        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
            $_SESSION['isMobile'] = true;
        else $_SESSION['isMobile'] = false;
        }
    return $_SESSION['isMobile'];
    }

$SYSTEM_DEBUG = false;
$_config = array();

include('paths.php');
include('functions.php');

if(file_exists(_path('root','absolute').'/project_settings.ini')){
    $project_settings = parse_ini_file(_path('root','absolute').'/project_settings.ini', true);
    if(!isset($project_settings['public_theme'])) $project_settings['public_theme'] = array();
    }
else $project_settings = null;

if(file_exists(_path('public','absolute').'/'.getProjectSettings('components,current_public_theme').'/theme_settings.ini')){
    $theme_settings = parse_ini_file(_path('public','absolute').'/'.getProjectSettings('components,current_public_theme').'/theme_settings.ini', true);
    $project_settings['public_theme'] = array_replace_recursive($project_settings['public_theme'], $theme_settings);
    unset($theme_settings);
    }

function has_project_settings($settings = null, $returnIfNotSet = true, $returnValueIfSet = true){
    global $project_settings;
    if($settings){
        if($project_settings){
            $settings = explode(',',$settings);
            $settingsLevel = count($settings);
            if($settingsLevel == 1){
                if(isset($project_settings[$settings[0]]))
                    return $returnValueIfSet ? $project_settings[$settings[0]] : true;
                else return null;
            }
            if($settingsLevel == 2){
                if(isset($project_settings[$settings[0]][$settings[1]]))
                    return $returnValueIfSet ? $project_settings[$settings[0]][$settings[1]] : true;
                else return null;
            }
            elseif($settingsLevel == 3){
                if(isset($project_settings[$settings[0]][$settings[1]][$settings[2]]))
                    return $returnValueIfSet ? $project_settings[$settings[0]][$settings[1]][$settings[2]] : true;
                else return null;
            }
            elseif($settingsLevel == 4){
                if(isset($project_settings[$settings[0]][$settings[1]][$settings[2]][$settings[3]]))
                    return $returnValueIfSet ? $project_settings[$settings[0]][$settings[1]][$settings[2]][$settings[3]] : true;
                else return null;
            }
        }
        else return $returnIfNotSet;
    }
    return $returnIfNotSet;
}
function getProjectSettings($settings = null, $returnIfNull = null){
    $settingValue = has_project_settings($settings, null, true);
    return is_null($settingValue) ? $returnIfNull : $settingValue;
}

include("vimeo/autoload.php");
use Vimeo\Vimeo;

$customHomePage = getProjectSettings('public_theme,public_home_page');

//Redirect if Home Page
if(isset($_GET['DEV_URL_PARAM'])){
    $thisUrl = trim($_GET['DEV_URL_PARAM'],'/');
    $root = _path('root');
    $reidrects = array(
        'home' => $root,
        );
    if($customHomePage) $reidrects[$customHomePage] = $root;

    $toUrl = null;

    if(isset($reidrects[$thisUrl])){
        Header("HTTP/1.1 301 Moved Permanently");
        Header("Location: ".$reidrects[$thisUrl]);
        exit();
        }
    }

//system language upgrades
$languages = getProjectSettings('language');

foreach($languages as $lang){
    $_config['langs'][$lang] = getProjectSettings($lang);
    }

$_config['dlang'] = $languages[0];
$_config['slang'] = isset($_SESSION['language']) ? $_SESSION['language'] : $_config['dlang'];

if (isset($_GET['change_language'])) {
    $preLang = $_config['slang'];
    $_SESSION['language'] = $_GET['change_language'];
    $_config['slang'] = $_GET['change_language'];

    header('location: ' . build_url(null, array('change_language'), current_url(0,1)));
    exit();
    }

include('class_security.php');

if($SYSTEM_DEBUG){
    include('classTimerMemory.php');
    $system_load = new Timer('System');
    }

//third part
function createPhpMailer(){
    if(!class_exists('PHPMailer')){
        include("phpmailer/lib/PHPMailer/PHPMailerAutoload.php");
        include("phpmailer/src/class.phpmailer.php");
        }
    return new PHPMailer();
    }

function createInStyle(){
    if(!class_exists('InStyle')){
        include(_path('common_files','absolute').'/inStyle/simple_html_dom.php');
        include(_path('common_files','absolute').'/inStyle/instyle.php');
        }

    return new InStyle();
    }

include('independentClasses.php');

//warm up
//$SYSTEM_EVENTS = new system_events();
$DB_CONFIG_EXISTS = false;
if(getProjectSettings('db')){
    $DB_CONFIG_EXISTS = true;
    include('class_db.php');
    }
else $devdb = null;

include('config.php');
include('class_session.php');

$cPermission->init();

include('class_jacks.php');
include('GateMan.php');

include('updater.php');

$WORLD_CURRENCY_LISTING = null;
function getWorldCurrency(){
    global $WORLD_CURRENCY_LISTING;
    if(!$WORLD_CURRENCY_LISTING){
        $WORLD_CURRENCY_LISTING = include(_path('common_files','absolute').'/autoloadable_scripts/world_currency.php');
        }
    return $WORLD_CURRENCY_LISTING;
    }

$WORLD_COUNTRY_LIST = null;
function getWorldCountry(){
    global $WORLD_COUNTRY_LIST;
    if(!$WORLD_COUNTRY_LIST){
        $WORLD_COUNTRY_LIST = include(_path('common_files','absolute').'/autoloadable_scripts/world_countries.php');
        }
    return $WORLD_COUNTRY_LIST;
    }
$BD_LOCATIONS_JSON = null;
$BD_LOCATIONS = null;

function getBDLocationJson(){
    global $BD_LOCATIONS_JSON;
    if(!$BD_LOCATIONS_JSON){
        $BD_LOCATIONS_JSON = file_get_contents(_path('common_files','absolute').'/autoloadable_scripts/bangladesh_locations.json');
        }
    return $BD_LOCATIONS_JSON;
    }
function getBDLocation(){
    global $BD_LOCATIONS_JSON, $BD_LOCATIONS;
    if(!$BD_LOCATIONS){
        if(!$BD_LOCATIONS_JSON) getBDLocationJson();
        $BD_LOCATIONS = json_decode($BD_LOCATIONS_JSON, true);
        }
    return $BD_LOCATIONS;
    }

if(file_exists(_path('admin','absolute').'/functions.php')) include(_path('admin','absolute').'/functions.php');
if(file_exists(_path('public','absolute').'/'.getProjectSettings('components,current_public_theme').'/functions.php')) include(_path('public','absolute').'/'.getProjectSettings('components,current_public_theme').'/functions.php');
if(file_exists(_path('public','absolute').'/'.getProjectSettings('components,current_public_theme').'/theme_option.php')) include(_path('public','absolute').'/'.getProjectSettings('components,current_public_theme').'/theme_option.php');

/* Setting up missing constants */

if(!defined('DO_NOT_USE_CONTENT_TYPE_IN_URL'))
    define('DO_NOT_USE_CONTENT_TYPE_IN_URL',false);

//warm up complete, now fire :)
include('fire_the_system.php');
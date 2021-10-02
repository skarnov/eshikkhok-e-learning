<?php
/*
 * Security Levels are as follows
 *      noSecurity [ :o ]
 *      internalOnly [ default ]
 *      loggedInUserOnly [ must have an user logged in to the system ]
 *      userOnly [ need to send user id and password tokens ]
 *      onlyPublicUser
 *      onlyAdminUser
 *      onlySuperAdminUser
 *      N/A fromAdminOnly [from backend only]
 *      N/A fromPublicOnly [from public theme only]
 *      appAlso [have to send app id and token with request]
 * */
/*
 * You can set security levels while registering an API call in following ways
 *      STRING | One single security level
 *      ARRAY | Multiple security levels
 *      ASSOCIATIVE ARRAY | For conditional multilevel security. Find detail below
 * */
/*
 * Register APIs with security levels as following ways
 *      $securityLevel = 'userOnly'
 *      api_register($this,'myApi','Demo API',$securityLevel);
 *      ------------------------------------------------------------------------------
 *
 *      $securityLevel = array('userOnly','onlyAdminUser');
 *      api_register($this,'myApi','Demo API',$securityLevel);
 *      ------------------------------------------------------------------------------
 * */
/*
 * NOTE:    If no security level is mentioned while registering API,
 *          the 'internalOnly' security check will be imposed.
 *          This system lacks AI, so use your own I to determine possible threats
 *          and impose security checks as per required.
 * */
class security{
    var $app_token = null;
    var $internal_token = null;
    var $admin_token = null;
    var $public_token = null;
    var $user_token = null;

    function __construct(){
        if(!isset($_SESSION['internal_token'])) $_SESSION['internal_token'] = openssl_token().md5(date('ymdHis'));
        $this->internal_token = $_SESSION['internal_token'];
        }

    function checkRequest($level){
        if(!$level) return false;
        else{
            $func = '_'.$level;
            return $this->$func();
            }
        }
    function _noSecurity(){
        return true;
        }
    function _internalOnly(){
        $receivedToken = null;
        if(isset($_GET['internalToken']) || isset($_POST['internalToken'])){
            if(isset($_GET['internalToken']) && $_GET['internalToken']) $receivedToken = $_GET['internalToken'];
            elseif(isset($_POST['internalToken']) && $_POST['internalToken']) $receivedToken = $_POST['internalToken'];
            if(strcmp($receivedToken,$this->internal_token) !== 0) return false;
            else return true;
            }
        else return false;
        }
    function _loggedInUserOnly(){
        if(HAS_USER()) return true;
        else return false;
        }
    function _onlyPublicUser(){
        if(HAS_USER('public')) return true;
        else return false;
        }
    function _onlyAdminUser(){
        if(HAS_USER('admin')) return true;
        else return false;
        }
    function _onlySuperAdminUser(){
        if(HAS_USER('sadmin')) return true;
        else return false;
        }
    function _userOnly(){
        global $devdb;

        $receivedToken = null;
        if(isset($_GET['userToken']) || isset($_POST['userToken'])){
            if(isset($_GET['userToken']) && $_GET['userToken']) $receivedToken = $_GET['userToken'];
            elseif(isset($_POST['userToken']) && $_POST['userToken']) $receivedToken = $devdb->escape($_POST['userToken']);

            $sql = "SELECT * FROM dev_users WHERE user_private_token = '".$receivedToken."'";
            $userFound = $devdb->get_row($sql);

            //Login the user if Found
            if($userFound) return true;
            else return false;
            }
        else return false;
        }
    function _appAlso(){
        global $devdb;

        $receivedID = null;
        $receivedToken = null;

        if((isset($_GET['appID']) && isset($_GET['appKey'])) || (isset($_POST['appID']) && isset($_POST['appKey']))){
            if(isset($_GET['appID']) && isset($_GET['appKey'])){
                $receivedID = $_GET['appID'];
                $receivedToken = $_GET['appKey'];
                }
            elseif(isset($_POST['appID']) && isset($_POST['appKey'])){
                $receivedID = $devdb->escape($_POST['appID']);
                $receivedToken = $devdb->escape($_POST['appKey']);
                }

            $sql = "SELECT * FROM dev_apps WHERE app_id = '".$receivedID."' AND app_token = '".$receivedToken."'";
            $appFound = $devdb->get_row($sql);

            //login the app if found
            if($appFound) return true;
            else return false;
            }
        else return false;
        }
    }
$SAFEGUARD = new security();

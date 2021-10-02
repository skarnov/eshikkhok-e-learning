<?php
$cached = false;

$cacheID = 'devConfig';

$c = getCache($cacheID);

if(!hasCache($c)){
    $sql = "SELECT * FROM dev_config";
    if($devdb){
        $c = array();
        $configs = $devdb->get_results($sql);

        foreach($configs as $i=>$v){
            $c[$v['config_name']] = $v['config_value'];
            }

        if(isset($c['unserialize'])){
            $c['unserialize'] = unserialize($c['unserialize']);
            if($c['unserialize']){
                foreach($c['unserialize'] as $i=>$v){
                    $c[$v] = unserialize($c[$v]);
                    }
                }
            }

        if($c) setCache($c,$cacheID);
        }
    else $c = null;
    }
if($c) $_config = array_merge($_config,$c);

$multilingualFields = array(
    'dev_pages' => array('page_title', 'page_sub_title','page_description','page_meta_description','page_meta_keyword','page_excerpt','page_extras'),
    'dev_contents' => array('content_title', 'content_sub_title', 'content_description', 'content_meta_description', 'content_meta_keyword', 'content_excerpt'),
    'dev_menu_items' => array('item_title'),
    'dev_content_types' => array('content_type_title'),
    'dev_tags' => array('tag_title', 'tag_description'),
    'e_courses' => array('item_title', 'item_subtitle', 'item_summary', 'item_description', 'item_objectives', 'item_difficulties', 'item_requirements'),
    );
$multilingualConfigFields = array(
    'site_name','website_description', 'website_keywords', 'website_copyright_text'
    );
$multilingualJackFields = array(
    'address', 'phone', 'email', 'about_us', 'copyright'
    );

$_config['time_zone'] = isset($_config['time_zone']) ? $_config['time_zone'] : 'Asia/Dhaka';
$_config['system_mode'] = isset($_config['system_mode']) ? $_config['system_mode'] : 'online';
$_config['reserved_pages'] = isset($_config['reserved_pages']) ? explode(',',$_config['reserved_pages']) : array();
$_config['_PUBLIC_FILEMANAGER_KEY_'] = isset($_config['_PUBLIC_FILEMANAGER_KEY_']) ? $_config['_PUBLIC_FILEMANAGER_KEY_'] : 'b9b7d0f30c2c300dbde1d961c5b00284';
$_config['noFront'] = isset($_config['noFront']) ? $_config['noFront'] : false;
if($_config['noFront'] == 'true') $_config['noFront'] = true;
else $_config['noFront'] = false;
$_config['admin_login_page'] = isset($_config['admin_login_page']) ? $_config['admin_login_page'] : '1029384756';

//actions based on configuration
date_default_timezone_set($_config['time_zone']);
if(true || $_config['system_mode'] == 'online'){
    //error_reporting(E_ALL & ~E_WARNING);
    error_reporting(E_ALL & ~E_NOTICE);
    }

function udate($format, $utimestamp = null){
    $m = explode(' ',microtime());
    list($totalSeconds, $extraMilliseconds) = array($m[1], (int)round($m[0]*1000,3));
    return date("YmdHis", $totalSeconds) . sprintf('%03d',$extraMilliseconds) ;
    }
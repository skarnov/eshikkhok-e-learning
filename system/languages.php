<?php
//language processor
$languages = array();
$lang_folder = glob(_path('system','absolute').'/languages/*');
foreach($lang_folder as $i=>$v){
    $languages = array_merge_recursive($languages, include($v));
    }
function numberHl($data){
    global $_config;
    $language = $_config['system_language'];
    $numbers = array(
        'bangla' => array('০','১','২','৩','৪','৫','৬','৭','৮','৯','.','হাজার','মিলিয়ন','বিলিয়ন','জানুয়ারী','ফেব্রুয়ারী','মার্চ','এপ্রিল','মে','জুন','জুলাই','অগাস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'),
        'english' => array('0','1','2','3','4','5','6','7','8','9','.','K','M','B','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec')
        );
    return str_replace($numbers['english'],$numbers[$language],$data);
    }
function putHl($key){
    $args = func_get_args();
    echo call_user_func_array('getHl',$args);
    }
function getHl($key){
    global $_config, $languages;
    $language = $_config['system_language'];
    $args = func_get_args();

    if(!isset($languages[$language][$key])) return $key;

    if(gettype($languages[$language][$key]) == 'object' && is_callable($languages[$language][$key]))
        return numberHl(call_user_func_array($languages[$language][$key],$args));
    else
        $args[0] = trim($languages[$language][$key]);

    return numberHl(call_user_func_array('sprintf',$args));
    }
function getMHl(){
    global $_config, $languages;
    $args = func_get_args();
    $reverse = false;

    if($args){
        if($args[0] === true){
            $reverse = true;
            unset($args[0]);
            }

        foreach($args as $i=>$key){
            $args[$i] = getHl($key);
            }
        }
    $args = $reverse && $_config['system_language'] != 'english' ? array_reverse($args) : $args;

    return implode(' ',$args);
    }

//echo getHl('table_search_result','ABCD',1,5,100);exit();
function getLanguages(){
    global $languages;
    $ret = array();
    foreach($languages as $i=>$v){
        $ret[$i] = strtoupper($i);
        }
    return $ret;
    }
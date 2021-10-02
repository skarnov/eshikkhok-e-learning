<?php
/*
 * All System Paths
 * */
$paths = array();
$_pathAdditional_ = null;
function getPathAdditional(){
    global $_pathAdditional_;
    if($_pathAdditional_) return $_pathAdditional_;

    $exploded_path = explode('/',$_SERVER['PHP_SELF']);

    $additional = '';
    if(count($exploded_path) > 2){
        for($i=1;$i<(count($exploded_path) - 1);$i++){
            $additional .= $exploded_path[$i] . '/';
            }
        $additional = rtrim($additional,'/');
        }
    $_pathAdditional_ = $additional;
    return $additional;
    }

$paths['absolute']['root'] = rtrim($_SERVER['DOCUMENT_ROOT'],'/').(getPathAdditional() ? '/'.getPathAdditional() : '');
$paths['absolute']['site'] = $paths['absolute']['root'].'/site';
$paths['absolute']['system'] = $paths['absolute']['root'].'/system';
$paths['absolute']['contents'] = $paths['absolute']['site'].'/contents';
$paths['absolute']['uploads'] = $paths['absolute']['contents'].'/uploads';
$paths['absolute']['cropped'] = $paths['absolute']['contents'].'/cropped';
$paths['absolute']['profile_pictures'] = $paths['absolute']['contents'].'/profile_pictures';
$paths['absolute']['themes'] = $paths['absolute']['site'].'/themes';
$paths['absolute']['common_files'] = $paths['absolute']['themes'].'/common_files';
$paths['absolute']['public'] = $paths['absolute']['themes'].'/public';
$paths['absolute']['admin'] = $paths['absolute']['themes'].'/admin';
$paths['absolute']['jacks'] = $paths['absolute']['site'].'/jacks';
$paths['absolute']['system_jacks'] = $paths['absolute']['jacks'].'/system_jacks';
$paths['absolute']['optional_jacks'] = $paths['absolute']['jacks'].'/optional';
$paths['absolute']['system_setup'] = $paths['absolute']['system_jacks'].'/setup';

//now the relatives
//$paths['protocol'] = explode('/',$_SERVER['SERVER_PROTOCOL']);
//$paths['protocol'] = strtolower($paths['protocol'][0]);
//$paths['protocol'] = strtolower($_SERVER['REQUEST_SCHEME']);
$paths['protocol'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";

$paths['relative']['root'] = $paths['protocol'].'://'.$_SERVER['HTTP_HOST'].(getPathAdditional() ? '/'.getPathAdditional() : '');
$paths['relative']['site'] = $paths['relative']['root'].'/site';
$paths['relative']['system'] = $paths['relative']['root'].'/system';
$paths['relative']['contents'] = $paths['relative']['site'].'/contents';
$paths['relative']['uploads'] = $paths['relative']['contents'].'/uploads';
$paths['relative']['cropped'] = $paths['relative']['contents'].'/cropped';
$paths['relative']['profile_pictures'] = $paths['relative']['contents'].'/profile_pictures';
$paths['relative']['themes'] = $paths['relative']['site'].'/themes';
$paths['relative']['common_files'] = $paths['relative']['themes'].'/common_files';
$paths['relative']['public'] = $paths['relative']['themes'].'/public';
$paths['relative']['admin'] = $paths['relative']['themes'].'/admin';
$paths['relative']['jacks'] = $paths['relative']['site'].'/jacks';
$paths['relative']['system_jacks'] = $paths['relative']['jacks'].'/system_jacks';
$paths['relative']['optional_jacks'] = $paths['relative']['jacks'].'/optional';
$paths['relative']['system_setup'] = $paths['relative']['system_jacks'].'/setup';

function _path($path_name,$path_type='relative'){
    global $paths;
    $pathParts = explode('/',$path_name);
    $path_name = $pathParts[0];
    if($paths[$path_type][$path_name]){
        unset($pathParts[0]);
        if($pathParts){
            if(file_exists($paths['absolute'][$path_name].'/'.implode('/',$pathParts)))
                return $paths[$path_type][$path_name].'/'.implode('/',$pathParts);
            else return null;
            }
        else return $paths[$path_type][$path_name];
        }
    else return null;
}

function urlFromRequestURI(){
     return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }
function url($part='', $urlPlatformType = null, $debug = false){
    global $_config, $gMan;

    $root = _path('root');

    $partDirQuery = explode('?',$part);
    $partDirQuery = array(
        'dirs' => isset($partDirQuery[0]) ? trim($partDirQuery[0],'/') : '',
        'queries' => isset($partDirQuery[1]) ? $partDirQuery[1] : ''
        );

    $partDirs = explode('/', $partDirQuery['dirs']);
    $partQueries = $partDirQuery['queries'];

    $call_type = $urlPlatformType ? $urlPlatformType : ($gMan->call_type ? $gMan->call_type : 'admin');

    if(isset($partDirs[0]) && ($partDirs[0] == 'api' || in_array($partDirs[0], array_keys($_config['langs'])) !== false)){
        //no change
        }
    elseif(isset($partDirs[0]) && $partDirs[0] == 'admin'){
        if($_config['noFront']){
            unset($partDirs[0]);
            }
        }
    else{
        if ($call_type == 'admin') {
            if(HAS_USER('admin')){
                if (!isset($partDirs[0]) || $partDirs[0] != 'admin')
                    array_unshift($partDirs, 'admin');
                }
            }
        elseif ($call_type == 'public') {
            if (count($_config['langs']) > 1 && $_config['slang'] != $_config['dlang']) {
                if (!isset($partDirs[0]) || $partDirs[0] != $_config['slang'])
                    array_unshift($partDirs, $_config['slang']);
                }
            }
        }
    //if ($debug) pre($_config);
    if($_GET['theme_preview']){
        $to = build_url(array('theme_preview' => $_GET['theme_preview']),array(),_path('root').$call_type.($part ? '/'.$part : ''));
        }
    else $to = _path('root').($partDirs ? '/'.implode('/',$partDirs) : '').($partQueries ? '?'.$partQueries : '');
    return $to;
    }

function current_url($withoutQueryString = false, $skipUrlFunction = false){
    $getVars = $_GET;
    $pagePath = '';
    if(isset($getVars['DEV_URL_PARAM'])){
        $pagePath = $getVars['DEV_URL_PARAM'] ? trim($getVars['DEV_URL_PARAM'],'/') : '';
        unset($getVars['DEV_URL_PARAM']);
        }
    $query = http_build_query($getVars);
    /*$part = preg_replace('/DEV_URL_PARAM=/','',$_SERVER['QUERY_STRING'], 1);
    $part = preg_replace('/&/','?',$part, 1);
    $currenturl = url($part);*/

    if($withoutQueryString) return $skipUrlFunction ? _path('root').'/'.$pagePath :  url($pagePath);
    else{
        $withQuery = $pagePath.(strlen($query) ? '?'.$query : '');
        return $skipUrlFunction ? _path('root').'/'.$withQuery : url($withQuery);
        }
    }
function addReplaceLanguageToUrl($theLanguage = '', $forceAddDefault = true){
    $url = current_url();
    $parts = explode('?',$url);
    }

function theme_path($path_type = 'relative'){
    if($path_type != 'relative' && $path_type != 'absolute') return NULL;

    global $gMan, $_config;

    if($gMan->call_type == 'public') return _path('public',$path_type).'/'.getProjectSettings('components,current_public_theme');
    else return _path('admin',$path_type);
}
function current_public_theme_path($path_type = 'relative'){
    if($path_type != 'relative' && $path_type != 'absolute') return NULL;

    global $gMan, $_config;

    return _path('public',$path_type).'/'.getProjectSettings('components,current_public_theme');
    }

function detail_url($content,$withSlug = true){
    global $_config;
    $pageManager = jack_obj('dev_page_management');
    $thePage = null;
    if($content['fk_page_id'])
        $thePage = $pageManager->get_a_page($content['fk_page_id'], array('tiny' => true));
    /*else if(strlen($content['fk_content_type_id']))
        $thePage = array('page_slug' => $content['fk_content_type_id']);
    else
        $thePage = array('page_slug' => $_config['url_parameters']['page']);*/
    else return null;

    if(DO_NOT_USE_CONTENT_TYPE_IN_URL)
        $part = (isset($_config['slang']) && $_config['slang'] != 'en' ? $_config['slang'].'/' : '').($thePage ? $thePage['page_slug'].'/' : '').$content['pk_content_id'].($withSlug ? '/'.$content['content_slug'] : '');
    else
        $part = (isset($_config['slang']) && $_config['slang'] != 'en' ? $_config['slang'].'/' : '').($thePage ? $thePage['page_slug'].'/' : '').$content['fk_content_type_id'].'/'.$content['pk_content_id'].($withSlug ? '/'.$content['content_slug'] : '');

    return url($part);
    }

function page_url($page){
    global $_config;
    $part = (isset($_config['slang']) && $_config['slang'] != 'en' ? $_config['slang'].'/' : '').$page['page_slug'];
    return url($part, 'public');
}

function image_url($img){
    return _path('uploads').'/'.$img;
}

function user_blog_roll($user){
    global $_config;
    return url('/'.$_config['blogger_niche'].'/'.$user['user_name']);
    }

function common_files($path_type = 'relative'){
    if($path_type != 'relative' && $path_type != 'absolute') return null;

    return _path('common_files',$path_type);
}
function convertPaths($path){
    $relRoot = _path('root');
    $absRoot = _path('root','absolute');
    if(strpos($path,$relRoot) === false)
        return str_replace($absRoot,$relRoot,$path);
    else
        return str_replace($relRoot,$absRoot,$path);
    }
//default files
function user_picture($link = null,$path_type = 'relative'){
    //pre(file_exists(_path('uploads','absolute').'/'.$link),0);
    if(strlen($link)){
        if(file_exists(_path('profile_pictures','absolute').'/'.$link))
            return _path('profile_pictures',$path_type).'/'.$link;
        elseif(file_exists(_path('uploads','absolute').'/'.$link)){
            return _path('uploads',$path_type).'/'.$link;
            }
        else{
            return _default_user();
        }
        }
    else{
        return _default_user();
        }
    }

//default files
function _default_user(){
    return image_url('default_user.png');
}

function _default_company(){
    return image_url('company-stock-analysis.jpg');
    }

function _default_content(){
    global $_config;
    return _path('public').'/'.getProjectSettings('components,current_public_theme').'/images/blog_post_default.jpg';
}
/**********************************************************************/
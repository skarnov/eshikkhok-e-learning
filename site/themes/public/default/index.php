<?php
$pageManager = jack_obj('dev_page_management');
$contentManager = jack_obj('dev_content_management');
$url = $_config['url_parameters'];
$isMaintenance = false;
$_404 = false;
$the_page = null;
$the_content = null;
$seoManager = jack_obj('dev_seo_management');

if($seoManager){
    if(isset($seoManager::$feeds[$gMan->url_obj[0]]) && $_config['noFront'] == false){
        header('Content-type: application/xml');
        $seoManager->create_site_feed($gMan->url_obj[0]);
        exit();
        }
    }


if($_config['system_mode'] == 'maintenance' && $url['page'] != 'maintenance'){
    if(file_exists(theme_path('absolute').'/maintenance.php')){
        $isMaintenance = true;
        }
    else $_404 = true;
    }
elseif($url['page']){
    $the_page = $pageManager->get_a_page_by_slug($url['page']);

    if($the_page){
        $_config['current_page'] = $the_page;
        global $multilingualFields;
        foreach($multilingualFields['dev_pages'] as $i=>$v){
            if(isset($_config['current_page'][$v])) $_config['current_page'][$v] = processToRender($_config['current_page'][$v]);
            }
        $_config['current_page']['page_description'] = fix_description_image($_config['current_page']['page_description']);
        $_config['current_page']['page_description'] = preg_replace('/Featured-Image-Will-Be-Displayed-Here/i','<img class="tinymce_featured_image" src="'.get_image($_config['current_page']['page_thumbnail'],'1170x0x2').'"/>',$_config['current_page']['page_description']);
        $_config['current_page']['page_extras'] = unserialize($_config['current_page']['page_extras']);

        $the_page = $_config['current_page'];

        if($_config['current_page']['page_status'] == 'active'){
            if($_config['current_page']['page_landing_template']){
                $template_file = $_config['current_page']['page_landing_template'];

                if(file_exists(theme_path('absolute').'/'.$template_file.'.php'))
                    $load_page = theme_path('absolute').'/'.$template_file.'.php';
                else
                    $_404 = true;

            }
            else{
                if(file_exists(theme_path('absolute').'/'.$url['page'].'.php'))
                    $load_page = theme_path('absolute').'/'.$url['page'].'.php';
                else $_404 = true;
            }
        }
        else $_404 = true;

        if(!$_404){
            if($url['content_id']){
                $args = array(
                    'published_till_now' => true,
                    //'status' => 'published',
                    //'content_types' => $url['content_type'],
                    'content_id' => $url['content_id'],
                    'ignore_parent' => true,
                    'single' => true,
                    'include_child' => true,
                    'include_meta' => true,
                    'include_tag' => true,
                    'include_category' => true,
                    'include_file' => true,
                    );
                $cManager = jack_obj('dev_content_management');
                $_config['current_content'] = $cManager->get_contents($args);

                if(
                    !$_config['current_content']
                    || $_config['current_content']['fk_page_id'] != $the_page['pk_page_id']
                    || $_config['current_content']['content_status'] != 'published'
                    || $_config['current_content']['fk_content_type_id'] != $url['content_type']
                    || (strlen($url['content_slug']) && $_config['current_content']['content_slug'] != $url['content_slug'])
                    )
                    $_404 = true;
                else{
                    $_config['current_content']['content_description'] = fix_description_image($_config['current_content']['content_description']);//preg_replace('/(\.\.\/\.\.\/)/i',_path('root').'/',$_config['current_content']['content_description']);
                    $_config['current_content']['content_description'] = preg_replace('/Featured-Image-Will-Be-Displayed-Here/i','<img class="tinymce_featured_image" src="'.get_image($_config['current_content']['content_thumbnail'],'1170x0x2').'"/>',$_config['current_content']['content_description']);

                    //increase view count of the content
                    $sql = "UPDATE dev_contents SET content_view_count = content_view_count + 1 WHERE pk_content_id = '".$url['content_id']."'";
                    $updatted = $devdb->query($sql);

                    $_config['current_content']['content_view_count'] += 1;

                    $the_content = $_config['current_content'];
                    }
                }
            else{
                $sql = "UPDATE dev_pages SET page_view_count = page_view_count + 1 WHERE pk_page_id = '".$_config['current_page']['pk_page_id']."'";
                $updatted = $devdb->query($sql);

                $_config['current_page']['page_view_count'] += 1;
                $the_page = $_config['current_page'];
                }
            }
        }
    else if(file_exists(theme_path('absolute').'/'.$url['page'].'.php')){
        $_config['current_page'] = array();
        $load_page = theme_path('absolute').'/'.$url['page'].'.php';

        /*if(isset($url['content_id']) && $url['content_id']){
            $args = array(
                'published_till_now' => true,
                'status' => 'published',
                'content_types' => $url['content_type'],
                'content_id' => $url['content_id'],
                'ignore_parent' => true,
                'single' => true,
                'include_child' => true,
                'include_meta' => true,
                'include_tag' => true,
                'include_file' => true,
                );
            $cManager = jack_obj('dev_content_management');
            $_config['current_content'] = $cManager->get_contents($args);

            if(!$_config['current_content'] || $_config['current_content']['content_status'] != 'published')
                $_404 = true;
            else{
                $_config['current_content']['content_description'] = fix_description_image($_config['current_content']['content_description']);//preg_replace('/(\.\.\/\.\.\/)/i',_path('root').'/',$_config['current_content']['content_description']);
                $_config['current_content']['content_description'] = preg_replace('/Featured-Image-Will-Be-Displayed-Here/i','<img class="tinymce_featured_image" src="'.get_image($_config['current_content']['content_thumbnail'],'1170x0x2').'"/>',$_config['current_content']['content_description']);

                //increase view count of the content
                $sql = "UPDATE dev_contents SET content_view_count = content_view_count + 1 WHERE pk_content_id = '".$url['content_id']."'";
                $updatted = $devdb->query($sql);

                $_config['current_content']['content_view_count'] += 1;

                $the_content = $_config['current_content'];
                }
            }*/
        }
    /*else if(isset($contentManager->cTypes[$url['page']]) && !isset($contentManager->TypeExceptional[$url['page']])){
        if(!$url['content_type']){
            //http_response_code(301);
            header('Location: '.url($url['page'].'/'.$url['page']), true, 301);
            exit();
            }
        $_config['current_page'] = NULL;
        if(file_exists(theme_path('absolute').'/content.php')){
            $load_page = theme_path('absolute').'/content.php';
            if($url['content_id']){
                $args = array(
                    'published_till_now' => true,
                    'status' => 'published',
                    'content_types' => $url['content_type'],
                    'content_id' => $url['content_id'],
                    'ignore_parent' => true,
                    'single' => true,
                    'include_child' => true,
                    'include_meta' => true,
                    'include_tag' => true,
                    'include_file' => true,
                    );
                $cManager = jack_obj('dev_content_management');
                $_config['current_content'] = $cManager->get_contents($args);

                if(!$_config['current_content'] || $_config['current_content']['content_status'] != 'published')
                    $_404 = true;
                else{
                    $_config['current_content']['content_description'] = fix_description_image($_config['current_content']['content_description']);//preg_replace('/(\.\.\/\.\.\/)/i',_path('root').'/',$_config['current_content']['content_description']);
                    $_config['current_content']['content_description'] = preg_replace('/Featured-Image-Will-Be-Displayed-Here/i','<img class="tinymce_featured_image" src="'.get_image($_config['current_content']['content_thumbnail'],'1170x0x2').'"/>',$_config['current_content']['content_description']);

                    //increase view count of the content
                    $sql = "UPDATE dev_contents SET content_view_count = content_view_count + 1 WHERE pk_content_id = '".$url['content_id']."'";
                    $updatted = $devdb->query($sql);

                    $_config['current_content']['content_view_count'] += 1;

                    $the_content = $_config['current_content'];
                    }
                }
            }
        else $_404 = true;
        }*/
    else{
        $_config['current_page'] = NULL;
        $_404 = true;
        }
    }

function load404($page404 = null){
    global $JACK_SETTINGS, $notify_user, $_config;
    http_response_code(404);
    if($page404) include($page404);
    else include(theme_path('absolute').'/404.php');
    exit();
    }
function load503($page503 = null){
    global $JACK_SETTINGS, $notify_user;
    http_response_code(503);
    if($page503) include($page503);
    else include(theme_path('absolute').'/maintenance.php');
    exit();
    }

if($_404){
    $gMan->publicRouter['404'] = true;
    $gMan->publicRouter['load_page'] = theme_path('absolute').'/404.php';
    //load404();
    }
elseif($isMaintenance){
    $gMan->publicRouter['maintenance'] = true;
    $gMan->publicRouter['load_page'] = theme_path('absolute').'/maintenance.php';
    //http_response_code(503);
    //include(theme_path('absolute').'/maintenance.php');
    }
else{
    $gMan->publicRouter['valid_call'] = true;
    $gMan->publicRouter['load_page'] = $load_page;
    //include($load_page);
    }
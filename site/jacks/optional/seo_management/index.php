<?php
class dev_seo_management{
    var $thsClass = 'dev_seo_management';
    static $siteMaps = array();
    static $feeds = array();
	function __construct(){
		jack_register($this);
		}
    function init(){
	    if(!isPublic()){
            if(!file_exists(_path('root','absolute').'/sitemap.xsl'))
                copy(_path('system_jacks','absolute').'/seo_management/resources/sitemap.xsl',_path('root','absolute').'/sitemap.xsl');
            }

        $permissions = array(
            'group_name' => 'Administration',
            'permissions' => array(
                'manage_seo' => 'Manage SEO Options'
                ),
            );
        register_permission($permissions);

        apiRegister($this,'create_site_feed');
        apiRegister($this,'submit_sitemap');

        if(!isPublic()){
            register_shutdown_function('create_sitemap_index');
            $this->adm_menus();
            }

        addAction('addHeadMeta',array($this, 'addFeedMeta'));
        }

    function adm_menus(){
        $params = array(
            'label' => 'Sitemaps',
            'description' => 'Manage Sitemaps',
            'menu_group' => 'Administration',
            'action' => 'manage_sitemaps',
            'iconClass' => 'fa-sitemap',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_seo')) admenu_register($params);
        }

    function manage_sitemaps(){
        if(!has_permission('manage_seo')) return null;

        global $devdb, $_config;

        $myUrl = jack_url($this->thsClass, 'manage_sitemaps');

        include('pages/list_sitemaps.php');
        }

    function addFeedMeta(){
        global $_config;
        echo '<link rel="alternate" type="application/rss+xml" title="'.processToRender($_config['site_name']).' RSS" href="'.url('feed').'"/>'."\n";
        }

    function create_site_feed($feedName){
        set_time_limit(5000);
        global $_config;

        echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
'. "\r\n";
        //$string .= "\t".'xmlns:content="http://purl.org/rss/1.0/modules/content/"'. "\r\n";
        //$string .= "\t".'xmlns:dc="http://purl.org/dc/elements/1.1/"'. "\r\n";
        //$string .= "\t".'xmlns:atom="http://www.w3.org/2005/Atom"'. "\r\n";
        //$string .= "\t".'xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">'. "\r\n";
        echo "\t".'<channel>'. "\r\n";
        echo "\t\t".'<title>'.form_modifiers::siteMapEscape(processToRender($_config['site_name'])).'</title>'. "\r\n";
        echo "\t\t".'<atom:link href="'.url('feed').'" type="application/rss+xml" rel="self" />'. "\r\n";
        echo "\t\t".'<link>'.url().'</link>'. "\r\n";
        echo "\t\t".'<description>'.form_modifiers::siteMapEscape(processToRender($_config['website_description'])).'</description>'. "\r\n";
        echo "\t\t".'<image>'. "\r\n";
        echo "\t\t\t".'<url>'.get_image('default_share.png','1200x600').'</url>'. "\r\n";
        echo "\t\t\t".'<title>'.form_modifiers::siteMapEscape(processToRender($_config['site_name'])).'</title>'. "\r\n";
        echo "\t\t\t".'<link>'.url().'</link>'. "\r\n";
        echo "\t\t".'</image>'. "\r\n";
        echo "\t\t".'<copyright>'.form_modifiers::siteMapEscape('Copyright 2015 3DEVs IT Ltd.').'</copyright>'. "\r\n";
        echo "\t\t".'<generator>'.form_modifiers::siteMapEscape('3DEVs CMS').'</generator>'. "\r\n";
        echo "\t\t".'<language>'.$_config['slang'].'</language>'. "\r\n";

        if(isset(self::$feeds[$feedName])){
            $func = self::$feeds[$feedName]['func'];
            if(self::$feeds[$feedName]['obj'])
                self::$feeds[$feedName]['obj']->$func();
            else $func();
            }

        echo "\t".'</channel></rss>';
        }

    function submit_sitemap(){
        set_time_limit(180);
        $url = $_POST['url'];
        $engines = array(
            'google' => "http://www.google.com/webmasters/sitemaps/ping?sitemap=".$url,
            'bing' => "http://www.bing.com/webmaster/ping.aspx?siteMap=".$url,
            //'ask' => "http://submissions.ask.com/ping?sitemap=".$url
            );
        $ret_codes = array();
        foreach($engines as $i=>$v){
            $ch = curl_init($v);
            curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
            curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
            ob_start();
            curl_exec($ch);
            ob_get_clean();
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $ret_codes[$i] = $httpCode;
            }

        foreach($ret_codes as $i=>$v){
            user_activity::add_activity('Sitemap '.$_POST['sitemap'].' was submitted to '.$i.'. The submission was '.($v == '200' ? 'successful' : 'unsuccessful'),($v == '200' ? 'success' : 'error'),'create');
            }

        return $ret_codes;
        }
	}
new dev_seo_management;

function getSiteMapHeader(){
    $header = '';
    $header .= '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    return $header;
    }

function getSiteMapFooter(){
    $footer = '';
    $footer .= '</urlset>';
    return $footer;
    }

function getSiteMapLoc($data){
    $loc = '';
    $loc .= '<url>';
    $loc .= '<loc>'.form_modifiers::siteMapEscape($data['url']).'</loc>';
    $loc .= '<lastmod>'.$data['time'].'</lastmod>';
    if(isset($data['change'])) $loc .= '<changefreq>daily</changefreq>';
    if(isset($data['priority'])) $loc .= '<priority>0.7</priority>';
    /*$string .= '<image:image>';
    $string .= '<image:loc>'.($v['content_thumbnail'] ? image_url($v['content_thumbnail']) : image_url($_config['default_share_image'])).'</image:loc>';
    $string .= '<image:caption>'.form_modifiers::siteMapEscape($v['content_title']).'</image:caption>';
    $string .= '</image:image>';*/
    $loc .= '</url>';

    return $loc;
    }

function create_sitemap_index(){
    $registeredSitemaps = _path('root','absolute').'/registeredSitemaps.dev';
    $sitemapIndex = _path('root','absolute').'/sitemap.xml';
    $sitemapExists = file_exists($sitemapIndex);
    $siteMaps = array();
    $new_found = false;
    //TODO: Add sitemaps of all other languages also along with default language
    if(!file_exists($registeredSitemaps)){
        $new_found = true;
        $siteMaps = dev_seo_management::$siteMaps;
        file_put_contents($registeredSitemaps,json_encode(dev_seo_management::$siteMaps));
        }
    else{
        $old = file_get_contents($registeredSitemaps);
        $old = json_decode($old,true);
        if(dev_seo_management::$siteMaps){
            foreach(dev_seo_management::$siteMaps as $i=>$v){
                if(!isset($old[$i])){
                    $old[$i] = $v;
                    $new_found = true;
                    }
                }
            }
        $siteMaps = $old;
        file_put_contents($registeredSitemaps,json_encode($old));
        }

    if($new_found || !$sitemapExists){
        $siteMap_content = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        if($siteMaps){
            foreach($siteMaps as $i=>$v){
                $siteMap_content .= '<sitemap><loc>'.url($v['file'],'public').'</loc></sitemap>';
                }
            }
        $siteMap_content .= '</sitemapindex>';
        file_put_contents($sitemapIndex,$siteMap_content);
        }
    }
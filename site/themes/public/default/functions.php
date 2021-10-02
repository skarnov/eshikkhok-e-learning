<?php
function printSocialLinks(){
    global $_config;
    if($_config['facebook_link']){
        ?>
        <a target="_blank" href="<?php echo $_config['facebook_link']; ?>" class="btn bottom-share-link"><i class="fa fa-facebook"></i></a>
        <?php
        }
    if($_config['twitter_link']){
        ?>
        <a target="_blank" href="<?php echo $_config['twitter_link']; ?>" class="btn bottom-share-link"><i class="fa fa-twitter"></i></a>
        <?php
        }
    if($_config['linkedin_link']){
        ?>
        <a target="_blank" href="<?php echo $_config['linkedin_link']; ?>" class="btn bottom-share-link"><i class="fa fa-linkedin"></i></a>
        <?php
        }
    if($_config['googleplus_link']){
        ?>
        <a target="_blank" href="<?php echo $_config['googleplus_link']; ?>" class="btn bottom-share-link"><i class="fa fa-google-plus"></i></a>
        <?php
        }
    if($_config['youtube_link']){
        ?>
        <a target="_blank" href="<?php echo $_config['youtube_link']; ?>" class="btn bottom-share-link"><i class="fa fa-youtube"></i></a>
        <?php
        }
    if($_config['pinterest_link']){
        ?>
        <a target="_blank" href="<?php echo $_config['pinterest_link']; ?>" class="btn bottom-share-link"><i class="fa fa-pinterest"></i></a>
        <?php
        }
    }
function each_footer_useful_links_render_function($parent, &$childs, $args = array()){
    global $_config;
    $pageManager = jack_obj('dev_page_management');

    $menu = '';
    $childs_found = false;
    foreach($childs as $i=>$v){
        if($v['fk_item_id'] != $parent) continue;
        $item_title = '';
        $page = null;
        if($v['fk_page_id']){
            $page = $pageManager->get_a_page($v['fk_page_id'],array('tiny' => true));
            $item_title = $page['page_title'];
            if(!$v['use_page_title']) $item_title = $v['item_title'];
            $link = page_url($page);
            }
        else{
            $item_title = $v['item_title'];
            if(strlen($v['item_ext_url'])) $link = $v['item_ext_url'];
            else $link = ':javascript:';
            }

        $item_title = processToRender($item_title);

        $childs_found = true;
        //$args['li_class'] = 'level2 nav-6-1-1 first';
        $c = each_footer_useful_links_render_function($v['pk_item_id'],$childs, $args);

        $menu .= '<li class="'.($c ? 'hasSubMenu' : '').'"><a href="'.$link.'"><i class="fa fa-send"></i>'.$item_title.'</a>' . ($c ? '<ul>' . $c . '</ul>' : '') . '</li>';
        }
    $menu .= '';
    if(!$childs_found) $menu = NULL;
    return $menu;
    }
function footer_useful_links_render_function($args){
    $menu_id = $args['menu_id'];
    global $devdb, $_config;
    $pageManager = jack_obj('dev_page_management');

    $cacheID = 'each_menu_item_'.$menu_id;
    $result = getCache($cacheID);

    if(!hasCache($result)){
        $result = $devdb->get_results("SELECT * FROM dev_menu_items WHERE fk_menu_id='". $menu_id ."'ORDER BY item_sort_order ASC");
        setCache($result,$cacheID);
        }

    $nav_menu = !$args['childs_only'] ? '<ul>' : '';

    foreach($result as $g=>$item){
        if($item['fk_item_id']) continue;
        $item_title = '';
        $page = null;

        if($item['fk_page_id']){
            $page = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
            $item_title = $page['page_title'];
            if(!$item['use_page_title']) $item_title = $item['item_title'];
            $link = page_url($page);
            }
        else{
            $item_title = $item['item_title'];
            if(strlen($item['item_ext_url'])) $link = $item['item_ext_url'];
            else $link = ':javascript:';
            }

        $item_title = processToRender($item_title);

        $childs = each_footer_useful_links_render_function($item['pk_item_id'],$result, $args);

        $nav_menu .= '<li class="'.($childs ? 'hasSubMenu' : '').'"><a href="'.$link.'"><i class="fa fa-send"></i>'.$item_title.'</a>' . ($childs ? '<ul>'.$childs.'</ul>' : '') . '</li>';
        }
    $nav_menu .= !$args['childs_only'] ? '</ul>' : '';

    return $nav_menu;
    }

function each_basic_menu_render_function($parent, &$childs, $args = array()){
    global $_config;
    $pageManager = jack_obj('dev_page_management');

    $menu = '';
    $childs_found = false;
    foreach($childs as $i=>$v){
        if($v['fk_item_id'] != $parent) continue;
        $item_title = '';
        $page = null;
        if($v['fk_page_id']){
            $page = $pageManager->get_a_page($v['fk_page_id'],array('tiny' => true));
            $item_title = $page['page_title'];
            if(!$v['use_page_title']) $item_title = $v['item_title'];
            $link = page_url($page);
            }
        else{
            $item_title = $v['item_title'];
            if(strlen($v['item_ext_url'])) $link = $v['item_ext_url'];
            else $link = ':javascript:';
            }

        $item_title = processToRender($item_title);

        $childs_found = true;
        //$args['li_class'] = 'level2 nav-6-1-1 first';
        $c = each_basic_menu_render_function($v['pk_item_id'],$childs, $args);

        $menu .= '<li class="'.($c ? 'hasSubMenu' : '').'"><a href="'.$link.'">'.$item_title.'</a>' . ($c ? '<ul>' . $c . '</ul>' : '') . '</li>';
        }
    $menu .= '';
    if(!$childs_found) $menu = NULL;
    return $menu;
    }

function basic_menu_render_function($args){
    $menu_id = $args['menu_id'];
    global $devdb, $_config;
    $pageManager = jack_obj('dev_page_management');

    $cacheID = 'each_menu_item_'.$menu_id;
    $result = getCache($cacheID);

    if(!hasCache($result)){
        $result = $devdb->get_results("SELECT * FROM dev_menu_items WHERE fk_menu_id='". $menu_id ."'ORDER BY item_sort_order ASC");
        setCache($result,$cacheID);
    }

    $nav_menu = !$args['childs_only'] ? '<ul>' : '';

    foreach($result as $g=>$item){
        if($item['fk_item_id']) continue;
        $item_title = '';
        $page = null;

        if($item['fk_page_id']){
            $page = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
            $item_title = $page['page_title'];
            if(!$item['use_page_title']) $item_title = $item['item_title'];
            $link = page_url($page);
            }
        else{
            $item_title = $item['item_title'];
            if(strlen($item['item_ext_url'])) $link = $item['item_ext_url'];
            else $link = ':javascript:';
            }

        $item_title = processToRender($item_title);

        $childs = each_basic_menu_render_function($item['pk_item_id'],$result, $args);

        $nav_menu .= '<li class="'.($childs ? 'hasSubMenu' : '').'"><a href="'.$link.'">'.$item_title.'</a>' . ($childs ? '<ul>'.$childs.'</ul>' : '') . '</li>';
        }
    $nav_menu .= !$args['childs_only'] ? '</ul>' : '';

    return $nav_menu;
    }
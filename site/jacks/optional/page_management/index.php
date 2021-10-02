<?php
class dev_page_management{
    var $thsClass = 'dev_page_management';
    var $templates = array();

	function __construct(){
        jack_register($this);
		}
	function init(){
        //page related
        $permissions = array(
            'group_name' => 'Page Management',
            'permissions' => array(
                'manage_pages' => array(
                    'add_page' => 'Add Page',
                    'edit_page' => 'Edit Page',
                    'delete_page' => 'Delete Page',
                    ),
                ),
            );

        apiRegister($this,'get_all_pages');
        apiRegister($this,'get_a_page');
        apiRegister($this,'get_a_page_by_slug');
        apiRegister($this,'delete_pages');
        apiRegister($this,'reCachePage');
        apiRegister($this,'create_page_sitemap');

        apiRegister($this, 'get_page_template_options');

        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();
            }

        addAction('after_execute_plugins_event',array($this,'afterJacksLoaded'));
        }
    function get_page_template_options($param = array()){
	    $template_id = null;
	    if($param['template_id']) $template_id = $param['template_id'];
	    else if($_POST['template_id']) $template_id = $_POST['template_id'];

	    $page_id = 0;
        if($param['page_id']) $page_id = $param['page_id'];
        else if($_POST['page_id']) $page_id = $_POST['page_id'];

        if($template_id){
            if($page_id) $pageData = $this->get_a_page($page_id, array('include_tags', 'include_allowed_content_types'));
            else $pageData = array();
            $templateOptionFunction = $template_id.'_options';

            ob_start();
            $this->templates[$template_id]->getFields($pageData);
            $optionalFields = ob_get_clean();

            ob_start();
            if(!$optionalFields){
                ?>
                <div id="page_template_option_<?php echo $template_id?>" class="dn each_page_template_options"></div>
                <?php
                }
            else{
                ?>
                <div id="page_template_option_<?php echo $template_id?>" class="panel panel-info each_page_template_options">
                    <div class="panel-heading">
                        <span class="panel-title"><?php echo $this->templates[$template_id]->label ?> - Options</span>
                    </div>
                    <div class="panel-body">
                        <?php echo $optionalFields?>
                    </div>
                </div>
                <?php
                }
            $output = ob_get_clean();
            return array('success' => $output);
            }
        else return array('error' => array('Invalid Template ID'));
        }
    function afterJacksLoaded(){
        $this->load_all_pages();
        if(class_exists('dev_seo_management')){
            dev_seo_management::$siteMaps['page_sitemap'] = array(
                'title' => 'Page Sitemap',
                'file' => 'page_sitemap.xml',
                'api_url' => url('api/dev_page_management/create_page_sitemap'),
            );
            addAction('after_page_processed',array($this,'create_page_sitemap'));
            addAction('after_page_deleted',array($this,'create_page_sitemap'));
            }
        }
    function registerPageTemplates($templateName, page_templates $pageTemplate){
        $this->templates[$templateName] = $pageTemplate;
        }
    function adm_menus(){
        $params = array(
            'label' => 'New Page',
            'description' => 'Add New Page',
            'menu_group' => 'New',
            'url' => url('admin/dev_page_management/manage_pages?action=add_edit_page'),
            'iconClass' => 'fa-plus-circle',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_pages')) admenu_register($params);

        $params = array(
            'label' => 'Pages',
            'description' => 'Manage all pages',
            'menu_group' => 'Contents',
            'action' => 'manage_pages',
            'iconClass' => 'fa-files-o',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_pages')) admenu_register($params);
        }
    function manage_pages(){
        if(!has_permission('manage_pages')) return;

        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_pages');

        if($_GET['action'] == 'add_edit_page')
            include('pages/add_edit_page.php');
        else
            include('pages/list_pages.php');
        }

    function create_page_sitemap(){
        if(!has_permission('manage_seo')) return array('error' => 'You do not have permission to generate sitemap.');

        set_time_limit(180);

        $file = 'page_sitemap.xml';

        $string = function_exists('getSiteMapHeader') ? getSiteMapHeader() : '';

        $pages = $this->get_all_pages(array('tiny' => true, 'data_only' => true));

        //TODO: Include link of each page for all languages

        foreach($pages['data'] as $i=>$v){
            $string .= getSiteMapLoc(array(
                'url' => page_url($v),
                'time' => date('c',strtotime($v['modified_at']))
                ));
            }

        $string .= function_exists('getSiteMapFooter') ? getSiteMapFooter() : '';

        $written = file_put_contents($file, $string);

        if($written === false)
            return array('error' => 'Something went wrong.');
        else{
            user_activity::add_activity('Generated Page Sitemap.', 'success', 'create');
            return array('success'=>1);
            }
        }

	function load_all_pages(){
		global $_config;

        //check for system pages
        if($_config['system_pages']){
            foreach($_config['system_pages'] as $i=>$v){
                $thePage = $this->get_a_page_by_slug($i);
                if(!$thePage){
                    $v['system_page'] = 'yes';
                    $ret = $this->add_edit_page($v);
                    }
                else{
                    if($v['update_page']){
                        $v['pk_page_id'] = $thePage['pk_page_id'];
                        $v['system_page'] = 'yes';
                        $this->add_edit_page($v);
                        cleanCache('page');
                        }
                    }
                }
            }
		}

    function add_edit_page($param = array(), $oldContent = array()){
        global $devdb, $_config;

        $preData = array();

        if($param['pk_page_id']) $preData = $this->get_a_page($param['pk_page_id']);
        else if($param['page_slug']) $preData = $this->get_a_page_by_slug($param['page_slug']);

        $param['page_slug'] = ($preData && $preData['is_locked'] == 'yes') ? $preData['page_slug'] : ($param['system_page'] == 'yes' ? $param['page_slug'] : form_modifiers::slug($param['page_slug']));

        $insert_data = array(
            'page_slug' => $param['page_slug'],
            'page_title' => processToStore($oldContent['page_title'], $param['page_title']),
            'page_sub_title' => processToStore($oldContent['page_sub_title'], $param['page_sub_title']),
            'page_description' => processToStore($oldContent['page_description'], strlen($param['page_description']) ? $param['page_description'] : ''),
            'parent_page_id' => isset($param['parent_page_id']) && $param['parent_page_id'] ? $param['parent_page_id'] : 0,
            'page_type' => isset($param['page_type']) ? $param['page_type'] : 'static',
            'page_as_category' => isset($param['page_as_category']) ? $param['page_as_category'] : 'no',
            'page_thumbnail' => strlen($param['page_thumbnail']) ? $param['page_thumbnail'] : '',
            'page_square_thumbnail' => strlen($param['page_square_thumbnail']) ? $param['page_square_thumbnail'] : '',
            'page_wide_thumbnail' => strlen($param['page_wide_thumbnail']) ? $param['page_wide_thumbnail'] : '',
            'page_status' => strlen($param['page_status']) ? $param['page_status'] : 'active',
            'page_landing_template' => isset($param['page_landing_template']) ? $param['page_landing_template'] : '',
            'page_meta_keyword' => processToStore($oldContent['page_meta_keyword'], isset($param['page_meta_keyword']) ? $param['page_meta_keyword'] : ''),
            'page_meta_description' => processToStore($oldContent['page_meta_description'], form_modifiers::sanitize_title($param['page_meta_description'] ? $param['page_meta_description'] : mb_substr(form_modifiers::content_to_excerpt($param['page_description']),0,250))),
            'page_excerpt' => processToStore($oldContent['page_excerpt'], form_modifiers::sanitize_title($param['page_excerpt'] ? $param['page_excerpt'] : mb_substr(form_modifiers::content_to_excerpt($param['page_description']),0,250))),
            'is_locked' => $param['system_page'] ? 'yes' : ($preData ? $preData['is_locked'] : 'no'),//$data['is_locked'],
            'page_extras' => processToStore($oldContent['page_extras'], $param['page_extras'] ? $param['page_extras'] : ''),
            );

        if($preData){
            $insert_data['modified_at'] = date('Y-m-d H:i:s');
            $insert_data['modified_by'] = $_config['user']['pk_user_id'];

            $ret = $devdb->insert_update('dev_pages',$insert_data," pk_page_id = '".$param['pk_page_id']."'");
            $page_id = $param['pk_page_id'];
            }
        else{
            $insert_data['created_at'] = date('Y-m-d H:i:s');
            $insert_data['created_by'] = $_config['user']['pk_user_id'];
            $insert_data['modified_at'] = date('Y-m-d H:i:s');
            $insert_data['modified_by'] = $_config['user']['pk_user_id'];

            $ret = $devdb->insert_update('dev_pages',$insert_data);
            $page_id = $ret['success'];
            }
        if($ret['success']){
            $this->reCachePage($page_id);
            }
        return $ret;
        }

    function reCachePage($pageID){
        $args = array(
            'tiny' => true,
            );
        $thePage = $this->get_a_page($pageID, $args);

        removeCache('pages');

        removeCache('each_page_'.$pageID, 'page');
        removeCache('each_page_'.$pageID.'_tiny', 'page');
        removeCache('each_page_'.$pageID.'_minimum', 'page');
        removeCache('each_page_'.$pageID.'_selected', 'page');
        removeCache('each_page_'.$thePage['page_slug'], 'page');
        removeCache('each_page_'.$thePage['page_slug'].'_tiny', 'page');
        removeCache('each_page_'.$thePage['page_slug'].'_minimum', 'page');
        removeCache('each_page_'.$thePage['page_slug'].'_selected', 'page');

        removeCache('each_content_tag_'.$pageID.'_page');

        //NOTE: We are just removing caches, not re-caching the page, think about it
        }

	function delete_pages($param = array()){
		global $devdb;

		if(!is_array($param)) $param = array($param);

        foreach($param as $pageID){
            $args = array(
                'tiny' => true,
                );
            $thePage = $this->get_a_page($pageID, $args);

            $page_sql = $devdb->query("DELETE FROM dev_pages WHERE pk_page_id = '".$pageID."'");
            $content_sql = $devdb->query("DELETE FROM dev_contents WHERE fk_page_id = '".$pageID."'");
            $tag_sql = $devdb->query("DELETE FROM dev_content_tag_relation WHERE fk_content_id = '".$pageID."' AND content_type = 'page'");
            $content_type_relation_sql = $devdb->query("DELETE FROM dev_page_content_types WHERE fk_page_id = '".$pageID."'");

            removeCache('each_page_'.$pageID);
            removeCache('each_page_'.$pageID.'_tiny');
            removeCache('each_page_'.$pageID.'_minimum');
            removeCache('each_page_'.$pageID.'_selected');
            removeCache('each_page_'.$thePage['page_slug']);
            removeCache('each_page_'.$thePage['page_slug'].'_tiny');
            removeCache('each_page_'.$thePage['page_slug'].'_minimum');
            removeCache('each_page_'.$thePage['page_slug'].'_selected');

            removeCache('each_content_tag_'.$pageID.'_page');
            }
		
		return array('success' => 1);
		}
    function search_pages($param=array()){
        /*
         * q = Item to search for
         * */
        global $devdb, $_config;

        if(!$param['page_status']) $param['page_status'] = 'active';

        $sql = "SELECT 'page' as data_type, pk_page_id, page_slug, page_title as title, page_sub_title as subtitle, page_description as description, page_meta_description as meta_description, page_excerpt as excerpt FROM dev_pages
                WHERE
                parent_page_id = 0
                AND MATCH (page_title,page_description,page_sub_title,page_meta_description,page_excerpt) AGAINST ('".$param['q']."')";

        $count_sql = "SELECT
                      COUNT(IF(MATCH (page_title,page_description,page_sub_title,page_meta_description,page_excerpt) AGAINST ('".$param['q']."'), 1, NULL))
                      AS count
                      FROM dev_pages WHERE 1 ";

        $condition = '';

        $loop_conditions = array(
            'page_status' => 'page_status',
            );
        $condition .= sql_condition_maker($loop_conditions, $param);

        $data = process_sql_operation($loop_conditions, $condition, $sql, $count_sql, $param);

        return $data;
        }

	function get_all_pages($param = array()){
        $tagManager = jack_obj('dev_tag_management');
		extract($param);
		
		global $devdb;

		$minimumPageFields = array(
		    'pk_page_id',
            'page_slug',
            'page_title',
            'parent_page_id',
            'page_type',
            'page_thumbnail',
            'page_status',
            'page_landing_template',
            'is_locked',
            'page_as_category',
            'created_at',
            'modified_at');

		$tinyFields = array(
            'pk_page_id',
            'page_slug',
            'page_title',
            'parent_page_id',
            'page_as_category',
            'modified_at',
            );

		$pageQueryTypeForCachingPostFix = $param['tiny'] ? '_tiny' : ($param['minimum'] ? '_minimum' : ($param['select_fields'] ? '_selected' : ''));

		$sql = "SELECT ".($param['tiny'] ? implode(',', $tinyFields) : ($param['minimum'] ? implode(',', $minimumPageFields) : ($param['select_fields'] ? implode(',',$param['select_fields']) : '*')))." FROM dev_pages WHERE 1 ";
        $countSql = "SELECT COUNT(pk_page_id) AS TOTAL FROM dev_pages WHERE 1 ";
        $condition = '';

		$loop_condition = array(
		    'page_types' => 'dev_pages.page_type',
            'parent_page_id' => 'dev_pages.parent_page_id',
            'page_status' => 'dev_pages.page_status',
            'page_id' => 'dev_pages.pk_page_id',
            'page_slug' => 'dev_pages.page_slug',
            'page_category' => 'dev_pages.page_as_category',
            );
		$condition .= sql_condition_maker($loop_condition, $param);

		$order_by = sql_order_by($param);
		$limit_by = sql_limit_by($param);

		$sql .= $condition.$order_by.$limit_by;
		$countSql .= $condition;

		if(isset($param['sql_only'])) return $sql;

		if($param['single']){
            $cacheID = null;
            if($param['page_id']){
                if(is_array($param['page_id'])){
                    if(count($param['page_id']) == 1) $cacheID = 'each_page_'.$param['page_id'][0].$pageQueryTypeForCachingPostFix;
                    }
                else $cacheID = 'each_page_'.$param['page_id'].$pageQueryTypeForCachingPostFix;
                }
            if($param['page_slug']){
                if(is_array($param['page_slug'])){
                    if(count($param['page_slug']) == 1) $cacheID = 'each_page_'.$param['page_slug'][0].$pageQueryTypeForCachingPostFix;
                    }
                else $cacheID = 'each_page_'.$param['page_slug'].$pageQueryTypeForCachingPostFix;
                }

            $pages = $cacheID ? getCache($cacheID,'page') : false;

            if(!hasCache($pages)){
                $pages = sql_data_collector($sql, $countSql, $param);
                if($pages){
                    if('include_tags') $pages['tags'] = $tagManager ? $tagManager->get_page_tags($pages['pk_page_id']) : array();
                    if('include_allowed_content_types') $pages['allowed_content_types'] = $devdb->get_results("SELECT * FROM dev_page_content_types WHERE fk_page_id = '".$pages['pk_page_id']."'");
                    }
                if($cacheID) setCache($pages, $cacheID, 'page');
				}
            }
		else{
		    $pages = sql_data_collector($sql, $countSql, $param);

		    if($pages && $pages['data']){
                foreach($pages['data'] as $i=>$item){
                    if('include_tags') $pages['data'][$i]['tags'] = $tagManager ? $tagManager->get_page_tags($item['pk_page_id']) : array();
                    if('include_allowed_content_types') $pages['data'][$i]['allowed_content_types'] = $devdb->get_results("SELECT * FROM dev_page_content_types WHERE fk_page_id = '".$item['pk_page_id']."'");
                    }
                }
            }
		
		return $pages;
		}
    function get_pages_by_content_type($content_type){
	    global $devdb;

	    $sql = "SELECT fk_page_id FROM dev_page_content_types WHERE fk_content_type_id = '".$content_type."'";
	    $data = $devdb->get_results($sql);

	    return $data;
        }
	function get_a_page($page_id = NULL, $additional = array()){
		if(!$page_id) return null;

        $args = array(
            'page_id' => $page_id,
            'single' => true,
            );
        $args = array_replace_recursive($args, $additional);
        $page = $this->get_all_pages(array_replace_recursive($args, $additional));

        return $page;
		}

	function get_a_page_by_slug($page_slug = NULL, $additional = array()){
        if(!$page_slug) return null;

        $args = array(
            'page_slug' => $page_slug,
            'single' => true,
            );
        $page = $this->get_all_pages(array_replace_recursive($args, $additional));
        return $page;
		}
	}
new dev_page_management;

function get_category_pages(){
    $pageManager = jack_obj('dev_page_management');

    $cats = array();

    $allPages = $pageManager->get_all_pages(array('data_only' => true));

    if($allPages['data']){
        foreach($allPages['data'] as $i=>$v){
            if($v['page_as_category'] == 'yes'){
                $cats[$v['pk_page_id']] = $v;
                }
            }
        }

    uasort($cats, function($a, $b) {
        return strcmp($a["page_title"], $b["page_title"]);
        });

    return $cats;
    }

function get_sub_categories($page_id,$include_me = false,$go_deep = true){
    $pageManager = jack_obj('dev_page_management');
    $cats = array();
    $allPages = $pageManager->get_all_pages(array('data_only' => true));
    if($allPages['data']){
        foreach($allPages['data'] as $i=>$v){
            //if($v['page_status'] != 'active') continue;
            if($v['parent_page_id'] == $page_id && $v['page_as_category'] == 'yes'){
                $cats[$v['pk_page_id']] = $v;
                if($go_deep){
                    $temp = get_sub_categories($v['pk_page_id']);
                    if($temp){
                        foreach($temp as $m=>$n){
                            $cats[$n['pk_page_id']] = $n;
                            }
                        }
                    }
                }
            }
        }

    if($include_me){
        foreach($allPages['data'] as $i=>$v){
            if($v['pk_page_id'] == $page_id)
                $cats[$page_id] = $v;
            }
        }
    return $cats;
    }

function get_my_parent_categories($page_id, $include_me = false){
    $cats = array();
    $pageManager = jack_obj('dev_page_management');
    $this_page = $pageManager->get_a_page($page_id, array('tiny' => true, 'page_category' => 'yes'));
    if($this_page){
        $cats[$this_page['parent_page_id']] = $this_page;
        get_my_parent_categories($this_page['parent_page_id']);
        //TODO: Is this okay? Shouldn't we get the return from the recursive call and append it to the cats array?
        }

    if($include_me){
        $cats[$page_id] = $this_page;
        }
    return $cats;
    }

function get_parent_category($page_id){
    $pageManager = jack_obj('dev_page_management');
    $thePage = $pageManager->get_a_page($page_id, array('tiny' => true));
    if($thePage['parent_page_id']){
        $theParentPage = $pageManager->get_a_page($thePage['parent_page_id'], array('tiny' => true));
        return $theParentPage;
        }
    else return null;
    }

function get_parent_categories_only(){
    $pageManager = jack_obj('dev_page_management');
    $thePages = $pageManager->get_all_pages(array('parent_page_id' => 0, 'page_category' => 'yes', 'data_only' => true));
    return $thePages['data'];
    }

function has_child_category($page_id){
    $pageManager = jack_obj('dev_page_management');
    $thePages = $pageManager->get_all_pages(array('parent_page_id' => $page_id, 'page_category' => 'yes', 'data_only' => true));
    if($thePages['data']) return true;
    else return false;
    }

function get_sub_pages($page_id,$recursive = false){
    $pageManager = jack_obj('dev_page_management');
    $thePages = $pageManager->get_all_pages(array('parent_page_id' => $page_id, 'data_only' => true));

    if($thePages['data']){
        if($recursive){
            foreach($thePages['data'] as $i=>$v){
                $thePages['data'][$i]['subpages'] = get_sub_pages($v['pk_page_id'], $recursive);
                }
            }
        return $thePages['data'];
        }
    else return array();
    }

function get_parent_page($page_id){
    $pageManager = jack_obj('dev_page_management');
    $thePage = $pageManager->get_a_page($page_id, array('tiny' => true));
    if($thePage['parent_page_id']) $theParentPage = $pageManager->get_a_page($thePage['parent_page_id'], array('tiny' => true));
    else $theParentPage = null;

    return $theParentPage;
    }

function get_parent_pages_only(){
    $pageManager = jack_obj('dev_page_management');
    $thePages = $pageManager->get_all_pages(array('parent_page_id' => 0, 'data_only' => true));
    $thePages = array_reverse($thePages['data']);

    return $thePages;
    }

function has_child_page($page_id){
    $pageManager = jack_obj('dev_page_management');
    $thePages = $pageManager->get_all_pages(array('parent_page_id' => $page_id, 'data_only' => true));

    if($thePages['data']) return true;
    else return false;
    }

function remove_child_categories($page_id, &$data){
    if($data){
        foreach($data as $i=>$v){
            if($v['fk_page_id'] == $page_id){
                remove_child_categories($v['pk_page_id'], $data);
                unset($data[$v['pk_page_id']]);
                }
            }
        }
    }

function compareByPageName($a, $b) {
    return strcmp($a["page_title"], $b["page_title"]);
    }

function getPageSelectOptions($selectedPage = null,$inititalPage = null,$level = 0,$excludePage = null,$include_inactive = false){
    $pageManager = jack_obj('dev_page_management');

    $output = '';

    $pages = $inititalPage ? array($inititalPage => $pageManager->get_a_page($inititalPage, array('tiny' => true))) : get_parent_pages_only();

    $indentation = '';
    if($level){
        for($i = 0; $i<$level; $i++){
            $indentation .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }

    if($pages){
        //pre($pages,0);
        usort($pages, 'compareByPageName');
        //pre($pages);
        foreach($pages as $i=>$v){
            if($v['page_status'] == 'inactive' && !$include_inactive) continue;
            if($excludePage == $v['pk_page_id']) continue;
            $selected = $selectedPage && $selectedPage == $v['pk_page_id'] ? 'selected' : '';
            $sub_pages = get_sub_pages($v['pk_page_id']);
            $output .= '<option class="'.($sub_pages ? 'optgroup' : '').'" value="'.$v['pk_page_id'].'" '.$selected.'>'.$indentation.processToRender($v['page_title']).'</li>';

            if($sub_pages){
                usort($pages, 'compareByPageName');
                foreach($sub_pages as $m=>$n){
                    if($n['page_status'] == 'inactive') continue;
                    if($excludePage == $n['pk_page_id']) continue;
                    $output .= getPageSelectOptions($selectedPage,$n['pk_page_id'],$level+1);
                    }
                }
            }
        }

    return $output;
    }

function add_system_pages(){
    $args = func_get_args();
    /*
     * 0: Page Slug
     * 1: page title
     * 2: Page landing template
     * 3: Update info in Database when true
     * */

    global $_config;
    if($args[0]){
        $_config['system_pages'][$args[0]] = array(
            'page_slug' => $args[0],
            'page_title' => isset($args[1]) ? $args[1] : '',
            'page_landing_template' => isset($args[2]) ? $args[2] : '',
            'update_page' => isset($args[3]) ? $args[3] : false,
            );
        }
    }
function thePage($field){
    global $_config;
    if(isset($_config['current_page']) && $_config['current_page']){
        if(isset($_config['current_page'][$field])) return $_config['current_page'][$field];
        else return '';
        }
    else return '';
    }
function thePageExtras($field, $returnFalse = false, $returnThis = ''){
    global $_config;
    if(isset($_config['current_page']) && $_config['current_page']){
        if($_config['current_page']['page_extras']){
            if(isset($_config['current_page']['page_extras'][$field])) return $_config['current_page']['page_extras'][$field];
            else return $returnFalse ? false : $returnThis;
            }
        else return $returnFalse ? false : $returnThis;
        }
    else return $returnFalse ? false : $returnThis;
    }
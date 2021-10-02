<?php
class dev_content_management{
    var $thsClass = 'dev_content_management';
    var $cTypes = array();
    var $cTypeFields = array();
    var $cTypePreProcess = array();
    var $cTypePostProcess = array();
    var $cTypeFlags = array();
    var $cTypeExceptional = array();
    var $useEditorForTitle = false;
    var $cTypeSystemFieldsSetting = array();

    var $uCTypes = array();

    function __construct(){
        jack_register($this);
        }

    function init(){
        global $devdb;

        apiRegister($this,'get_contents');
        apiRegister($this,'get_responsive_image', array('internalOnly'));
        //apiRegister($this,'copy_contents');
        apiRegister($this,'search_contents');
        apiRegister($this,'delete_contents');
        apiRegister($this,'get_content_extended');
        apiRegister($this,'get_content_meta');
        apiRegister($this,'put_content_meta');
        apiRegister($this,'search_content_meta');
        apiRegister($this,'related_posts');
        apiRegister($this,'create_content_sitemap');
        apiRegister($this,'get_image','noSecurity');

        $permissions = array(
            'group_name' => 'Content Management',
            'permissions' => array(
                'manage_contents' => array(
                    'add_contents' => 'Add',
                    'edit_contents' => 'Edit',
                    'delete_contents' => 'Delete',
                    //'copy_contents' => 'Copy',
                    ),
                'manage_content_types' => 'Manage Content Types',
                ),
            );

        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();
            }
        addAction('list_content_process_on_get',array($this,'delete_content_on_get'));
        addAction('after_execute_plugins_event',array($this,'afterJacksLoaded'));
        addAction('content_types_loaded',array($this,'create_dashboard_widgets'));

        //Custom Content Types

        }
    function create_dashboard_widgets(){
        register_dashboard_widgets(array(
            'widget_id' => 'content_management',
            'jack_object' => $this,
            'render_action' => 'dw_content_management',
            'widget_title' => 'Manage Contents',
            'widget_size' => 12,
            ));

        register_dashboard_widgets(array(
            'widget_id' => 'last_updated_contents',
            'jack_object' => $this,
            'render_action' => 'dw_last_updated_contents',
            'widget_title' => 'Last Updated Contents',
            'widget_size' => 6,
            'register_api' => true,
            'container_padding' => false,
            ));

        register_dashboard_widgets(array(
            'widget_id' => 'most_viewed_contents',
            'jack_object' => $this,
            'render_action' => 'dw_most_viewed_contents',
            'widget_title' => 'Most Viewed Contents',
            'widget_size' => 6,
            'register_api' => true,
            'container_padding' => false,
            ));
        }
    function dw_last_updated_contents(){
        global $multilingualFields, $_config;
        $pageManager = jack_obj('dev_page_management');

        $start = $_POST['start'] ? $_POST['start'] : 0;
        $per_page = 5;

        $args = array(
            'status' => array('published','draft','pending'),
            'only_parent' => true,
            'data_only' => true,
            'order_by' => array(
                'col' => 'dev_contents.modified_at',
                'order' => 'DESC'
                ),
            'limit' => array(
                'start' => $start * $per_page,
                'count' => $per_page
                ),
            );
        $contents = $this->get_contents($args);

        $rows = '';
        $rowCount = !$start ? 1 : ($start*$per_page)+1;
        if($contents['data']){
            ob_start();
            foreach($contents['data'] as $i=>$item){
                if($multilingualFields['dev_contents']){
                    foreach($item as $index=>$value){
                        if(in_array($index, $multilingualFields['dev_contents']) !== false)
                            $item[$index] = processToRender($item[$index]);
                        }
                    }
                $thePage = null;
                if($item['fk_page_id']) $thePage = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
                $edit_link = url('admin/dev_content_management/manage_contents?content_type='.$item['fk_content_type_id'].'&action=add_edit_contents&edit='.$item['pk_content_id']);
                ?>
                <tr data-href="<?php echo $edit_link; ?>">
                    <td><?php echo $rowCount++; ?></td>
                    <td><?php echo $item['content_title']; ?></td>
                    <td><?php echo $thePage ? processToRender($thePage['page_title']) : 'N/A'; ?></td>
                    <td><?php echo $item['fk_content_type_id'] ? $_config['content_types'][$item['fk_content_type_id']]['title'] : 'N/A'; ?></td>
                    <td class="tac vam"><span class="fa fa-circle fa-2x text-<?php echo $item['content_status'] == 'draft' ? 'default' : 'success' ?>"></span></span></td>
                    <td class="action_column"><?php echo print_date($item['modified_at'],1); ?></td>
                </tr>
                <?php
                }
            $rows = ob_get_clean();
            }

        if($_POST['ajax_call']) return $rows;

        ?>
        <div class="table-primary">
            <table id="dw_last_updated_table" class="linked-row-table table table-bordered table-condensed table-striped table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Page</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Update Time</th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    if(strlen($rows)) echo $rows;
                    else echo '<tr><td colspan="6" class="text-danger"><em>No Content Found</em></td></tr>'
                    ?>
                </tbody>
            </table>
            <div class="table-footer tac">
                <?php
                echo linkButtonGenerator(array(
                    'href' => 'javascript:',
                    'action' => 'view',
                    'icon' => 'icon_refresh',
                    'text' => 'Load More',
                    'title' => 'Load More',
                    'id' => 'dw_last_updated_load_more',
                    'size' => 'sm',
                    ));
                ?>
            </div>
        </div>
        <script type="text/javascript">
            var dw_last_updated_start = <?php echo $start + 1;?>;
            init.push(function(){
                $('#dw_last_updated_load_more').on('click', function(){
                    var ths = $(this);
                    basicAjaxCall({
                        beforeSend: function(){show_button_overlay_working(ths)},
                        complete: function(){hide_button_overlay_working(ths)},
                        url: _root_path_+'/api/dev_content_management/dw_last_updated_contents',
                        data: {
                            start: dw_last_updated_start,
                            ajax_call: true,
                            },
                        success: function(ret){
                            if(ret.length){
                                $('#dw_last_updated_table tbody').append(ret);
                                dw_last_updated_start += 1;
                                }
                            else{
                                ths.addClass('disabled btn-danger');
                                ths.html('No More Content');
                                }
                            },
                        });
                    });
                });
        </script>
        <?php
        }
    function dw_most_viewed_contents(){
        global $multilingualFields, $_config;
        $pageManager = jack_obj('dev_page_management');

        $start = $_POST['start'] ? $_POST['start'] : 0;
        $per_page = 5;

        $args = array(
            'status' => array('published','draft','pending'),
            'only_parent' => true,
            'data_only' => true,
            'order_by' => array(
                'col' => 'dev_contents.content_view_count',
                'order' => 'DESC'
                ),
            'limit' => array(
                'start' => $start * $per_page,
                'count' => $per_page
                ),
            );
        $contents = $this->get_contents($args);

        $rows = '';

        $rowCount = !$start ? 1 : ($start*$per_page)+1;

        if($contents['data']){
            ob_start();
            foreach($contents['data'] as $i=>$item){
                if($multilingualFields['dev_contents']){
                    foreach($item as $index=>$value){
                        if(in_array($index, $multilingualFields['dev_contents']) !== false)
                            $item[$index] = processToRender($item[$index]);
                    }
                }
                $thePage = null;
                if($item['fk_page_id']) $thePage = $pageManager->get_a_page($item['fk_page_id'],array('tiny' => true));
                $edit_link = url('admin/dev_content_management/manage_contents?content_type='.$item['fk_content_type_id'].'&action=add_edit_contents&edit='.$item['pk_content_id']);
                ?>
                <tr data-href="<?php echo $edit_link; ?>">
                    <td><?php echo $rowCount++; ?></td>
                    <td><?php echo $item['content_title']; ?></td>
                    <td><?php echo $thePage ? processToRender($thePage['page_title']) : 'N/A'; ?></td>
                    <td><?php echo $item['fk_content_type_id'] ? $_config['content_types'][$item['fk_content_type_id']]['title'] : 'N/A'; ?></td>
                    <td class="tac vam"><span class="fa fa-circle fa-2x text-<?php echo $item['content_status'] == 'draft' ? 'default' : 'success' ?>"></span></span></td>
                    <td class="action_column"><?php echo $item['content_view_count']; ?></td>
                </tr>
                <?php
                }
            $rows = ob_get_clean();
            }

        if($_POST['ajax_call']) return $rows;
        ?>
        <div class="table-primary">
            <table id="dw_most_viewed_table" class="linked-row-table table table-bordered table-condensed table-striped table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Page</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Views</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(strlen($rows)) echo $rows;
                else echo '<tr><td colspan="6" class="text-danger"><em>No Content Found</em></td></tr>'
                ?>
                </tbody>
            </table>
            <div class="table-footer tac">
                <?php
                echo linkButtonGenerator(array(
                    'href' => 'javascript:',
                    'action' => 'view',
                    'icon' => 'icon_refresh',
                    'text' => 'Load More',
                    'title' => 'Load More',
                    'id' => 'dw_most_viewed_load_more',
                    'size' => 'sm',
                ));
                ?>
            </div>
        </div>
        <script type="text/javascript">
            var dw_most_viewed_start = <?php echo $start + 1;?>;
            init.push(function(){
                $('#dw_most_viewed_load_more').on('click', function(){
                    var ths = $(this);
                    basicAjaxCall({
                        beforeSend: function(){show_button_overlay_working(ths)},
                        complete: function(){hide_button_overlay_working(ths)},
                        url: _root_path_+'/api/dev_content_management/dw_most_viewed_contents',
                        data: {
                            start: dw_most_viewed_start,
                            ajax_call: true,
                        },
                        success: function(ret){
                            if(ret.length){
                                $('#dw_most_viewed_table tbody').append(ret);
                                dw_most_viewed_start += 1;
                            }
                            else{
                                ths.addClass('disabled btn-danger');
                                ths.html('No More Content');
                            }
                        },
                    });
                });
            });
        </script>
        <?php
    }
    function dw_content_management(){
        global $devdb, $_config;
        ?>
        <div class="row mb0">
        <?php
        foreach($_config['content_types'] as $i=>$v){
            if(isset($v['exceptional']) && $v['exceptional']) continue;
            $args = array(
                'status' => array('published','draft','pending'),
                'content_types' => $i,
                'only_parent' => true,
                'count_only' => true,
                );
            $totalContents = $this->get_contents($args);
            ?>
            <div class="col-sm-3">
                <div class="panel">
                    <div class="panel-heading">
                        <span class="panel-title"><?php echo $v['title']; ?></span>
                        <div class="panel-heading-controls">
                            <span class="panel-heading-text"><a href="<?php echo url('admin/dev_content_management/manage_contents?action=add_edit_contents&content_type='.$i); ?>"><i class="fa fa-plus-circle"></i>&nbsp;New</a></span>
                        </div>
                    </div>
                    <div class="panel-body p0">
                        <a class="db p15" title="Manage All <?php echo $v['title']?>" href="<?php echo url('admin/dev_content_management/manage_contents?content_type='.$i); ?>">
                            <span class="text-xlg"><?php echo $totalContents; ?></span>
                            <span class="text-sm">Total</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php
            }
        ?>
        </div>
        <?php
        }
    function afterJacksLoaded(){
        global $devdb, $_config;

        if(class_exists('dev_seo_management')){
            dev_seo_management::$feeds['feed'] = array(
                'obj' => $this,
                'func' => 'contentFeed'
                );
            dev_seo_management::$siteMaps['content_sitemap'] = array(
                'title' => 'Content Sitemap',
                'file' => 'content_sitemap.xml',
                'api_url' => url('api/dev_content_management/create_content_sitemap'),
                );
            addAction('after_content_processed', array($this,'create_content_sitemap'));
            addAction('after_content_deleted', array($this,'create_content_sitemap'));
            }
        //processing content's comments
        $commentManager = jack_obj('dev_comment_management');
        if($commentManager && $_POST['comment_form_submit']){

            if(!$_POST['the_post']){
                add_notification('No post selected.','error');
                header('Location: '.current_url());
                exit();
            }
            $args = array(
                'content_id' => $_POST['the_post'],
                'single' => true,
            );
            $the_content = $this->get_contents($args);

            if($the_content['content_comment_moderate'] == 'yes')
                $_POST['comment_status'] = 'pending';
            else $_POST['comment_status'] = 'published';

            if($the_content['content_allow_comment'] == 'yes'){
                $_POST['content_type'] = 'content';

                $ret = $commentManager->put_comment();

                if($ret['success']){
                    $post_url = detail_url($the_content);
                    $update_content = $devdb->query("UPDATE dev_contents SET content_comment_count = content_comment_count+1 WHERE pk_content_id='".$_POST['the_post']."'");

                    $commentManager->reCacheComment($the_content['pk_content_id']);
                    add_notification('Comment has been received.','success');
                    header('Location: '.current_url());
                    exit();
                }
                else{
                    if($ret['error']){
                        foreach($ret['error'] as $e){
                            add_notification($e,'error');
                            //header('Location: '.current_url());
                            //exit();
                        }
                    }
                }
            }
            else{
                add_notification('Comment is not allowed on this post','error');
                header('Location: '.current_url());
                exit();
            }
        }

        if($this->uCTypes){
            $_config['content_types'] = array();
            foreach($this->uCTypes as $obj){
                $_config['content_types'][$obj->name] = array(
                    'title' => ucfirst($obj->name),
                    'exceptional' => $obj->exceptional,
                    );
                }
            }

        if(!isPublic()){
            foreach($_config['content_types'] as $i=>$v){
                if(isset($v['exceptional']) && $v['exceptional']) continue;

                $params = array(
                    'label' => 'New '.$v['title'],
                    'description' => 'Add New '.$v['title'],
                    'menu_group' => 'New',
                    'url' => url('admin/dev_content_management/manage_contents?action=add_edit_contents&content_type='.$i),
                    'iconClass' => 'fa-plus-circle',
                    'jack' => $this->thsClass,
                    );
                if (has_permission('add_contents')) admenu_register($params);
                $params = array(
                    'label' => $v['title'],
                    'description' => 'Manage all '.$v['title'],
                    'menu_group' => 'Contents',
                    'url' => url('admin/dev_content_management/manage_contents?content_type='.$i),
                    'iconClass' => 'fa-list-alt',
                    'jack' => $this->thsClass,
                    );

                if (has_permission('manage_contents')) admenu_register($params);
                }
            }
        doAction('content_types_loaded');
        }
    function get_image(){
        $image = convertPaths(get_image($_GET['image'], $_GET['size']));
        $mime = explode(';',get_file_mime_type($image));
        $mime = $mime[0];
        header('Content-Type:'.strtoupper($mime));
        readfile($image);
        exit();
        }
    function get_responsive_image(){
        $file = $_GET['image'];
        $size = $_GET['size'];
        $pathFolder = $_GET['path_folder'];
        $saveDir = $_GET['save_dir'];
        $alternatives = json_decode($_GET['alternatives']);
        $forceMaxWidth = $_GET['force_max_width'];

        $screenWidth = $_GET['screenWidth'];
        $baseWidth = $_GET['baseWidth'];

        /*$resizedDesiredWidth = $desiredWidth ? ceil(($desiredWidth/$baseWidth)*$screenWidth) : $desiredWidth;
        $resizedDesiredHeight = $desiredHeight ? ceil(($desiredHeight/$desiredWidth)*$resizedDesiredWidth) : $desiredHeight;
        $desiredSizeParts[0] = $resizedDesiredWidth;
        $desiredSizeParts[1] = $resizedDesiredHeight;
        $size = implode('x', $desiredSizeParts);*/

        $image = get_image($file, $size, $pathFolder, $saveDir, $alternatives, $forceMaxWidth);
        //pre($image);
        echo json_encode(array('success' => $image));
        exit();

        $image = convertPaths(get_image($file, $size, $pathFolder, $saveDir, $alternatives, $forceMaxWidth));
        $mime = explode(';',get_file_mime_type($image));
        $mime = $mime[0];
        header('Content-Type:'.strtoupper($mime));
        readfile($image);
        exit();
        }
    function adm_menus(){
        $args = array(
            'menu_group' => 'New',
            'menu_icon' => 'fa fa-plus-circle'
            );
        admin_menu_group($args);
        $params = array(
            'label' => 'New Content',
            'description' => 'Add New Content',
            'menu_group' => 'Contents',
            'url' => url('admin/dev_content_management/manage_contents?action=add_edit_contents'),
            'iconClass' => 'fa-plus-circle',
            'jack' => $this->thsClass,
            );
        //if (has_permission('add_contents')) admenu_register($params);
        $params = array(
            'label' => 'Contents',
            'description' => 'Manage all Contents',
            'menu_group' => 'Contents',
            'action' => 'manage_contents',
            'iconClass' => 'fa-list-alt',
            'jack' => $this->thsClass,
            );
        //if (has_permission('manage_contents')) admenu_register($params);
        $params = array(
            'label' => 'Content Types',
            'description' => 'Manage all Content Types',
            'menu_group' => 'Contents',
            'action' => 'manage_content_types',
            'iconClass' => 'fa-list-alt',
            'jack' => $this->thsClass,
            );
        //if (has_permission('manage_content_types')) admenu_register($params);
        $params = array(
            'label' => 'Manage Contents',
            'description' => 'Manage All Contents',
            'menu_group' => 'Contents',
            'action' => 'manage_all_content',
            'iconClass' => 'fa-cog',
            'jack' => $this->thsClass,
            );
        if (has_permission('manage_contents')) admenu_register($params);
        }

    function manage_all_content(){
        if (!has_permission('manage_contents')) return;
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_all_content');
        include('pages/swap_contents.php');
        }

    function manage_content_types(){
        if (!has_permission('manage_content_types')) return;
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_content_types');
        include('pages/list_content_types.php');
        }

    function manage_contents(){
        if (!has_permission('manage_contents')) return;

        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_contents');

        if ($_GET['action'] == 'add_edit_contents')
            include('pages/add_edit_contents.php');
        else
            include('pages/list_contents.php');
        }

    function delete_content_on_get(){
        $args = func_get_args();
        if(!isset($args[0]['delete'])) return null;
        $item = $args[0]['delete'];

        if(!has_permission('delete_contents')){
            add_notification('You don\'t have enough permission to delete contents.','error');
            header('Location:'.build_url(NULL,array('delete')));
            exit();
            }
        $args = array(
            'content_id' => $item,
            'status' => array('published','draft','pending'),
            'single' => true,
            );
        $the_content = $this->get_contents($args);

        if($the_content){
            $ret = $this->delete_contents($item);

            if($ret){
                add_notification('Content(s) has been deleted.','success');
                user_activity::add_activity('The content (ID: '.$the_content['pk_content_id'].') has been deleted','success', 'delete');
                doAction('after_content_deleted');
                }
            else add_notification('Content(s) has not been deleted, please try again.','error');
            }
        else add_notification('Content was not found.','error');

        header('Location:'.build_url(NULL,array('delete')));
        exit();
        }

    function create_content_sitemap(){
        if(!has_permission('manage_seo')) return array('error' => 'You do not have permission to generate sitemap.');

        set_time_limit(180);

        $file = 'content_sitemap.xml';

        $args = array(
            'GREATER_THAN' => array(
                'page_id' => '0'
                ),
            'status' => 'published',
            );
        $contents = $this->get_contents($args);

        $string = function_exists('getSiteMapHeader') ? getSiteMapHeader() : '';

        foreach($contents['data'] as $i=>$v){
            $string .= getSiteMapLoc(array(
                            'url' => detail_url($v),
                            'time' => date('c',strtotime($v['modified_at']))
                            ));
            }

        $string .= function_exists('getSiteMapFooter') ? getSiteMapFooter() : '';

        $written = file_put_contents($file, $string);

        if($written === false)
            return array('error'=>'Something went wrong.');
        else{
            user_activity::add_activity('Generated Content Sitemap.', 'success', 'create');
            return array('success'=>1);
            }
        }

    function contentFeed(){
        global $_config;
        $pageManager = jack_obj('dev_page_management');
        $args = array(
            'select_fields' => array(
                'pk_content_id',
                'content_slug',
                'content_title',
                'content_excerpt',
                'content_thumbnail',
                'fk_page_id',
                'fk_content_type_id',
                'content_status',
                'content_published_time',
                'created_at',
                'modified_at',
                ),
            'status' => 'published',
            'GREATER_THAN' => array(
                'page_id' => '0'
                ),
            );
        $contents = $this->get_contents($args);

        if($contents['data']){
            foreach($contents['data'] as $i=>$v){
                $output = '';
                $thePage = $pageManager->get_a_page($v['fk_page_id'],array('tiny' => true));

                $output .= "\t\t".'<item>'. "\r\n";
                $output .= "\t\t\t".'<title><![CDATA['.form_modifiers::siteMapEscape(processToRender($v['content_title'])).']]></title>'. "\r\n";
                $output .= "\t\t\t".'<link>'.form_modifiers::siteMapEscape(detail_url($v)).'</link>'. "\r\n";
                $output .= "\t\t\t".'<pubDate>'.date('c',strtotime($v['created_at'])).'</pubDate>'. "\r\n";
                $output .= "\t\t\t".'<updated>'.date('c',strtotime($v['modified_at'])).'</updated>'. "\r\n";
                $output .= "\t\t\t".'<category>'.form_modifiers::siteMapEscape(processToRender($thePage['page_title'])).'</category>'. "\r\n";
                $output .= "\t\t\t".'<content:encoded><p>'.form_modifiers::siteMapEscape(processToRender($v['content_excerpt'])).'<a href="'.detail_url($v).'">Read More</a></p></content:encoded>'. "\r\n";
                $output .= "\t\t\t".'<image>'. "\r\n";
                $output .= "\t\t\t\t".'<url>'.form_modifiers::siteMapEscape(get_image($v['content_thumbnail'],'1200x600')).'</url>'. "\r\n";
                $output .= "\t\t\t\t".'<title>'.form_modifiers::siteMapEscape(processToRender($v['content_title'])).'</title>'. "\r\n";
                $output .= "\t\t\t\t".'<link>'.form_modifiers::siteMapEscape(detail_url($v)).'</link>'. "\r\n";
                $output .= "\t\t\t".'</image>'. "\r\n";
                $output .= "\t\t".'</item>'. "\r\n";

                echo $output;
                }
            }
        }

    function lowest_archive_date(){
        global $devdb, $_config;

        $_config['lowest_archive'] = getCache('lowest_archive_date');

        if(!hasCache($_config['lowest_archive'])){
            if($devdb){
                $sql = "SELECT DATE(content_published_time) AS lowest_archive FROM dev_contents WHERE content_status = 'published' AND fk_content_type_id != '".$_config['content_types']['magazine']."' ORDER BY lowest_archive ASC";
                $data = $devdb->get_row($sql);
                $_config['lowest_archive'] = $data ? $data['lowest_archive'] : null;
                }
            else $_config['lowest_archive'] = null;

            //setCache($_config['lowest_archive'], 'lowest_archive_date', 'content');
            }
        }

    function getCTypeSetting($field, $setting, $cType){
        $settingsSetName = $field.'_settings';
        if($this->uCTypes[$cType] && isset($this->uCTypes[$cType]->$settingsSetName)){
            $cTypeConfig = $this->uCTypes[$cType]->$settingsSetName;
            if(isset($cTypeConfig[$setting])) return $cTypeConfig[$setting];
            else return null;
            }

        else return null;
        }

    function register_content_type($cTypeName, custom_content_type $cType){
        $this->uCTypes[$cTypeName] = $cType;
        }

    /* widgets*/

    function processFiles($param = array()){
        global $devdb, $_config;

        $contentId = $param['content_id'];
        $contentType = $param['content_type'];
        $files = $param['files'];

        $sql = "DELETE FROM dev_content_files WHERE fk_content_id = '".$contentId."'";
        $deleteOldFiles = $devdb->query($sql);

        if($files){
            foreach($files as $i=>$v){
                if(!mb_strlen($v['file_name'])) continue;
                $fileData = array(
                    'fk_content_id' => $contentId,
                    'fk_content_type' => $contentType,
                    'file_title' => $v['file_title'],
                    'file_name' => $v['file_name'],
                    'file_description' => $v['file_description'],
                    'file_sort_order' => $i,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $_config['user']['pk_user_id'],
                    'modified_at' => date('Y-m-d H:i:s'),
                    'modified_by' => $_config['user']['pk_user_id'],
                    );
                $fileInserted = $devdb->insert_update('dev_content_files',$fileData);
                if($fileInserted['error']){
                    foreach($fileInserted['error'] as $e){
                        add_notification($e,'error');
                        }
                    }
                }
            }
        }

    function search_contents($param=array()){
        /*
         * q = Item to search for
         * */
        global $devdb, $_config;

        if(!$param['status']) $param['status'] = 'published';
        $param['GREATER_THAN'] = array();
        $param['BETWEEN_INCLUSIVE'] = array();
        $param['LESS_THAN_EQUAL'] = array();
        $param['BETWEEN_INCLUSIVE'] = array();

        $sql = "SELECT 'content' as data_type, pk_content_id, fk_content_type_id, content_slug, fk_page_id,  content_title as title, content_sub_title as subtitle, content_description as description, content_meta_description as meta_description, content_excerpt as excerpt FROM dev_contents
                WHERE
                fk_content_id = 0
                AND MATCH (content_title,content_description,content_sub_title,content_meta_description,content_excerpt) AGAINST ('".$param['q']."')";

        $count_sql = "SELECT
                      COUNT(IF(MATCH (content_title,content_description,content_sub_title,content_meta_description,content_excerpt) AGAINST ('".$param['q']."'), 1, NULL))
                      AS count
                      FROM dev_contents WHERE 1 ";

        $condition = '';

        if($param['archive_date']) $param['BETWEEN_INCLUSIVE']['publish_time'] = array('left' => $param['archive_date'], 'right' => $param['archive_date'].' 23:59:59');
        if($param['published_so_far']) $param['LESS_THAN_EQUAL']['publish_time'] = $param['published_so_far'].' 23:59:59';
        if($param['published_till_now']) $param['LESS_THAN_EQUAL']['publish_time'] = date('Y-m-d H:i:s');
        if($param['yearwise']) $param['BETWEEN_INCLUSIVE']['publish_time'] = array('left' => $param['yearwise'].'-01-01', 'right' => $param['yearwise'].'-12-31 23:59:59');

        $param['GREATER_THAN']['page_id'] = 0;

        $loop_conditions = array(
            'content_types' => 'fk_content_type_id',
            'status' => 'content_status',
            'publish_time' => 'content_published_time',
            'page_id' => 'fk_page_id',
            );

        $data = process_sql_operation($loop_conditions, $condition, $sql, $count_sql, $param);

        if($param['include_page_search']){
            $pageManager = jack_obj('dev_page_management');
            if($pageManager){
                $pageData = $pageManager->search_pages($param);
                $data['data'] = array_merge_recursive($data['data'], $pageData['data']);
                if(!$param['data_only']) $data['total'] += $pageData['total'];
                }
            }

        return $data;
        }

    function get_contents($param = array()){
        $tagManager = jack_obj('dev_tag_management');
        /*** SAMPLE ---
         * $args = array(
         *  'q' = 'abcd'
         * 'content_types' => array('article','blog'),
         * 'not_content_types' => array('article','blog'),
         * 'content_types_ids' => array(1,2),
         * 'not_content_types_ids' => array(1,2),
         * 'content_slug' => 'a_alug' // string only
         * 'status' => array('published','active'),
         * 'content_id' => array(1,2),
         * 'ignore_content_id' => array(1,2) or 1 or 2 or3
         * 'parent_content_id' => array(1,2),
         * 'page_id' => array(1,2),
         * 'user_id' => array(1,2) or 1
         * 'tag_id' => array(1,2) or 1
         * 'select_fields' => array() FIELDs to select;
         * 'order_by' => array(
         * 'col' => 'content_published_time',
         * 'order' => 'DESC'
         * ),
         * 'limit' => array(
         * 'start' => 0,
         * 'count' => 20
         * )
         * meta = array(
            'hot_news' => 1
         * )
         *  'archive_date' =
         *  'include_child' = true | false : if true, all child of this content will be returned, false by default
         *  'include_tag' = true | false : if true, all tags of this content will be returned, false by default
         *  'include_meta' = true | false : if true, all tags of this content will be returned, false by default
         * );
         */
        global $devdb, $_config;

        if (!$param['status']) $param['status'] = 'published';
        $param['single'] = isset($param['single']) ? $param['single'] : false;
        $content_types = $_config['content_types'];

        $contents = array();

        $sql = "SELECT ".($param['select_fields'] ? implode(',',$param['select_fields']) : '*')." FROM dev_contents WHERE 1 ";
        $count_sql = "SELECT COUNT(pk_content_id) AS TOTAL FROM dev_contents WHERE 1 ";
        $condition = '';

        if($param['not_content_types']) $param['NOT']['content_types'] = $param['not_content_types'];
        if($param['ignore_content_id']) $param['NOT']['content_id'] = $param['ignore_content_id'];

        if(!isset($param['ignore_parent'])){
            if(!isset($param['parent_content_id'])) $param['parent_content_id'] = 0;
            }
        elseif(isset($param['parent_content_id'])) unset($param['parent_content_id']);

        if(isset($param['only_parent'])) $param['parent_content_id'] = 0;

        if($param['archive_date']) $param['BETWEEN_INCLUSIVE'] = array('publish_time' => array('left' => $param['archive_date'], 'right' => $param['archive_date'].' 23:59:59'));
        if($param['published_so_far']) $param['LESS_THAN_EQUAL'] = array('publish_time' => $param['published_so_far'].' 23:59:59');
        if($param['published_till_now']) $param['LESS_THAN_EQUAL'] = array('publish_time' => date('Y-m-d H:i:s'));
        if($param['yearwise']) $param['BETWEEN_INCLUSIVE'] = array('publish_time' => array('left' => $param['yearwise'].'-01-01', 'right' => $param['yearwise'].'-12-31 23:59:59'));

        $loop_condition = array(
            'content_slug' => 'dev_contents.content_slug',
            'content_types' => 'dev_contents.fk_content_type_id',
            'status' => 'dev_contents.content_status',
            'content_id' => 'dev_contents.pk_content_id',
            'page_id' => 'dev_contents.fk_page_id',
            'user_id' => 'dev_contents.created_by',
            'parent_content_id' => 'dev_contents.fk_content_id',
            'publish_time' => 'dev_contents.content_published_time',
            );

        if ($param['q']) {
            if($param['full_string'] == true){
                if($param['q_only_title'])
                    $condition .= " AND content_title LIKE '%".$param['q']."%'";
                else
                    $condition .= " AND ( content_title LIKE '%".$param['q']."%' OR content_description LIKE '%".$param['q']."%')";
                }
            else{
                $s_words = explode(' ', $param['q']);

                foreach ($s_words as $i => $v) {
                    if($param['q_only_title'])
                        $s_words[$i] = "content_title LIKE '%" . $v . "%'";
                    else
                        $s_words[$i] = "content_title LIKE '%" . $v . "%' OR content_description LIKE '%" . $v . "%'";
                    }
                $condition .= " AND (" . implode(' OR ', $s_words) . ")";
                }
            }

        if (is_array($param['tag_id']))
            $condition .= " AND dev_contents.pk_content_id IN (SELECT fk_content_id FROM dev_content_tag_relation WHERE content_type = 'content' AND tag_type = 'tag' AND fk_tag_id IN ('" . implode("','", $param['tag_id']) . "'))";
        elseif ($param['tag_id'])
            $condition .= " AND dev_contents.pk_content_id IN (SELECT fk_content_id FROM dev_content_tag_relation WHERE content_type = 'content' AND tag_type = 'tag' AND fk_tag_id = '" . $param['tag_id'] . "')";

        if (is_array($param['category_id']))
            $condition .= " AND dev_contents.pk_content_id IN (SELECT fk_content_id FROM dev_content_tag_relation WHERE content_type = 'content' AND tag_type = 'tag' AND fk_tag_id IN ('" . implode("','", $param['category_id']) . "'))";
        elseif ($param['category_id'])
            $condition .= " AND dev_contents.pk_content_id IN (SELECT fk_content_id FROM dev_content_tag_relation WHERE content_type = 'content' AND tag_type = 'category' AND fk_tag_id = '" . $param['category_id'] . "')";

        if($param['next_content'] && $param['current_content_id'])
            $condition .= " AND (pk_content_id IN (SELECT pk_content_id FROM dev_contents WHERE pk_content_id > '".$param['current_content_id']."' ORDER BY content_published_time ASC)) ";

        if($param['previous_content'] && $param['current_content_id'])
            $condition .= " AND (pk_content_id IN (SELECT pk_content_id FROM dev_contents WHERE pk_content_id < '".$param['current_content_id']."' ORDER BY content_published_time DESC)) ";

        $order_by = sql_order_by($param,'dev_contents.content_published_time','DESC');
        $limit_by = sql_limit_by($param);

        if($param['meta'])
            $condition .= ' AND pk_content_id IN ('.$this->search_content_meta($param, true).')';

        $condition .= sql_condition_maker($loop_condition, $param);

        $sql .= $condition.$order_by.$limit_by;
        $count_sql .= $condition;

        $contents['sql'] = $sql;

        //pre($param);
        //Caching Part
        $cacheID = null;
        $cacheGroup = null;
        $content_id = null;
        if($param['content_id']){
            if(is_array($param['content_id'])){
                if(count($param['content_id']) == 1) $content_id = $param['content_id'][0];
                }
            else $content_id = $param['content_id'];
            }

        if($content_id) $cacheID = 'each_content_'.$content_id;
        else{
            /*$cacheID = 'contents_'.hash('sha512',$sql);
            if($param['page_id']){
                if(is_array($param['page_id'])){
                    if(count($param['page_id']) == 1) $cacheGroup = 'page_'.$param['page_id'][0];
                    else $cacheGroup = 'comboPages';
                    }
                else $cacheGroup = 'page_'.$param['page_id'];
                }
            else $cacheGroup = 'comboPages';*/
            }
        if ($param['single']) {
            $contents = getCache($cacheID);
            if($param['skipCache'] || !hasCache($contents)){
                $contents = sql_data_collector($sql,$count_sql,$param);
                if ($contents) {
                    $contents['content_title_html'] = $contents['content_title'];
                    $contents['content_title'] = strip_tags($contents['content_title']);
                    $contents['content_description'] = stripcslashes(remove_rn($contents['content_description']));
                    $contents['content_excerpt'] = remove_rn($contents['content_excerpt']);
                    }
                if(!isset($param['skipCache'])){
                    if($cacheID && $cacheGroup) setCache($contents,$cacheID, $cacheGroup);
                    else if($cacheID) setCache($contents,$cacheID );
                    }
                }
            }
        else {
            $contents = false;
            if(!$param['skipCache']){
                if($cacheID && $cacheGroup) $contents = getCache($cacheID, $cacheGroup);
                else if($cacheID) $contents = getCache($cacheID);
                }
            if(!hasCache($contents)){
                $contents = sql_data_collector($sql,$count_sql,$param);
                if (isset($contents['data']) && $contents['data']) {
                    foreach ($contents['data'] as $i => $item) {
                        $contents['data'][$i]['content_title_html'] = $contents['data'][$i]['content_title'];
                        $contents['data'][$i]['content_title'] = strip_tags($contents['data'][$i]['content_title']);
                        $contents['data'][$i]['content_description'] = stripslashes(remove_rn($item['content_description']));
                        $contents['data'][$i]['content_excerpt'] = remove_rn($item['content_excerpt']);
                        }

                    //$contents['total'] = $devdb->get_row($count_sql);
                    //$contents['total'] = $contents['total']['TOTAL'];
                    }

                if(!isset($param['skipCache'])){
                    if($cacheID && $cacheGroup) setCache($contents,$cacheID, $cacheGroup);
                    else if($cacheID) setCache($contents,$cacheID );
                    }
                }
            }



        if($contents && $param['include_tag']){
            if($tagManager){
                if(isset($contents['data'])){
                    foreach($contents['data'] as $i=>$v){
                        $contents['data'][$i]['tags'] = $tagManager->get_content_tags($v['pk_content_id'], 'tag');
                        }
                    }
                else
                    $contents['tags'] = $tagManager->get_content_tags($contents['pk_content_id'], 'tag');
                    }
                }
        if($contents && $param['include_category']){
            if($tagManager){
                if(isset($contents['data'])){
                    foreach($contents['data'] as $i=>$v){
                        $contents['data'][$i]['categories'] = $tagManager->get_content_tags($v['pk_content_id'], 'category');
                        }
                    }
                else
                    $contents['categories'] = $tagManager->get_content_tags($contents['pk_content_id'], 'category');
                }
            }
        if ($contents && $param['include_child'] == true) {
            $this->process_include_child($contents,$param);
            }
        if ($contents && $param['include_meta'] == true) {
            $this->process_include_meta($contents,$param);
            }
        if($contents && $param['include_file'] == true){
            $this->process_include_files($contents,$param);
            }

        return $contents;
        }

    function get_content_files($param){
        /***
         * SELECT sql
         * WHERE condition
         * content_id => array(1,2,..)
         * q => array('any flags',other...)
         */
        global $devdb;

        $sql = "SELECT * FROM dev_content_files";
        $sql_cond = " WHERE 1";

        $condition = '';
        if(is_array($param['content_id']))
            $condition .= " AND fk_content_id IN( '" . implode(',', $param['content_id']) . "')";
        elseif ($param['content_id'])
            $condition .= " AND fk_content_id = '" . $param['content_id'] . "'";

        if ($param['q'])
            $condition .= " AND (file_name LIKE '%" . $param['q'] . "%' OR file_title LIKE '%".$param['q']."%')";

        $sql .= $sql_cond . $condition;

        if($param['single'])
            $data = $devdb->get_row($sql);
        else
            $data['data'] = $devdb->get_results($sql);

        return $data;
        }

    function process_include_files(&$content,$param){
        if (isset($content['data'])) {
            if(!$content['data']) return null;
            foreach ($content['data'] as $i => $v) {
                $cacheID = 'each_content_files_'.$v['pk_content_id'];
                $files = getCache($cacheID, 'content');

                if(!hasCache($files)){
                    $args = array('content_id' => $v['pk_content_id']);
                    $files = $this->get_content_files($args);
                    $files = $files['data'];
                    if(!isset($param['skipCache'])) setCache($files, $cacheID, 'content');
                    }
                $content['data'][$i]['files'] = $files;
                }
            }
        elseif($content){
            $cacheID = 'each_content_files_'.$content['pk_content_id'];
            $files = getCache($cacheID, 'content');
            if(!hasCache($files)){
                $args = array('content_id' => $content['pk_content_id']);
                $files = $this->get_content_files($args);
                $files = $files['data'];
                if(!isset($param['skipCache'])) setCache($files, $cacheID, 'content');
                }
            $content['files'] = $files;
            }
        }

    function get_content_meta($param=array()){
        return meta_manager::get_metas(array(
            'fk' => $param['content_id'],
            'type' => 'content',
            ));
        }

    function process_include_child(&$content, $param){
        $parents = array();
        if(isset($content['data'])){
            if(!$content['data']) return null;
            foreach ($content['data'] as $i => $v) {
                $cacheID = 'each_content_childs_'.$v['pk_content_id'];
                $childs = getCache($cacheID,'content');

                if(!hasCache($childs)){
                    $args = array(
                        'parent_content_id' => $v['pk_content_id'],
                        //'include_child' => true,
                        'data_only' => true,
                        'order_by' => array(
                            'col' => 'pk_content_id',
                            'order' => 'ASC'
                            ),
                        );

                    $childs = $this->get_contents($args);
                    $childs = $childs['data'];
                    if(!isset($param['skipCache'])) setCache($childs, $cacheID, 'content');
                    //setCache($childs, $cacheID, 'content');
                    }

                $content['data'][$i]['childs'] = $childs;
                }
            }
        elseif($content) {
            $cacheID = 'each_content_childs_'.$content['pk_content_id'];
            $childs = getCache($cacheID,'content');
            if(!hasCache($childs)){
                $args = array(
                    'parent_content_id' => $content['pk_content_id'],
                    //'include_child' => true,
                    'order_by' => array(
                        'col' => 'pk_content_id',
                        'order' => 'ASC'
                        ),
                    );
                $childs = $this->get_contents($args);
                $childs = $childs['data'];
                if(!isset($param['skipCache'])) setCache($childs, $cacheID, 'content');
                //setCache($childs, $cacheID, 'content');
                }

            $content['childs'] = $childs;
            }
        }

    function process_include_meta(&$content){
        if (isset($content['data'])) {
            if(!$content['data']) return null;
            foreach ($content['data'] as $i => $v) {
                $content['data'][$i]['meta'] = meta_manager::get_metas(array(
                    'fk' => $v['pk_content_id'],
                    'type' => 'content',
                    ));
                }
            }
        elseif($content){
            $content['meta'] = meta_manager::get_metas(array(
                'fk' => $content['pk_content_id'],
                'type' => 'content',
                ));
            }
        }

    function reCacheContent($content_id, $archive = true, $tags = true, $childs = true, $meta = true, $content = true, $combo = true, $page = true){
        //cleaning caches
        if($archive) removeCache('lowest_archive_date');
        if($tags) removeCache('each_content_tag_'.$content_id.'_content_tag', 'content');
        if($tags) removeCache('each_content_tag_'.$content_id.'_content_category', 'content');
        if($childs) removeCache('each_content_childs_'.$content_id, 'content');
        if($meta) removeCache('meta_set_'.$content_id.'_content', 'meta');
        if($content) removeCache('each_content_'.$content_id);
        if($combo) cleanCache('comboPages');
        //pre('s');
        //---------
        $args = array(
            'content_id' => $content_id,
            'single' => true,
            'status' => array('pending','draft','published'),
            'skipCache' => true,
            );
        $the_content = $this->get_contents($args);
        //--------
        if($page) cleanCache('page_'.$the_content['fk_page_id']);
        }

    function search_content_meta($args = array(), $return_sql = false){
        return meta_manager::search_metas(array(
            'fk_table_name' => 'dev_contents',
            'fk_primary_key' => 'pk_content_id',
            'fk_type' => 'content',
            'meta' => $args['meta'],
            'join_metas_with' => $args['join_metas_with'] ? $args['join_metas_with'] : null,
            ), $return_sql);
        }

    function put_content_meta($args=array()){
        meta_manager::put_metas('content', $args['content_id'], $args['post']);
        }

	function delete_contents($id){
		global $devdb;
		
		$sql = "DELETE FROM dev_contents WHERE pk_content_id = '".$id."'";
		$content_deleted = $devdb->query($sql);
		
		if($content_deleted){
			$sql = "DELETE FROM dev_content_tag_relation WHERE fk_content_id = '".$id."' AND content_type = 'content'";
			$tags_deleted = $devdb->query($sql);
			$sql = "DELETE FROM dev_contents WHERE fk_content_id = '".$id."'";
			$childs_deleted = $devdb->query($sql);
            $sql = "DELETE FROM dev_metas WHERE fk_id = '".$id."' AND fk_type = 'content'";
            $meta_deleted = $devdb->query($sql);
            $sql = "DELETE FROM dev_content_files WHERE fk_content_id = '".$id."'";
            $file_deleted = $devdb->query($sql);
			}
		$this->reCacheContent($id);
		return $content_deleted;
		}

    function related_posts($args = array()){
        $tagManager = jack_obj('dev_tag_management');
        /*
        $args = array(
            'max_posts' => 10,
            'the_post' => Full array of the primary content with which other contents relate
            );
        content_types = array or single | you can specify the types of contents to include, otherwise the contents of content type of the post will be considered only.
        content_types_ids = array or single | you can specify the types of contents to include, otherwise the contents of content type of the post will be considered only.
        */

        if(!$args['max_posts']) $args['max_posts'] = 10;

        if(!$args['the_post']) return null;

        $post_tags = array();

        if($tagManager){
            $tags_of_this_post = $tagManager->get_content_tags($args['the_post']['pk_content_id']);
            foreach($tags_of_this_post as $i=>$v){
                $post_tags[] = $i;
                }
            }

        $params = array(
            'tag_id' => $post_tags ? $post_tags : null,
            'status' => 'published',
            'ignore_content_id' => $args['the_post']['pk_content_id'],
            'page_id' => $args['page_id'] ? $args['page_id'] : null,
            'limit' => array(
                'start' => 0,
                'count' => $args['max_posts'],
                ),
            'order_by' => array(
                'col' => 'dev_contents.content_view_count',
                'order' => 'desc'
                ),
            );

        if($args['content_types']) $params['content_types'] = $args['content_types'];
        if($args['content_types_ids']) $params['content_types_ids'] = $args['content_types_ids'];

        if(!$args['content_types_ids'] && !$args['content_types']) $params['content_types_ids'] = $args['the_post']['fk_content_type_id'];

        $contents = $this->get_contents($params);
        $contents = $contents['data'];

        $remaining_posts = $args['max_posts'] - $contents['total'];

        if($remaining_posts){
            $params = array(
                'status' => 'published',
                'ignore_content_id' => $args['the_post']['pk_content_id'],
                'page_id' => $args['page_id'] ? $args['page_id'] : null,
                'limit' => array(
                    'start' => 0,
                    'count' => $remaining_posts,
                    ),
                'order_by' => array(
                    'col' => 'dev_contents.content_view_count',
                    'order' => 'desc'
                    ),
                );

            if($args['content_types']) $params['content_types'] = $args['content_types'];
            if($args['content_types_ids']) $params['content_types_ids'] = $args['content_types_ids'];

            if(!$args['content_types_ids'] && !$args['content_types']) $params['content_types_ids'] = $args['the_post']['fk_content_type_id'];

            $contents2 =$this->get_contents($params);
            $contents2 = $contents2['data'];
            if($contents2){
                foreach($contents2 as $i=>$v){
                    $contents[$i] = $v;
                    }
                }
            }

        return $contents;
        }
	}
new dev_content_management;

function getMetaValue($content, $metaName){
    if($content && $content['meta'] && $content['meta'][$metaName]) return $content['meta'][$metaName]['meta_value'];
    else return null;
    }

function is_content_owner($args = array()){
    global $devdb;

    $sql = "SELECT pk_content_id FROM dev_contents WHERE created_by = '".$args['user_id']."' AND pk_content_id = '".$args['content_id']."'";
    $ok = $devdb->get_row($sql);

    if($ok['pk_content_id']) return true;
    else return false;
    }

function content_id_of_user($user, $condition = '1'){
    global $devdb;

    $sql = "SELECT pk_content_id FROM dev_contents WHERE created_by = '".$user."' AND ".$condition;
    $ids = $devdb->get_results($sql);

    $ret = array();
    foreach($ids as $i=>$v){
        array_push($ret,$v['pk_content_id']);
        }

    return $ret;
    }

function get_content_default_image(){
    return theme_path().'/images/blog_post_default.jpg';
    }
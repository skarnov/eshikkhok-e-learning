<?php
class dev_administration{
    var $thsClass = 'dev_administration';
	public function __construct(){
        jack_register($this);
		}
    function init(){
        $permissions = array(
            'group_name' => 'Administration',
            'permissions' => array(
                'manage_user_activities' => array(
                    'flush_user_activities' => 'Flush User Activities',
                    ),
                'manage_system_settings' => 'Manage System Settings',
                'manage_api' => 'View system APIs',
                'access_to_dashboard' => 'Access to Dashboard'
                ),
            );

        apiRegister($this,'autocomplete_handler');
        apiRegister($this,'has_api_permission');
        apiRegister($this,'update_api_settings');
        apiRegister($this,'adminWidgetRefresh');
        apiRegister($this,'get_converted_currency');


        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();
            }
        }
    function get_converted_currency($param = array()){
        if(isset($param['post'])) $param = array_merge($param,$param['post']);
        if(isset($param['get'])) $param = array_merge($param,$param['get']);

        $converted = convert_currency($param['from'], $param['to'], $param['amount']);

        return array('success' => $converted);
        }
    function insertUpdateSingleAdminSetting($param = array()){
        $setting = $param['setting'];
        $value = $param['value'];

        global $devdb, $_config;

        $sql = "SELECT * FROM dev_config WHERE config_name = '".$setting."'";
        $data = $devdb->get_row($sql);
        $insertData = array(
            'config_value' => $value,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_config['user']['pk_user_id']
            );
        if($data){
            $ret = $devdb->insert_update('dev_config',$insertData," config_id = '".$data['config_id']."'");
            }
        else{
            $insertData['config_name'] = $setting;
            $ret = $devdb->insert_update('dev_config',$insertData);
            }
        if($ret['success']) removeCache('devConfig');
        return $ret;
        }
    /**
     * Runs to register the jack in the Admin menu
     */
    function adm_menus(){
        $params = array(
            'label' => 'User Activities',
            'description' => 'Manage User Activities',
            'menu_group' => 'Administration',
            'action' => 'manage_user_activities',
            'iconClass' => 'fa-tasks',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_user_activities')) admenu_register($params);
        $params = array(
            'label' => 'System Settings',
            'description' => 'Manage System Settings',
            'menu_group' => 'Administration',
            'action' => 'manage_system_settings',
            'iconClass' => 'fa-cogs',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_system_settings')) admenu_register($params);

        $params = array(
            'label' => 'Manage APIs',
            'description' => 'All APIs Listed in a table',
            'menu_group' => 'Administration',
            'action' => 'manage_api',
            'iconClass' => 'fa-cogs',
            'jack' => $this->thsClass,
            );
        //if(has_permission('manage_api')) admenu_register($params);
        }
    function adminWidgetRefresh(){
        $args = $_POST['args'];
        if(isset($args['widget']) && $args['widget']){
            global $adminWidgets;
            return $adminWidgets->refresh_widget($args['widget'], $args);
            }

        return null;
        }
    function site_settings_widget(){
        ?>
        <style>
            .stat_title{
                height: 116px;
            }
            a:hover{
                background-color: #676767;
                color: #FFFFFF;
                }
            .each_row{
                height: 46px;
            }
            .comments .stat_title{
                width: 246px;
            }
            .each_comment{
                height: 95px;
            }
            .each_comment .cont_title{
                text-overflow: ellipsis;
                width: 112px;
                overflow: hidden;
                white-space: nowrap;
                display: inline-block;
            }
        </style>
        <div class="col-sm-12">
            <div class="stat-panel">
                <div class="stat-row">
                    <div class="stat-cell stat_title">
                        <i class="fa fa-cogs bg-icon" style="font-size:60px;line-height:80px;height:80px;"></i>
                        <span class="text-bg">Shortcut to Site Settings&nbsp;<i class="fa fa-cogs"></i></span><br>
                        <hr/>
                    </div>

                </div>
                <div class="stat-row ">
                    <div class="stat-counters each_row no-border-b no-padding text-center">
                        <a href="<?php echo url('/admin/dev_menu_management/manage_menus')?>" class="stat-cell col-xs-4 padding-sm no-padding-hr">
                            <span class="text-xs"><i class="fa fa-bars"></i>&nbsp;MENUS</span>
                        </a>
                        <a href="<?php echo url('/admin/dev_page_management/manage_pages')?>" class="stat-cell col-xs-4 padding-sm no-padding-hr">
                            <span class="text-xs"><i class="fa fa-file-text-o"></i>&nbsp;PAGES</span>
                        </a>
                        <a href="<?php echo url('/admin/dev_inventory_management/manage_inventory')?>" class="stat-cell col-xs-4 padding-sm no-padding-hr">
                            <span class="text-xs"><i class="fa fa-gift"></i>&nbsp;INVENTORY</span>
                        </a>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-counters each_row no-border-b no-padding text-center">
                        <a href="<?php echo url('/admin/dev_report_management/manage_reports?action=generate_report&report=SalesReport')?>" class="stat-cell col-xs-4 padding-sm no-padding-hr">
                            <span class="text-xs"><i class="fa fa-bar-chart-o"></i>&nbsp;SALES REPORT</span>
                        </a>
                        <a href="<?php echo url('/admin/dev_sales_management/manage_sales')?>" class="stat-cell col-xs-4 padding-sm no-padding-hr">
                            <span class="text-xs"><i class="fa fa-file-text-o"></i>&nbsp;INVOICES</span>
                        </a>
                        <a href="<?php echo url('/admin/dev_gallery_management/manage_gallery')?>" class="stat-cell col-xs-4 padding-sm no-padding-hr">
                            <span class="text-xs"><i class="fa fa-film"></i>&nbsp;GALLERY</span>
                        </a>
                        <a href="<?php echo url('/admin/dev_offer_management/manage_offers')?>" class="stat-cell col-xs-4 padding-sm no-padding-hr">
                            <span class="text-xs"><i class="fa fa-gift"></i>&nbsp;OFFERS</span>
                        </a>

                    </div>
                </div>
            </div>
        </div>
        <!--div class="col-sm-6 pl0">

            <div class="stat-panel">
                <div class="stat-row comments">

                    <div class="stat-cell head stat_title bg-color-green darker">

                        <i class="fa fa-comments bg-icon" style="font-size:60px;line-height:80px;height:80px;"></i>

                        <span class="text-bg">Latest Comments</span><br>
                        <hr/>

                    </div>

                </div>
                <div class="stat-row">
                    <div class="p0 stat-cell bg-color-green">
                        <?php
                        $contentManagement = jack_obj('dev_content_management');
                        foreach($comments as $i=>$v){
                            $args = array(
                                'content_id' => $v['fk_parent_content_id'],
                                'single' => true,
                                );
                            $content_title = $contentManagement->get_contents($args);
                            ?>

                            <div class="each_comment table table-bordered pl20 comment_widget" style="display: block;
    padding-right: 27px;border-radius: 37px;">
                                <p class="fr cont_title"><?php echo $content_title['content_title']?></p>
                                <h2 style="font-size:18px;"><strong><?php echo $v['comment_name']?></strong></h2>
                                <p><?php echo $v['comment_body']?></p>
                                <h4 style="font-size:10px;"><?php echo print_date($v['created_at'])?></h4>
                            </div>
                        <?php
                        }
                        ?>
                        <a class="comment_previous btn btn-xs btn-labeled btn-outline left btn-success fl"><span class="icon fa fa-arrow-left"></span></a>
                        <a class="comment_next btn btn-xs btn-labeled btn-outline right btn-success fr"><span class="icon fa fa-arrow-right"></span></a>
                        <script type="text/javascript">
                            init.push(function(){
                                //initially keep 1st content and hide others.
                                $('.each_comment').hide().eq(0).show();

                                function next_comment(){
                                    var $cur = $('.each_comment:visible').index();
                                    $('.each_comment').eq($cur).hide();
                                    $cur = ++$cur >= $('.each_comment').length ? 0 : $cur;
                                    $('.each_comment').eq($cur).show();
                                }
                                function previous_comment(){
                                    var $cur = $('.each_comment:visible').index();
                                    $('.each_comment').eq($cur).hide();
                                    $cur = --$cur < 0 ? $('.each_comment').length - 1 : $cur;
                                    $('.each_comment').eq($cur).show();
                                }
                                //setInterval(function(){next_comment()},3000);
                                $('.comment_previous').click(function(){previous_comment()});
                                $('.comment_next').click(function(){next_comment()});
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>-->
        <?php
        }
    function autocomplete_handler(){
        global $devdb;

        $param = array_replace_recursive($_POST, $_GET);

        if($param['_data']['ajax_type'] == 'content_autocomplete'){

            $data = $param['_data'];

            $term = $param['term'];

            if($data['api_call']){
                $args = array();

                if(is_array($data['api_call']['param'])){
                    foreach($data['api_call']['param'] as $i=>$v){
                        $args[$i] = $v;
                        }
                    }

                $args[$data['api_call']['term_param']] = $term;

                $jackObject = jack_obj($data['api_call']['jack']);
                $jackFunc = $data['api_call']['function'];
                $query = $jackObject->$jackFunc($args);

                if(isset($query['data'])) $query = $query['data'];
                }
            else{
                $sql = "SELECT ".$data['id'].', '.$data['label'].($data['role'] ? ','.$data['role'] : '').($data['role_type'] ? ','.$data['role_type'] : '').' FROM '.$data['from'].' WHERE 1 ';

                foreach($data['select_fields'] as $i=>$v){
                    $sql .= $v . ', ';
                    }

                $sql = rtrim($sql.', ');

                $sql .= ' FROM '.$data['from'].' WHERE 1 ';

                if($data['condition']) $sql .= $data['condition'];

                $compare = '';

                if($data['compare']){
                    foreach($data['compare'] as $i=>$v){
                        $data['compare'][$i] = $v." LIKE '%".$term."%'";
                        }
                    $sql .= ' AND ('.implode(' OR ', $data['compare']).')';
                    }

                $sql = stripslashes($sql);

                $query = $devdb->get_results($sql);
                }

            $i = 0;

            if(is_array($query)) {
                foreach ($query as $c) {
                    $json[$i] = array(
                        'label' => $c[$data['label']],
                        'id' => $c[$data['id']]

                        );
                    if($json[$i]['role']) $json[$i]['role']=$c[$data['role']];
                    if($json[$i]['role_type']) $json[$i]['role_type']=$c[$data['role_type']];
                    if(is_array($data['select_fields'])){
                        foreach ($data['select_fields'] as $m => $v) {
                            $json[$i][$v] = $c[$v];
                            }
                        }
                    else{
                        foreach ($c as $m => $v) {
                            $json[$i][$m] = $v;
                            }
                        }
                    $i++;
                    }
                }
            return $json;
            }
        else return null;
        }
    function manage_api(){
        return null;
        if(!has_permission('manage_api')) return;

        global $devdb,$_config;

        $myUrl = jack_url($this->thsClass, 'manage_api');
        include('pages/manage_apis.php');
        }
	function manage_user_activities(){
        if(!has_permission('manage_user_activities')) return;
		global $devdb, $_config, $paths;
		$myUrl = jack_url($this->thsClass, 'manage_user_activities');
		
		include('pages/list_user_activities.php');
		}
    function manage_system_settings(){
        if(!has_permission('manage_system_settings')) return;
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_system_settings');

        include('pages/admin_settings.php');
        }
    function has_api_permission($api_name=null,$permission=null,$single = true){
        global $devdb;
        $sql = "SELECT * FROM dev_api_settings WHERE 1";
        if($api_name){
            $api_cond_sql = " AND the_api='".$api_name."'";
            }
        if($permission){
            $permission_cond_sql = " AND '".$permission."' = '1'";
            }
        $sql = $sql.$api_cond_sql.$permission_cond_sql;
        if($single)
            $data = $devdb->get_row($sql);
        else
            $data = $devdb->get_results($sql);
        return $data;
        }
    function update_api_settings(){
        global $devdb,$_config;
        $api_name = $_POST['api'];
        $permission = $_POST['permission'];
        $api = $devdb->get_row("SELECT * FROM dev_api_settings WHERE the_api = '".$api_name."'");
        if($api){
            $sql = "UPDATE dev_api_settings SET $permission = '".($_POST['data'] == 'false' ? 0 : 1)."' WHERE the_api = '".$api_name."'";
            $update_status = $devdb->query($sql);
            }
        else{
            $insert_data = array(
                'the_api' => $api_name,
                'access_from_another_jack' => $permission=='access_from_another_jack' ? ($_POST['data'] == 'false' ? '0' : '1') : '0',
                'access_from_another_jack' => $permission=='access_from_another_jack' ? ($_POST['data'] == 'false' ? '0' : '1') : '0',
                'access_from_ajax' => $permission=='access_from_ajax' ? ($_POST['data'] == 'false' ? '0' : '1') : '0',
                'access_from_public' => $permission=='access_from_public' ? ($_POST['data'] == 'false' ? '0' : '1') : '0',
                'created_by' => $_config['user']['pk_user_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'modified_by' => $_config['user']['pk_user_id'],
                'modified_at' => date('Y-m-d H:i:s')
                );
            $update_status = $devdb->insert_update('dev_api_settings',$insert_data);
            }
        return $update_status;
        }
	}
new dev_administration;
<?php
class dev_email_template_manager{
    var $thsClass = 'dev_email_template_manager';
    //Email templates from DB
    var $email_templates = array();

    function __construct(){
        jack_register($this);
        }

    function init(){
        global $devdb;

        apiRegister($this,'get_email_template');
        apiRegister($this,'set_email_template');
        apiRegister($this,'get_email_output');
        apiRegister($this,'sendEmailthroughID');
        apiRegister($this,'reset_template_to_default');
        apiRegister($this,'reorder_email_templates');


        $permissions = array(
            'group_name' => 'Administration',
            'permissions' => array(
                'manage_email_template_manager' => 'Manage Email Templates',
                ),
            );
        register_permission($permissions);

        $this->adm_menus();
        }
    function pullEmailTemplates(){
        global $devdb;

        $sql = "SELECT * FROM dev_email_templates ORDER BY template_order ASC";
        $data = $devdb->get_results($sql);

        if($data){
            foreach($data as $i=>$v){
                if(isset($this->email_templates[$v['template_id']])){
                    $e = $this->email_templates[$v['template_id']];
                    $e->saved_source = $v['user_code'];
                    $e->sortOrder = $v['template_order'];
                    $e->pk_etemplate_id = $v['pk_etemplate_id'];
                    if($e->saved_source) $e->email_body = $e->saved_source;
                    }
                }
            }
        }

    function adm_menus(){
        $params = array(
            'label' => 'Email Templates',
            'description' => 'Manage Email Templates',
            'menu_group' => 'administration',
            'action' => 'manage_email_template_manager',
            'iconClass' => 'fa-envelope',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_email_template_manager')) admenu_register($params);
        }

    function manage_email_template_manager(){
        global $devdb,$_config;
        $myUrl = jack_url($this->thsClass, 'manage_email_template_manager');
        include('pages/manage_email_templates.php');
        }

    function registerEmailTemplates($template_name, $template){
        //$emailHeader = function_exists('EmailTemplateHeader') ? EmailTemplateHeader() : '';
        //$emailFooter = function_exists('EmailTemplateFooter') ? EmailTemplateFooter() : '';
        $template->email_body = $template->source;
        $this->email_templates[$template_name] = $template;
        }

    function reorder_email_templates(){
        global $_config, $devdb;
        if($_POST['emailTemplates']){
            foreach($_POST['emailTemplates'] as $i=>$v){
                $updateData = array(
                    'template_order' => $i,
                    'modified_by' => $_config['user']['pk_user_id'],
                    'modified_at' => date('Y-m-d H:i:s'),
                    );
                $updateNow = $devdb->insert_update('dev_email_templates',$updateData," pk_etemplate_id = '".$v."'");
                }
            }
        return array('success' => count($_POST['emailTemplates']));
        }

    function get_email_template($args = array()){
        global $devdb;

        $posted_data = $args['post'] ? $args['post'] : array();
        $args = array_merge($args, $posted_data);

        $args['single'] = $args['single'] ? $args['single'] : false;

        $sql = "SELECT * FROM dev_email_templates WHERE 1";

        if(is_array($args['template_id'])){
            $cond = " AND template_id IN ('".implode("','",$args['template_id'])."')";
            }
        elseif($args['template_id']){
            $cond = " AND template_id = '".$args['template_id']."'";
            }
        $sql = $sql.$cond;

        //$data['sql'] = $sql;
        if($args['single']) $data = $devdb->get_row($sql);
        else $data['data'] = $devdb->get_results($sql,'template_id');

        return $data;
        }

    function set_email_template($args=array()){
        global $devdb,$_config;
        $posted_data = $args['post'] ? $args['post'] : array();
        $args = array_merge($args, $posted_data);

        if(!has_permission('manage_email_template_manager')) return array('error' => array('You do not have permission to update email templates.'));

        $is_update = $args['pk_id'] ? $args['pk_id'] : null;

        $insert_data = array(
            'template_id' => $args['template_id'],
            'user_code' => $args['user_code'],
            'modified_by' => $_config['user']['pk_user_id'],
            'modified_at' => date('Y-m-d H:i:s'),
            );
        if($is_update)
            $insertion = $devdb->insert_update('dev_email_templates',$insert_data," pk_etemplate_id = '$is_update'");
        else $insertion = $devdb->insert_update('dev_email_templates',$insert_data);

        if($insertion['success']){
            user_activity::add_activity('Email Template (ID: '.$args['template_id'].') has been updated.','success','update');
            }

        return $insertion;
        }

    function reset_template_to_default($param=array()){
        global $devdb;
        $posted_data = $param['post'] ? $param['post'] : array();
        $url_data = $param['get'] ? $param['get'] : array();

        $param = array_merge($posted_data,$url_data,$param);

        $delete_sql = "DELETE FROM dev_email_templates WHERE template_id='".$param['template_id']."'";
        $delete = $devdb->query($delete_sql);
        if($delete)
            return array('success'=>'Template body restored to default');
        else
            return array('error' => 'Error Restoring Template body!');
        }
    }
new dev_email_template_manager();
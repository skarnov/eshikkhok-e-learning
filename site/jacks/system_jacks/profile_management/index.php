<?php
class dev_profile_management{
    var $thsClass = 'dev_profile_management';
	function __construct(){
		jack_register($this);
		}

	function init(){
        $permissions = array(
            'group_name' => 'Administration',
            'permissions' => array(
                'manage_users' => array(
                    'add_user' => 'Add Users',
                    'edit_user' => 'Edit Users',
                    ),
                ),
            );

        apiRegister($this,'get_user_auto_complete');
        apiRegister($this,'get_users');
        apiRegister($this,'add_edit_user');
        apiRegister($this,'has_user');

        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();
            }
        }
    function get_user_auto_complete($param = array()){
        global $devdb;

        $param = array_replace_recursive($param, $_POST, $_GET);

        $options = $param['_data'];
        $term = $param['term'];

        $sql = "SELECT 
                    pk_user_id AS id, 
                    user_fullname AS `value`, 
                    user_fullname AS label 
                FROM dev_users 
                WHERE user_meta_type != 'role'";

        if($param['term']) $sql .= " AND user_fullname LIKE '%".$term."%'";
        if($param['user_id']) $sql .= " AND pk_user_id = '".$param['user_id']."'";

        $sql .= " LIMIT 0, 200";

        $output = $devdb->get_results($sql);

        return $output;
        }
    function adm_menus(){

        $args = array(
            'menu_group' => 'my account',
            'menu_icon' => 'fa-user',
            );
        admin_menu_group($args);

        $params = array(
            'label' => 'Users',
            'description' => 'Manage all users',
            'menu_group' => 'Administration',
            'action' => 'manage_users',
            'iconClass' => 'fa-users',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_users')) admenu_register($params);

        $params = array(
            'label' => 'View My Account',
            'description' => 'Manage My Account',
            'menu_group' => 'My Account',
            'action' => 'manage_my_account',
            'iconClass' => 'fa-user',
            'jack' => $this->thsClass,
            );
        //admenu_register($params);
        }

    function manage_my_account(){
        return null;
        global $devdb,$_config;
        $myUrl = jack_url($this->thsClass, 'manage_my_account');

        if($_GET['action'] == 'edit_my_account')
            include('pages/edit_my_account.php');
        else
            include('pages/my_account.php');
        }

    function manage_users(){
        if(!has_permission('manage_users')) return;
        global $devdb, $_config;
        $myUrl = jack_url($this->thsClass, 'manage_users');

        if($_GET['action'] == 'add_edit_user')
            include('pages/add_edit_user.php');
        elseif($_GET['action'] == 'view_profile')
            include('pages/view_profile.php');
        else
            include('pages/list_users.php');
        }


    function has_user(){
        if(HAS_USER()) return array('success' => 1);
        else return array('success' => 0);
        }

    function all_user_select_box($selected_user = null, $template = array(), $param = array()){
	    $template = $template ? $template : array('user', 'local_agent', 'foreign_agent', 'sub_agent', 'passenger', 'employee', 'company');
	    $param['meta_type'] = $template;

	    $users = $this->get_users($param);

	    $users = $users['data'];
        $templateCount = count($template);

	    foreach($template as $type){
            echo $templateCount > 1 ? '<optgroup label="'.dbReadableString($type).'">' : '';
            foreach($users as $i=>$v){
                if($v['user_meta_type'] == $type){
                    $selected = $selected_user && $selected_user == $v['pk_user_id'] ? 'selected' : '';
                    echo '<option value="'.$v['pk_user_id'].'" '.$selected.'>'.$v['user_fullname'].'</option>';
                    }
                }
            echo $templateCount > 1 ? '</optgroup>' : '';
            }
        }
    function get_users($param = null){
        /*
        q = text searching crieria for full name and email address
        user_id => can be single or an array of user_ids
        user_name => username
        user_status = user_status | active or inactive
        user_type = admin or public
        limit = array(start,count)
        order_by = array(col,order)
        single = true or false
        */

        $param['single'] = $param['single'] ? $param['single'] : false;
        $param['index_with'] = isset($param['index_with']) ? $param['index_with'] : 'pk_user_id';
        $param['visible'] = isset($param['visible']) ? $param['visible'] : 1;

        global $devdb;

        $select = "SELECT ".($param['select_fields'] ? implode(", ",$param['select_fields'])." " : ' dev_users.* ');
        $from = "FROM dev_users LEFT JOIN dev_users_roles_relation On (dev_users.pk_user_id = dev_users_roles_relation.fk_user_id) ";
        //TODO: we should only join to dev_users_roles_relation table when there is a filter on user_role field

        $where = "WHERE 1 ";
        $conditions = "";

        $count_sql = "SELECT COUNT(pk_user_id) AS TOTAL ".$from.$where;

        $loopCondition = array(
            'user_id' => 'dev_users.pk_user_id',
            'user_fb_id' => 'dev_users.user_fb_id',
            'user_email' => 'dev_users.user_email',
            'user_status' => 'dev_users.user_status',
            'user_type' => 'dev_users.user_type',
            'visible' => 'dev_users.user_is_visible',
            'meta_type' => 'user_meta_type',
            'q' => 'dev_users.user_fullname',
            'user_role' => 'dev_users_roles_relation.fk_role_id',
            );

        /*if($param['user_role'] || $param['user_role']){
            $from .= " LEFT JOIN dev_users_roles_relation On (dev_users.pk_user_id = dev_users_roles_relation.fk_user_id) ";
            }*/

        $conditions .= sql_condition_maker($loopCondition, $param);

        $orderBy = sql_order_by($param);
        $limitBy = sql_limit_by($param);

        $sql = $select.$from.$where.$conditions.$orderBy.$limitBy;
        $count_sql .= $conditions;

        $cacheID = null;
        $cacheGroup = null;
        $user_id = null;

        if($param['user_id']){
            if(is_array($param['user_id'])){
                if(count($param['user_id']) == 1) $user_id = $param['user_id'][0];
                }
            else $user_id = $param['user_id'];
            }

        if($user_id) $cacheID = 'user_'.$user_id;

        if($param['single'] == true){
            $users['data'] = $cacheID ? getCache($cacheID) : false;
            if(!hasCache($users['data'])){
                $users['data'] = sql_data_collector($sql, $count_sql, $param);
                if($users['data']){
                    $users['data']['rel_user_picture'] = user_picture($users['data']['user_picture']);
                    };

                if($cacheID) setCache($users['data'], $cacheID);
                }
            if($param['include_meta']){
                $users['data']['meta'] = $this->get_user_meta($users['data']['pk_user_id']);
                }
            }
        else{
            $users = sql_data_collector($sql, $count_sql, $param);

            if(isset($users['data'])){
                foreach($users['data'] as $i=>$v){
                    $users['data'][$i]['rel_user_picture'] = user_picture($v['user_picture']);
                    if($param['include_meta']){
                        $users['data'][$i]['meta'] = $this->get_user_meta($v['pk_user_id']);
                        }
                    }
                }
            }

        return $users;
        }

    function get_user_meta($user_id){
        return meta_manager::get_metas(array(
            'fk' => $user_id,
            'type' => 'user'
            ));
        }

    function reCacheUser($user_id){
        removeCache('user_'.$user_id);
        removeCache('user_meta_'.$user_id);

        $args = array(
            'user_id' => $user_id,
            'include_meta' => true,
            );
        $user = $this->get_users($args);
        }

    function add_edit_user($args = array()){
        global $devdb, $_config, $paths;
        $roleManager = jack_obj('dev_role_permission_management');
        $ret = array();
        $edit = $args['edit'];

        if($edit){
            $_params = array(
                'user_id' => $edit,
                'single' => true,
                );
            $oldData = $this->get_users($_params);
            $oldData = $oldData['data'];
            }
        else $oldData = array();

        $required_fields = array(
            'user_fullname' => 'Full Name',
            'user_name' => 'User Name',
            'user_email' => 'User Email',
            'user_type' => 'User Type',
            'user_status' => 'User Status',
            );

        foreach($required_fields as $field=>$label){
            if(isset($args[$field])){
                $temp = form_validator::required($args[$field]);
                if($temp !== true) $ret['error'][] = $label.' '.$temp;
                }
            else $ret['error'][] = $label.' is required';
            }

        $checkPassword = !$edit ? true : (!($args['user_password']) ? false : true);

        $temp_username = form_modifiers::userName($args['user_name']);
        if(strcmp($temp_username,$args['user_name'])) $ret['error'][] = 'Username is not acceptable. You can try with <em>'.$temp_username.'</em>, if not already taken.';

        $temp = form_validator::_length($args['user_name'], 250);
        if($temp !== true) $ret['error'][] = 'User Name '.$temp;

        $temp = form_validator::_length($args['user_fullname'], 490);
        if($temp !== true) $ret['error'][] = 'Full Name '.$temp;

        $temp = form_validator::_length($args['user_email'], 390);
        if($temp !== true) $ret['error'][] = 'Email '.$temp;

        $temp = form_validator::_length($args['user_password'], 100);
        if($temp !== true) $ret['error'][] = 'Password '.$temp;

        $temp = form_validator::_length($args['user_religion'], 20);
        if($temp !== true) $ret['error'][] = 'Religion '.$temp;

        $temp = form_validator::_length($args['user_country'], 20);
        if($temp !== true) $ret['error'][] = 'Country '.$temp;

        $temp = form_validator::_length($args['user_mobile'], 45);
        if($temp !== true) $ret['error'][] = 'Mobile Number '.$temp;

        $temp = form_validator::_length($args['user_meta_type'], 45);
        if($temp !== true) $ret['error'][] = 'Meta Type '.$temp;

        $cond = $edit ? " pk_user_id != '".$edit."'" : '';
        $temp = form_validator::unique($args['user_name'],'dev_users','user_name',$cond);
        if($temp !== true) $ret['error'][] = 'User Name is not unique, try another.';

        if($args['user_email']){
            $temp = form_validator::email($args['user_email']);
            if($temp !== true) $ret['error'][] = 'User Email '.$temp;

            $cond = $edit ? " pk_user_id != '".$edit."'" : '';
            $temp = form_validator::unique($args['user_email'],'dev_users','user_email',$cond);
            if($temp !== true) $ret['error'][] = 'Email is not unique, try another.';
            }

        if(!$args['user_fb_id']){
            $temp = form_validator::required($args['user_email']);
            if($temp !== true) $ret['error'][] = 'User Email '.$temp;
            }

        if($checkPassword) {
            $temp = form_validator::password($args['user_password']);
            if ($temp !== true) $ret['error'][] = 'User ' . $temp;
            }
        if($checkPassword) $args['user_password'] = hash_password($args['user_password']);

        if(!$args['user_password']) $args['user_password'] = $oldData['user_password'];

        $temp = form_validator::_in($args['user_type'],array('public','admin'));
        if($temp !== true) $ret['error'][] = 'User Type '.$temp;

        $temp = form_validator::_in($args['user_status'],array('active','inactive','not_verified'));
        if($temp !== true) $ret['error'][] = 'User Status '.$temp;


        if($ret['error']) return $ret;

        if ($_FILES['user_picture']["name"]) {
            $supported_ext = array('jpg','png');
            $max_filesize = 512000;
            $target_dir = $paths['absolute']['profile_pictures']."/";
            if(!file_exists($target_dir)) mkdir($target_dir);
            $target_file = $target_dir . basename($_FILES['user_picture']["name"]);
            $fileinfo = pathinfo($target_file);
            $target_file = $target_dir . str_replace(' ','_',$fileinfo['filename']) .'_'.time().'.'.$fileinfo['extension'];
            $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
            if (in_array(strtolower($imageFileType), $supported_ext)) {
                if ($max_filesize && $_FILES['user_picture']["size"] <= $max_filesize) {
                    if (!move_uploaded_file($_FILES['user_picture']["tmp_name"], $target_file)) {
                        $ret['error'][] = 'User Picture : File was not uploaded, please try again.';
                        $args['user_picture'] = '';
                        }
                    else {
                        $fileinfo = pathinfo($target_file);
                        $args['user_picture'] = $fileinfo['basename'];
                        }
                    }
                else $ret['error'][] = 'User Picture : <strong>'.$_FILES['user_picture']["size"].' B</strong> is more than supported file size <strong>'.$max_filesize.' B';
                }
            else $ret['error'][] = 'User Picture : <strong>.'.$imageFileType.'</strong> is not supported extension. Only supports .'.implode(', .', $supported_ext);
            }

        if(!$ret['error']){
            $insertData = array(
                'user_fb_id' => $args['user_fb_id'],
                'user_fullname' => $args['user_fullname'],
                'user_name' => $args['user_name'],
                'user_description' => $args['user_description'],
                'user_picture' => $args['user_picture'],
                'user_email' => $args['user_email'],
                'user_password' => $args['user_password'],
                'user_gender' => $args['user_gender'],
                'user_country' => $args['user_country'],
                'user_mobile' => $args['user_mobile'],
                'user_type' => $args['user_type'],
                'user_status' => $args['user_status'],
                'user_is_visible' => 1,
                'user_meta_type' => $args['user_meta_type'],
                'user_password_updated' => 1,
                'user_birthdate' => $args['user_birthdate'],
                'modified_at' => date('Y-m-d H:i:s'),
                'modified_by' => $_config['user']['pk_user_id'],
                );

            if($edit) {
                $ret = $devdb->insert_update('dev_users', $insertData, " pk_user_id = '" . $edit . "'");
                $user_id = $edit;
                }
            else {
                $insertData['created_at'] = date('Y-m-d H:i:s');
                $insertData['created_by'] = $_config['user']['pk_user_id'];

                $ret = $devdb->insert_update('dev_users', $insertData);
                }

            if($ret['success']){
                $user_id = $edit ? $edit : $ret['success'];

                //insert/update Meta
                meta_manager::put_metas('user', $user_id, $args['meta']);

                if(isset($args['roles_list']))
                    $roleManager->assign_role($user_id, $args['roles_list']);

                $this->reCacheUser($user_id);
                }
            }

        return $ret;
        }
	}

new dev_profile_management();

function getUserMetaValue($user, $metaName){
    if($user && $user['meta'] && $user['meta'][$metaName]) return $user['meta'][$metaName]['meta_value'];
    else return null;
    }
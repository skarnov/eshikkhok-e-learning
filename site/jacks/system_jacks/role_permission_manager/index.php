<?php
class dev_role_permission_management{
    var $thsClass = 'dev_role_permission_management';
    public function __construct(){
		jack_register($this);
		}
    function init(){
        $permissions = array(
            'group_name' => 'Role &amp; Permission Management',
            'permissions' => array(
                'manage_roles' => 'manage_roles',
                ),
            );

        apiRegister($this,'get_roles');
        apiRegister($this,'get_roles_autocomplete');
        apiRegister($this,'get_role_form');
        apiRegister($this,'set_role_form');

        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();
            }
        }
    function assign_role($user_id, $roles = array()){
        global $devdb;

        if(!$user_id) return array('error' => array('User is required.'));

        $profileManager = jack_obj('dev_profile_management');

        $oldRoles = $this->get_assigned_roles($user_id);

        if($roles && !is_array($roles)) $roles = array($roles);
        else if(!$roles) $roles = array();

        $newRoles = array();
        //find out new roles
        foreach($roles as $i=>$v){
            if(!isset($oldRoles[$v])) $newRoles[] = $v;
            }

        //find out roles which has been removed
        if($oldRoles){
            foreach($oldRoles as $i=>$v){
                if(in_array($i, $roles) === false){
                    $devdb->query("DELETE FROM dev_users_roles_relation WHERE pk_rel_id = '".$v['pk_rel_id']."'");
                    }
                }
            }

        //add new roles
        foreach($newRoles as $i=>$v){
            $devdb->query("INSERT INTO dev_users_roles_relation (fk_user_id, fk_role_id) VALUES('$user_id', '$v')");
            }

        //get all roles as comma separated and update users table
        $devdb->query("UPDATE dev_users SET user_roles = (SELECT GROUP_CONCAT(fk_role_id) FROM dev_users_roles_relation WHERE fk_user_id = '$user_id' GROUP BY dev_users_roles_relation.`fk_user_id`) WHERE pk_user_id = '$user_id'");

        $profileManager->reCacheUser($user_id);

        return array('success' => 1);
        }
    function get_roles_autocomplete(){
        $param = array_replace_recursive($_POST, $_GET);

        $term = $param['term'];

        $items = $this->get_roles(array(
            'select_fields' => array('pk_role_id as id', 'role_name as label', 'role_name as value'),
            'data_only' => true,
            'LIKE' => array('role_name' => $term),
            'limit' => array('start' => 0, 'count' => 200)
            ));
        $output = $items['data'];

        return $output;
        }
    function get_role_form($param = array()){
        $ret = array();

        if(!has_permission('manage_roles'))
            $ret['error'][] = 'You do not have permission.';
        else{
            $posted_data = $param['post'] ? $param['post'] : array();
            $url_data = $param['get'] ? $param['get'] : array();

            $param = array_merge($param, $posted_data, $url_data);

            $is_update = isset($param['role_id']) && strlen(trim($param['role_id'])) ? trim($param['role_id']) : null;

            $fillData = array();
            if($is_update)
                $fillData = $this->get_roles(array('role_id' => $is_update));

            if($is_update && !$fillData)
                $ret['error'][] = 'Role Not Found';
            else{
                ob_start();
                ?>
                <form onsubmit="return false;" name="add_edit_role_form" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="role_id" value="<?php echo $is_update ? $is_update : ''; ?>" />
                    <div class="form-group">
                        <label>Role Title</label>
                        <input class="form-control char_limit " data-max-char="90" type="text" name="role_name" value="<?php echo $fillData ? $fillData['role_name'] : ''?>" required/>
                    </div>
                    <div class="form-group">
                        <label>Role Description</label>
                        <textarea class="form-control" name="role_description" ><?php echo $fillData ? $fillData['role_description'] : ''?></textarea>
                    </div>
                </form>
                <?php
                $ret['success'] = ob_get_clean();
                }
            }

        return $ret;
        }
    function set_role_form($param = array()){
        $posted_data = $param['post'] ? $param['post'] : array();
        $url_data = $param['get'] ? $param['get'] : array();

        $param = array_merge($param, $posted_data, $url_data);

        global $devdb;

        $ret = array();

        $is_update = isset($param['role_id']) && strlen(trim($param['role_id'])) ? trim($param['role_id']) : null;

        if(!has_permission('manage_roles')){
            $ret['error'][] = 'You do not have permission.';
            }
        else{
            $fillData = array();
            if($is_update)
                $fillData = $this->get_roles(array('role_id' => $is_update));

            if($is_update && !$fillData)
                $ret['error'][] = 'Role Not Found';
            else{
                $temp = form_validator::required($param['role_name']);
                if($temp !== true)
                    $ret['error'][] = 'Role Title '.$temp;

                if(!$ret['error']){
                    $data = array(
                        'role_name' => $param['role_name'],
                        'role_description' => $param['role_description'],
                        );

                    if($is_update){
                        $ret = $devdb->insert_update('dev_user_roles',$data," pk_role_id = '".$is_update."'");
                        }
                    else{
                        $data['role_permissions'] = serialize(array());
                        $ret = $devdb->insert_update('dev_user_roles',$data,'');
                        }

                    if($ret['success']){
                        $role_id = $is_update ? $is_update : $ret['success'];
                        $ret['success'] = $this->get_roles(array('role_id' => $role_id));
                        user_activity::add_activity('Role (ID: '.$role_id.') has been '.($is_update ? 'updated' : 'created'), 'success', ($is_update ? 'update' : 'create'));
                        }
                    }
                }
            }

        return $ret;
        }

    function adm_menus(){
        $params = array(
            'label' => 'Roles &amp; Permissions',
            'description' => 'Manage Roles &amp; Permissions',
            'menu_group' => 'Administration',
            'action' => 'manage_roles',
            'iconClass' => 'fa-dot-circle-o',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_roles')) admenu_register($params);
        }

    function manage_roles(){
        if(!has_permission('manage_roles')) return;
        global $devdb, $_config;

        $myUrl = jack_url($this->thsClass, 'manage_roles');

        if($_GET['action'] == 'assign_role')
            include('pages/assign_roles.php');
        elseif($_GET['action'] == 'config_role')
            include('pages/config_role.php');
        else
            include('pages/list_roles.php');
        }

    function get_roles($param=array()){
        $ret = array();

        if(isset($param['role_id']) && !is_array($param['role_id'])) $param['single'] = true;

        $param['index_with'] = 'pk_role_id';

        $sql = "SELECT ".($param['select_fields'] ? implode(',', $param['select_fields']) : '*')." FROM dev_user_roles WHERE 1 ";
        $count_sql = "SELECT COUNT(pk_role_id) AS TOTAL FROM dev_user_roles WHERE 1 ";

        $conditions = '';

        $loop_condition = array(
            'role_id' => 'pk_role_id',
            'role_name' => 'role_name',
            );

        $conditions .= sql_condition_maker($loop_condition, $param);

        $order_by = sql_order_by($param, 'pk_role_id', 'DESC');
        $limit_by = sql_limit_by($param);

        $sql .= $conditions.$order_by.$limit_by;
        $count_sql .= $conditions;

        $data = sql_data_collector($sql, $count_sql, $param);

        return $data;
        }

    function get_assigned_roles($user_id){
        if(!$user_id) return null;

        global $devdb;

        $sql = "SELECT * FROM dev_users_roles_relation WHERE fk_user_id = '$user_id'";
        $data = $devdb->get_results($sql, 'fk_role_id');

        return $data;
        }

    function reCachePermissions($roleId){
        $cacheID = 'rolePermissions_'.$roleId;
        removeCache($cacheID);
        }
	}
new dev_role_permission_management;
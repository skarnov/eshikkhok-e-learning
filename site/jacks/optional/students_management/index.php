<?php
class dev_student_management{
    var $thsClass = 'dev_student_management';
    var $default_role = null;

    public function __construct(){
        jack_register($this);
        }
        
    function init(){
        $permissions = array(
            'group_name' => 'Student Management',
            'permissions' => array(
                'manage_students' => array(
                    'add_student' => 'Add',
                    'edit_student' => 'Edit',
                    'delete_student' => 'Delete',
                    ),
                ),
            );

        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();

        }
        $this->default_role = getProjectSettings('default_roles,students');
    }

    function adm_menus(){
        /*Main Menu*/
        $args = array(
            'menu_group' => 'Students',
            'menu_icon' => 'fa-paper-plane'
            );
        admin_menu_group($args);
        /*Sub - Menu*/
        $params = array(
            'label' => 'Manage Students',
            'description' => 'Students Management',
            'menu_group' => 'Students',
            'action' => 'manage_students',
            'iconClass' => 'fa-plus-circle',
            'jack' => $this->thsClass,
        );
        admenu_register($params);
    }
                
    function manage_students(){
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_students');
        
        if(!has_permission('manage_students')) return null;
        
        if($_GET['action']){
            if($_GET['action'] == 'add_edit_student'){
                if($_GET['edit']){
                    if(has_permission('edit_student'))
                        include('pages/add_edit_student.php');
                    else{
                        add_notification('You don\'t have enough permission to edit student.','error');
                        header('Location:'.build_url(NULL,array('action','edit')));
                        exit();
                        }
                    }
                elseif(has_permission('add_student'))
                    include('pages/add_edit_student.php');
                else{
                    add_notification('You don\'t have enough permission to add student.','error');
                    header('Location:'.build_url(NULL,array('action')));
                    exit();
                    }
                }
            }
        else
            include('pages/list_studnets.php');
        }
    
    function get_students($param = array()){
        $profileManager = jack_obj('dev_profile_management');

        if($this->default_role){
            $param['user_role'] = $this->default_role;
            return $profileManager->get_users($param);
            }
        else return array();
        }
    
    function delete_student($delete_id){
        global $devdb;
        
        $sql = "DELETE FROM dev_users WHERE pk_user_id = '$delete_id'";
        $result = $devdb->query($sql);
        return $result;
    }
}  
new dev_student_management;
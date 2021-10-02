<?php
class dev_instructor_management{
    var $thsClass = 'dev_instructor_management';
    var $default_role = null;

    public function __construct(){
        jack_register($this);
        }
        
    function init(){
        $permissions = array(
            'group_name' => 'Instructor Management',
            'permissions' => array(
                'manage_instructors' => array(
                    'add_instructor' => 'Add',
                    'edit_instructor' => 'Edit',
                    'delete_instructor' => 'Delete',
                    ),
                ),
            );

        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();
        }
        $this->default_role = getProjectSettings('default_roles,instructors');
    }

    function adm_menus(){
        /*Main Menu*/
        $args = array(
            'menu_group' => 'Instructors',
            'menu_icon' => 'fa-paper-plane'
            );
        admin_menu_group($args);
        /*Sub - Menu*/
        $params = array(
            'label' => 'Manage Instructors',
            'description' => 'Instructor Management',
            'menu_group' => 'Instructors',
            'action' => 'manage_instructors',
            'iconClass' => 'fa-plus-circle',
            'jack' => $this->thsClass,
        );
        admenu_register($params);
    }
                
    function manage_instructors(){
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_instructors');
        
        if(!has_permission('manage_instructors')) return null;
        
        if($_GET['action']){
            if($_GET['action'] == 'add_edit_instructor'){
                if($_GET['edit']){
                    if(has_permission('edit_instructor'))
                        include('pages/add_edit_instructor.php');
                    else{
                        add_notification('You don\'t have enough permission to edit instructor.','error');
                        header('Location:'.build_url(NULL,array('action','edit')));
                        exit();
                        }
                    }
                elseif(has_permission('add_instructor'))
                    include('pages/add_edit_instructor.php');
                else{
                    add_notification('You don\'t have enough permission to add instructor.','error');
                    header('Location:'.build_url(NULL,array('action')));
                    exit();
                    }
                }
            }
        else
            include('pages/list_instructors.php');
        }
    
    function get_instructors($param = array()){
        $profileManager = jack_obj('dev_profile_management');

        if($this->default_role){
            $param['user_role'] = $this->default_role;
            return $profileManager->get_users($param);
            }
        else return array();
        }
    
    function delete_instructor($delete_id){
        global $devdb;
        
        $sql = "DELETE FROM dev_users WHERE pk_user_id = '$delete_id'";
        $result = $devdb->query($sql);
        return $result;
    }
}  
new dev_instructor_management;
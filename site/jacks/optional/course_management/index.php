<?php
class dev_course_management{
    var $thsClass = 'dev_course_management';
    var $contactTypes = array();
    public function __construct(){
        jack_register($this);
    }
        
    function init(){
        $permissions = array(
            'group_name' => 'Course Management',
            'permissions' => array(
                'manage_courses' => array(
                    'add_course' => 'Add',
                    'edit_course' => 'Edit',
                    'delete_course' => 'Delete',
                ),
                'manage_lecturers' => array(
                    'add_lecturer' => 'Add',
                    'edit_lecturer' => 'Edit',
                    'delete_lecturer' => 'Delete',
                ),
            ),
        );

        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();
        }            
    }

    function adm_menus(){
        /*Main Menu*/
        $args = array(
            'menu_group' => 'Courses',
            'menu_icon' => 'fa-paper-plane'
            );
        admin_menu_group($args);
        /*Sub - Menu*/
        $params = array(
            'label' => 'Manage Courses',
            'description' => 'Course Management',
            'menu_group' => 'Courses',
            'action' => 'manage_courses',
            'iconClass' => 'fa-plus-circle',
            'jack' => $this->thsClass,
        );
        
        admenu_register($params);
        
        $params = array(
            'label' => 'Manage Lectures',
            'description' => 'Lectures Management',
            'menu_group' => 'Courses',
            'action' => 'manage_lectures',
            'iconClass' => 'fa-plus-circle',
            'jack' => $this->thsClass,
        );
        
        admenu_register($params);
    }
                
    function manage_courses(){
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_courses');
        
        if(!has_permission('manage_courses')) return null;
        
        if($_GET['action']){
            if($_GET['action'] == 'add_edit_course'){
                if($_GET['edit']){
                    if(has_permission('edit_course'))
                        include('pages/add_edit_course.php');
                    else{
                        add_notification('You don\'t have enough permission to edit courses.','error');
                        header('Location:'.build_url(NULL,array('action','edit')));
                        exit();
                        }
                    }
                elseif(has_permission('add_course'))
                    include('pages/add_edit_course.php');
                else{
                    add_notification('You don\'t have enough permission to add courses.','error');
                    header('Location:'.build_url(NULL,array('action')));
                    exit();
                    }
                }
            }
        else
            include('pages/list_courses.php');
        }
    
    function get_courses($param = array()){
        
        global $devdb, $_config;

        $param['single'] = isset($param['single']) ? $param['single'] : false;

        $courses = array();

        $sql = "SELECT ".($param['select_fields'] ? implode(',',$param['select_fields']) : '*')." FROM e_courses WHERE 1 ";
        $count_sql = "SELECT COUNT(pk_item_id) AS TOTAL FROM e_courses WHERE 1 ";
       
        if(isset($param['fk_parent_id'])){
            $condition = ' AND fk_parent_id = "'.$param['fk_parent_id'].'" ';
        }
        
        $loop_condition = array(
            'id' => 'pk_item_id',
            'type' => 'item_type',
            'publication_status' => 'publication_status',
            'access_mode' => 'access_mode',
            'item_difficulties' => 'item_difficulties',
            'item_language' => 'item_language',
            );
        
        if ($param['q']) {
            if($param['full_string'] == true){
                if($param['q_only_title'])
                    $condition .= " AND item_title LIKE '%".$param['q']."%'";
                else
                    $condition .= " AND ( item_title LIKE '%".$param['q']."%' OR item_description LIKE '%".$param['q']."%')";
                }
            else{
                $s_words = explode(' ', $param['q']);

                foreach ($s_words as $i => $v) {
                    if($param['q_only_title'])
                        $s_words[$i] = "item_title LIKE '%" . $v . "%'";
                    else
                        $s_words[$i] = "item_title LIKE '%" . $v . "%' OR item_description LIKE '%" . $v . "%'";
                    }
                $condition .= " AND (" . implode(' OR ', $s_words) . ")";
                }
            }
            
        $courses = process_sql_operation($loop_condition, $condition, $sql, $count_sql,$param);

        if($param['course_name']){
            foreach ($courses['data'] as $i=>$v_lecture) {
                $find_course = "SELECT item_title FROM e_courses WHERE pk_item_id = '".$v_lecture['fk_parent_id']."'";
                $courses['data'][$i]['course_name'] = $devdb->get_row($find_course);
            }            
        }
        
        return $courses;
    }
    
    function get_curriculum($param = array()){
        
        global $devdb, $_config;

        $param['single'] = isset($param['single']) ? $param['single'] : false;

        $sql = "SELECT ".($param['select_fields'] ? implode(',',$param['select_fields']) : '*')." FROM e_modules WHERE 1 ";
        $count_sql = "SELECT COUNT(pk_module_id) AS TOTAL FROM e_modules WHERE 1 ";
       
        if(isset($param['fk_course_id'])){
            $condition = ' AND fk_course_id = "'.$param['fk_course_id'].'" ';
        }
        
        $loop_condition = array(

        );
         
        $curriculum['module_info'] = process_sql_operation($loop_condition, $condition, $sql, $count_sql,$param);
        return $curriculum;
    }
    
    function get_lectures($param = array()){
        
        global $devdb, $_config;

        $param['single'] = isset($param['single']) ? $param['single'] : false;

        $sql = "SELECT ".($param['select_fields'] ? implode(',',$param['select_fields']) : '*')." FROM e_lectures WHERE 1 ";
        $count_sql = "SELECT COUNT(pk_lecture_id) AS TOTAL FROM e_lectures WHERE 1 ";
       
        if(isset($param['fk_course_id'])){
            $condition = ' AND fk_course_id = "'.$param['fk_course_id'].'" ';
        }
        
        $loop_condition = array(

        );
        
        $lectures = process_sql_operation($loop_condition, $condition, $sql, $count_sql,$param);
        return $lectures;
    }
    
    function delete_course($delete_id){
        global $devdb;
        
        $find_child = "SELECT pk_item_id FROM e_courses WHERE fk_parent_id = '$delete_id'";
        $all_childs = $devdb->get_results($find_child);
    
        foreach ($all_childs AS $v_child){        
            $delete = "DELETE FROM e_courses WHERE pk_item_id ='".$v_child['pk_item_id']."'";
            $devdb->query($delete);
        }
    
        $sql = "DELETE FROM e_courses WHERE pk_item_id = '$delete_id'";
        $result = $devdb->query($sql);
        return $result;
    }
  function manage_lectures(){
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_lectures');
        
        if(!has_permission('manage_lectures')) return null;
        
        if($_GET['action']){
            if($_GET['action'] == 'add_edit_lecture'){
                if($_GET['edit']){
                    if(has_permission('edit_lecture'))
                        include('pages/add_edit_lecture.php');
                    else{
                        add_notification('You don\'t have enough permission to edit lectures.','error');
                        header('Location:'.build_url(NULL,array('action','edit')));
                        exit();
                        }
                    }
                elseif(has_permission('add_lecture'))
                    include('pages/add_edit_lecture.php');
                else{
                    add_notification('You don\'t have enough permission to add lectures.','error');
                    header('Location:'.build_url(NULL,array('action')));
                    exit();
                    }
                }
            }
        else
            include('pages/list_lectures.php');
    }      
        
        
        
}  
new dev_course_management;
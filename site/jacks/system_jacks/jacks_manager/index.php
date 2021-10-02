<?php
class dev_jacks_manager{
    var $thsClass = 'dev_jacks_manager';
    var $jack_status_file = '';

    function __construct(){
        jack_register($this);
        $this->jack_status_file = _path('root','absolute').'/jack_status.dev';
        }

    function init(){
        global $jacker;
        $this->jack_status_file = $jacker->jack_status_file;

        apiRegister($this,'update_jack_status');
        apiRegister($this,'get_jack_statuses');

        $permissions = array(
            'group_name' => 'Administration',
            'permissions' => array(
                'manage_jacks' => 'Manage Jacks',
                ),
            );
        if(!isPublic()){
            //register_permission($permissions);
            $this->adm_menus();
            }
        }

    function adm_menus(){
        $params = array(
            'label' => 'Jacks',
            'description' =>'Manage Jacks',
            'menu_group' => 'Administration',
            'action' => 'manage_jacks',
            'iconClass' => 'fa-code-fork',
            'jack' => $this->thsClass,
            );
        if(HAS_USER('sadmin')) admenu_register($params);
        $params = array(
            'label' => 'Options',
            'description' => 'Manage Options',
            'menu_group' => 'Administration',
            'action' => 'manage_jack_settings',
            'iconClass' => 'fa-cogs',
            'jack' => $this->thsClass,
            );
        if(HAS_USER('sadmin')) admenu_register($params);
        }

    function manage_jacks(){
        if(!has_permission('manage_jacks')) return;
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_jacks');

        include('pages/manage_jacks.php');
        }

    function manage_jack_settings(){
        if(!has_permission('manage_jacks')) return;
        global $devdb, $_config, $paths;
        $myUrl = jack_url($this->thsClass, 'manage_jack_settings');
        include('pages/manage_jack_settings.php');
        }

    function update_jack_status(){
        if(!has_permission('manage_jacks')) return array('error' => array('You do not have permission to update jack status'));

        $jack_status = array();
        if(file_exists($this->jack_status_file)){
            $jack_status = file_get_contents($this->jack_status_file);
            $jack_status = $jack_status ? json_decode($jack_status, true) : array();
            }

        $jack_status[$_POST['d_id']] = $_POST['data'] == 'false' ? 0 : 1;

        $ret = file_put_contents($this->jack_status_file, json_encode($jack_status));

        if($ret !== false) user_activity::add_activity('Jack (ID: '.$_POST['d_id'].') has been turned '.( $_POST['data'] == 'false' ? 'off' : 'on'), 'success', 'update');
        }

    function get_jack_statuses($args = array()){
        $jack_status = array();
        if(file_exists($this->jack_status_file)){
            $jack_status = file_get_contents($this->jack_status_file);
            $jack_status = $jack_status ? json_decode($jack_status, true) : array();
            }
        return $jack_status;
        }
    }
new dev_jacks_manager();
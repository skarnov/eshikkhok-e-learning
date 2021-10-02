<?php
//this is jack class 
class jacks{
	private static $instance = 0;
	var $all_jacks = array();
	var $jack_dependecy = array();
	var $jack_status = array();
	var $jack_event_data = array();
    var $jack_execution_order = array();
    var $new_jacks = array();
    var $jack_status_file = '';
    var $default_jacks = array(
        'dev_administration' => 1,
        'dev_authentication_manager' => 1,
        'dev_content_management' => 1,
        'dev_custom_content_type_management' => 1,
        'dev_jacks_manager' => 1,
        'dev_menu_management' => 1,
        'dev_profile_management' => 1,
        'dev_page_management' => 1,
        'dev_role_permission_management' => 1,
        'dev_tag_management' => 1,
        'dev_comment_management' => 1,
        'dev_seo_management' => 1,
        //'dev_pull_notification' => 1,
        //'dev_site_statistics' => 1,
        );
	function __construct(){
		if(self::$instance) 
			throw new Exception('Can not create another instance of this class.');
		
		self::$instance++;
        $this->jack_status_file = _path('root','absolute').'/jack_status.dev';

        //get current active jacks from DB
        global $devdb;
        if($devdb){
            //$t = new Timer();
            $jack_status = array();
            if(file_exists($this->jack_status_file)){
                $jack_status = file_get_contents($this->jack_status_file);
                $jack_status = strlen($jack_status) ? json_decode($jack_status, true) : array();
                }
            if(!$jack_status){
                $jack_status = $this->default_jacks;
                file_put_contents($this->jack_status_file, json_encode($jack_status));
                }
            foreach($jack_status as $i=>$v){
                if(!$v) continue;
                $this->jack_status[$i]['status'] = 'active';
                $this->jack_status[$i]['executed'] = 'no';
                }
            //$t->_stop();
            }
		}
	function get_jack_content($jack_id,$jack_function_name){
		$temp = $this->all_jacks[$jack_id];
		$temp->$jack_function_name();
		}
	function discover_jacks(){
		global $paths, $devdb;
        //find and register the jacks now
		$jack_folder = glob($paths['absolute']['system_jacks'].'/*',GLOB_ONLYDIR);
		foreach($jack_folder as $i=>$v){
			if(file_exists($v.'/index.php') && !file_exists($v.'/ignore')){
				include($v.'/index.php');
				}
			}
        $jack_folder = glob($paths['absolute']['optional_jacks'].'/*',GLOB_ONLYDIR);
        foreach($jack_folder as $i=>$v){
            if(file_exists($v.'/index.php')){
                if(!file_exists($v.'/ignore')) include($v.'/index.php');
                }
            }

        //ok, now register the jacks as per their events
        if($this->all_jacks){
            foreach($this->all_jacks as $i=>$v){
                if(
                    !isset($this->jack_status[$i])
                    || $this->jack_status[$i]['status'] != 'active'
                    || $this->jack_status[$i]['executed'] != 'no'
                    ) continue;

                $this->all_jacks[$i]->init();
                $this->jack_status[$i]['executed'] = 'yes';
                }
            }
		}
	function jack_register(&$jack){
		$class_name = get_class($jack);
        if(!isset($this->jack_status[$class_name])){
            $this->new_jacks[$class_name] = array('dev_jack_name'=>$class_name);
            return;
            };
		if(!isset($this->all_jacks[$class_name])){
			$this->all_jacks[$class_name] = $jack;
			}
		}
	function jack_exist($jack_id){
		return isset($this->all_jacks[$jack_id]);
		}
	function jack_function_exist($jack_id,$jack_function_name){
		return method_exists($this->all_jacks[$jack_id],$jack_function_name);
		}
	}
$jacker = new jacks;

function jack_register(&$jack){
	global $jacker;

	$jacker->jack_register($jack);
	}
function jack_url($class, $method){
    return url("/admin/$class/$method");
	}
function jack_obj($jack_id){
    global $jacker;

    if(isset($jacker->jack_status[$jack_id]) && $jacker->jack_status[$jack_id]['status'] == 'active'){
        return $jacker->all_jacks[$jack_id];
        }
    else return null;
    }
<?php
class static_multilanguage{
    var $thsClass = 'static_multilanguage';
    var $jackDir = '';
    var $currentLanguageData = array();
    var $defaultLanguageData = array();
    var $languageLoaded = false;

	function __construct(){
        $this->jackDir = _path('system_jacks', 'absolute').'/'.$this->thsClass;
        jack_register($this);
		}

	function init(){
        $permissions = array(
            'group_name' => 'Multilingual Static Texts',
            'permissions' => array(
                'manage_multilingual_static_texts' => 'Manage Multilingual Static Texts',
                ),
            );

        if(!isPublic()){
            register_permission($permissions);
            $this->adm_menus();
            }
        }
    function loadLanguages(){
	    global $_config;

        $this->currentLanguageData = $this->getLanguageDataIndexed($_config['slang']);
        $this->defaultLanguageData = $this->getLanguageDataIndexed($_config['dlang']);

        $this->languageLoaded = true;
        }
    function getLanguageDataIndexed($language){
        $cacheID = "language_data_".$language;
        $data = getCache($cacheID);
        if(!hasCache($data)){
            $data = $this->getLanguageData(array('language' => $language));
            $data = $data['data'];
            $saveData = array();
            foreach($data as $i=>$v){
                $saveData[$v['fk_term_slug_id']] = $v['translated_text'];
                }
            $data = $saveData;
            setCache($data, $cacheID);
            }
        return $data;
        }
    function getLanguageData($param=array()){
        global $devdb;

        $sql = "SELECT * FROM dev_multilingual_data WHERE 1 ";
        $count_sql = "SELECT COUNT(pk_term_data_id) AS TOTAL FROM dev_multilingual_data WHERE 1 ";
        $conditions = '';

        if($param['language'])
            $conditions .= " AND language_id = '".$param['language']."'";
        if($param['term'])
            $conditions .= " AND fk_term_slug_id = '".$param['term']."'";
        if($param['term_data_id'])
            $conditions .= " AND pk_term_data_id = '".$param['term_data_id']."'";

        if($param['single'])
            $ret = $devdb->get_row($sql.$conditions);
        else{
            $ret['data'] = $devdb->get_results($sql.$conditions);
            //$ret['total'] = $devdb->get_row($count_sql.$conditions);
            //$ret['total'] = $ret['total']['TOTAL'];
            }

        return $ret;
        }
    function getTerms($param=array()){
        global $devdb;

        $cacheID = "language_all_terms";
        $data = !$param['single'] ? getCache($cacheID) : false;

        if(!hasCache($data)){
            $sql = "SELECT * FROM dev_multilingual_terms WHERE 1 ";
            $count_sql = "SELECT COUNT(pk_term_id) AS TOTAL FROM dev_multilingual_terms WHERE 1 ";
            $conditions = '';

            if($param['term'])
                $conditions .= " AND term_slug = '".$param['term']."'";
            if($param['term_id'])
                $conditions .= " AND pk_term_id = '".$param['term_id']."'";

            if($param['single'])
                $ret = $devdb->get_row($sql.$conditions);
            else{
                $ret['data'] = $devdb->get_results($sql.$conditions);
                setCache($ret, $cacheID);
                }
            return $ret;
            }
        else return $data;
        }
    function adm_menus(){
        $params = array(
            'label' => 'Multilingual Static Texts',
            'description' => 'Multilingual Static Texts',
            'menu_group' => 'Administration',
            'action' => 'manage_multilingual_texts',
            'iconClass' => 'fa-list',
            'jack' => $this->thsClass,
            );
        if(has_permission('manage_multilingual_static_texts')) admenu_register($params);
        }

    function manage_multilingual_texts(){
        if(!has_permission('manage_multilingual_static_texts')) return null;

        global $devdb, $_config;

        $myUrl = jack_url($this->thsClass, 'manage_multilingual_texts');

        include('pages/list_static_texts.php');
        }

    function ML($args){
        $term = $args[0];
        $value = '';
        if(isset($this->currentLanguageData[$term])) $value = $this->currentLanguageData[$term];
        elseif (isset($this->defaultLanguageData[$term])) $value = $this->defaultLanguageData[$term];
        unset($args[0]);
        array_unshift($args,$value);
        return call_user_func_array('sprintf', $args);
        }
	}
new static_multilanguage;

function ML(){
    $obj = jack_obj('static_multilanguage');
    if(!$obj->languageLoaded) $obj->loadLanguages();
    $args = func_get_args();
    return $obj->ML($args);
    }
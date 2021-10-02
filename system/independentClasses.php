<?php
class meta_manager{
    static function get_metas($param = array()){
        $sql = "SELECT * FROM dev_metas WHERE 1 ";
        $conditions = '';

        $params_n_columns = array(
            'fk' => 'fk_id',
            'type' => 'fk_type',
            'name' => 'meta_name'
            );

        $param['data_only'] = true;
        $param['index_with'] = 'meta_name';

        $cacheID = 'meta_set_'.$param['fk'].'_'.$param['type'];
        $data = getCache($cacheID);
        if(!hasCache($data)){
            $data = process_sql_operation($params_n_columns,$conditions,$sql,'',$param);
            $data = $data['data'] ? $data['data'] : array();
            setCache($data, $cacheID, 'meta');
            }

        return $data;
        }
    static function put_metas($type, $fk, $metas){
        global $devdb;
        if($type && $fk && $metas){
            $oldMetas = self::get_metas(array('fk' => $fk, 'type' => $type));

            $insertData = array(
                'fk_id' => $fk,
                'fk_type' => $type
                );
            foreach($metas as $i=>$v){
                if(isset($oldMetas[$i])){
                    $updateData = array(
                        'meta_value' => $v,
                        );

                    $updateNow = $devdb->insert_update('dev_metas', $updateData, " pk_meta_id = '".$oldMetas[$i]['pk_meta_id']."'");
                    unset($oldMetas[$i]);
                    }
                else{
                    $insertData['meta_name'] = $i;
                    $insertData['meta_value'] = $v;

                    $insertNow = $devdb->insert_update('dev_metas', $insertData);
                    }
                }
            if($oldMetas){
                foreach($oldMetas as $i=>$v){
                    $devdb->query("DELETE FROM dev_metas WHERE fk_id = '$fk' AND fk_type = '$type'");
                    }
                }
            }
        }
    static function search_metas($params = array(), $return_sql = false){
        global $devdb;
        $from = ' FROM '.$params['fk_table_name'];
        $meta_table_alias = array();
        $meta_count = 0;
        if($params['meta']){
            foreach($params['meta'] as $cond=>$v){
                foreach($v as $i=>$m){
                    $meta_count++;
                    $mt = "metaTable".$meta_count;
                    $meta_table_alias[] = $mt;
                    $from .= " INNER JOIN dev_metas AS ".$mt." on (".$mt.".fk_id = ".$params['fk_table_name'].".".$params['fk_primary_key'].") ";
                    }
                }
            }
        $sql = "SELECT ".$params['fk_table_name'].".".$params['fk_primary_key']." ".$from." WHERE fk_type = '".$params['fk_type']."'";

        $meta_count = 0;
        $conditions = array();
        if($params['meta']['OR']){
            $or_condition = array();
                foreach($params['meta']['OR'] as $i=>$v){
                    $mt = $meta_table_alias[$meta_count];
                    $meta_count++;
                    if(is_array($v))
                        $or_condition[] = " (".$mt.".meta_name = '".$i."' AND ".$mt.".meta_value IN ('".implode("','", $v)."') ) ";
                    else $or_condition[] = " (".$mt.".meta_name = '".$i."' AND ".$mt.".meta_value = '".$v."' ) ";
                    }
            $conditions[] = implode(" OR ", $or_condition);
            }

        if($params['meta']['AND']){
            $and_condition = array();
            foreach($params['meta']['AND'] as $i=>$v){
                $mt = $meta_table_alias[$meta_count];
                $meta_count++;
                if(is_array($v))
                    $and_condition[] = " (".$mt.".meta_name = '".$i."' AND ".$mt.".meta_value IN ('".implode("','", $v)."') ) ";
                else $and_condition[] = " (".$mt.".meta_name = '".$i."' AND ".$mt.".meta_value = '".$v."' ) ";
                }
            $conditions[] = implode(" AND ", $and_condition);
            }

        $date_condition = '';
        /*
         * $args['meta'] = array(
         *      'DATE' = array(
         *          'LESS_THAN_EQUAL' = array(
         *              0 = array(
         *                  'meta' = 'meta_name'
         *                  'value' = 'd-m-Y'
         *                  )
         *              )
         *          )
         *      )
         * */
        if($params['meta']['DATE']){
            $date_condition = array();
            foreach($params['meta']['DATE'] as $i=>$v){
                if($i == 'LESS_THAN_EQUAL'){
                    foreach($v as $eachConditionSet){
                        $mt = $meta_table_alias[$meta_count];
                        $meta_count++;
                        $date_condition[] = "(".$mt.".meta_name = '".$eachConditionSet['meta']."' AND '".$eachConditionSet['value']."' <= STR_TO_DATE(".$mt.".meta_value, '%d-%m-%Y'))";
                        }
                    }
                }
            $conditions[] = implode(" AND ", $date_condition);
            }

        $joiner = $params['join_metas_with'] ? $params['join_metas_with'] : 'OR';

        $sql .= " AND " . implode(" ".$joiner." ", $conditions) ." GROUP BY ".$params['fk_table_name'].'.'.$params['fk_primary_key'];

        if($return_sql) return $sql;

        $_contents = $devdb->get_results($sql);
        $contents = array();
        foreach($_contents as $i=>$v){
            $contents[] = $v[$params['fk_primary_key']];
            }

        return $contents;
        }
    }

abstract class custom_content_type{
    private static $customContentTypes = array();

    var $name = null;
    var $label = null;
    var $exceptional = false;

    var $title_settings = array(
        'label' => 'Title',
        'hide' => false,
        'useEditorForTitle' => false,
        );
    var $subtitle_settings = array(
        'label' => 'Sub-Title',
        'hide' => false,
        );
    var $content_settings = array(
        'label' => 'Content',
        'hide' => false,
        );
    var $category_settings = array(
        'label' => 'Category',
        'hide' => false,
        );
    var $featuredImage_settings = array(
        'label' => 'Featured Image',
        'hide' => false,
        );
    var $squareFeaturedImage_settings = array(
        'label' => 'Featured Image (Square)',
        'hide' => false,
        );
    var $wideFeaturedImage_settings = array(
        'label' => 'Featured Image (Wide)',
        'hide' => false,
        );
    var $seoFeatures_settings = array(
        'label' => 'SEO Features',
        'hide' => false,
        );
    var $publishTime_settings = array(
        'label' => 'Publish At',
        'hide' => false,
        );

    final function __construct(){
        $this->init();
        self::$customContentTypes[$this->name] = $this;
        }

    static function registerCustomContentTypes(){
        $cManager = jack_obj('dev_content_management');
        if($cManager){
            foreach(self::$customContentTypes as $cTypeName=>$cType){
                $cManager->register_content_type($cTypeName, $cType);
                }
            }
        self::$customContentTypes = array();
        }

    abstract public function init();
    abstract public function preProcess($content);
    abstract public function postProcess($content_id, $content);
    abstract public function get_fields($content);
    }
function registerCustomContentTypes(){ custom_content_type::registerCustomContentTypes(); }
addAction('after_execute_plugins_event', 'registerCustomContentTypes');

abstract class page_templates{
    private static $pageTemplates = array();

    var $name = null;
    var $label = null;
    var $disableSelection = false;

    final function __construct(){
        $this->init();
        self::$pageTemplates[$this->name] = $this;
        }

    static function registerPageTemplates(){
        $pManager = jack_obj('dev_page_management');
        if($pManager){
            foreach(self::$pageTemplates as $templateName=>$template){
                $pManager->registerPageTemplates($templateName, $template);
                }
            }
        self::$pageTemplates = array();
        }

    abstract public function init();
    abstract public function preProcess($content);
    abstract public function getFields($content);
    }
function registerPageTemplates(){ page_templates::registerPageTemplates(); }
addAction('after_execute_plugins_event', 'registerPageTemplates');

abstract class public_routers{
    private static $publicRouters = array();
    var $url_path = null;

    final function __construct(){
        $this->init();
        if(!$this->url_path) return false;
        self::$publicRouters[$this->url_path] = $this;
        }
    static function registerPublicRouters(){
        global $gMan;
        foreach(self::$publicRouters as $routerName=>$routerObject){
            $gMan->register_public_routers($routerName, $routerObject);
            }
        self::$publicRouters = array();
        }
    abstract public function init();
    abstract public function hasAccess();
    }
function registerPublicRouters(){ public_routers::registerPublicRouters(); }
addAction('after_boot_event', 'registerPublicRouters');

abstract class email_templates{
    private static $emailTemplates = array();
    private static $registrationComplete = false;

    var $name = null;
    var $label = null;
    var $source = null;
    var $saved_source = null;
    var $email_body = null;
    var $availableVariables = null;
    var $sortOrder = null;
    var $pk_etemplate_id = null;

    abstract function get_replace_array($dataArray);

    final function __construct(){
        $this->init();
        self::$emailTemplates[$this->name] = $this;
        if(self::$registrationComplete) self::registerEmailTemplates();
        }

    static function registerEmailTemplates(){
        $emailTemplateManager = jack_obj('dev_email_template_manager');
        foreach(self::$emailTemplates as $templateName=>$template){
            $emailTemplateManager->registerEmailTemplates($templateName, $template);
            }
        $emailTemplateManager->pullEmailTemplates();
        self::$emailTemplates = array();
        self::$registrationComplete = true;
        }

    function get_email_body($dataArray){
        $emailHeader = function_exists('EmailTemplateHeader') ? EmailTemplateHeader() : '';
        $emailFooter = function_exists('EmailTemplateFooter') ? EmailTemplateFooter() : '';

        $replaces = $this->get_replace_array($dataArray);

        $emailBody = str_replace(array_keys($replaces), array_values($replaces), $emailHeader.$this->email_body.$emailFooter);

        return $emailBody;
        }
    function send_email($dataArray, $to, $subject, $toName = 'Recipient'){
        send_mail($to, $subject, $this->get_email_body($dataArray), $toName);
        }
    }
function registerEmailTemplates(){ email_templates::registerEmailTemplates(); }
addAction('after_execute_plugins_event', 'registerEmailTemplates');

abstract class contact_message_types{
    private static $contactMessageTypes = array();
    private static $registrationComplete = false;

    var $name = null;
    var $label = null;
    var $emailTemplate = array();
    var $view_columns = array();

    final function __construct(){
        $this->init();
        self::$contactMessageTypes[$this->name] = $this;
        if(self::$registrationComplete) self::registerContactMessageTypes();
        }

    static function registerContactMessageTypes(){
        $contactManager = jack_obj('dev_contact_management');
        if($contactManager){
            foreach(self::$contactMessageTypes as $typeName=>$type){
                $contactManager->registerContactMessageTypes($typeName, $type);
                }
            }
        self::$registrationComplete = true;
        self::$contactMessageTypes = array();
        }

    abstract public function init();
    }
function registerContactMessageTypes(){ contact_message_types::registerContactMessageTypes(); }
addAction('after_execute_plugins_event', 'registerContactMessageTypes');

/**** minifier ****/
class minify_handler{
    static $cssBundle = array();
    static $jsBundle = array();
    static $jsData = '';
    static $cssData = '';
    static $minified_js_data = '';
    static $minified_css_data = '';
    static $bundle = array();
    static $default_target = '';

    function __construct(){
        self::$default_target = theme_path('absolute').'/';
        }

    static function addSource($bundle_name,$file_arr,$target = null, $ext = null){
        if(!$ext && $file_arr) $ext = getExtension($file_arr[0]);

        if($ext == 'css') self::$cssBundle[$bundle_name] = $file_arr;
        if($ext == 'js') self::$jsBundle[$bundle_name] = $file_arr;

        if($target === true){
            if($file_arr) $saveDir = dirname($file_arr[0]);
            }
        else $saveDir = $target ? $target : theme_path('absolute').'/';

        self::$bundle[$bundle_name] = array(
            'file_arr' => $file_arr,
            'target' => $saveDir,
            );
        }

    static function renderMinifiedCss($filename, $nextVersion = 0, $printData = false){
        $target = self::$bundle[$filename]['target'];
        $target = $target ? $target :  theme_path('absolute').'/';
        $previousFileMin = NULL;
        $previousFiles = glob($target.$filename.'_*.css');

        foreach ($previousFiles as $fName){
            $theFileName = getFilename($fName);
            $previousFileMinAbs = $target.$theFileName.'.'.getExtension($fName);
            $previousFileMin = convertPaths($previousFileMinAbs);

            if(file_exists($previousFileMinAbs)){
                $theFileNameParts = explode('_', $theFileName);;
                $version = !isset($theFileNameParts[1]) ? null : $theFileNameParts[1];

                if(($version && $version != $nextVersion) || !$version){
                    unlink($previousFileMinAbs);
                    }
                else{
					if($printData) echo '<style type="text/css">'.@file_get_contents($previousFileMinAbs).'</style>';
                    else echo '<link href="'.$previousFileMin.'" rel="stylesheet" type="text/css" />';
                    return true;
                    }
				break;
                }
            }

        $targetFileMin = self::$bundle[$filename]['target'].$filename.'_'.$nextVersion.'.min.css';
        $targetFileMinRelative = convertPaths(self::$bundle[$filename]['target']).$filename.'_'.$nextVersion.'.min.css';

       // require_once(common_files('absolute').'/minifier/class.minify.php');


        $cssData = '';
        $minified_css_data = '';
        if (self::$cssBundle[$filename]){
            foreach(self::$cssBundle[$filename] as $i=>$v){
                $cssData .= @file_get_contents($v);
                }
            $minified_css_data = self::minifyCSS($cssData);
			if($printData) echo '<style type="text/css">'.$minified_css_data.'</style>';
            else {
				@file_put_contents($targetFileMin,$minified_css_data);
				echo '<link href="'.$targetFileMinRelative.'" rel="stylesheet" type="text/css" />';
				}
            }

        }

    static function renderMinifiedJs($filename, $doMinify = true, $nextVersion = 0){
        self::$bundle[$filename]['target'] = self::$bundle[$filename]['target'] ? self::$bundle[$filename]['target'] :  theme_path('absolute').'/';
        $previousFileMin = NULL;
        $previousFiles = glob(self::$bundle[$filename]['target'].$filename.'_*.js');

        foreach ($previousFiles as $fName){
            $previousFileMin = convertPaths(self::$bundle[$filename]['target']).getFilename($fName).'.'.getExtension($fName);
            $previousFileMinAbs = self::$bundle[$filename]['target'].getFilename($fName).'.'.getExtension($fName);

            $theFileName = getFilename($fName);
            $theFileNameParts = explode('_', $theFileName);;
            $version = !isset($theFileNameParts[1]) ? null : $theFileNameParts[1];

            if(($version && $version != $nextVersion) || !$version){
                unlink($previousFileMinAbs);
                }
            else{
                echo '<script src="'.$previousFileMin.'" type="text/javascript" ></script>';
                return;
                }
            }

        $targetFileMin = self::$bundle[$filename]['target'].$filename.'_'.$nextVersion.'.min.js';
        $targetFileMinRelative = convertPaths(self::$bundle[$filename]['target']).$filename.'_'.$nextVersion.'.min.js';

        //require_once(common_files('absolute').'/minifier/class.minify.php');
        //pre(self::$jsBundle[$filename]);
        $jsData = '';
        $minified_js_data = '';
        if (self::$jsBundle[$filename]){
            foreach(self::$jsBundle[$filename] as $i=>$v){
                $jsData .= "/* ".$v." */"."\r\n".file_get_contents($v)."\r\n \r\n";
                }
            if($doMinify) $minified_js_data = self::minifyJsApi($jsData);
            else $minified_js_data = $jsData;
            file_put_contents($targetFileMin,$minified_js_data);
            echo '<script src="'.$targetFileMinRelative.'" type="text/javascript" ></script>';
            }
        }

    private static function minifyJS($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                // Remove comment(s)
                '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
                // Remove white-space(s) outside the string and regex
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
                // Remove the last semicolon
                '#;+\}#',
                // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
                // --ibid. From `foo['bar']` to `foo.bar`
                '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
                ),
            array(
                '$1',
                '$1$2',
                '}',
                '$1$3',
                '$1.$3'
                ),
            $input);
        }

    private static function minifyJsApi($js, $options = array()){
        require_once(common_files('absolute').'/minifier/Shrink.php');

        $options = array(
            'encode' => true,
            'timer' => true,
            'gzip' => true,
            'closure' => true,
            'remove_comments' => false
            );

        return Minifier::minify($js, $options);

        }

    private static function minifyCSS($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
                ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
                ),
            $input);
    }




    }
/*
 * hookAction - is a hook class to add and execute actions from various points
 *
 * HOW TO ADD A FUNCTION AS AN ACTION
 *      addAction([hook name],[your function to call]);
 *      However, if you want to send 1 or more parameter to your function, then call like this
 *      addAction([hook name],[your function to call],[parameter 1],[parameter 2],[....]);
 *
 * HOW TO ADD A METHOD AS AN ACTION
 *      addAction([hook name],array([object],[method]));
 *      However, if you want to send 1 or more parameter to your function, then call like this
 *      addAction([hook name],array([object],[method]),[parameter 1],[parameter 2],[....]);
 *
 * HOW TO EXECUTE AN ACTION
 *      simply add this code where you want all the hooked calls be called
 *      doAction([hook name]);
 *      That's it.
 *
 * NOTICE: do not declare any parameter in your callback function. Instead, to get the parameters
 * use func_get_args();
 * */
class hookAction{
    private static $actions = array();

    /**
 * @return null
*/static function addAction(){
        $args = func_get_args();
        $hook = isset($args[0]) ? $args[0] : null;
        $call = isset($args[1]) ? $args[1] : null;

        if(!$hook || !$call) return null;

        $thisIndex = isset(self::$actions[$hook]) ? count(self::$actions[$hook]) : 0;
        self::$actions[$hook][$thisIndex] = array(
            'call' => $call,
            );
        $totalArgs = func_num_args();
        if($totalArgs > 2) {
            self::$actions[$hook][$thisIndex]['param'] = array();
            $arg_list = func_get_args();
            for($i = 2;$i < $totalArgs;$i++){
                self::$actions[$hook][$thisIndex]['param'][] = $args[$i];
                }
            }
        }
    static function doAction(){
        $rets = array();
        $args = func_get_args();
        if(!$args) return null;
        $hook = $args[0];
        unset($args[0]);

        if(isset(self::$actions[$hook]) && self::$actions[$hook]){
            foreach(self::$actions[$hook] as $i=>$v){
                $count = count($args);
                if(isset($v['param']) && $v['param']){
                    foreach($args as $m){
                        $v['param'][] = $m;
                        }
                    $args = $v['param'];
                    }
                else{
                    $_args = array();
                    foreach($args as $arg_value){
                        $_args[] = $arg_value;
                        }
                    $args = $_args;
                    }
                if(!$args) $args = array();
                $rets[$i] = call_user_func_array($v['call'],$args);
                }
            }
        return $rets;
        }
    /**
* @param $hook
 * @return int
*/static function hasAction(hasAction$hook){
        if(isset(self::$actions[$hook]) && self::$actions[$hook]) return count(self::$actions[$hook]);
        else return 0;
        }
    }
function addAction(){
    $args = func_get_args();
    $hook = isset($args[0]) ? $args[0] : null;
    $call = isset($args[1]) ? $args[1] : null;
    if(!$hook || !$call) return null;
    call_user_func_array('hookAction::addAction',$args);
    }
function doAction(){
    $ret = call_user_func_array('hookAction::doAction',func_get_args());
    return $ret;
    }
function hasAction($hook){
    return hookAction::hasAction($hook);
    }
/*
 * Jack Settings Manager
 * */
class jack_settings{
    var $settings = array();
    public function __construct(){}
    public function register_settings($jack, $settings){
        if(isset($this->settings[$jack])){
            $this->settings[$jack] = array_merge($this->settings[$jack], $settings);
        }
        else{
            $this->settings[$jack] = $settings;
        }
    }
    function get_settings($jack){
        return $this->settings[$jack];
    }
    function get_saved_settings($jack_id, $dropNonSettingsFields = false){
        global $devdb, $multilingualJackFields;

        //first pull any saved settings for this jack from DB

        $cacheID = 'jack_settings_'.md5($jack_id);
        $result = getCache($cacheID);

        if(!hasCache($result)){
            $sql = " SELECT * FROM dev_jack_settings WHERE fk_jack_id = '".$jack_id."'";
            $result = $devdb->get_results($sql);
            setCache($result,$cacheID);
            }

        $settings = array();
        if($result){
            foreach($result as $i=>$v){
                $settings[$v['settings_key']] = (in_array($v['settings_key'], $multilingualJackFields) !== false) ? processToRender($v['settings_value']) : $v['settings_value'];
                }
            }

        if($dropNonSettingsFields) unset($settings['jack_id'], $settings['submitSettings']);

        return $settings;
        }
    public function get_settings_form($jack_id, $settings = null){
        global $formPro, $multilingualJackFields;

        if($settings){
            foreach ($settings as $i => $v) {
                if (in_array($i, $multilingualJackFields) === false) continue;

                $settings[$i] = processToRender($v);
                }
            }

        $settings = $settings ? $settings : $this->get_saved_settings($jack_id);

        $formArray = array(
            'noFormTag' => true,
            'noSubmitButton' => true,
            'fields' => $this->settings[$jack_id]
        );

        return $formPro->form_creator($formArray,$settings);
    }
    public function put_settings($jack_id,$submitted_settings){
        global $devdb,$_config, $multilingualJackFields;
        $sql = " SELECT * FROM dev_jack_settings WHERE fk_jack_id = '" . $jack_id . "'";
        $oldData = $devdb->get_results($sql,'settings_key');

        //first delete all the settings against this jack from DB
        $sql = " DELETE FROM dev_jack_settings WHERE fk_jack_id = '".$jack_id."'";
        $delete_all = $devdb->query($sql);

        //now save these settings against this jack in DB
        $inserted = 0;
        foreach($submitted_settings as $i=>$v){
            $insertData = array(
                'fk_jack_id' => $jack_id,
                'settings_type' => 'global',
                'fk_user_id' => $_config['user']['pk_user_id'],
                'settings_key' => $i,
                'settings_value' => is_array($v) ? implode(MULTIVALUE_SEPARATOR,$v) : $v,
                'settings_created_by' => $_config['user']['pk_user_id'],
                'settings_created_at' => date('Y-m-d H:i:s'),
                'settings_modified_by' => $_config['user']['pk_user_id'],
                'settings_modified_at' => date('Y-m-d H:i:s'),
                );
            if (in_array($i, $multilingualJackFields) !== false)
                $insertData['settings_value'] = processToStore($oldData[$i]['settings_value'], $insertData['settings_value']);

            $insert = $devdb->insert_update('dev_jack_settings', $insertData);
            if($insert['success']) $inserted++;
            }

        $this->reCacheJackSettings($jack_id);
        return $inserted;
    }
    function reCacheJackSettings($jack_id){
        $cacheID = 'jack_settings_'.md5($jack_id);
        removeCache($cacheID);
        $this->get_saved_settings($jack_id);
    }
}

$JACK_SETTINGS = new jack_settings();

/*
 * Role Permission Manger Class
 * */
class permission_manager {
    var $permissions = array();

    function __construct(){}
    function init(){
        global $_config, $devdb;

        if(!$devdb) return null;

        if(HAS_USER()){
            $_config['user']['permissions'] = array();
            if($_config['user']['roles']){
                $roles = explode(',',$_config['user']['roles']);
                $allRolePermissions = array();
                foreach($roles as $i=>$v){
                    if(!$v || $v == '-1') continue;
                    $cacheID = 'rolePermissions_'.$v;
                    $allRolePermissions[$v] = getCache($cacheID);

                    if(!hasCache($allRolePermissions[$v])){
                        $allRolePermissions[$v] = $devdb->get_var("SELECT role_permissions FROM dev_user_roles WHERE pk_role_id = '".$v."'");
                        $allRolePermissions[$v] = unserialize($allRolePermissions[$v]);
                        setCache($allRolePermissions[$v], $cacheID);
                        }
                    }

                foreach($allRolePermissions as $i=>$v){
                    $_config['user']['permissions'] = array_replace_recursive($_config['user']['permissions'], $v);
                    }
                }
            else $_config['user']['permissions'] = array();
        }
    }
    function register_permissions($args){
        if(!$this->permissions[$args['group_name']]) $this->permissions[$args['group_name']] = array();
        foreach($args['permissions'] as $i=>$v){
            $this->permissions[$args['group_name']][$i] = $v;
        }
    }
}

$cPermission = new permission_manager();

function register_permission($args){
    global $cPermission;

    $cPermission->register_permissions($args);
}
function has_permission($p,$forceSuperAdminCheck = false){
    if(!$p) return false;
    if(!HAS_USER()) return false;
    global $_config;

    if(in_array('-1', $_config['user']['roles_list']) !== false){
        if($forceSuperAdminCheck){
            if($_config['user']['permissions'][$p]) return true;
            else return false;
        }
        else return true;
    }
    elseif($_config['user']['permissions'][$p]) return true;
    else return false;
}
/**********************************************/

/*
 * Admin Menu Class
 * */

class adminmenu{
    var $menu_groups_icons = array(
        'dashboard' => 'fa-dashboard',
        'administration' => 'fa-shield',
        'contents' => 'fa-credit-card',
        'menus' => 'fa-bars',
        'pages' => 'fa-file-text-o',
        'tags' => 'fa-tags',
        'student &amp; lecturers' => '',
        'courses' => 'fa-book',
        'comments' => 'fa-comment-o',
        'notifications' => 'fa-bullhorn',
        );

    var $menu_items = array();
    var $only_menu_tems = array();
    var $registeredItemCount = 0;

    function __construct(){
        $this->menu_items['default'] = array();
        $this->menu_items['top'] = array();
        }

    function admenu_register($param){
        $this->registeredItemCount++;
        $group = strtoupper($param['menu_group']);
        $group = !strlen($group) ? 'NULL' : $group;
        $position = !isset($param['position']) || !strlen($param['position']) ? 'top' : $param['position'];
        $menuId = $this->registeredItemCount.'_'.md5($param['label']);
        if(isset($this->menu_items[$position][$group][$menuId])){
            $e = 'Multiple Menu Items with Label "'.$param['label'].'" was found under group "'.$group.'"';
            trigger_error($e,E_USER_ERROR);
            }
        elseif(isset($this->only_menu_tems[$menuId])){
            $e = 'Multiple Menu Items with Label "'.$param['label'].'" was found under group "'.$group.'"';
            trigger_error($e,E_USER_ERROR);
            }
        else{
            $this->menu_items[$position][$group][$menuId] = array(
                'label' => $param['label'],
                'description' => $param['description'],
                'action' => $param['action'],
                'jack' => $param['jack'],
                'icon-class' => $param['iconClass'],
                'target' => $param['target'],
                'position' => $position,
                'menu_group' => $group,
                'parent-icon-class' => $param['parentIconClass'],
                'type' => $param['type'] ? $param['type'] : 'menu_item',
                );
            $this->only_menu_tems[$menuId] = $this->menu_items[$position][$group][$menuId];
            return $this->only_menu_tems;
        }
    }

    function get_each_menu($childs,$withoutUl = false){
        $menu = '';

        foreach($childs as $i=>$v){
            if($v['type'] == 'divider'){
                $menu .= '<li class="divider"></li>';
                }
            else
                $menu .= '<li><a '.($v['target'] ? 'target="'.$v['target'].'"' : '').' tabindex="-1" href="'.$v['action'].'" title="'.$v['description'].'"><i class="menu-icon fa '.$v['icon-class'].'"></i><span class="mm-text">'.$v['label'].'</span></a></li>';
                }

        if(strlen($menu)){
            if($withoutUl) return $menu;
            else return '<ul>'.$menu.'</ul>';
            }
        return $menu;
        }

    function get_admin_menu($renderFunction = ''){
        global $adminmenu,$_config;
        if(strlen($renderFunction)) return $renderFunction();

        $nav_menu = '<ul class="navigation">';
        if(has_permission('access_to_dashboard')) $nav_menu .= '<li><a href="'.($_config['noFront'] ? url('') : url('admin')).'" class="dashboard_link"><i class="menu-icon fa fa-dashboard"></i><span class="mm-text">DASHBOARD</span></a></li>';

        reOrderAdminMenu();

        foreach($adminmenu->menu_items['default'] as $g=>$item){
            if($g != 'NULL'){
                $childs = $adminmenu->get_each_menu($item);
                if(!strlen($childs)) continue;
                $icon = $this->menu_groups_icons[strtolower($g)] ? $this->menu_groups_icons[strtolower($g)] : 'fa-tasks';
                $nav_menu .= '<li class="mm-dropdown"><a href="javascript:"><i class="menu-icon fa '.$icon.'"></i><span class="mm-text">'.$g.'</span></a>';
                $nav_menu .= $childs.'</li>';
                }
            else{
                $childs = $adminmenu->get_each_menu($item, true);
                if(!strlen($childs)) continue;
                $nav_menu .= $childs;
                }
            }
        $nav_menu .= '</ul>';

        return $nav_menu;
        }
    }

function getTopAdminMenuItems($childs,$withoutUl = false){
    $menu = '';

    foreach($childs as $i=>$v){
        if($v['type'] == 'divider')
            $menu .= '<li class="divider"></li>';
        else
            $menu .= '<li><a '.($v['target'] ? 'target="'.$v['target'].'"' : '').' tabindex="-1" href="'.$v['action'].'" title="'.$v['description'].'"><i class="menu-icon fa '.$v['icon-class'].'"></i>&nbsp;<span class="">'.$v['label'].'</span></a></li>';
        }

    if(strlen($menu)){
        if($withoutUl) return $menu;
        else return '<ul class="dropdown-menu">'.$menu.'</ul>';
        }

    return $menu;
    }
function topAdminMenu(){
    global $adminmenu, $_config;
    $nav_menu = '<ul class="nav navbar-nav openOnHover">';
    if(has_permission('access_to_dashboard')) $nav_menu .= getProjectSettings('features,backend_left_menu') ? '' : '<li><a href="'.($_config['noFront'] ? url('') : url('admin')).'" class="dashboard_link"><i class="menu-icon fa fa-dashboard"></i>&nbsp;DASHBOARD</a></li>';

    reOrderAdminMenu();

    if($adminmenu->menu_items['top']){
        foreach($adminmenu->menu_items['top'] as $g=>$item){
            if($g != 'NULL'){
                $childs = getTopAdminMenuItems($item);
                if(!strlen($childs)) continue;
                $icon = $adminmenu->menu_groups_icons[strtolower($g)] ? $adminmenu->menu_groups_icons[strtolower($g)] : 'fa-tasks';
                $nav_menu .= '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="javascript:"><i class="menu-icon fa '.$icon.'"></i>&nbsp;<span>'.$g.'</span></a>';
                $nav_menu .= $childs.'</li>';
                }
            else{
                $childs = getTopAdminMenuItems($item, true);
                if(!strlen($childs)) continue;
                $nav_menu .= $childs;
                }
            }
        }

    $nav_menu .= '</ul>';

    return $nav_menu;
    }

$adminmenu = new adminmenu;

function reOrderAdminMenu($editMode = false){
    global $_config, $adminmenu;

    $newArray = array();
    $tempMenuItems = $adminmenu->only_menu_tems;
    //pre($_config['adminMenu'],0);
    if($_config['adminMenu']){
        $cacheID = 'devAdminMenuOrder'.md5($editMode);
        $newArray = false;//getCache($cacheID, 'devAdminMenuOrder');

        if(!hasCache($newArray)){
            foreach($adminmenu->menu_items as $i=>$v){
                $newArray[$i] = array();
                }
            if(!is_array($_config['adminMenu']) && strlen($_config['adminMenu']))
                $_config['adminMenu'] = unserialize($_config['adminMenu']);
            foreach($_config['adminMenu'] as $position=>$menuGroup){
                foreach($menuGroup as $group=>$items){
                    if(is_array($items)){
                        foreach($items as $itemId=>$itemData){
                            $thisItem = array();
                            if(isset($tempMenuItems[$itemId])){
                                $tempMenuItems[$itemId]['label'] = $itemData['label'];

                                $show = isset($itemData['show']) && $itemData['show'] == 'yes' ? true : false;

                                $newArray[$position][$group][$itemId] = $tempMenuItems[$itemId];
                                $newArray[$position][$group][$itemId]['show'] = $show;
                                unset($tempMenuItems[$itemId]);
                                }
                            }
                        }
                    else{
                        $newArray[$position][$items] = array();
                        }
                    }
                }
            if($tempMenuItems){
                foreach($tempMenuItems as $i=>$v){
                    $newArray[$v['position']][$v['menu_group']][$i] = $v;
                    }
                }

            $adminmenu->menu_items = $newArray;

            //if($newArray) setCache($newArray,$cacheID,'devAdminMenuOrder');
        }

        return true;
        }
    return false;
    }
function admenu_register($param){
    global $_config;

    if(!$param['iconClass']) $param['iconClass'] = 'fa-tasks';
    extract($param);

    $param['menu_group'] = !strlen($param['menu_group']) ? 'NULL' : $param['menu_group'];

    //if(!$param['label'] || !$param['description'] || !$param['menu_group']) return null;

    if($param['url']) $param['action'] = $param['url'];
    else $param['action'] = url(($_config['noFront'] ? '/' : '/admin/').$param['jack'].'/'.$param['action']);

    if($param['action_args'])
        $param['action'] = build_url($param['action_args'],array(),$param['action']);

    if(!isset($param['target']))
        $param['target'] = null;

    if($param['permissions']){
        if(is_array($param['permissions'])){
            foreach($param['permissions'] as $i){
                if(!has_permission($i)) return false;
            }
        }
        else{
            if(!has_permission($param['permissions'])) return false;
        }
    }

    global $adminmenu;

    return $adminmenu->admenu_register($param);
    }
function admin_menu_group($args = array()){
    global $adminmenu;
    $adminmenu->menu_groups_icons[strtolower($args['menu_group'])] = $args['menu_icon'];
}
function admin_menu_position($args = array()){
    global $adminmenu;
    if(!isset($adminmenu->menu_items[$args['position']]))
        $adminmenu->menu_items[$args['position']] = array();
}

/**************************************************************************/

/*
 * Session Based Notification
 * */
class notify_user{
    var $messages = array();
    function __construct(){
        $this->pull_notification();
    }
    function pull_notification(){
        if(isset($_SESSION['notify_user']) && $_SESSION['notify_user']){
            $this->messages = $_SESSION['notify_user'];
            }
        unset($_SESSION['notify_user']);
        }
    function put_notification(){
        $_SESSION['notify_user'] = $this->messages;
        }
    function add_notification($msg, $type = 'success'){
        $type = $type == 'error' ? 'danger' : $type;
        $this->messages[] = array('msg' => $msg, 'type' => $type);
        }
    function get_notification(){
        $messages = array();
        $lastType = null;
        $first = true;
        $messageGroupCount = 0;
        $messageGroupType = array();
        if($this->messages){
            foreach($this->messages as $i=>$v){
                if($first){
                    $messages[$messageGroupCount] = array($v);
                    $lastType = $v['type'];
                    $messageGroupType[$messageGroupCount] = $lastType;
                    $first = false;
                    }
                else{
                    if($v['type'] == $lastType){
                        $messages[$messageGroupCount][] = $v;
                        }
                    else{
                        $messageGroupCount += 1;
                        $messages[$messageGroupCount] = array($v);
                        $lastType = $v['type'];
                        $messageGroupType[$messageGroupCount] = $lastType;
                        }
                    }
                }
            ?>
            <div id="pa-page-alerts-box">
                <?php
                foreach($messages as $i=>$messageGroup){
                    ?>
                    <div class="tal alert alert-page alert-dark pa_page_alerts_dark alert-<?php echo $messageGroupType[$i];?>" data-animate="true" style="">
                        <button type="button" class="close">×</button>
                        <?php
                        foreach($messageGroup as $msg){
                            echo '<p>'.$msg['msg'].'</p>';
                            }
                        ?>
                    </div>
                    <?php
                    }
                ?>
            </div>
            <?php
            }
        $this->messages = array();
        }
    }
$notify_user = new notify_user;

function add_notification($msg,$type = 'success'){
    global $notify_user;
    $notify_user->add_notification($msg,$type);
}
function via_put_notification(){
    global $notify_user;
    $notify_user->put_notification();
}
register_shutdown_function('via_put_notification');
/*********************************************************/

/*
 * User Activity Logger
 * */
class user_activity{
    static public function add_activity($msg = '', $status = 'success', $type = 'create', $url = ''){
        global $devdb, $_config;

        if($url === true || $url == '1') $url = current_url();

        $data = array(
            'activity_msg' => $msg,
            'activity_url' => $url,
            'activity_type' => $type,
            'activity_status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_config['user']['pk_user_id'],
            );

        $inserted = $devdb->insert_update('dev_user_activities',$data);

        return $inserted;
    }
    static public function get_activity($param=array()){
        global $devdb;

        if(!isset($param['index_with'])) $param['index_with'] = 'pk_activity_log';

        $sql = "SELECT dev_user_activities.*
                        ,dev_users.user_fullname
                        ,dev_users.pk_user_id
                FROM
                        dev_user_activities
                        ,dev_users
                WHERE
                        dev_users.pk_user_id = dev_user_activities.created_by AND 1 ";
        $count_sql = "SELECT COUNT(pk_activity_log) AS TOTAL FROM dev_user_activities WHERE 1 ";
        $condition = '';

        $loopCondition = array(
            'type' => 'activity_type',
            'user_id' => 'created_by',
            );

        $condition .= sql_condition_maker($loopCondition, $param);

        $orderBy = sql_order_by($param, 'dev_user_activities.created_at', 'DESC');
        $limitBy = sql_limit_by($param);

        $sql .= $condition.$orderBy.$limitBy;
        $count_sql .= $condition;

        $activities = sql_data_collector($sql, $count_sql, $param);

        return $activities;
        }

    static public function delete_activity($id=NULL){
        global $devdb;

        if($id) $sql = "DELETE FROM dev_user_activities WHERE pk_activity_log = '".$id."'";
        else $sql = "TRUNCATE TABLE dev_user_activities";

        $deleted = $devdb->query($sql);

        return $deleted;
    }
}
new user_activity;
/*********************************************************/

/*
 * API Manager Class
 * */

class apis{
    private static $instance = 0;
    var $all_apis = array();

    public function __construct(){
        if(self::$instance)
            throw new Exception('Can not create another instance of this class.');

        self::$instance++;
    }
    public function api_register(&$jack, $api, $securityLevels, $help){
        $class_name = get_class($jack);
        $take = true;

        $this->all_apis[$class_name][$api] = array(
                'jack' => $jack,
                'securityLevels' => $securityLevels,
                'api_help' => $help
                );
        return true;
        }
    function api_call($jack,$api,$param){
        global $gMan, $SAFEGUARD;
        $isSafe = true;
        $returnData = null;

        if($gMan->call_type == 'api' && isset($this->all_apis[$jack]) && isset($this->all_apis[$jack][$api])){
            $thsApi = $this->all_apis[$jack][$api];
            if(isset($thsApi['securityLevels'])){
                if(is_array($thsApi['securityLevels'])){
                    foreach($thsApi['securityLevels'] as $securityLevel){
                        $isSafe = $SAFEGUARD->checkRequest($securityLevel);
                        if(!$isSafe) break;
                        }
                    }
                else $isSafe = $SAFEGUARD->checkRequest($thsApi['securityLevels']);
                }
            if($isSafe){
                $jack_obj = $thsApi['jack'];
                $api_func = $api;

                $returnData = $param ? $jack_obj->$api_func($param) : $jack_obj->$api_func();
                }
            else $returnData = array('error' => array('You are not permitted to call this API'));
            }

        return $returnData;
        }
    }

$apis = new apis;

function api_call($jack,$api,$param = NULL){
    global $apis;

    return $apis->api_call($jack,$api,$param);
}

function apiRegister(&$jack, $api, $security_levels = null,$help = null){
    if(!$security_levels) $security_levels = array('internalOnly', 'loggedInUserOnly');

    global $apis;
    $apis->api_register($jack, $api, $security_levels, $help);
    }
function curl_call($url){
    $caller = curl_init();

    curl_setopt($caller, CURLOPT_URL, $url);
    curl_setopt($caller, CURLOPT_POSTFIELDS, $_POST);
    curl_setopt($caller, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($caller);
    $response = json_decode(trim($response), TRUE);
    curl_close($caller);

    return $response;
}

/**************************************************************************/

/*
 * Cache Lite Extended Class
 * */

$useCacheLite = getProjectSettings('components,cache_lite', 5);

if($useCacheLite){
    if(file_exists(_path('system','absolute').'/Cache/Lite.php'))
        require _path('system','absolute')."/Cache/Lite.php";
    if(file_exists(_path('system','absolute').'/Cache/Lite/Function.php'))
        require _path('system','absolute')."/Cache/Lite/Function.php";

    if(class_exists('Cache_Lite')){
        if(!file_exists(_path('root','absolute').'/cached_data'))
            mkdir(_path('root','absolute').'/cached_data', 0777, true);
        $options = array(
            'cacheDir' => _path('root','absolute').'/cached_data/',
            'lifeTime' => 2592000,
            'pearErrorMode' => CACHE_LITE_ERROR_RETURN
            );
        $devCache = new Cache_Lite($options);
        }
    else $devCache = false;
    }
else $devCache = false;

function setCache($data,$cacheID,$cacheGroup=null){
    global $devCache;

    if(!$data) return null;

    if($devCache){
        if($cacheGroup)
            return $devCache->save(json_encode($data),$cacheID,$cacheGroup);
        else return $devCache->save(json_encode($data),$cacheID);
    }
    return false;
}
function getCache($cacheID,$cacheGroup=null){
    global $devCache;

    if($devCache){
        if($cacheGroup)
            $data = $devCache->get($cacheID,$cacheGroup);
        else $data = $devCache->get($cacheID);

        if($data === false) return false;
        else return $data = json_decode($data, true);
    }
    return false;
}
function removeCache($cacheID,$cacheGroup=null){
    global $devCache;

    if($devCache){
        if($cacheGroup) return $devCache->remove($cacheID, $cacheGroup);
        else return $devCache->remove($cacheID);
    }
    return false;
}
function cleanCache($cacheGroup){
    global $devCache;

    if($devCache) return $devCache->clean($cacheGroup);
    return false;
}
function hasCache($data){
    if($data === false) return false;
    else return true;
}
/**************************************************************************/

/*
 * Mailer Class
 * */
$mailCss = '<style type="text/css">
                    .fullTable{
                        width: 100%;
                        }
                    a.mailBtn{
                        text-decoration: none;
                        }
                    .mailBtn{
                        display: inline-block;
                        padding: 5px;
                        background-color: #333;
                        color: #fff;
                        border: 1px groove #FFF;
                        font-weight: bold;
                        }
                    .btnDanger{
                        background-color: #F70000;
                        }
                    .btnPrimary{
                        background-color: #80a5ff;
                        }
                    .p10{
                        padding: 10px;
                        }
                </style>';
function sendEmail($to,$subject,$msg,$to_name = 'Recipient'){
    global $_config, $mailCss;

    if(!isset($_config['use_smtp_email_account'])) return send_mail($to,$subject,$msg,$to_name);

    $smtpHost = $_config['smtp_host'] ? $_config['smtp_host'] : 'smtp.gmail.com';
    $smtpPort = $_config['smtp_port'] ? $_config['smtp_port'] : '587';
    $fromEmail = $_config['smtp_email_address'];
    $fromEmailPass = $_config['smtp_email_password'];
    $fromName = $_config['smtp_email_name'];

    $email = $to ;
    $message = $msg ;

    $mail = createPhpMailer();

// set mailer to use SMTP
    $mail->IsSMTP();

    $mail->SMTPAuth = true;     // turn on SMTP authentication
    //$mail->SMTPSecure = "ssl";
    //$mail->Host = "avalon.websitewelcome.com";
    //$mail->Port = 465;
    $mail->SMTPSecure = "tls";
    $mail->SMTPDebug  = 0;
    $mail->Host = $smtpHost;
    $mail->Port = $smtpPort;

    $mail->Username = $fromEmail;  // SMTP username
    $mail->Password = $fromEmailPass; // SMTP password


    $mail->From = $fromEmail;
    $mail->FromName = $fromName;

    if(is_array($email)){
        foreach($email as $i=>$v){
            $i = trim($i);
            $mail->AddAddress($i,isset($v) ? $v : $i);
            }
        }
    else
        $mail->AddAddress($email, $to_name ? $to_name : $email);

    $mail->WordWrap = 50;

    $mail->IsHTML(true);

    $mail->Subject = $subject;

    $mail->Body    = $mailCss.$message;

    if(class_exists('InStyle')){
        $instyle = createInStyle();
        $mail->Body = $instyle->convert($mail->Body);
    }

    $mail->AltBody = $message;
    //pre($mail);
    if(!$mail->Send()) return false;
    else return true;
}
function sendMailerEmail($to,$subject,$msg,$to_name = 'Recipient',$attachment = null){
    global $_config, $mailCss;
    $headers = "From: ".$_config['site_name'] .' <' . $_SERVER['SERVER_ADMIN'] . ">\r\n";
    //$headers .= "Reply-To: ". $_SERVER['SERVER_ADMIN'] . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $msg = $mailCss . $msg ;

    require_once(_path('common_files','absolute').'/inStyle/simple_html_dom.php');
    require_once(_path('common_files','absolute').'/inStyle/instyle.php');

    $instyle = createInStyle();
    $msg = $instyle->convert($msg);

    $mail = createPhpMailer();
    $mail->setFrom($_SERVER['SERVER_ADMIN'], $_config['site_name']);
    $mail->addAddress($to, $to_name);
    if($attachment){
        if(is_array($attachment)){
            foreach($attachment as $eachAttachment)
                $mail->addAttachment($eachAttachment);
            }
        else $mail->addAttachment($attachment);
        }

    $mail->isHTML(true);

    $mail->Subject = $subject;
    $mail->Body    = $msg;

    return $mail->send();
    }
function send_mail($to,$subject,$message,$to_name='Recipient'){
    global $_config, $mailCss;

    if(isset($_config['use_smtp_email_account'])) return sendEmail($to,$subject,$message,$to_name);

    if(!is_array($to)) $recipients = explode(',',$to);
    else $recipients = $to;

    foreach($recipients as $i=>$eachRecipient){
        $recipients[$i] = trim($eachRecipient);
        }
    $recipients = implode(',',$recipients);

    $headers = "From: ".$_config['site_name'] .' <' . $_SERVER['SERVER_ADMIN'] . ">\r\n";
    //$headers .= "Reply-To: ". $_SERVER['SERVER_ADMIN'] . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $msg = '<html>
            <head>
                '.$mailCss.'
            </head>
            <body>
            ';
    $msg = $msg . $message .'</body></html>';

    require_once(_path('common_files','absolute').'/inStyle/simple_html_dom.php');
    require_once(_path('common_files','absolute').'/inStyle/instyle.php');

    $instyle = createInStyle();
    $msg = $instyle->convert($msg);
    $errLevel = error_reporting(E_ALL ^ E_NOTICE);  // suppress NOTICEs

    $m = @mail($recipients, $subject, $msg, $headers);

    error_reporting($errLevel);  // restore old error levels
    return true;
    }

/*********************************************************/

/*
 * Form Validator Class
 * */
class form_validator{
    static function required($data){
        if(!$data || !strlen($data) || $data == null) return 'is required.';
        else return true;
    }

    static function email($data){
        if(self::required($data) !== true) return self::required($data);
        //return true;
        if(filter_var($data, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $data)) return true;
        else return 'is not valid.';
    }

    static function password($data){
        //check length
        if(strlen($data) < 6 ) return 'password length must be more than 6 characters.';
        else return true;
    }

    static function unique($data,$table,$field_name,$cond = ''){
        global $devdb;

        $sql = "SELECT COUNT(".$field_name.") AS C FROM ".$table." WHERE ".$field_name." = '".$data."' AND ".($cond ? $cond : '1')." LIMIT 0,1";

        $ret = $devdb->get_row($sql);
        if($ret['C'])
            return $data.'-'.$ret['C'];
        else return true;
    }

    static function numeric($data){
        if(!$data) return true;
        //check length
        if(!is_numeric($data)) return 'have to be numeric';
        else return true;
    }
    static function _integer($data){
        if(!$data) return true;
        //check length
        if(!is_numeric($data)) return 'have to be integer';
        else{
            if(strpos($data,'.') === false) return true;
            else return 'have to be integer';
        }
    }

    static function _isValidDate($data){
        if(!$data) return null;
        @list($year,$month,$day) = @explode('-',$data);
        if(@checkdate($month, $day, $year)) return true;
        else return "$data is not a  valid date";
    }

    static function _float($data){
        if(!$data) return true;
        //check length
        if(!is_numeric($data)) return 'have to be float';
        else{
            if(strpos($data,'.') !== false) return true;
            else return 'have to be float';
        }
    }
    static function _in($data,$haystack = array()){
        if(!$data) return null;
        //check length
        if(in_array($data,$haystack)) return true;
        else return 'have to be one of these: '.implode(',',$haystack);
    }
    static function _length($data, $allowedMaxLength){
        if(!$data) $data = '';
        if(mb_strlen($data) < $allowedMaxLength) return true;
        else return 'containing more than '.$allowedMaxLength.' characters, however, it should be less than '.$allowedMaxLength.' characters';
        }
}
$form_validator = new form_validator;
/***********************************************************/

/*
 * Form Modifiers Class
 * */
class form_modifiers{
    private static $strip = array("_","~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")","=", "+", "[", "{", "]","}", "\\", "|", ";", ":", "\"", "'", "&#96;", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;","â€”", "â€“", ",", "<", ".", ">", "/", "?");

    static function slug($data, $replacor = '-', $force_lowercase = false, $anal = false){
        if(!$data) return '';
        $data = strip_tags($data);
        if($force_lowercase) $data = $force_lowercase ? (function_exists('mb_strtolower') ? mb_strtolower($data, 'UTF-8') : strtolower($data)) : $data;

        $stop_words = array(" a "," able "," about "," above "," abroad "," according "," accordingly "," across "," actually "," adj "," after "," afterwards "," again "," against "," ago "," ahead "," ain't "," all "," allow "," allows "," almost "," alone "," along "," alongside "," already "," also "," although "," always "," am "," amid "," amidst "," among "," amongst "," an "," and "," another "," any "," anybody "," anyhow "," anyone "," anything "," anyway "," anyways "," anywhere "," apart "," appear "," appreciate "," appropriate "," are "," aren't "," around "," as "," a's "," aside "," ask "," asking "," associated "," at "," available "," away "," awfully "," b "," back "," backward "," backwards "," be "," became "," because "," become "," becomes "," becoming "," been "," before "," beforehand "," begin "," behind "," being "," believe "," below "," beside "," besides "," best "," better "," between "," beyond "," both "," brief "," but "," by "," c "," came "," can "," cannot "," cant "," can't "," caption "," cause "," causes "," certain "," certainly "," changes "," clearly "," c'mon "," co "," co. "," com "," come "," comes "," concerning "," consequently "," consider "," considering "," contain "," containing "," contains "," corresponding "," could "," couldn't "," course "," c's "," currently "," d "," dare "," daren't "," definitely "," described "," despite "," did "," didn't "," different "," directly "," do "," does "," doesn't "," doing "," done "," don't "," down "," downwards "," during "," e "," each "," edu "," eg "," eight "," eighty "," either "," else "," elsewhere "," end "," ending "," enough "," entirely "," especially "," et "," etc "," even "," ever "," evermore "," every "," everybody "," everyone "," everything "," everywhere "," ex "," exactly "," example "," except "," f "," fairly "," far "," farther "," few "," fewer "," fifth "," first "," five "," followed "," following "," follows "," for "," forever "," former "," formerly "," forth "," forward "," found "," four "," from "," further "," furthermore "," g "," get "," gets "," getting "," given "," gives "," go "," goes "," going "," gone "," got "," gotten "," greetings "," h "," had "," hadn't "," half "," happens "," hardly "," has "," hasn't "," have "," haven't "," having "," he "," he'd "," he'll "," hello "," help "," hence "," her "," here "," hereafter "," hereby "," herein "," here's "," hereupon "," hers "," herself "," he's "," hi "," him "," himself "," his "," hither "," hopefully "," how "," howbeit "," however "," hundred "," i "," i'd "," ie "," if "," ignored "," i'll "," i'm "," immediate "," in "," inasmuch "," inc "," inc. "," indeed "," indicate "," indicated "," indicates "," inner "," inside "," insofar "," instead "," into "," inward "," is "," isn't "," it "," it'd "," it'll "," its "," it's "," itself "," i've "," j "," just "," k "," keep "," keeps "," kept "," know "," known "," knows "," l "," last "," lately "," later "," latter "," latterly "," least "," less "," lest "," let "," let's "," like "," liked "," likely "," likewise "," little "," look "," looking "," looks "," low "," lower "," ltd "," m "," made "," mainly "," make "," makes "," many "," may "," maybe "," mayn't "," me "," mean "," meantime "," meanwhile "," merely "," might "," mightn't "," mine "," minus "," miss "," more "," moreover "," most "," mostly "," mr "," mrs "," much "," must "," mustn't "," my "," myself "," n "," name "," namely "," nd "," near "," nearly "," necessary "," need "," needn't "," needs "," neither "," never "," neverf "," neverless "," nevertheless "," new "," next "," nine "," ninety "," no "," nobody "," non "," none "," nonetheless "," noone "," no-one "," nor "," normally "," not "," nothing "," notwithstanding "," novel "," now "," nowhere "," o "," obviously "," of "," off "," often "," oh "," ok "," okay "," old "," on "," once "," one "," ones "," one's "," only "," onto "," opposite "," or "," other "," others "," otherwise "," ought "," oughtn't "," our "," ours "," ourselves "," out "," outside "," over "," overall "," own "," p "," particular "," particularly "," past "," per "," perhaps "," placed "," please "," plus "," possible "," presumably "," probably "," provided "," provides "," q "," que "," quite "," qv "," r "," rather "," rd "," re "," really "," reasonably "," recent "," recently "," regarding "," regardless "," regards "," relatively "," respectively "," right "," round "," s "," said "," same "," saw "," say "," saying "," says "," second "," secondly "," see "," seeing "," seem "," seemed "," seeming "," seems "," seen "," self "," selves "," sensible "," sent "," serious "," seriously "," seven "," several "," shall "," shan't "," she "," she'd "," she'll "," she's "," should "," shouldn't "," since "," six "," so "," some "," somebody "," someday "," somehow "," someone "," something "," sometime "," sometimes "," somewhat "," somewhere "," soon "," sorry "," specified "," specify "," specifying "," still "," sub "," such "," sup "," sure "," t "," take "," taken "," taking "," tell "," tends "," th "," than "," thank "," thanks "," thanx "," that "," that'll "," thats "," that's "," that've "," the "," their "," theirs "," them "," themselves "," then "," thence "," there "," thereafter "," thereby "," there'd "," therefore "," therein "," there'll "," there're "," theres "," there's "," thereupon "," there've "," these "," they "," they'd "," they'll "," they're "," they've "," thing "," things "," think "," third "," thirty "," this "," thorough "," thoroughly "," those "," though "," three "," through "," throughout "," thru "," thus "," till "," to "," together "," too "," took "," toward "," towards "," tried "," tries "," truly "," try "," trying "," t's "," twice "," two "," u "," un "," under "," underneath "," undoing "," unfortunately "," unless "," unlike "," unlikely "," until "," unto "," up "," upon "," upwards "," us "," use "," used "," useful "," uses "," using "," usually "," v "," value "," various "," versus "," very "," via "," viz "," vs "," w "," want "," wants "," was "," wasn't "," way "," we "," we'd "," welcome "," well "," we'll "," went "," were "," we're "," weren't "," we've "," what "," whatever "," what'll "," what's "," what've "," when "," whence "," whenever "," where "," whereafter "," whereas "," whereby "," wherein "," where's "," whereupon "," wherever "," whether "," which "," whichever "," while "," whilst "," whither "," who "," who'd "," whoever "," whole "," who'll "," whom "," whomever "," who's "," whose "," why "," will "," willing "," wish "," with "," within "," without "," wonder "," won't "," would "," wouldn't "," x "," y "," yes "," yet "," you "," you'd "," you'll "," your "," you're "," yours "," yourself "," yourselves "," you've "," z "," zero ");
        $stop_replacer = " ".$replacor." ";

        $trans = array();
        foreach(self::$strip as $i=>$v){
            $trans[$v] = $replacor;
        }

        $clean = trim(strip_tags(strtr($data,$trans)));

        foreach($stop_words as $i=>$v){
            $clean = str_replace($v,$stop_replacer,$clean);
        }

        $clean = preg_replace('/\s+/', $replacor, $clean);
        $clean = preg_replace('/[-]+/', $replacor, $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", $replacor, $clean) : $clean ;
        return strtolower($clean);
        }
    static function slug_simple($data, $replacer = '-', $force_lowercase = false){
        if(!$data) return '';

        $data = strip_tags($data);

        if($force_lowercase) $data = $force_lowercase ? (function_exists('mb_strtolower') ? mb_strtolower($data, 'UTF-8') : strtolower($data)) : $data;

        $data = preg_replace('/\s+/', $replacer, $data);

        return $data;
        }
    static function userName($data, $replacor = '_'){
        if(!$data) return null;
        $trans = array();
        foreach(self::$strip as $i=>$v){
            $trans[$v] = $replacor;
        }
        $clean = trim(strip_tags(strtr($data,$trans)));
        $clean = preg_replace('/\s+/', $replacor, $clean);
        $clean = preg_replace('/[-]+/', $replacor, $clean);
        $clean = preg_replace("/[^a-zA-Z0-9]/", $replacor, $clean);
        return $clean;
    }
    static function siteMapEscape($data){
        return htmlentities($data,ENT_XML1);
        }
    static function xmlEscapeReverse($data){
        return html_entity_decode($data,ENT_XML1);
        }
    static function reverse($data){
        return implode('',array_reverse(str_split($data)));
    }
    static function remove_nr($s){
        return preg_replace('/(n\\\r\\\)*/i','',$s);
    }
    static function db_datetime($data,$default_form='m/d/y',$new_form='y/m/d'){
        if(!$data) return;

        $default_form = explode('/',$default_form);
        $data_parts = explode(' ',$data);
        $date_parts = explode('/',$data_parts[0]);
    }
    static function sanitize_title($title){
        //return filter_var(htmlspecialchars(strip_tags($title)),FILTER_SANITIZE_STRING,FILTER_FLAG_ENCODE_HIGH);
        return htmlspecialchars(strip_tags($title));
        }
    static function sanitize_input($data, $replacer = ''){
        if(!$data) return '';

        $strip = array("\\", "\"", "'", "`","&#96;","&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;","â€”", "â€“", "<", ">", "/");

        $trans = array();
        foreach($strip as $i=>$v){
            $trans[$v] = $replacer;
            }

        $clean = trim(strip_tags(strtr($data,$trans)));

        return $clean;
        }
    static function encodeInput($data, $allowedTags = '<b><a><strong>'){
        $data = htmlspecialchars($data,ENT_QUOTES | ENT_HTML401);
        $data = strip_tags($data,$allowedTags);

        return $data;
        }
    static function remove_underscore($data,$replacor = ' '){
        return str_replace('_',$replacor,$data);
        }
    static function html_purify($data){
        require_once(common_files('absolute').'/HTMLPurifier/HTMLPurifier.auto.php');

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('CSS.AllowTricky', true);
        $config->set('HTML.SafeIframe',true);
        $config->set('URI.SafeIframeRegexp','%^//www.youtube.com/%');
        $config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
        $config->set('HTML.SafeEmbed',true);
        $config->set('HTML.SafeObject',true);

        // Set some HTML5 properties
        $config->set('HTML.DefinitionID', 'html5-definitions'); // unqiue id
        $config->set('HTML.DefinitionRev', 1);
        //$config->set('Cache.DefinitionImpl', null); // remove this later!
        //$def = $config->getHTMLDefinition(true);
        //$def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            // http://developers.whatwg.org/sections.html
            $def->addElement('section', 'Block', 'Flow', 'Common');
            $def->addElement('nav',     'Block', 'Flow', 'Common');
            $def->addElement('article', 'Block', 'Flow', 'Common');
            $def->addElement('aside',   'Block', 'Flow', 'Common');
            $def->addElement('header',  'Block', 'Flow', 'Common');
            $def->addElement('footer',  'Block', 'Flow', 'Common');

            // Content model actually excludes several tags, not modelled here
            $def->addElement('address', 'Block', 'Flow', 'Common');
            $def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');

            // http://developers.whatwg.org/grouping-content.html
            $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
            $def->addElement('figcaption', 'Inline', 'Flow', 'Common');

            // http://developers.whatwg.org/the-video-element.html#the-video-element
            $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
                'src' => 'URI',
                'type' => 'Text',
                'width' => 'Length',
                'height' => 'Length',
                'poster' => 'URI',
                'preload' => 'Enum#auto,metadata,none',
                'controls' => 'Bool',
            ));
            $def->addElement('source', 'Block', 'Flow', 'Common', array(
                'src' => 'URI',
                'type' => 'Text',
            ));

            // http://developers.whatwg.org/text-level-semantics.html
            $def->addElement('s',    'Inline', 'Inline', 'Common');
            $def->addElement('var',  'Inline', 'Inline', 'Common');
            $def->addElement('sub',  'Inline', 'Inline', 'Common');
            $def->addElement('sup',  'Inline', 'Inline', 'Common');
            $def->addElement('mark', 'Inline', 'Inline', 'Common');
            $def->addElement('wbr',  'Inline', 'Empty', 'Core');

            // http://developers.whatwg.org/edits.html
            $def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
            $def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));

            // TinyMCE
            $def->addAttribute('img', 'data-mce-src', 'Text');
            $def->addAttribute('img', 'data-mce-json', 'Text');

            // Others
            $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
            $def->addAttribute('table', 'height', 'Text');
            $def->addAttribute('td', 'border', 'Text');
            $def->addAttribute('th', 'border', 'Text');
            $def->addAttribute('tr', 'width', 'Text');
            $def->addAttribute('tr', 'height', 'Text');
            $def->addAttribute('tr', 'border', 'Text');
        }

        $purifier = new HTMLPurifier($config);
        $clean_html = $purifier->purify($data);

        return $clean_html;
    }
    static function content_to_excerpt($data){
        $data = str_replace(array('Featured-Image-Will-Be-Displayed-Here'), array(''), $data);
        return $data;
        }
}

$form_modifier = new form_modifiers;
/************************************************************/

/*
 * Form Processor
 * */
define("MULTIVALUE_SEPARATOR","{|*@*|}");
class formProcessor{
    public function form_creator($form_array,$form_data){
        $method = $form_array['submit_method'] ? $form_array['submit_method'] : 'post';
        $action = $form_array['action'] ? $form_array['action'] : '';
        $no_form_tag = $form_array['noFormTag'] ? true : false;
        $no_submit_button = $form_array['noSubmitButton'] ? true : false;

        if(!$no_form_tag)
            $form = "<form method='".$method."' action='".$action."' enctype='multipart/form-data'>";
        else $form = '';

        foreach($form_array['fields'] as $i=>$v){
            if(isset($v['name'])) $f_name = $v['name'];
            else $f_name = $i;
            $form .= self::form_elements($i, $f_name, $v, $form_data[$i]);
            }

        if(!$no_submit_button)
            $form .= "<div><input type='submit' name='submit' value='submit'/></div>";

        if(!$no_form_tag)
            $form .= "</form>";

        return $form;
    }

    function form_element_data_modifier($form_element, &$form_data){
        if(isset($form_element['modifier'])){
            if(is_array($form_element['modifier'])){
                foreach($form_element['modifier'] as $i=>$v){

                    }
                }
            }
        }

    /**
     * @param $f_name
     * @param $config
     * @param $data
     * @return string
     */
    static function form_elements($id, $f_name, $config, $data = NULL){
        global $devdb,$_config;
        $field = '';
        $value = $data ? $data : '';
        $groupTypes = array('fieldset_start','fieldset_end','panel_start','panel_end');
        if($config['type'] == 'fieldset_start'){
            $width = $config['width'] ? $config['width'] : 4;
            $field = '<div class="col-sm-'.$config['width'].' '.($config['class'] ? $config['class'] : '').'"><fieldset>'.($config['label'] ? '<legend>'.$config['label'].'</legend>' : '');
            if(!isset($config['skip_wrapper'])) $config['skip_wrapper'] = true;
            }
        elseif($config['type'] == 'fieldset_end'){
            $field = '</fieldset></div>';
            if(!isset($config['skip_wrapper'])) $config['skip_wrapper'] = true;
            }
        if($config['type'] == 'panel_start'){
            $width = $config['width'] ? $config['width'] : 4;
            $field = '<div class="col-sm-'.$config['width'].' '.($config['class'] ? $config['class'] : '').'"><div class="panel '.($config['panel_class'] ? $config['panel_class'] : 'panel-default').'">'.($config['label'] ? '<div class="panel-heading"><span class="panel-title">'.$config['label'].'</span></div>' : '').'<div class="panel-body">';
            if(!isset($config['skip_wrapper'])) $config['skip_wrapper'] = true;
            }
        elseif($config['type'] == 'panel_end'){
            $field = '</div><!-- Panel Body --></div><!-- Panel --></div><!-- Wrapper -->';
            if(!isset($config['skip_wrapper'])) $config['skip_wrapper'] = true;
            }
        elseif ($config['type'] == 'text'
            || $config['type'] == 'email'
            || $config['type'] == 'number'
            || $config['type'] == 'password'
        ){
            $field = '<div class="form-group form_element_holder '.$config['type'].'_holder '.$config['type'].'_holder_'.$id.'"><label>'.$config['label'].'</label><input class="form-control" id="text_holder_'.$id.'" type="'.$config['type'].'" name="'.$f_name.'" value="'.$value.'"/>';
        }
        elseif ($config['type'] == 'textarea'){
            $field = '<div class="form-group form_element_holder textarea_holder textarea_holder_'.$id.'"><label>'.$config['label'].'</label><textarea class="form-control" id="textarea_holder_'.$id.'" name="'.$f_name.'">'.$value.'</textarea>';
        }
        elseif($config['type'] == 'sortable'){
            global $dashboardWidgets;
            $_dashboardWidgets = $dashboardWidgets;
            $value = strlen($value) ? explode(MULTIVALUE_SEPARATOR,$value) : array();
            $items = array();
            foreach($value as $key){
                $items[$key] = $_dashboardWidgets[$key];
                unset($_dashboardWidgets[$key]);
                }
            foreach($_dashboardWidgets as $i=>$v){
                $items[$i] = $v;
                }
            ob_start();
            ?>
            <div class="form-group form_element_holder sortable_holder sortable_holder_<?php echo $id?>">
                <?php if(strlen($config['label'])):?><label><?php echo $config['label']?></label><?php endif;?>
                <ul id="sortable_<?php echo $id?>" class="widget-tasks list-group ml20 mt10 mb0">
                    <?php
                    foreach($items as $i=>$v){
                        ?>
                        <li class="list-group-item task">
                            <i class="sorthandle fa fa-arrows-v task-sort-icon"></i>
                            <input type="hidden" name="<?php echo $f_name?>[]" value="<?php echo $i?>" />
                            <span class="task-title"><?php echo $v?></span>
                        </li>
                        <?php
                        }
                    ?>
                </ul>
            <script type="text/javascript">
                init.push(function(){
                    $('#sortable_<?php echo $f_name?>').sortable().disableSelection();
                    });
            </script>
            <?php
            $field = ob_get_clean();
            }
        elseif($config['type'] == 'radio'){
            $field = '<div class="form_element_holder radio_holder radio_holder_'.$id.'">';
            $field .= '<label>'.$config['label'].'</label>';
            $field .= '<div class="options_holder radio">';
            /*  if($config['data']['static']){
                  foreach($config['data']['static'] as $i=>$v){
                      $checked = $value ? ($i == $value ? 'checked' : '') : ($config['data']['default'] && $config['data']['default'] == $i ? 'checked' : '');
                      $field .= '<label class="fl"><input type="radio" name="'.$f_name.'" value="'.$i.'" '.$checked.'>'.$v.'</label>';
                      }
                  }*/
            $options = $config['data']['static'] ? $config['data']['static'] : null;
            if(!$options){
                if($config['data']['dynamic']){
                    if($config['data']['dynamic']['label'] && $config['data']['dynamic']['value'] && $config['data']['dynamic']['table']){
                        $sql = "SELECT ".$config['data']['dynamic']['label'].",".$config['data']['dynamic']['value']." FROM ".$config['data']['dynamic']['table'];
                        $sql .= $config['data']['dynamic']['condition'] ? " WHERE ".$config['data']['dynamic']['condition'] : '';

                        $_options = $devdb->get_results($sql);
                        foreach($_options as $i=>$v){
                            $options[$v[$config['data']['dynamic']['value']]] = $v[$config['data']['dynamic']['label']];
                        }
                    }
                }
            }
            foreach($options as $i=>$v){
                $checked = $value ? ($i == $value ? 'checked' : '') : ($config['data']['default'] && $config['data']['default'] == $i ? 'checked' : '');
                $field .= '<label><input class="px" type="radio" name="'.$f_name.'" value="'.$i.'" '.$checked.'><span class="lbl">'.$v.'</span></label>';
            }
            $field .= '</div>';

            if($config['js']){
                foreach($config['js'] as $i=>$v){
                    $field .= '<script type="text/javascript">
                                init.push(function(){
                                    $(document).on("'.$i.'",".radio_holder_'.$id.'",'.$v.').'.$i.'();
                                    });
                                </script>
                                ';
                }
            }
        }
        elseif($config['type'] == 'checkbox'){
            $value = explode(MULTIVALUE_SEPARATOR,$value);
            $field = '<div class="form_element_holder checkbox_holder checkbox_holder_'.$id.'">';
            $field .= '<label>'.$config['label'].'</label>';
            $field .= '<div class="options_holder">';
            /* if($config['data']['static']){
                 foreach($config['data']['static'] as $i=>$v){
                     $checked = $data ? (in_array($i,$pre_data) ? 'checked' : '') : '';
                     $field .= '<label class="fl"><input type="checkbox" name="'.$f_name.'[]" value="'.$i.'" '.$checked.'>'.$v.'</label>';
                     }
                 }*/
            $options = $config['data']['static'] ? $config['data']['static'] : null;
            if(!$options){
                if($config['data']['dynamic']){
                    if($config['data']['dynamic']['label'] && $config['data']['dynamic']['value'] && $config['data']['dynamic']['table']){
                        $sql = "SELECT ".$config['data']['dynamic']['label'].",".$config['data']['dynamic']['value']." FROM ".$config['data']['dynamic']['table'];
                        $sql .= $config['data']['dynamic']['condition'] ? " WHERE ".$config['data']['dynamic']['condition'] : '';

                        $_options = $devdb->get_results($sql);
                        foreach($_options as $i=>$v){
                            $options[$v[$config['data']['dynamic']['value']]] = $v[$config['data']['dynamic']['label']];
                            }
                        }
                    }
                }
            if($options){
                foreach($options as $i=>$v){
                    $checked = $value ? (in_array($i,$value) ? 'checked' : '') : '';
                    $field .= '<label class="checkbox"><input class="px" type="checkbox" name="'.$f_name.'[]" value="'.$i.'" '.$checked.'><span class="lbl">'.$v.'</span></label>';
                    }
                }
            else $field .= '<p class="help-block text-danger">No Data</p>';
            $field .= '</div>';
            }
        elseif($config['type'] == 'select'){
            $field = '<div class="form-group form_element_holder select_holder select_holder_'.$id.'">';
            $field .= '<label>'.$config['label'].'</label>';
            $field .= '<select class="form-control" id="select_holder_'.$id.'" name="'.$f_name.'">';

            $options = array();
            $options = $config['data']['static'] ? $config['data']['static'] : array();

            if(!$options){
                if($config['data']['dynamic']){
                    if($config['data']['dynamic']['label'] && $config['data']['dynamic']['value']){
                        if(!isset($config['data']['dynamic']['sql'])){
                            $sql = "SELECT ".$config['data']['dynamic']['label'].",".$config['data']['dynamic']['value']." FROM ".$config['data']['dynamic']['table'];
                            $sql .= $config['data']['dynamic']['condition'] ? " WHERE ".$config['data']['dynamic']['condition'] : '';
                            }
                        else $sql = $config['data']['dynamic']['sql'];

                        $_options = $devdb->get_results($sql);

                        foreach($_options as $i=>$v){
                            $options[$v[$config['data']['dynamic']['value']]] = processToRender($v[$config['data']['dynamic']['label']]);
                            }
                        }
                    }
                }
            if($config['add_empty']){
                $temp = array('' => $config['add_empty']);
                $options = $temp + $options;
                }
            elseif(!$options['']){
                $temp = array(''=>'Select One');
                $options = $temp + $options;
                }
            if($options){
                foreach($options as $i=>$v){
                    $selected = $i == $value ? 'selected' : ($config['data']['default'] && $config['data']['default'] == $i ? 'selected' : '');
                    $field .= '<option value="'.$i.'" '.$selected.'>'.$v.'</option>';
                    }
                }
            else $field .= '<p class="help-block text-danger">No Data</p>';

            $field .= '</select>';
            }
        elseif($config['type'] == 'date'){
            //TODO::JS ERROR
            $field = '<div class="form-group form_element_holder date_holder date_holder_'.$id.'"><label>'.$config['label'].'</label><input id="date_holder_'.$id.'" type="text" name="'.$f_name.'" class="form-control" >';
            $field .= '<script type="text/javascript">
                        init.push(function(){
                            _datepicker("date_holder_'.$id.'");
                            });
                        </script>';
        }
        elseif($config['type'] == 'time'){
            //TODO::JS ERROR
            $field = '<div class="form_element_holder time_holder time_holder_'.$id.'"><label>'.$config['label'].'</label><input id="time_holder_'.$id.'" type="text" name="'.$f_name.'" class="pick_date" >';
            $field .= '<script type="text/javascript">
                        init.push(function() {
                        //init a datetimepicker
                           $( "#time_holder_'.$id.'" ).timepicker({
                                timeFormat: "HH:mm"

                            });
                         });
                        </script>';
        }
        elseif($config['type'] == 'datetime'){
            //TODO::JS ERROR
            $field = '<div class="form_element_holder datetime_holder datetime_holder_'.$id.'"><label>'.$config['label'].'</label><input id="datetime_holder_'.$id.'" type="text" name="'.$f_name.'" class="pick_date" >';
            $field .= '<script type="text/javascript">
                    init.push(function(){
                        $( "#datetime_holder_'.$id.'" ).datetimepicker({
                            dateFormat:"yy-mm-dd",
                            changeMonth: true,
                            changeYear: true,
                            timeFormat: "HH:mm"
                            });

                        });

                        </script>';
        }
        elseif($config['type'] == 'file'){
            $field = '
                    <div class="panel form_element_holder form-group file_holder file_holder_'.$id.'">
                        <div class="panel-heading"><span class="panel-title">'.$config['label'].'</span></div>
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="'._path('common_files').'/filemanager/dialog.php?type=1&field_id='.$id.'&relative_url=1&akey='.$_config['__FILEMANGER_KEY__'].'" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <img class="" src="'. ($value ? get_image($value,'100x100x1') : '').'" />
                                <input id="'.$id.'" name="'.$f_name.'" type="hidden" class="form-control" value="'.$value.'">
                            </div>
                        </div>
                    <!--/div-->
                    ';
            }
        else if($config['type'] == 'autocomplete'){
            $field = '<br/> <label>'.$config['label'].'</label><div class="form_element_holder autocomplete_holder autocomplete_holder_'.$id.'"><div class="added_items">';

            if($config['data']['dynamic']){
                if($config['data']['dynamic']['label'] && $config['data']['dynamic']['value'] && $config['data']['dynamic']['table']){
                    $sql = "SELECT ".$config['data']['dynamic']['label'].",".$config['data']['dynamic']['value']." FROM ".$config['data']['dynamic']['table'];
                    $sql .= $config['data']['dynamic']['condition'] ? " WHERE ".$config['data']['dynamic']['condition'] : '';

                    $_options = $devdb->get_results($sql);

                    foreach($_options as $i=>$v){
                        $options[$v[$config['data']['dynamic']['value']]] = $v[$config['data']['dynamic']['label']];
                    }
                }
            }
            //TODO::tagitems showing & autocomplete box doesnt work
            if(is_array($options)){
                foreach($options as $m=>$n){
                    $field .= '<span class="tagsItems">'.$n.'<input type="hidden" name="'.$f_name.'_values[]" value="'.$m.'"><span class="closeTag"><i class="icon-large icon-remove"></i></span></span>';
                }
            }

            $field .= '</div>';
            $field .= '<div class="tag_input_field">
					       <input type="text" id="'.$id.'" name="'.$f_name.'" value="" />
                        </div>';
            $field .= '</div>';
            $field .= '<script type="text/javascript">
                        init.push(function(){
                            set_autocomplete({
                            "ajax_page" : "'.$_SERVER['REQUEST_URI'].'",
                            "field" : "#'.$id.'",
                            "field_name" : "'.$f_name.'_values"
                                });
                            });
                        </script>';
        }
        elseif($config['type'] == 'tinymce'){

            $field = '<br/> <label>'.$config['label'].'</label><div class="form_element_holder tinymce_holder tinymce_holder_'.$id.'">';
            $field .= '<div>
					       <textarea id="textarea_holder_'.$id.'" name="'.$f_name.'">'.$value.'</textarea>
                        </div>';
            $field .= '<script type="text/javascript">
                init.push(function(){
                           tinymce.init({
    selector: "textarea#textarea_holder_'.$id.'",
	height:260,
    plugins: [
                "lists link image preview",
                "fullscreen",
                "insertdatetime media table responsivefilemanager"
            ],

	relative_urls: false,
    browser_spellcheck : true ,
    filemanager_title:"Filemanager",
    filemanager_crossdomain: true,
    external_filemanager_path:"filemanager/",

    external_plugins: { "filemanager" : "filemanager/plugin.min.js"},
    image_advtab: true,

    toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link responsivefilemanager fullscreen",
	menubar: false,
    toolbar_items_size: "medium",

	style_formats: [

		{title: "Blocks", items: [
			{title: "p", block: "p"},
			{title: "div", block: "div"}
		    ]},

		{title: "Image Alignment", items: [
			    {
                title: "Image Left",
				selector: "img",
				styles: {
                "float": "left",
					"margin": "0 10px 0 10px"
				    }
			    },
			    {
                 title: "Image Right",
				 selector: "img",
				 styles: {
                 "float": "right",
					 "margin": "0 0 10px 10px"
				    }
			    }
		    ]},

	    ]

	    });
    });

</script>';
        }

        if(in_array($config['type'],$groupTypes) === false){
            if($config['help']) $field .= '<p class="help-block">'.$config['help'].'</p></div>';
            else $field .= '</div>';
            }

        $width = $config['width'] ? $config['width'] : 4;
        if(isset($config['skip_wrapper']) && $config['skip_wrapper']){
            return $field;
            }
        else return '<div class="col-sm-'.$width.' '.($config['class'] ? $config['class'] : '').'">'.$field.'</div>';
        }

    public function process_form($form,$post_data,$is_update){
        global $devdb;
        $checked = '';
        $_data = array();

        $errors = array();

        foreach($form['fields'] as $i=>$v){
            $errors_found = false;
            if($v['validators']){
                foreach($v['validators'] as $m=>$n){
                    $temp = form_validator::$m($post_data[$i],$n);
                    if($temp !== true) $errors[] = $v['label'].' '.$temp;
                    $errors_found = true;
                }
            }
            if($errors_found) continue;

            if ($v['type'] == 'checkbox'){
                $checked = implode(MULTIVALUE_SEPARATOR,$post_data[$i]);
                $_data[$i]=$checked;
            }
            elseif($v['type'] == 'file') {
                if ($_FILES[$i]["name"]) {
                    $target_dir = _path('uploads','absolute')."/" . ($v['folder'] ? $v['folder'] . '/' : '');
                    $target_file = $target_dir . basename($_FILES[$i]["name"]);
                    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                    if (is_array($v['extensions']) && in_array($imageFileType, $v['extensions'])) {
                        if ($v['max_file_size'] && $_FILES[$i]["size"] <= $v['max_file_size']) {
                            if (!move_uploaded_file($_FILES[$i]["tmp_name"], $target_file)) {
                                $errors[] = $v['label'] . ': File was not uploaded, please try again.';
                            }
                            else {
                                $_data[$i] = $target_file;
                            }
                        }
                        else $errors[] = $v['label'] . ': <strong>' . $_FILES[$i]["size"] . ' B</strong> is more than supported file size [' . $v['max_file_size'] . ']';
                    }
                    else $errors[] = $v['label'] . ': <strong>.' . $imageFileType . '</strong> is not supported extension. Only supports .' . implode(', .', $v['extensions']);
                }
            }
            else
                $_data[$i] = $post_data[$i];
        }

        if(!$errors) {
            foreach($_data as $i=>$v){
                if(!$v) unset($_data[$i]);
            }

            $cond = $is_update ? " " . $form['primary'] . " = '" . $is_update . "'" : '';
            $ins_update = $devdb->insert_update($form['table'], $_data, $cond);
            $content_id = $is_update ? $is_update : $ins_update['success'];
            if($ins_update['success']){
                foreach($form['fields'] as $i=>$v){
                    //validation
                    if($v['type'] == 'autocomplete' && $form['table'] != $v['store_data']['table']){
                        if($is_update) {
                            //clear data from store; reference $is_update
                            $sql = "DELETE FROM ".$v['store_data']['table']." WHERE ".$v['autocomplete_column']." = '".$is_update."'";
                            $pre_deleted = $devdb->query($sql);
                        }
                        if($_POST[$i.'_values']){
                            foreach($_POST[$i.'_values'] as $m=>$n){
                                $auto_data = array(
                                    $v['store_data']['autocomplete_column'] => $n,
                                    $v['store_data']['content_column'] => $content_id
                                );
                                $auto_data_inserted = $devdb->insert_update($v['store_data']['table'],$auto_data);
                            }
                        }
                    }
                }
            }
        }
        else return array('error' => $errors);

        return $ins_update;
    }

    public function get_form_data($form,$id){
        if(!$id && !$form) return NULL;

        global $devdb;

        $sql = "SELECT ";
        $fields = '';
        //pulling data only from the Form Table
        foreach($form['fields'] as $i=>$v){
            if($v['type'] == 'autocomplete' && $form['table'] != $v['store_data']['table'])continue;
            $fields .= '`'.$i.'`, ';
        }
        $fields = rtrim($fields,", ");
        $sql .= $fields." FROM ".$form['table']." WHERE ".$form['primary']." = '".$id."'";

        $table_data = $devdb->get_row($sql);

        //now pulling relational data of the form fields while live on other related tables.
        foreach($form['fields'] as $i=>$v){
            if($v['store_data']){
                $sql = "SELECT ".$v['store_data']['autocomplete_column']." FROM ".$v['store_data']['table']." WHERE ".$v['store_data']['content_column']." = '".$id."'";
                $stored_data = $devdb->get_results($sql);
                //TODO: OPTIMIZE
                foreach($stored_data as $m=>$n){
                    $sql = "SELECT ".$v['data']['dynamic']['label']." FROM ".$v['data']['dynamic']['table']." WHERE ".$v['data']['dynamic']['value']." = '".$n[$v['store_data']['autocomplete_column']]."'";
                    $related_data = $devdb->get_row($sql);
                    $stored_data[$m] = array(
                        'label' => $related_data[$v['data']['dynamic']['label']],
                        'value' => $n[$v['store_data']['autocomplete_column']]
                    );
                }
                $table_data[$i] = $stored_data;
            }
        }
        return $table_data;
    }

    public function delete_form_data($form,$delete){
        global $devdb;
        $sql = "DELETE FROM ".$form['table']." WHERE ".$form['primary']." = '".$delete."'";
        $left_data = $devdb->query($sql);
        return $left_data;
    }

    public function ajax_handler($form){
        global $devdb;
        foreach($form['fields'] as $i=>$v){
            if($v['type'] == 'file'){
                if($_POST["ajax_type"] == "delete_file"):
                    if($_POST["file_name"]):
                        $_root = _path('uploads','absolute');
                        $_here = $_SERVER['SCRIPT_FILENAME'];
                        $_cut = str_replace($_root,'',$_here);
                        $temp = explode('/',$_cut);
                        $temp = unlink($_root.$temp[0].'/'.$_POST["file_name"]);
                        if($temp) echo "success";
                        else echo 'error';
                        exit();
                    endif;
                endif;
            }
            elseif($v['type'] == 'autocomplete'){
                if($_GET['field_name'] == $i){
                    $term = $_GET["term"];

                    $sql = "SELECT ".$v['data']['dynamic']['label'].",".$v['data']['dynamic']['value']." FROM ".$v['data']['dynamic']['table'] . " WHERE 1";
                    $sql .= $v['data']['dynamic']['condition'] ? " AND ".$v['data']['dynamic']['condition'] : '';

                    foreach($v['data']['dynamic']['search_columns'] as $m=>$n){
                        $sql .= " AND ".$n." LIKE '%".$term."%'";
                    }
                    $_options = $devdb->get_results($sql);
                    /* if($devdb->num_rows==0){
                         $tags=array(
                             $v['data']['dynamic']['label']=$term,
                             );

                        $add_tags = $devdb->insert_update($v['data']['dynamic']['table'],$tags);
                         }*/
                    $json = array();

                    foreach($_options as $opt){
                        $json[] = array(
                            'value' => $opt[$v['data']['dynamic']['label']],
                            'label' => $opt[$v['data']['dynamic']['label']],
                            'id' => $opt[$v['data']['dynamic']['value']]
                        );
                    }

                    echo json_encode($json);
                    exit();
                }
            }
        }
    }
}
$formPro = new formProcessor;
/*************************************************************/

/*
 * Image Class
 * */
class ImageManipulator
{
    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var resource
     */
    protected $image;

    /**
     * Image manipulator constructor
     *
     * @param string $file OPTIONAL Path to image file or image data as string
     * @return void
     */
    public function __construct($file = null)
    {
        if (null !== $file) {
            if (is_file($file)) {
                $this->setImageFile($file);
            } else {
                $this->setImageString($file);
            }
        }
    }

    /**
     * Set image resource from file
     *
     * @param string $file Path to image file
     * @return ImageManipulator for a fluent interface
     * @throws InvalidArgumentException
     */
    public function setImageFile($file)
    {
        if (!(is_readable($file) && is_file($file))) {
            throw new InvalidArgumentException("Image file $file is not readable");
        }

        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }

        list ($this->width, $this->height, $type) = getimagesize($file);

        switch ($type) {
            case IMAGETYPE_GIF  :
                $this->image = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG :
                $this->image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG  :
                $this->image = imagecreatefrompng($file);
                break;
            default             :
                throw new InvalidArgumentException("Image type $type not supported");
        }

        return $this;
    }

    /**
     * Set image resource from string data
     *
     * @param string $data
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function setImageString($data)
    {
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }

        if (!$this->image = imagecreatefromstring($data)) {
            throw new RuntimeException('Cannot create image from data string');
        }
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        return $this;
    }

    /**
     * Resamples the current image
     *
     * @param int  $width                New width
     * @param int  $height               New height
     * @param bool $constrainProportions Constrain current image proportions when resizing
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function resample($width, $height, $constrainProportions = true)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }
        if ($constrainProportions) {
            if ($this->height >= $this->width) {
                $width  = round($height / $this->height * $this->width);
            } else {
                $height = round($width / $this->width * $this->height);
            }
        }
        $temp = imagecreatetruecolor($width, $height);
        imagealphablending($temp, false);
        imagesavealpha($temp,true);
        $transparent = imagecolorallocatealpha($temp, 255, 255, 255, 127);
        imagefilledrectangle($temp, 0, 0, $width, $height, $transparent);
        imagecopyresampled($temp, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        return $this->_replace($temp);
    }

    /**
     * Enlarge canvas
     *
     * @param int   $width  Canvas width
     * @param int   $height Canvas height
     * @param array $rgb    RGB colour values
     * @param int   $xpos   X-Position of image in new canvas, null for centre
     * @param int   $ypos   Y-Position of image in new canvas, null for centre
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function enlargeCanvas($width, $height, array $rgb = array(), $xpos = null, $ypos = null)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }


        $width = max($width, $this->width);
        $height = max($height, $this->height);

        $temp = imagecreatetruecolor($width, $height);
        if (count($rgb) == 3) {
            $bg = imagecolorallocate($temp, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($temp, 0, 0, $bg);
        }

        if (null === $xpos) {
            $xpos = round(($width - $this->width) / 2);
        }
        if (null === $ypos) {
            $ypos = round(($height - $this->height) / 2);
        }

        imagecopy($temp, $this->image, (int) $xpos, (int) $ypos, 0, 0, $this->width, $this->height);
        return $this->_replace($temp);
    }

    /**
     * Crop image
     *
     * @param int|array $x1 Top left x-coordinate of crop box or array of coordinates
     * @param int       $y1 Top left y-coordinate of crop box
     * @param int       $x2 Bottom right x-coordinate of crop box
     * @param int       $y2 Bottom right y-coordinate of crop box
     * @return ImageManipulator for a fluent interface
     * @throws RuntimeException
     */
    public function crop($x1, $y1 = 0, $x2 = 0, $y2 = 0)
    {
        if (!is_resource($this->image)) {
            throw new RuntimeException('No image set');
        }
        if (is_array($x1) && 4 == count($x1)) {
            list($x1, $y1, $x2, $y2) = $x1;
        }

        $x1 = max($x1, 0);
        $y1 = max($y1, 0);

        $x2 = min($x2, $this->width);
        $y2 = min($y2, $this->height);

        $width = $x2 - $x1;
        $height = $y2 - $y1;

        $temp = imagecreatetruecolor($width, $height);
        imagealphablending($temp, false);
        imagesavealpha($temp,true);
        $transparent = imagecolorallocatealpha($temp, 255, 255, 255, 127);
        imagefilledrectangle($temp, 0, 0, $width, $height, $transparent);
        imagecopy($temp, $this->image, 0, 0, $x1, $y1, $width, $height);

        return $this->_replace($temp);
    }

    /**
     * Replace current image resource with a new one
     *
     * @param resource $res New image resource
     * @return ImageManipulator for a fluent interface
     * @throws UnexpectedValueException
     */
    protected function _replace($res)
    {
        if (!is_resource($res)) {
            throw new UnexpectedValueException('Invalid resource');
        }
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }
        $this->image = $res;
        $this->width = imagesx($res);
        $this->height = imagesy($res);
        return $this;
    }

    /**
     * Save current image to file
     *
     * @param string $fileName
     * @return void
     * @throws RuntimeException
     */
    public function save($fileName, $type = IMAGETYPE_JPEG, $quality)
    {
        $dir = dirname($fileName);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new RuntimeException('Error creating directory ' . $dir);
            }
        }
        $quality = $quality > 100 && $quality < 0 ? 85 : $quality;
        try {
            switch ($type) {
                case IMAGETYPE_GIF  :
                    if (!imagegif($this->image, $fileName)) {
                        throw new RuntimeException;
                    }
                    break;
                case IMAGETYPE_PNG  :
                    $quality = round(9 - (($quality/100)*9));
                    if (!imagepng($this->image, $fileName,$quality)) {
                        throw new RuntimeException;
                    }
                    break;
                case IMAGETYPE_BMP  :
                    if (!imagewbmp($this->image, $fileName)) {
                        throw new RuntimeException;
                    }
                    break;
                case IMAGETYPE_JPEG :
                default             :
                    if (!imagejpeg($this->image, $fileName, $quality)) {
                        throw new RuntimeException;
                    }
            }
        } catch (Exception $ex) {
            throw new RuntimeException('Error saving image file to ' . $fileName);
        }
    }

    /**
     * Returns the GD image resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->image;
    }

    /**
     * Get current image resource width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get current image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }
}

function find_size($size,$checkMobile = true){
    if(!$size) return null;

    global $_config;

    $s_parts = explode('x',$size);

    /*
     * 0 = width
     * 1 = height
     * 2 = image_cropping_mode
     *      1:  Crops the source image to the desired width and height from center of the source image
     *          If the source image is smaller than the desired, the source image will be resized
     *          Note: If desired height is 0, the result image will have desired width and height will be responsive to aspect ration
     *          Note: If desired width is 0, the result image will have desired height and width will be responsive to aspect ration
     *      2:  In this case the desired height and width are considered as upper limits. The image will be resized to a size that is contented in these upper limites.
     *          Resizes the source image to fit on an image of desired width and height
     *          The resized source image will be placed at the center while vertically in middle
     *          For example, your source image is 1280x720, desired image size is 200x200
     *          The source will be resized to 200x112.5. The first preference when resizing is to resize the source width to the desired width only if the resulting height is less than or equal to the desired height. The resulting image will be placed vertially middle on the container image.
     *          However, if the resizing the source width to the desired width results in a resized height which is greater than the desired height, the preference will change to height. That is,
     *          First the source height will be resized to desired height and the resulting resized width will be less than the desired width automatically, the resulting image will be placed in center of the container image
     *      3:  This is same as 2 except that the image will not be placed on a container image
     * 3 = quality
     * 4 = HEX value (without hash) of Image BG Color in case of image_cropping_mode = 2, defualt FFFFFF
     * */

    if($checkMobile && isMobile() && $s_parts[0] > 100 && $s_parts[1] > 100){
        $s_parts[0] = (85/100)*$s_parts[0];
        $s_parts[1] = (85/100)*$s_parts[1];
    }

    if(!isset($s_parts[2]) || $_config['image_cropping_mode_force'] == 'yes') $s_parts[2] = $_config['image_cropping_mode'];
    if(!isset($s_parts[3])) $s_parts[3] = 90;
    if($_config['image_quality']) $s_parts[3] = $_config['image_quality'];

    if(!isset($s_parts[4])) $s_parts[4] = isset($_config['image_bg_color']) ? str_replace('#','',$_config['image_bg_color']) : 'FFFFFF';

    return implode('x',$s_parts);
}

function get_image($image, $size = NULL, $path_folder = 'uploads', $save_dir = null, $alternatives = array(), $force_max_width = null){
    global $_config;

    if(!$path_folder) $path_folder = 'uploads';

    $org_image = $image;

    if(!$image){
        if(!$alternatives){
            if($_config['defaultNoImage'])
                $image = $_config['defaultNoImage'];
            else{
                $path_folder = 'common_files/css/images';
                if(file_exists(_path($path_folder,'absolute').'/noImage.png'))
                    $image = 'noImage.png';
                else return NULL;
                }

            }
        else{
            $image = $alternatives;
            }
        }

    $path = _path($path_folder,'absolute');

    $image = '/'.$image;

    $abs_image = $path.$image;

    $abs_image = rtrim($abs_image,'/');

    if(!file_exists($abs_image)) return '';

    $size = find_size($size);

    list($desired_width,$desired_height,$mode,$quality,$bgColor) = explode('x',$size);
    if(!strlen($bgColor)) $bgColor = '#ffffff';
    if(!$size || !$mode){
        return _path($path_folder).$image;
    }

    if(strpos($abs_image,' ')){
        $abs_new_image = str_replace(' ','',$abs_image);
        if(!file_exists($abs_new_image)){
            copy($abs_image,$abs_new_image);
        }
        $image = str_replace($path,'',$abs_new_image);
        $abs_image = $abs_new_image;
    }

    if(!$force_max_width && file_exists(_path('contents','absolute').'/cropped/'.$size.$image))
        return _path('contents').'/cropped/'.$size.$image;

    $manipulator = new ImageManipulator($abs_image);

    if(!$desired_height && $desired_width) $desired_height = ($manipulator->getHeight()/$manipulator->getWidth()) * $desired_width;
    elseif(!$desired_width && $desired_height) $desired_width = ($manipulator->getWidth()/$manipulator->getHeight()) * $desired_height;
    elseif(!$desired_width && !$desired_height){
        return get_image($org_image, implode('x', array($manipulator->getWidth(), $manipulator->getHeight(), $mode, $quality, $bgColor)), $path_folder, $save_dir, $alternatives, $force_max_width);
        }
    //pre($image,0);
    //pre($manipulator->getWidth(),0);
    //pre($manipulator->getHeight(),0);
    //pre($desired_width,0);
    //pre($desired_height,0);
    //pre($mode,0);
    $reSampleSize = image_resize_calc($manipulator->getWidth(),$manipulator->getHeight(),$desired_width,$desired_height,$mode);

    if($force_max_width){
        $desired_height = ceil(($desired_height/$desired_width)*$force_max_width);
        $desired_width = $force_max_width;

        return get_image($org_image, implode('x', array($desired_width, $desired_height, $mode, $quality, $bgColor)), $path_folder, $save_dir, $alternatives, null);
        }

    $newImage = $manipulator->resample($reSampleSize['w'], $reSampleSize['h'], false);

    if($mode == 1){
        //$newImage = $manipulator->resample($reSampleSize['w'], $reSampleSize['h'], false);
        $width  = $manipulator->getWidth();
        $height = $manipulator->getHeight();

        $centreX = round($width / 2);
        $centreY = round($height / 2);

        $x1 = $centreX - floor($desired_width/2);
        $y1 = $centreY - floor($desired_height/2);

        $x2 = $centreX + floor($desired_width/2);
        $y2 = $centreY + floor($desired_height/2);


        $newImage = $manipulator->crop($x1, $y1, $x2, $y2);
        }

    if(!file_exists(_path('contents','absolute').'/cropped/'.$size))
        mkdir(_path('contents').'/cropped/'.$size);

    $ext = strtolower(getExtension(_path('contents','absolute').'/cropped/'.$size.$image));
    if($ext == 'png') $ext = IMAGETYPE_PNG;
    elseif($ext == 'jpg' || $ext == 'jpeg') $ext = IMAGETYPE_JPEG;
    elseif($ext == 'gif') $ext = IMAGETYPE_GIF;
    elseif($ext == 'bmp') $ext = IMAGETYPE_BMP;

    $manipulator->save(_path('contents','absolute').'/cropped/'.$size.$image, $ext, $quality);

    if($mode == 2){
        $saveDir = _path('contents','absolute').'/cropped/'.$size.$image;
        create_image(_path('contents','absolute').'/cropped/'.$size.$image,$desired_width,$desired_height,$saveDir,$bgColor);
    }

    return _path('contents').'/cropped/'.$size.$image;
}

function get_og_image($image, $size){
    if(!$image) return NULL;

    global $paths;

    $image = '/'.$image;

    $_image = $paths['absolute']['uploads'].$image;

    $_image = rtrim($_image,'/');

    if(!file_exists($_image)) return '';

    $size = find_size($size, false);

    list($o_width,$_height,$mode,$quality,$bgColor) = explode('x',$size);
    if(!strlen($bgColor)) $bgColor = '#ffffff';

    if(!$size || !$mode){
        return _path('uploads').$image;
    }

    if(strpos($_image,' ')){
        $abs_new_image = str_replace(' ','',$_image);
        if(!file_exists($abs_new_image)){
            copy($_image,$abs_new_image);
        }
        $image = str_replace($paths['absolute']['uploads'],'',$abs_new_image);
        $_image = $abs_new_image;
    }

    $image_name = str_replace(array('/media','/news'),'',$image);
    $image_name = str_replace(' ','',$image_name);

    if(file_exists($paths['absolute']['root'].'/og_image'.$image_name))
        return $paths['relative']['root'].'/og_image'.$image_name;

    list($o_width,$_height) = explode('x',$size);

    $manipulator = new ImageManipulator($_image);

    if($_height == 0) $_height = ($manipulator->getHeight()/$manipulator->getWidth()) * $o_width;

    if($o_width == 0) $o_width = ($manipulator->getWidth()/$manipulator->getHeight()) * $_height;

    $resample_size = image_resize_calc($manipulator->getWidth(),$manipulator->getHeight(),$o_width,$_height,$mode);

    $newImage = $manipulator->resample($resample_size['w'], $resample_size['h']);

    $width  = $manipulator->getWidth();
    $height = $manipulator->getHeight();
    $centreX = round($width / 2);
    $centreY = round($height / 2);
    $x1 = $centreX - floor($o_width/2);
    $y1 = $centreY - floor($_height/2);

    $x2 = $centreX + floor($o_width/2);
    $y2 = $centreY + floor($_height/2);

    $newImage = $manipulator->crop($x1, $y1, $x2, $y2);

    $ext = strtolower(getExtension($paths['absolute']['root'].'/og_image'.$image_name));
    if($ext == 'png') $ext = IMAGETYPE_PNG;
    elseif($ext == 'jpg' || $ext == 'jpeg') $ext = IMAGETYPE_JPEG;
    elseif($ext == 'gif') $ext = IMAGETYPE_GIF;
    elseif($ext == 'bmp') $ext = IMAGETYPE_BMP;

    $manipulator->save($paths['absolute']['root'].'/og_image'.$image_name,$ext,100);

    return $paths['relative']['root'].'/og_image'.$image_name;
}

function create_image($sourceFile,$targetWidth,$targetHeight, $saveDir, $bgColor){
    if(!strlen($bgColor)) $bgColor = '#ffffff';
    list($r,$g,$b) = hex2rgb($bgColor,false);
    $ext = strtolower(getExtension($sourceFile));
    $name = getFilename($sourceFile);
    $fullname = $name.'.'.$ext;
    $fullsaveDir = $saveDir.$fullname;

    if($ext == 'png')
        $sourceImg = imagecreatefrompng($sourceFile);
    elseif($ext == 'jpg' || $ext == 'jpeg')
        $sourceImg = imagecreatefromjpeg($sourceFile);
    elseif($ext == 'gif')
        $sourceImg = imagecreatefromgif($sourceFile);
    elseif($ext == 'bmp')
        $sourceImg = imagecreatefromwbmp($sourceFile);
    else return false;

    $targetImg = imagecreatetruecolor($targetWidth,$targetHeight);
    $background = imagecolorallocate($targetImg,$r,$g,$b);
    //$black = imagecolorallocate($targetImg, 0, 0, 0);
    imagefill($targetImg,0,0,$background);
    //imagecolortransparent($targetImg, $black);

    list($width, $height, $type, $attr)= getimagesize($sourceFile);
    $posX = ($targetWidth - $width)/2;
    $posY = ($targetHeight - $height)/2;

    //imagecopymerge($targetImg,$sourceImg,$posX,$posY,0,0,$width,$height,100);
    imagecopy($targetImg,$sourceImg,$posX,$posY,0,0,$width,$height);

    if($ext == 'png')
        return imagepng($targetImg,$saveDir);
    elseif($ext == 'jpg' || $ext == 'jpeg')
        return imagejpeg($targetImg,$saveDir,100);
    elseif($ext == 'gif')
        return imagegif($targetImg,$saveDir);
    elseif($ext == 'bmp')
        return imagewbmp($targetImg,$saveDir);

    return true;
}

function image_resize_calc($img_width,$img_height,$desired_width,$desired_height,$option=1){
    if($option == 1){
        //cropping and resizing
        //Keeping aspect ratio
        $new_w = ($img_width * $desired_height) / $img_height;
        $new_h = ($img_height * $desired_width) / $img_width;

        if(($desired_width - $new_w) < ($desired_height - $new_h)){
            $arr = array(
                'w' => $new_w,
                'h' => $desired_height,
            );
            return	$arr;
        }
        else{
            $arr = array(
                'w' => $desired_width,
                'h' => $new_h,
            );
            return $arr;
        }
    }
    elseif($option == 2 || $option == 3) {//resizing
        //first less down the image width to check the height
        $new_w = $desired_width;
        $new_h = floor(($img_height / $img_width) * $new_w);

        if ($new_w <= $desired_width && $new_h <= $desired_height)
            return array('w'=>$new_w,'h'=>$new_h);
        //img width is already less desired width
        //thus lets check the image height
        $new_h = $desired_height;
        $new_w = floor(($img_width / $img_height) * $new_h);

        if ($new_w <= $desired_width && $new_h <= $desired_height)
            return array('w'=>$new_w,'h'=>$new_h);
        }
    elseif($option == 3.1){
        $new_w = $desired_width;
        $new_h = floor(($img_height / $img_width) * $new_w);
        return array('w'=>$new_w,'h'=>$new_h);
        }
    elseif($option == 3.2){
        $new_h = $desired_height;
        $new_w = floor(($img_width / $img_height) * $new_h);
        return array('w'=>$new_w,'h'=>$new_h);
        }
    }
/*******************************************************/

/*
 * Header Footer Class
 * */
class header_manager{
    var $js = array();
    var $css = array();

    public function load_js($items=array(), $for){
        $count = isset($this->js[$for]) ? count($this->js[$for]) : 0;
        foreach($items as $i=>$v){
            $this->js[$for][$count++] = $v;
        }
    }
    public function load_css($items=array(), $for){
        $count = isset($this->css[$for]) ? count($this->css[$for]) : 0;
        foreach($items as $i=>$v){
            $this->css[$for][$count++] = $v;
        }
    }
}

$header_manager = new header_manager;

function get_header($for = 'public'){
    global $header_manager;

    $output = "";

    if($header_manager->css[$for]){
        foreach($header_manager->css[$for] as $i=>$v){
            $output .= '<link href="'.$v.'" type="text/css" rel="stylesheet">';
        }
    }

    if($header_manager->js[$for]){
        foreach($header_manager->js[$for] as $i=>$v){
            if(is_callable($v)) $output .= $v();
            else $output .= '<script type="text/javascript" src="'.$v.'"></script>';
        }
    }

    $output .= get_meta();

    echo $output;
    return NULL;
}

function load_js($items=array(), $for = 'public'){
    global $header_manager;
    $header_manager->load_js($items, $for);
}

function load_css($items=array(),$for = 'public'){
    global $header_manager;
    $header_manager->load_css($items, $for);
}

function get_meta(){
    global $_config;

    $og_image = '';
    $shortlink = false;
    $cannonical = getCanonical();
    $cannonical = $cannonical ? url(ltrim($cannonical,'/')) : null;
    $page_keywords = $_config['current_page']['page_meta_keyword'] ? explode(',', $_config['current_page']['page_meta_keyword']) : array();
    $content_keywords = $_config['current_content']['content_meta_keyword'] ? explode(',',processToRender($_config['current_content']['content_meta_keyword'])) : array();
    $website_keywords = $_config['website_keywords'] ? explode(',',processToRender($_config['website_keywords'])) : array();

    $keywords = implode(',',array_merge($content_keywords,$page_keywords,$website_keywords));
    if(mb_strlen($_config['current_content']['content_meta_description']))
        $description = processToRender($_config['current_content']['content_meta_description']);
    elseif(mb_strlen($_config['current_content']['content_excerpt']))
        $description = processToRender($_config['current_content']['content_excerpt']);
    elseif(mb_strlen($_config['current_page']['page_meta_description']))
        $description = $_config['current_page']['page_meta_description'];
    elseif(mb_strlen($_config['current_page']['page_excerpt']))
        $description = $_config['current_page']['page_excerpt'];
    else $description = processToRender($_config['website_description']);

    if($_config['current_content']){
        $og_image = $_config['current_content']['content_thumbnail'] ? get_og_image($_config['current_content']['content_thumbnail'],'1200x630') : '';
        if(!$_config['url_parameters']['content_slug'])
            $shortlink = _path('root').'/'.$_config['url_parameters']['page'].(!DO_NOT_USE_CONTENT_TYPE_IN_URL ? '/'.$_config['url_parameters']['content_type'] : '').'/'.$_config['url_parameters']['content_id'];
        else $shortlink = null;
        }
    elseif($_config['current_page']){
        if($_config['current_page']['og_image'])
            $og_image = $_config['current_page']['og_image'];
        else $og_image = $_config['current_page']['page_thumbnail'] ? get_og_image($_config['current_page']['page_thumbnail'],'1200x630') : '';
        }
    $pageTitle = generate_page_title();
    $ret_meta = '';
    doAction('addHeadMeta');
    $ret_meta .= (strlen($_config['fb_app_id']) ? '<meta property="fb:app_id" content="'.$_config['fb_app_id'].'" />'."\n" : '');
    $ret_meta .= '<meta name="keywords" content="'.$keywords.'"/>'."\n";
    $ret_meta .= '<meta name="description" content="'.$description.'"/>'."\n";
    //Twitter Card data
    $ret_meta .= '<meta name="twitter:card" content="summary">'."\n";
    if($_config['twitter_link'])
        $ret_meta .= '<meta name="twitter:site" content="'.$_config['twitter_link'].'">'."\n";
    $ret_meta .= '<meta name="twitter:title" content="'.$pageTitle.'">'."\n";
    $ret_meta .= '<meta name="twitter:description" content="'.$description.'">'."\n";
    //$ret_meta .= '<meta name="twitter:creator" content="@author_handle">'."\n";
    //Twitter Summary card images must be at least 120x120px
    $ret_meta .= '<meta property="twitter:image" content="'.($og_image ? $og_image : ($_config['default_share_image'] ? get_og_image($_config['default_share_image'],'1200x630') : '')).'"/>'."\n";

    //FB SHARE META
    $ret_meta .= '<meta property="og:title" content="'.generate_page_title().'"/>'."\n";
    $ret_meta .= '<meta property="og:site_name" content="'.processToRender($_config['site_name']).'"/>'."\n";
    $ret_meta .= '<meta property="og:description" content="'.$description.'"/>'."\n";
    $ret_meta .= '<meta property="og:url" content="'.($cannonical ? $cannonical : current_url()).'"/>'."\n";
    $ret_meta .= '<meta property="og:image" content="'.($og_image ? $og_image : ($_config['default_share_image'] ? get_og_image($_config['default_share_image'],'1200x630') : '')).'"/>'."\n";
    if($cannonical)
        $ret_meta .= '<link rel="canonical" href="'.$cannonical.'">'."\n";

    $ret_meta .= ($shortlink ? '<link rel="shortlink" href="'.$shortlink.'" />' : '');
    //$ret_meta .= '<meta property="og:locale" content="en_US" />';

    return $ret_meta;
    }
function getCanonical(){
    $url = $_SERVER['REQUEST_URI'];
    $parts = explode('?',$url);
    $cur_url = $parts[0];
    $q = @$parts[1] ? $parts[1] : NULL;
    //echo '<!-- '.$cur_url.' --><br />';
    //echo '<!-- '.$q.' -->';
    $lastChar = mb_substr($cur_url, -1);
    $needCanonical = false;
    global $_config;
    //pre($url);
    if($_config['current_content']){
        if($_config['url_parameters']['content_slug']){
            if($lastChar == '/'){
                $url = rtrim($url, "/");
                return $url.(mb_strlen($q) ? '/?'.$q : '');
            }
            else null;
        }
        else{
            if($lastChar != '/') $url .= '/';
            return $url.$_config['current_content']['content_slug'].(mb_strlen($q) ? '/?'.$q : '');
        }
    }
    if($lastChar == '/'){
        $url = rtrim($url, "/");
        return $url.(mb_strlen($q) ? '?'.$q : '');
    }
    return  null;
}
function generate_page_title(){
    global $_config;
    $siteName = processToRender($_config['site_name']);
    $page_title = $siteName;
    $title = '';
    if($_config['current_content']) $title = processToRender($_config['current_content']['content_title']);
    elseif($_config['current_page']){
        if(isset($_config['url_parameters']['content_type']) && $_config['url_parameters']['content_type'])
            $title = processToRender($_config['current_page']['page_title']) . ' | ' . $_config['url_parameters']['content_type'];
        else $title = processToRender($_config['current_page']['page_title']);
        }

    if($_config['add_page_title'] == 'yes' && $title){
        if($_config['site_page_title_placement'] == 1) $page_title = $siteName .($_config['site_page_title_separator'] ? ' '.$_config['site_page_title_separator'].' ' : ' | '). $title;
        elseif($_config['site_page_title_placement'] == 2) $page_title = $title .($_config['site_page_title_separator'] ? ' '.$_config['site_page_title_separator'].' ' : ' | '). $siteName;
        }

    return $page_title;
    }

class footer_manager{
    var $js = array();
    var $css = array();

    public function load_js($items=array(), $for){
        $count = isset($this->js[$for]) ? count($this->js[$for]) : 0;
        foreach($items as $i=>$v){
            $this->js[$for][$count++] = $v;
        }
    }
    public function load_css($items=array(), $for){
        $count = isset($this->css[$for]) ? count($this->css[$for]) : 0;
        foreach($items as $i=>$v){
            $this->css[$for][$count++] = $v;
        }
    }
}

$footer_manager = new footer_manager;

function get_footer($for = 'public'){
    global $footer_manager;
    $output = "";
    if($footer_manager->css[$for]){
        foreach($footer_manager->css[$for] as $i=>$v){
            $output .= '<link href="'.$v.'" type="text/css" rel="stylesheet">';
        }
    }
    if($footer_manager->js[$for]){
        foreach($footer_manager->js[$for] as $i=>$v){
            if(is_callable($v)) $output .= $v();
            else $output .= '<script type="text/javascript" src="'.$v.'"></script>';
        }
    }

    echo $output;
    return NULL;
}

function load_js_footer($items=array(),$for = 'public'){
    global $footer_manager;
    $footer_manager->load_js($items,$for);
}

function load_css_footer($items=array(),$for = 'public'){
    global $footer_manager;
    $footer_manager->load_css($items, $for);
}
/***************************************************/

/*
 * Admin Widget Class
 * */

class adminWidgets {
    private static $instance = 0;
    private $default_widget_position = 'dashboard';
    var $widgets = array();
    var $total_widgets = array();
    var $output = null;
    var $widgets_by_size = array();
    var $adminWidgetPositions = array();

    public function __construct(){
        if(self::$instance)
            throw new Exception('Can not create another instance of this class.');

        self::$instance++;

        load_js_footer(array(
            function(){
                ob_start();
                ?>
                <script type="text/javascript">
                    function adminWidgetRefresh(opts){
                        var config = {
                            widgetId : null,
                            functionName: null,
                            onSuccess : null,
                            onError: null,
                            beforeSend: null,
                            onComplete: null,
                            args: {}
                        };
                        $.extend(true,config,opts);
                        if(!config.widgetId) return null;

                        config.args['widget'] = config.widgetId;
                        if(config.functionName) config.args['function'] = config.functionName;

                        var $data = {args : config.args};

                        $.ajax({
                            url : _root_path_+'/api/dev_administration/adminWidgetRefresh?internalToken='+_internalToken_,
                            type: 'post',
                            dataType: 'json',
                            data: $data,
                            cache: false,
                            beforeSend: function(){
                                if(config.beforeSend && typeof config.beforeSend == 'function')
                                    config.beforeSend();
                            },
                            complete: function(){
                                if(config.onComplete && typeof config.onComplete == 'function')
                                    config.onComplete();
                            },
                            success: function(data){
                                if(config.onSuccess && typeof config.onSuccess == 'function')
                                    config.onSuccess(data);
                            },
                            error: function(){
                                if(config.onError && typeof config.onError == 'function')
                                    config.onError();
                            }
                        });
                    }
                </script>
                <?php
                $data = ob_get_clean();
                return $data;
            }
        ),'admin');
    }
    function register_widget($data){
        /*
         * widget_id = ID of the widget
         * jack_object = Reference object of the jack
         * render_action = Function of the jack's class to render the widget
         * refresh_action = A function that can be called through AJAX to refresh the data of the widget
         * widget_title = title of the widget
         * widget_size = 3,4,6,12
         * widget_position = PositionID
         * add_permission = true | false
         * container = true | false
         * register_api = true | false - If true, the render_function will be registered as an api of the jack_object
         * container_padding = true | false - If false and container true, then the panel-body that holds widget data will have no padding
         * */
        if(!$data['widget_position']) $data['widget_position'] = $this->default_widget_position;
        if(!$data['widget_size']) $data['widget_size'] = 6;
        if(!isset($data['add_permission'])) $data['add_permission'] = true;
        if(!isset($data['container'])) $data['container'] = true;
        if(!isset($data['register_api'])) $data['register_api'] = false;
        if(!isset($data['container_padding'])) $data['container_padding'] = true;

        $data['class_name'] = get_class($data['jack_object']);

        if(!isset($this->widgets[$data['widget_position']][$data['widget_size']][$data['widget_id']])){
            $this->widgets[$data['widget_position']][$data['widget_size']][$data['widget_id']] = $data;
            $this->total_widgets[$data['widget_position']]++;
            $this->widgets_by_size[$data['widget_position']][] = $data['widget_size'];
            if(isset($data['add_permission']) && $data['add_permission']){
                $args = array(
                    'group_name' => 'Widgets',
                    'permissions' => array(
                        'hide_'.$data['widget_id'] => 'Hide '.$data['widget_title'],
                    ),
                );
                register_permission($args);
            }
            if($data['register_api']) apiRegister($data['jack_object'], $data['render_action']);
        }
    }
    function render_widgets($widget = null, $widget_param = null, $widget_position = null){
        global $_config;
        if(!$widget_position) $widget_position = $this->default_widget_position;
        $myId = '';
        if($widget){
            /*
             * Extra parameter you can send in an array in widget_id
             * use widget_id index for actual widget id and use the following indexes for other purpose
             *
             * widget_size = 2,4,6,12 | This will replace the default widget size
             * */
            if(!is_array($widget)) $widget = array(
                'widget_id' => $widget
            );

            $w = '';
            foreach($this->widgets[$widget_position] as $i=>$v){
                foreach($v as $m=>$n){
                    if($m == $widget['widget_id']){
                        $w = $n;
                        break;
                    }
                }
            }

            $widget_size = $widget['widget_size'] ? $widget['widget_size'] : $w['widget_size'];

            $output = '';
            ob_start();
            ?>
            <div class="<?php echo $w['widget_id'] ?> col-sm-<?php echo $widget_size?>">
                <?php
                $jackObject = $w['jack_object'];
                $widgetRenderFunction = $w['render_action'];
                if($widget_param) $jackObject->$widgetRenderFunction($widget_param);
                else $jackObject->$widgetRenderFunction();
                ?>
            </div>
            <?php
            $output = ob_get_clean();
        }
        else{
            $copy_of_widgets = $this->widgets[$widget_position];
            $widget_default_order = $this->customize_widgets($this->widgets_by_size[$widget_position]);
            $output = '';

            if($widget_default_order){
                foreach($widget_default_order as $i=>$v){
                    $temp_output = '';
                    if(is_array($v) && $v) {
                        foreach ($v as $m => $n) {
                            if ($copy_of_widgets[$n]) {
                                foreach ($copy_of_widgets[$n] as $widget_id => $widget_data) {
                                    if(!isset($_config['adminWidgets'][$widget_id])) continue;
                                    if(isset($widget_data['add_permission']) && $widget_data['add_permission']){
                                        if(has_permission('hide_'.$widget_id,true)) continue;
                                    }
                                    $myId = $widget_data['class_name'].'|'.$widget_data['widget_position'].'|'.$widget_data['widget_size'].'|'.$widget_data['widget_id'];
                                    $argsForWidget = array(
                                        'myId' => $myId,
                                    );
                                    ob_start();
                                    ?>
                                    <div data-myId="<?php echo $myId; ?>" data-id="<?php echo $widget_id;?>" class=" dashboard_widget <?php echo $widget_data['widget_id']?> col-sm-<?php echo $n ?>">
                                        <?php if(getProjectSettings('features,dashboard_widget_sortable')): ?><div class="dashboardWidgetSortHandle"></div><?php endif; ?>
                                        <?php if($widget_data['container']): ?>
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <span class="panel-title"><?php echo $widget_data['widget_title']?></span>
                                            </div>
                                            <div class="panel-body <?php echo !$widget_data['container_padding'] ? 'p0' : '';?>">
                                        <?php endif; ?>
                                                <?php
                                                $jackObject = $widget_data['jack_object'];
                                                $widgetRenderFunction = $widget_data['render_action'];
                                                $jackObject->$widgetRenderFunction($argsForWidget);
                                                ?>
                                        <?php if($widget_data['container']): ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    $temp_output .= ob_get_clean();
                                    unset($copy_of_widgets[$n][$widget_id]);
                                    break;
                                }
                            }
                        }
                    }
                    if(strlen($temp_output)) $temp_output .= '<div class="oh cb"></div>';

                    $output .= $temp_output;
                }
            }
        }
        return $output;
    }
    function refresh_widget($widget,$args = array()){
        list($widget_class, $widget_position,$widget_size,$widget_id) = explode('|', $widget);

        global $jacker;

        if(isset($this->widgets[$widget_position][$widget_size][$widget_id]))
            $widget_data = $this->widgets[$widget_position][$widget_size][$widget_id];
        else return 'Widget Data Not Found';

        $func = $widget_data['refresh_action'];

        if(is_array($widget_data['refresh_action'])){
            if(isset($args['functionName']) && $args['functionName'] && in_array($args['functionName'], $widget_data['refresh_action']))
                $func = $args['functionName'];
            else $func = null;
        }
        else $func = $widget_data['refresh_action'];

        if(!$func) return 'Invalid function called.';

        if(method_exists($widget_data['jack_object'],$func))
            $ret = $widget_data['jack_object']->$func($_POST['args']);
        else $ret = 'Class object or method not found';

        return $ret;
    }
    function adminWidgetRefreshCode($widgetID){
        ob_start();
        ?>
        <script type="text/javascript">
            init.push(function(){
                $.ajax({

                });
            });
        </script>
        <?php
        $data = ob_get_clean();
        return $data;
    }
    function customize_widgets($stack = array(),$n = 12){
        $sum = 0;
        if(!$stack) return null;
        rsort($stack);
        foreach($stack as $i=>$v){
            $sum = $sum + $v;
            $visited[] = $v;
            if(($sum<$n) && ($sum!=$n)) continue;
            elseif($sum>$n){
                $sum = $sum - $v;
                unset($visited[$i]);
                continue;
            }
            elseif($sum == $n) break;
        }

        $this->output[] = $visited;

        if($visited){
            foreach($visited as $p=>$q){
                unset($stack[$p]);
            }
        }


        if(empty($stack)) return $this->output;

        $this->customize_widgets($stack,$n);

        return $this->output;
    }
}
$adminWidgets = new adminWidgets();

function register_dashboard_widgets($data){
    global $adminWidgets;
    $adminWidgets->register_widget($data);
}

/*********************************************************/
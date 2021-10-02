<?php
class generalContactMessageEmail extends email_templates{
    function init(){
        $this->name = 'new_general_contact_message';
        $this->label = 'General Contact Message';
        $this->availableVariables = array('$$NAME$$','$$EMAIL$$','$$WEBSITE$$','$$MESSAGE$$','$$LINK$$');
        $this->source = '<strong>Dear Concern</strong>
                                <br />
                                <br />
                                    A new General Contact Message has arrived as follows
                                <br />
                                <br />
                                <div class="p10">
                                <hr />
                                    <strong>Name: </strong>$$NAME$$<br />
                                    <strong>Email: </strong>$$EMAIL$$<br />
                                    <strong>Website: </strong>$$WEBSITE$$<br />
                                <hr />
                                    '.nl2br('$$MESSAGE$$').'
                                <hr />

                                     <a class="mailBtn btnDanger" href="$$LINK$$">Click Here to Delete This Message</a>
                                </div>
                                <br />
                                <br />
                                    Thank You.
                                ';
        }
    function get_replace_array($dataArray){
        return array(
            '$$NAME$$' => $dataArray['con_name'],
            '$$EMAIL$$' => $dataArray['con_email'],
            '$$WEBSITE$$' => $dataArray['con_website'],
            '$$MESSAGE$$' => $dataArray['con_content'],
            '$$LINK$$' => url('admin/dev_contact_management/manage_contacts?delete='.$dataArray['contact_id'])
            );
        }
    }
class general_contact_message extends contact_message_types{
    function init(){
        $this->name = 'general';
        $this->label = 'General Contact Message';
        $this->emailTemplate = new generalContactMessageEmail();
        $this->view_columns = array(
            'con_name' => 'Name',
            'con_email' => 'Email',
            'con_web' => 'Web',
            'con_content' => 'Message',
            );
        }
    }
new general_contact_message();

class dev_contact_management{
    var $thsClass = 'dev_contact_management';
    var $contactTypes = array();

    function __construct(){
        jack_register($this);
        }

    function init(){
        apiRegister($this,'get_contact_entry');
        apiRegister($this,'add_contact');

        $permissions = array(
            'group_name' => 'Contact Management',
            'permissions' => array(
                'manage_contacts' => array(
                    'delete_contacts' => 'Delete',
                    ),
                ),
            );

        addAction('after_execute_plugins_event', array($this, 'handleContactFormSubmit'));

        if(!isPublic()){
            register_permission($permissions);

            $this->register_settings();
            $this->adm_menus();
            }
        }

    function registerContactMessageTypes($typeName, $type){

        $this->contactTypes[$typeName] = $type;

        //TODO: add to menu can be optional
        $menuParams = array(
            'label' => $type->label,
            'description' => 'Manage '.$type->label,
            'menu_group' => 'Communications',
            'action' => 'manage_contacts',
            'action_args' => array('contact_type' => $type->name),
            'iconClass' => ' fa-envelope-o',
            'jack' => $this->thsClass,
            );
        //TODO: may be specific permission is needed, let the object set it
        if(has_permission('manage_contacts')) admenu_register($menuParams);
        }

    function handleContactFormSubmit(){
        if(isset($_POST['contact_form_submit'])){
            $ret = $this->add_contact();

            if($_POST['api_call']){
                echo json_encode($ret);
                }
            else{
                if($ret['success']){
                    user_activity::add_activity('A new contact message has been received','success','create');
                    add_notification('Your message has been received. Thank you.','success');
                    }
                elseif($ret['error']){
                    foreach($ret['error'] as $e){
                        add_notification($e,'error');
                        }
                    }
                header('location: '.current_url());
                }
            exit();
            }
        }

    function adm_menus(){
        $args = array(
            'menu_group' => 'Communications',
            'menu_icon' => ' fa-envelope-o'
            );
        admin_menu_group($args);
        }

    function manage_contacts(){
        if(!has_permission('manage_contacts')) return null;
        global $devdb, $_config;

        $myUrl = jack_url($this->thsClass, 'manage_contacts');

        include('pages/list_contacts.php');
        }

    function register_settings(){
        global $JACK_SETTINGS;

        $mySettings = array(
            'send_contact_msg_to_email' => array(
                'type' => 'select',
                'label' => 'Send new contact message to Email?',
                'data' => array(
                    'static' => array(
                        'yes' => 'Yes',
                        'no' => 'No',
                        ),
                    ),
                'width' => 12,
                ),
            'contact_msg_email_addresses' => array(
                'type' => 'text',
                'label' => 'Email addresses where new contact messages will be sent',
                'width' => 12,
                ),
            /*'show_captcha_for_visitor' => array(
                'width' => 12,
                'type' => 'radio',
                'label' => 'Show captcha for visitors?',
                'help' => 'Available only if used in theme',
                'data' => array(
                    'static' => array(
                        'no' => 'No',
                        'yes' => 'yes'
                        )
                    ),
                ),*/
            );

        $JACK_SETTINGS->register_settings('Contact Message Settings', $mySettings);
        }

    function add_contact(){
        global $devdb, $JACK_SETTINGS;
        $authManager = jack_obj('dev_authentication_manager');
        $settings = $JACK_SETTINGS->get_saved_settings('Contact Message Settings');

        $params = $_POST;

        if(!isset($this->contactTypes[$params['contact_type']]))
            return array('error' => array('No allowed contact types'));
        else $contactType = $this->contactTypes[$params['contact_type']];

        if($settings['show_captcha_for_visitor'] == 'yes' && !$authManager->verifyCaptcha()){
            return array('error' => array('Captcha did not matched.'));
            }

        $ret = array('error' => array(), 'success' => array());
        //$params = $devdb->deep_escape($params);
        $params['contact_type'] = strlen($params['contact_type']) ? form_modifiers::encodeInput($params['contact_type'],'') : 'general';
        $params['contact_name'] = form_modifiers::encodeInput($params['contact_name'],'');
        $params['contact_email'] = form_modifiers::encodeInput($params['contact_email'],'');
        $params['contact_mobile'] = form_modifiers::encodeInput($params['contact_mobile'],'');
        $params['con_company'] = form_modifiers::encodeInput($params['con_company'],'');
        $params['con_country'] = form_modifiers::encodeInput($params['con_country'],'');
        $params['con_skills'] = form_modifiers::encodeInput($params['contact_skills'],'');
        $params['contact_website'] = form_modifiers::encodeInput($params['contact_website'],'');
        $params['con_subject'] = form_modifiers::encodeInput($params['contact_subject'],'');
        $params['contact_content'] = form_modifiers::encodeInput($params['contact_content']);

        $temp = form_validator::required($params['contact_name']);
        if($temp !== true) $ret['error'][] = "Name ".$temp;

        $temp = form_validator::required($params['contact_content']);
        if($temp !== true) $ret['error'][] = "Message ".$temp;

        $temp = form_validator::email($params['contact_email']);
        if($temp !== true) $ret['error'][] = 'Email '.$temp;

        $temp = form_validator::_length($params['contact_name'], 250);
        if($temp !== true) $ret['error'][] = "Name ".$temp;

        $temp = form_validator::_length($params['contact_email'], 90);
        if($temp !== true) $ret['error'][] = "Email ".$temp;

        $temp = form_validator::_length($params['contact_mobile'], 45);
        if($temp !== true) $ret['error'][] = "Mobile ".$temp;

        $temp = form_validator::_length($params['con_company'], 250);
        if($temp !== true) $ret['error'][] = "Company ".$temp;

        $temp = form_validator::_length($params['con_country'], 25);
        if($temp !== true) $ret['error'][] = "Country ".$temp;

        $temp = form_validator::_length($params['con_skills'], 490);
        if($temp !== true) $ret['error'][] = "Skills ".$temp;

        $temp = form_validator::_length($params['contact_website'], 190);
        if($temp !== true) $ret['error'][] = "Website ".$temp;

        $temp = form_validator::_length($params['con_subject'], 290);
        if($temp !== true) $ret['error'][] = "Subject ".$temp;

        $temp = form_validator::_length($params['con_subject'], 290);
        if($temp !== true) $ret['error'][] = "Subject ".$temp;

        if(!$ret['error']){
            $data = array(
                'con_type' => $params['contact_type'],
                'con_name' => $params['contact_name'],
                'con_email' => $params['contact_email'],
                'con_mobile' => $params['contact_mobile'],
                'con_company' => $params['contact_company'],
                'con_country' => $params['contact_country'],
                'con_skills' => $params['contact_skills'],
                'con_web' => $params['contact_website'],
                'con_subject' => $params['contact_subject'],
                'con_content' => $params['contact_content'],
                'con_status' => 'new',
                'con_created_at' => date('Y-m-d H:i:s'),
                'con_created_by' => $_SESSION['user_id'] ? $_SESSION['user_id'] : 0,
                'con_modified_at' => date('Y-m-d H:i:s'),
                'con_modified_by' => $_SESSION['user_id'] ? $_SESSION['user_id'] : 0,
                );

            $inserted = $devdb->insert_update('dev_contacts',$data);

            if($inserted['success']){
                $data['contact_id'] = $inserted['success'];

                global $JACK_SETTINGS;
                $contactSettings = $JACK_SETTINGS->get_saved_settings('Contact Message Settings');

                if(isset($contactSettings['send_contact_msg_to_email']) && $contactSettings['send_contact_msg_to_email'] == 'yes' && strlen($contactSettings['contact_msg_email_addresses'])){
                    $thisEmail = $contactType->emailTemplate;
                    $thisEmail->send_email($data, $contactSettings['contact_msg_email_addresses'], $thisEmail->label. ' ['.$data['contact_id'].']');
                    }
                return $inserted;
                }
            else return $inserted;
            }
        else return $ret;
        }

    function get_contact_entry($args=NULL){
        $args['index_with'] = 'pk_con_id';

        $sql = "SELECT * FROM dev_contacts WHERE 1 ";
        $count_sql = "SELECT COUNT(pk_con_id) AS TOTAL FROM dev_contacts WHERE 1 ";
        $condition = '';

        $loop_conditions = array(
            'con_id' => 'pk_con_id',
            'name' => 'con_name',
            'email' => 'con_email',
            'content' => 'con_content',
            'type' => 'con_type',
            'status' => 'con_status'
            );

        $data = process_sql_operation($loop_conditions, $condition, $sql, $count_sql, $args);

        return $data;
        }

    //value render function
    function getPrintableValue($field, $data){
        $thisData = $data[$field];

        if($field == 'con_name') return $thisData;
        else if($field == 'con_email') return $thisData;
        else if($field == 'con_web') return handleEmptyStrings($thisData);
        else if($field == 'con_content') return remove_rn(nl2br($thisData),'<br />');
        else if($field == 'con_created_at') return print_date($thisData);
        else if($field == 'con_mobile') return handleEmptyStrings($thisData);
        else if($field == 'con_subject') return handleEmptyStrings($thisData);
        else if($field == 'con_company') return handleEmptyStrings($thisData);
        else if($field == 'con_country') return handleEmptyStrings($thisData);
        else if($field == 'con_skills') return handleEmptyStrings($thisData);
        }
    }
new dev_contact_management;
<?php
reRouteLoggedInUser();

$data = array();

if(isset($_POST['registration_request']) && $_POST['registration_request'] == '2'){
    $instructorManager = jack_obj('dev_instructor_management');
    $studentManager = jack_obj('dev_student_management');
    $roleManager = jack_obj('dev_role_permission_management');
    $emailManager = jack_obj('dev_email_template_manager');

    //sanitize
    $data = $devdb->deep_escape($_POST['data']);

    $ret = returnArray();

    //validate
    $required = array(
        'fullname' => 'Full Name',
        'email_address' => 'Email Address',
        'mobile_number' => 'Mobile Phone Number',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        );
    foreach($required as $i=>$v){
        $valid = form_validator::required($data[$i]);
        if($valid !== true) $ret['error'][] = $v.' '.$valid;
        }

    $invalidEmail = validateEmail($data['email_address']);
    if($invalidEmail) $ret['error'] = array_merge($ret['error'], $invalidEmail);

    //TODO: Validate mobile number

    if(strcmp($data['password'], $data['confirm_password']) != 0)
        $ret['error'][] = 'Password and Confirm Password did not match.';

    if(!isset($data['terms_n_condition']))
        $ret['error'][] = 'Please read and agree to the terms & conditions and privacy policy';

    if(!$ret['error']){
        $userName = form_modifiers::userName($data['fullname']);
        $isValidUserName = form_validator::unique($userName, 'dev_users', 'user_name');
        if($isValidUserName !== true) $userName = $isValidUserName;

        $userMetaType = $data['user_meta_type'] == 'instructor' ? 'instructor' : 'student';
        $userRole = $userMetaType == 'instructor' ? $instructorManager->default_role : $studentManager->default_role;

        $pass = hash_password($data['password']);

        $verification_code = getEmailResetCode();

        $insert_data = array(
            'user_fb_id' => '',
            'user_fullname' => $data['fullname'],
            'user_name' => $userName,
            'user_gender' => 'N/A',
            'user_email' => $data['email_address'],
            'user_email_verified' => 0,
            'user_password' => $pass,
            'user_password_updated' => '1',
            'user_status' => 'inactive',
            'user_type' => 'public',
            'user_mobile' => $data['mobile_number'],
            'user_mobile_verified' => 0,
            'user_meta_type' => $userMetaType,
            'user_email_verification_code' => $verification_code,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => 0,
            'modified_at' => date('Y-m-d H:i:s'),
            'modified_by' => 0,
            );

        $ret = $devdb->insert_update('dev_users', $insert_data);

        if($ret['success']){
            $userID = $ret['success'];

            $roleManager->assign_role($userID, array($userRole));

            $thisEmail = $emailManager->email_templates['verify_email_address'];
            $emailData = array('fullname' => $data['fullname'], 'link' => url('email_verification') . '?key=' . $verification_code);
            $thisEmail->send_email($emailData, $data['email_address'], 'Email Verification', $data['fullname']);

            user_activity::add_activity('New '.$userMetaType.' registered. Name: '.$data['fullname'].', Email: '.$data['email_address'].', ID: '.$userID);
            $ret['success'] = 'Thank you for registering with E-Shikkhok. We have sent you an email in your given email address. Please click on the given email verification link to verify your email address and activate your account.';
            }
        }

    echo json_encode($ret);
    exit();
    }

include('header.php');
$notify_user->get_notification();
?>

<form onsubmit="return false;" method="post">
    <div class="form-group">
        <input required type="text" name="fullname" placeholder="Full Name" />
    </div>
    <div class="form-group">
        <input required type="email" name="email_address" placeholder="Email Address" />
    </div>
    <div class="form-group">
        <input required type="text" name="mobile_number" placeholder="Mobile Number" />
    </div>
    <div class="form-group">
        <input type="password" name="password" placeholder="Password" />
    </div>
    <div class="form-group">
        <input type="password" name="confirm_password" placeholder="Confirm Password" />
    </div>
    <div class="form-group">
        <label>
            <input type="checkbox" name="terms_n_condition" value="1" />
            I agree to terms and conditions and privacy policy
        </label>
    </div>
    <div class="form-group">
        <label>I am</label>
        <label>
            <input type="radio" name="user_meta_type" value="instructor" checked/>
            An Instructor
        </label>
        <label>
            <input type="radio" name="user_meta_type" value="student" />
            A Student
        </label>
    </div>
    <div class="form-group">
        <button type="button" name="registration_request" value="1" class="register_now">Register Now</button>
    </div>
    <div class="error_container"></div>
</form>
<script type="text/javascript">
    init.push(function(){
        $('.register_now').on('click', function(){
            var ths = $(this);
            var _form = ths.closest('form');
            var errContainer = _form.find('.error_container');

            var userFullName = $('[name="fullname"]', _form).val();
            var userEmail = $('[name="email_address"]', _form).val();
            var userMobile = $('[name="mobile_number"]', _form).val();
            var userPassword = $('[name="password"]', _form).val();
            var userRePassword = $('[name="confirm_password"]', _form).val();
            var userMetaType = $('[name="user_meta_type"]:checked', _form).val();
            var userTerms = $('[name="terms_n_condition"]:checked', _form).val();

            $.ajax({
                beforeSend: function(){
                    show_button_overlay_working(ths);
                    errContainer.html();
                    },
                complete: function(){hide_button_overlay_working(ths)},
                url: _root_path_+'/registration',
                type: 'post',
                dataType: 'json',
                data: {
                    internalToken: _internalToken_,
                    registration_request: 2,
                    data:{
                        fullname: userFullName,
                        email_address: userEmail,
                        mobile_number: userMobile,
                        password: userPassword,
                        confirm_password: userRePassword,
                        user_meta_type: userMetaType,
                        terms_n_condition: userTerms,
                        },
                    },
                success: function(ret){
                    if(ret.success.length) _form.html(ret.success);
                    else errContainer.html(ret.error);
                    },
                });
            });
        });
</script>
<?php include('footer.php');
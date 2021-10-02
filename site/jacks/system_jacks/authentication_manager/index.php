<?php
class passwordResetCompleteEmail extends email_templates{
    function init(){
        $this->name = 'password_reset_complete';
        $this->label = 'Password Reset Complete';
        $this->availableVariables = array('$$username$$','$$new_password$$');
        $this->source = 'Dear <strong>$$username$$</strong>,<br /><br />Your password changed successfully. Please copy and use the following password to login to your account and change it from your profile panel in dashboard.<br /><br />$$new_password$$<br /><br />Thank You.';
        }
    function get_replace_array($dataArray){
        return array(
            '$$username$$' => $dataArray['fullname'],
            '$$new_password$$' => $dataArray['password'],
            );
        }
    }
new passwordResetCompleteEmail();

class passwordResetRequestEmail extends email_templates{
    function init(){
        $this->name = 'password_reset_request';
        $this->label = 'Password Reset Request';
        $this->availableVariables = array('$$username$$','$$link$$');
        $this->source = 'Dear <strong>$$username$$</strong>,
                    <br /><br />
                    A password reset request has been made for your account. If you have made the request, please go to this link to reset your password.
                    <br />
                    <br />
                    <a target="_blank" title="Reset Your Password" href="$$link$$">Click here to reset your password</a>
                    <br /><br />OR click on the link below if the above link is not working<br /><br />
                    $$link$$
                    <br /><br />
                    If you haven\'t made the request, ignore this email.';
    }
    function get_replace_array($dataArray){
        return array(
            '$$username$$' => $dataArray['fullname'],
            '$$link$$' => $dataArray['link'],
        );
    }
}
new passwordResetRequestEmail();

class verifyEmailEmail extends email_templates{
    function init(){
        $this->name = 'verify_email_address';
        $this->label = 'Verify Email Address';
        $this->availableVariables = array('$$username$$','$$link$$');
        $this->source = 'Dear <strong>$$username$$</strong>,
                        <br/><br/>
                        <a title="Verify Email Address" target="_blank"
                           href="$$link$$">Click here to
                            verify your email address</a>
                        <br/><br/>OR click on the link below if the above link is not working<br/><br/>
                        $$link$$
                        <br/><br/>
                        If you haven\'t made the request, ignore this email.';
        }
    function get_replace_array($dataArray){
        return array(
            '$$username$$' => $dataArray['fullname'],
            '$$link$$' => $dataArray['link'],
            );
        }
    }
new verifyEmailEmail();

class dev_authentication_manager{
	function __construct(){
		jack_register($this);
        }

    function init(){
        apiRegister($this,'perform_registration');
        apiRegister($this,'perform_login');
        apiRegister($this,'perform_password_reset_request');
        apiRegister($this,'perform_password_reset');
        apiRegister($this,'facebook_login_helper');
        apiRegister($this,'getCaptchaForm');
        apiRegister($this,'getCaptcha');
        apiRegister($this,'verifyCaptcha');
        }

    function getCaptcha($id = null,$imgWidth = 400,$imgHeight = 150){
        //return 'abcd';
        $id = $_GET['id'] ? $_GET['id'] : ($id ? $id : time());
        $imgWidth = $_GET['imgWidth'] ? $_GET['imgWidth'] : $imgWidth;
        $imgHeight = $_GET['imgHeight'] ? $_GET['imgHeight'] : $imgHeight;

        $random = substr( md5(rand()), 0, 7);

        $_SESSION['captcha_code'][$id] = str_split($random);

        $dir = _path('system_jacks','absolute').'/authentication_manager/fonts/';

        $image = imagecreatetruecolor($imgWidth, $imgHeight);
        $black = imagecolorallocatealpha($image, 0, 0, 0,90);
        $color = imagecolorallocate($image, 200, 100, 90); // red
        $red_line = imagecolorallocatealpha($image, 200, 100, 90, 90);
        $white = imagecolorallocate($image, 255, 255, 255);
        $font_colors = array(
            imagecolorallocate($image, 255, 0, 0),
            imagecolorallocate($image, 255, 0, 92),
            imagecolorallocate($image, 253, 0, 255),
            imagecolorallocate($image, 65, 0, 255),
            imagecolorallocate($image, 0, 159, 255),
            imagecolorallocate($image, 0, 255, 80),
            imagecolorallocate($image, 255, 108, 0),
            );
        imagefilledrectangle($image,0,0,$imgWidth, $imgHeight,$white);
        $mod = 10;
        $totalChars = count($_SESSION['captcha_code'][$id]);
        $eachCharSpace = ($imgWidth-$mod)/$totalChars;

        foreach($_SESSION['captcha_code'][$id] as $i=>$v){
            $thisColor = mt_rand(0,count($font_colors)-1);
            $thisColor = $thisColor < 0 || $thisColor == count($font_colors) ? count($font_colors)-1 : $thisColor;
            $mod = $i ? $mod += $eachCharSpace : $mod;
            $angle = mt_rand(300,400);
            $size = mt_rand(20,40);
            $height = mt_rand(40,70);
            $x1 = mt_rand(0,300);
            $y1 = mt_rand(0,100);
            $x2 = mt_rand(0,300);
            $y2 = mt_rand(10,100);
            $thickness = mt_rand(1,5);
            $center_rand1 = mt_rand(1,300);
            $center_rand2 = mt_rand(1,300);
            $start_rand1 = mt_rand(10,300);
            $start_rand2 = mt_rand(10,300);
            $arc_width = mt_rand(10,300);
            $arc_height = mt_rand(10,300);
            imageline($image, $x1, $y1, $x2, $y2, $black);
            imagearc ($image , $center_rand1 , $center_rand2 ,$arc_width ,$arc_height ,$start_rand1 ,$start_rand2 ,$red_line );
            imagesetthickness($image,$thickness);

            imagettftext($image, $size, $angle, $mod, $height, $font_colors[$thisColor], $dir."arial.ttf", $v);
            }

        $path = $id.'_temp.png';

        imagepng($image, $path);
        $data = file_get_contents($path);
        $base64 = 'data:image/png;base64,' . base64_encode($data);
        unlink($path);
        return $base64;
        }

    function getCaptchaForm($id = null,$imgWidth = 400,$imgHeight = 150, $jqueryNoConflict = false){
        if(is_array($id)) extract($id);

        $id = $id ? $id : time();
        ob_start();
        ?>
        <div class="captcha_form">
            <img id="captcha_image_<?php echo $id?>" src="<?php echo $this->getCaptcha($id,$imgWidth,$imgHeight)?>"/>
            <input type="text" class="form-control db" name="captcha_code[<?php echo $id?>]"/>
            <a id="captchaReferesh_<?php echo $id?>" class="captcha_refresh_link" onclick=" " href="javascript:"><i class="fa fa-recycle"></i></a>
            <script type="text/javascript">
                <?php if($jqueryNoConflict): ?>
                    (function($) {
                        <?php endif; ?>
                        $(document).off('click', '#captchaReferesh_<?php echo $id?>').on('click', '#captchaReferesh_<?php echo $id?>', function () {
                            var ths = $(this);
                            $.ajax({
                                beforeSend: function () {
                                    ths.find('i').removeClass('fa-recycle').addClass('fa-cog fa-spin');
                                },
                                complete: function () {
                                    ths.find('i').removeClass('fa-cog fa-spin').addClass('fa-recycle');
                                },
                                url: '<?php echo url('api/dev_authentication_manager/getCaptcha?id=' . $id . '&imgWidth=' . $imgWidth . '&imgHeight=' . $imgHeight); ?>',
                                type: 'post',
                                data: {'internalToken': _internalToken_},
                                success: function (data) {
                                    $('#captcha_image_<?php echo $id ?>').attr('src', data);
                                },
                                dataType: 'json',
                                cache: false
                            });
                        });
                        <?php if($jqueryNoConflict): ?>
                    })(jQuery);
                <?php endif; ?>
            </script>
        </div>
        <?php
        $output = ob_get_clean();
        return $output;
        }

    function verifyCaptcha(){
        $matched = false;
        if($_POST['captcha_code']) {
            foreach($_POST['captcha_code'] as $i=>$v){
                if($v == implode('',$_SESSION['captcha_code'][$i])) $matched = true;
                else $matched = false;
                }
            }
        elseif(isset($_POST['captcha_code'])) return false;
        else return true;

        return $matched;
        }

    function facebook_login_helper($param=array()){
        global $_config;
        $ret = array();

        $profile_manager = jack_obj('dev_profile_management');
        $role_manager = jack_obj('dev_role_permission_management');

        $posted_data = $param['post'] ? $param['post'] : array();
        $url_data = $param['get'] ? $param['get'] : array();

        $param = array_merge($param, $posted_data, $url_data);

        $fbid = $param['fbid'];
        $femail = $param['femail'];
        $fbfullname = $param['fbfullname'];

        $args = array(
            'user_fb_id' => $fbid,
            'single' => true,
            );

        $found_user = $profile_manager->get_users($args);
        $found_user = $found_user['data'];

        if($found_user){
            if($found_user['user_status'] == 'active'){
                LOGIN_THE_USER($found_user,true);
                add_notification('You are now logged in','success');
                return array('success'=> 'User Logged in Successfully');
                }
            }
        else {
            $userName = form_modifiers::slug($fbfullname, '-', true);
            $temp = form_validator::unique($userName, 'dev_users', 'user_name');
            if ($temp !== true) $userName = $temp;

            if($femail){
                $temp = form_validator::unique($femail,'dev_users','user_email');
                if($temp !== true) $ret['error'][] = 555;
                }

            $userPassword = 'autoPassword' . date('ymdHis') . rand(1, 10);

            if($ret['error']){
                return $ret;
                }
            $args = array(
                'user_fb_id' => $fbid,
                'user_fullname' => $fbfullname,
                'user_name' => $userName,
                'user_email' => $femail,
                'terms_agree' => 1,
                'user_password' => $userPassword,
                'user_password_updated' => 1,
                'confirm_user_password' => $userPassword,
                'user_status' => $_config['fb_user_status'] ? 'active' : null,
                'registration_from_facebook' => true,
                );
            $ret = $this->perform_registration($args);

            if ($ret['success']) {
                $args = array(
                    'user_id' => $ret['success'],
                    'single' => true,
                    );
                $user = $profile_manager->get_users($args);
                $user = $user['data'];

                $role_manager->assign_role($ret['success'], ($_config['fb_user_default_role'] ? $_config['fb_user_default_role'] : 0));

                add_notification('You have been successfully registered with your facebook ID, from now on, you can simply login by clicking Login With Facebook button in login page.', 'success');
                LOGIN_THE_USER($user,true);
                }
            return $ret;
            }
        }

    function perform_password_reset($data){
        global $devdb, $_config;


        if($_config['user']) return array('error' => array('You are already logged in.'));
        if($data['key']){
            $data['key'] = urlencode($data['key']);

            if($data['key'] && $data['confirm_reset'] == 'yes'){
                $sql = "SELECT * FROM dev_users WHERE user_password_reset_link = '".$data['key']."'";

                $found = $devdb->get_row($sql);

                if($found){
                    $emailTemplateManager = jack_obj('dev_email_template_manager');
                    $new_pass = md5(serialize($found));
                    $new_hash = hash_password($new_pass);

                    $sql = "UPDATE dev_users SET user_password = '".$new_hash."',user_password_reset_link = null WHERE pk_user_id = '".$found['pk_user_id']."'";
                    $updated = $devdb->query($sql);

                    if($updated){
                        $emailTemplateManager = jack_obj('dev_email_template_manager');
                        $thisEmail = $emailTemplateManager->email_templates['password_reset_complete'];
                        $emailData =array('fullname' => $found['user_fullname'], 'password' => $new_pass);
                        $thisEmail->send_email($emailData, $found['user_email'], 'Password Reset Complete');
                        }
                    return array('success' => 'Password was reset successfully.');
                    }
                else return array('error' => array('Sorry, the user was not found.'));
                }
            else return array('error' => array('Insufficient data to reset password.'));
            }
        else{
            $temp = form_validator::password($data['user_password']);
            if($temp !== true)
                $ret['error'][] = 'Password '.$temp;

            if($data['user_password'] != $data['confirm_user_password'])
                $ret['error'][] = 'Password and Confirm Password are not matching.';
            if(!$ret['error']){
                $new_hash = hash_password($data['user_password']);
                $sql = "UPDATE dev_users SET user_password = '".$new_hash."' WHERE pk_user_id='".$_SESSION['verified_user']."'";
                $updation = $devdb->query($sql);
                return array('success' => 'success');
                }
            else{
                $ret['not_done'] = 'not done';
                return $ret;
                }
            }
        }

	function perform_password_reset_request($data){
        global $_config;
        $emailTemplateManager = jack_obj('dev_email_template_manager');
        $smsManager = jack_obj('dev_sms_management');

        if($_config['user']) return array('error' => array('You are already logged in.'));

        global $devdb;

        $data['user_email'] = form_modifiers::sanitize_input($data['user_email']);

        $data['user_email'] = $devdb->escape($data['user_email']);

        if($data['user_email']){
            $sql = "SELECT * FROM dev_users WHERE user_email = '".$data['user_email']."' OR user_name = '".$data['user_email']."'";
            $user = $devdb->get_row($sql);

            if($user){
                $crap = array(
                    'user_id' => $user['pk_user_id'],
                    'user_email' => $user['user_email'],
                    'current_time' => date('Y-m-d H:i:s'),
                    'more_secure' => '3devs@3devs.com'
                    );

                $pre_key = hash_password(serialize($crap));
                $key = trim(urlencode($pre_key));

                $insert_data = array(
                    'user_password_reset_link' => $key
                    );

                $inserted = $devdb->insert_update('dev_users',$insert_data," pk_user_id = '".$user['pk_user_id']."'");

                if($inserted['success']){
                    $thisEmail = $emailTemplateManager->email_templates['password_reset_request'];
                    $data = array('fullname' => $user['user_fullname'], 'link' => url('password_reset').'?key='.$key);
                    $thisEmail->send_email($data, $user['user_email'], 'Password Reset Request', $user['user_fullname']);
                    return $inserted;
                    }
                else return array('error' => array('Failed to reset, please try again.'));
                }
            else return array('error' => array('User was not found.'),'email_user_not_found' => 'not found');
            }
        elseif(strlen($data['user_mobile'])){
            $sql = "SELECT * FROM dev_users WHERE user_mobile = '".$data['user_mobile']."'";
            $user = $devdb->get_row($sql);

            if($user){
                $temp = form_validator::required($data['user_mobile']);

                $verification_code = mt_rand(100000, 999999);

                $_SESSION['sms_pin'] = $verification_code;
                $_SESSION['verified_user'] = $user['pk_user_id'];
                $sms_array=array(
                    'sms_text' => $verification_code,
                    'sms_number' => $data['user_mobile']
                    );
                $return_sms = $smsManager->send_sms($sms_array);

                if($return_sms['success']) {
                    return array('success'=>'sms');
                    }
                else return array('error' => array('Failed to send PIN... please try again'));
                }
            else return array('error' => array('No User found!!'),'mobile_user_not_found' => 'mobile_user_not_found');
            }
        else return array('error' => array('User email is required.'));
        }

	function perform_registration($data){
		if(!$data) return array('error' => array('No Data.'));
        $emailTemplateManager = jack_obj('dev_email_template_manager');
        $smsManager = jack_obj('dev_sms_management');
        $roleManager = jack_obj('dev_role_permission_management');

		global $devdb,$_config;

		$ret = array();
		
		//sanetizing
        $data['user_fullname'] = form_modifiers::sanitize_input($data['user_fullname']);
        $data['user_name'] = form_modifiers::sanitize_input($data['user_name']);
        $data['user_email'] = $data['user_email'] ? form_modifiers::sanitize_input($data['user_email']) : '';

        //TODO: better escape function required.
		$data = $devdb->deep_escape($data);

		//validating
        if($_config['use_captcha_in_registration'] && !$this->verifyCaptcha())
            $ret['error'][] = 'Captcha mismatched.';

        if($_config['required_terms'] && !isset($data['terms_agree']))
            $ret['error'][] = 'Please read and agree Privacy Policy and Terms &amp; Condition';

		$temp = form_validator::required($data['user_fullname']);
		if($temp !== true)
			$ret['error'][] = 'Fullname '.$temp;

        $temp = form_validator::required($data['user_name']);
        if($temp !== true)
            $ret['error'][] = 'User Name '.$temp;

        //checking if valid slug username
        $temp_username = form_modifiers::userName($data['user_name']);
        if($temp_username != $data['user_name'])
            $ret['error'][] = 'Username is not acceptable. You can try with <em>'.$temp_username.'</em>, if not already taken.';

        $temp = form_validator::unique($data['user_name'],'dev_users','user_name');
        if($temp !== true)
            $ret['error'][] = 'User Name has already been used, try another.';
        //---------


        if($_config['required_email'] && !$data['registration_from_facebook']){
            $temp = form_validator::required($data['user_email']);
            if($temp !== true)
                $ret['error'][] = 'User Email '.$temp;
            else{
                $eRet = validateEmail($data['user_email']);
                if($eRet){
                    foreach($eRet as $i){
                        $ret['error'][] = $i;
                        }
                    }
                }
            }
        else{
            if($data['user_email']){
                $eRet = validateEmail($data['user_email']);
                if($eRet){
                    foreach($eRet as $i){
                        $ret['error'][] = $i;
                        }
                    }
                }
            }

        if($_config['required_mobile'] && !$data['registration_from_facebook']){
            $temp = form_validator::required($data['user_mobile']);
            if($temp !== true)
                $ret['error'][] = 'User Mobile '.$temp;
            else{
                $eRet = validateMobile($data['user_mobile']);
                if($eRet){
                    foreach($eRet as $i){
                        $ret['error'][] = $i;
                        }
                    }
                }
            }
        else{
            if($data['user_mobile']){
                $eRet = validateMobile($data['user_mobile']);
                if($eRet){
                    foreach($eRet as $i){
                        $ret['error'][] = $i;
                        }
                    }
                }
            }

		$temp = form_validator::password($data['user_password']);
		if($temp !== true)
			$ret['error'][] = 'Password '.$temp;
		
		if($data['user_password'] != $data['confirm_user_password'])
			$ret['error'][] = 'Password and Confirm Password are not matching.';

        if(!$data['registration_from_facebook'] && $_config['apply_registration_verification'] && !strlen($data['user_email']) && !strlen($data['user_mobile']))
            $ret['error'][] = 'Please provide email address or mobile number.';

        //verification method
        $verification = null;
        $verification_code = null;

        if(!$data['registration_from_facebook'] && $_config['apply_registration_verification']){
            if($_config['verification_method'] == 'email'){
                $temp = form_validator::required($data['user_email']);
                if($temp !== true)
                    $ret['error'][] = 'User Email is required to verify your account.';
                else{
                    $verification = 'email';
                    $verification_code = getEmailResetCode();
                    }
                }
            elseif($_config['verification_method'] == 'sms'){
                $temp = form_validator::required($data['user_mobile']);
                if($temp !== true)
                    $ret['error'][] = 'Mobile Number is required to verify your account.';
                else{
                    $verification = 'sms';
                    $verification_code = mt_rand(100000, 999999);
                    }
                }
            elseif($_config['verification_method'] == 'user'){
                $verification = 'user';
                }
            }

		if(!$ret['error']){
            $pass = hash_password($data['user_password']);
			$insert_data = array(
				'pk_user_id' => NULL,
				'user_fb_id' => $data['user_fb_id'] ? $data['user_fb_id'] : '',
				'user_fullname' => $data['user_fullname'],
				'user_name' => $data['user_name'],
                'user_gender' => $data['user_gender'] ? $data['user_gender'] : 'male',
				'user_email' => $data['user_email'],
				'user_password' => $pass,
				'user_password_updated' => '1',
				'user_status' => $verification ? 'not_verified' : ($_config['force_default_user_settings'] ? $_config['default_user_status'] : ($data['user_status'] ? $data['user_status'] : 'inactive')),
				'user_type' => $data['user_type'] ? $data['user_type'] : 'public',
                'user_mobile' => $data['user_mobile'] ? $data['user_mobile'] : '',
                'user_private_token' => $this->generateUserToken($data['user_name']),
                'created_at' => date('Y-m-d H:i:s'),
                'modified_at' => date('Y-m-d H:i:s'),
				);

            if($verification){
                if($verification == 'email')
                    $insert_data['user_email_verification_code'] = $verification_code;
                }

			$ret = $devdb->insert_update('dev_users',$insert_data);

            if($ret['success']){
                $userID = $ret['success'];

                $updateData = array(
                    'created_by' => $userID,
                    'modified_by' => $userID
                    );
                $update = $devdb->insert_update('dev_users',$updateData," pk_user_id = '".$ret['success']."'");

                $roleManager->assign_role($userID, ($_config['force_default_user_settings'] ? $_config['default_user_role'] : ($data['user_role'] ? $data['user_role'] : 0 )));

                user_activity::add_activity('New User ID '.$ret['success'].', Name: '.$insert_data['user_fullname'].' has just been registered as inactive.','success', 'create');

                $_SESSION['not_verified_user'] = $userID;

                if($verification){
                    $ret['verified_with'] = $verification;
                    if($verification == 'sms'){
                        $_SESSION['sms_pin'] = $verification_code;
                        $sms_array=array(
                            'sms_text' => $verification_code,
                            'sms_number' => $data['user_mobile']
                            );
                        $return_sms = $smsManager->send_sms($sms_array);
                        }
                    elseif($verification == 'email'){
                        $thisEmail = $emailTemplateManager->email_templates['verify_email_address'];
                        $data = array('fullname' => $insert_data['user_fullname'], 'link' => url('verify_email') . '?key=' . $verification_code);
                        $thisEmail->send_email($data, $insert_data['user_email'], 'Email Verification', $insert_data['user_fullname']);
                        }
                    }
                }
			}
		return $ret;
		}

	function perform_login($data){
		if(!$data) return;
		
		global $devdb;
		
		$ret = array();
		
		//sanetizing
        $data['user_email'] = form_modifiers::sanitize_input($data['user_email']);

		$data = $devdb->deep_escape($data);
		
		//validating
		$temp = form_validator::required($data['user_email']);
		if($temp !== true)
			$ret['error'][] = 'Email '.$temp;
		
		$temp = form_validator::required($data['user_password']);
		if($temp !== true)
			$ret['error'][] = 'Password '.$temp;
		
		if(!$ret['error']){
            $sql = "SELECT * FROM dev_users WHERE (user_email = '".$data['user_email']."' OR user_name = '".$data['user_email']."' OR user_mobile = '".$data['user_email']."')";
            $ret = $devdb->get_row($sql);
            if($ret){
                if($ret['user_status'] == 'inactive')
                    $ret['error'][] = 'The user is inactive.';

                elseif($ret['user_status'] == 'not_verified'){
                    $db_password = $ret['user_password'];
                    $old = $ret['user_password_updated'] == 0 ? true : false;
                    $verified = verify_password($data['user_password'],$db_password, $old);
                    if(!$verified){
                        $ret['error'][] = "Mismatched Email or Password.";
                        }
                    else{
                        $_SESSION['not_verified_user'] = $ret['pk_user_id'];
                        $ret['error'][] = 'Your account is not verified yet. Please <a class="btn btn-xs" href="'.url('user_verification').'">Click Here</a> to continue verification of your account';
                        }
                    }
                else{
                    $db_password = $ret['user_password'];
                    $old = $ret['user_password_updated'] == 0 ? true : false;
                    $verified = verify_password($data['user_password'],$db_password, $old);
                    if(!$verified)
                        $ret['error'][] = "Mismatched Email or Password.";
                    else{
                        if($old){
                            $new_password = hash_password($data['user_password']);
                            if($new_password){
                                $update_data = array(
                                    'user_password' => $new_password,
                                    'user_password_updated' => 1,
                                    );

                                $updatePassword = $devdb->insert_update('dev_users', $update_data, " pk_user_id = '".$ret['pk_user_id']."'");
                                }
                            }
                        LOGIN_THE_USER($ret,true);
                        }
                    }

                }
            else $ret['error'] = array(
                'The user wasn\'t found, please try again.'
                );
			}

		return $ret;
		}

    function generateUserToken($userName){
        $hash = hash_password($userName).openssl_token();
        return $hash;
        }
	}
new dev_authentication_manager;

function validateEmail($email){
    $ret = array();
    $temp = form_validator::email($email);
    if($temp !== true)
        $ret[] = 'Email '.$temp;

    $temp = form_validator::unique($email,'dev_users','user_email', " AND user_email_verified = '1'");
    if($temp !== true)
        $ret[] = 'Given email has already been used, try another.';

    if($ret) return $ret;
    else return null;
    }

function validateMobile($mob){
    $ret = array();

    $temp = form_validator::unique($mob,'dev_users','user_mobile', " AND user_mobile_verified = '1'");
    if($temp !== true)
        $ret[] = 'Given mobile has already been used, try another.';

    if($ret) return $ret;
    else return null;
    }

function getEmailResetCode(){
    return hash_password('3devsIT'.mt_rand(10000,99999).date('Y-m-d H:i:s'));
    }

function reRouteLoggedInUser(){
    global $_config;

    if($_config['noFront']){
        if(HAS_USER('admin')){
            header('location:'.($_GET['next'] ? urldecode($_GET['next']) : url('')));
            exit();
            }
        }
    else{
        if(HAS_USER('admin')){
            header('location:'.($_GET['next'] ? urldecode($_GET['next']) : url('admin')));
            exit();
            }
        elseif(HAS_USER('public')){
            header('location:'.($_GET['next'] ? urldecode($_GET['next']) : url('')));
            exit();
            }
        }
    }

function processLogin($reRouteUser = true){
    global $_config, $JACK_SETTINGS;

    $authManager = jack_obj('dev_authentication_manager');

    if(isset($_POST['login_request'])){
        $ret = $authManager->perform_login($_POST);
        if($ret['error']){
            foreach($ret['error'] as $e){
                add_notification($e,'error');
                }
            }
        else{
            if($_config['user'] && $_config['user']['user_type'] == 'admin'){
                $_SESSION['admin_loggedin'] = 1;
                }

            $next = $_GET['next'] ? urldecode($_GET['next']) : url('');
            add_notification('You are now logged in.', 'success');
            header('location:' . $next);
            exit();
            }
        }
    }

function processRegistration(){
    global $_config, $JACK_SETTINGS;

    $authManager = jack_obj('dev_authentication_manager');

    if(isset($_POST['registration_request'])){
        $_POST['user_status'] = 'not verified';
        $_POST['user_type'] = 'public';
        $profile_manager = jack_obj('dev_profile_management');

        $ret = $authManager->perform_registration($_POST);
        if($ret['error']){
            foreach($ret['error'] as $e){
                add_notification($e,'error');
                }
            }
        else{
            $user_id = $ret['success'];

            if($ret['verified_with'] == 'email'){
                add_notification('You are now registered. We have sent you an email with a link to the address you gave in registration. Open that email and click on that link to verify your email address. You can login only after you have verified your email address.','success');
                header('Location: '.url('registration'));
                exit();
                }
            elseif($ret['verified_with'] == 'sms'){
                add_notification('You are now registered. We have sent you a code via SMS to your given mobile number.','success');
                header('Location: '.url('verify_sms'));
                exit();
                }
            elseif($ret['verified_with'] == 'user'){
                add_notification('You are now registered. Please verify your account.','success');
                header('Location: '.url('user_verification'));
                exit();
                }
            else{
                $args = array(
                    'user_id' => $user_id,
                    'single' => true,
                    );
                $user = $profile_manager->get_users($args);
                $user = $user['data'];
                if($user['user_status'] == 'active'){
                    if($_config['auto_login']){
                        add_notification('You are now registered.','success');
                        LOGIN_THE_USER($user, true);
                        }
                    else {
                        add_notification('You are now registered.', 'success');
                        header('Location: ' . url('login'));
                        exit();
                        }
                    }
                else{
                    add_notification('You are now registered.','success');
                    header('Location: '.url('login'));
                    exit();
                    }
                }
            $success = true;
            }
        }
    }

function echoFacebookLoginCode(){
    global $_config, $SAFEGUARD;

    if($_config['allow_facebook_login']){
        ?>
        <script type="text/javascript">
            function statusChangeCallback(response) {
                if (response.status === 'connected') {
                    // Logged into your app and Facebook.
                    getUserInfo();
                    }
                else if (response.status === 'not_authorized') {
                    // The person is logged into Facebook, but not your app.
                    document.getElementById('status').innerHTML = 'Please log into this app.';
                    }
                else {
                    // The person is not logged into Facebook, so we're not sure if
                    // they are logged into this app or not.
                    document.getElementById('status').innerHTML = 'Please log into Facebook.';
                    }
                }

            // This function is called when someone finishes with the Login
            // Button.  See the onlogin handler attached to it in the sample
            // code below.
            function checkLoginState() {
                FB.getLoginStatus(function(response) {
                    statusChangeCallback(response);
                    });
                }

            window.fbAsyncInit = function() {
                FB.init({
                    appId      : '<?php echo $_config['fb_app_id'] ? $_config['fb_app_id'] : ''?>',
                    cookie     : true,  // enable cookies to allow the server to access the session
                    xfbml      : true,  // parse social plugins on this page
                    version    : 'v2.2' // use version 2.2
                    });

                // Now that we've initialized the JavaScript SDK, we call
                // FB.getLoginStatus().  This function gets the state of the
                // person visiting this page and can return one of three states to
                // the callback you provide.  They can be:
                //
                // 1. Logged into your app ('connected')
                // 2. Logged into Facebook, but not your app ('not_authorized')
                // 3. Not logged into Facebook and can't tell if they are logged into
                //    your app or not.
                //
                // These three cases are handled in the callback function.

                /*FB.getLoginStatus(function(response) {
                 statusChangeCallback(response);
                 });
                 */
                };

            // Load the SDK asynchronously
            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));

            // Here we run a very simple test of the Graph API after login is
            // successful.  See statusChangeCallback() for when this call is made.
            function getUserInfo() {
                FB.api('/me?fields=id,name,email', function(response) {
                    if(response.id && response.name){
                        $.ajax({
                            beforeSend: function(){
                                $('.fb_login_status').removeClass('dn');
                                $('.fb_login_status').html('<i class="fa fa-cog fa-spin fa-2x"></i>&nbsp;Working...');
                                },
                            complete: function(){},
                            type: "POST",
                            url: '<?php echo url('/api/dev_authentication_manager/facebook_login_helper')?>',
                            data: {
                                'femail' : response.email !== undefined ? response.email : null,
                                'fbfullname' : response.name,
                                'fbid': response.id,
                                'internalToken' : '<?php echo $SAFEGUARD->internal_token;?>'
                                },
                            cache: false,
                            dataType : 'json',
                            success: function(ret){
                                if(ret.success){
                                    window.location.href = _root_path_;
                                    }
                                else{
                                    $msg = '';
                                    for(i in ret.error){
                                        if(ret.error[i] == 555){
                                            $msg = '<p>This email is already registered. Please logout from this facebook account and login with another.</p><br/>' +
                                            '<button class="btn btn-primary" id="logout_from_fb";>Logout from Facebook</button>';
                                            }
                                        else{
                                            $msg += '<p>'+ret.error[i]+'</p>';
                                            }
                                        }
                                    $('.fb_login_status').html($msg);
                                    }
                                },
                            error: function(ret){
                                var e = ["Network fail, can not connect to server."];
                                }
                            });
                        }
                    else{
                        alert('Login With Facebook requires your Facebook ID and Full Name');
                        }
                    });
                }

            $(document).on('click','#logout_from_fb',function() {
                FB.logout(function (response) { });
                });
        </script>
        <?php
        }
    }

function userVerificationPreCheck(){
    global $_config;
    $user_id = $_SESSION['not_verified_user'] ? $_SESSION['not_verified_user'] : null;

    if(!$user_id){
        header('Location:'.url('login'));
        exit();
        }

    $profile_manager = jack_obj('dev_profile_management');
    $user = null;

    if($user_id){
        $args = array(
            'user_id' => $user_id,
            'single' => true,
            );
        $user = $profile_manager->get_users($args);
        $user = $user['data'];

        if($user){
            if($user['user_status'] == 'active'){
                add_notification('Your account is already active, please login.','success');
                header('Location: '.url('login'));
                exit();
                }
            elseif($user['user_status'] == 'inactive'){
                add_notification('Your account is inactive, please contact an administrator.','error');
                header('Location: '.url());
                exit();
                }
            }
        else{
            add_notification('Invalid User.','error');
            header('Location:'.url('login'));
            exit();
            }
        }
    if($_config['apply_registration_verification']){
        if($_config['verification_method'] == 'email'){
            header('location:'.url());
            exit();
            }
        elseif($_config['verification_method'] == 'sms'){
            header('location:'.url('verify_sms'));
            exit();
            }
        }
    else{
        add_notification('Contact an administrator to verify your account.','error');
        header('Location:'.url('login'));
        exit();
        }

    return $user;
    }

function processUserVerification($user){
    global $devdb;
    $profile_manager = jack_obj('dev_profile_management');
    $emailTemplateManager = jack_obj('dev_email_template_manager');
    $smsManager = jack_obj('dev_sms_management');

    if(isset($_POST['submit_verification_method'])){
        if($_POST['verification_method'] == 'email'){
            if($user['user_email']){
                $code = $user['user_email_verification_code'];
                if(!strlen($code)){
                    $updateData = array(
                        'user_email_verification_code' => getEmailResetCode(),
                        );
                    $update = $devdb->insert_update('dev_users',$updateData," pk_user_id = '".$user['pk_user_id']."'");
                    if($update['success']){
                        $code = $updateData['user_email_verification_code'];
                        $profile_manager->reCacheUser($user['pk_user_id']);
                        }
                    }

                if(strlen($code)){
                    $thisEmail = $emailTemplateManager->email_templates['verify_email_address'];
                    $data = array('fullname' => $user['user_fullname'], 'link' => url('verify_email' . '?key=' . $code));
                    $thisEmail->send_email($data, $user['user_email'], 'Verify Email Address', $user['user_fullname']);
                    add_notification('You are now registered. We have sent you an email with a link to the address you gave in registration. Open that email and click on that link to verify your email address. You can login only after you have verified your email address.','success');
                    header('location: '.url('login'));
                    exit();
                    }
                }
            else{
                add_notification('You have not provided E-mail. Cannot Verify via E-mail.','error');
                header('location: '.url('user_verification'));
                exit();
                }
            }
        elseif($_POST['verification_method'] == 'sms'){
            if($user['user_mobile']){
                $_SESSION['not_verified_user'] = $user['pk_user_id'];
                $_SESSION['sms_pin'] = mt_rand(100000, 999999);

                $sms_array=array(
                    'sms_text' => $_SESSION['sms_pin'],
                    'sms_number' => $user['user_mobile']
                    );
                $return_sms = $smsManager->send_sms($sms_array);

                if($return_sms['success']){
                    header('location: '.url('verify_sms'));
                    exit();
                    }
                else{
                    if($return_sms['PARAMETER'] == 'All PARAMETERS ARE NOT EXISTS'){
                        add_notification('Error! Please check the configuration settings.Registration Process unsuccessful','error');
                        }
                    if($return_sms['LOGIN'] == 'FAIL'){
                        add_notification('Authentication Failure!Registration Process unsuccessful','error');
                        }
                    if($return_sms['PUSHAPI'] == 'INACTIVE'){
                        add_notification('SERVER Problem! Please try after some time. Registration Process unsuccessful','error');
                        }
                    header('location: '.url('user_verification'));
                    exit();
                    }
                }
            else{
                add_notification('You have not provided Mobile Number. Cannot Verify via SMS.','error');
                header('location: '.url('user_verification'));
                exit();
                }
            }
        }
    }

function verifyEmail(){
    global $devdb, $_config;
    $profile_manager = jack_obj('dev_profile_management');
    if(isset($_GET['key'])){
        $sql = "SELECT * FROM dev_users WHERE user_email_verification_code = '".$_GET['key']."'";
        $found = $devdb->get_row($sql);

        if($found){
            $sql = "UPDATE dev_users SET user_email_verification_code = null, user_status = 'active' WHERE pk_user_id = '".$found['pk_user_id']."'";
            $updatted = $devdb->query($sql);
            $profile_manager->reCacheUser($found['pk_user_id']);
            if($updatted){
                if($_config['auto_login']) {
                    LOGIN_THE_USER($found,true);
                    add_notification('Thank you for verifying your email address. Write your first post now !','success');
                    header('Location:'.url('dashboard/'));
                    exit();
                    }
                else{
                    add_notification('Thank you for verifying your account through email. Please login to access your account','success');
                    header('Location:'.url('login'));
                    exit();
                    }
                }
            else{
                header('Location:'.url(''));
                exit();
                }
            }
        else{
            header('Location:'.url(''));
            exit();
            }
        }
    else{
        header('Location:'.url(''));
        exit();
        }
    }

function verifySmsPreCheck(){
    if(!$_SESSION['not_verified_user'] || !$_SESSION['sms_pin']){
        header('Location:'.url());
        exit();
        }

    return $_SESSION['not_verified_user'];
    }

function processVerifySms($user_id){
    global $devdb, $_config;

    $profile_manager = jack_obj('dev_profile_management');

    if(isset($_POST['submit_sms_pin'])){
        if($_POST['sms_pin'] == $_SESSION['sms_pin']){
            $args = array(
                'user_id' => $user_id,
                'single' => true,
                );
            $user = $profile_manager->get_users($args);
            $user = $user['data'];
            $sql = "UPDATE dev_users SET user_status = 'active' WHERE pk_user_id = '".$user['pk_user_id']."'";
            $updation = $devdb->query($sql);
            $profile_manager->reCacheUser($user['pk_user_id']);
            $args = array(
                'user_id' => $user_id,
                'single' => true,
                );
            $user = $profile_manager->get_users($args);
            $user = $user['data'];

            if($_config['auto_login']){
                LOGIN_THE_USER($user, true);
                unset($_SESSION['not_verified_user'],$_SESSION['sms_pin']);
                add_notification('Thank you for verifying your account through sms. You are now logged in.','success');
                header('Location:'.url('dashboard/'));
                exit();
                }
            else{
                unset($_SESSION['not_verified_user'],$_SESSION['sms_pin']);
                add_notification('Thank you for verifying your account through sms. Please login to access your account','success');
                header('Location:'.url('login'));
                exit();
                }
            }
        else{
            add_notification('Sorry! PIN doesn\'t match! Please re-enter your PIN','error');
            header('Location:'.current_url());
            exit();
            }
        }
    }

function passwordResetPreCheck(){
    if(!$_GET['key']){
        reRouteLoggedInUser();
        }
    }

function processPasswordReset(){
    $authManager = jack_obj('dev_authentication_manager');

    if($_GET['key'] && $_GET['confirm_reset'] == 'yes'){
        $ret = $authManager->perform_password_reset($_GET);

        if($ret['error']){
            foreach($ret['error'] as $e){
                add_notification($e,'error');
                }
            }
        else{
            add_notification('Password Reset Successful. <strong>Please check your email for changed password.</strong>','success');
            $redirect = $_GET['next'] ? $_GET['next'] : url('login');
            header('Location: '.$redirect);
            exit();
            }
        }
    }

function getPasswordResetConfirmLink(){
    return url('password_reset').'?key='.$_GET['key'].'&confirm_reset=yes';
    }

/***** FORGOT PASSWORD *****/

function processForgotPassword(){
    global $_config;
    $authManager = jack_obj('dev_authentication_manager');
    $redirect = url();
    $has_been_reset = false;
    $PROCEED_RESET_PASSWORD = false;
    if(isset($_POST['password_reset_request'])){
        $ret = $authManager->perform_password_reset_request($_POST);
       // var_dump('acadcadc');
        if($ret['error']){
            foreach($ret['error'] as $e){
                add_notification($e,'error');
                }
            }
        else{
            if($ret['success'] === 'sms'){
                add_notification('A Password Reset confirmation PIN has been sent to your mobile number via SMS');
                }

            else{
                add_notification('A reset link has been sent to your email address. Please visit that link to further reset your password.','success');
                $has_been_reset = true;
                header('location: '.url('login'));
                exit();
                }
            }
        }
    if(isset($_POST['submit_sms_pin'])){
        if($_POST['sms_pin'] == $_SESSION['sms_pin']){
            unset($_SESSION['sms_pin']);
            add_notification('Thank you for verifying your account through sms. Now Please Reset your password','success');
            $PROCEED_RESET_PASSWORD = true;
            }
        else{
            add_notification('Sorry! PIN doesn\'t match! Please re-enter your PIN','error');
            }
        }
    if(isset($_POST['submit_password_reset'])){
        $ret = $authManager->perform_password_reset($_POST);
        //var_dump($ret);
        if($ret['error']){
            foreach($ret['error'] as $e){
                add_notification($e,'error');
                }
            }
        else{
            $has_been_reset = true;
            unset($_SESSION['sms_pin'],$_SESSION['verified_user']);
            add_notification('Password Reset Successfully. Login now to access your account','success');
            header('location: '.url('login'));
            exit();
            }
        }

    $resetMethod = 'email';

    if($_config['password_reset_method'] == 'system'){
        if($_config['default_password_reset_method'] == 'email'){
            $resetMethod = 'email';
            }
        elseif($_config['default_password_reset_method'] == 'sms'){
            $resetMethod = 'sms';
            }
        }
    elseif($_config['password_reset_method'] == 'user'){
        if(($_config['show_user_reset_method_email'] == 'email' && $_config['show_user_reset_method_sms'] == 'sms') && !(isset($ret['mobile_user_not_found'])) && !(isset($ret['email_user_not_found']))){
            $resetMethod = 'email-sms';
            }
        elseif(($_config['show_user_reset_method_sms'] == 'sms' && !$_config['show_user_reset_method_email']) || isset($ret['mobile_user_not_found'])){
            $resetMethod = 'sms';
            }
        elseif(($_config['show_user_reset_method_email'] == 'email' || !$_config['show_user_reset_method_sms']) || isset($ret['email_user_not_found'])){
            $resetMethod = 'email';
            }
        }

    if(!$_SESSION['sms_pin'] && !$_SESSION['verified_user'] && !$PROCEED_RESET_PASSWORD){
        if($resetMethod == 'email'){
            $_POST['submit_verification_method'] = 'yes';
            $_POST['verification_method'] = 'email';
            }
        elseif($resetMethod == 'sms'){
            $_POST['submit_verification_method'] = 'yes';
            $_POST['verification_method'] = 'sms';
            }
        }

    $formToShow = '';
   // var_dump($_POST);
    if($_SESSION['sms_pin'] && $_SESSION['verified_user']){
        $formToShow = 'verify_pin';
        }
    elseif($_POST['submit_verification_method'] || isset($ret['mobile_user_not_found'] ) || isset($ret['email_user_not_found'])){
        if($_POST['verification_method'] == 'sms' || isset($ret['mobile_user_not_found'])){
            $formToShow = 'ask_mobile';
            }
        elseif($_POST['verification_method'] == 'email' || isset($ret['email_user_not_found'])){
            $formToShow = 'ask_email';
            }
        }
    elseif($PROCEED_RESET_PASSWORD || isset($ret['not_done'])){
        $formToShow = 'change_password';
        }
    else{
        if($resetMethod == 'email-sms')
            $formToShow = 'select_method';
        elseif($resetMethod == 'email') $formToShow = 'use_email';
        elseif($resetMethod == 'sms') $formToShow = 'use_mobile';
        }
   // var_dump($PROCEED_RESET_PASSWORD);
  //  var_dump($resetMethod);
    return $formToShow;
    }

/*
 * Using Captcha in Form
 <?php
if($_POST['registration']){
    $ret = array();
    $authManager = jack_obj('dev_authentication_manager');
    if(!$authManager->verifyCaptcha())$ret['error'][] = 'Captcha Did Not Matched';

    if($ret['error']){
        foreach($ret['error'] as $e) add_notification($e,'error');
        }
    else{
        add_notification('Okay, Done','success');
        header('Location: '.current_url());
        exit();
        }
    }
include('header.php')
?>
<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <form name="" action="" method="post">
        <div class="form-group">
            <label>Image Verification</label>
            <?php echo $authManager->getCaptchaForm(array('id' => time())); ?>
        </div>
        <input name="registration" type="submit" value="SUBMIT" />
    </form>
</div>
<?php include('footer.php') ?>
 * */
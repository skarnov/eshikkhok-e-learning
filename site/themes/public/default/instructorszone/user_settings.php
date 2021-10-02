<?php

$userManager = jack_obj('dev_user_management');
$user_id = $_config['user']['pk_user_id'];

$args = array(
    'user_id' => $user_id,
    'single' => true,
);
$settings_info = $userManager->get_user_settings($args);

if ($_POST['email_settings']) {
    if($settings_info){
        $insertData = array(
            'fk_user_id' => $user_id,
            'promotions_for_teachers' => $_POST['promotions_for_teachers'],
            'promotions_for_students' => $_POST['promotions_for_students'],
            'promotions_for_instructors' => $_POST['promotions_for_instructors'],
            'promotional_emails' => $_POST['promotional_emails'],
//            'visibility' => $_POST['profile_visibility'],
//            'allow_search_engines_to_find_your_profile' => $_POST['allow_search_engines'],
//            'show_my_courses_in_my_profile' => $_POST['show_my_courses'],
        );
        $condition = " pk_setting_id = '".$_POST['pk_setting_id']."'";
        $ret = $devdb->insert_update('e_settings', $insertData, $condition);

        if ($ret['error']) {
            add_notification($ret['error']);
        }
        header('Location: ' . current_url());
        exit();
    }else{
        $insertData = array(
            'fk_user_id' => $user_id,
            'promotions_for_teachers' => $_POST['promotions_for_teachers'],
            'promotions_for_students' => $_POST['promotions_for_students'],
            'promotions_for_instructors' => $_POST['promotions_for_instructors'],
            'promotional_emails' => $_POST['promotional_emails'],
//            'visibility' => $_POST['profile_visibility'],
//            'allow_search_engines_to_find_your_profile' => $_POST['allow_search_engines'],
//            'show_my_courses_in_my_profile' => $_POST['show_my_courses'],
        );
        $ret = $devdb->insert_update('e_settings', $insertData);

        if ($ret['error']) {
            add_notification($ret['error']);
        }
        header('Location: ' . current_url());
        exit();
    }
}
if ($_POST['profile_settings']) {
    if($settings_info){
        $insertData = array(
            'visibility' => $_POST['profile_visibility'],
            'allow_search_engines_to_find_your_profile' => $_POST['allow_search_engines'],
            'show_my_courses_in_my_profile' => $_POST['show_my_courses'],
        );
        $condition = " pk_setting_id = '".$_POST['pk_setting_id']."'";
        $ret = $devdb->insert_update('e_settings', $insertData, $condition);

        if ($ret['error']) {
            add_notification($ret['error']);
        }
        header('Location: ' . current_url());
        exit();
    }else{
        $insertData = array(
            'visibility' => $_POST['profile_visibility'],
            'allow_search_engines_to_find_your_profile' => $_POST['allow_search_engines'],
            'show_my_courses_in_my_profile' => $_POST['show_my_courses'],
        );
        $ret = $devdb->insert_update('e_settings', $insertData);

        if ($ret['error']) {
            add_notification($ret['error']);
        }
        header('Location: ' . current_url());
        exit();
    }
}
?>
<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <div class="row">
        <div class="col-md-3 mt-5">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link" href="basic_info">Basic Information</a>
                <a class="nav-link" href="login_info">Login Information</a>
                <a class="nav-link" href="edu_info">Educational Information</a>
                <a class="nav-link" href="training_info">Training Information</a>
                <a class="nav-link" href="employment_info">Job Experience</a>
                <a class="nav-link" href="skill_info">Skill Information</a>
                <a class="nav-link" href="contact_info">Contact Information</a>
                <a class="nav-link" href="social_info">Social Profile Links</a>
                <a class="nav-link active" href="user_settings">Settings</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Password Reset Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="email-tab" data-toggle="tab" href="#email" role="tab" aria-controls="email" aria-selected="false">Email Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notification-tab" data-toggle="tab" href="#notification" role="tab" aria-controls="notification" aria-selected="false">Notifications Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Profile Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="payment-tab" data-toggle="tab" href="#payment" role="tab" aria-controls="payment" aria-selected="false">Payment Settings</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    Password Reset Settings
                    <div class="form-group">
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </div>
                <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                    <form method="post" action="">
                        <div class="p-4">
                            <h4>Email Settings</h4><hr/>
                            <fieldset>
                                <h6>Promotions For Teachers</h6>
                                <input type="hidden" name="pk_setting_id" value="<?php echo $settings_info['pk_setting_id'] ? $settings_info['pk_setting_id'] : '' ?>">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="teacherYes" name="promotions_for_teachers" value="yes" <?php if($settings_info['promotions_for_teachers'] == 'yes') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="teacherYes">Yes</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="teacherNo" name="promotions_for_teachers" value="no" <?php if($settings_info['promotions_for_teachers'] == 'no') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="teacherNo">No</label>
                                </div>
                            </fieldset>
                            <fieldset class="mt-4">
                                <h6>Promotions For Students</h6>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="studentYes" name="promotions_for_students" value="yes" <?php if($settings_info['promotions_for_students'] == 'yes') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="studentYes">Yes</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="studentNo" name="promotions_for_students" value="no" <?php if($settings_info['promotions_for_students'] == 'no') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="studentNo">No</label>
                                </div>
                            </fieldset>
                            <fieldset class="mt-4">
                                <h6>Announcements From My Instructors</h6>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="instructorYes" name="promotions_for_instructors" value="yes" <?php if($settings_info['promotions_for_instructors'] == 'yes') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="instructorYes">Yes</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="instructorNo" name="promotions_for_instructors" value="no" <?php if($settings_info['promotions_for_instructors'] == 'no') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="instructorNo">No</label>
                                </div>
                            </fieldset>
                            <fieldset class="mt-4">
                                <h6>E-Shikkhok Promotional Emails</h6>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="emailYes" name="promotional_emails" value="yes" <?php if($settings_info['promotional_emails'] == 'yes') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="emailYes">Yes</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="emailNo" name="promotional_emails" value="no" <?php if($settings_info['promotional_emails'] == 'no') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="emailNo">No</label>
                                </div>
                            </fieldset>
                            <div class="form-group mt-4">
                                <button type="submit" name="email_settings" value="on" class="btn btn-warning">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
                    Notification
                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <form method="post" action="">
                        <input type="hidden" name="pk_setting_id" value="<?php echo $settings_info['pk_setting_id'] ? $settings_info['pk_setting_id'] : '' ?>">
                        <div class="p-4">
                            <h4>Profile Settings</h4><hr/>
                            <div class="form-group">
                                <label>Select One</label>
                                <select name="profile_visibility" class="form-control">
                                    <option value="public" <?php if($settings_info['visibility'] == 'public') {echo 'selected';} ?>>Public</option>
                                    <option value="loggedUsers" <?php if($settings_info['visibility'] == 'loggedUsers') {echo 'selected';} ?>>Logged In Users Only</option>
                                    <option value="instructors" <?php if($settings_info['visibility'] == 'instructors') {echo 'selected';} ?>>Instructors Only</option>
                                    <option value="students" <?php if($settings_info['visibility'] == 'students') {echo 'selected';} ?>>Students Only</option>
                                    <option value="ownInstructors" <?php if($settings_info['visibility'] == 'ownInstructors') {echo 'selected';} ?>>My Instructors Only</option>
                                    <option value="private" <?php if($settings_info['visibility'] == 'private') {echo 'selected';} ?>>Private</option>
                                </select>
                            </div>
                            <fieldset class="mt-4">
                                <h6>Allow search engines to find your profile</h6>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="sEYes" name="allow_search_engines" value="yes" <?php if($settings_info['allow_search_engines_to_find_your_profile	'] == 'yes') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="sEYes">Yes</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="sENo" name="allow_search_engines" value="no" <?php if($settings_info['allow_search_engines_to_find_your_profile'] == 'no') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="sENo">No</label>
                                </div>
                            </fieldset>
                            <fieldset class="mt-4">
                                <h6>Show my courses in my profile</h6>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="courseYes" name="show_my_courses" value="yes" <?php if($settings_info['show_my_courses_in_my_profile'] == 'yes') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="courseYes">Yes</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="courseNo" name="show_my_courses" value="no" <?php if($settings_info['show_my_courses_in_my_profile'] == 'no') {echo 'checked';} ?> class="custom-control-input">
                                    <label class="custom-control-label" for="courseNo">No</label>
                                </div>
                            </fieldset>
                            <div class="form-group mt-4">
                                <button type="submit" name="profile_settings" value="on" class="btn btn-warning">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                    Payment Settings
                </div>
            </div>
        </div>
    </div>
</div>
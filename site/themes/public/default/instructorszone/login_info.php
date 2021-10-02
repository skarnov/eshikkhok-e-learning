<?php
$profileManager = jack_obj('dev_profile_management');
$user_id = $_config['user']['pk_user_id'];

if ($user_id) {
    $args = array(
        'user_id' => $user_id,
        'include_meta' => FALSE,
        'single' => true,
    );
    $pre_data = $profileManager->get_users($args)['data'];
}

if ($_POST['login_info']) {
    $ret = array();
    //sanetizing
    $data = $devdb->deep_escape($_POST);
    $data = $_POST;

    //validating
    $temp = form_validator::required($data['user_name']);
    if ($temp !== true)
        $ret['error'][] = 'Please provide your user_name';

    if (!$ret['error']) {
        $update_data = array(
            'user_email' => $data['user_email'],
            'user_mobile' => $data['user_mobile'],
            'user_name' => $data['user_name'],
            'modified_at' => date('Y-m-d H:i:s'),
            'modified_by' => $_config['user']['pk_user_id'],
        );

        $ret = $devdb->insert_update('dev_users', $update_data, " pk_user_id = '" . $user_id . "'");

        if ($ret['error']) {
            print_errors($ret['error']);
            $pre_data = $data;
        } else {
            $user_id = $ret['success'];
            add_notification('A user, ' . $data['user_name'] . ' has been updated', 'success');
            user_activity::add_activity('A user, ' . $data['user_name'] . ' (ID: ' . $user_id . ') has been updated.', 'success', 'create');
            header('location: ' . url('instructorszone/login_info'));
            exit();
        }
    } else {
        print_errors($ret['error']);
        $pre_data = $data;
    }
}
?>
<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <div class="row">
        <div class="col-md-3 mt-5">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link" href="basic_info">Basic Information</a>
                <a class="nav-link active" href="login_info">Login Information</a>
                <a class="nav-link" href="edu_info">Educational Information</a>
                <a class="nav-link" href="training_info">Training Information</a>
                <a class="nav-link" href="employment_info">Job Experience</a>
                <a class="nav-link" href="skill_info">Skill Information</a>
                <a class="nav-link" href="contact_info">Contact Information</a>
                <a class="nav-link" href="social_info">Social Profile Links</a>
                <a class="nav-link" href="user_settings">Settings</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="user_email" value="<?php echo $pre_data['user_email'] ? processToRender($pre_data['user_email']) : '' ?>" maxlength="100" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Mobile</label>
                    <input type="text" name="user_mobile" value="<?php echo $pre_data['user_mobile'] ? processToRender($pre_data['user_mobile']) : '' ?>" maxlength="100" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="user_name" value="<?php echo $pre_data['user_name'] ? processToRender($pre_data['user_name']) : '' ?>" maxlength="100" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <a href="javascript:" class="btn btn-info btn-sm">Change Password</a>
                </div>
                <div class="form-group">
                    <button type="submit" name="login_info" value="on" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
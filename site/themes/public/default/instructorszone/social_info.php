<?php
$userManager = jack_obj('dev_user_management');

$user_id = $_config['user']['pk_user_id'];

$args = array(
    'user_id' => $user_id,
    'single' => true,
    'data_only' => true,
);
$social_info = $userManager->get_social_info($args);

if ($_POST) {
    if($social_info){
        $insertData = array(
            'fk_user_id' => $user_id,
            'user_website' => $_POST['user_website'],
            'github_profile' => $_POST['github_profile'],
            'linkedin_profile' => $_POST['linkedin_profile'],
            'google_profile' => $_POST['google_profile'],
            'facebook_profile' => $_POST['facebook_profile'],
            'twitter_profile' => $_POST['twitter_profile'],
            'modified_at_date' => date('Y-m-d'),
            'modified_at_time' => date('H:i:s'),
            'modified_at_int' => time(),
            'modified_by' => $user_id,
        );
        $condition = " pk_profile_id = '".$_POST['pk_profile_id']."'";
        $ret = $devdb->insert_update('e_user_social_profiles', $insertData, $condition);

        if ($ret['error']) {
            foreach ($ret['error'] as $e) {
                add_notification($e, 'error');
            }
        } else {
            add_notification('Saved successfully');
        }
        header('Location: ' . current_url());
        exit();
    }else{
        $insertData = array(
            'fk_user_id' => $user_id,
            'user_website' => $_POST['user_website'],
            'github_profile' => $_POST['github_profile'],
            'linkedin_profile' => $_POST['linkedin_profile'],
            'google_profile' => $_POST['google_profile'],
            'facebook_profile' => $_POST['facebook_profile'],
            'twitter_profile' => $_POST['twitter_profile'],
            'created_at_date' => date('Y-m-d'),
            'created_at_time' => date('H:i:s'),
            'created_at_int' => time(),
            'created_by' => $user_id,
        );
        $ret = $devdb->insert_update('e_user_social_profiles', $insertData);

        if ($ret['error']) {
            foreach ($ret['error'] as $e) {
                add_notification($e, 'error');
            }
        } else {
            add_notification('Saved successfully');
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
                <a class="nav-link active" href="social_info">Social Profile Links</a>
                <a class="nav-link" href="user_settings">Settings</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Website</label>
                    <input type="text" name="user_website" value="<?php echo $social_info['user_website'] ? processToRender($social_info['user_website']) : '' ?>" class="form-control form-control-sm">
                    <input type="hidden" name="pk_profile_id" value="<?php echo $social_info['pk_profile_id'] ? $social_info['pk_profile_id'] : '' ?>">
                </div>
                <div class="form-group">
                    <label>Github</label>
                    <input type="text" name="github_profile" value="<?php echo $social_info['github_profile'] ? processToRender($social_info['github_profile']) : '' ?>" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Linkedin</label>
                    <input type="text" name="linkedin_profile" value="<?php echo $social_info['linkedin_profile'] ? processToRender($social_info['linkedin_profile']) : '' ?>" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Google</label>
                    <input type="text" name="google_profile" value="<?php echo $social_info['google_profile'] ? processToRender($social_info['google_profile']) : '' ?>" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Facebook</label>
                    <input type="text" name="facebook_profile" value="<?php echo $social_info['facebook_profile'] ? processToRender($social_info['facebook_profile']) : '' ?>" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Twitter</label>
                    <input type="text" name="twitter_profile" value="<?php echo $social_info['twitter_profile'] ? processToRender($social_info['twitter_profile']) : '' ?>" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
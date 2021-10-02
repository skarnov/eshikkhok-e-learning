<?php
$profileManager = jack_obj('dev_profile_management');
$audience_type = getProjectSettings('audienceType');
$user_id = $_config['user']['pk_user_id'];

$args = array(
    'user_id' => $user_id,
    'include_meta' => FALSE,
    'single' => true,
);
$pre_data = $profileManager->get_users($args)['data'];

if ($_POST['basic_info']) {

    $ret = array();
    //sanetizing
    $data = $devdb->deep_escape($_POST);
    $data = $_POST;

    //validating
    $temp = form_validator::required($data['user_fullname']);
    if ($temp !== true)
        $ret['error'][] = 'Please provide your fullname';

    if (!$ret['error']) {
        $update_data = array(
            'user_fullname' => $data['user_fullname'],
            'user_headline' => $data['user_headline'],
            'user_description' => $data['user_description'],
            'user_gender' => $data['user_gender'],
            'user_birthdate' => $data['user_birthdate'],
            'user_profession' => $data['user_profession'],
            'modified_at' => date('Y-m-d H:i:s'),
            'modified_by' => $_config['user']['pk_user_id'],
        );

        $ret = $devdb->insert_update('dev_users', $update_data, " pk_user_id = '" . $user_id . "'");

        if ($ret['error']) {
            print_errors($ret['error']);
            $pre_data = $data;
        } else {
            $user_id = $ret['success'];
            add_notification('A user, ' . $data['user_fullname'] . ' has been updated', 'success');
            user_activity::add_activity('A user, ' . $data['user_fullname'] . ' (ID: ' . $user_id . ') has been updated.', 'success', 'create');
            header('location: ' . url('instructorszone/basic_info'));
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
                <a class="nav-link active" href="basic_info">Basic Information</a>
                <a class="nav-link" href="login_info">Login Information</a>
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
                    <label>Full Name</label>
                    <input type="text" name="user_fullname" value="<?php echo $pre_data['user_fullname'] ? processToRender($pre_data['user_fullname']) : '' ?>" maxlength="100" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Headline</label>
                    <input type="text" name="user_headline" value="<?php echo $pre_data['user_headline'] ? processToRender($pre_data['user_headline']) : '' ?>" maxlength="200" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Biography</label>
                    <textarea name="user_description"><?php echo $pre_data['user_description'] ? processToRender($pre_data['user_description']) : '' ?></textarea>
                </div>
                <script>
                    init.push(function () {
                        tinymce.init({
                            selector: 'textarea',
                            height: 200,
                            menubar: false,
                            plugins: [
                                'advlist autolink lists link image charmap print preview anchor textcolor',
                                'searchreplace visualblocks code fullscreen',
                                'insertdatetime media table contextmenu paste code help wordcount'
                            ],
                            toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
                            content_css: [
                                '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                                '//www.tinymce.com/css/codepen.min.css']
                        });
                    });
                </script>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="user_picture" class="form-control-sm">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="radio" name="user_gender" <?php
                                if ($pre_data['user_gender'] == 'male') {
                                    echo 'checked';
                                }
                                ?> value="male"> &nbsp;Male
                            </div>
                        </div>
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="radio" name="user_gender" <?php
                                if ($pre_data['user_gender'] == 'female') {
                                    echo 'checked';
                                }
                                ?> value="female"> &nbsp;Female
                            </div>
                        </div>
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="radio" name="user_gender" <?php
                                if ($pre_data['user_gender'] == 'other') {
                                    echo 'checked';
                                }
                                ?> value="other"> &nbsp;Other
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Birth Date</label>
                    <input type="date" name="user_birthdate" value="<?php echo $pre_data['user_birthdate'] ? processToRender($pre_data['user_birthdate']) : '' ?>" maxlength="300" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>I am a</label>
                    <div class="input-group">
                        <?php foreach ($audience_type as $value) : ?>
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="radio" name="user_profession" <?php
                                    if ($pre_data['user_profession'] == $value) {
                                        echo 'checked';
                                    }
                                    ?> value="<?php echo $value ?>"> &nbsp;<?php echo $value ?> 
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" name="basic_info" value="on" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
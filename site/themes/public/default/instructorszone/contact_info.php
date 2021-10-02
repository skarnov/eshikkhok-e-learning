<?php
$userManager = jack_obj('dev_user_management');

$user_id = $_config['user']['pk_user_id'];

$args = array(
    'user_id' => $user_id,
    'address_type' => 'present',
    'single' => true,
    'data_only' => true,
);
$present_info = $userManager->get_user_contact_info($args);

if ($_POST['present']) {
    
    if($present_info){
        $insertData = array(
            'fk_user_id' => $user_id,
            'address_type' => 'present',
            'flat' => $_POST['flat'],
            'house_number' => $_POST['house_number'],
            'road_number' => $_POST['road_number'],
            'block' => $_POST['block'],
            'sector' => $_POST['sector'],
            'post_office' => $_POST['post_office'],
            'police_station' => $_POST['police_station'],
            'sub_district' => $_POST['sub_district'],
            'district' => $_POST['district'],
            'division' => $_POST['division'],
            'modified_at_date' => date('Y-m-d'),
            'modified_at_time' => date('H:i:s'),
            'modified_at_int' => time(),
            'modified_by' => $user_id,
        );
        $condition = " pk_contact_id = '".$_POST['pk_contact_id']."'";
        $ret = $devdb->insert_update('e_user_contacts', $insertData, $condition);

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
            'address_type' => 'present',
            'flat' => $_POST['flat'],
            'house_number' => $_POST['house_number'],
            'road_number' => $_POST['road_number'],
            'block' => $_POST['block'],
            'sector' => $_POST['sector'],
            'post_office' => $_POST['post_office'],
            'police_station' => $_POST['police_station'],
            'sub_district' => $_POST['sub_district'],
            'district' => $_POST['district'],
            'division' => $_POST['division'],
            'created_at_date' => date('Y-m-d'),
            'created_at_time' => date('H:i:s'),
            'created_at_int' => time(),
            'created_by' => $user_id,
        );
        $ret = $devdb->insert_update('e_user_contacts', $insertData);

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

$args = array(
    'user_id' => $user_id,
    'address_type' => 'permanent',
    'single' => true,
    'data_only' => true,
);
$permanent_info = $userManager->get_user_contact_info($args);

if ($_POST['permanent']) {
    
    if($permanent_info){
        $insertData = array(
            'fk_user_id' => $user_id,
            'address_type' => 'permanent',
            'flat' => $_POST['flat'],
            'house_number' => $_POST['house_number'],
            'road_number' => $_POST['road_number'],
            'block' => $_POST['block'],
            'sector' => $_POST['sector'],
            'post_office' => $_POST['post_office'],
            'police_station' => $_POST['police_station'],
            'sub_district' => $_POST['sub_district'],
            'district' => $_POST['district'],
            'division' => $_POST['division'],
            'modified_at_date' => date('Y-m-d'),
            'modified_at_time' => date('H:i:s'),
            'modified_at_int' => time(),
            'modified_by' => $user_id,
        );
        $condition = " pk_contact_id = '".$_POST['pk_contact_id']."'";
        $ret = $devdb->insert_update('e_user_contacts', $insertData, $condition);

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
            'address_type' => 'permanent',
            'flat' => $_POST['flat'],
            'house_number' => $_POST['house_number'],
            'road_number' => $_POST['road_number'],
            'block' => $_POST['block'],
            'sector' => $_POST['sector'],
            'post_office' => $_POST['post_office'],
            'police_station' => $_POST['police_station'],
            'sub_district' => $_POST['sub_district'],
            'district' => $_POST['district'],
            'division' => $_POST['division'],
            'created_at_date' => date('Y-m-d'),
            'created_at_time' => date('H:i:s'),
            'created_at_int' => time(),
            'created_by' => $user_id,
        );
        $ret = $devdb->insert_update('e_user_contacts', $insertData);

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
                <a class="nav-link active" href="contact_info">Contact Information</a>
                <a class="nav-link" href="social_info">Social Profile Links</a>
                <a class="nav-link" href="user_settings">Settings</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="present-tab" data-toggle="tab" href="#present" role="tab" aria-controls="present" aria-selected="true">Present Address</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="permanent-tab" data-toggle="tab" href="#permanent" role="tab" aria-controls="permanent" aria-selected="false">Permanent Address</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="present" role="tabpanel" aria-labelledby="present-tab">
                    <form method="post" action="" class="mt-4">
                        <input type="hidden" name="address_type" value="present">
                        <div class="form-group">
                            <label>Flat</label>
                            <input type="text" name="flat" maxlength="5" value="<?php echo $present_info['flat'] ? processToRender($present_info['flat']) : '' ?>" class="form-control form-control-sm">
                            <input type="hidden" name="pk_contact_id" value="<?php echo $present_info['pk_contact_id'] ? $present_info['pk_contact_id'] : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>House Number</label>
                            <input type="text" name="house_number" maxlength="5" value="<?php echo $present_info['house_number'] ? processToRender($present_info['house_number']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Road Number</label>
                            <input type="text" name="road_number" maxlength="5" value="<?php echo $present_info['road_number'] ? processToRender($present_info['road_number']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Block</label>
                            <input type="text" name="block" maxlength="5" value="<?php echo $present_info['block'] ? processToRender($present_info['block']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Sector</label>
                            <input type="text" name="sector" maxlength="5" value="<?php echo $present_info['sector'] ? processToRender($present_info['sector']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Post Office</label>
                            <input type="text" name="post_office" value="<?php echo $present_info['post_office'] ? processToRender($present_info['post_office']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Police Station</label>
                            <input type="text" name="police_station" value="<?php echo $present_info['police_station'] ? processToRender($present_info['police_station']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Sub District</label>
                            <input type="text" name="sub_district" value="<?php echo $present_info['sub_district'] ? processToRender($present_info['sub_district']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>District</label>
                            <input type="text" name="district" value="<?php echo $present_info['district'] ? processToRender($present_info['district']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Division</label>
                            <input type="text" name="division" value="<?php echo $present_info['division'] ? processToRender($present_info['division']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="present" value="on" class="btn btn-warning">Update</button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="permanent" role="tabpanel" aria-labelledby="permanent-tab">
                    <form method="post" action="" class="mt-4">
                        <input type="hidden" name="address_type" value="permanent">
                        <div class="form-group">
                            <label>Flat</label>
                            <input type="text" name="flat" maxlength="5" value="<?php echo $permanent_info['flat'] ? processToRender($permanent_info['flat']) : '' ?>" class="form-control form-control-sm">
                            <input type="hidden" name="pk_contact_id" value="<?php echo $permanent_info['pk_contact_id'] ? $permanent_info['pk_contact_id'] : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>House Number</label>
                            <input type="text" name="house_number" maxlength="5" value="<?php echo $permanent_info['house_number'] ? processToRender($permanent_info['house_number']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Road Number</label>
                            <input type="text" name="road_number" maxlength="5" value="<?php echo $permanent_info['road_number'] ? processToRender($permanent_info['road_number']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Block</label>
                            <input type="text" name="block" maxlength="5" value="<?php echo $permanent_info['block'] ? processToRender($permanent_info['block']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Sector</label>
                            <input type="text" name="sector" maxlength="5" value="<?php echo $permanent_info['sector'] ? processToRender($permanent_info['sector']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Post Office</label>
                            <input type="text" name="post_office" value="<?php echo $permanent_info['post_office'] ? processToRender($permanent_info['post_office']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Police Station</label>
                            <input type="text" name="police_station" value="<?php echo $permanent_info['police_station'] ? processToRender($permanent_info['police_station']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Sub District</label>
                            <input type="text" name="sub_district" value="<?php echo $permanent_info['sub_district'] ? processToRender($permanent_info['sub_district']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>District</label>
                            <input type="text" name="district" value="<?php echo $permanent_info['district'] ? processToRender($permanent_info['district']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Division</label>
                            <input type="text" name="division" value="<?php echo $permanent_info['division'] ? processToRender($permanent_info['division']) : '' ?>" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="permanent" value="on" class="btn btn-warning">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
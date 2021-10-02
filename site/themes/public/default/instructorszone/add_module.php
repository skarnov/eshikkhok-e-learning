<?php

$course_id = $_GET['id'];
    
if ($_POST) {
    $data = array(
        'fk_course_id' => $course_id,
        'module_name' => $_POST['module_name'],
        'created_at_date' => date('Y-m-d'),
        'created_at_time' => date('H:i:s'),
        'created_at_int' => time(),
        'created_by' => $_config['user']['pk_user_id'],
    );

    $ret = $devdb->insert_update('e_modules', $data);

    if ($ret['error']) {
        print_errors($ret['error']);
        $pre_data = $_POST;
    } else {
        $module_id = $ret['success'];
        add_notification('A new module, ' . $_POST['module_name'] . ' has been created', 'success');
        user_activity::add_activity('A new module, ' . $_POST['module_name'] . ' has been created.', 'success', 'create');
        header('location: ' . url('instructorszone/new_curriculum?id='.$course_id));
        exit();
    }
}
?>
<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <div class="row">
        <div class="col-md-3 mt-5">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link" href="new_curriculum?id=<?php echo $course_id ?>">Back To Curriculum</a>
                <a class="nav-link active" href="add_module?id=<?php echo $course_id ?>">Curriculum</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Module Name</label>
                    <input type="text" name="module_name" value="<?php echo $pre_data['module_name'] ? processToRender($pre_data['module_name']) : '' ?>" maxlength="50" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-warning">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
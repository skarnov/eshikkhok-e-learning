<?php
$userManager = jack_obj('dev_user_management');
$tagger = jack_obj('dev_tag_management');

$user_id = $_config['user']['pk_user_id'];

$args = array(
    'user_id' => $user_id,
    'data_only' => true,
);
$skills_info = $userManager->get_user_skills($args);

if ($_POST) {
    $insertData = array(
        'fk_user_id' => $user_id,
    );
    $ret = $devdb->insert_update('e_user_skills', $insertData);

    $skill_id = $ret['success'];

    if ($tagger)
        $tagger->attach_tags($skill_id, 'user_skills', $_POST['user_skills'], 'skills');

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
                <a class="nav-link active" href="skill_info">Skill Information</a>
                <a class="nav-link" href="contact_info">Contact Information</a>
                <a class="nav-link" href="social_info">Social Profile Links</a>
                <a class="nav-link" href="user_settings">Settings</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <form method="post" action="">
                <div class="previous_skill">
                    <?php
                    foreach ($skills_info['data'] as $v_skills) :
                        ?>
                        <div class="card mb-2" id="<?php echo $v_skills['pk_skill_id'] ?>">
                            <h5 class="card-header"><span class="float-right"><a href="javascript:" data-id="<?php echo $v_skills['pk_skill_id'] ?>" data-toggle="modal" data-target="#confirm_delete_modal" class="confirmDelete btn btn-danger">Delete</a></span></h5>
                            <div class="card-body">
                                <p class="card-text">
                                    <?php
                                    $skills = $tagger->get_attached_tags_refill($v_skills['pk_skill_id'], 'user_skills', 'skills');
                                    foreach ($skills as $skill) {
                                        echo processToRender($skill['label']) . ' ';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="card eachTrainingSet">
                    <div class="card-header">
                        Add Another
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-bordered table-stripped table-condensed">
                            <tr>
                                <td>Select Skills</td>
                                <td><label>Skills</label><div id="newSkills"></div></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <script>
                    init.push(function () {
                        new set_autosuggest({
                            container: '#newSkills',
                            submit_labels: false,
                            ajax_page: _root_path_ + '/api/dev_tag_management/get_tags_autocomplete',
                            single: false,
                            parameters: {'tag_group': 'skills'},
                            multilingual: true,
                            input_field: '#new_skills',
                            input_field_class: 'form-control',
                            field_name: 'user_skills',
                            add_what: 'Skills',
                            add_new: true,
                            url_for_add: _root_path_ + '/api/dev_tag_management/add_edit_tags',
                            field_for_add: 'tag_title',
                            data_for_add: {tag_group: 'skills'}
                        });
                    });
                </script>
                <div class="form-group">
                    <button type="submit" class="btn btn-warning btn-sm mt-3">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirm_delete_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4>Are you sure want to delete this?</h4>
            </div>
            <div class="modal-footer">
                <a href="javascript:" data-id="" class="modalDelete btn btn-danger">Delete</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<script>
    init.push(function () {
        $(document).on("click", ".confirmDelete", function () {
            var Id = $(this).data('id');
            $(".modalDelete").attr("data-id", Id);
        });
        $(document).on("click", ".modalDelete", function () {
            var Id = $(this).data('id');
            $.ajax({
                type: "GET",
                url: "delete_skill?id=" + Id + "&type=user_skills",
                success: function ()
                {
                    $('#confirm_delete_modal').modal('toggle');
                    $('#' + Id).hide();
                }
            });
        });
    });
</script>
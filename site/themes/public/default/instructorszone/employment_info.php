<?php
$userManager = jack_obj('dev_user_management');
$tagger = jack_obj('dev_tag_management');

$user_id = $_config['user']['pk_user_id'];

$args = array(
    'user_id' => $user_id,
    'data_only' => true,
);
$employment_info = $userManager->get_user_employments($args);

if ($_POST) {
    if ($_POST['employment']) {
        $foundError = 0;
        foreach ($_POST['employment'] as $i => $v) {
            $insertData = array(
                'fk_user_id' => $user_id,
                'fk_company_id' => $v['institution'],
                'fk_position_id' => $v['position'],
                'fk_current_position_id' => $v['current_position'],
                'joining_date' => $v['joining_date'],
                'is_working_here' => $v['is_working_here'],
                'resigning_date' => $v['resigning_date'],
                'primary_duties' => $v['primary_duties'],
            );
            $condition = $i < 0 ? '' : " pk_employment_id = '$i'";
            $ret = $devdb->insert_update('e_user_employments', $insertData, $condition);
            if ($ret['error'])
                $foundError++;
        }
        if ($foundError) {
            add_notification((count($_POST['employment']) - $foundError) . ' out of ' . count($_POST['employment']) . ' entries were saved successfully');
        }
    }
    header('Location: ' . current_url());
    exit();
}
$institutions = $tagger->get_tags_by_group('institutions');
$positions = $tagger->get_tags_by_group('job_positions');
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
                <a class="nav-link active" href="employment_info">Job Experience</a>
                <a class="nav-link" href="skill_info">Skill Information</a>
                <a class="nav-link" href="contact_info">Contact Information</a>
                <a class="nav-link" href="social_info">Social Profile Links</a>
                <a class="nav-link" href="user_settings">Settings</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <form method="post" action="">                
                <div id="employmentContainer">

                </div>
                <a id="add_another_employment" href="javascript:" class="btn btn-success btn-sm mt-3">Add Another</a>
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
        $(document).on('change', '.isWorkingHere', function () {
            var status = this.value;
            if (status === 'yes') {
                $('.resignDate').hide();
            } else {
                $('.resignDate').show();
            }
        });
        $(document).on("click", ".confirmDelete", function () {
            var Id = $(this).data('id');
            $(".modalDelete").attr("data-id", Id);
        });
        $(document).on("click", ".modalDelete", function () {
            var Id = $(this).data('id');
            $.ajax({
                type: "GET",
                url: "delete_employment?id=" + Id,
                success: function ()
                {
                    console.log(Id);
                    
                    $('#confirm_delete_modal').modal('toggle');
                    $('#' + Id).hide();
                }
            });
        });
    });
</script>
<script type="text/javascript">
    var institutions = <?php echo $institutions['data'] ? json_encode($institutions['data']) : '{}'; ?>;
    var positions = <?php echo $positions['data'] ? json_encode($positions['data']) : '{}'; ?>;
    var lastEmploymentID = 0;
    var oldData = <?php echo $employment_info['data'] ? json_encode($employment_info['data']) : '{}'; ?>;
    var oldDataCount = <?php echo $employment_info['data'] ? count($employment_info['data']) : '0'; ?>;

    function getInstitutionsOptions(thisInstitution) {
        if (typeof thisInstitution === 'undefined')
            thisInstitution = null;

        var selected = '';
        var options = '';

        for (var i in institutions) {
            var thsInstitution = institutions[i];
            selected = thisInstitution && thisInstitution == thsInstitution['pk_tag_id'] ? 'selected' : '';

            options += '<option value="' + thsInstitution['pk_tag_id'] + '" ' + selected + '>' + processToRender(thsInstitution['tag_title']) + '</option>';
        }

        return options;
    }
    
    function getJobPositionsOptions(thisPosition) {
        if (typeof thisPosition === 'undefined')
            thisPosition = null;

        var selected = '';
        var options = '';

        for (var i in positions) {
            var thsPosition = positions[i];
            selected = thisPosition && thisPosition == thsPosition['pk_tag_id'] ? 'selected' : '';

            options += '<option value="' + thsPosition['pk_tag_id'] + '" ' + selected + '>' + processToRender(thsPosition['tag_title']) + '</option>';
        }
        return options;
    }
    
    function getCurrentJobPositionsOptions(thisPosition) {
        if (typeof thisPosition === 'undefined')
            thisPosition = null;

        var selected = '';
        var options = '';

        for (var i in positions) {
            var thsPosition = positions[i];
            selected = thisPosition && thisPosition == thsPosition['pk_tag_id'] ? 'selected' : '';

            options += '<option value="' + thsPosition['pk_tag_id'] + '" ' + selected + '>' + processToRender(thsPosition['tag_title']) + '</option>';
        }
        return options;
    }
    
    function addNewEmployment(id, data) {
        if (typeof data === 'undefined')
            data = null;
        if (!id) {
            lastEmploymentID -= 1;
            id = lastEmploymentID;
        }
        var thisInstitution = data && data['fk_company_id'] ? data['fk_company_id'] : null;
        var thisPosition = data && data['fk_position_id'] ? data['fk_position_id'] : null;
        var thisCurrentPosition = data && data['fk_current_position_id'] ? data['fk_current_position_id'] : null;
        var joiningDate = data && data['joining_date'] ? data['joining_date'] : '';
        var resigningDate = data && data['resigning_date'] ? data['resigning_date'] : '';
        var primaryDuties = data && data['primary_duties'] ? data['primary_duties'] : '';
        var output = '<div class="card eachEmploymentSet" id="' + id + '">\
                <div class="card-header"><span class="float-right"><a href="javascript:" data-id="' + id + '" data-toggle="modal" data-target="#confirm_delete_modal" class="confirmDelete btn btn-danger">Delete</a></span></div>\
                <div class="card-body">\
                    <table class="table table-hover table-bordered table-stripped table-condensed">\
                        <tr>\
                            <td>Company/Institute</td>\
                            <td><select class="form-control" name="employment[' + id + '][institution]">' + getInstitutionsOptions(thisInstitution) + '</select></td>\
                        </tr>\
                        <tr>\
                            <td>Joined As A</td>\
                            <td><select class="form-control" name="employment[' + id + '][position]">' + getJobPositionsOptions(thisPosition) + '</select></td>\
                        </tr>\
                        <tr>\
                            <td>Current Position</td>\
                            <td><select class="form-control" name="employment[' + id + '][current_position]">' + getCurrentJobPositionsOptions(thisCurrentPosition) + '</select></td>\
                        </tr>\
                        <tr>\
                            <td>Joining Date</td>\
                            <td><input type="date" class="form-control" name="employment[' + id + '][joining_date]" value="' + joiningDate + '" /></td>\
                        </tr>\
                        <tr>\
                            <td>Currently Working Here?</td>\
                            <td><input type="radio" class="isWorkingHere" name="employment[' + id + '][is_working_here]" value="yes" <?php if ($employment_info['data'][0]['is_working_here'] == 'yes') {echo 'checked'; } ?>> &nbsp;Yes&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" class="isWorkingHere" name="employment[' + id + '][is_working_here]" value="no" <?php if ($employment_info['data'][0]['is_working_here'] == 'no') { echo 'checked';} ?>> &nbsp;No</td>\
                        </tr>\
                        <tr class="resignDate">\
                            <td>Resigning Date</td>\
                            <td><input type="date" class="form-control" name="employment[' + id + '][resigning_date]" value="' + resigningDate + '" /></td>\
                        </tr>\
                        <tr>\
                            <td>Primary Duities</td>\
                            <td><textarea class="form-control" name="employment[' + id + '][primary_duties]">'+primaryDuties+'</textarea></td>\
                        </tr>\
                    </table>\
                </div>\
            </div>';
        $('#employmentContainer').append(output);
    }

    init.push(function () {
        $('#add_another_employment').on('click', function () {
            addNewEmployment();
        });
        
        if (oldDataCount) {
            for (var i in oldData) {
                addNewEmployment(oldData[i]['pk_employment_id'], oldData[i]);
            }
        }
    });
</script>
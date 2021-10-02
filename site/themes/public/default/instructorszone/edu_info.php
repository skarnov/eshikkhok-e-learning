<?php
$userManager = jack_obj('dev_user_management');
$tagger = jack_obj('dev_tag_management');

$user_id = $_config['user']['pk_user_id'];

$args = array(
    'user_id' => $user_id,
    'data_only' => true,
);
$edu_info = $userManager->get_user_educations($args);

if ($_POST) {
    if ($_POST['edu']) {

        $foundError = 0;
        foreach ($_POST['edu'] as $i => $v) {

            //sanitize data
            //validate data

            $insertData = array(
                'fk_user_id' => $user_id,
                'fk_degree_id' => $v['degree'],
                'fk_institution_id' => $v['institution'],
                'fk_board_id' => $v['board'],
                'fk_group_major_id' => $v['group_major'],
                'passing_year' => $v['passing_year'],
                'result_method' => $v['result_method'],
                'grade_out_of' => isset($v['grade_out_of']) ? $v['grade_out_of'] : null,
                'achieved_grade' => isset($v['result_grade']) ? $v['result_grade'] : null,
                'achieved_division' => isset($v['result_division']) ? $v['result_division'] : null,
            );

            $condition = $i < 0 ? '' : " pk_education_id = '$i'";
            $ret = $devdb->insert_update('e_user_educations', $insertData, $condition);

            if ($ret['error'])
                $foundError++;
        }

        if ($foundError) {
            add_notification((count($_POST['edu']) - $foundError) . ' out of ' . count($_POST['edu']) . ' entries were saved successfully');
        }
    }
    header('Location: ' . current_url());
    exit();
}

$degrees = $tagger->get_tags_by_group('degrees');
$institutions = $tagger->get_tags_by_group('institutions');
$boards = $tagger->get_tags_by_group('boards');
$group_majors = $tagger->get_tags_by_group('groups_majors');
?>
<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <div class="row">
        <div class="col-md-3 mt-5">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link" href="basic_info">Basic Information</a>
                <a class="nav-link" href="login_info">Login Information</a>
                <a class="nav-link active" href="edu_info">Educational Information</a>
                <a class="nav-link" href="training_info">Training Information</a>
                <a class="nav-link" href="employment_info">Job Experience</a>
                <a class="nav-link" href="skill_info">Skill Information</a>
                <a class="nav-link" href="contact_info">Contact Information</a>
                <a class="nav-link" href="social_info">Social Profile Links</a>
                <a class="nav-link" href="user_settings">Settings</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <form method="post" action="">
                <div id="educationContainer">

                </div>
                <a id="add_another_education" href="javascript:" class="btn btn-success btn-sm mt-3">Add Another</a>
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
                url: "delete_edu?id=" + Id,
                success: function ()
                {
                    $('#confirm_delete_modal').modal('toggle');
                    $('#' + Id).hide();
                }
            });
        });
    });
</script>

<script type="text/javascript">
    var degrees = <?php echo $degrees['data'] ? json_encode($degrees['data']) : '{}'; ?>;
    var institutions = <?php echo $institutions['data'] ? json_encode($institutions['data']) : '{}'; ?>;
    var boards = <?php echo $boards['data'] ? json_encode($boards['data']) : '{}'; ?>;
    var group_majors = <?php echo $group_majors['data'] ? json_encode($group_majors['data']) : '{}'; ?>;
    var lastEduID = 0;
    var resultMethods = ['Grade', 'Division'];
    var resultDivisions = ['First Division', 'Second Division', 'Third Division'];
    var resultGradeOutOf = [4, 5];

    var oldData = <?php echo $edu_info['data'] ? json_encode($edu_info['data']) : '{}'; ?>;
    var oldDataCount = <?php echo $edu_info['data'] ? count($edu_info['data']) : '0'; ?>;

    function getDegreesOptions(thisDegree) {
        if (typeof thisDegree === 'undefined')
            thisDegree = null;

        var selected = '';
        var options = '';

        for (var i in degrees) {
            var thsDegree = degrees[i];
            selected = thisDegree && thisDegree == thsDegree['pk_tag_id'] ? 'selected' : '';

            options += '<option value="' + thsDegree['pk_tag_id'] + '" ' + selected + '>' + processToRender(thsDegree['tag_title']) + '</option>';
        }

        return options;
    }

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

    function getBoardsOptions(thisBoard) {
        if (typeof thisBoard === 'undefined')
            thisBoard = null;

        var selected = '';
        var options = '';

        for (var i in boards) {
            var thsBoard = boards[i];
            selected = thisBoard && thisBoard == thsBoard['pk_tag_id'] ? 'selected' : '';

            options += '<option value="' + thsBoard['pk_tag_id'] + '" ' + selected + '>' + processToRender(thsBoard['tag_title']) + '</option>';
        }

        return options;
    }

    function getGroupMajorOptions(thisGroupMajor) {
        if (typeof thisGroupMajor === 'undefined')
            thisGroupMajor = null;

        var selected = '';
        var options = '';

        for (var i in group_majors) {
            var thsGroupMajor = group_majors[i];
            selected = thisGroupMajor && thisGroupMajor == thsGroupMajor['pk_tag_id'] ? 'selected' : '';

            options += '<option value="' + thsGroupMajor['pk_tag_id'] + '" ' + selected + '>' + processToRender(thsGroupMajor['tag_title']) + '</option>';
        }

        return options;
    }

    function getResultMethodOptions(thisMethod) {
        var selected = '';
        var options = '';

        for (var i in resultMethods) {
            var thsMethod = resultMethods[i];
            selected = thisMethod && thisMethod == thsMethod ? 'selected' : '';

            options += '<option value="' + thsMethod + '" ' + selected + '>' + thsMethod + '</option>';
        }
        return options;
    }

    function getResultDivisionsOptions(thisDivision) {
        var selected = '';
        var options = '';

        for (var i in resultDivisions) {
            var thsDivision = resultDivisions[i];
            selected = thisDivision && thisDivision == thsDivision ? 'selected' : '';

            options += '<option value="' + thsDivision + '" ' + selected + '>' + thsDivision + '</option>';
        }
        return options;
    }

    function getResultGradeOutOfOptions(thisOutOf) {
        var selected = '';
        var options = '';

        for (var i in resultGradeOutOf) {
            var thsGradeOutOf = resultGradeOutOf[i];
            selected = thisOutOf && thisOutOf == thsGradeOutOf ? 'selected' : '';

            options += '<option value="' + thsGradeOutOf + '" ' + selected + '>' + thsGradeOutOf + '</option>';
        }
        return options;
    }

    function addNewEducation(id, data) {
        if (typeof data === 'undefined')
            data = null;
        if (!id) {
            lastEduID -= 1;
            id = lastEduID;
        }

        var thisDegree = data && data['fk_degree_id'] ? data['fk_degree_id'] : null;
        var thisInstitution = data && data['fk_institution_id'] ? data['fk_institution_id'] : null;
        var thisBoard = data && data['fk_board_id'] ? data['fk_board_id'] : null;
        var thisGroupMajor = data && data['fk_group_major_id'] ? data['fk_group_major_id'] : null;
        var passingYear = data && data['passing_year'] ? data['passing_year'] : '';
        var result_method = data && data['result_method'] ? data['result_method'] : null;
        var grade_out_of = data && data['grade_out_of'] ? data['grade_out_of'] : null;
        var achieved_grade = data && data['achieved_grade'] ? data['achieved_grade'] : '';
        var achieved_division = data && data['achieved_division'] ? data['achieved_division'] : null;

        var output = '<div class="card eachEduSet" id="' + id + '">\
                <div class="card-header"><span class="float-right"><a href="javascript:" data-id="' + id + '" data-toggle="modal" data-target="#confirm_delete_modal" class="confirmDelete btn btn-danger">Delete</a></span></div>\
                <div class="card-body">\
                    <table class="table table-hover table-bordered table-stripped table-condensed">\
                        <tr>\
                            <td>Degree</td>\
                            <td><select class="form-control" name="edu[' + id + '][degree]">' + getDegreesOptions(thisDegree) + '</select></td>\
                            <td>Result Method</td>\
                            <td><select class="form-control result-method" name="edu[' + id + '][result_method]">' + getResultMethodOptions(result_method) + '</select></td>\
                        </tr>\
                        <tr>\
                            <td>Institution</td>\
                            <td><select class="form-control" name="edu[' + id + '][institution]">' + getInstitutionsOptions(thisInstitution) + '</select></td>\
                            <td>Select Division</td>\
                            <td><select class="form-control result-division" name="edu[' + id + '][result_division]">' + getResultDivisionsOptions(achieved_division) + '</select></td>\
                        </tr>\
                        <tr>\
                            <td>Board</td>\
                            <td><select class="form-control" name="edu[' + id + '][board]">' + getBoardsOptions(thisBoard) + '</select></td>\
                            <td>Grade Type</td>\
                            <td><select class="form-control grade-type" name="edu[' + id + '][grade_out_of]">' + getResultGradeOutOfOptions(grade_out_of) + '</select></td>\
                        </tr>\
                        <tr>\
                            <td>Group/Major</td>\
                            <td><select class="form-control" name="edu[' + id + '][group_major]">' + getGroupMajorOptions(thisGroupMajor) + '</select></td>\
                            <td>Grade</td>\
                            <td><input type="number" step="0.01" class="form-control result-grade" name="edu[' + id + '][result_grade]" value="' + achieved_grade + '" /></td>\
                        </tr>\
                        <tr>\
                            <td>Passing Year</td>\
                            <td><input type="text" class="form-control" name="edu[' + id + '][passing_year]" value="' + passingYear + '" /></td>\
                            <td></td>\
                            <td></td>\
                        </tr>\
                    </table>\
                </div>\
            </div>';

        $('#educationContainer').append(output);
        $('.result-method').change();
        $('.grade-type').change();
    }

    init.push(function () {
        $('#add_another_education').on('click', function () {
            addNewEducation()
        });

        $(document).on('change', '.result-method', function () {
            var ths = $(this);
            var container = ths.closest('.card');

            var resultDivisionSelect = $('.result-division', container);
            var gradeTypeSelect = $('.grade-type', container);
            var resultGradeInput = $('.result-grade', container);

            if (ths.val() == 'Grade') {
                resultDivisionSelect.attr('disabled', true);
                gradeTypeSelect.attr('disabled', false);
                resultGradeInput.attr('disabled', false);
            } else {
                resultDivisionSelect.attr('disabled', false);
                gradeTypeSelect.attr('disabled', true);
                resultGradeInput.attr('disabled', true);
            }
        }).change();

        $(document).on('change', '.grade-type', function () {
            var ths = $(this);
            var container = ths.closest('.card');
            var resultGradeInput = $('.result-grade', container);
            resultGradeInput.attr('max', ths.val());
        }).change();

        if (oldDataCount) {
            for (var i in oldData) {
                addNewEducation(oldData[i]['pk_education_id'], oldData[i]);
            }
        }
    });
</script>
<?php
global $multilingualFields;

$pageManager = jack_obj('dev_page_management');
$tagger = jack_obj('dev_tag_management');
$user_id = $_config['user']['pk_user_id'];

$pre_data = array();

if ($_POST) {
    $ret = array();

    $data = $devdb->deep_escape($_POST);
    $data = $_POST;
    
    $data['fk_parent_id'] = $user_id;
    $data['item_title'] = processToStore('', $data['item_title']);
    $data['item_summery'] = processToStore('', $data['item_summery']);
    $data['item_learning_goal'] = processToStore('', json_encode($data['item_learning_goal']));
    $data['item_prerequisites'] = processToStore('', json_encode($data['item_prerequisites']));
    $data['item_requirements'] = processToStore('', json_encode($data['item_requirements']));

    $temp = form_validator::required($data['item_title']);
    if ($temp !== true)
        $ret['error'][] = 'Please provide the name of the course';

    $temp = form_validator::required($data['fk_category_id']);
    if ($temp !== true)
        $ret['error'][] = 'Please select a category';

    if(!$ret['error']){
        $insert_data = array(
            'fk_parent_id' => $data['fk_parent_id'],
            'item_title' => $data['item_title'],
            'item_summery' => $data['item_summery'],
            'fk_category_id' => $data['fk_category_id'],
            'item_learning_goal' => $data['item_learning_goal'],
            'item_prerequisites' => $data['item_prerequisites'],
            'item_requirements' => $data['item_requirements'],
            'created_at_date' => date('Y-m-d'),
            'created_at_time' => date('H:i:s'),
            'created_at_int' => time(),
            'created_by' => $_config['user']['pk_user_id'],
            'modified_at_date' => date('Y-m-d'),
            'modified_at_time' => date('H:i:s'),
            'modified_at_int' => time(),
            'modified_by' => $_config['user']['pk_user_id'],
            );

        $ret = $devdb->insert_update('e_courses', $insert_data);

        if ($ret['error']){
            print_errors($ret['error']);
            $pre_data = $data;
            }
        else{
            $course_id = $ret['success'];
            doAction('after_course_created', $course_id);
            add_notification('A course, '.processToRender($data['item_title']).' has been created', 'success');
            user_activity::add_activity('A course, '.processToRender($data['item_title']).' (ID: ' . $course_id . ') has been created.', 'success', 'create');
            header('location: '.url('instructorszone/update_course?id='.$course_id));
            exit();
            }
        }
    else{
        print_errors($ret['error']);
        $pre_data = $data;
        }
    }

$categories = $tagger->get_tags(array('tag_group_slug' => 'course_category', 'data_only' => true));
$categories = $categories['data'];

if($pre_data){
    $pre_data['item_learning_goal'] = processToRender($pre_data['item_learning_goal']);
    $pre_data['item_prerequisites'] = processToRender($pre_data['item_prerequisites']);
    $pre_data['item_requirements'] = processToRender($pre_data['item_requirements']);
    }
?>
<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center">Create New Course</h1><hr/>
            <form method="POST" action="">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" name="item_title" value="<?php echo $pre_data['item_title'] ? processToRender($pre_data['item_title']) : '' ?>" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" name="fk_category_id" id="fk_category_id">
                            <option value="">Select One</option>
                            <?php echo get_tags_in_hierarchy_select($pre_data['fk_category_id'], $categories); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Summary</label>
                        <input type="text" name="item_summery" value="<?php echo $pre_data['item_summery'] ? processToRender($pre_data['item_summery']) : '' ?>" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label>What Student Will Learn</label>
                        <div id="what_std_learn">

                        </div>
                        <a href="javascript:" id="add_what_std_learn" class="badge"><i class="fa fa-plus-circle"></i>&nbsp;Add Another</a>
                    </div>
                    <div class="form-group">
                        <label>Prerequisites</label>
                        <div id="prerequisites">

                        </div>
                        <a href="javascript:" id="add_prerequisite" class="badge"><i class="fa fa-plus-circle"></i>&nbsp;Add Prerequisite</a>
                    </div>
                    <div class="form-group">
                        <label>Requirements</label>
                        <div id="requirements">

                        </div>
                        <a href="javascript:" id="add_requirement" class="badge"><i class="fa fa-plus-circle"></i>&nbsp;Add Requirement</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-info btn-sm">Create Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    var whatStdLearn = <?php echo strlen($pre_data['item_learning_goal']) ? $pre_data['item_learning_goal'] : '{}';?>;
    var itemPrerequisites = <?php echo strlen($pre_data['item_prerequisites']) ? $pre_data['item_prerequisites'] : '{}';?>;
    var itemRequirements = <?php echo strlen($pre_data['item_requirements']) ? $pre_data['item_requirements'] : '{}';?>;
    init.push(function(){
        $(document).on('click', '.removeThisInputGroup', function(){
            $(this).closest('.input-group').remove();
            });

        var whatStdLearnContainer = $('#what_std_learn');
        var itemPrerequisitesContainer = $('#prerequisites');
        var itemRequirementsContainer = $('#requirements');

        function addWhatStdLearn($item){
            whatStdLearnContainer.append('<div class="form-group"><div class="input-group">\
                    <input type="text" class="form-control" name="item_learning_goal[]" value="'+$item+'" />\
                    <div class="input-group-append"><button type="button" class="btn-danger removeThisInputGroup btn"><i class="fa fa-times-circle"></i></button></div>\
                </div></div>');
        }

        if(Object.keys(whatStdLearn).length){
            for(i in whatStdLearn){
                addWhatStdLearn(whatStdLearn[i]);
            }
        }
        else addWhatStdLearn('');

        function additemPrerequisites($item){
            itemPrerequisitesContainer.append('<div class="form-group"><div class="input-group">\
                    <input type="text" class="form-control" name="item_prerequisites[]" value="'+$item+'" />\
                    <div class="input-group-append"><button type="button" class="btn-danger removeThisInputGroup btn"><i class="fa fa-times-circle"></i></button></div>\
                </div></div>');
        }

        if(Object.keys(itemPrerequisites).length){
            for(i in itemPrerequisites){
                additemPrerequisites(itemPrerequisites[i]);
            }
        }
        else additemPrerequisites('');

        function additemRequirements($item){
            itemRequirementsContainer.append('<div class="form-group"><div class="input-group">\
                    <input type="text" class="form-control" name="item_requirements[]" value="'+$item+'" />\
                    <div class="input-group-append"><button type="button" class="btn-danger removeThisInputGroup btn"><i class="fa fa-times-circle"></i></button></div>\
                </div></div>');
        }

        if(Object.keys(itemRequirements).length){
            for(i in itemRequirements){
                additemRequirements(itemRequirements[i]);
            }
        }
        else additemRequirements('');


        $('#add_what_std_learn').on('click', function(){addWhatStdLearn('')});
        $('#add_prerequisite').on('click', function(){additemPrerequisites('')});
        $('#add_requirement').on('click', function(){additemRequirements('')});
        });
</script>
<?php
global $multilingualFields;

$courseManager = jack_obj('dev_course_management');
$tagger = jack_obj('dev_tag_management');

$audience_type = getProjectSettings('audienceType');
$difficulty_level = getProjectSettings('difficultyLevel');

$course_id = $_GET['id'];

if ($course_id) {
    $args = array(
        'id' => $course_id,
        'single' => true,
    );
    $pre_data = $courseManager->get_courses($args);

    if (!$pre_data) {
        add_notification('Course not found for editing.', 'error');
        header('location:' . url('instructorszone'));
        exit();
    }
} else {
    add_notification('Course not found for editing.', 'error');
    header('location:' . url('instructorszone'));
    exit();
}

if ($_POST) {
    $ret = array();
    $data = $devdb->deep_escape($_POST);
    $data = $_POST;

    $data['item_title'] = processToStore($pre_data['item_title'], $data['item_title']);
    $data['item_summery'] = processToStore($pre_data['item_summery'], $data['item_summery']);
    $data['item_description'] = processToStore($pre_data['item_description'], $data['item_description']);
    $data['item_learning_goal'] = processToStore($pre_data['item_learning_goal'], json_encode($data['item_learning_goal']));
    $data['item_prerequisites'] = processToStore($pre_data['item_prerequisites'], json_encode($data['item_prerequisites']));
    $data['item_requirements'] = processToStore($pre_data['item_requirements'], json_encode($data['item_requirements']));
    $data['item_faqs'] = processToStore($pre_data['item_faqs'], json_encode($data['item_faqs']));

    $temp = form_validator::required($data['item_title']);
    if ($temp !== true)
        $ret['error'][] = 'Content Title ' . $temp;

    if (!$ret['error']) {

        if ($_FILES['featured_image']["name"]) {
            $supported_ext = array('jpg', 'png', 'gif');
            $max_filesize = 512000;
            $target_dir = _path('uploads', 'absolute');
            if (!file_exists($target_dir))
                mkdir($target_dir);
            $orgFileName = $_FILES['featured_image']["name"];
            $fileName = time() . '_' . str_replace(' ', '-', $orgFileName);
            $target_file = $target_dir . '/' . $fileName;
            $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
            if (in_array(strtolower($imageFileType), $supported_ext)) {
                if ($max_filesize && $_FILES['featured_image']["size"] <= $max_filesize) {
                    if (!move_uploaded_file($_FILES['featured_image']["tmp_name"], $target_file)) {
                        $ret['error'][] = 'Featured Image : File was not uploaded, please try again.';
                        $args['featured_image'] = '';
                    } else {
                        $args['featured_image'] = $fileName;
                        $pre_file = $target_dir . '/' . $pre_data['featured_image'];
                        if (file_exists($pre_file)) {
                            unlink($pre_file);
                        }
                    }
                } else
                    $ret['error'][] = 'Featured Image : <strong>' . $_FILES['featured_image']["size"] . ' B</strong> is more than supported file size <strong>' . $max_filesize . ' B';
            } else
                $ret['error'][] = 'Featured Image : <strong>.' . $imageFileType . '</strong> is not supported extension. Only supports .' . implode(', .', $supported_ext);
        }

        if ($_FILES['promotional_video']["name"]) {
            $supported_ext = array('mp4', '');
            $max_filesize = 512000;
            $target_dir = "upload/";
            if (!file_exists($target_dir))
                mkdir($target_dir);
            $target_file = $target_dir . basename($_FILES['promotional_video']["name"]);
            $fileinfo = pathinfo($target_file);
            $target_file = $target_dir . str_replace(' ', '_', $fileinfo['filename']) . '_' . time() . '.' . $fileinfo['extension'];
            $videoFileType = pathinfo($target_file, PATHINFO_EXTENSION);
            if (in_array(strtolower($videoFileType), $supported_ext)) {
                if ($max_filesize && $_FILES['promotional_video']["size"] <= $max_filesize) {
                    if (!move_uploaded_file($_FILES['promotional_video']["tmp_name"], $target_file)) {
                        $ret['error'][] = 'Promotional Video : File was not uploaded, please try again.';
                        $args['promotional_video'] = '';
                    } else {
                        $fileinfo = pathinfo($target_file);
                        $args['promotional_video'] = $fileinfo['basename'];
                    }
                } else
                    $ret['error'][] = 'Promotional Video : <strong>' . $_FILES['promotional_video']["size"] . ' B</strong> is more than supported file size <strong>' . $max_filesize . ' B';
            } else
                $ret['error'][] = 'Promotional Video : <strong>.' . $videoFileType . '</strong> is not supported extension. Only supports .' . implode(', .', $supported_ext);
        }

        $insert_data = array(
            'item_title' => $data['item_title'],
            'item_summery' => $data['item_summery'],
            'item_description' => $data['item_description'],
            'promotional_video' => $args['promotional_video'] ? $args['promotional_video'] : $pre_data['promotional_video'],
            'item_learning_goal' => $data['item_learning_goal'],
            'item_prerequisites' => $data['item_prerequisites'],
            'item_requirements' => $data['item_requirements'],
            'item_faqs' => $data['item_faqs'],
            'item_target_audience' => $data['item_target_audience'],
            'price' => $data['price'],
            'item_difficulty_level' => $data['item_difficulty_level'],
            'featured_image' => $args['featured_image'] ? $args['featured_image'] : $pre_data['featured_image'],
            'modified_at_date' => date('Y-m-d'),
            'modified_at_time' => date('H:i:s'),
            'modified_at_int' => time(),
            'modified_by' => $_config['user']['pk_user_id'],
        );

        $ret = $devdb->insert_update('e_courses', $insert_data, " pk_item_id = '" . $course_id . "'");
    }

    if ($ret['error']) {
        foreach ($ret['error'] as $e) {
            add_notification($e, 'error');
        }
        $pre_data = $data;
    } else {

        if ($tagger)
            $tagger->attach_tags($course_id, 'course', $_POST['course_tags'], 'course_tags');
        if ($tagger)
            $tagger->attach_tags($course_id, 'course', $_POST['course_skills'], 'skills');

        if (!$ret['error']) {
            doAction('after_course_updated');
            add_notification('The course has been updated.', 'success');
            user_activity::add_activity('The course (ID: ' . $course_id . ') has been updated', 'success', 'update');
            header('location:' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
}

$pre_data['item_learning_goal'] = processToRender($pre_data['item_learning_goal']);
$pre_data['item_prerequisites'] = processToRender($pre_data['item_prerequisites']);
$pre_data['item_requirements'] = processToRender($pre_data['item_requirements']);
$pre_data['item_faqs'] = processToRender($pre_data['item_faqs']);

$tags = $tagger->get_attached_tags_refill($course_id, 'course', 'course_tags');
$skills = $tagger->get_attached_tags_refill($course_id, 'course', 'skills');
?>
<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <div class="row">
        <div class="col-md-3 mt-5">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Course Information</a>
                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Info For Students</a>
                <a class="nav-link" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Pricing And Featured Medias</a>
                <a class="nav-link" href="new_curriculum?id=<?php echo $course_id ?>">Curriculum</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                        <div class="form-group">
                            <label>Course Name</label>
                            <input type="text" name="item_title" value="<?php echo $pre_data['item_title'] ? processToRender($pre_data['item_title']) : '' ?>" maxlength="300" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label>Summary</label>
                            <textarea name="item_summery"><?php echo $pre_data['item_summery'] ? processToRender($pre_data['item_summery']) : '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="item_description"><?php echo $pre_data['item_description'] ? processToRender($pre_data['item_description']) : '' ?></textarea>
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
                                    toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                                    content_css: [
                                        '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                                        '//www.tinymce.com/css/codepen.min.css']
                                });
                            });
                        </script>
                        <div class="form-group">
                            <label>Tags</label>
                            <div id="course_tags_autocomplete"></div>
                            <script type="text/javascript">
                                init.push(function () {
                                    new set_autosuggest({
                                        container: '#course_tags_autocomplete',
                                        submit_labels: false,
                                        ajax_page: _root_path_ + '/api/dev_tag_management/get_tags_autocomplete',
                                        single: false,
                                        parameters: {'tag_group': 'course_tags'},
                                        multilingual: true,
                                        input_field: '#input_course_tag',
                                        input_field_class: 'form-control',
                                        field_name: 'course_tags',
                                        add_what: 'Tag',
                                        add_new: true,
                                        url_for_add: _root_path_ + '/api/dev_tag_management/add_edit_tags',
                                        field_for_add: 'tag_title',
                                        data_for_add: {tag_group: 'course_tags'},
                                        existing_items: <?php echo to_json_object($tags); ?>,
                                    });
                                });
                            </script>
                        </div>
                        <div class="form-group">
                            <label>Skills</label>
                            <div id="course_skills_autocomplete"></div>
                            <script type="text/javascript">
                                init.push(function () {
                                    new set_autosuggest({
                                        container: '#course_skills_autocomplete',
                                        submit_labels: false,
                                        ajax_page: _root_path_ + '/api/dev_tag_management/get_tags_autocomplete',
                                        single: false,
                                        parameters: {'tag_group': 'skills'},
                                        multilingual: true,
                                        input_field: '#input_course_skills',
                                        input_field_class: 'form-control',
                                        field_name: 'course_skills',
                                        add_what: 'Skill',
                                        add_new: true,
                                        url_for_add: _root_path_ + '/api/dev_tag_management/add_edit_tags',
                                        field_for_add: 'tag_title',
                                        data_for_add: {tag_group: 'skills'},
                                        existing_items: <?php echo to_json_object($skills); ?>,
                                    });
                                });
                            </script>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                        <div class="form-group">
                            <label>What Student Will Learn</label>
                            <div id="what_std_learn">

                            </div>
                            <a href="javascript:" id="add_what_std_learn" class="label"><i class="fa fa-plus-circle"></i>&nbsp;Add Another</a>
                        </div>
                        <div class="form-group">
                            <label>Prerequisites</label>
                            <div id="prerequisites">

                            </div>
                            <a href="javascript:" id="add_prerequisite" class="label"><i class="fa fa-plus-circle"></i>&nbsp;Add Prerequisite</a>
                        </div>
                        <div class="form-group">
                            <label>Requirements</label>
                            <div id="requirements">

                            </div>
                            <a href="javascript:" id="add_requirement" class="label"><i class="fa fa-plus-circle"></i>&nbsp;Add Requirement</a>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">FAQs</span>
                            </div>
                            <div id="faqs_container" class="card-body">

                            </div>
                            <div class="card-footer">
                                <a href="javascript:" id="add_faq" class="label"><i class="fa fa-plus-circle"></i>&nbsp;Add Another FAQ</a>
                            </div>
                        </div>
                        <div class="input-group mb-3 mt-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text">Target Audience</label>
                            </div>
                            <select name="item_target_audience" class="custom-select">
                                <option>Choose...</option>
                                <?php foreach ($audience_type as $value) : ?>
                                    <option value="<?php echo $value ?>" <?php
                                    if ($pre_data['item_target_audience'] == $value) {
                                        echo 'selected';
                                    }
                                    ?>><?php echo $value ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text">Difficulty Level</label>
                            </div>
                            <select name="item_difficulty_level" class="custom-select">
                                <option>Choose...</option>
                                <?php foreach ($difficulty_level as $value) : ?>
                                    <option value="<?php echo $value ?>" <?php
                                            if ($pre_data['item_difficulty_level'] == $value) {
                                                echo 'selected';
                                            }
                                            ?>><?php echo $value ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                        <div class="form-group">
                            <label>Price</label>
                            <input type="text" name="price" value="<?php echo $pre_data['price'] ? $pre_data['price'] : '' ?>" class="form-control form-control-sm">
                        </div>
                        <?php
                        if ($pre_data['featured_image']){ ?>
                            <div id="<?php echo $pre_data['pk_item_id'] ?>">
                                <div class="form-group">
                                    <img src="<?php echo $pre_data['featured_image'] ? get_image($pre_data['featured_image'], '250x250x2') : get_image($_config['defaultNoImage'], '250x250x2') ?>" class="img-responsive" height="250" width="250"/>
                                </div>
                                <div class="form-group">
                                    <a href="javascript:" data-id="<?php echo $pre_data['pk_item_id'] ?>" data-name="featured_image" data-value="<?php echo $pre_data['featured_image'] ?>" data-toggle="modal" data-target="#confirm_delete_modal" class="confirmDelete btn btn-danger">Delete Featured Image</a>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="featured_image" class="form-control-sm">
                        </div>
                        <?php
                        if ($pre_data['promotional_video']){ ?>
                            <div id="<?php echo $pre_data['pk_item_id'] ?>">
                                <div class="form-group">
                                    <video width="320" height="240" controls>
                                        <source src="<?php echo url() ?>upload/<?php echo $pre_data['promotional_video'] ?>" type="video/mp4">
                                    </video>                            
                                </div>
                                <div class="form-group">
                                    <a href="javascript:" data-id="<?php echo $pre_data['pk_item_id'] ?>" data-name="promotional_video" data-value="<?php echo $pre_data['promotional_video'] ?>" data-toggle="modal" data-target="#confirm_delete_modal" class="confirmDelete btn btn-danger">Delete Promotional Video</a>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label>Video</label>
                            <input type="file" name="promotional_video" class="form-control-sm">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                        
                    </div>
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
                <a href="javascript:" data-id="" data-name="" data-value="" class="modalDelete btn btn-danger">Delete</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<script>
    init.push(function () {
        $(document).on("click", ".confirmDelete", function () {
            var Id = $(this).data('id');            
            var name = $(this).data('name');
            var value = $(this).data('value');
            $(".modalDelete").attr("data-id", Id);
            $(".modalDelete").attr("data-name", name);
            $(".modalDelete").attr("data-value", value);
        });
        $(document).on("click", ".modalDelete", function () {
            var Id = $(this).data('id');
            var name = $(this).data('name');
            var value = $(this).data('value');            
            $.ajax({
                type: "GET",
                url: "delete_media?id=" + Id + "&name=" + name + "&value=" + value,
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
    var whatStdLearn = <?php echo strlen($pre_data['item_learning_goal']) ? $pre_data['item_learning_goal'] : '{}'; ?>;
    var itemPrerequisites = <?php echo strlen($pre_data['item_prerequisites']) ? $pre_data['item_prerequisites'] : '{}'; ?>;
    var itemRequirements = <?php echo strlen($pre_data['item_requirements']) ? $pre_data['item_requirements'] : '{}'; ?>;
    var faqs = <?php echo strlen($pre_data['item_faqs']) ? $pre_data['item_faqs'] : '{}'; ?>;
    init.push(function () {
        $(document).on('click', '.removeThisInputGroup', function () {
            $(this).closest('.input-group').remove();
        });

        var whatStdLearnContainer = $('#what_std_learn');
        var itemPrerequisitesContainer = $('#prerequisites');
        var itemRequirementsContainer = $('#requirements');

        function addWhatStdLearn($item) {
            whatStdLearnContainer.append('<div class="form-group"><div class="input-group">\
                    <input type="text" class="form-control" name="item_learning_goal[]" value="' + $item + '" />\
                    <div class="input-group-append"><button type="button" class="btn-danger removeThisInputGroup btn"><i class="fa fa-times-circle"></i></button></div>\
                </div></div>');
        }

        if (Object.keys(whatStdLearn).length) {
            for (i in whatStdLearn) {
                addWhatStdLearn(whatStdLearn[i]);
            }
        } else
            addWhatStdLearn('');

        function additemPrerequisites($item) {
            itemPrerequisitesContainer.append('<div class="form-group"><div class="input-group">\
                    <input type="text" class="form-control" name="item_prerequisites[]" value="' + $item + '" />\
                    <div class="input-group-append"><button type="button" class="btn-danger removeThisInputGroup btn"><i class="fa fa-times-circle"></i></button></div>\
                </div></div>');
        }

        if (Object.keys(itemPrerequisites).length) {
            for (i in itemPrerequisites) {
                additemPrerequisites(itemPrerequisites[i]);
            }
        } else
            additemPrerequisites('');

        function additemRequirements($item) {
            itemRequirementsContainer.append('<div class="form-group"><div class="input-group">\
                    <input type="text" class="form-control" name="item_requirements[]" value="' + $item + '" />\
                    <div class="input-group-append"><button type="button" class="btn-danger removeThisInputGroup btn"><i class="fa fa-times-circle"></i></button></div>\
                </div></div>');
        }

        if (Object.keys(itemRequirements).length) {
            for (i in itemRequirements) {
                additemRequirements(itemRequirements[i]);
            }
        } else
            additemRequirements('');


        $('#add_what_std_learn').on('click', function () {
            addWhatStdLearn('')
        });
        $('#add_prerequisite').on('click', function () {
            additemPrerequisites('')
        });
        $('#add_requirement').on('click', function () {
            additemRequirements('')
        });

        //working on faqs
        $(document).on('click', '.removeThisFaq', function () {
            $(this).closest('.eachFaq').remove();
        });

        var faqsContainer = $('#faqs_container');
        var totalFaq = 0;
        function addFaq($item) {
            var qus = $item ? $item['q'] : '';
            var ans = $item ? $item['a'] : '';
            totalFaq++;
            faqsContainer.append('<div class="eachFaq">\
                    <div class="form-group">\
                        <div class="input-group">\
                            <input type="text" class="form-control" name="item_faqs[' + totalFaq + '][q]" value="' + qus + '" placeholder="FAQ ..."/>\
                            <div class="input-group-append"><button type="button" class="btn-danger removeThisFaq btn"><i class="fa fa-times-circle"></i></button></div>\
                        </div>\
                    </div>\
                    <div class="form-group">\
                        <input type="text" class="form-control" name="item_faqs[' + totalFaq + '][a]" value="' + ans + '" placeholder="Answer to Faq ..."/>\
                    </div>\
                </div><hr />');
        }
        if (Object.keys(faqs).length) {
            for (i in faqs) {
                addFaq(faqs[i]);
            }
        } else
            addFaq(null);
        $('#add_faq').on('click', function () {
            addFaq(null)
        });
    });
</script>
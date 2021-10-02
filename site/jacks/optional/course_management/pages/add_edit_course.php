<?php
use Vimeo\Vimeo;

global $multilingualFields;

$pageManager = jack_obj('dev_page_management');

$edit = $_GET['edit'] ? $_GET['edit'] : NULL;

$pre_data = array();

if ($edit) {
    $args = array(
        'id' => $edit,
        'single' => true,
    );
    $pre_data = $this->get_courses($args);
    
    if (!$pre_data) {
        add_notification('Course not found for editing.', 'error');
        header('location:' . $myUrl);
        exit();
    }
}

if ($_POST) {
    $ret = array();
    //sanetizing
    $data = $devdb->deep_escape($_POST);
    $data = $_POST;

    $data['item_type'] = 'course';
    $data['item_title'] = processToStore($pre_data['item_title'], $data['item_title']);
    $data['item_subtitle'] = processToStore($pre_data['item_subtitle'], $data['item_subtitle']);
    $data['item_summery'] = processToStore($pre_data['item_summery'], $data['item_summery']);
    $data['item_description'] = processToStore($pre_data['item_description'], $data['item_description']);
    $data['item_objectives'] = processToStore($pre_data['item_objectives'], $data['item_objectives']);
    $data['item_requirements'] = processToStore($pre_data['item_requirements'], $data['item_requirements']);
    $data['item_meta_keyword'] = form_modifiers::sanitize_title($data['item_meta_keyword']);
    $data['item_description'] = form_modifiers::html_purify($data['item_description']);
    $data['item_excerpt'] = form_modifiers::sanitize_title($data['item_excerpt'] ? $data['item_excerpt'] : mb_substr(form_modifiers::content_to_excerpt($data['item_description']),0,250));
    
  
    /* Start Vimeo API On 3devs IT
     * Auther: Shaik Obydullah */
    if ($_FILES['promotional_video']['name']) {

        $target_dir = "temp/";
        $target_file = $target_dir . basename($_FILES["promotional_video"]["name"]);

        if (move_uploaded_file($_FILES["promotional_video"]["tmp_name"], $target_file)) {
            echo "The file " . basename($_FILES["promotional_video"]["name"]) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }

        $client = new Vimeo("1c20f4739bbea5b955e87c0a3e78696bc37ed8a5", "q6CPZxlCgQrk95wVyHV4r0w9FMQNQL7gElV+OXEmcsaD5r1DfntzcNHBSMo9kvMHtAYGVNXSXbl+Kt9i59jYvDeA0z/inGYv80vT8fC/Y/Yg2iIDCB1qR8rCV/qwtCZJ", "733c3da593e21183f01a5d96522f694c");
//            $response = $client->request('/tutorial', array(), 'GET');

        $file_name = $_FILES['promotional_video']['name'];

        $uri = $client->upload('temp/' . $file_name, array(
            'name' => $data['item_title'],
            'description' => $data['item_summery']
        ));


//            pre($uri);
//            $response = $client->request($uri . '?fields=transcode.status');
//            if ($response['body']['transcode']['status'] === 'complete') {
//                print 'Your video finished transcoding.';
//            } elseif ($response['body']['transcode']['status'] === 'in_progress') {
//                print 'Your video is still transcoding.';
//            } else {
//                print 'Your video encountered an error during transcoding.';
//            }
//            pre($uri);
        $response = $client->request($uri . '?fields=link');

        $data['promotional_video'] = $response['body']['link'];
    }
    /* End Vimeo API */

    //validating
    $temp = form_validator::required($data['item_title']);
    if ($temp !== true)
        $ret['error'][] = 'Content Title ' . $temp;

    $temp = form_validator::required($data['item_subtitle']);
    if ($temp !== true)
        $ret['error'][] = 'Content Sub-Title ' . $temp;

    $temp = form_validator::required($data['item_summery']);
    if ($temp !== true)
        $ret['error'][] = 'Content Summary ' . $temp;

    $temp = form_validator::required($data['item_description']);
    if ($temp !== true)
        $ret['error'][] = 'Content Description ' . $temp;

    if (!$ret['error']) {
//        foreach($data as $column=>$value){
//            if (in_array($column, $multilingualFields['e_courses']) !== false){
//                $data[$column] = processToStore($pre_content[$column], $value);
//                }
//            }

        $insert_data = array(
            'item_type' => $data['item_type'],
            'item_title' => $data['item_title'],
            'item_subtitle' => $data['item_subtitle'],
            'item_summery' => $data['item_summery'],
            'item_description' => $data['item_description'],
            'promotional_video' => $data['promotional_video'],
            'item_objectives' => $data['item_objectives'],
            'featured_image' => $data['featured_image'],
            'publication_status' => $data['publication_status'],
            'published_date' => $data['published_date'],
            'access_mode' => $data['access_mode'],
            'pricing_mode' => $data['pricing_mode'],
            'item_content_flow_type' => $data['item_content_flow_type'],
            'net_price' => $data['net_price'],
            'discount' => $data['discount'],
            'price' => $data['price'],
            'item_difficulties' => $data['item_difficulties'],
            'item_language' => $data['item_language'],
            'featured_status' => $data['featured_status'],
            'lecture_numbers' => $data['lecture_numbers'],
            'item_duration' => $data['item_duration'],
            'item_requirements' => $data['item_requirements'],
            'item_meta_keyword' => $data['item_meta_keyword'],
            'item_meta_description' => $data['item_meta_description'],
            'item_excerpt' => $data['item_excerpt'],
            'modified_at_date' => date('Y-m-d'),
            'modified_at_time' => date('H:i:s'),
            'modified_at_int' => time(),
            'modified_by' => $_config['user']['pk_user_id'],
        );

        if ($edit) {
            $ret = $devdb->insert_update('e_courses', $insert_data, " pk_item_id = '" . $edit . "'");
            $course_id = $edit;
        } else {
            $insert_data['created_at_date'] = date('Y-m-d');
            $insert_data['created_at_time'] = date('H:i:s');
            $insert_data['created_at_int'] = time();
            $insert_data['created_by'] = $_config['user']['pk_user_id'];

            $ret = $devdb->insert_update('e_courses', $insert_data);
            $course_id = $ret['success'];
        }
    }

    if ($ret['error']) {
        foreach ($ret['error'] as $e) {
            add_notification($e, 'error');
        }
        $content = $_POST;
    } else {

        if (!$ret['error']) {
            if($edit) doAction('after_course_updated');
            else doAction('after_course_created');

            if ($edit) {
                add_notification('The course has been updated.', 'success');
                user_activity::add_activity('The course (ID: ' . $course_id . ') has been updated', 'success', 'update');
            } else {
                add_notification('The course has been created', 'success');
                user_activity::add_activity('The course (ID: ' . $content_id . ') has been created.', 'success', 'create');
            }

            header('location:' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
}

doAction('render_start');
?>
<div class="page-header">
    <h1><?php echo $edit ? 'Update Course: ' : 'New Course' ?></h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => build_url(null, array('action', 'edit')),
                'action' => 'list',
                'icon' => 'icon_list',
                'text' => 'All Courses',
                'title' => 'Manage All Courses',
                'size' => 'sm',
            ));
            ?>
            <?php if (has_permission('add_contents')): ?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_course'), array('edit')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New Course',
                    'title' => 'Create New Course',
                    'size' => 'sm',
                ));
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<form onsubmit="return true;" name="widget_pos_add_edit" method="post" action="" enctype="multipart/form-data">
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-xl-12 col-lg-9">                    
                    <div class="form-group">
                        <label>Course Title</label>
                        <input class="form-control" type="text" name="item_title" value="<?php echo $pre_data['item_title'] ? processToRender($pre_data['item_title']) : '' ?>" required/>
                    </div>
                    <div class="form-group">
                        <label>Course Subtitle</label>
                        <textarea class="form-control" name="item_subtitle" required><?php echo $pre_data['item_subtitle'] ? processToRender($pre_data['item_subtitle']) : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Course Summary</label>
                        <textarea class="form-control" id="item_summery" name="item_summery"><?php echo $pre_data['item_summery'] ? processToRender($pre_data['item_summery']) : '' ?></textarea>
                        <script>
                            init.push(function () {
                                init_tinymce({
                                    selector: '#item_summery',
                                    external_filemanager_path: '<?php echo $paths['relative']['common_files']; ?>/filemanager/',
                                    body_class: 'static_page_container',
                                    autoResizeMaxHeight: 0,
                                    autoResizeMinHeight: 0,
                                    content_css: '../../site/themes/public/<?php echo getProjectSettings('components,current_public_theme'); ?>/assets/tinymce_content_styles.css?a=' + new Date().getTime()
                                });
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label>Course Description</label>
                        <textarea class="form-control" id="item_description" name="item_description"><?php echo $pre_data['item_description'] ? processToRender($pre_data['item_description']) : '' ?></textarea>
                        <script>
                            init.push(function () {
                                init_tinymce({
                                    selector: '#item_description',
                                    external_filemanager_path: '<?php echo $paths['relative']['common_files']; ?>/filemanager/',
                                    body_class: 'static_page_container',
                                    autoResizeMaxHeight: 0,
                                    autoResizeMinHeight: 0,
                                    content_css: '../../site/themes/public/<?php echo getProjectSettings('components,current_public_theme'); ?>/assets/tinymce_content_styles.css?a=' + new Date().getTime()
                                });
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label>Course Objectives</label>
                        <textarea class="form-control" id="item_objectives" name="item_objectives"><?php echo $pre_data['item_objectives'] ? processToRender($pre_data['item_objectives']) : '' ?></textarea>
                        <script>
                            init.push(function () {
                                init_tinymce({
                                    selector: '#item_objectives',
                                    external_filemanager_path: '<?php echo $paths['relative']['common_files']; ?>/filemanager/',
                                    body_class: 'static_page_container',
                                    autoResizeMaxHeight: 0,
                                    autoResizeMinHeight: 0,
                                    content_css: '../../site/themes/public/<?php echo getProjectSettings('components,current_public_theme'); ?>/assets/tinymce_content_styles.css?a=' + new Date().getTime()
                                });
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label>Course Requirement</label>
                        <textarea class="form-control" id="item_requirements" name="item_requirements"><?php echo $pre_data['item_requirements'] ? processToRender($pre_data['item_requirements']) : '' ?></textarea>
                        <script>
                            init.push(function () {
                                init_tinymce({
                                    selector: '#item_requirements',
                                    external_filemanager_path: '<?php echo $paths['relative']['common_files']; ?>/filemanager/',
                                    body_class: 'static_page_container',
                                    autoResizeMaxHeight: 0,
                                    autoResizeMinHeight: 0,
                                    content_css: '../../site/themes/public/<?php echo getProjectSettings('components,current_public_theme'); ?>/assets/tinymce_content_styles.css?a=' + new Date().getTime()
                                });
                            });
                        </script>
                    </div>
                </div>
                <div class="col-xl-12 col-lg-3">
                    <div class="panel widget-tasks">
                        <div class="panel-heading">
                            <span class="panel-title"><i class="panel-title-icon fa fa-tasks"></i>Select Category</span>
                        </div>
                        <div class="panel-body ui-sortable">
                            <div class="task">
                                <div class="action-checkbox">
                                    <label class="px-single">
                                        <input type="checkbox" name="" value="" class="px">
                                        <span class="lbl"></span>
                                    </label>
                                </div>
                                <a href="#" class="task-title">Category Name</a>
                            </div>
                        </div>
                        <div class="panel-footer clearfix">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input class="form-control" type="text" name="category_name"/>
                            </div>
                            <div class="pull-right">
                                <button class="btn btn-xs" id="clear-completed-tasks"><i class="fa fa-eraser text-success"></i> Create Category</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Promotional Video</label>
                        <?php // if($picture['promotional_video']){ ?>
                        <!--                                <div class="old_image_holder">
                                                            <div class="old_image">
                                                                <img src="<?php echo user_picture($picture['user_picture']); ?>" />
                                                                <a href="javascript:" class="delete_old_image btn btn-danger btn-xs" title="Remove Image"/><i class="fa fa-times-circle"></i></a>
                                                            </div>
                                                            <p class="help-block">To upload new image, <a href="javascript:" class="delete_old_image">remove the old image</a> first.</p>
                                                        </div>-->
                        <?php
// }
//                            else{
                        ?>
                        <div class="new_image">
                            <input type="file" id="promotional_video" name="promotional_video">
                            <script type="text/javascript">
                                init.push(function () {
                                    $('#promotional_video').pixelFileInput({placeholder: 'No file selected...'});
                                })
                            </script>
                            <p class="help-block">JPG or PNG image with max file size 500KB &amp; MAX 300x300 resolution.</p>
                        </div>
                        <?php
//                            }
                        ?>
                    </div>
                    <div class="form-group">
                        <label>Featured Image</label>
                        <div class="panel-body p0">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files'); ?>/filemanager/dialog.php?type=1&field_id=thumbImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <img class="" src="" />
                                <input id="thumbImage" name="featured_image" type="hidden" class="form-control" value="<?php echo $pre_data['featured_image'] ? $pre_data['featured_image'] : $_config['default_share_image'] ?>">
                            </div>
                        </div>             
                    </div>
                    <div class="form-group">
                        <label>Published Date</label>
                        <div class="datetimepicker_holder">
                            <input type="text" class="form-control" id="published_date" name="published_date" value="<?php echo $pre_data['published_date'] ? datetime_to_user($pre_data['published_date']) : date('Y-m-d'); ?>" />
                        </div>
                        <script type="text/javascript">
                            init.push(function () {
                                _datetimepicker('course_published_time');
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label>Lecture Numbers</label>
                        <input class="form-control" type="text" name="lecture_numbers" value="<?php echo $pre_data['lecture_numbers'] ? $pre_data['lecture_numbers'] : '' ?>" />
                    </div>
                    <div class="form-group">
                        <label>Course Duration</label>
                        <input class="form-control" type="text" name="item_duration" value="<?php echo $pre_data['item_duration'] ? $pre_data['item_duration'] : '' ?>"/>
                    </div>
                    <div class="form-group">
                        <label>Publication Status</label>
                        <select name="publication_status" class="form-control form-group-margin">
                            <option value="drafted" <?php
                            if ($pre_data['publication_status'] == 'drafted') {
                                echo 'selected';
                            }
                            ?>>Drafted</option>
                            <option value="published" <?php
                            if ($pre_data['publication_status'] == 'published') {
                                echo 'selected';
                            }
                            ?>>Published</option>
                            <option value="pending-approval" <?php
                                    if ($pre_data['publication_status'] == 'pending-approval') {
                                        echo 'selected';
                                    }
                                    ?>>Pending Approval</option>
                            <option value="approved" <?php
                            if ($pre_data['publication_status'] == 'approved') {
                                echo 'selected';
                            }
                                    ?>>Approved</option>
                            <option value="paused" <?php
                            if ($pre_data['publication_status'] == 'paused') {
                                echo 'selected';
                            }
                            ?>>Paused</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Access Mode</label>
                        <select name="access_mode" class="form-control form-group-margin">
                            <option value="online" <?php
                            if ($pre_data['access_mode'] == 'online') {
                                echo 'selected';
                            }
                            ?>>Online</option>
                            <option value="offline" <?php
                                    if ($pre_data['access_mode'] == 'offline') {
                                        echo 'selected';
                                    }
                                    ?>>Offline</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pricing Mode</label>
                        <select name="pricing_mode" class="form-control form-group-margin">
                            <option value="course" <?php
                            if ($pre_data['pricing_mode'] == 'course') {
                                echo 'selected';
                            }
                            ?>>Course</option>
                            <option value="per-content" <?php
                            if ($pre_data['pricing_mode'] == 'per-content') {
                                echo 'selected';
                            }
                            ?>>Per-Content</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Course Content Flow Type</label>
                        <select name="item_content_flow_type" class="form-control form-group-margin">
                            <option value="open" <?php
                            if ($pre_data['item_content_flow_type'] == 'open') {
                                echo 'selected';
                            }
                            ?>>Open</option>
                            <option value="pass" <?php
                            if ($pre_data['item_content_flow_type'] == 'pass') {
                                echo 'selected';
                            }
                            ?>>Pass</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Net Price</label>
                        <input class="form-control" type="text" name="net_price" value="<?php echo $pre_data['net_price'] ? $pre_data['net_price'] : '' ?>"/>
                    </div>
                    <div class="form-group">
                        <label>Discount</label>
                        <input class="form-control" type="text" name="discount" value="<?php echo $pre_data['discount'] ? $pre_data['discount'] : '' ?>"/>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input class="form-control" type="text" name="price" value="<?php echo $pre_data['price'] ? $pre_data['price'] : '' ?>"/>
                    </div>
                    <div class="form-group">
                        <label>Course Difficulties</label>
                        <select name="item_difficulties" class="form-control form-group-margin">
                            <option value="beginner" <?php
                            if ($pre_data['item_difficulties'] == 'beginner') {
                                echo 'selected';
                            }
                            ?>>Beginner</option>
                            <option value="medium" <?php
                            if ($pre_data['item_difficulties'] == 'medium') {
                                echo 'selected';
                            }
                            ?>>Medium</option>
                            <option value="advanced" <?php
                            if ($pre_data['item_difficulties'] == 'advanced') {
                                echo 'selected';
                            }
                            ?>>Advanced</option>    
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Course Language</label>
                        <select name="item_language" class="form-control form-group-margin">
                            <option value="bangla" <?php
                        if ($pre_data['item_language'] == 'bangla') {
                            echo 'selected';
                        }
                        ?>>Bangla</option>
                            <option value="english" <?php
                        if ($pre_data['item_language'] == 'english') {
                            echo 'selected';
                        }
                        ?>>English</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <span class="panel-title">SEO Feature</span>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label>Tags&nbsp;<img class="autocomplete_loading" src="<?php echo common_files() . '/css/images/working.GIF'; ?>"></label>
                        <div id="tag_autocomplete"></div>
                        <script type="text/javascript">
                            init.push(function () {
                                new set_autosuggest({
                                    container: '#tag_autocomplete',
                                    submit_labels: false,
                                    ajax_page: _root_path_ + '/api/dev_tag_management/get_tags_autocomplete',
                                    single: false,
                                    parameters: {'tag_group': 'tags'},
                                    multilingual: true,
                                    input_field: '#input_tag',
                                    field_name: 'tags',
                                    add_what: 'Tag',
                                    add_new: true,
                                    url_for_add: _root_path_ + '/api/dev_tag_management/add_edit_tags',
                                    field_for_add: 'tag_title',
                                    data_for_add: {tag_group: 'tags'},
                                    existing_items: <?php echo to_json_object($refillTags); ?>,
                                });
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label>Content Meta Keywords</label>
                        <textarea class="form-control" name="item_meta_keyword"><?php echo $pre_data['item_meta_keyword'] ? $pre_data['item_meta_keyword'] : ''; ?></textarea>
                        <p class="help-block">Separate keywords by Comma (,)</p>
                    </div>
                    <div class="form-group">
                        <label>Content Meta Descriptions</label>
                        <textarea class="form-control" name="item_meta_description"><?php echo $pre_data['item_meta_description'] ? $pre_data['item_meta_description'] : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Content Excerpt</label>
                        <textarea class="form-control" name="item_excerpt"><?php echo $pre_data['item_excerpt'] ? $pre_data['item_excerpt'] : ''; ?></textarea>
                        <p class="help-block">Leave blank to use content description</p>
                    </div>
                    <div class="form-group">
                        <label>Featured Status</label>
                        <div class="switcherElement">
                            <input type="checkbox" name="featured_status" value="yes"> 
                        </div>
                        <script>
                            init.push(function () {
                                initSwitcher();
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label class="db">&nbsp;</label>
                            <?php
                            echo submitButtonGenerator(array(
                                'action' => $edit ? 'update' : 'save',
                                'size' => '',
                                'title' => $edit ? 'Update Course' : 'Save Course',
                                'icon' => $edit ? 'icon_update' : 'icon_save',
                                'name' => 'submit_content',
                                'value' => 'submit_action',
                                'text' => $edit ? 'Update Course' : 'Save Course',))
                            ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
$course_id = $_GET['id'];
$courseManager = jack_obj('dev_course_management');

$args = array(
    'pk_item_id' => $course_id,
    'single' => true,
);
$argCurriculum = array(
    'fk_course_id' => $course_id,
);
$course = $courseManager->get_courses($args);
$curriculum = $courseManager->get_curriculum($argCurriculum);
$lectures = $courseManager->get_lectures($arg_lectures = array(
    'fk_course_id' => $course_id,
        ));

if ($_POST['curriculum_type']) {
    $data = array(
        'curriculum_type' => $_POST['curriculum_type'],
        'modified_at_date' => date('Y-m-d'),
        'modified_at_time' => date('H:i:s'),
        'modified_at_int' => time(),
        'modified_by' => $_config['user']['pk_user_id'],
    );

    $ret = $devdb->insert_update('e_courses', $data, " pk_item_id = '" . $course_id . "'");

    if ($ret['error']) {
        print_errors($ret['error']);
        $course = $_POST;
    } else {
        $course_id = $ret['success'];
        add_notification($course_id . ' has been updated.', 'success');
        user_activity::add_activity($course_id . ' has been updated.', 'success', 'update');
        header('location: ' . url('instructorszone/new_curriculum?id=' . $course_id));
        exit();
    }
}

if ($_POST['lecture_type']) {
    if ($_POST['lecture_type'] == 'video') {
        if ($_FILES['lecture_video']["name"]) {
            $supported_ext = array('mp4', '');
            $max_filesize = 512000;
            $target_dir = "upload/";
            if (!file_exists($target_dir))
                mkdir($target_dir);
            $target_file = $target_dir . basename($_FILES['lecture_video']["name"]);
            $fileinfo = pathinfo($target_file);
            $target_file = $target_dir . str_replace(' ', '_', $fileinfo['filename']) . '_' . time() . '.' . $fileinfo['extension'];
            $videoFileType = pathinfo($target_file, PATHINFO_EXTENSION);
            if (in_array(strtolower($videoFileType), $supported_ext)) {
                if ($max_filesize && $_FILES['lecture_video']["size"] <= $max_filesize) {
                    if (!move_uploaded_file($_FILES['lecture_video']["tmp_name"], $target_file)) {
                        $ret['error'][] = 'Promotional Video : File was not uploaded, please try again.';
                        $args['lecture_video'] = '';
                    } else {
                        $fileinfo = pathinfo($target_file);
                        $args['lecture_video'] = $fileinfo['basename'];
                    }
                } else
                    $ret['error'][] = 'Video : <strong>' . $_FILES['lecture_video']["size"] . ' B</strong> is more than supported file size <strong>' . $max_filesize . ' B';
            } else
                $ret['error'][] = 'Video : <strong>.' . $videoFileType . '</strong> is not supported extension. Only supports .' . implode(', .', $supported_ext);
        }
        
        $data = array(
            'fk_course_id' => $course_id,
            'fk_module_id' => $_POST['module_id'],
            'lecture_type' => 'video',
            'lecture_title' => $_POST['lecture_title'],
            'lecture_video' => $args['lecture_video'] ? $args['lecture_video'] : $pre_data['lecture_video'],
            'lecture_duration' => $_POST['lecture_duration'],
            'created_at_date' => date('Y-m-d'),
            'created_at_time' => date('H:i:s'),
            'created_at_int' => time(),
            'created_by' => $_config['user']['pk_user_id'],
        );
    }

    if ($_POST['lecture_type'] == 'text') {
        $data = array(
            'fk_course_id' => $course_id,
            'fk_module_id' => $_POST['module_id'],
            'lecture_type' => 'text',
            'lecture_title' => $_POST['lecture_title'],
            'lecture_content' => $_POST['lecture_content'],
            'created_at_date' => date('Y-m-d'),
            'created_at_time' => date('H:i:s'),
            'created_at_int' => time(),
            'created_by' => $_config['user']['pk_user_id'],
        );
    }

    $ret = $devdb->insert_update('e_lectures', $data);

    if ($ret['error']) {
        print_errors($ret['error']);
        $course = $_POST;
    } else {
        $lecture_id = $ret['success'];
        add_notification($lecture_id . ' has been saved.', 'success');
        user_activity::add_activity($lecture_id . ' has been saved.', 'success', 'save');
        header('location: ' . url('instructorszone/new_curriculum?id=' . $course_id));
        exit();
    }
}
?>
<style type="text/css">
    .lecture-text, .lecture-video{
        border-bottom: 1px solid graytext
    }
</style>
<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <div class="row">
        <div class="col-md-3 mt-5">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link" href="update_course?id=<?php echo $course_id ?>">Back To Course Update</a>
                <a class="nav-link active" href="new_curriculum?id=<?php echo $course_id ?>">Curriculum</a>
            </div>
        </div>
        <div class="col-md-9 mt-5">
            <?php
            $course_type = $course['curriculum_type'];
            if (!$course_type) {
                ?>       
                <form method="POST" action="">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Curriculum Type</label>
                            <select class="form-control" name="curriculum_type">
                                <option value="module" <?php
                                if ($course_type == 'module') {
                                    echo 'selected';
                                }
                                ?>>Module</option>
                                <option value="lecture" <?php
                                if ($course_type == 'lecture') {
                                    echo 'selected';
                                }
                                ?>>Lecture</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </div>
                </form>
                <?php
            }
            if ($course_type == 'module') {
                ?>
                <a href="add_module?id=<?php echo $course_id ?>" class="btn btn-danger">Add New Module</a>            
                <div class="accordion mt-3" id="accordionExample">
                    <?php
                    foreach ($curriculum['module_info']['data'] as $module) :
                        $id = $module['pk_module_id'];
                        ?>
                        <div class="card" id="<?php echo $id ?>">
                            <div class="card-header" id="heading-<?php echo $id ?>">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-<?php echo $id ?>" aria-expanded="true" aria-controls="collapse-<?php echo $id ?>">
                                        <?php echo $module['module_name'] ?>
                                    </button>
                                    <a href="javascript:" data-id="<?php echo $id ?>" data-toggle="modal" data-target="#confirm_delete_modal" class="confirmDelete btn btn-danger pull-right">Delete</a>
                                </h5>
                            </div>
                            <div id="collapse-<?php echo $id ?>" class="collapse show" aria-labelledby="heading-<?php echo $id ?>" data-parent="#accordionExample">
                                <div class="card-body">
                                    <button type="button" class="lecture btn btn-success btn-sm mb-3" data-id="<?php echo $id ?>" data-toggle="modal" data-target="#lectureModal">
                                        Add Lecture
                                    </button>
                                    <?php
                                    foreach ($lectures['data'] as $lecturer) {
                                        if($lecturer['fk_module_id'] == $id){                                       
                                        if($lecturer['lecture_content']){
                                    ?>
                                    <p class="lecture-text"><a href="view_lecture?content=<?php echo $lecturer['lecture_content'] ?>" target="_blank"><span class="text-dark"><?php echo $lecturer['lecture_title'] ?> </span></a> <span class="pull-right"><a href="delete_lecture?id=<?php echo $lecturer['pk_lecture_id'] ?>&cur=<?php echo $course_id ?>" class="btn-sm btn btn-danger">Delete</a></span></p>
                                        <?php
                                        }else{
                                        ?>
                                    <p class="lecture-video"><a href="view_lecture?video=<?php echo $lecturer['lecture_video'] ?>" target="_blank"><span class="text-danger"><?php echo $lecturer['lecture_title'] ?></span></a> <span class="pull-right"><?php echo $lecturer['lecture_duration'] ?></span></p>
                                    <?php
                                        }
                                        }    
                                    }  
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                    <div class="modal fade bd-example-modal-lg" id="lectureModal" tabindex="-1" role="dialog" aria-labelledby="lectureModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="lectureModalLabel">Add New Lecture</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="module_id" class="moduleId" value="">
                                        <div class="form-group">
                                            <label>Lecture Type</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text">
                                                        <input type="radio" name="lecture_type" class="lectureType" value="video"> &nbsp;Video
                                                    </div>
                                                    <div class="input-group-text">
                                                        <input type="radio" name="lecture_type" class="lectureType" value="text"> &nbsp;Text
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Lecture Title</label>
                                            <input type="text" name="lecture_title" class="form-control">
                                        </div>
                                        <div class="video">
                                            <div class="form-group">
                                                <label>Video</label>
                                                <input type="file" name="lecture_video" class="form-control-sm">
                                            </div>
                                            <div class="form-group">
                                                <label>Lecture Duration</label>
                                                <input type="text" name="lecture_duration" class="form-control">
                                            </div>
                                        </div>
                                        <div class="text">
                                            <div class="form-group">
                                                <label>Lecture Content</label>
                                                <textarea name="lecture_content"></textarea>
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
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Lecture</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
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
                url: "delete_module?id=" + Id,
                success: function ()
                {              
                    $('#confirm_delete_modal').modal('toggle');
                    $('#' + Id).hide();
                }
            });
        });
    });
</script>


<script>
    init.push(function () {
        $('.lecture').click(function () {
            var Id = $(this).data('id');
            $(".moduleId").val(Id);
        });

        $('.lectureType').click(function () {
            var lectureType = $(this).val();
            if (lectureType == 'video') {
                $('.text').hide();
                $('.video').show();
            }
            if (lectureType == 'text') {
                $('.video').hide();
                $('.text').show();
            }
        });
    });
</script>
<?php
$edit = $_config['user']['pk_user_id'];
global $paths;

if($_POST['ajax_type'] == 'delete_old_image'){
    if($_POST['user_id']){
        $sql = "SELECT user_picture FROM dev_users WHERE pk_user_id = '".$_POST['user_id']."'";
        $user_picture = $devdb->get_row($sql);
        if($user_picture['user_picture']){
            if(file_exists($paths['absolute']['profile_pictures'].'/'.$user_picture['user_picture'])){
                if(@unlink($paths['absolute']['profile_pictures'].'/'.$user_picture['user_picture'])){
                    $data = array(
                        'user_picture' => ''
                    );
                    $update = $devdb->insert_update('dev_users',$data," pk_user_id='".$_POST['user_id']."'");
                    echo json_encode(array('success' => 'Profile Picture Removed'));
                    $this->reCacheUser($_POST['user_id']);
                }
                else echo json_encode(array('error' => 'Failed to delete the picture.'));
            }
            else echo json_encode(array('success' => 'Picture wasn\'t found, upload a new one.'));
        }
        else echo json_encode(array('error' => 'No Profile Picture Found.'));
    }
    else echo json_encode(array('error' => 'No User Found.'));
    exit();
}

if($_POST){
    if($edit){
        $args = array(
            'user_id' => array($edit),
            'single' => true,
            );
        $pre_user = $this->get_users($args);
        $pre_user = $pre_user['data'];
        }
    $required_fields = array(
        'data' => array(
            'user_fullname' => 'Full Name',
            'user_mobile' => 'Contact Number',
            //'user_email' => 'Email ID',
            ),
        );
    $ret = array();
    $data = $_POST;
    $data['edit'] = $edit ? $edit : NULL;
    $data['validate'] = $required_fields;

    $ret = $this->add_edit_user($data);
    if($ret['error']){
        foreach($ret['error'] as $e){
            add_notification($e,'error');
        }
        $user = $_POST['data'];
    }
    else{
        add_notification('Your Account has been '.($edit ? 'updated.':'added.'),'success');
        user_activity::add_activity('Your Account '.$user_id.' has been '.($edit ? 'updated.':'added.'),'success', ($edit ? 'update':'create'));
        header('location:'.$_SERVER['REQUEST_URI']);
        exit();
    }
}
if($edit && !$user){
    $args = array(
        'user_id' => array($edit),
        'single' => true,
        );
    $user = $this->get_users($args);
    $user = $user['data'];
    if(!$user){
        add_notification('User not found for editing.','error');
        header('location:'.$myUrl);
        exit();
        }
    }

load_js(array(
    theme_path().'/js/fileupload.min.js'
    ));
doAction('render_start');
?>
    <div class="page-header">
        <h1><?php echo $edit ? 'Edit ' : 'Add '?>My Account</h1>
        <div class="oh">
            <div class="btn-group btn-group-sm">
                <a href="<?php echo $myUrl?>" class="btn btn-flat btn-labeled" title="">
                    <span class="btn-label  fa fa-arrow-left"></span>
                    Back
                </a>
            </div>
        </div>
    </div>
    <div class="">
        <form name="widget_pos_add_edit" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="data[user_fb_id]" value="<?php echo $user ? $user['user_fb_id'] : ''?>">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Full Name</label>
                    <input class="form-control" type="text" name="data[user_fullname]" id="user_fullname" value="<?php echo $user ? $user['user_fullname'] : ''?>" required/>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" type="text" name="data[user_email]" id="user_email" value="<?php echo $user ? $user['user_email'] : ''?>" required/>
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <?php if($user['user_picture']){?>
                        <div class="old_image_holder">
                            <div class="old_image">
                                <img src="<?php echo user_picture($user['user_picture']); ?>" />
                                <a href="javascript:" class="delete_old_image btn btn-danger btn-xs" title="Remove Image"/><i class="fa fa-times-circle"></i></a>
                            </div>
                            <p class="help-block">To upload new image, <a href="javascript:" class="delete_old_image">remove the old image</a> first.</p>
                        </div>
                        <?php }
                    else{
                        ?>
                        <div class="new_image">
                            <input type="file" id="profile_picture" name="data[user_picture]">
                            <script type="text/javascript">
                                init.push(function () {
                                    $('#profile_picture').pixelFileInput({ placeholder: 'No file selected...' });
                                })
                            </script>
                            <p class="help-block">JPG or PNG image with max file size 500KB &amp; MAX 300x300 resolution.</p>
                        </div>
                        <?php
                        }
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" class="form-control" name="data[user_country]" value="<?php echo $user['user_country'] ? $user['user_country'] : ''; ?>"/>
                </div>
                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="text" class="form-control" name="data[user_mobile]" value="<?php echo $user['user_mobile'] ? $user['user_mobile'] : ''; ?>"/>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="panel-footer">
                    <input type="submit" name="submit_btn" class="btn btn-success" value="Submit" />
                </div>
            </div>
        </form>
    </div>
<?php if($edit):?>
    <script type="text/javascript">
        init.push(function(){
            $('.delete_old_image').click(function(){
                var ths = $(this);
                var container = ths.closest('.form-group');
                var old_image = container.find('.old_image_holder');
                var new_image_text = '<div class="new_image">\
                                        <input type="file" id="profile_picture" name="data[user_picture]">\
                                        <p class="help-block">JPG or PNG image with max file size 500KB &amp; MAX 300x300 resolution.</p>\
                                    </div>';
                if(confirm('Do you really want to delete the picture?')){
                    var _data = {
                        'ajax_type' : 'delete_old_image',
                        'user_id' : <?php echo $edit?>
                        };
                    $.ajax({
                        type: "POST",
                        url: '<?php echo current_url()?>',
                        data: _data,
                        cache: false,
                        dataType: 'json',
                        success: function(reply_data){
                            if(reply_data.success){
                                $.growl.warning({ title: "Success", message: "Your Picture is removed.", size: 'large' });
                                old_image.slideUp(200,function(){
                                    old_image.remove();
                                    container.append(new_image_text);
                                    $('#profile_picture').pixelFileInput({ placeholder: 'No file selected...' });
                                    });
                                }
                            else
                                $.growl.error({ title: "Error", message: "Your Picture wasn't removed.<br />Please try again.", size: 'large' });
                            }
                        });
                    }
                });
            });
    </script>
<?php endif; ?>
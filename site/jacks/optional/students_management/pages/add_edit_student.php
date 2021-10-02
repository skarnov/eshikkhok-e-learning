<?php
global $multilingualFields;

$edit = $_GET['edit'] ? $_GET['edit'] : NULL;

$user = array();

if(!$this->default_role){
    add_notification('Default role for students is not set. Please set the default role for students.', 'error');
    header('Location: '.$myUrl);
    exit();
    }

if ($edit) {
    $args = array(
        'user_role' => $this->default_role,
        'user_id' => $edit,
        'single' => true,
        );
    $user = $this->get_instructors($args);
    
    if (!$user) {
        add_notification('Student not found for editing.', 'error');
        header('location:' . $myUrl);
        exit();
        }
    else $user = $user['data'];
    }

if($_POST){
    $profileManager = jack_obj('dev_profile_management');

    $data = $_POST;
    $data['edit'] = $edit ? $edit : NULL;
    $data['user_type'] = 'public';
    $data['user_meta_type'] = 'student';
    
    if(!$edit) $data['roles_list'] = array($this->default_role);

    $ret = $profileManager->add_edit_user($data);

    if($ret['error']){
        print_errors($ret['error']);
        $user = $data;
        }
    else{
        $user_id = $edit ? $edit : $ret['success'];
        add_notification('The student has been '.($edit ? 'updated.':'added.'),'success');
        user_activity::add_activity('The student (ID: '.$user_id.') has been '.($edit ? 'updated.':'created.'),'success', ($edit ? 'update':'create'));
        header('location:'.$_SERVER['REQUEST_URI']);
        exit();
        }
    }

doAction('render_start');
?>
<div class="page-header">
    <h1><?php echo $edit ? 'Update Student' : 'New Student' ?></h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => build_url(null, array('action', 'edit')),
                'action' => 'list',
                'icon' => 'icon_list',
                'text' => 'All Students',
                'title' => 'Manage All Students',
                'size' => 'sm',
            ));
            ?>
            <?php if (has_permission('add_student')): ?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_student'), array('edit')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New Student',
                    'title' => 'Create New Student',
                    'size' => 'sm',
                ));
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="panel">
    <form name="widget_pos_add_edit" method="post" action="" enctype="multipart/form-data">
        <div class="panel-body">
            <div class="side_aligned_tab">
                <ul id="uidemo-tabs-default-demo" class="nav nav-tabs">
                    <li class="active">
                        <a href="#basic" data-toggle="tab">Basic</a>
                    </li>
                    <li class="">
                        <a href="#additional" data-toggle="tab">Additional</a>
                    </li>
                    <li class="">
                        <a href="#social" data-toggle="tab">Social</a>
                    </li>
                </ul>
                <div class="tab-content tab-content-bordered">
                    <div class="tab-pane fade active in" id="basic">
                        <input type="hidden" name="user_fb_id" value="<?php echo $user ? $user['user_fb_id'] : ''?>">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input class="form-control char_limit" data-max-char="490" type="text" name="user_fullname" id="user_fullname" value="<?php echo $user ? $user['user_fullname'] : ''?>" required/>
                        </div>
                        <div class="form-group">
                            <label>User Name/Login Name</label>
                            <input class="form-control char_limit" data-max-char="250" type="text" name="user_name" id="user_name" value="<?php echo $user ? $user['user_name'] : ''?>" required/>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input class="form-control char_limit" data-max-char="390" type="text" name="user_email" id="user_email" value="<?php echo $user ? $user['user_email'] : ''?>" required/>
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
                                    <input type="file" id="profile_picture" name="user_picture">
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
                        <div class="form-group">
                            <label>Password - <span class="fa fa-lock text-danger removeReadOnly" title="Change Password"></span></label>
                            <input <?php echo $edit ? 'readonly' : ''?> class="form-control char_limit" data-max-char="100" type="password" name="user_password" id="user_password" value="" placeholder="<?php echo $edit ? '*********' : '' ?>" <?php echo $edit ? '' : 'required'?>/>
                            <?php echo $edit ? '<p class="help-block">Leave password blank if you don\'t want to change.</p>' : '' ?>
                        </div>
                        <!--<div class="form-group">
                            <label>User Type</label>
                            <select class="form-control" name="user_type" required>
                                <option value="admin" <?php /*echo $user && $user['user_type'] == 'admin' ? 'selected' : ''*/?>>Admin</option>
                                <option value="public" <?php /*echo $user && $user['user_type'] == 'public' ? 'selected' : ''*/?>>Public</option>
                            </select>
                        </div>-->
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="user_status" required>
                                <option value="active" <?php echo $user && $user['user_status'] == 'active' ? 'selected' : ''?>>Active</option>
                                <option value="inactive" <?php echo $user && $user['user_status'] == 'inactive' ? 'selected' : ''?>>Inactive</option>
                                <option value="not_verified" <?php echo $user && $user['user_status'] == 'not_verified' ? 'selected' : ''?>>Not Verified</option>
                            </select>
                        </div>
                       <!-- <div class="form-group">
                            <label class="db">Roles</label>
                            <?php
/*                            foreach($roles as $i=>$v){
                                $selected = $user && in_array($v['pk_role_id'], $user['roles_list']) !== false ? 'checked' : '';
                                */?>
                                <label class="checkbox-inline">
                                    <input type="checkbox" class="px" name="roles_list[]" value="<?php /*echo $v['pk_role_id']; */?>" <?php /*echo $selected; */?> />
                                    <span class="lbl"><?php /*echo $v['role_name']; */?></span>
                                </label>
                                <?php
/*                            }
                            */?>
                        </div>-->
                    </div>
                    <div class="tab-pane fade " id="additional">
                        <div class="form-group">
                            <label>Birthdate</label>
                            <div class="input-group">
                                <input id="birthdate" type="text" class="form-control" name="user_birthdate" value="<?php echo $user['user_birthdate'] && $user['user_birthdate'] != '0000-00-00' ? date('d-m-Y',strtotime($user['user_birthdate'])) : date('d-m-Y'); ?>"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                            <script type="text/javascript">
                                init.push(function(){
                                    _datepicker('birthdate');
                                });
                            </script>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select class="form-control" name="user_gender">
                                <option value="male" <?php echo $user['user_gender'] == 'male' ? 'selected' : ''?>>Male</option>
                                <option value="female" <?php echo $user['user_gender'] == 'female' ? 'selected' : ''?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" class="form-control char_limit" data-max-char="20" name="user_country" value="<?php echo $user['user_country'] ? $user['user_country'] : ''; ?>"/>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" class="form-control char_limit" data-max-char="45" name="user_mobile" value="<?php echo $user['user_mobile'] ? $user['user_mobile'] : ''; ?>"/>
                        </div>
                    </div>
                    <div class="tab-pane fade " id="social">
                        <div class="form-group">
                            <label>Facebook</label>
                            <input class="form-control" type="text" name="meta[facebook_link]" value="<?php echo $user ? getUserMetaValue($user, 'facebook_link') : ''?>"/>
                        </div>
                        <div class="form-group">
                            <label>Google Plus</label>
                            <input class="form-control" type="text" name="meta[googleplus_link]" value="<?php echo $user ? getUserMetaValue($user, 'googleplus_link') : ''?>"/>
                        </div>
                        <div class="form-group">
                            <label>Linked-in</label>
                            <input class="form-control" type="text" name="meta[linkedin_link]" value="<?php echo $user ? getUserMetaValue($user, 'linkedin_link') : ''?>"/>
                        </div>
                        <div class="form-group">
                            <label>Twitter</label>
                            <input class="form-control" type="text" name="meta[twitter_link]" value="<?php echo $user ? getUserMetaValue($user, 'twitter_link') : ''?>"/>
                        </div>
                        <div class="form-group">
                            <label>Pinterest</label>
                            <input class="form-control" type="text" name="meta[pinterest_link]" value="<?php echo $user ? getUserMetaValue($user, 'pinterest_link') : ''?>"/>
                        </div>
                        <div class="form-group">
                            <label>Youtube</label>
                            <input class="form-control" type="text" name="meta[youtube_link]" value="<?php echo $user ? getUserMetaValue($user, 'youtube_link') : ''?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer tar">
            <?php
            echo submitButtonGenerator(array(
                'href' => current_url(),
                'action' => $edit ? 'edit' : 'add',
                'icon' => $edit ? 'icon_save' : 'icon_update',
                'text' => $edit ? 'Update Student' : 'Save Student',
                'title' => $edit ? 'Update Student' : 'Save Student',
                'size' => ''
            ));
            ?>
        </div>
    </form>
</div>
<script type="text/javascript">
    init.push(function(){
        initCharLimit();
        function handleReadOnlyIcon(ths,event){
            //var ths = $(this);
            var inputElem = ths.closest('.form-group').find('input');
            if(inputElem.attr('readonly')){
                if(event == 'click') inputElem.removeAttr('readonly');
                ths.removeClass('fa-lock text-danger');
                ths.addClass('fa-unlock text-success');
            }
            else{
                if(event == 'click') inputElem.attr('readonly',true);
                ths.removeClass('fa-unlock text-success');
                ths.addClass('fa-lock text-danger');
            }
        }
        $('.removeReadOnly').click(function(){
            handleReadOnlyIcon($(this),'click');
        }).mouseenter(function(){
            //handleReadOnlyIcon($(this),'hover');
        }).mouseout(function(){
            //handleReadOnlyIcon($(this),'hover');
        });
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
                    'user_id' : <?php echo $edit ? $edit : -1?>
                };
                $.ajax({
                    type: "POST",
                    url: '<?php echo current_url()?>',
                    data: _data,
                    cache: false,
                    dataType: 'json',
                    success: function(reply_data){
                        if(reply_data.success){
                            $.growl.warning({ title: "Success", message: "User Picture is removed.", size: 'large' });
                            old_image.slideUp(200,function(){
                                old_image.remove();
                                container.append(new_image_text);
                                $('#profile_picture').pixelFileInput({ placeholder: 'No file selected...' });
                            });
                        }
                        else
                            $.growl.error({ title: "Error", message: "User Picture wasn't removed.<br />Please try again.", size: 'large' });
                    }
                });
            }
        });
    });
</script>
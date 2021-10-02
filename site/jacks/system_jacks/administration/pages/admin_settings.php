<?php
global $adminmenu, $multilingualConfigFields;;

if(!has_permission('edit_system_settings')){
    add_notification('You don\'t have enough permission to edit system settings.','error');
    header('Location:'.build_url(NULL,array('edit')));
    exit();
    }
if(isset($_POST['remove_cache'])){
    $dir    = _path('root','absolute').'/cached_data';
    array_map('unlink', glob($dir.'/*')) == @rmdir($dir);

    mkdir($dir);
    add_notification('All Cache Files deleted','success');
    }
$orginal_settings = array();
$cont = array();
$sql = "SELECT * FROM dev_config";
$content = $devdb->get_results($sql);

foreach($content as $i=>$v){
    $theValue = $v['config_value'];
    if (in_array($v['config_name'], $multilingualConfigFields) !== false){
        $theValue = processToRender($v['config_value']);
        }
    $cont[$v['config_name']] = $theValue;
    $orginal_settings[$v['config_name']] = $v['config_value'];
    }

if(isset($cont['unserialize'])){
    $cont['unserialize'] = unserialize($cont['unserialize']);
    if($cont['unserialize']){
        foreach($cont['unserialize'] as $i=>$v){
            $cont[$v] = unserialize($cont[$v]);
            }
        }
    }

if($_POST){
    $ret = array();
    //pre($_POST);
    //sanetizing
    //$data = $devdb->deep_escape($_POST);
    $data = $_POST;

    if(!$ret['error']){

        $serializedFields = array();
        if(isset($data['serializeToData'])){
            foreach($data['serializeToData'] as $i=>$v){
                $data['data'][$i] = serialize($v);
                $serializedFields[] = $i;
                }
            $data['data']['unserialize'] = serialize($serializedFields);
            }

        $insertion = " INSERT INTO dev_config (config_name,config_value) VALUES";

        $defaults = array(
            'required_email' => 0,
            'required_mobile' => 0,
            'allow_facebook_login' => 0,
            'fb_user_status' => 0,
            'apply_registration_verification' => 0,
            'auto_login' => 0,
            'required_terms' => 0,
            'use_captcha_in_registration' => 0,
            'force_default_user_settings' => 0,
            'show_user_reset_method_email' => 'email',
            'show_user_reset_method_sms' => 0,
            'use_smtp_email_account' => 0,
            'mainNavMenuToRight' => 0,
            'mainNavMenuHide' => 0,
            );

        foreach($defaults as $i=>$v){
            if(!isset($data['data'][$i]))
                $data['data'][$i] = $v;
            }

        foreach($orginal_settings as $i=>$v){
            if(!isset($data['data'][$i]))
                $data['data'][$i] = $v;
            }

        foreach($data['data'] as $i=>$v){
            if($i == 'website_description')
                $v = htmlspecialchars($v,ENT_QUOTES);

            if (in_array($i, $multilingualConfigFields) !== false){
                $v = processToStore($orginal_settings[$i], $v);
                }

            $insertion .= " ('".$i."','".$v."'),";
            }
        $insertion = rtrim($insertion,',');
        
        $deleted = $devdb->query("TRUNCATE TABLE dev_config");

        $ret = $devdb->query($insertion);
        }

    if($ret['error']){
        foreach($ret['error'] as $e){
            add_notification($e,'error');
            }
        $content = $_POST['data'];
        }
    else{
        removeCache('devConfig');
        //cleanCache('devAdminMenuOrder');
        add_notification('The system settings has been updated.','success');
        user_activity::add_activity('The system settings has been updated.','success', 'update');
        if($_POST['lastTab'])
            header('location:'.build_url(array('lastTab'=>$_POST['lastTab'])));
        elseif($_GET['lastTab'])
            header('location:'.build_url(array('lastTab'=>$_GET['lastTab'])));
        else header('location:'.build_url());
        exit();
        }
    }



$roleManager = jack_obj('dev_role_permission_management');
$roles = $roleManager->get_roles();

doAction('render_start');
?>
<div class="page-header">
    <h1>System Settings</h1>
</div>
<form name="widget_pos_add_edit" id="setting_form" method="post" action="" enctype="multipart/form-data">
    <div class="panel">
        <div class="panel-body">
			<div class="side_aligned_tab">
				<ul id="uidemo-tabs-default-demo" class="nav nav-tabs">
                    <?php if(has_project_settings('system_settings_menu,title_description')): ?>
					<li class="active">
						<a href="#titleDescription" data-toggle="tab">Title and Description</a>
					</li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,image_settings')): ?>
					<li class="">
						<a href="#imgSettings" data-toggle="tab">Image Settings</a>
					</li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,social_settings')): ?>
					<li class="">
						<a href="#socialSettings" data-toggle="tab">Social Settings</a>
					</li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,admin_login_page_settings')): ?>
                    <li class="">
                        <a href="#adminLoginPage" data-toggle="tab">Admin Login Page Settings</a>
                    </li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,system_authentication_settings')): ?>
                    <li class="">
                        <a href="#SystemAuthenticationSettings" data-toggle="tab">System Authentication Settings</a>
                    </li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,online_payment_settings')): ?>
                    <li class="">
                        <a href="#OnlinePayementSettings" data-toggle="tab">Online Payment Settings</a>
                    </li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,misc_settings')): ?>
					<li class="">
						<a href="#miscSettings" data-toggle="tab">MISC. Settings</a>
					</li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,email_settings')): ?>
                    <li class="">
                        <a href="#emailAccountSettings" data-toggle="tab">Email Accounts</a>
                    </li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,admin_menus')): ?>
                    <li class="">
                        <a href="#adminMenuSettings" data-toggle="tab">Admin Menus</a>
                    </li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,admin_themes')): ?>
                    <li class="">
                        <a href="#adminThemeSettings" data-toggle="tab">Admin Theme</a>
                    </li>
                    <?php endif; ?>
                    <?php if(has_project_settings('system_settings_menu,admin_widgets')): ?>
                    <li class="">
                        <a href="#adminWidgetSettings" data-toggle="tab">Admin Widgets</a>
                    </li>
                    <?php endif; ?>
				</ul>
				<div class="tab-content tab-content-bordered">
                    <?php if(has_project_settings('system_settings_menu,title_description')): ?>
					<div class="tab-pane fade active in" id="titleDescription">
						<div class="form-group">
							<label>Site Title</label>
							<input class="form-control" type="text" name="data[site_name]" id="site_name" value="<?php echo $cont ? $cont['site_name'] : ''?>" required/>
						</div>
						<div class="form-group">
							<label>Add Page/Content Title with Site Title</label>
							<select name="data[add_page_title]" id="add_page_title" class="form-control">
								<option value="yes" <?php echo $cont['add_page_title'] == 'yes' ? 'selected' : ''; ?>>Yes</option>
								<option value="no" <?php echo $cont['add_page_title'] == 'no' ? 'selected' : ''; ?>>No</option>
							</select>
						</div>
						<div class="form-group site_title_related" style="display: none">
							<label>Site Title &amp; Page/Content Title Placement</label>
							<select name="data[site_page_title_placement]" class="form-control">
								<option value="1" <?php echo $cont['site_page_title_placement'] == '1' ? 'selected' : ''; ?>>Site Title First, then Page/Content title</option>
								<option value="2" <?php echo $cont['site_page_title_placement'] == '2' ? 'selected' : ''; ?>>Page/Content title first, then site title</option>
							</select>
						</div>
						<div class="form-group site_title_related" style="display: none">
							<label>Site &amp; Page/Content Title Separator</label>
							<input class="form-control" type="text" name="data[site_page_title_separator]" id="site_page_title_separator" value="<?php echo $cont ? $cont['site_page_title_separator'] : ''?>" required/>
							<p class="help-block">For example <strong> | </strong> or <strong> - </strong>. If you use <strong> | </strong>, it will appear like <strong>My Site Title | My Page/Content Title</strong></p>
						</div>
						<div class="form-group">
							<label>Site Description</label>
							<textarea class="form-control" name="data[website_description]"><?php echo $cont ? $cont['website_description'] : ''?></textarea>
							<p class="help-block">Describe your website here, this information will help search engines.</p>
						</div>
						<div class="form-group">
							<label>Site Keywords</label>
							<textarea class="form-control" name="data[website_keywords]"><?php echo $cont ? $cont['website_keywords'] : ''?></textarea>
							<p class="help-block">Provide valuable keywords for your website.</p>
						</div>
						<div class="form-group dn">
							<label>Copyright Text</label>
							<textarea class="form-control" name="data[website_copyright_text]"><?php echo $cont ? $cont['website_copyright_text'] : ''?></textarea>
						</div>
						<div class="form-group">
                            <label>Site Favicon</label>
                            <div class="panel">
                                <div class="panel-body">
                                    <div class="image_upload_container controlVisible">
                                        <div class="controlBtnContainer">
                                            <div class="controlBtn">
                                                <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=favicon&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                            </div>
                                        </div>
                                        <?php if($cont && $cont['website_favicon']): ?>
                                            <img class="" src="<?php echo get_image($cont['website_favicon'],'100x100x1')?>" />
                                        <?php endif; ?>
                                        <input id="favicon" name="data[website_favicon]" type="hidden" class="form-control" value="<?php echo $cont && $cont['website_favicon'] ? $cont['website_favicon'] : ''?>">
                                    </div>
                                </div>
                            </div>
						</div>
					</div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,image_settings')): ?>
					<div class="tab-pane fade" id="imgSettings">
						<div class="form-group">
							<label>Image Cropping &amp; Resizing Mode</label>
							<div class="form-group">
								<label class="radio">
									<input type="radio" name="data[image_cropping_mode]" value="1" class="px" <?php echo $cont['image_cropping_mode'] == '1' ? 'checked' : ''; ?>>
									<span class="lbl">Crop &amp; Resize Images<p class="help-block">Recommended. Use this for better performance and better view.</p></span>
								</label>

								<label class="radio">
									<input type="radio" name="data[image_cropping_mode]" value="2" class="px" <?php echo $cont['image_cropping_mode'] == '2' ? 'checked' : ''; ?>>
									<span class="lbl">Do not crop, only resize</span>
								</label>
								<label class="radio">
									<input type="radio" name="data[image_cropping_mode]" value="0" class="px" <?php echo $cont['image_cropping_mode'] == '0' ? 'checked' : ''; ?>>
									<span class="lbl">Do nothing. Keep original Image in original size</span>
								</label>
							</div>
						</div>
						<div class="form-group">
							<label>Force of Image Cropping &amp; Resizing Mode</label>
							<div class="form-group">
								<label class="radio">
									<input type="radio" name="data[image_cropping_mode_force]" value="yes" class="px" <?php echo $cont['image_cropping_mode_force'] == 'yes' ? 'checked' : ''; ?>>
									<span class="lbl">Force all images to use selected cropping despite of how they are configured.</span>
								</label>
								<label class="radio">
									<input type="radio" name="data[image_cropping_mode_force]" value="no" class="px" <?php echo $cont['image_cropping_mode_force'] == 'no' ? 'checked' : ''; ?>>
									<span class="lbl">Do not force to use this mode. Set this mode only if no mode is configured.</span>
								</label>
							</div>
						</div>
                        <div class="form-group">
                            <label>Image BG Color</label>
                            <input type="text" class="form-control hue_picker" id="image_bg_color" name="data[image_bg_color]" value="<?php echo $cont['image_bg_color'] ? $cont['image_bg_color'] : 'ffffff'?>" />
                            <p class="help-block">Only works in <strong>Do not crop, only resize</strong> mode.</p>
                        </div>
                        <div class="form-group">
                            <label>Image Quality</label>
                            <input type="text" class="form-control" name="data[image_quality]" value="<?php echo $cont ? $cont['image_quality'] : '90'?>" />
                            <p class="help-block">Value from 1 to 100, where 100 means No Compression, Best Quality, High File Size and 1 means Full Compression, Worst Quality, Smallest File Size. Only applicable for JPG and PNG images.</p>
                        </div>
                        <div class="form-group">
                            <label>Default Image For Sharing</label>
                            <div class="panel">
                                <div class="panel-body">
                                    <div class="image_upload_container controlVisible">
                                        <div class="controlBtnContainer">
                                            <div class="controlBtn">
                                                <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=defaultShare&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                            </div>
                                        </div>
                                        <?php if($cont && $cont['default_share_image']): ?>
                                            <img class="" src="<?php echo get_image($cont['default_share_image'],'100x100x1')?>" />
                                        <?php endif; ?>
                                        <input id="defaultShare" name="data[default_share_image]" type="hidden" class="form-control" value="<?php echo $cont && $cont['default_share_image'] ? $cont['default_share_image'] : ''?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Default Image For NO IMAGE</label>
                            <div class="panel">
                                <div class="panel-body">
                                    <div class="image_upload_container controlVisible">
                                        <div class="controlBtnContainer">
                                            <div class="controlBtn">
                                                <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=defaultNoImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                            </div>
                                        </div>
                                        <?php if($cont && $cont['defaultNoImage']): ?>
                                            <img class="" src="<?php echo get_image($cont['defaultNoImage'],'100x100x1')?>" />
                                        <?php endif; ?>
                                        <input id="defaultNoImage" name="data[defaultNoImage]" type="hidden" class="form-control" value="<?php echo $cont && $cont['defaultNoImage'] ? $cont['defaultNoImage'] : ''?>">
                                    </div>
                                </div>
                            </div>
                        </div>
					</div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,social_settings')): ?>
					<div class="tab-pane fade" id="socialSettings">
						<div class="form-group">
							<label>Facebook</label>
							<input class="form-control" type="text" name="data[facebook_link]" id="facebook_link" value="<?php echo $cont ? $cont['facebook_link'] : ''?>"/>
						</div>
						<div class="form-group">
							<label>Google Plus</label>
							<input class="form-control" type="text" name="data[googleplus_link]" id="googleplus_link" value="<?php echo $cont ? $cont['googleplus_link'] : ''?>"/>
						</div>
						<div class="form-group">
							<label>Linked-in</label>
							<input class="form-control" type="text" name="data[linkedin_link]" id="linkedin_link" value="<?php echo $cont ? $cont['linkedin_link'] : ''?>"/>
						</div>
						<div class="form-group">
							<label>Twitter</label>
							<input class="form-control" type="text" name="data[twitter_link]" id="twitter_link" value="<?php echo $cont ? $cont['twitter_link'] : ''?>"/>
						</div>
						<div class="form-group">
							<label>Pinterest</label>
							<input class="form-control" type="text" name="data[pinterest_link]" id="pinterest_link" value="<?php echo $cont ? $cont['pinterest_link'] : ''?>"/>
						</div>
                        <div class="form-group">
                            <label>Youtube</label>
                            <input class="form-control" type="text" name="data[youtube_link]" id="youtube_link" value="<?php echo $cont ? $cont['youtube_link'] : ''?>"/>
                        </div>
					</div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,admin_login_page_settings')): ?>
                    <div class="tab-pane fade" id="adminLoginPage">
                        <div class="form-group">
                            <label>Admin Page URL Slug</label>
                            <input class="form-control" type="text" name="data[admin_login_page]" value="<?php echo $cont['admin_login_page'] ? $cont['admin_login_page'] : '1029384756'?>">
                            <p class="help-block">Case Sensitive. That is "Login" and "login" are not the same.</p>
                        </div>
                        <div class="form-group">
                            <label>Admin Page Logo</label>
                            <div class="panel">
                                <div class="panel-body">
                                    <div class="image_upload_container controlVisible">
                                        <div class="controlBtnContainer">
                                            <div class="controlBtn">
                                                <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=adminPageLogo&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                            </div>
                                        </div>
                                        <?php if($cont && $cont['admin_page_logo']): ?>
                                            <img class="" src="<?php echo get_image($cont['admin_page_logo'],'100x100x1')?>" />
                                        <?php endif; ?>
                                        <input id="adminPageLogo" name="data[admin_page_logo]" type="hidden" class="form-control" value="<?php echo $cont && $cont['admin_page_logo'] ? $cont['admin_page_logo'] : ''?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Admin Page Heading Text</label>
                            <input type="text" class="form-control" name="data[admin_page_heading]" value="<?php echo $cont['admin_page_heading'] ? $cont['admin_page_heading'] : 'Admin Login'?>" />
                        </div>
                        <div class="form-group">
                            <label>Admin Page Background Image</label>
                            <div class="panel">
                                <div class="panel-body">
                                    <div class="image_upload_container controlVisible">
                                        <div class="controlBtnContainer">
                                            <div class="controlBtn">
                                                <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=admin_page_bg_image&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                            </div>
                                        </div>
                                        <?php if($cont && $cont['admin_page_bg_image']): ?>
                                            <img class="" src="<?php echo get_image($cont['admin_page_bg_image'],'100x100x1')?>" />
                                        <?php endif; ?>
                                        <input id="admin_page_bg_image" name="data[admin_page_bg_image]" type="hidden" class="form-control" value="<?php echo $cont && $cont['admin_page_bg_image'] ? $cont['admin_page_bg_image'] : ''?>">
                                    </div>
                                </div>
                            </div>
                            <p class="help-block">Please use at least 1920x1080 Full HD Image</p>
                        </div>
                        <div class="form-group">
                            <label>Admin Page Background Color</label>
                            <input type="text" class="form-control hue_picker" id="admin_page_bg_color" name="data[admin_page_bg_color]" value="<?php echo $cont['admin_page_bg_color'] ? $cont['admin_page_bg_color'] : '#fff'?>" />
                        </div>
                        <div class="form-group">
                            <label>Admin Page Background Color Opacity</label>
                            <input type="number" min="0" max="1" step=".1" class="form-control" id="admin_page_bg_color_opacity" name="data[admin_page_bg_color_opacity]" value="<?php echo $cont['admin_page_bg_color_opacity'] ? $cont['admin_page_bg_color_opacity'] : '.5'?>" />
                            <p class="help-block">Value between 0 to 1</p>
                        </div>
                        <div class="form-group">
                            <label>Admin Page Header Background Color</label>
                            <input type="text" class="form-control hue_picker" id="admin_page_header_bg_color" name="data[admin_page_header_bg_color]" value="<?php echo $cont['admin_page_header_bg_color'] ? $cont['admin_page_header_bg_color'] : '#fff'?>" />
                        </div>
                        <div class="form-group">
                            <label>Admin Page Header Text Color</label>
                            <input type="text" class="form-control hue_picker" id="admin_page_header_text_color" name="data[admin_page_header_text_color]" value="<?php echo $cont['admin_page_header_text_color'] ? $cont['admin_page_header_text_color'] : '#000'?>" />
                        </div>
                        <div class="panel">
                            <div class="panel-heading">
                                <span class="panel-title">Admin Login Form</span>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Admin Login Prompt Text</label>
                                    <input type="text" class="form-control" name="data[admin_login_prompt_text]" value="<?php echo $cont['admin_login_prompt_text'] ? $cont['admin_login_prompt_text'] : 'Sign in to your Account'?>" />
                                </div>
                                <div class="form-group">
                                    <label>Admin Login Prompt Text Font Color</label>
                                    <input type="text" class="form-control hue_picker" id="admin_login_prompt_text" name="data[admin_login_prompt_text_color]" value="<?php echo $cont['admin_login_prompt_text_color'] ? $cont['admin_login_prompt_text_color'] : '#555555'?>" />
                                </div>
                                <div class="form-group">
                                    <label>Login Form Background Color</label>
                                    <input type="text" class="form-control hue_picker" id="admin_page_login_form_bg_color" name="data[admin_page_login_form_bg_color]" value="<?php echo $cont['admin_page_login_form_bg_color'] ? $cont['admin_page_login_form_bg_color'] : '#fff'?>" />
                                </div>
                                <div class="form-group">
                                    <label>Login Form Background Color Opacity</label>
                                    <input type="number" min="0" max="1" step=".1" class="form-control" id="admin_page_login_form_bg_color_opacity" name="data[admin_page_login_form_bg_color_opacity]" value="<?php echo $cont['admin_page_login_form_bg_color_opacity'] ? $cont['admin_page_login_form_bg_color_opacity'] : '1'?>" />
                                    <p class="help-block">Value between 0 to 1</p>
                                </div>
                                <div class="form-group">
                                    <label class="cb db">Login Form Position</label>
                                    <label class="radio-inline">
                                        <input type="radio" name="data[login_form_position]" class="px" value="default" checked />
                                        <span class="lbl">Default</span>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="data[login_form_position]" class="px" value="left" <?php echo $cont['login_form_position'] && $cont['login_form_position'] == 'left' ? 'checked' : ''?> />
                                        <span class="lbl">Left</span>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="data[login_form_position]" class="px" value="right" <?php echo $cont['login_form_position'] && $cont['login_form_position'] == 'right' ? 'checked' : ''?> />
                                        <span class="lbl">Right</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,system_authentication_settings')): ?>
                    <div class="tab-pane fade" id="SystemAuthenticationSettings">
                        <ul id="uidemo-tabs-default-demo" class="nav nav-tabs">
                            <li class="active">
                                <a href="#registration_login_settings" data-toggle="tab">Registration & Login Settings</a>
                            </li>
                            <li class="">
                                <a href="#password_reset_settings" data-toggle="tab">Password Reset Settings</a>
                            </li>
                            <!--li class="">
                                <a href="#forgot_id_settings" data-toggle="tab">Forgot ID Settings</a>
                            </li--><!-- / .dropdown -->
                        </ul>

                        <div class="tab-content tab-content-bordered">
                            <div class="tab-pane fade active in" id="registration_login_settings">
                                <div class="form-group mb0">
                                    <label>Is Email required to register ?&nbsp;&nbsp; </label>
                                    <div id="switchers-colors-square-required-email" class="form-group-margin">
                                        <input type="checkbox" id="allow_email_login" data-class="switcher-success" name="data[required_email]" <?php echo $cont['required_email'] ? 'checked' : ''; ?> />&nbsp;&nbsp;
                                    </div>
                                    <script type="text/javascript">
                                        init.push(function(){
                                            $('#switchers-colors-square-required-email > input').switcher({
                                                theme: 'square',
                                                on_state_content: 'Yes',
                                                off_state_content: 'No'
                                            });
                                        });
                                    </script>
                                </div>
                                <div class="form-group mb0">
                                    <label>Is Mobile Number required to register?&nbsp;&nbsp; </label>
                                    <div id="switchers-colors-square-login-mobile" class="form-group-margin">
                                        <input type="checkbox" id="allow_mobile_login" data-class="switcher-success" name="data[required_mobile]" <?php echo $cont['required_mobile'] ? 'checked' : ''; ?> />&nbsp;&nbsp;
                                    </div>
                                    <div id="error_msg" class="text-bold text-danger"></div>
                                    <script type="text/javascript">
                                        init.push(function(){
                                            $('#allow_email_login, #allow_mobile_login').change(function(){

                                                if(!$('#allow_email_login').is(':checked') && !$('#allow_mobile_login').is(':checked')){
                                                    var e = ['Please check at least one between Email & Mobile for Registration process'];
                                                    //growl_error(e);
                                                    //$('#error_msg').html(e);
                                                    }
                                                else{
                                                    $('#error_msg').html('');
                                                    }

                                                }).change();
                                            $('#switchers-colors-square-login-mobile > input').switcher({
                                                theme: 'square',
                                                on_state_content: 'Yes',
                                                off_state_content: 'No'
                                            });
                                            if($('#allow_mobile_login').is(":checked")) {
                                                $('.panel-mobile').removeClass('dn');
                                                $('.verification_sms').removeAttr('disabled');
                                                $('.verification_user').removeAttr('disabled');
                                               // $('.show_password_reset_sms').removeAttr('disabled');
                                                $('.default_password_reset_sms').removeAttr('disabled');

                                            }
                                            else{
                                                $('.verification_sms').removeAttr('checked');
                                                $('.verification_user').removeAttr('checked');
                                                $('.verification_sms').attr('disabled', true);
                                                $('.verification_user').attr('disabled', true);
                                                //$('.show_password_reset_sms').attr('disabled',true);
                                                $('.default_password_reset_sms').attr('disabled',true);
                                                $('.panel-mobile').addClass('dn');
                                                }
                                            if(!$('#allow_email_login').is(":checked")){
                                                $('.verification_email').attr('disabled', true);
                                                //$('.show_password_reset_email').attr('disabled', true);
                                                $('.default_password_reset_email').attr('disabled', true);
                                                //$('.verification_user').removeAttr('disabled');
                                                }
                                            $('#allow_mobile_login').change(function(){
                                                if(this.checked){
                                                    $('.verification_sms').removeAttr('disabled');
                                                    $('.verification_user').removeAttr('disabled');
                                                    //$('.show_password_reset_sms').removeAttr('disabled');
                                                    $('.default_password_reset_sms').removeAttr('disabled');
                                                    $('.panel-mobile').removeClass('dn');
                                                    }
                                                else{
                                                    $('.verification_sms').removeAttr('checked');
                                                    $('.verification_user').removeAttr('checked');
                                                   // $('.show_password_reset_sms').removeAttr('checked');
                                                    $('.default_password_reset_sms').removeAttr('checked');
                                                    $('.verification_sms').attr('disabled', true);
                                                   // $('.show_password_reset_sms').attr('disabled', true);
                                                    $('.default_password_reset_sms').attr('disabled', true);
                                                    $('.panel-mobile').addClass('dn');
                                                    if($('#allow_registration_verification').is(':checked') && !$('#verification_email').is(':checked') && !$('#verification_sms').is(':checked') && !$('#verification_user').is(':checked')){
                                                        var err = ['Please select one of the following registration methods or uncheck the apply verification system'];
                                                        $('#error_verification').html(err);
                                                    }
                                                    else{
                                                        $('#error_verification').html('');
                                                    }
                                                    }
                                                });
                                            $('#allow_email_login').change(function(){
                                                if(this.checked){
                                                    $('.verification_email').removeAttr('disabled');
                                                    $('.verification_user').removeAttr('disabled');
                                                 //   $('.show_password_reset_email').removeAttr('disabled');
                                                    $('.default_password_reset_email').removeAttr('disabled');
                                                    }
                                                else{
                                                    $('.verification_email').removeAttr('checked');
                                                    $('.verification_user').removeAttr('checked');
                                                 //   $('.show_password_reset_email').removeAttr('checked');
                                                    $('.default_password_reset_email').removeAttr('checked');
                                                    $('.verification_email').attr('disabled', true);
                                                 //   $('.show_password_reset_email').attr('disabled', true);
                                                    $('.default_password_reset_email').attr('disabled', true);

                                                    if($('#allow_registration_verification').is(':checked') && !$('#verification_email').is(':checked') && !$('#verification_sms').is(':checked') && !$('#verification_user').is(':checked')){
                                                        var err = ['Please select one of the following registration methods or uncheck the apply verification system'];
                                                        $('#error_verification').html(err);
                                                    }
                                                    else{
                                                        $('#error_verification').html('');
                                                    }
                                                    }
                                            });
                                        });
                                    </script>
                                </div>
                                <div class="panel panel-mobile dn">
                                    <div class="panel-heading">
                                        <span class="panel-title">
                                            SMS Gateway Settings
                                        </span>
                                        </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label>ID</label>
                                            <input type="text" name="data[sms_id]" value="<?php echo $cont['sms_id'] ? $cont['sms_id'] : ''; ?>" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" name="data[sms_password]" value="<?php echo $cont['sms_password'] ? $cont['sms_password'] : ''; ?>" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label>Brand Name</label>
                                            <input type="text" name="data[sms_brand]" value="<?php echo $cont['sms_brand'] ? $cont['sms_brand'] : ''; ?>" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <hr/>
                                <?php if(!isset($_config['hide_facebook_login_settings'])): ?>
                                <div class="form-group mb0">
                                    <label>Allow Login with Facebook ?&nbsp;&nbsp; </label>
                                    <div id="switchers-colors-square-login-facebook" class="form-group-margin">
                                        <input type="checkbox" id="allow_facebook_login" data-class="switcher-success" name="data[allow_facebook_login]" <?php echo $cont['allow_facebook_login'] ? 'checked' : ''; ?> />&nbsp;&nbsp;
                                    </div>
                                    <script type="text/javascript">
                                        init.push(function(){
                                            $('#switchers-colors-square-login-facebook > input').switcher({
                                                theme: 'square',
                                                on_state_content: 'Yes',
                                                off_state_content: 'No'
                                                });

                                            if($('#allow_facebook_login').is(":checked")) $('.panel-facebook').removeClass('dn');
                                            $(document).off('change','#allow_facebook_login').on('change','#allow_facebook_login',function(){
                                                if(this.checked){
                                                    $('.panel-facebook').removeClass('dn');
                                                    }
                                                else{
                                                    $('.panel-facebook').addClass('dn');
                                                    }
                                                });
                                        });
                                    </script>
                                </div>
                                <div class="panel panel-facebook dn">
                                    <div class="panel-heading">
                                        <span class="panel-title">Facebook Login Settings</span>
                                    </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label>App ID</label>
                                            <input class="form-control" type="text" name="data[fb_app_id]" id="fb_app_id" value="<?php echo $cont ? $cont['fb_app_id'] : ''?>"/>
                                        </div>
                                        <div class="form-group">
                                            <label>App Secret</label>
                                            <input class="form-control" type="text" name="data[fb_app_secret]" id="fb_app_secret" value="<?php echo $cont ? $cont['fb_app_secret'] : ''?>"/>
                                        </div>
                                        <hr/>
                                        <div class="form-group">
                                            <label>Default Role for Users</label>
                                            <select name="data[fb_user_default_role]" id="fb_user_default_role" class="form-control">
                                                <?php
                                                if($roles->data){
                                                    foreach($roles->data as $i=>$v){
                                                        $selected = $cont['fb_user_default_role'] && $cont['fb_user_default_role'] == $v->pk_role_id ? 'selected' : '';
                                                        ?>
                                                        <option value="<?php echo $v->pk_role_id?>" <?php echo $selected?>><?php echo $v->role_name?></option>
                                                    <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group site_title_related" style="display: none">
                                            <label>Register Users as Active ? &nbsp;&nbsp; </label>
                                            <div id="switchers-colors-square_active_users" class="form-group-margin">
                                                <input type="checkbox" name="data[fb_user_status]" data-class="switcher-success" <?php echo $cont['fb_user_status'] ? 'checked' : ''; ?>>&nbsp;&nbsp;
                                            </div>
                                            <script type="text/javascript">
                                                init.push(function(){
                                                    $('#switchers-colors-square_active_users > input').switcher({
                                                        theme: 'square',
                                                        on_state_content: 'Yes',
                                                        off_state_content: 'No'
                                                    });
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                                <hr/>
                                <?php endif; ?>
                                <div class="form-group mb0">
                                    <label>Apply Registration Verification System ?&nbsp;&nbsp; </label>
                                    <div id="switchers-colors-square-registration-verification" class="form-group-margin">
                                        <input type="checkbox"  id="allow_registration_verification" data-class="switcher-success" name="data[apply_registration_verification]" <?php echo $cont['apply_registration_verification'] ? 'checked' : ''; ?> />&nbsp;&nbsp;
                                    </div>
                                    <div class="text-bold text-danger" id="error_verification"></div>
                                    <script type="text/javascript">
                                        init.push(function(){
                                            $('#switchers-colors-square-registration-verification > input').switcher({
                                                theme: 'square',
                                                on_state_content: 'Yes',
                                                off_state_content: 'No'
                                                });
                                            if($('#allow_registration_verification').is(":checked")){
                                                $('.panel-registration-verification').removeClass('dn');
                                                }
                                            $('#verification_email, #verification_sms, #verification_user').change(function(){
                                                if(!$('#verification_email').is(':checked') && !$('#verification_sms').is(':checked') && !$('#verification_user').is(':checked')){
                                                    var err = ['Please select one of the following registration methods or uncheck the apply verification system'];
                                                    $('#error_verification').html(err);
                                                    }
                                                else{
                                                    $('#error_verification').html('');
                                                    }
                                                }).change();
                                            $('#allow_registration_verification').change(function(){
                                                if(this.checked){
                                                    $('.panel-registration-verification').removeClass('dn');
                                                    if(!$('#verification_email').is(':checked') && !$('#verification_sms').is(':checked') && !$('#verification_user').is(':checked')){
                                                        var err = ['Please select one of the following registration methods or uncheck the apply verification system'];
                                                        $('#error_verification').html(err);
                                                        }
                                                    else{
                                                        $('#error_verification').html('');
                                                        }
                                                    }
                                                else{
                                                    $('.panel-registration-verification').addClass('dn');
                                                    $('#error_verification').html('');
                                                    }


                                                }).change();


                                        });
                                    </script>
                                </div>
                                <div class="panel panel-registration-verification dn">
                                    <div class="panel-heading">
                                        <span class="panel-title">Verification Methods</span>
                                    </div>
                                    <div class="panel-body">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" id="verification_email" name="data[verification_method]" value="email" <?php echo $cont['verification_method'] == 'email' ? 'checked' : ''?> class="px verification_email"/>
                                                <span class="lbl">Email</span>
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" id="verification_sms" name="data[verification_method]" value="sms" <?php echo $cont['verification_method']=='sms' ? 'checked' : ''?> class="px verification_sms"/>
                                                <span class="lbl">SMS</span>
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" id="verification_user" name="data[verification_method]" value="user" <?php echo $cont['verification_method']=='user' ? 'checked' : ''?> class="px verification_user"/>
                                                <span class="lbl">Prompt User for Registration Verification System</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <hr/>
                                <div class="form-group mb0">
                                    <label>Auto Login after Registration ?&nbsp;&nbsp; </label>
                                    <div id="switchers-colors-square-auto-login" class="form-group-margin">
                                        <input type="checkbox" data-class="switcher-success" name="data[auto_login]" <?php echo $cont['auto_login'] ? 'checked' : ''; ?> />&nbsp;&nbsp;
                                    </div>
                                    <script type="text/javascript">
                                        init.push(function(){
                                            $('#switchers-colors-square-auto-login > input').switcher({
                                                theme: 'square',
                                                on_state_content: 'Yes',
                                                off_state_content: 'No'
                                            });

                                        });
                                    </script>
                                </div>
                                <hr/>
                                <div class="form-group mb0">
                                    <label>Terms & Conditions required ?&nbsp;&nbsp; </label>
                                    <div id="switchers-colors-square-termsnconditions" class="form-group-margin">
                                        <input type="checkbox" data-class="switcher-success" name="data[required_terms]" <?php echo $cont['required_terms'] ? 'checked' : ''; ?> />&nbsp;&nbsp;
                                    </div>
                                    <script type="text/javascript">
                                        init.push(function(){
                                            $('#switchers-colors-square-termsnconditions > input').switcher({
                                                theme: 'square',
                                                on_state_content: 'Yes',
                                                off_state_content: 'No'
                                            });
                                        });
                                    </script>
                                </div>
                                <hr/>
                                <div class="form-group mb0">
                                    <label>Use captcha in Registration Form ? [Only works if theme supports captcha] &nbsp;&nbsp; </label>
                                    <div id="registration_captcha" class="form-group-margin">
                                        <input type="checkbox" data-class="switcher-success" name="data[use_captcha_in_registration]" <?php echo $cont['use_captcha_in_registration'] ? 'checked' : ''; ?> />&nbsp;&nbsp;
                                    </div>
                                    <script type="text/javascript">
                                        init.push(function(){
                                            $('#registration_captcha > input').switcher({
                                                theme: 'square',
                                                on_state_content: 'Yes',
                                                off_state_content: 'No'
                                            });
                                        });
                                    </script>
                                </div>
                                <hr/>
                                <div class="panel">
                                    <div class="panel-heading">
                                        <a class="pl0 accordion-toggle collapsed" data-toggle="collapse" href="#collapseOne">
                                            Default User Settings for Registration
                                        </a>
                                    </div> <!-- / .panel-heading -->
                                    <div id="collapseOne" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <label>User Role</label>
                                                <?php
                                                if($roles->data){
                                                    foreach($roles->data as $i=>$v){
                                                        $selected = $cont['default_user_role'] && $cont['default_user_role'] == $v->pk_role_id ? 'checked' : '';
                                                        ?>
                                                        <div class="radio">
                                                            <label>
                                                                <input type="radio" name="data[default_user_role]" value="<?php echo $v->pk_role_id?>" <?php echo $selected?> class="px"/>
                                                                <span class="lbl"><?php echo $v->role_name?></span>
                                                            </label>
                                                        </div>
                                                    <?php
                                                    }
                                                }
                                                ?>
                                            </div><!-- / .collapse -->
                                            <!--div class="form-group">
                                                <label>User Type</label>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="data[default_user_type]" value="admin" <?php echo $cont['default_user_type'] && $cont['default_user_type'] == 'admin' ? 'checked' : '';?> class="px"/>
                                                        <span class="lbl">Admin</span>
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="data[default_user_type]" value="public" <?php echo $cont['default_user_type'] && $cont['default_user_type'] == 'public' ? 'checked' : '';?> class="px"/>
                                                        <span class="lbl">Public</span>
                                                    </label>
                                                </div>
                                            </div-->
                                            <div class="form-group">
                                                <label>User Status</label>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="data[default_user_status]" value="active" <?php echo $cont['default_user_status'] && $cont['default_user_status'] == 'active' ? 'checked' : '';?> class="px"/>
                                                        <span class="lbl">Active</span>
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="data[default_user_status]" value="inactive" <?php echo $cont['default_user_status'] && $cont['default_user_status'] == 'inactive' ? 'checked' : '';?> class="px"/>
                                                        <span class="lbl">Inactive</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group mb0">
                                                <label>Force Default User Settings ?&nbsp;&nbsp; </label>
                                                <div id="switchers-colors-square-force-default" class="form-group-margin">
                                                    <input type="checkbox" data-class="switcher-success" name="data[force_default_user_settings]" <?php echo $cont['force_default_user_settings'] ? 'checked' : ''; ?> />&nbsp;&nbsp;
                                                </div>
                                                <script type="text/javascript">
                                                    init.push(function(){
                                                        $('#switchers-colors-square-force-default > input').switcher({
                                                            theme: 'square',
                                                            on_state_content: 'Yes',
                                                            off_state_content: 'No'
                                                        });
                                                    });
                                                </script>
                                            </div>
                                        </div> <!-- / .panel-body -->
                                    </div>
                                </div>
                                <script type="text/javascript">
                                    init.push(function(){
                                        var values = <?php echo ($cont['default_user_role'] || $cont['default_user_type'] || $cont['default_user_status']) ? 1 : 0?>;
                                        if(values == 1){
                                            $('.accordion-toggle').removeClass('collapsed');
                                            $('.panel-collapse').addClass('in');
                                            $('.panel-collapse').css({
                                                'height' : 'auto'
                                                });
                                            }
                                        else{
                                            $('.accordion-toggle').addClass('collapsed');
                                            $('.panel-collapse').removeClass('in');
                                            $('.panel-collapse').css({
                                                'height' : '0'
                                            });
                                        }
                                        });
                                </script>
                            </div> <!-- / .tab-pane -->
                            <div class="tab-pane fade" id="password_reset_settings">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="data[password_reset_method]" value="user" <?php echo $cont['password_reset_method'] && $cont['password_reset_method'] == 'user' ? 'checked' : '';?> class="px password_reset"/>
                                        <span class="lbl">Prompt User for Password Reset Method</span>
                                    </label>
                                </div>
                                <div class="panel panel-user-password dn">
                                    <div class="panel-heading">
                                        <span class="panel-title">Password Reset Methods</span>
                                    </div>
                                    <div class="panel-body">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="data[show_user_reset_method_email]" value="email" <?php echo $cont && $cont['show_user_reset_method_email'] ? 'checked' : '';?> class="px show_password_reset_email"/>
                                                <span class="lbl">Email</span>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="data[show_user_reset_method_sms]" value="sms" <?php echo $cont && $cont['show_user_reset_method_sms'] ? 'checked' : '';?> class="px show_password_reset_sms"/>
                                                <span class="lbl">SMS</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="data[password_reset_method]" value="system" <?php echo $cont['password_reset_method'] && $cont['password_reset_method'] == 'system' ? 'checked' : '';?> class="px password_reset"/>
                                        <span class="lbl">Set Default Password Reset Method</span>
                                    </label>
                                </div>
                                <div class="panel panel-default-password dn">
                                    <div class="panel-heading">
                                        <span class="panel-title">Password Reset Methods</span>
                                    </div>
                                    <div class="panel-body">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="data[default_password_reset_method]" value="email" <?php echo $cont['default_password_reset_method'] && $cont['default_password_reset_method'] == 'email' ? 'checked' : '';?> class="px default_password_reset_email"/>
                                                <span class="lbl">Email</span>
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="data[default_password_reset_method]" value="sms" <?php echo $cont['default_password_reset_method'] && $cont['default_password_reset_method'] == 'sms' ? 'checked' : '';?> class="px default_password_reset_sms"/>
                                                <span class="lbl">SMS</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <script type="text/javascript">
                                    init.push(function(){
                                        if($('.password_reset:checked').val() == 'system') {
                                            $('.panel-default-password').removeClass('dn');
                                            $('.panel-user-password').addClass('dn');
                                            }
                                        else if($('.password_reset:checked').val() == 'user'){
                                            $('.panel-user-password').removeClass('dn');
                                            $('.panel-default-password').addClass('dn');
                                            }
                                        $('.password_reset').change(function(){
                                            if($(this).val() == 'system'){
                                                $('.panel-default-password').removeClass('dn');
                                                $('.panel-user-password').addClass('dn');
                                            }
                                            else{
                                                $('.panel-user-password').removeClass('dn');
                                                $('.panel-default-password').addClass('dn');
                                            }
                                        });
                                    });
                                </script>
                            </div> <!-- / .tab-pane -->
                            <!-- / .tab-pane -->
                        </div>
                    </div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,online_payment_settings')): ?>
                    <div class="tab-pane fade" id="OnlinePayementSettings">
                        <div class="form-group">
                            <label>Store ID</label>
                            <input class="form-control" type="text" name="data[op_store_id]" id="op_store_id" value="<?php echo $cont ? $cont['op_store_id'] : ''?>" />
                        </div>
                        <div class="form-group">
                            <label>Store Password</label>
                            <input class="form-control" type="text" name="data[op_store_password]" id="op_store_password" value="<?php echo $cont ? $cont['op_store_password'] : ''?>" />
                        </div>
                        <div class="form-group">
                            <label>Payment URL</label>
                            <input class="form-control" type="text" name="data[op_url]" id="op_url" value="<?php echo $cont ? $cont['op_url'] : ''?>" />
                        </div>
                        <div class="form-group">
                            <label>Payment Validation URL</label>
                            <input class="form-control" type="text" name="data[op_validation_url]" id="op_validation_url" value="<?php echo $cont ? $cont['op_validation_url'] : ''?>" />
                        </div>
                    </div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,misc_settings')): ?>
					<div class="tab-pane fade" id="miscSettings">
                        <div class="form-group">
                            <label>Remove All Cache</label>
                            <button type="submit" name="remove_cache" class="btn btn-default btn-sm"><i class="fa fa-rotate-right"></i>&nbsp;Remove Cache</button>
                        </div>
                        <?php
                        $sysL = $_config['langs'];
                        if(false && count($sysL) > 1){
                            ?>
                            <div class="form-group">
                                <label class="db">System Language</label>
                                <?php
                                foreach($sysL as $l=>$L){
                                    $checked = isset($_config['slang']) && $_config['slang'] == $l ? 'checked' : '';
                                    ?>
                                    <label class="radio-inline">
                                        <input <?php echo $checked?> type="radio" class="px" name="data[system_language]" value="<?php echo $l?>" />
                                        <span class="lbl"><?php echo $L['title']?></span>
                                    </label>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="form-group">
                            <label>Private API Key for Google Map</label>
                            <input class="form-control" type="text" name="data[map_api]" id="map_api" value="<?php echo $cont ? $cont['map_api'] : ''?>" />
                        </div>
						<div class="form-group">
							<label>Private Security Key for Filemanager</label>
							<input class="form-control" type="text" name="data[__FILEMANGER_KEY__]" id="__FILEMANGER_KEY__" value="<?php echo $cont ? $cont['__FILEMANGER_KEY__'] : ''?>" required/>
						</div>
                        <div class="form-group">
                            <label>Meta Tags for Header</label>
                            <textarea class="form-control" name="data[meta_tags_for_header]" id="meta_tags_for_header"><?php echo $cont ? $cont['meta_tags_for_header'] : ''?></textarea>
                        </div>
						<div class="form-group">
							<label>System Mode</label>
							<select class="form-control" name="data[system_mode]">
								<option value="online" <?php echo $cont['system_mode'] == 'online' ? 'selected' : ''?>>Online</option>
								<option value="maintenance" <?php echo $cont['system_mode'] == 'maintenance' ? 'selected' : ''?>>Maintenance</option>
							</select>
						</div>
						<div class="form-group">
							<label>Only Back-End?</label>
							<select class="form-control" name="data[noFront]">
								<option value="false" <?php echo $cont['noFront'] == 'false' ? 'selected' : ''?>>No</option>
								<option value="true" <?php echo $cont['noFront'] == 'true' ? 'selected' : ''?>>Yes</option>
							</select>
						</div>
						<div class="form-group">
							<label>Time Zone</label>
							<input class="form-control" type="text" name="data[time_zone]" id="time_zone" value="<?php echo $cont ? $cont['time_zone'] : ''?>" required/>
						</div>
						<div class="form-group">
							<label>Mobile URL of this Site</label>
							<input class="form-control" type="text" name="data[mob_url]" id="mob_url" value="<?php echo $cont ? $cont['mob_url'] : ''?>"/>
						</div>
						<div class="form-group">
							<label>Reserved Pages</label>
							<input class="form-control" type="text" name="data[reserved_pages]" id="reserved_pages" value="<?php echo $cont ? $cont['reserved_pages'] : ''?>"/>
							<p class="help-block">Separate each page slug by comma (,), do not use spaces.</p>
						</div>
						<!--<div class="form-group">
							<label>Current Public Theme</label>
							<input class="form-control" type="text" name="data[current_public_theme]" id="current_public_theme" value="<?php /*echo $cont ? $cont['current_public_theme'] : ''*/?>" required/>
						</div>-->
						<div class="form-group">
							<label>Religions</label>
							<input class="form-control" type="text" name="data[religions]" id="religions" value="<?php echo $cont ? $cont['religions'] : ''?>"/>
							<p class="help-block">Separate each religion by comma (,), do not use spaces.</p>
						</div>
					</div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,email_settings')): ?>
                    <div class="tab-pane fade" id="emailAccountSettings">
                        <div class="panel">
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Email Account</label>
                                    <input class="form-control email_account" type="text" name="data[smtp_email_address]" id="smtp_email_address" value="<?php echo $cont ? $cont['smtp_email_address'] : ''?>"/>
                                </div>
                                <div class="form-group">
                                    <label>Email Password</label>
                                    <input class="form-control email_password" type="password" name="data[smtp_email_password]" id="smtp_email_password" value="<?php echo $cont ? $cont['smtp_email_password'] : ''?>"/>
                                </div>
                                <div class="form-group">
                                    <label>Email Account Holder's Name</label>
                                    <input class="form-control email_account_holder" type="text" name="data[smtp_email_name]" id="smtp_email_name" value="<?php echo $cont ? $cont['smtp_email_name'] : ''?>"/>
                                </div>
                                <div class="form-group">
                                    <label>SMTP Host</label>
                                    <input class="form-control smtp_host" type="text" name="data[smtp_host]" id="smtp_host" value="<?php echo $cont ? $cont['smtp_host'] : ''?>"/>
                                </div>
                                <div class="form-group">
                                    <label>SMTP Port</label>
                                    <input class="form-control smtp_port" type="text" name="data[smtp_port]" id="smtp_port" value="<?php echo $cont ? $cont['smtp_port'] : ''?>"/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="data[use_smtp_email_account]" value="use_smtp_email_account" <?php echo $cont && $cont['use_smtp_email_account'] ? 'checked' : '';?> class="px use_smtp_email_account"/>
                                    <span class="lbl">Send all emails from this Email Account</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,admin_menus')): ?>
                    <div class="tab-pane fade" id="adminMenuSettings">
                        <style type="text/css">
                            .adminMenuItemsSortable .sorthandle{
                                position: absolute !important;
                                left: 7px;
                                top: 15px;
                                }
                            .sortablePlaceHolder{
                                border:1px dashed #ddd;
                                }
                        </style>
                        <div class="form-group">
                            <?php
                            global $adminmenu;
                            $sorted = reOrderAdminMenu(true);

                            foreach($adminmenu->menu_items as $position=>$g){
                                ?>
                                <div class="panel">
                                    <div class="panel-heading">
                                        <span class="panel-title">Position: <strong><?php echo ucwords($position)?></strong></span>
                                    </div>
                                    <div class="panel-body">
                                        <ul class="mb0 widget-tasks list-group adminMenuSortable" data-position="<?php echo $position?>">
                                            <?php
                                            if($g){
                                                foreach($g as $gId=>$gItems){
                                                    if(!$gItems) continue;
                                                    ?>
                                                    <li class="ml0 mr0 task list-group-item">
                                                        <i class="sorthandle fa fa-arrows-v task-sort-icon"></i>
                                                        <input type="hidden" name="serializeToData[adminMenu][<?php echo $position?>][<?php echo $gId?>]" value="<?php echo $gId?>" />
                                                    <span class="task-title">
                                                    <?php echo $gId?>
                                                    </span>
                                                        <ul class="ml20 mt10 mb0 widget-tasks list-group adminMenuItemsSortable" data-position="<?php echo $gId?>">
                                                            <?php
                                                            if($gItems){
                                                                foreach($gItems as $i=>$v){
                                                                    ?>
                                                                    <li class="ml0 mr0 task list-group-item">
                                                                        <i class="sorthandle fa fa-arrows-v task-sort-icon"></i>
                                                                        <div class="input-group">
                                                                            <input class="form-control" type="text" name="serializeToData[adminMenu][<?php echo $position?>][<?php echo $gId?>][<?php echo $i?>][label]" value="<?php echo $v['label']?>" />
                                                                            <span class="input-group-addon">
								                                                <label class="px-single"><input type="checkbox" name="serializeToData[adminMenu][<?php echo $position?>][<?php echo $gId?>][<?php echo $i?>][show]" value="yes" class="px" <?php echo !isset($v['show']) || (isset($v['show']) && $v['show']) || !$sorted ? 'checked' : ''?>><span class="lbl"></span></label>
							                                                </span>
                                                                        </div>
                                                                    </li>
                                                                    <?php
                                                                    }
                                                                }
                                                            else{
                                                                ?>
                                                                <li class="emptyRow ml0 mr0 task list-group-item">
                                                                    <p class="help-block mt0">Drag and Drop a menu item here</p>
                                                                </li>
                                                                <?php
                                                                }
                                                            ?>
                                                        </ul>
                                                    </li>
                                                    <?php
                                                    }
                                                }
                                            else{
                                                ?>
                                                <li class="emptyRow ml0 mr0 task list-group-item">
                                                    <p class="help-block mt0">Drag and Drop a menu group here</p>
                                                </li>
                                                <?php
                                                }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php
                                }
                            ?>
                            <script type="text/javascript">
                                init.push(function(){
                                    $('.adminMenuSortable').sortable({
                                        handle: '.sorthandle',
                                        connectWith: ".adminMenuSortable",
                                        placeholder: {
                                            element: function(currentItem) {
                                                return $("<li class='sortablePlaceHolder ml0 mr0 task list-group-item '></li>")[0];
                                                },
                                            update: function(container, p){return null;}
                                            },
                                        start: function(event, ui){
                                            $('.sortablePlaceHolder').css({
                                                height: ui.item.outerHeight()+'px'
                                                });
                                            },
                                        opacity: 0.5,
                                        axis: "y",
                                        receive: function(event, ui){
                                            var thisItem = ui.item;
                                            var thisList = $(this);
                                            var senderList = ui.sender;
                                            var positionFrom = senderList.attr('data-position');
                                            var positionTo = thisList.attr('data-position');
                                            var thisMenuGroupInput = thisItem.find('>input');
                                            var thisMenuGroupInputNameParts = thisMenuGroupInput.attr('name').rtrim(']').split('][');
                                            thisMenuGroupInputNameParts = thisMenuGroupInputNameParts[0].split('[').concat(thisMenuGroupInputNameParts.splice(1));
                                            var thisMenuGroupItemsInput = thisItem.find(' > ul input');

                                            //first: change the position in the name of the menu group
                                            thisMenuGroupInputNameParts[2] = positionTo;
                                            thisMenuGroupInput.attr('name', processFormElementName(thisMenuGroupInputNameParts));

                                            //second: change the position in the name of all the menu items of this group
                                            thisMenuGroupItemsInput.each(function(index, element){
                                                var thisElm = $(element);
                                                var thisElmNameParts = thisElm.attr('name').rtrim(']').split('][');
                                                thisElmNameParts = thisElmNameParts[0].split('[').concat(thisElmNameParts.splice(1));
                                                thisElmNameParts[2] = positionTo;
                                                thisElm.attr('name', processFormElementName(thisElmNameParts));
                                                });

                                            //third: remove any emptyRow from this list
                                            thisList.find(' > .emptyRow').remove();

                                            //fourth: adding an empty element to the sender list if it became empty due to this shifting
                                            if(!senderList.find(' > li').length)
                                                senderList.append('<li class="ml0 mr0 task list-group-item emptyRow"><p class="help-block mt0">Drag and Drop a menu group here</p></li>');
                                            }
                                        }).disableSelection();
                                    $('.adminMenuItemsSortable').sortable({
                                        handle: '.sorthandle',
                                        connectWith: ".adminMenuItemsSortable",
                                        placeholder: {
                                            element: function(currentItem) {
                                                return $("<li class='sortablePlaceHolder ml0 mr0 task list-group-item '></li>")[0];
                                                },
                                            update: function(container, p){return null;}
                                            },
                                        start: function(event, ui){
                                            $('.sortablePlaceHolder').css({
                                                height: ui.item.outerHeight()+'px'
                                                });
                                            },
                                        opacity: 0.5,
                                        axis: "y",
                                        receive: function(event, ui){
                                            var thisItem = ui.item;
                                            var thisList = $(this);
                                            var senderList = ui.sender;
                                            var senderListMenuPosition = senderList.closest('.adminMenuSortable');
                                            var thisListMenuPosition = thisList.closest('.adminMenuSortable');
                                            var menuPositionFrom = senderListMenuPosition.attr('data-position');
                                            var menuPositionTo = thisListMenuPosition.attr('data-position');
                                            var positionFrom = senderList.attr('data-position');
                                            var positionTo = thisList.attr('data-position');
                                            var thisItemTextBox = thisItem.find('input[type="text"]');
                                            var thisItemTextBoxNameParts = thisItemTextBox.attr('name').rtrim(']').split('][');
                                            thisItemTextBoxNameParts = thisItemTextBoxNameParts[0].split('[').concat(thisItemTextBoxNameParts.splice(1));

                                            var thisItemCheckBox = thisItem.find('input[type="checkbox"]');
                                            var thisItemCheckBoxNameParts = thisItemCheckBox.attr('name').rtrim(']').split('][');
                                            thisItemCheckBoxNameParts = thisItemCheckBoxNameParts[0].split('[').concat(thisItemCheckBoxNameParts.splice(1));

                                            //first: change the position in the name of the textBox
                                            thisItemTextBoxNameParts[2] = menuPositionTo;
                                            thisItemTextBoxNameParts[3] = positionTo;
                                            thisItemTextBox.attr('name', processFormElementName(thisItemTextBoxNameParts));

                                            //second: change the position in the name of the checkBox
                                            thisItemCheckBoxNameParts[2] = menuPositionTo;
                                            thisItemCheckBoxNameParts[3] = positionTo;
                                            thisItemCheckBox.attr('name', processFormElementName(thisItemCheckBoxNameParts));

                                            //third: remove any emptyRow from this list
                                            thisList.find(' > .emptyRow').remove();

                                            //fourth: adding an empty element to the sender list if it became empty due to this shifting
                                            if(!senderList.find(' > li').length)
                                                senderList.append('<li class="ml0 mr0 task list-group-item emptyRow"><p class="help-block mt0">Drag and Drop a menu group here</p></li>');
                                            }
                                        }).disableSelection();
                                    });
                            </script>
                        </div>
                    </div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,admin_themes')): ?>
                    <div class="tab-pane fade" id="adminThemeSettings">
                        <div class="form-group">
                            <label>Main navigation menu to right.</label>
                            <div id="" class="squareModernInput form-group-margin ">
                                <input type="checkbox" id="mainNavMenuToRight" data-class="switcher-success" name="data[mainNavMenuToRight]" <?php echo $cont['mainNavMenuToRight'] ? 'checked' : ''; ?> />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Hide Main Navigation</label>
                            <div id="" class="squareModernInput form-group-margin ">
                                <input type="checkbox" id="mainNavMenuHide" data-class="switcher-success" name="data[mainNavMenuHide]" <?php echo $cont['mainNavMenuHide'] ? 'checked' : ''; ?> />
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-heading">
                                <span class="panel-title">Select an Admin Theme</span>
                            </div>
                            <div class="panel-body">
                                <?php
                                $adminThemes = array(
                                    'default' => 'Default',
                                    'asphalt' => 'Asphalt',
                                    'purple-hills' => 'Purple Hills',
                                    'adminflare' => 'Admin Flare',
                                    'dust' => 'Dust',
                                    'frost' => 'Frost',
                                    'fresh' => 'Fresh',
                                    'silver' => 'Silver',
                                    'clean' => 'Clean',
                                    'white' => 'white',
                                    'c-green' => 'Green',
                                    'ab-bank' => 'AB Bank',
                                    'a2s' => 'Access 2 School',
                                    );
                                ?>
                                <div class="form-group">
                                    <?php
                                    foreach($adminThemes as $i=>$v){
                                        $checked = '';
                                        if($cont['adminTheme'] && $cont['adminTheme'] == $i) $checked = 'checked';
                                        elseif(!$cont['adminTheme'] && $i == 'default') $checked = 'checked';
                                        ?>
                                        <div class="themeSelectorLabelContainer">
                                            <label class="themeSelectorLabel <?php echo $checked?>">
                                                <span class="title"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $v?></span>
                                                <img src="<?php echo theme_path()?>/assets/theme/<?php echo $i?>.png" />
                                                <input type="radio" name="data[adminTheme]" value="<?php echo $i?>" <?php echo $checked?> />
                                            </label>
                                        </div>
                                        <?php
                                        }
                                    ?>
                                </div>
                                <script type="text/javascript">
                                    init.push(function(){
                                        $('.themeSelectorLabel').click(function(){
                                            $('.themeSelectorLabel').removeClass('checked');
                                            $(this).addClass('checked');
                                            });
                                        });
                                </script>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
                    <?php if(has_project_settings('system_settings_menu,admin_widgets')): ?>
                    <div class="tab-pane fade" id="adminWidgetSettings">
                        <div class="note note-info">
                            <h4 class="title">Notice</h4>
                            An user may not see a widget though the widget is <span class="text-success">turned ON</span> from here as it could be that the user's role is specifically configured not to access the widget at all.
                            <br />
                            However, if an widget is <span class="text-danger">turned OFF</span> from here, the wigdet will not appear even if the user is a super-admin.
                        </div>
                        <div class="form-group">
                            <?php
                            global $adminWidgets;
                            ?>
                            <div class="panel">
                                <div class="panel-heading">
                                    <span class="panel-title">Turn widget On or Off, globally</span>
                                </div>
                                <div class="panel-body">
                                    <ul class="mb0 widget-tasks list-group">
                                        <?php
                                        foreach($adminWidgets->widgets as $position=>$sizes){
                                            foreach($sizes as $size=>$widgets){
                                                foreach($widgets as $widget){
                                                    ?>
                                                    <li class="ml0 mr0 task list-group-item">
                                                        <div class="input-group">
                                                            <?php echo $widget['widget_title']?>
                                                            <span class="input-group-addon">
                                                                <label class="px-single"><input type="checkbox" name="serializeToData[adminWidgets][<?php echo $widget['widget_id']?>]" value="yes" class="px" <?php echo isset($cont['adminWidgets'][$widget['widget_id']]) ? 'checked' : ''?>><span class="lbl"></span></label>
                                                            </span>
                                                        </div>
                                                    </li>
                                                    <?php
                                                    }
                                                }
                                            }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
				</div>
			</div>
        </div>
        <div class="panel-footer oh">
            <div class="pull-right">
                <input type="hidden" name="lastTab" value="<?php echo $_GET['lastTab'] ? $_GET['lastTab'] : ''?>" />
                <?php echo submitButtonGenerator(array(
                        'action' => 'update',
                        'size' => '',
                        'title' => 'Update System Settings',
                        'icon' => 'icon_update',
                        'name' => 'submit_btn',
                        'value' => 'SUBMIT',
                        'text' => 'Update System Settings')) ?>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    init.push(function(){
        var lastTab = '<?php echo $_GET['lastTab'] ? $_GET['lastTab'] : ''?>';
        if(lastTab){
            $('#uidemo-tabs-default-demo li').removeClass('active');
            $('#uidemo-tabs-default-demo li a[href="#'+lastTab+'"]').trigger('click');
            }
        $('#add_page_title').change(function(){
            if($(this).val() == 'yes') $('.site_title_related').slideDown('slow');
            else $('.site_title_related').slideUp('slow');
            }).change();
        $('#uidemo-tabs-default-demo li').click(function(){
            $('#setting_form input[name="lastTab"]').val($(this).find('a').attr('href').replace('#',''));
            });
        $("#setting_form").submit(function(e) {
            var self = this;
            e.preventDefault();
            if($('#allow_email_login').length && !$('#allow_email_login').is(':checked') && !$('#allow_mobile_login').is(':checked')){
                var e = ['Please solve the following error before submit'];
                growl_error(e);
                }
            else if($('#allow_registration_verification').length && $('#allow_registration_verification').is(':checked') && !$('#verification_email').is(':checked') && !$('#verification_sms').is(':checked') && !$('#verification_user').is(':checked')){
                var e = ['Please solve the following error before submit'];
                growl_error(e);
                var err = ['Please select one of the following registration methods or uncheck the apply verification system'];
                $('#error_verification').html(err);
                }
            else{
                self.submit();
                }
            return false;
            });
        var smtpProfiles = {
            gmail : {
                host : 'smtp.gmail.com',
                port : '587'
                },
            yahoo : {
                host : 'mail.yahoo.com',
                port : '587'
                },
            live : {
                host : 'smtp.live.com',
                port : '587'
                },
            outlook : {
                host : 'smtp.live.com',
                port : '587'
                },
            hotmail : {
                host : 'smtp.live.com',
                port : '587'
                }
            };
        $('#smtp_email_address').on('keyup',function(){
            var current_email = $(this).val();
            var current_provider =  current_email.replace(/.*@/, "");
            if(current_provider.length && smtpProfiles[current_provider] !== undefined){
                $('#smtp_host').val(smtpProfiles[current_provider]['host']);
                $('#smtp_port').val(smtpProfiles[current_provider]['port']);
                }
            });
        $('.squareModernInput input').each(function(index, element){
            $(element).switcher({
                theme: 'square',
                on_state_content: 'Yes',
                off_state_content: 'No'
                });
            });
        });
</script>
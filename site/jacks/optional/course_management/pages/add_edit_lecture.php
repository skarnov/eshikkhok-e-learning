<?php

use Vimeo\Vimeo;
global $multilingualFields;

$pageManager = jack_obj('dev_page_management');

$edit = $_GET['edit'] ? $_GET['edit'] : NULL;

$course_id = $_GET['course_id'] ? $_GET['course_id'] : NULL;

$args = array(
    'single' => true,
    'id' => $course_id,
    );
$course_info = $this->get_courses($args);

if(!$course_info){
    add_notification('Please select course first.','error');
    header('location:'.$myUrl);
    exit();
    }
   
$pre_data = array();

if($edit){
    $args = array(
        'single' => true,
        'id' => $edit,
        );
    $pre_data = $this->get_courses($args);

    if(!$pre_data){
        add_notification('Lecture not found for editing.','error');
        header('location:'.$myUrl);
        exit();
        }
    }

if($_POST){
	$ret = array();
	//sanetizing
	$data = $devdb->deep_escape($_POST);
	$data = $_POST;
       
        $data['price'] = $data['price'];
        $data['fk_parent_id'] = $data['course_id'];
        $data['sort_order'] = 0;
        $data['item_type'] = 'lecture';
        $data['item_title'] = processToStore($pre_data['item_title'], $data['item_title']);
        $data['item_summery'] = processToStore($pre_data['item_summery'], $data['item_summery']);
        $data['item_description'] = processToStore($pre_data['item_description'], $data['item_description']);
        $data['item_objectives'] = processToStore($pre_data['item_objectives'], $data['item_objectives']);

        /* Start Vimeo API On 3devs IT
         * Auther: Shaik Obydullah*/
        if($_FILES['promotional_video']['name']){
                 
            $target_dir = "temp/";
            $target_file = $target_dir . basename($_FILES["promotional_video"]["name"]);

            if (move_uploaded_file($_FILES["promotional_video"]["tmp_name"], $target_file)) {
                   echo "The file ". basename( $_FILES["promotional_video"]["name"]). " has been uploaded.";
               } else {
                   echo "Sorry, there was an error uploading your file.";
               }
        
            $client = new Vimeo("1c20f4739bbea5b955e87c0a3e78696bc37ed8a5", "q6CPZxlCgQrk95wVyHV4r0w9FMQNQL7gElV+OXEmcsaD5r1DfntzcNHBSMo9kvMHtAYGVNXSXbl+Kt9i59jYvDeA0z/inGYv80vT8fC/Y/Yg2iIDCB1qR8rCV/qwtCZJ", "733c3da593e21183f01a5d96522f694c");
            $file_name = $_FILES['promotional_video']['name'];
            
            $uri = $client->upload('temp/'.$file_name, array(
                'name' => $data['lecture_title'],
                'description' => $data['lecture_summary']
            ));
            
            $response = $client->request($uri . '?fields=link');
            $data['promotional_video'] = $response['body']['link'];                
        }
        /* End Vimeo API*/

	//validating
	$temp = form_validator::required($data['item_title']);
	if($temp !== true)
		$ret['error'][] = 'Course Title '.$temp;
	
        $temp = form_validator::required($data['item_summery']);
	if($temp !== true)
		$ret['error'][] = 'Course Summary '.$temp;
        
        $temp = form_validator::required($data['item_description']);
	if($temp !== true)
		$ret['error'][] = 'Course Description '.$temp;
         
	if(!$ret['error']){

            $insert_data = array(
                'price' => $data['price'],
                'fk_parent_id' => $data['course_id'],
                'sort_order' => $data['sort_order'],
                'item_type' => $data['item_type'],
                'item_title' => $data['item_title'],
                'item_summery' => $data['item_summery'],
                'item_description' => $data['item_description'],
                'item_objectives' => $data['item_objectives'],
                'featured_image' => $data['featured_image'],
                'item_duration' => $data['item_duration'],
                'publication_status' => $data['publication_status'],
                'modified_at_date' => date('Y-m-d'),
                'modified_at_time' => date('H:i:s'),
                'modified_at_int' => time(),
                'modified_by' => $_config['user']['pk_user_id'],
            );

		if($edit){
			$ret = $devdb->insert_update('e_courses',$insert_data," pk_item_id = '".$edit."'");
			$course_id = $edit;
            }
		else{
            $insert_data['created_at_date'] = date('Y-m-d');
            $insert_data['created_at_time'] = date('H:i:s');
            $insert_data['created_at_int'] = time();
            $insert_data['created_by'] = $_config['user']['pk_user_id'];
			
			$ret = $devdb->insert_update('e_courses',$insert_data);
			$lecture_id = $ret['success'];
                    }
		}
	
	if($ret['error']){
		foreach($ret['error'] as $e){
			add_notification($e,'error');
			}
		$content = $_POST;
		}
	else {

        if (!$ret['error']) {
            doAction('after_lecture_processed');
            if($edit){
                add_notification('The lecture has been updated.', 'success');
                user_activity::add_activity('The lecture (ID: '.$lecture_id.') has been updated', 'success', 'update');
                }
            else{
                add_notification('The lecture has been created', 'success');
                user_activity::add_activity('The lecture (ID: '.$lecture_id.') has been created.', 'success', 'create');
                }

            header('location:' . $_SERVER['REQUEST_URI']);
            exit();
            }
	}
    }
    
doAction('render_start');
?>
<div class="page-header">
    <h1><?php echo $edit ? 'Update Lecture: ' : 'New Lecture' ?></h1>
    <h1>(<?php echo processToRender($course_info['item_title']) ?>)</h1>
    
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => build_url(null,array('action','edit')),
                'action' => 'list',
                'icon' => 'icon_list',
                'text' => 'All Lectures',
                'title' => 'Manage All Lectures',
                'size' => 'sm',
                ));
            ?>
            <?php if(has_permission('add_contents')):?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_lecture'),array('edit')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New Lecture',
                    'title' => 'Create New Lecture',
                    'size' => 'sm',
                ));
                ?>
            <?php endif;?>
        </div>
    </div>
</div>
<form onsubmit="return true;" name="widget_pos_add_edit" method="post" action="" enctype="multipart/form-data">
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-xl-12 col-lg-6">
                    <?php if($course_info['pricing_mode'] == 'per-content'): ?>
                    <div class="form-group">
                        <label>Price</label>
                        <input class="form-control" type="text" name="price" value="<?php echo $pre_data['price'] ? $pre_data['price'] : ''?>"/>
                    </div>
                    <?php endif ?>
                    <div class="form-group">
                        <label>Lecture Title</label>
                        <input type="hidden" name="course_id" value="<?php echo $course_info['pk_item_id'] ?>"/>
                        <input class="form-control" type="text" name="item_title" value="<?php echo $pre_data['item_title'] ? processToRender($pre_data['item_title']) : ''?>" required/>
                    </div>
                    <div class="form-group">
                        <label>Lecture Summary</label>
                        <textarea class="form-control" id="item_summery" name="item_summery"><?php echo $pre_data['item_summery'] ? processToRender($pre_data['item_summery']) : ''?></textarea>
                        <script>
                            init.push(function(){
                                init_tinymce({
                                    selector: '#item_summery',
                                    external_filemanager_path: '<?php echo $paths['relative']['common_files'];?>/filemanager/',
                                    body_class: 'static_page_container',
                                    autoResizeMaxHeight: 0,
                                    autoResizeMinHeight: 0,
                                    content_css: '../../site/themes/public/<?php echo getProjectSettings('components,current_public_theme');?>/assets/tinymce_content_styles.css?a='+new Date().getTime()
                                });
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label>Lecture Description</label>
                        <textarea class="form-control" id="item_description" name="item_description"><?php echo $pre_data['item_description'] ? processToRender($pre_data['item_description']) : ''?></textarea>
                        <script>
                            init.push(function(){
                                init_tinymce({
                                    selector: '#item_description',
                                    external_filemanager_path: '<?php echo $paths['relative']['common_files'];?>/filemanager/',
                                    body_class: 'static_page_container',
                                    autoResizeMaxHeight: 0,
                                    autoResizeMinHeight: 0,
                                    content_css: '../../site/themes/public/<?php echo getProjectSettings('components,current_public_theme');?>/assets/tinymce_content_styles.css?a='+new Date().getTime()
                                });
                            });
                        </script>
                    </div>
                </div>
                <div class="col-xl-12 col-lg-6">
                    <div class="form-group">
                        <label>Lecture Objectives</label>
                        <textarea class="form-control" id="item_objectives" name="item_objectives"><?php echo $pre_data['item_objectives'] ? processToRender($pre_data['item_objectives']) : ''?></textarea>
                        <script>
                            init.push(function(){
                                init_tinymce({
                                    selector: '#item_objectives',
                                    external_filemanager_path: '<?php echo $paths['relative']['common_files'];?>/filemanager/',
                                    body_class: 'static_page_container',
                                    autoResizeMaxHeight: 0,
                                    autoResizeMinHeight: 0,
                                    content_css: '../../site/themes/public/<?php echo getProjectSettings('components,current_public_theme');?>/assets/tinymce_content_styles.css?a='+new Date().getTime()
                                });
                            });
                        </script>
                    </div>
                    <div class="form-group">
                        <label>Featured Image</label>
                        <div class="panel-body p0">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=thumbImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <img class="" src="" />
                                <input id="thumbImage" name="featured_image" type="hidden" class="form-control" value="<?php echo $pre_data['featured_image'] ? $pre_data['featured_image'] : $_config['default_share_image']?>">
                            </div>
                        </div>             
                    </div>
                    <div class="form-group">
                        <label>Lecture Duration</label>
                        <input class="form-control" type="text" name="item_duration" value="<?php echo $pre_data['item_duration'] ? $pre_data['item_duration'] : ''?>"/>
                    </div>
                    <div class="form-group">
                        <label>Publication Status</label>
                        <select name="publication_status" class="form-control form-group-margin">
                            <option value="drafted" <?php if($pre_data['publication_status'] == 'drafted') {echo 'selected';}?>>Drafted</option>
                            <option value="published" <?php if($pre_data['publication_status'] == 'published') {echo 'selected';}?>>Published</option>
                            <option value="pending-approval" <?php if($pre_data['publication_status'] == 'pending-approval') {echo 'selected';}?>>Pending Approval</option>
                            <option value="approved" <?php if($pre_data['publication_status'] == 'approved') {echo 'selected';}?>>Approved</option>
                            <option value="paused" <?php if($pre_data['publication_status'] == 'paused') {echo 'selected';}?>>Paused</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <?php
                            echo buttonButtonGenerator(array(
                                'action' => 'add',
                                'title' => 'Add Downloadable Content',
                                'icon' => 'icon_add',
                                'text' => 'Add Downloadable Content',
                                'classes' => 'add_gallery_item'
                            ));
                        ?> 
                    </div>
                    <div class="form-group">
                        <label class="db">&nbsp;</label>
                        <?php echo submitButtonGenerator(array(
                            'action' => $edit ? 'update' : 'save',
                            'size' => '',
                            'title' => $edit ? 'Update Lecture' : 'Save Lecture',
                            'icon' => $edit ? 'icon_update' : 'icon_save',
                            'name' => 'submit_content',
                            'value' => 'submit_action',
                            'text' => $edit ? 'Update Lecture' : 'Save Lecture',))
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
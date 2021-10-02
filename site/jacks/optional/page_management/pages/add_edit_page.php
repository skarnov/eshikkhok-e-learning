<?php
global $multilingualFields;

$edit = $_GET['edit'] ? $_GET['edit'] : NULL;

if($edit && !has_permission('edit_page')){
    add_notification('You don\'t have enough permission to edit page.','error');
    header('Location:'.build_url(null,array('action','edit')));
    exit();
    }
elseif(!has_permission('add_page')){
    add_notification('You don\'t have enough permission to add page.','error');
    header('Location:'.build_url(null,array('action')));
    exit();
    }

$content = array();

if($edit){
    $content = $this->get_a_page($edit, array('include_tags', 'include_allowed_content_types'));
    if(!$content){
        add_notification('Page not found for editing.','error');
        header('location:'.build_url(null,array('action','edit')));
        exit();
        }
    }

if($_POST){
	$ret = array();
	
	//sanetizing
	//$data = $devdb->deep_escape($_POST);
	$data = $_POST;
    //pre($data);
	$pageLandingTemplate = $data['page_landing_template'] ? $data['page_landing_template'] : null;

	if($pageLandingTemplate){
        $processedData = $this->templates[$pageLandingTemplate]->preProcess($data);
	    $data = is_null($processedData) ? $data : $processedData;
        }

    $data['page_as_category'] = 'no';

	//modifying
	if(!$data['page_slug']) $data['page_slug'] = $data['page_title'];
	
	$data['page_slug'] = form_modifiers::slug($data['page_slug']);
	
	//validating

    //TODO: Auto validate the page slug, thus auto generate, do not bother the user
	if(in_array($data['page_slug'],$_config['reserved_pages']))
		$ret['error'][] = 'Page slug is reserved, try another.';
	
	$temp = form_validator::required($data['page_title']);
	if($temp !== true)
		$ret['error'][] = 'Page Title '.$temp;
	
	if($data['lock_slug'] == 'false'){
		$temp = form_validator::required($data['page_slug']);
		if($temp !== true)
			$ret['error'][] = 'Page slug '.$temp;
		}
	
	$cond = $edit ? " pk_page_id != '".$edit."'" : '';
	$temp = form_validator::unique($data['page_slug'],'dev_pages','page_slug',$cond);
	if($temp !== true)
        $data['page_slug'] = $temp;
	
	$temp = form_validator::_integer($data['parent_page_id']);
	if($temp !== true)
		$ret['error'][] = 'Parent Page '.$temp;
	
	$temp = form_validator::required($data['page_type']);
	if($temp !== true)
		$ret['error'][] = 'Page type '.$temp;
	
	$temp = form_validator::_in($data['page_type'], array('static','dynamic'));
	if($temp !== true)
		$ret['error'][] = 'Page type '.$temp;
	
	$temp = form_validator::required($data['page_status']);
	if($temp !== true)
		$ret['error'][] = 'Page status '.$temp;
	
	$temp = form_validator::_in($data['page_status'], array('active','inactive'));
	if($temp !== true)
		$ret['error'][] = 'Page status '.$temp;
	
	/*$temp = form_validator::required($data['is_locked']);
	if($temp !== true)
		$ret['error'][] = 'is_locked '.$temp;
	
	$temp = form_validator::_in($data['is_locked'], array('yes','no'));
	if($temp !== true)
		$ret['error'][] = 'is_locked '.$temp;*/

	if(!$ret['error']){
        /*foreach($data as $column=>$value){
            if (in_array($column, $multilingualFields['dev_pages']) !== false){
                $data[$column] = processToStore($content[$column], $value);
                }
            }
		$insert_data = array(
			'page_slug' => $data['page_slug'],
			'page_title' => $data['page_title'],
			'page_description' => $data['page_description'],
			'parent_page_id' => $data['parent_page_id'],
			'page_type' => $data['page_type'],
			'page_as_category' => $data['page_as_category'],
			'page_thumbnail' => $data['page_thumbnail'],
			'page_status' => $data['page_status'],
			'page_landing_template' => $data['page_landing_template'],
			'page_meta_keyword' => $data['page_meta_keyword'],
			'page_meta_description' => $data['page_meta_description'],
            'page_excerpt' => $data['page_excerpt'] ? strip_tags($data['page_excerpt']) : mb_substr(strip_tags($data['page_description']),0,250),
			'is_locked' => 'no',//$data['is_locked'],
			);
		if($edit){
			$insert_data['modified_at'] = date('Y-m-d H:i:s');
			$insert_data['modified_by'] = $_config['user']['pk_user_id'];
			
			$ret = $devdb->insert_update('dev_pages',$insert_data," pk_page_id = '".$edit."'");
			$page_id = $edit;
			}
		else{
			$insert_data['created_at'] = date('Y-m-d H:i:s');
			$insert_data['created_by'] = $_config['user']['pk_user_id'];
			$insert_data['modified_at'] = date('Y-m-d H:i:s');
			$insert_data['modified_by'] = $_config['user']['pk_user_id'];
			
			$ret = $devdb->insert_update('dev_pages',$insert_data);
			$page_id = $ret['success'];
			}*/
        if($edit) $data['pk_page_id'] = $edit;

        $data['page_extras'] = serialize(array());
        if($data['extras']){
            $data['page_extras'] = serialize($data['extras']);
            unset($data['extras']);
            }
        //pre($data);
        $ret = $this->add_edit_page($data, $content);
        $page_id = $edit ? $edit : ($ret['success'] ? $ret['success'] : null);
		}
	
	if($ret['error']){
		foreach($ret['error'] as $e){
			add_notification($e,'error');
			}
		$content = $_POST;
		if(!$content['tags']) $content['tags'] = array();
		}
	else{
	    //processing allowed content types
        $sql = "DELETE FROM dev_page_content_types WHERE fk_page_id = '".$page_id."' ";
        $deleted = $devdb->query($sql);
        if($data['allowed_content_types']){
            foreach($data['allowed_content_types'] as $i=>$v){
                $insert_data = array(
                    'fk_page_id' => $page_id,
                    'fk_content_type_id' => $v,
                    );
                $insertNew = $devdb->insert_update('dev_page_content_types', $insert_data);
                }
            }
        //processing tags
        $tagger = jack_obj('dev_tag_management');
        if($tagger) $tagger->attach_tags($page_id, 'page', $_POST['tags']);

	    $this->reCachePage($page_id);
	    //pre(hookAction::$actions);
        doAction('after_page_processed');
        if($edit){
            add_notification('The page has been updated','success');
            user_activity::add_activity('The page (ID: '.$edit.') has been update','success', 'update');
            }
        else{
            add_notification('The page has been created','success');
            user_activity::add_activity('The page (ID: '.$ret['success'].') has been created','success', 'create');
            }

		header('location:'.current_url());
		exit();
		}
	}

$refillTags = array();
$isSystemPage = false;
$systemPageClass = '';
$allowedContentTypes = array();

if($content){
    if($multilingualFields['dev_pages']){
        foreach($content as $index=>$value){
            if(in_array($index, $multilingualFields['dev_pages']) !== false)
                $content[$index] = processToRender($content[$index]);
            }
        }
    if($content['tags']){
        foreach($content['tags'] as $i=>$v){
            $refillTags[] = array('id' => $v['pk_tag_id'], 'label' => $v['tag_title'], 'value' => $v['tag_title']);
            }
        }

    if($content['is_locked'] == 'yes'){
        $systemPageClass = 'dn';
        $isSystemPage = true;
        }

    if($content['allowed_content_types']){
        foreach($content['allowed_content_types'] as $i=>$v){
            $allowedContentTypes[] = $v['fk_content_type_id'];
            }
        }
    }
//pre($content);
doAction('render_start');
?>
<div class="page-header">
    <h1><?php echo $edit ? 'Update Page: '.processToRender($content['page_title']) : 'New Page'?></h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => build_url(null, array('action', 'edit')),
                'action' => 'list',
                'icon' => 'icon_list',
                'text' => 'All Pages',
                'title' => 'All Pages',
                'size' => 'sm',
            ));
            ?>
            <?php if(has_permission('add_page')): ?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_page')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New Page',
                    'title' => 'Create New Page',
                    'size' => 'sm',
                ));
                ?>
            <?php endif; ?>

        </div>
    </div>
</div>
<script type="text/javascript">
    function processForm(){
        var currentTemplate = $('.page_landing_template:checked').val();
        $('.page_template_options .each_page_template_options:not(#page_template_option_'+currentTemplate+')').remove();
        }
</script>
<div class="panel">
    <form onsubmit="return processForm();" name="widget_pos_add_edit" method="post" action="" enctype="multipart/form-data">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-8">
                    <div class="form-group">
                        <label>Title</label>
                        <input required class="form-control" type="text" name="page_title" value="<?php echo $content['page_title'] ? $content['page_title'] : ''?>" />
                    </div>
                    <div class="form-group">
                        <label>Sub Title</label>
                        <input class="form-control" type="text" name="page_sub_title" value="<?php echo $content['page_sub_title'] ? $content['page_sub_title'] : ''?>" />
                    </div>
                    <div class="form-group dn <?php echo $systemPageClass; ?>">
                        <label>Slug</label>
                        <input class="form-control" type="text" name="page_slug" value="<?php echo $content['page_slug'] ? $content['page_slug'] : ''?>" />
                        <p class="help-block">Leave blank, slug will be generated auto.</p>
                    </div>
                    <?php if(!$isSystemPage):?>
                        <div class="panel page_template_selection_panel <?php echo $systemPageClass; ?>">
                            <div class="panel-heading"><span class="panel-title">Landing Template</span></div>
                            <div class="panel-body">
                                <div class="row">
                                    <?php
                                    $first = true;
                                    foreach($this->templates as $i=>$v){
                                        if($v->disableSelection) continue;
                                        $checked = $content && $content['page_landing_template'] == $v->name ? 'checked' : ($first ? 'checked' : '');
                                        ?>
                                        <div class="col-sm-3">
                                            <label class="radio">
                                                <input type="radio" class="px page_landing_template" name="page_landing_template" value="<?php echo $v->name ?>" <?php echo $checked ?>/>
                                                <span class="lbl"><?php echo $v->label ?></span>
                                            </label>
                                        </div>
                                        <?php
                                        $first = false;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <input type="radio" checked class="page_landing_template dn" name="page_landing_template" value="<?php echo $content['page_landing_template']; ?>" />
                    <?php endif ?>
                    <div class="page_template_options">
                        <?php
                        /*foreach($_config['page_templates'] as $i=>$v){
                            $func = $i.'_options';
                            if(!function_exists($func)) continue;
                            */?><!--
                            <div id="page_template_option_<?php /*echo $i*/?>" style="display: none" class="panel panel-info each_page_template_options">
                                <div class="panel-heading">
                                    <span class="panel-title"><?php /*echo $v['title'] */?> - Options</span>
                                </div>
                                <div class="panel-body">
                                    <?php /*$func($content); */?>
                                </div>
                            </div>
                            --><?php
/*                        }*/
                        ?>
                    </div>
                    <div class="form-group">
                        <label>Page Body Content</label>
                        <textarea class="form-control" id="page_description" name="page_description"><?php echo $content['page_description'] ? $content['page_description'] : ''?></textarea>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel">
                        <div class="panel-heading"><span class="panel-title">Featured Image</span></div>
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=thumbImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <img class="" src="<?php echo $edit && $content['page_thumbnail'] ? get_image($content['page_thumbnail'],'100x100x1') : get_image($_config['default_share_image'],'100x100x1')?>" />
                                <input id="thumbImage" name="page_thumbnail" type="hidden" class="form-control" value="<?php echo $content ? $content['page_thumbnail'] : $_config['default_share_image']?>">
                            </div>
                        </div>
                        <div class="panel-footer">
                            <a href="javascript:" class="previewFeaturedImageAll">Preview This Image for Social Medias</a>
                        </div>
                    </div>
                    <?php if(getProjectSettings('features,page_square_picture', 1)): ?>
                    <div class="panel">
                        <div class="panel-heading"><span class="panel-title">Square Featured Image</span></div>
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=squareThumbImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <img class="" src="<?php echo $edit && $content['page_square_thumbnail'] ? get_image($content['page_square_thumbnail'],'100x100x1') : get_image($_config['default_share_image'],'100x100x1')?>" />
                                <input id="squareThumbImage" name="page_square_thumbnail" type="hidden" class="form-control" value="<?php echo $content ? $content['page_square_thumbnail'] : $_config['default_share_image']?>">
                            </div>
                        </div>
                        <div class="panel-footer">

                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(getProjectSettings('features,page_wide_picture', 1)): ?>
                    <div class="panel">
                        <div class="panel-heading"><span class="panel-title">Wide Featured Image</span></div>
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=wideThumbImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <img class="" src="<?php echo $edit && $content['page_wide_thumbnail'] ? get_image($content['page_wide_thumbnail'],'100x100x1') : get_image($_config['default_share_image'],'100x100x1')?>" />
                                <input id="wideThumbImage" name="page_wide_thumbnail" type="hidden" class="form-control" value="<?php echo $content ? $content['page_wide_thumbnail'] : $_config['default_share_image']?>">
                            </div>
                        </div>
                        <div class="panel-footer">

                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group dn <?php echo $systemPageClass; ?>">
                        <label>Parent Page</label>
                        <select class="form-control" name="parent_page_id">
                            <option value="">Select One</option>
                            <?php
                            $parentPages = get_parent_pages_only();
                            foreach($parentPages as $i=>$page){
                                $selected = $content['parent_page_id'] && ($content['parent_page_id'] == $page['pk_page_id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $page['pk_page_id']?>" <?php echo $selected?>><?php echo $page['page_title']?></option><?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="dn form-group <?php echo $systemPageClass; ?>">
                        <label>Page Type</label>
                        <input type="hidden" name="page_type" value="static" />
                        <!--select class="form-control" name="page_type">
                            <option value="dynamic" <?php echo $content['page_type'] == 'dynamic' ? 'selected' : ''?>>Dynamic</option>
                            <option value="static" <?php echo $content['page_type'] == 'static' ? 'selected' : ''?>>Static</option>
                        </select-->
                    </div>
                    <div class="form-group dn <?php echo $systemPageClass; ?>">
                        <label>Page Status</label>
                        <label class="radio-inline">
                            <input type="checkbox" class="px form-control" name="page_status" value="active" <?php echo $content ? ($content['page_status'] == 'active' ? 'checked' : '') : 'checked' ?> />
                            <span class="lbl">Published</span>
                        </label>
                        <label class="radio-inline">
                            <input type="checkbox" class="px form-control" name="page_status" value="inactive" <?php echo $content ? ($content['page_status'] == 'inactive' ? 'checked' : '') : '' ?> />
                            <span class="lbl">Unpublished</span>
                        </label>
                    </div>
                    <div class="panel">
                        <div class="panel-heading"><span class="panel-title">Accepted Content Types</span></div>
                        <div class="panel-body">
                            <?php
                            foreach($_config['content_types'] as $i=>$content_type){
                                if($content_type['exceptional']) continue;
                                $selected = in_array($i, $allowedContentTypes) !== false ? 'checked' : '';
                                ?>
                                <label class="checkbox">
                                    <input type="checkbox" class="px" name="allowed_content_types[]" value="<?php echo $i?>" <?php echo $selected; ?> />
                                    <span class="lbl"><?php echo processToRender($content_type['title'])?></span>
                                </label>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="panel-footer">
                            This change will only  take effect on new content or when you update a content
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-heading">
                            <span class="panel-title">SEO Features</span>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>Tags</label>
                                <div id="tag_autocomplete"></div>
                                <script type="text/javascript">
                                    init.push(function(){
                                        new set_autosuggest({
                                            container: '#tag_autocomplete',
                                            submit_labels: false,
                                            ajax_page: _root_path_+'/api/dev_tag_management/get_tags_autocomplete',
                                            single: false,
                                            parameters: {'tag_group' : 'tags'},
                                            multilingual: true,
                                            input_field: '#input_tag',
                                            field_name: 'tags',
                                            add_what: 'Tag',
                                            add_new: true,
                                            url_for_add: _root_path_+'/api/dev_tag_management/add_edit_tags',
                                            field_for_add: 'tag_title',
                                            data_for_add: {tag_group: 'tags'},
                                            existing_items: <?php echo to_json_object($refillTags);?>,
                                        });
                                    });
                                </script>
                            </div>
                            <div class="form-group">
                                <label>Meta Keywords</label>
                                <textarea class="form-control" name="page_meta_keyword"><?php echo $content['page_meta_keyword'] ? $content['page_meta_keyword'] : ''; ?></textarea>
                                <p class="help-block">Separate keywords by Comma (,)</p>
                            </div>
                            <div class="form-group">
                                <label>Meta Descriptions</label>
                                <textarea class="form-control" name="page_meta_description"><?php echo $content['page_meta_description'] ? $content['page_meta_description'] : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Excerpt</label>
                                <textarea class="form-control" name="page_excerpt"><?php echo $content['page_excerpt'] ? $content['page_excerpt'] : ''; ?></textarea>
                                <p class="help-block">Leave blank to use page description</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="panel-footer tar">
            <?php echo submitButtonGenerator(array(
                'action' => $edit ? 'update' : 'save',
                'size' => '',
                'title' => $edit ? 'Update Page' : 'Save Page',
                'icon' => $edit ? 'icon_update' : 'icon_save',
                'name' => 'submit_btn',
                'value' => 'SUBMIT',
                'text' => $edit ? 'Update Page' : 'Save Page',)) ?>
        </div>
    </form>
</div>

<script type="text/javascript">
    init.push(function () {
        $('.page_landing_template').change(function(){
            if($(this).is(':checked')){
                var template_id = $(this).val();
                $('.each_page_template_options').slideUp();
                if(!$('#page_template_option_'+template_id).length){
                    var page_id = <?php echo $content ? $content['pk_page_id'] : '0';?>;
                    basicAjaxCall({
                        beforeSend: function(){
                            $('.page_template_selection_panel input').attr('disabled', true);
                            },
                        complete: function(){
                            $('.page_template_selection_panel input').attr('disabled', false);
                            },
                        url: _root_path_+'/api/dev_page_management/get_page_template_options',
                        data: {template_id: template_id, page_id: page_id},
                        success: function(ret){
                            if(ret.success){
                                $('.page_template_options').append(ret.success);
                                $('#page_template_option_'+template_id+' textarea').autosize().css('resize', 'none');
                                }
                            },
                        });
                    }
                else $('#page_template_option_'+template_id).slideDown();
                }
            }).change();
        init_tinymce({
            selector: '#page_description',
            external_filemanager_path: '<?php echo $paths['relative']['common_files'];?>/filemanager/',
            autoResizeMaxHeight: 450,
            autoResizeMinHeight: 300,
            });
        });
</script>
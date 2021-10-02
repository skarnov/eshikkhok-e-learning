<?php
global $multilingualFields;

$pageManager = jack_obj('dev_page_management');
$content_types = $_config['content_types'];

$edit = $_GET['edit'] ? $_GET['edit'] : NULL;
$pre_content = null;

if($edit){
    $args = array(
        'content_id' => $edit,
        'single' => true,
        'status' => array('pending','draft','published'),
        'ignore_parent' => true,
        'include_tag' => true,
        'include_category' => true,
        'include_child' => true,
        'include_meta' => true,
        'include_file' => true,
        'skipCache' => true,
        );
    $pre_content = $this->get_contents($args);

    if(!$pre_content){
        add_notification('Content not found for editing.','error');
        header('location:'.$myUrl);
        exit();
        }
    }

if($edit && !has_permission('edit_contents')){
    add_notification('You don\'t have enough permission to edit contents.','error');
    header('Location:'.build_url(NULL,array('edit')));
    exit();
    }
elseif(!has_permission('add_contents')){
    add_notification('You don\'t have enough permission to add contents.','error');
    header('Location:'.build_url(NULL,array('action')));
    exit();
    }

if($_POST){
	$ret = array();

	$data = $_POST;

	$post_type = $data['fk_content_type_id'];

    if($this->getCTypeSetting('title','useEditorForTitle',$post_type))
        $data['content_title'] = form_modifiers::html_purify($data['content_title']);
    else $data['content_title'] = form_modifiers::sanitize_title($data['content_title']);

    $data['content_meta_keyword'] = form_modifiers::sanitize_title($data['content_meta_keyword']);
    $data['content_description'] = form_modifiers::html_purify($data['content_description']);
    $data['content_excerpt'] = form_modifiers::sanitize_title($data['content_excerpt'] ? $data['content_excerpt'] : mb_substr(form_modifiers::content_to_excerpt($data['content_description']),0,250));
    $data['content_meta_description'] = form_modifiers::sanitize_title($data['content_meta_description'] ? $data['content_meta_description'] : mb_substr(form_modifiers::content_to_excerpt($data['content_description']),0,250));
    $data['content_status'] = !isset($data['content_status']) ? 'draft' : $data['content_status'];
    $data['content_thumbnail'] = !strlen($data['content_thumbnail']) ? $_config['default_share_image'] : $data['content_thumbnail'];

    if(defined('_CONTENT_CUSTOM_AUTHOR_')){
        $data['content_author'] = isset($_POST['content_author']) && $_POST['content_author'] ? $_POST['content_author'] : '';
        }

    //Pre Processing Data
    $preProcessedData = $this->uCTypes[$post_type]->preProcess($data);
    if(!is_null($preProcessedData)) $data = $preProcessedData;

	//modifying
	if(!$data['content_slug']) $data['content_slug'] = $data['content_title'];
	
	$data['content_slug'] = form_modifiers::slug($data['content_slug']);
	
	//validating
	$temp = form_validator::required($data['content_title']);
	if($temp !== true)
		$ret['error'][] = 'Content Title '.$temp;

	$temp = form_validator::required($data['content_slug']);
	if($temp !== true)
		$ret['error'][] = 'Content Slug '.$temp;

	/*$temp = form_validator::required($data['fk_page_id']);
	if($temp !== true)
		$ret['error'][] = 'Category '.$temp;*/

	$temp = form_validator::required($data['fk_content_type_id']);
	if($temp !== true)
		$ret['error'][] = 'Content Type '.$temp;

    $temp = form_validator::required($data['content_thumbnail']);
    if($temp !== true)
        $ret['error'][] = 'Featured Image '.$temp;

	$temp = form_validator::required($data['content_published_time']);
	if($temp !== true)
		$ret['error'][] = 'Publish Time '.$temp;

	$temp = form_validator::_in($data['content_status'], array('published','draft'));
	if($temp !== true)
		$ret['error'][] = 'Status '.$temp;

	$cond = $edit ? " pk_content_id != '".$edit."'" : '';
	$temp = form_validator::unique($data['content_slug'],'dev_contents','content_slug',$cond);
	if($temp !== true)
        $data['content_slug'] = $temp;

	/*$temp = form_validator::_in($data['content_featured_news'], array('yes','no'));
	if($temp !== true)
		$ret['error'][] = 'Featured News '.$temp;
    */
	if(!$ret['error']){
        foreach($data as $column=>$value){
            if (in_array($column, $multilingualFields['dev_contents']) !== false){
                $data[$column] = processToStore($pre_content[$column], $value);
                }
            }

		$insert_data = array(
			'content_slug' => $data['content_slug'],
			'content_title' => $data['content_title'],
			'content_sub_title' => $data['content_sub_title'],
			'content_description' => $data['content_description'],
			'fk_page_id' => $data['fk_page_id'] ? $data['fk_page_id'] : 0,
			'fk_content_id' => $data['fk_content_id'] ? $data['fk_content_id'] : 0,
			'fk_content_type_id' => $data['fk_content_type_id'],
            'content_thumbnail' => strlen($data['content_thumbnail']) ? $username.str_replace($username,'',$data['content_thumbnail']) : '',
            'content_square_thumbnail' => strlen($data['content_square_thumbnail']) ? $username.str_replace($username,'',$data['content_square_thumbnail']) : '',
            'content_wide_thumbnail' => strlen($data['content_wide_thumbnail']) ? $username.str_replace($username,'',$data['content_wide_thumbnail']) : '',
			'content_status' => $data['content_status'],
			'content_published_time' => $data['content_published_time'] && _checkDateTime($data['content_published_time'], 'd-m-Y') ? datetime_to_db($data['content_published_time']) : date('Y-m-d H:i:s'),
			'content_featured_news' => $data['content_featured_news'] ? $data['content_featured_news'] : 'no',
			'content_hot_news' => $data['content_hot_news'] ? $data['content_hot_news'] : 'no',
			'content_meta_keyword' => $data['content_meta_keyword'],
			'content_meta_description' => $data['content_meta_description'],
            'content_excerpt' => $data['content_excerpt'],
            'content_allow_comment' => $data['content_allow_comment'] ? 'yes' : 'no',
            'content_hide_comment' => $data['content_hide_comment'] ? 'yes' : 'no',
			);

        if(defined('_CONTENT_CUSTOM_AUTHOR_')){
            $insert_data['content_author'] = $data['content_author'];
            }
		if($edit){
			$insert_data['modified_at'] = date('Y-m-d H:i:s');
			$insert_data['modified_by'] = $_config['user']['pk_user_id'];
			
			$ret = $devdb->insert_update('dev_contents',$insert_data," pk_content_id = '".$edit."'");
			$content_id = $edit;
			}
		else{
			$insert_data['created_at'] = date('Y-m-d H:i:s');
			$insert_data['created_by'] = $_config['user']['pk_user_id'];
			$insert_data['modified_at'] = date('Y-m-d H:i:s');
			$insert_data['modified_by'] = $_config['user']['pk_user_id'];
			
			$ret = $devdb->insert_update('dev_contents',$insert_data);
			$content_id = $ret['success'];
			}
		}
	
	if($ret['error']){
		foreach($ret['error'] as $e){
			add_notification($e,'error');
			}
		$content = $_POST;
		if(!$content['tags']) $content['tags'] = array();
        if(isset($content['meta'])){
            $temp_meta = array();
            foreach($content['meta'] as $i=>$v){
                $temp_meta[$i] = array(
                    'meta_value' => $v
                    );
                }
            $content['meta'] = $temp_meta;
            }
		}
	else {
        //processing tags
        $tagger = jack_obj('dev_tag_management');
        if($tagger) $tagger->attach_tags($content_id, 'content', $_POST['tags'], 'tag');

        //processing categories
        $tagger = jack_obj('dev_tag_management');
        if($tagger) $tagger->attach_tags($content_id, 'content', $_POST['category'], 'category');

        //Post Processing
        $postProcessedData = $this->uCTypes[$post_type]->postProcess($content_id, $data);
        if(!is_null($postProcessedData)) $data = $postProcessedData;

        //processing files
        $fileData = array(
            'content_id' => $content_id,
            'content_type' => 'content',
            'files' => isset($_POST['files']) ? $_POST['files'] : null
            );
        $this->processFiles($fileData);

        $args = array(
            'post' => $data['meta'],
            'content_id' => $content_id,
            );

        $ret = $this->put_content_meta($args);

        if (!$ret['error']) {
            $this->reCacheContent($content_id);
            doAction('after_content_processed');
            if($edit){
                add_notification('The content has been updated.', 'success');
                user_activity::add_activity('The content (ID: '.$content_id.') has been updated', 'success', 'update');
                }
            else{
                add_notification('The content has been created', 'success');
                user_activity::add_activity('The content (ID: '.$content_id.') has been created.', 'success', 'create');
                }

            header('location:' . $_SERVER['REQUEST_URI']);
            exit();
            }
		}
	}

$content = $pre_content;
$refillTags = array();
$refillCategories = array();

if($content){
    if($multilingualFields['dev_contents']){
        foreach($content as $index=>$value){
            if(in_array($index, $multilingualFields['dev_contents']) !== false)
                $content[$index] = processToRender($content[$index]);
            }
        }

    if($content['tags']){
        foreach($content['tags'] as $i=>$v){
            $refillTags[] = array('id' => $v['pk_tag_id'], 'label' => $v['tag_title'], 'value' => $v['tag_title']);
            }
        }

    if($content['categories']){
        foreach($content['categories'] as $i=>$v){
            $refillCategories[] = array('id' => $v['pk_tag_id'], 'label' => $v['tag_title'], 'value' => $v['tag_title']);
            }
        }
    }

$theContentTypeID = $_GET['content_type'] ? $_GET['content_type'] : ($content ? $content['fk_content_type_id'] : null);
$theContentType = $theContentTypeID ? $content_types[$theContentTypeID] : array();

if(!$theContentTypeID){
    foreach($_config['content_types'] as $i=>$v){
        header('Location: '.build_url(array('content_type' => $i)));
        exit();
        }
    }
$pages = array();
$allowedPages = $pageManager->get_pages_by_content_type($theContentTypeID);
//pre($allowedPages);
doAction('render_start');
?>
<div class="page-header">
    <h1><?php echo $edit ? 'Update '.$theContentType['title'].': '.processToRender($content['content_title']) : 'New '.$theContentType['title']?></h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => build_url(null,array('action','edit')),
                'action' => 'list',
                'icon' => 'icon_list',
                'text' => 'All '.$theContentType['title'],
                'title' => 'Manage All '.$theContentType['title'],
                'size' => 'sm',
                ));
            ?>
            <?php if(has_permission('add_contents')):?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_contents'),array('edit')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New '.$theContentType['title'],
                    'title' => 'New '.$theContentType['title'],
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
                <div class="col-xl-8 col-lg-12">
                    <?php
                    if($this->getCTypeSetting('title', 'useEditorForTitle', $theContentTypeID)){
                        ?>
                        <div class="panel <?php echo $this->getCTypeSetting('title','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                            <div class="panel-heading"><span class="panel-title"><?php echo $this->getCTypeSetting('title','label',$theContentTypeID) ? $this->getCTypeSetting('title','label',$theContentTypeID) : 'Title'; ?></span></div>
                            <div class="panel-body p0">
                                <textarea class="form-control" id="content_title" name="content_title"><?php echo $content ? $content['content_title_html'] : ''?></textarea>
                            </div>
                        </div>

                        <script type="text/javascript">
                            init.push(function(){
                                init_tinymce({
                                    selector: '#content_title',
                                    toolbar1: ' bold italic | forecolor | backcolor | fontsizeselect',
                                    toolbar2: false,
                                    height: 50,
                                    external_filemanager_path: '<?php echo _path('common_files');?>/filemanager/'
                                });
                            });
                        </script>
                    <?php
                    }
                    else{
                    ?>
                        <div class="form-group <?php echo $this->getCTypeSetting('title','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                            <label><?php echo $this->getCTypeSetting('title','label',$theContentTypeID) ? $this->getCTypeSetting('title','label',$theContentTypeID) : 'Title'; ?></label>
                            <input class="form-control" type="text" name="content_title" id="content_title" value="<?php echo $content ? form_modifiers::sanitize_input($content['content_title']) : ''?>" required/>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="form-group <?php echo $this->getCTypeSetting('subtitle','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                        <label><?php echo $this->getCTypeSetting('subtitle','label',$theContentTypeID) ? $this->getCTypeSetting('subtitle','label',$theContentTypeID) : 'Sub Title'; ?></label>
                        <input class="form-control" type="text" name="content_sub_title" id="content_sub_title" value="<?php echo $content ? $content['content_sub_title'] : ''?>"/>
                    </div>
                    <?php
                    if(defined('_CONTENT_CUSTOM_AUTHOR_')){
                        ?>
                        <div class="form-group">
                            <label>Author</label>
                            <input class="form-control" type="text" name="content_author" id="content_author" placeholder="<?php echo _CONTENT_CUSTOM_AUTHOR_?>" value="<?php echo $content ? $content['content_author'] : ''?>"/>
                        </div>
                        <?php
                    }
                    ?>
                    <input type="hidden" name="content_slug" value="<?php echo $content ? $content['content_slug'] : ''?>"/>
                    <input type="radio" class="px fk_content_type_id dn" name="fk_content_type_id" data-slug="<?php echo $theContentTypeID ?>" value="<?php echo $theContentTypeID?>" checked/>
                    <?php
                    ob_start();
                    $this->uCTypes[$theContentTypeID]->get_fields($content);
                    $optionalFields = ob_get_clean();
                    if($optionalFields){
                        ?>
                        <div class="optional_panel <?php echo $theContentTypeID; ?>_type" >
                            <?php echo $optionalFields;?>
                        </div>
                        <?php
                        }
                    ?>
                    <div class="form-group <?php echo $this->getCTypeSetting('content','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                        <label><?php echo $this->getCTypeSetting('content','label',$theContentTypeID) ? $this->getCTypeSetting('content','label',$theContentTypeID) : 'Content'; ?></label>
                        <textarea class="form-control" id="content_description" name="content_description"><?php echo $content ? $content['content_description'] : ''?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="panel <?php echo $this->getCTypeSetting('featuredImage','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                                <div class="panel-heading"><span class="panel-title"><?php echo $this->getCTypeSetting('featuredImage','label',$theContentTypeID) ? $this->getCTypeSetting('featuredImage','label',$theContentTypeID) : 'Featured Image (1200x630)'; ?></span></div>
                                <div class="panel-body p0">
                                    <div class="image_upload_container controlVisible">
                                        <div class="controlBtnContainer">
                                            <div class="controlBtn">
                                                <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=thumbImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                            </div>
                                        </div>
                                        <img class="" src="<?php echo $edit && $content['content_thumbnail'] ? get_image($content['content_thumbnail'],'100x100x1') : get_image($_config['default_share_image'],'100x100x1')?>" />
                                        <input id="thumbImage" name="content_thumbnail" type="hidden" class="form-control" value="<?php echo $content ? $content['content_thumbnail'] : $_config['default_share_image']?>">
                                    </div>
                                </div>
                                <div class="panel-footer">
                                    <a href="javascript:" class="previewFeaturedImageAll">Preview This Image for Social Medias</a>
                                    <!--<a href="javascript:" class="btn btn-xs previewFeaturedImage" data-social="fb"><i class="fa fa-facebook"></i></a>
                                    <a href="javascript:" class="btn btn-xs previewFeaturedImage" data-social="li"><i class="fa fa-linkedin"></i></a>
                                    <a href="javascript:" class="btn btn-xs previewFeaturedImage" data-social="gp"><i class="fa fa-google-plus"></i></a>
                                    <a href="javascript:" class="btn btn-xs previewFeaturedImage" data-social="tw"><i class="fa fa-twitter"></i></a>
                                    <a href="javascript:" class="btn btn-xs previewFeaturedImage" data-social="pi"><i class="fa fa-pinterest"></i></a>-->
                                </div>
                            </div>
                        </div>
                        <?php if(getProjectSettings('features,content_square_picture', 1)): ?>
                        <div class="col-sm-4">
                            <div class="panel <?php echo $this->getCTypeSetting('squareFeaturedImage','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                                <div class="panel-heading"><span class="panel-title"><?php echo $this->getCTypeSetting('squareFeaturedImage','label',$theContentTypeID) ? $this->getCTypeSetting('squareFeaturedImage','label',$theContentTypeID) : 'Featured Image (Square)'; ?></span></div>
                                <div class="panel-body p0">
                                    <div class="image_upload_container controlVisible">
                                        <div class="controlBtnContainer">
                                            <div class="controlBtn">
                                                <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=squareThumbImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                            </div>
                                        </div>
                                        <img class="" src="<?php echo $edit && $content['content_square_thumbnail'] ? get_image($content['content_square_thumbnail'],'100x100x1') : get_image($_config['default_share_image'],'100x100x1')?>" />
                                        <input id="squareThumbImage" name="content_square_thumbnail" type="hidden" class="form-control" value="<?php echo $content ? $content['content_square_thumbnail'] : ''?>">
                                    </div>
                                </div>
                                <div class="panel-footer">

                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if(getProjectSettings('features,content_wide_picture', 1)): ?>
                        <div class="col-sm-4">
                            <div class="panel <?php echo $this->getCTypeSetting('wideFeaturedImage','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                                <div class="panel-heading"><span class="panel-title"><?php echo $this->getCTypeSetting('wideFeaturedImage','label',$theContentTypeID) ? $this->getCTypeSetting('wideFeaturedImage','label',$theContentTypeID) : 'Featured Image (Wide)'; ?></span></div>
                                <div class="panel-body p0">
                                    <div class="image_upload_container controlVisible">
                                        <div class="controlBtnContainer">
                                            <div class="controlBtn">
                                                <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=wideThumbImage&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                            </div>
                                        </div>
                                        <img class="" src="<?php echo $edit && $content['content_wide_thumbnail'] ? get_image($content['content_wide_thumbnail'],'100x100x1') : get_image($_config['default_share_image'],'100x100x1')?>" />
                                        <input id="wideThumbImage" name="content_wide_thumbnail" type="hidden" class="form-control" value="<?php echo $content ? $content['content_wide_thumbnail'] : ''?>">
                                    </div>
                                </div>
                                <div class="panel-footer"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php
                    if(count($allowedPages) == 1){
                        ?>
                        <input type="hidden" name="fk_page_id" value="<?php echo $allowedPages[0]['fk_page_id']; ?>" />
                        <?php
                    }
                    elseif(count($allowedPages) > 1){
                        ?>
                        <div class="form-group">
                            <label>Page</label>
                            <select class="form-control" name="fk_page_id" required>
                                <option value="">Select One</option>
                                <?php
                                foreach($allowedPages as $i=>$v){
                                    $thePage = $pageManager->get_a_page($v['fk_page_id'],array('tiny' => true));
                                    $selected = $content && $content['fk_page_id'] == $thePage['pk_page_id'] ? 'selected' : '';
                                    echo '<option value="'.$thePage['pk_page_id'].'" '.$selected.'>'.processToRender($thePage['page_title']).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="form-group dn switcher_holder">
                        <div class="fl switchers_square mr10">
                            <input type="checkbox" data-class="switcher-success" name="content_allow_comment" value="yes" <?php echo $edit ? ($content['content_allow_comment'] == 'yes' ? 'checked' : '') : 'checked'?>>
                        </div>
                        <label>Allow Comments</label>
                    </div>
                    <div class="form-group dn switcher_holder">
                        <div class="fl switchers_square mr10">
                            <input type="checkbox" data-class="switcher-success" name="content_hide_comment" value="yes" <?php echo $edit ? ($content['content_hide_comment'] == 'yes' ? 'checked' : '') : ''?>>
                        </div>
                        <label>Hide Comments</label>
                    </div>
                    <div class="form-group <?php echo $this->getCTypeSetting('category','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                        <label><?php echo $this->getCTypeSetting('category','label',$theContentTypeID) ? $this->getCTypeSetting('category','label',$theContentTypeID) : 'Category'; ?>&nbsp;<img class="autocomplete_loading" src="<?php echo common_files().'/css/images/working.GIF'; ?>"></label>
                        <div id="category_autocomplete"></div>
                        <script type="text/javascript">
                            init.push(function(){
                                new set_autosuggest({
                                    minLength: 0,
                                    searchOnClick: true,
                                    container: '#category_autocomplete',
                                    submit_labels: false,
                                    ajax_page: _root_path_+'/api/dev_tag_management/get_tags_autocomplete',
                                    single: false,
                                    parameters: {'tag_group' : 'content_category'},
                                    multilingual: true,
                                    input_field: '#input_category',
                                    field_name: 'category',
                                    add_what: 'Category',
                                    add_new: true,
                                    url_for_add: _root_path_+'/api/dev_tag_management/add_edit_tags',
                                    field_for_add: 'tag_title',
                                    data_for_add: {tag_group: 'content_category'},
                                    existing_items: <?php echo to_json_object($refillCategories);?>,
                                });
                            });
                        </script>
                    </div>
                    <div class="panel <?php echo $this->getCTypeSetting('seoFeatures','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                        <div class="panel-heading">
                            <span class="panel-title"><?php echo $this->getCTypeSetting('seoFeatures','label',$theContentTypeID) ? $this->getCTypeSetting('seoFeatures','label',$theContentTypeID) : 'SEO Features'; ?></span>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>Tags&nbsp;<img class="autocomplete_loading" src="<?php echo common_files().'/css/images/working.GIF'; ?>"></label>
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
                                <label>Content Meta Keywords</label>
                                <textarea class="form-control" name="content_meta_keyword"><?php echo $content['content_meta_keyword'] ? $content['content_meta_keyword'] : ''; ?></textarea>
                                <p class="help-block">Separate keywords by Comma (,)</p>
                            </div>
                            <div class="form-group">
                                <label>Content Meta Descriptions</label>
                                <textarea class="form-control" name="content_meta_description"><?php echo $content['content_meta_description'] ? $content['content_meta_description'] : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Content Excerpt</label>
                                <textarea class="form-control" name="content_excerpt"><?php echo $content['content_excerpt'] ? $content['content_excerpt'] : ''; ?></textarea>
                                <p class="help-block">Leave blank to use content description</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-12">
                    <div class="row">
                        <div class="  col-xl-12 col-lg-3">
                            <div class="form-group <?php echo $this->getCTypeSetting('publishTime','hide',$theContentTypeID) == true ? 'dn' : ''; ?>">
                                <label class=""><?php echo $this->getCTypeSetting('publishTime','label',$theContentTypeID) ? $this->getCTypeSetting('publishTime','label',$theContentTypeID) : 'Publish Time'; ?></label>
                                <div class="datetimepicker_holder">
                                    <input type="text" class="form-control" id="content_published_time" name="content_published_time" value="<?php echo $content ? datetime_to_user($content['content_published_time']) : date('d-m-Y H:i'); ?>" />
                                </div>
                                <script type="text/javascript">
                                    init.push(function(){
                                        _datetimepicker('content_published_time');
                                    });
                                </script>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-9 tar">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="switchers_square">
                                    <input type="checkbox" data-on-state="Publish <?php echo $theContentType['title']; ?>" data-off-state="Draft <?php echo $theContentType['title']; ?>" data-class="switcher-success" name="content_status" value="published" <?php echo $edit ? ($content['content_status'] == 'published' ? 'checked' : '') : 'checked'?>>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="db">&nbsp;</label>
                                <?php echo submitButtonGenerator(array(
                                    'action' => $edit ? 'update' : 'save',
                                    'size' => '',
                                    'title' => $edit ? 'Update '.$theContentType['title'] : 'Save '.$theContentType['title'],
                                    'icon' => $edit ? 'icon_update' : 'icon_save',
                                    'name' => 'submit_content',
                                    'value' => 'submit_action',
                                    'text' => $edit ? 'Update '.$theContentType['title'] : 'Save '.$theContentType['title'],))
                                ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    init.push(function () {
        $('textarea').autosize().css('resize','none');
        var GET_VARS = <?php echo $_GET ? json_encode($_GET) : null?>;
        if(GET_VARS){
            var container, selectControl;

            if(GET_VARS['page'] !== undefined){
                container = $('[name="fk_page_id"]').closest('.form-group');
                selectControl = $('[name="fk_page_id"]');
                container.hide();
                if(GET_VARS['page'] != -1){
                    selectControl.find('option').removeAttr('selected');
                    selectControl.find('option[value="'+GET_VARS['page']+'"]').attr('selected','selected');
                    }
                else{
                    selectControl.find('option').remove();
                    selectControl.append('<option value="0" selected></option>');
                    }
                }
            if(GET_VARS['content_type'] !== undefined){
                $('.fk_content_type_id [value="'+GET_VARS['content_type']+'"]').attr('checked','checked');
                }
            }

        $('.switchers_square > input').each(function(index,element){
            var ths = $(this);
            var onStateContent = ths.attr('data-on-state') ? ths.attr('data-on-state') : 'YES';
            var offStateContent = ths.attr('data-off-state') ? ths.attr('data-off-state') : 'NO';

            ths.switcher({
                theme: 'square',
                on_state_content: onStateContent,
                off_state_content: offStateContent
                });
            });

        init_tinymce({
            selector: '#content_description',
            external_filemanager_path: '<?php echo $paths['relative']['common_files'];?>/filemanager/',
            body_class: 'static_page_container',
            autoResizeMaxHeight: <?php echo $this->getCTypeSetting('content','maxHeight',$theContentTypeID) ? $this->getCTypeSetting('content','maxHeight',$theContentTypeID) : 0 ?>,
            autoResizeMinHeight: <?php echo $this->getCTypeSetting('content','minHeight',$theContentTypeID) ? $this->getCTypeSetting('content','minHeight',$theContentTypeID) : 0 ?>,
            content_css: '../../site/themes/public/<?php echo getProjectSettings('components,current_public_theme');?>/assets/tinymce_content_styles.css?a='+new Date().getTime()
            });

        $(document).on('click','.add_meta',function(){
            var html = '<div class="form-group mt5 meta_form">\
                    <input type="text" name="meta_name" id="meta_name" class="meta_name form-control"/>\
                    <a href="javascript:" class="btn btn-success btn-xs add_another_meta mt5" >Add Meta</a>\
                    </div>';
                $('.meta').append(html);
            var meta_name = $('.meta_name').val();
            });
        $(document).on('click','.add_another_meta',function(){
            var ths = $(this);
            var meta_name = ths.closest('.meta_form').find(' .meta_name').val();
            var html = '<div class="form-group">\
                <label>'+meta_name+'</label>\
                <div class="radio" style="margin-top: 0;">\
                    <label class="radio-inline fl">\
                        <input type="radio" name="meta['+meta_name.replace(/\s+/g, '_').toLowerCase()+']" value="yes" class="px">\
                        <span class="lbl">Yes</span>\
                    </label>\
                    <label class="radio-inline fl">\
                        <input type="radio" name="meta['+meta_name.replace(/\s+/g, '_').toLowerCase()+']" value="no" class="px">\
                        <span class="lbl">No</span>\
                     </label>\
                </div>\
            </div>';
            $('.meta_panel').append(html);
            ths.closest('.meta_form').remove();
            });
        });
</script>

<?php
$_config['menu_positions'] = array(
    'header-primary-menu-position' => 'Header Primary Menu Position',
    'job-seeker-primary-position' => 'Job Seekers Primary Menu Position',
    'recruiters-primary-position' => 'Recruiters Primary Menu Position',
    'header-hidden-primary-menu-position' => 'Header Hidden Primary Menu Position',
    'header-tiny-menu-position' => 'Header Tiny Menu Position',
    'footer-useful-links-position' => 'Footer Useful Links Position',
    );

global $JACK_SETTINGS;

$settings = array(
    'address' => array(
        'type' => 'textarea',
        'label' => 'Address'
        ),
    'phone' => array(
        'type' => 'textarea',
        'label' => 'Phone'
        ),
    'email' => array(
        'type' => 'textarea',
        'label' => 'Email'
        ),
    );

$JACK_SETTINGS->register_settings('Get In Touch', $settings);

$settings = array(
    'about_us' => array(
        'type' => 'textarea',
        'label' => 'About Us (Short)'
        ),
    'copyright' => array(
        'type' => 'text',
        'label' => 'Copyright Text'
        ),
    );

$JACK_SETTINGS->register_settings('Footer Section', $settings);

$_config['system_pages'] = array();

function addSystemPages(){
	add_system_pages('recruiters','Recruiters', 'recruiters');
	add_system_pages('job_seekers','Job Seekers', 'job_seekers');
	add_system_pages('home','Home', 'home', 0);
	add_system_pages('contact-us','Contact Us', 'contact_page', 0);	
	}

addAction('after_execute_plugins_event', 'addSystemPages');

class recruiterContactMessageEmail extends email_templates{
    function init(){
        $this->name = 'new_recruiter_contact_message';
        $this->label = 'New Recruiter Message';
        $this->source = '<strong>Dear Concern</strong>
									<br />
									<br />
										A new Recruiter Contact Message has arrived as follows
									<br />
									<br />
									<div class="p10">
									<hr />
										<strong>Name: </strong>$$NAME$$<br />
										<strong>Email: </strong>$$EMAIL$$<br />
										<strong>Organization: </strong>$$ORGANIZATION$$<br />
										<strong>Country: </strong>$$COUNTRY$$<br />
										<strong>Skill Requirements: </strong>$$SKILLS$$<br />
									<hr />
										'.nl2br('$$MESSAGE$$').'
									<hr />

										 <a class="mailBtn btnDanger" href="$$LINK$$">Click Here to Delete This Message</a>
									</div>
									<br />
									<br />
										Thank You.
									';
        $this->availableVariables = array('$$NAME$$','$$EMAIL$$','$$ORGANIZATION$$','$$COUNTRY$$','$$SKILLS$$', '$$MESSAGE$$', '$$LINK$$');
    }
    function get_replace_array($dataArray){
        return  array(
            '$$NAME$$' => $dataArray['con_name'],
            '$$EMAIL$$' => $dataArray['con_email'],
            '$$ORGANIZATION$$' => $dataArray['con_company'],
            '$$COUNTRY$$' => $dataArray['con_country'],
            '$$SKILLS$$' => $dataArray['con_skills'],
            '$$MESSAGE$$' => $dataArray['con_content'],
            '$$LINK$$' => url('admin/dev_contact_management/manage_contacts?delete='.$dataArray['contact_id'])
            );
        }
    }
class recruiter_contact_message extends contact_message_types{
    function init(){
        $this->name = 'recruiter';
        $this->label = 'Recruiter Contact Message';
        $this->emailTemplate = new recruiterContactMessageEmail();
        $this->view_columns = array(
            'con_name' => 'Name',
            'con_email' => 'Email',
            'con_company' => 'Organization',
            'con_country' => 'Country',
            'con_skills' => 'Skills',
            'con_content' => 'Message',
            'con_created_at' => 'Date',
            );
        }
    }
new recruiter_contact_message();

class jobSeekerContactMessageEmail extends email_templates{
    function init(){
        $this->name = 'new_job_seeker_contact_message';
        $this->label = 'New Job Seeker Message';
        $this->availableVariables = array('$$NAME$$','$$EMAIL$$','$$MOBILE$$','$$SUBJECT$$', '$$MESSAGE$$', '$$LINK$$');
        $this->source = '<strong>Dear Concern</strong>
									<br />
									<br />
										A new Job Seeker Contact Message has arrived as follows
									<br />
									<br />
									<div class="p10">
									<hr />
										<strong>Name: </strong>$$NAME$$<br />
										<strong>Email: </strong>$$EMAIL$$<br />
										<strong>Mobile: </strong>$$MOBILE$$<br />
										<strong>Subject: </strong>$$SUBJECT$$<br />
									<hr />
										'.nl2br('$$MESSAGE$$').'
									<hr />

										 <a class="mailBtn btnDanger" href="$$LINK$$">Click Here to Delete This Message</a>
									</div>
									<br />
									<br />
										Thank You.
									';

        }
    function get_replace_array($dataArray){
        return array(
            '$$NAME$$' => $dataArray['con_name'],
            '$$EMAIL$$' => $dataArray['con_email'],
            '$$MOBILE$$' => $dataArray['con_mobile'],
            '$$SUBJECT$$' => $dataArray['con_subject'],
            '$$MESSAGE$$' => $dataArray['con_content'],
            '$$LINK$$' => url('admin/dev_contact_management/manage_contacts?delete='.$dataArray['contact_id'])
            )    ;
        }
    }
class job_seeker_contact_message extends contact_message_types{
    function init(){
        $this->name = 'job_seeker';
        $this->label = 'Job Seeker Contact Message';
        $this->emailTemplate = new jobSeekerContactMessageEmail();
        $this->view_columns = array(
            'con_name' => 'Name',
            'con_email' => 'Email',
            'con_mobile' => 'Mobile',
            'con_subject' => 'Subject',
            'con_content' => 'Message',
            'con_created_at' => 'Date',
            );
        }
    }
new job_seeker_contact_message();

//content types
class faq extends custom_content_type{
    function init(){
        $this->name = 'Faq';
        $this->label = 'Faq';
        $this->exceptional = false;

        $this->title_settings['label'] = 'Question';

        $this->subtitle_settings['hide'] = true;

        $this->content_settings['label'] = 'Answer';
        $this->content_settings['maxHeight'] = '250';
        $this->content_settings['minHeight'] = '200';

        $this->category_settings['hide'] = true;
        $this->featuredImage_settings['hide'] = true;
        $this->squareFeaturedImage_settings['hide'] = true;
        $this->wideFeaturedImage_settings['hide'] = true;
        $this->seoFeatures_settings['hide'] = true;
        $this->publishTime_settings['hide'] = true;
    }

    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){return null;}
    public function get_fields($content){
        $meta = $content ? $content['meta'] : array();
        $client_link = getMetaValue($content, 'client_link');
        ?>
        <div class="form-group">
            <label>Link To Client Website</label>
            <input type="text" class="form-control" name="meta[client_link]" value="<?php echo $client_link; ?>" />
        </div>
        <?php
    }
}
new faq();

class gallery extends custom_content_type{
    function init(){
        $this->name = 'gallery';
        $this->label = 'gallery';
        $this->exceptional = false;

        $this->category_settings['hide'] = true;
        $this->squareFeaturedImage_settings['hide'] = true;
        $this->wideFeaturedImage_settings['hide'] = true;
    }

    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){
        global $_config, $devdb;
        $content_types = $_config['content_types'];
        //TODO: We should consider setting cache for each child element, so ultimately we can benefit from not even querying a single time.
        $oldChild = $devdb->get_results("SELECT * FROM dev_contents WHERE fk_content_id = '".$content_id."'",'pk_content_id');
        $sql = "DELETE FROM dev_contents WHERE fk_content_id = '".$content_id."'";
        $cleaning = $devdb->query($sql);

        $insert_data = array();

        if(isset($_POST['child']) && $_POST['child']){
            foreach($_POST['child'] as $i=>$v){
                $oldData = array();
                if($v['content_id'] && isset($oldChild[$v['content_id']])) $oldData = $oldChild[$v['content_id']];

                $extraSettings = array('item_url' => $v['item_url']);

                $insert_data['content_title'] = processToStore($oldData['content_title'],$v['content_title']);
                $insert_data['content_sub_title'] = processToStore('','');
                $insert_data['content_slug'] = form_modifiers::slug($v['content_slug'] ? $v['content_slug'] : $v['content_title']);
                $insert_data['content_description'] = processToStore($oldData['content_description'],$v['content_description']);
                $insert_data['fk_content_id'] = $content_id ? $content_id : 0;
                $insert_data['content_published_time'] = $data['content_published_time'] ? datetime_to_db($data['content_published_time']) : date('Y-m-d H:i:s');
                $insert_data['content_meta_description'] = processToStore('','');
                $insert_data['content_excerpt'] = processToStore('','');
                $insert_data['fk_content_type_id'] = 'gallery_item';
                $insert_data['content_thumbnail'] = $v['content_thumbnail'];
                $insert_data['content_status'] = 'published';
                $insert_data['content_extra_settings'] = json_encode($extraSettings);
                $insert_data['created_at'] = date('Y-m-d H:i:s');
                $insert_data['created_by'] = $_config['user']['pk_user_id'];
                $insert_data['modified_at'] = date('Y-m-d H:i:s');
                $insert_data['modified_by'] = $_config['user']['pk_user_id'];

                $insert_data['fk_page_id'] = 0;

                $insert = $devdb->insert_update('dev_contents',$insert_data);
            }
        }

        return null;
    }
    public function get_fields($content){
        global $_config;
        $image_count = 0;
        ?>
        <fieldset class="mb10">
            <div class="gallery_images table-primary table-responsive">
                <div class="table-header">Gallery Images</div>
                <table class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th style="width: 110px">Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Optional Link</th>
                        <th class="action_column tar">...</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <div class="table-footer">
                    <?php
                    echo buttonButtonGenerator(array(
                        'action' => 'add',
                        'title' => 'Add Another Slide',
                        'icon' => 'icon_add',
                        'text' => 'Add Another Slide',
                        'classes' => 'add_gallery_item'
                    ));
                    ?>
                </div>
            </div>
        </fieldset>
        <script type="text/javascript">
            init.push(function(){
                var count = 0;
                var previousImage = <?php echo $content && $content['fk_content_type_id'] == 'gallery' && $content['childs'] ? to_json_object($content['childs']) : '{}' ?>;
                var totalPrevious = Object.keys(previousImage).length;
                function addGalleryImage(theImage){
                    var thisImage = {
                        content_id: null,
                        image: '',
                        imageLink: null,
                        title: '',
                        link: '',
                        description: '',
                    };

                    thisImage = $.extend(true, thisImage, theImage);

                    count = thisImage.content_id ? thisImage.content_id : count++;

                    var html = '<tr class="each_gallery_image">\
                                    <td class="action_column tac sortHandle"><span class="eachItemSerial">'+count+'</span><input type="hidden" name="gallery_image_sort_order[]" value="'+count+'" /></td>\
                                    <td style="width: 110px">\
                                        <div class="image_upload_container">\
                                            <div class="controlBtnContainer"><div class="controlBtn"><a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=thumbImage_x'+count+'&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x2" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i></a><a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i></a></div></div>\
                                            '+(thisImage['imageLink'] ? '<img src="'+thisImage['imageLink']+'" />' : '')+'\
                                            <input id="thumbImage_x'+count+'" name="child['+count+'][content_thumbnail]" type="hidden" class="form-control" value="'+thisImage['image']+'">\
                                        </div>\
                                    </td>\
                                    <td>\
                                        '+(thisImage['content_id'] ? '<input type="hidden" name="child['+count+'][content_id]" value="'+thisImage['content_id']+'" />' : '')+'\
                                        <input type="text" class="form-control" name="child['+count+'][content_title]" value="'+thisImage['title']+'" required/>\
                                    </td>\
                                    <td>\
                                        <textarea class="form-control" name="child['+count+'][content_description]">'+thisImage['description']+'</textarea>\
                                    </td>\
                                    <td>\
                                        <input type="text" class="form-control" name="child['+count+'][item_url]" value="'+thisImage['link']+'"/>\
                                    </td>\
                                    <td class="action_column tar"><a class="btn btn-danger btn-xs delete_image"><i class="fa fa-times-circle"></a></td>\
                                </tr>';
                    $('.gallery_images tbody').append(html);
                    $('textarea').autosize().css('resize','none');
                    make_gallery_item_sortable();
                }

                if(totalPrevious){
                    for(var i = 0; i < totalPrevious; i++){
                        var thisImage = previousImage[i];
                        var thisImageExtra = JSON.parse(thisImage['content_extra_settings']);
                        addGalleryImage({
                            content_id: thisImage['pk_content_id'],
                            image: thisImage['content_thumbnail'] ? thisImage['content_thumbnail'] : '',
                            imageLink: thisImage['content_thumbnail'] ? get_image(thisImage['content_thumbnail'], '100x100x2') : '',
                            title: processToRender(thisImage['content_title']),
                            link: thisImageExtra['item_url'],
                            description: processToRender(thisImage['content_description']),
                        });
                        fixGalleryItemsSerialNumber();
                    }
                }
                $(document).on('click','.add_gallery_item',function(){
                    addGalleryImage({});
                    fixGalleryItemsSerialNumber();
                });
                /*if(!$('.gallery_images .each_gallery_image').length){
                    addGalleryImage({});
                    fixGalleryItemsSerialNumber();
                    }*/
                $(document).on('click','.delete_image',function(){
                    if(confirm('Delete this gallery item?')){
                        $(this).closest('.each_gallery_image').slideUp().remove();
                    }
                    fixGalleryItemsSerialNumber();
                });
                function make_gallery_item_sortable(){
                    $('.gallery_images tbody').sortable({
                        handle: '.sortHandle',
                        cursor: 'move',
                        axis: 'y',
                        opacity: .5,
                        start: function (event, ui) {
                            ui.placeholder.html("<td style='width:"+$(ui.item).closest('table').width()+"px;' colspan='6'></td>");
                        },
                        update: function(){fixGalleryItemsSerialNumber();},
                    });
                }
                make_gallery_item_sortable();
                function fixGalleryItemsSerialNumber(){
                    count = 1;
                    $('.gallery_images .eachItemSerial').each(function(i,e){
                        $(e).text(count++);
                    });
                }
            });
        </script>
        <?php
    }
}
new gallery();

class gallery_item extends custom_content_type{
    function init(){
        $this->name = 'gallery_item';
        $this->label = 'gallery_item';
        $this->exceptional = true;
    }

    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){return null;}
    public function get_fields($content){return null;}
}
new gallery_item();

class job_circular extends custom_content_type{
    function init(){
        $this->name = 'Job-Circular';
        $this->label = 'Job-Circular';
        $this->exceptional = false;

        $this->category_settings['hide'] = true;
        $this->squareFeaturedImage_settings['hide'] = true;
        $this->wideFeaturedImage_settings['hide'] = true;
    }

    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){return null;}
    public function get_fields($content){
        $tagger = jack_obj('dev_tag_management');

        $WORLD_COUNTRY_LIST = getWorldCountry();
        $meta = $content ? $content['meta'] : array();
        $job_country = getMetaValue($content, 'job_country');
        $occupation = getMetaValue($content, 'job_occupation');
        $expireDate = getMetaValue($content, 'content_expire_date');
        $salary = getMetaValue($content, 'job_salary');
        $refillOccupation = array();
        if($occupation){
            $theOccupation = $tagger->get_tags(array('tag_id' => $occupation, 'single' => true));
            $refillOccupation[] = array('id' => $occupation, 'label' => $theOccupation['tag_title'], 'value' => $theOccupation['tag_title']);
        }
        ?>
        <div class="row">
            <div class="col-lg-6 form-group">
                <label>Country</label>
                <select class="form-control adv_select" name="meta[job_country]">
                    <?php
                    foreach($WORLD_COUNTRY_LIST as $i=>$v){
                        $selected = $job_country == $i ? 'selected' : '';
                        echo '<option value="'.$i.'" '.$selected.'>'.$v.'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-lg-6 form-group">
                <label>Occupation</label>
                <div id="occupation_autocomplete"></div>
                <script type="text/javascript">
                    init.push(function(){
                        new set_autosuggest({
                            container: '#occupation_autocomplete',
                            submit_labels: false,
                            ajax_page: _root_path_+'/api/dev_tag_management/get_tags_autocomplete',
                            single: true,
                            parameters: {'tag_group' : 'occupation'},
                            multilingual: true,
                            input_field: '#input_occupation',
                            field_name: 'meta[job_occupation]',
                            add_what: 'Occupation',
                            add_new: true,
                            url_for_add: _root_path_+'/api/dev_tag_management/add_edit_tags',
                            field_for_add: 'tag_title',
                            data_for_add: {tag_group: 'occupation'},
                            existing_items: <?php echo to_json_object($refillOccupation);?>,
                        });
                    });
                </script>
            </div>
            <div class="col-lg-6 form-group">
                <label>Expire Data</label>
                <div class="datepicker_holder">
                    <input type="text" class="form-control" id="content_expire_date" name="meta[content_expire_date]" value="<?php echo $expireDate ? $expireDate : ''; ?>" />
                </div>
                <script type="text/javascript">
                    init.push(function(){
                        _datepicker('content_expire_date');
                    });
                </script>
            </div>
            <div class="form-group col-lg-6">
                <lable>Salary</lable>
                <input type="text" class="form-control" name="meta[job_salary]" value="<?php echo $salary; ?>" />
            </div>
        </div>
        <?php
    }
}
new job_circular();

class news extends custom_content_type{
    function init(){
        $this->name = 'News';
        $this->label = 'News';
        $this->exceptional = false;

        $this->category_settings['hide'] = true;
        $this->squareFeaturedImage_settings['hide'] = true;
        $this->wideFeaturedImage_settings['hide'] = true;
    }

    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){return null;}
    public function get_fields($content){return null;}
}
new news();

class partners extends custom_content_type{
    function init(){
        $this->name = 'partners';
        $this->label = 'partners';
        $this->exceptional = false;

        $this->title_settings['label'] = 'Partner Name';

        $this->subtitle_settings['hide'] = true;

        $this->content_settings['label'] = 'About Partner';
        $this->content_settings['maxHeight'] = '250';
        $this->content_settings['minHeight'] = '200';

        $this->category_settings['hide'] = true;
        $this->featuredImage_settings['label'] = 'Partner Logo';
        $this->squareFeaturedImage_settings['hide'] = true;
        $this->wideFeaturedImage_settings['hide'] = true;
    }

    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){return null;}
    public function get_fields($content){
        $meta = $content ? $content['meta'] : array();
        $client_link = getMetaValue($content, 'client_link');
        ?>
        <div class="form-group">
            <label>Link To Partner Website</label>
            <input type="text" class="form-control" name="meta[client_link]" value="<?php echo $client_link; ?>" />
        </div>
        <?php
    }
}
new partners();

class services extends custom_content_type{
    function init(){
        $this->name = 'Service';
        $this->label = 'Service';
        $this->exceptional = false;

        $this->category_settings['hide'] = true;
        $this->squareFeaturedImage_settings['hide'] = true;
        $this->wideFeaturedImage_settings['hide'] = true;
    }

    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){return null;}
    public function get_fields($content){
        $price = getMetaValue($content, 'service_price');
        ?>
        <div class="row">
            <div class="col-lg-4 form-group">
                <label>Price</label>
                <input type="text" class="form-control" name="meta[service_price]" value="<?php echo $price; ?>" />
            </div>
        </div>
        <?php
    }
}
new services();

class testimonials extends custom_content_type{
    function init(){
        $this->name = 'Testimonial';
        $this->label = 'Testimonial';
        $this->exceptional = false;

        $this->title_settings['label'] = 'Testimonial By';
        $this->subtitle_settings['label'] = 'Designation';
        $this->content_settings['label'] = 'Testimony';
        $this->category_settings['hide'] = true;
        $this->featuredImage_settings['label'] = 'Photo';
        $this->squareFeaturedImage_settings['hide'] = true;
        $this->wideFeaturedImage_settings['hide'] = true;
        $this->seoFeatures_settings['hide'] = true;
        $this->publishTime_settings['hide'] = true;
    }
    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){return null;}
    public function get_fields($content){
        $company = getMetaValue($content, 'company');
        ?>
        <div class="form-group">
            <label>Company or Organization</label>
            <input type="text" class="form-control" name="meta[company]" value="<?php echo $company; ?>" />
        </div>
        <?php
    }
}
new testimonials();

class trainings extends custom_content_type{
    function init(){
        $this->name = 'Training';
        $this->label = 'Training';
        $this->exceptional = false;

        $this->category_settings['hide'] = true;
        $this->squareFeaturedImage_settings['hide'] = true;
        $this->wideFeaturedImage_settings['hide'] = true;
    }

    public function preProcess($content){return null;}
    public function postProcess($content_id, $data){return null;}
    public function get_fields($content){
        $price = getMetaValue($content, 'training_price');
        ?>
        <div class="row">
            <div class="col-lg-4 form-group">
                <label>Price</label>
                <input type="text" class="form-control" name="meta[training_price]" value="<?php echo $price; ?>" />
            </div>
        </div>
        <?php
    }
}
new trainings();

//register page templates
class static_page extends page_templates{
    function init(){
        $this->name = 'static_page';
        $this->label = 'Static Page';
        }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new static_page();

class contact_page extends page_templates{
    function init(){
        $this->name = 'contact_page';
        $this->label = 'Contact Page';
        $this->disableSelection = true;
    }

    public function preProcess($content){return null;}
    public function getFields($content){
        $extras = $content && strlen($content['page_extras']) ? unserialize(processToRender($content['page_extras'])) : array();
        $col_1 = 'col-sm-4';
        $col_2 = 'col-sm-8';
        ?>
        <fieldset>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Prompt Text for Get In Touch</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[get_in_touch][text]" ><?php echo $extras ? $extras['get_in_touch']['text'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Prompt Text for Feedback</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[feedback][text]"><?php echo $extras ? $extras['feedback']['text'] : ''; ?></textarea>
                </div>
            </div>
        </fieldset>
        <?php
    }
}
new contact_page();

class home extends page_templates{
    function init(){
        $this->name = 'home';
        $this->label = 'Home Page';
        $this->disableSelection = true;
    }

    public function preProcess($content){return null;}
    public function getFields($content){
        global $_config;
        $extras = $content && strlen($content['page_extras']) ? unserialize(processToRender($content['page_extras'])) : array();
        ?>
        <div class="note note-danger">
            <h4 class="note-title">Please Notice</h4>
            If you do not provide data for these following fields for a language, they will be rendered as empty. For example, if you do provide the Title of this page in the default language (English), but do not provide in Bangla, the Title will still be rendered in English while viewing as Bangla. But for the following fields, this auto-adaption will not happen.
        </div>
        <div class="row form-group">
            <label class="control-label">Welcome Text</label>
            <input type="text" class="form-control" name="extras[welcome_text]" value="<?php echo $extras ? $extras['welcome_text'] : ''; ?>"/>
        </div>
        <?php
    }
}
new home();

class template_services extends page_templates{
    function init(){
        $this->name = 'services';
        $this->label = 'Service Page';
    }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new template_services();

class template_trainings extends page_templates{
    function init(){
        $this->name = 'trainings';
        $this->label = 'Trainings Page';
    }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new template_trainings();

class template_news extends page_templates{
    function init(){
        $this->name = 'news';
        $this->label = 'News Page';
    }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new template_news();

class template_faq extends page_templates{
    function init(){
        $this->name = 'faq';
        $this->label = 'Faq Page';
    }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new template_faq();

class template_testimonial extends page_templates{
    function init(){
        $this->name = 'testimonial';
        $this->label = 'Testimonial Page';
    }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new template_testimonial();

class template_partner extends page_templates{
    function init(){
        $this->name = 'partner';
        $this->label = 'Partner Page';
    }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new template_partner();

class template_job_circular extends page_templates{
    function init(){
        $this->name = 'partner';
        $this->label = 'Partner Page';
    }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new template_job_circular();

class template_gallery extends page_templates{
    function init(){
        $this->name = 'gallery';
        $this->label = 'Gallery Page';
    }

    public function preProcess($content){return null;}
    public function getFields($content){return null;}
}
new template_gallery();

class template_recruiters extends page_templates{
    function init(){
        $this->name = 'recruiters';
        $this->label = 'Recruiters Page';
        $this->disableSelection = true;
    }

    public function preProcess($content){return null;}
    public function getFields($content){
        global $_config;
        $extras = $content && strlen($content['page_extras']) ? unserialize(processToRender($content['page_extras'])) : array();
        $col_1 = 'col-sm-4';
        $col_2 = 'col-sm-8';
        $cManager = jack_obj('dev_content_management');
        $params = array(
            'published_till_now' => true,
            'content_types' => 'Service',
        );
        $services = $cManager->get_contents($params);

        $params = array(
            'published_till_now' => true,
            'content_types' => 'gallery',
        );
        $galleries = $cManager->get_contents($params);
        ?>
        <div class="note note-danger">
            <h4 class="note-title">Please Notice</h4>
            If you do not provide data for these following fields for a language, they will be rendered as empty. For example, if you do provide the Title of this page in the default language (English), but do not provide in Bangla, the Title will still be rendered in English while viewing as Bangla. But for the following fields, this auto-adaption will not happen.
        </div>
        <fieldset>
            <legend>Search Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[find_better_section][title]" value="<?php echo $extras ? $extras['find_better_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Sub Title</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[find_better_section][sub_title]"><?php echo $extras ? $extras['find_better_section']['sub_title'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Bottom Line</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[find_better_section][bottom_line]" value="<?php echo $extras ? $extras['find_better_section']['bottom_line'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Background Type</label>
                <div class="<?php echo $col_2; ?>">
                    <label class="radio-inline">
                        <input type="radio" class="px form-control recruiter_search_bg_type" name="extras[find_better_section][bg_type]" value="image" <?php echo $extras ? ($extras['find_better_section']['bg_type'] == 'image' ? 'checked' : '') : 'checked'; ?>/>
                        <span class="lbl">Image</span>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" class="px form-control recruiter_search_bg_type" name="extras[find_better_section][bg_type]" value="gallery" <?php echo $extras ? ($extras['find_better_section']['bg_type'] == 'gallery' ? 'checked' : '') : ''; ?>/>
                        <span class="lbl">Gallery</span>
                    </label>
                </div>
            </div>
            <div class="row form-group recruiter_bg_image_container" style="display: none">
                <label class="<?php echo $col_1; ?> control-label">Background Image</label>
                <div class="<?php echo $col_2; ?>">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=find_better_section_photo&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <?php if($content && $extras['find_better_section']['bg_image']): ?>
                                    <img class="" src="<?php echo get_image($extras['find_better_section']['bg_image'],'100x100x1')?>" />
                                <?php endif; ?>
                                <input id="find_better_section_photo" name="extras[find_better_section][bg_image]" type="hidden" class="form-control" value="<?php echo $content && $extras['find_better_section']['bg_image'] ? $extras['find_better_section']['bg_image'] : ''?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group recruiter_bg_gallery_container" style="display: none">
                <label class="<?php echo $col_1; ?> control-label">Background Gallery</label>
                <div class="<?php echo $col_2; ?>">
                    <select class="form-control" name="extras[find_better_section][bg_gallery]">
                        <option value="">Select One</option>
                        <?php
                        if($galleries['data']){
                            foreach($galleries['data'] as $i=>$item){
                                $selected = $extras && $extras['find_better_section']['bg_gallery'] == $item['pk_content_id'] ? 'selected' : '';
                                ?>
                                <option value="<?php echo $item['pk_content_id']; ?>" <?php echo $selected; ?>><?php echo processToRender($item['content_title']); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <script type="text/javascript">
                $('.recruiter_search_bg_type').on('change', function(){
                    if($('.recruiter_search_bg_type:checked').val() == 'image'){
                        $('.recruiter_bg_image_container').slideDown();
                        $('.recruiter_bg_gallery_container').slideUp();
                    }
                    else if($('.recruiter_search_bg_type:checked').val() == 'gallery'){
                        $('.recruiter_bg_image_container').slideUp();
                        $('.recruiter_bg_gallery_container').slideDown();
                    }
                }).change();
            </script>
        </fieldset>
        <fieldset>
            <legend>About Company Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[about_company_section][title]" value="<?php echo $extras ? $extras['about_company_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Sub-Title</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[about_company_section][sub_title]" ><?php echo $extras ? $extras['about_company_section']['sub_title'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Content</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[about_company_section][content]"><?php echo $extras ? $extras['about_company_section']['content'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Photo</label>
                <div class="<?php echo $col_2; ?>">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=about_company_section_photo&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <?php if($content && $extras['about_company_section']['photo']): ?>
                                    <img class="" src="<?php echo get_image($extras['about_company_section']['photo'],'100x100x1')?>" />
                                <?php endif; ?>
                                <input id="about_company_section_photo" name="extras[about_company_section][photo]" type="hidden" class="form-control" value="<?php echo $content && $extras['about_company_section']['photo'] ? $extras['about_company_section']['photo'] : ''?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Link 1: Select Page</label>
                <div class="<?php echo $col_2; ?>">
                    <select name="extras[about_company_section][page_one]" class="form-control">
                        <?php
                        echo getPageSelectOptions(($extras['about_company_section']['page_one'] ? $extras['about_company_section']['page_one'] : null));
                        ?>
                    </select>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Link 2: Select Page</label>
                <div class="<?php echo $col_2; ?>">
                    <select name="extras[about_company_section][page_two]" class="form-control">
                        <?php
                        echo getPageSelectOptions(($extras['about_company_section']['page_two'] ? $extras['about_company_section']['page_two'] : null));
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Message Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[message_section][title]" value="<?php echo $extras ? $extras['message_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">The Message</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[message_section][content]"><?php echo $extras ? $extras['message_section']['content'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Message By</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[message_section][message_by]" value="<?php echo $extras ? $extras['message_section']['message_by'] : ''; ?>"/>
                </div>
            </div><div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Company</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[message_section][message_by_company]" value="<?php echo $extras ? $extras['message_section']['message_by_company'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Photo</label>
                <div class="<?php echo $col_2; ?>">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=message_section_photo&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <?php if($content && $extras['message_section']['photo']): ?>
                                    <img class="" src="<?php echo get_image($extras['message_section']['photo'],'100x100x1')?>" />
                                <?php endif; ?>
                                <input id="message_section_photo" name="extras[message_section][photo]" type="hidden" class="form-control" value="<?php echo $content && $extras['message_section']['photo'] ? $extras['message_section']['photo'] : ''?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Services Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[service_section][title]" value="<?php echo $extras ? $extras['service_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Sub Title</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[service_section][sub_title]"><?php echo $extras ? $extras['service_section']['sub_title'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Service One</label>
                <div class="<?php echo $col_2; ?>">
                    <select class="form-control adv_select" name="extras[service_section][service_one]">
                        <option value="">Select One</option>
                        <?php
                        if($services['data']){
                            foreach($services['data'] as $i=>$item){
                                $selected = $extras && $extras['service_section']['service_one'] == $item['pk_content_id'] ? 'selected' : '';
                                ?>
                                <option value="<?php echo $item['pk_content_id']; ?>" <?php echo $selected; ?>><?php echo processToRender($item['content_title']); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Service Two</label>
                <div class="<?php echo $col_2; ?>">
                    <select class="form-control adv_select" name="extras[service_section][service_two]">
                        <option value="">Select One</option>
                        <?php
                        if($services['data']){
                            foreach($services['data'] as $i=>$item){
                                $selected = $extras && $extras['service_section']['service_two'] == $item['pk_content_id'] ? 'selected' : '';
                                ?>
                                <option value="<?php echo $item['pk_content_id']; ?>" <?php echo $selected; ?>><?php echo processToRender($item['content_title']); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Clients Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[client_section][title]" value="<?php echo $extras ? $extras['client_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Sub Title</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[client_section][sub_title]"><?php echo $extras ? $extras['client_section']['sub_title'] : ''; ?></textarea>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Contact Information Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[contact_section][title]" value="<?php echo $extras ? $extras['contact_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Descriptive Text</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[contact_section][content]"><?php echo $extras ? $extras['contact_section']['content'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Contact Number</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[contact_section][contact]" value="<?php echo $extras ? $extras['contact_section']['contact'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Open Hours</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[contact_section][open_hours]"><?php echo $extras ? $extras['contact_section']['open_hours'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Background Image</label>
                <div class="<?php echo $col_2; ?>">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=contact_section_photo&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <?php if($content && $extras['contact_section']['bg_image']): ?>
                                    <img class="" src="<?php echo get_image($extras['contact_section']['bg_image'],'100x100x1')?>" />
                                <?php endif; ?>
                                <input id="contact_section_photo" name="extras[contact_section][bg_image]" type="hidden" class="form-control" value="<?php echo $content && $extras['contact_section']['bg_image'] ? $extras['contact_section']['bg_image'] : ''?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <?php
    }
}
new template_recruiters();

class template_job_seekers extends page_templates{
    function init(){
        $this->name = 'job_seekers';
        $this->label = 'Job Seekers Page';
        $this->disableSelection = true;
    }

    public function preProcess($content){return null;}
    public function getFields($content){
        global $_config;
        $extras = $content && strlen($content['page_extras']) ? unserialize(processToRender($content['page_extras'])) : array();
        $col_1 = 'col-sm-4';
        $col_2 = 'col-sm-8';
        $cManager = jack_obj('dev_content_management');
        $params = array(
            'published_till_now' => true,
            'content_types' => 'Service',
        );
        $services = $cManager->get_contents($params);

        $params = array(
            'published_till_now' => true,
            'content_types' => 'gallery',
        );
        $galleries = $cManager->get_contents($params);

        $menuManager = jack_obj('dev_menu_management');
        $menus = $menuManager->get_menus();
        ?>
        <div class="note note-danger">
            <h4 class="note-title">Please Notice</h4>
            If you do not provide data for these following fields for a language, they will be rendered as empty. For example, if you do provide the Title of this page in the default language (English), but do not provide in Bangla, the Title will still be rendered in English while viewing as Bangla. But for the following fields, this auto-adaption will not happen.
        </div>
        <fieldset>
            <legend>Gallery Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Select a Gallery</label>
                <div class="<?php echo $col_2; ?>">
                    <select class="form-control adv_select" name="extras[gallery_section][gallery]">
                        <option value="">Select One</option>
                        <?php
                        if($galleries['data']){
                            foreach($galleries['data'] as $i=>$item){
                                $selected = $extras && $extras['gallery_section']['gallery'] == $item['pk_content_id'] ? 'selected' : '';
                                ?>
                                <option value="<?php echo $item['pk_content_id']; ?>" <?php echo $selected; ?>><?php echo processToRender($item['content_title']); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>About Company Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[about_company_section][title]" value="<?php echo $extras ? $extras['about_company_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Sub-Title</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[about_company_section][sub_title]" ><?php echo $extras ? $extras['about_company_section']['sub_title'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Content</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[about_company_section][content]"><?php echo $extras ? $extras['about_company_section']['content'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Photo</label>
                <div class="<?php echo $col_2; ?>">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=js_about_company_section_photo&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <?php if($content && $extras['about_company_section']['photo']): ?>
                                    <img class="" src="<?php echo get_image($extras['about_company_section']['photo'],'100x100x1')?>" />
                                <?php endif; ?>
                                <input id="js_about_company_section_photo" name="extras[about_company_section][photo]" type="hidden" class="form-control" value="<?php echo $content && $extras['about_company_section']['photo'] ? $extras['about_company_section']['photo'] : ''?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Link 1: Select Page</label>
                <div class="<?php echo $col_2; ?>">
                    <select name="extras[about_company_section][page_one]" class="form-control">
                        <?php
                        echo getPageSelectOptions(($extras['about_company_section']['page_one'] ? $extras['about_company_section']['page_one'] : null));
                        ?>
                    </select>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Link 2: Select Page</label>
                <div class="<?php echo $col_2; ?>">
                    <select name="extras[about_company_section][page_two]" class="form-control">
                        <?php
                        echo getPageSelectOptions(($extras['about_company_section']['page_two'] ? $extras['about_company_section']['page_two'] : null));
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Message Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[message_section][title]" value="<?php echo $extras ? $extras['message_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">The Message</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[message_section][content]"><?php echo $extras ? $extras['message_section']['content'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Message By</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[message_section][message_by]" value="<?php echo $extras ? $extras['message_section']['message_by'] : ''; ?>"/>
                </div>
            </div><div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Company</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[message_section][message_by_company]" value="<?php echo $extras ? $extras['message_section']['message_by_company'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Photo</label>
                <div class="<?php echo $col_2; ?>">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=js_message_section_photo&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <?php if($content && $extras['message_section']['photo']): ?>
                                    <img class="" src="<?php echo get_image($extras['message_section']['photo'],'100x100x1')?>" />
                                <?php endif; ?>
                                <input id="js_message_section_photo" name="extras[message_section][photo]" type="hidden" class="form-control" value="<?php echo $content && $extras['message_section']['photo'] ? $extras['message_section']['photo'] : ''?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Current Jobs Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[current_job_section][title]" value="<?php echo $extras ? $extras['current_job_section']['title'] : ''; ?>"/>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>How BPL Works Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[how_bpl_works_section][title]" value="<?php echo $extras ? $extras['how_bpl_works_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Sub Title</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[how_bpl_works_section][sub_title]"><?php echo $extras ? $extras['how_bpl_works_section']['sub_title'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <fieldset>
                        <legend>Work Model One</legend>
                        <div class="row form-group">
                            <div class="col-lg-12">
                                <div class="panel">
                                    <div class="panel-body">
                                        <div class="image_upload_container controlVisible">
                                            <div class="controlBtnContainer">
                                                <div class="controlBtn">
                                                    <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=js_how_bpl_works_section_icon_one&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                    <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                                </div>
                                            </div>
                                            <?php if($content && $extras['how_bpl_works_section']['work_model_one']['icon']): ?>
                                                <img class="" src="<?php echo get_image($extras['how_bpl_works_section']['work_model_one']['icon'],'100x100x1')?>" />
                                            <?php endif; ?>
                                            <input id="js_how_bpl_works_section_icon_one" name="extras[how_bpl_works_section][work_model_one][icon]" type="hidden" class="form-control" value="<?php echo $content && $extras['how_bpl_works_section']['work_model_one']['icon'] ? $extras['how_bpl_works_section']['work_model_one']['icon'] : ''?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="<?php echo $col_1; ?> control-label">Title</label>
                            <div class="<?php echo $col_2; ?>">
                                <input type="text" class="form-control" name="extras[how_bpl_works_section][work_model_one][title]" value="<?php echo $extras ? $extras['how_bpl_works_section']['work_model_one']['title'] : ''; ?>"/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="<?php echo $col_1; ?> control-label">Descriptive Text</label>
                            <div class="<?php echo $col_2; ?>">
                                <textarea class="form-control" name="extras[how_bpl_works_section][work_model_one][content]"><?php echo $extras ? $extras['how_bpl_works_section']['work_model_one']['content'] : ''; ?></textarea>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-lg-4">
                    <fieldset>
                        <legend>Work Model Two</legend>
                        <div class="row form-group">
                            <div class="col-lg-12">
                                <div class="panel">
                                    <div class="panel-body">
                                        <div class="image_upload_container controlVisible">
                                            <div class="controlBtnContainer">
                                                <div class="controlBtn">
                                                    <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=js_how_bpl_works_section_icon_two&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                    <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                                </div>
                                            </div>
                                            <?php if($content && $extras['how_bpl_works_section']['work_model_two']['icon']): ?>
                                                <img class="" src="<?php echo get_image($extras['how_bpl_works_section']['work_model_two']['icon'],'100x100x1')?>" />
                                            <?php endif; ?>
                                            <input id="js_how_bpl_works_section_icon_two" name="extras[how_bpl_works_section][work_model_two][icon]" type="hidden" class="form-control" value="<?php echo $content && $extras['how_bpl_works_section']['work_model_two']['icon'] ? $extras['how_bpl_works_section']['work_model_two']['icon'] : ''?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="<?php echo $col_1; ?> control-label">Title</label>
                            <div class="<?php echo $col_2; ?>">
                                <input type="text" class="form-control" name="extras[how_bpl_works_section][work_model_two][title]" value="<?php echo $extras ? $extras['how_bpl_works_section']['work_model_two']['title'] : ''; ?>"/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="<?php echo $col_1; ?> control-label">Descriptive Text</label>
                            <div class="<?php echo $col_2; ?>">
                                <textarea class="form-control" name="extras[how_bpl_works_section][work_model_two][content]"><?php echo $extras ? $extras['how_bpl_works_section']['work_model_two']['content'] : ''; ?></textarea>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-lg-4">
                    <fieldset>
                        <legend>Work Model Three</legend>
                        <div class="row form-group">
                            <div class="col-lg-12">
                                <div class="panel">
                                    <div class="panel-body">
                                        <div class="image_upload_container controlVisible">
                                            <div class="controlBtnContainer">
                                                <div class="controlBtn">
                                                    <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=js_how_bpl_works_section_icon_three&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                                    <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                                </div>
                                            </div>
                                            <?php if($content && $extras['how_bpl_works_section']['work_model_three']['icon']): ?>
                                                <img class="" src="<?php echo get_image($extras['how_bpl_works_section']['work_model_three']['icon'],'100x100x1')?>" />
                                            <?php endif; ?>
                                            <input id="js_how_bpl_works_section_icon_three" name="extras[how_bpl_works_section][work_model_three][icon]" type="hidden" class="form-control" value="<?php echo $content && $extras['how_bpl_works_section']['work_model_three']['icon'] ? $extras['how_bpl_works_section']['work_model_three']['icon'] : ''?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="<?php echo $col_1; ?> control-label">Title</label>
                            <div class="<?php echo $col_2; ?>">
                                <input type="text" class="form-control" name="extras[how_bpl_works_section][work_model_three][title]" value="<?php echo $extras ? $extras['how_bpl_works_section']['work_model_three']['title'] : ''; ?>"/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="<?php echo $col_1; ?> control-label">Descriptive Text</label>
                            <div class="<?php echo $col_2; ?>">
                                <textarea class="form-control" name="extras[how_bpl_works_section][work_model_three][content]"><?php echo $extras ? $extras['how_bpl_works_section']['work_model_three']['content'] : ''; ?></textarea>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="row form-group">
                        <label class="<?php echo $col_1; ?> control-label">Service One</label>
                        <div class="<?php echo $col_2; ?>">
                            <select class="form-control adv_select" name="extras[how_bpl_works_section][service_one]">
                                <option value="">Select One</option>
                                <?php
                                if($services['data']){
                                    foreach($services['data'] as $i=>$item){
                                        $selected = $extras && $extras['how_bpl_works_section']['service_one'] == $item['pk_content_id'] ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $item['pk_content_id']; ?>" <?php echo $selected; ?>><?php echo processToRender($item['content_title']); ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row form-group">
                        <label class="<?php echo $col_1; ?> control-label">Service Two</label>
                        <div class="<?php echo $col_2; ?>">
                            <select class="form-control adv_select" name="extras[how_bpl_works_section][service_two]">
                                <option value="">Select One</option>
                                <?php
                                if($services['data']){
                                    foreach($services['data'] as $i=>$item){
                                        $selected = $extras && $extras['how_bpl_works_section']['service_two'] == $item['pk_content_id'] ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $item['pk_content_id']; ?>" <?php echo $selected; ?>><?php echo processToRender($item['content_title']); ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Clients Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[client_section][title]" value="<?php echo $extras ? $extras['client_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Sub Title</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[client_section][sub_title]"><?php echo $extras ? $extras['client_section']['sub_title'] : ''; ?></textarea>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Usefull Links Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[usefull_links_section][title]" value="<?php echo $extras ? $extras['usefull_links_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Sub-Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[usefull_links_section][sub_title]" value="<?php echo $extras ? $extras['usefull_links_section']['sub_title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Select Menu</label>
                <div class="<?php echo $col_2; ?>">
                    <select class="form-control" name="extras[usefull_links_section][theMenu]">
                        <option>N/A</option>
                        <?php
                        foreach($menus as $i=>$v){
                            $selected = $extras['usefull_links_section']['theMenu'] && $extras['usefull_links_section']['theMenu'] == $v['pk_menu_id'] ? 'selected' : '';
                            echo '<option value="'.$v['pk_menu_id'].'" '.$selected.'>'.$v['menu_title'].'</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Contact Information Section</legend>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Title</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[contact_section][title]" value="<?php echo $extras ? $extras['contact_section']['title'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Descriptive Text</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[contact_section][content]"><?php echo $extras ? $extras['contact_section']['content'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Contact Number</label>
                <div class="<?php echo $col_2; ?>">
                    <input type="text" class="form-control" name="extras[contact_section][contact]" value="<?php echo $extras ? $extras['contact_section']['contact'] : ''; ?>"/>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Open Hours</label>
                <div class="<?php echo $col_2; ?>">
                    <textarea class="form-control" name="extras[contact_section][open_hours]"><?php echo $extras ? $extras['contact_section']['open_hours'] : ''; ?></textarea>
                </div>
            </div>
            <div class="row form-group">
                <label class="<?php echo $col_1; ?> control-label">Background Image</label>
                <div class="<?php echo $col_2; ?>">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="image_upload_container controlVisible">
                                <div class="controlBtnContainer">
                                    <div class="controlBtn">
                                        <a href="<?php echo _path('common_files');?>/filemanager/dialog.php?type=1&field_id=js_contact_section_photo&relative_url=1&akey=<?php echo $_config['__FILEMANGER_KEY__']; ?>" data-img-size="100x100x1" class="addBtn img-iframe-btn text-success"><i class=" fa-fw fa fa-plus-circle"></i><span class="controlBtnText">&nbsp;Upload/Select</span></a>
                                        <a href="javascript:" class="trashBtn text-danger"><i class=" fa-fw fa fa-times-circle"></i><span class="controlBtnText">&nbsp;Remove</span></a>
                                    </div>
                                </div>
                                <?php if($content && $extras['contact_section']['bg_image']): ?>
                                    <img class="" src="<?php echo get_image($extras['contact_section']['bg_image'],'100x100x1')?>" />
                                <?php endif; ?>
                                <input id="js_contact_section_photo" name="extras[contact_section][bg_image]" type="hidden" class="form-control" value="<?php echo $content && $extras['contact_section']['bg_image'] ? $extras['contact_section']['bg_image'] : ''?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <?php
    }
}
new template_job_seekers();

//registering dashboard
class students_dashboard extends public_routers{
    function init(){
        $this->url_path = 'studentszone';
        }
    function hasAccess(){
        global $_config;
        if(HAS_USER()){
            $studentManager = jack_obj('dev_student_management');
            if(!$studentManager->default_role) return false;
            else{
                if(in_array($studentManager->default_role, $_config['user']['roles_list']) === false) return false;
                else return true;
                }
            }
        else return false;
        }
    }
new students_dashboard();

class instructor_dashboard extends public_routers{
    function init(){
        $this->url_path = 'instructorszone';
        }
    function hasAccess(){
        global $_config;
        if(HAS_USER()){
            $instructorManager = jack_obj('dev_instructor_management');
            if(!$instructorManager->default_role) return false;
            else{
                if(in_array($instructorManager->default_role, $_config['user']['roles_list']) === false) return false;
                else return true;
                }
            }
        else return false;
        }
    }
new instructor_dashboard();
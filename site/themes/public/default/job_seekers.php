<?php
$_SESSION['view_type'] = 'job_seekers';
$gallerySection = thePageExtras('gallery_section');
$howBplWorksSection = thePageExtras('how_bpl_works_section');
$aboutCompanySection = thePageExtras('about_company_section');
$messageSection = thePageExtras('message_section');
$clientSection = thePageExtras('client_section');
$usefullLinksSection = thePageExtras('usefull_links_section');
$contactSection = thePageExtras('contact_section');
$currentJobSection = thePageExtras('current_job_section');

$cManager = jack_obj('dev_content_management');
$tagger = jack_obj('dev_tag_management');
$occupation = $tagger->get_tags(array('tag_group_slug' => 'occupation'));
$WORLD_COUNTRY_LIST = getWorldCountry();

include ('header_inner_pages.php');
?>
<?php if($gallerySection): ?>
    <?php
    if($gallerySection['gallery']){
        $params = array(
            'published_till_now' => true,
            'content_id' => $gallerySection['gallery'],
            'include_child' => true,
            'single' => true,
            );
        $theGallery = $cManager->get_contents($params);
        if($theGallery && $theGallery['childs']){
            ?>
            <div class="row homepageslideshowcontainer">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-5 col-md-6 col-xs-12 slideTextContainer">
                            <?php
                            foreach($theGallery['childs'] as $i=>$v){
                                $ex = json_decode($v['content_extra_settings'], true)
                                ?>
                                <div class="eachSlideItem">
                                    <p class="slideTitle"><?php echo processToRender($v['content_title']); ?></p>
                                    <p class="slideText"><?php echo processToRender($v['content_description']); ?></p>
                                    <?php if($ex['item_url']):?>
                                        <a class="btn2" href="<?php echo $ex['item_url']; ?>"><?php echo ML('learn-more'); ?></a>
                                    <?php endif; ?>
                                </div>
                                <?php
                                }
                            ?>
                        </div>
                        <div class="col-xl-7 col-md-6 col-xs-12 slideImageContainer">
                            <div class="eachSlideItem">
                                <div class="slideImage">
                                    <div class="images">
                                        <?php
                                        foreach($theGallery['childs'] as $i=>$v){
                                            ?>
                                            <img class="eachSlideItemImage" src="<?php echo get_image($v['content_thumbnail'],'725x485'); ?>" />
                                            <?php
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script type="text/javascript">
                            init.push(function(){
                                new devSplitSlider({
                                    sectionOne: $('.homepageslideshowcontainer .slideTextContainer .eachSlideItem'),
                                    sectionTwo: $('.homepageslideshowcontainer .slideImageContainer .eachSlideItemImage'),
                                    sectionOneEffect: 'slide',
                                    sectionTwoEffect: 'fade',
                                    });
                                });
                        </script>
                    </div>
                </div>
            </div>
            <?php
            }
        }
    ?>
<?php endif; ?>
<div class="row find_better_section_container">
    <div class="col-lg-12 pl0 pr0 find_better_section_inner_container">
        <div class="find_better_section container tac" >
            <div class="dib">
                <form class="searchDetailForm aligner" name="searchDetailForm" action="" method="get">
                    <div class="form-group mb10">
                        <label class="radio-inline mr30">
                            <input type="radio" class="dn searchDetailSearchType" name="search_type" value="job" checked/>
                            <span class="lbl"><?php echo ML('search_job'); ?></span>
                            <span class="highlightChecked"></span>
                        </label>
                        <!--label class="radio-inline">
                            <input type="radio" class="dn searchDetailSearchType" name="search_type" value="talent"/>
                            <span class="lbl"><?php echo ML('find_talent'); ?></span>
                            <span class="highlightChecked"></span>
                        </label-->
                    </div>
                    <div class="form-group findBetterSelect2Container mb0">
                        <div class="input-group">
                            <span class="fa fa-search theSearchIcon"></span>
                            <span class="input-group-addon clearThisFilter <?php echo $_GET['country'] ? '' : 'dn'; ?>"><i class="fa fa-times"></i></span>
                            <select class="adv_select" name="occupation">
                                <option value=""><?php echo ML('occupation'); ?></option>
                                <?php
                                foreach($occupation['data'] as $i=>$v){
                                    $selected = $_GET['occupation'] && $_GET['occupation'] == $v['pk_tag_id'] ? 'selected' : '';
                                    echo '<option value="'.$v['pk_tag_id'].'" '.$selected.'>'.processToRender($v['tag_title']).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <span class="fa fa-search theSearchIcon"></span>
                            <span class="input-group-addon clearThisFilter <?php echo $_GET['country'] ? '' : 'dn'; ?>"><i class="fa fa-times text-danger"></i></span>
                            <select class="adv_select" name="country">
                                <option value=""><?php echo ML('country'); ?></option>
                                <?php
                                foreach($WORLD_COUNTRY_LIST as $i=>$v){
                                    $selected = $_GET['country'] && $_GET['country'] == $i ? 'selected' : '';
                                    echo '<option value="'.$i.'" '.$selected.'>'.$v.'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <input type="submit" name="search" value="<?php echo ML('search'); ?>" />
                        <script type="text/javascript">
                            init.push(function(){
                                $('.searchDetailSearchType').change(function(){
                                    if($('.searchDetailSearchType:checked').val() == 'job')
                                        $(this).closest('form').attr('action', '<?php echo url('job-circulars');?>');
                                    else $(this).closest('form').attr('action', '<?php echo url('talents');?>');
                                }).change();
                            });
                        </script>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if($aboutCompanySection || $messageSection): ?>
    <div class="row sectionBorder static_page_container">
        <div class="container">
            <?php if($aboutCompanySection): ?>
                <div class="row mb60">
                    <div class="col-lg-6 col-sm-12">
                        <p class="fs_8 pink_title"><?php echo $aboutCompanySection['title']; ?></p>
                        <p class="mb20 fs_9"><?php echo nl2br($aboutCompanySection['sub_title']); ?></p>
                        <p class="mb30 fs_10"><?php echo nl2br($aboutCompanySection['content']); ?></p>
                        <div class="text-lg-left text-xs-center">
                            <?php if($aboutCompanySection['page_one']):
                                $pageOne = $pageManager->get_a_page($aboutCompanySection['page_one'],array('tiny' => true));
                                ?>
                                <a href="<?php echo page_url($pageOne); ?>" class="mr20 btn2"><?php echo processToRender($pageOne['page_title']); ?></a>
                            <?php endif; ?>
                            <?php if($aboutCompanySection['page_two']):
                                $pageTwo = $pageManager->get_a_page($aboutCompanySection['page_two'],array('tiny' => true));
                                ?>
                                <a href="<?php echo page_url($pageTwo); ?>" class="mr20 btn2"><?php echo processToRender($pageTwo['page_title']); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <?php if($aboutCompanySection['photo']): ?>
                            <div class="photoStyle_1 text-lg-right text-xs-center">
                                <iframe width="100%" height="315" src="https://www.youtube.com/embed/w-frx42Y6Hk" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if($messageSection): ?>
                <div class="row">
                    <div class="col-lg-4 col-sm-12">
                        <?php if($messageSection['photo']): ?>
                            <div class="photoStyle_2 text-lg-left text-xs-center">
                                <img src="<?php echo get_image($messageSection['photo'],'315x316'); ?>" />
                                <span class="fa fa-quote-right quoteHigh"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-8 col-sm-12">
                        <p class="fs_11 mb20"><?php echo $messageSection['title']; ?></p>
                        <p class="fs_12 mb20 aboutUsMessage"><em>“<?php echo nl2br($messageSection['content']); ?>”</em></p>
                        <p class="mb0"><span class="fs_13"><?php echo $messageSection['message_by']; ?>,</span> <span class="fs_14"><?php echo $messageSection['message_by_company']; ?></span></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
    <?php
    $params = array(
        'meta' => array('DATE' => array(
            'LESS_THAN_EQUAL' => array(
                array(
                    'meta' => 'content_expire_date',
                    'value' => date('Y-m-d'),
                    ),
                ),
            )),
        'published_till_now' => true,
        'content_types' => 'Job-Circular',
        'include_meta' => true,
        );
    $jobCirculars = $cManager->get_contents($params);
    if($jobCirculars['data']){
        ?>
        <div class="row allCurrentJobContainer">
            <div class="container">
                <?php if($currentJobSection): ?>
                <div class="allCurrentJobTitle pink_title"><?php echo $currentJobSection['title']; ?></div>
                <?php endif; ?>
                <div class="row owl-carousel owl-theme allVacancyJobs fixMyChildHeights" data-height-type="outer" data-fix-child=".vacancyJobs">
                    <?php
                    foreach($jobCirculars['data'] as $i=>$item){
                        ?>
                        <div class="vacancyJobs">
                            <div class="vacancyTitle aligner"><?php echo processToRender($item['content_title']); ?></div>
                            <div class="vacancyDescription aligner"><?php echo processToRender($item['content_excerpt']); ?></div>
                            <a href="<?php echo detail_url($item); ?>" class="vacancyLearnMore aligner"><?php echo ML('learn-more'); ?> <i class="fa fa-chevron-right"></i></a>
                        </div>
                        <?php
                        }
                    ?>
                </div>
                <script type="text/javascript">
                    init.push(function(){
                        $('.allVacancyJobs').owlCarousel({
                            responsive : {
                                0 : {
                                    items : 1,
                                },
                                576 : {
                                    items : 2,
                                },
                                992 : {
                                    items: 3
                                },
                            }
                        });
                    });
                </script>
            </div>
        </div>
        <?php
        }
    ?>


    <?php if($howBplWorksSection): ?>
    <div class="row bestPeopleContainer">
        <div class="container">
            <p class="mb20 fs_8 tac pink_title"><?php echo $howBplWorksSection['title']; ?></p>
            <p style="margin-bottom: 110px" class="fs_15 tac"><?php echo nl2br($howBplWorksSection['sub_title']); ?></p>
            <div class="row howWeHelpContainer fixMyChildHeights" data-height-type="outer" data-fix-child=".howWeHelp">
                <div class="col-sm-4 col-xs-12 howWeHelp">
                    <div class="howWeHelpIcon">
                        <img src="<?php echo $howBplWorksSection['work_model_one']['icon'] ? get_image($howBplWorksSection['work_model_one']['icon'],'50x50') : theme_path().'/images/icon_1.png'; ?>" />
                    </div>
                    <div class="howWeHelpTitle"><?php echo $howBplWorksSection['work_model_one']['title']; ?></div>
                    <div class="howWeHelpDescription"><?php echo nl2br($howBplWorksSection['work_model_one']['content']); ?></div>
                </div>
                <div class="col-sm-4 col-xs-12 howWeHelp">
                    <div class="howWeHelpIcon">
                        <img src="<?php echo $howBplWorksSection['work_model_two']['icon'] ? get_image($howBplWorksSection['work_model_two']['icon'],'50x50') : theme_path().'/images/icon_2.png'; ?>" />
                    </div>
                    <div class="howWeHelpTitle"><?php echo $howBplWorksSection['work_model_two']['title']; ?></div>
                    <div class="howWeHelpDescription"><?php echo nl2br($howBplWorksSection['work_model_two']['content']); ?></div>
                </div>
                <div class="col-sm-4 col-xs-12 howWeHelp">
                    <div class="howWeHelpIcon">
                        <img src="<?php echo $howBplWorksSection['work_model_three']['icon'] ? get_image($howBplWorksSection['work_model_three']['icon'],'50x50') : theme_path().'/images/icon_3.png'; ?>" />
                    </div>
                    <div class="howWeHelpTitle"><?php echo $howBplWorksSection['work_model_three']['title']; ?></div>
                    <div class="howWeHelpDescription"><?php echo nl2br($howBplWorksSection['work_model_three']['content']); ?></div>
                </div>
            </div>
            <?php
            $output = '';
            if($howBplWorksSection['service_one']){
                $params = array(
                    'published_till_now' => true,
                    'content_id' => $howBplWorksSection['service_one'],
                    'single' => true,
                );
                $theService = $cManager->get_contents($params);
                if($theService){
                    $thisID = 'service_'.$theService['pk_content_id'].'_'.time().'_'.rand(1,10);
                    ob_start();
                    ?>
                    <style type="text/css">
                        #<?php echo  $thisID;?>::after{
                            background-image: url('<?php echo $theService['content_thumbnail'] ? get_image($theService['content_thumbnail'], '570x394') : theme_path('images/img_3.png'); ?>');
                        }
                    </style>
                    <div class="col-lg-6 col-sm-12">
                        <div class="boxModel_1" id="<?php echo $thisID; ?>">
                            <div class="boxModel_1_inner">
                                <p class="boxModel_1_title"><?php echo processToRender($theService['content_title']); ?></p>
                                <p class="boxModel_1_text"><?php echo processToRender($theService['content_excerpt']); ?></p>
                                <a href="<?php echo detail_url($theService); ?>" class="btn3"><?php echo ML('learn-more'); ?></a>
                            </div>
                        </div>
                    </div>
                    <?php
                    $output .= ob_get_clean();
                }
            }
            if($howBplWorksSection['service_two']){
                $params = array(
                    'published_till_now' => true,
                    'content_id' => $howBplWorksSection['service_two'],
                    'single' => true,
                );
                $theService = $cManager->get_contents($params);
                if($theService){
                    $thisID = 'service_'.$theService['pk_content_id'].'_'.time().'_'.rand(1,10);
                    ob_start();
                    ?>
                    <style type="text/css">
                        #<?php echo  $thisID;?>::after{
                            background-image: url('<?php echo $theService['content_thumbnail'] ? get_image($theService['content_thumbnail'], '570x394') : theme_path('images/img_3.png'); ?>');
                        }
                    </style>
                    <div class="col-lg-6 col-sm-12">
                        <div class="boxModel_1" id="<?php echo $thisID; ?>">
                            <div class="boxModel_1_inner">
                                <p class="boxModel_1_title"><?php echo processToRender($theService['content_title']); ?></p>
                                <p class="boxModel_1_text"><?php echo processToRender($theService['content_excerpt']); ?></p>
                                <a href="<?php echo detail_url($theService); ?>" class="btn3"><?php echo ML('learn-more'); ?></a>
                            </div>
                        </div>
                    </div>
                    <?php
                    $output .= ob_get_clean();
                }
            }

            if(strlen($output)){
                ?>
                <div class="row fixMyChildHeights" data-height-type="outer" data-fix-child=".boxModel_1_inner">
                    <div class="container">
                        <div class="row">
                            <?php echo $output; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
    </div>
    </div>
    <?php endif; ?>

<?php
$params = array(
    'include_meta' => true,
    'published_till_now' => true,
    'content_types' => 'partners',
    );
$clients = $cManager->get_contents($params);

$output = '';
if($clients['data']){
    ob_start();
    foreach($clients['data'] as $i=>$item){
        $clientLink = getMetaValue($item, 'client_link');
        $clientLink = strlen($clientLink) ? $clientLink : 'javascript:';
        ?>
        <div class="">
            <a target="_blank" href="<?php echo $clientLink; ?>"><img title="<?php echo processToRender($item['content_title']); ?>" class="" src="<?php echo get_image($item['content_thumbnail'],'185x132x2') ?>" /></a>
        </div>
        <?php
    }
    $output .= ob_get_clean();
}

if(strlen($output)){
    ?>
    <div class="row partnerContainer">
        <div class="container">
            <?php if($clientSection): ?>
                <p class="tac mb20 partnerContainerTitle"><?php echo $clientSection['title']; ?></p>
                <p class="tac mb20 partnerContainerSubTitle"><?php echo nl2br($clientSection['sub_title']); ?></p>
            <?php endif; ?>
            <div class="owl-carousel owl-theme partner_carousel">
                <?php echo $output ?>
            </div>
            <script type="text/javascript">
                init.push(function(){
                    $('.partner_carousel').owlCarousel({
                        responsive : {
                            0 : {
                                items : 3,
                            },
                            576 : {
                                items : 4,
                            },
                            768 : {
                                items: 5
                            },
                            992 : {
                                items: 6
                            }
                        }});
                });
            </script>
        </div>
    </div>
    <?php
    }
?>

    <?php if($usefullLinksSection):?>
    <div class="row usefullLinksSection">
        <div class="container">
            <p class="mb20 fs_8 tac pink_title"><?php echo $usefullLinksSection['title']; ?></p>
            <p style="margin-bottom: 60px" class="fs_15 tac"><?php echo nl2br($usefullLinksSection['sub_title']); ?></p>
            <?php
            if(isset($usefullLinksSection['theMenu']) && $usefullLinksSection['theMenu']){
                $menuManager = jack_obj('dev_menu_management');
                $pageManager = jack_obj('dev_page_management');
                $items = $menuManager->get_menuItems($usefullLinksSection['theMenu'], true);
                if($items){
                    ?>
                    <div class="owl-carousel owl-theme usefulllinks_carousel">
                        <?php
                        $currentLink = 0;
                        $maxLink = 2;
                        foreach($items as $i=>$v){
                            $item_title = '';
                            $item_link = '';
                            if($v['fk_page_id']){
                                $page = $pageManager->get_a_page($v['fk_page_id'],array('tiny' => true));
                                $item_title = $page['page_title'];
                                if(!$v['use_page_title']) $item_title = $v['item_title'];
                                $item_link = page_url($page);
                            }
                            else{
                                $item_title = $v['item_title'];
                                if(strlen($v['item_ext_url'])) $item_link = $v['item_ext_url'];
                                else $item_link = ':javascript:';
                            }

                            $item_title = processToRender($item_title);
                            ?>
                            <?php if(!$currentLink): ?><div class="eachUseFullLinksItemsContainer"><?php  endif; ?>
                            <a class="eachUseFullLinks" href="<?php echo $item_link; ?>"><?php echo $item_title; ?></a>
                            <?php
                            $currentLink += 1;
                            if($currentLink == $maxLink){
                                $currentLink = 0;
                                ?></div><?php
                            }
                        }
                        if($currentLink) echo '</div>';
                        ?>
                        <script type="text/javascript">
                            init.push(function(){
                                $('.usefulllinks_carousel').owlCarousel({
                                    responsive : {
                                        0 : {
                                            items : 1,
                                        },
                                        576 : {
                                            items : 2,
                                        },
                                        768 : {
                                            items: 3
                                        },
                                        992 : {
                                            items: 4
                                        }
                                    }});
                            });
                        </script>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <?php  endif; ?>

    <div class="row contactForInformationContainer" style="<?php echo isset($contactSection['bg_image']) && $contactSection['bg_image'] ? "background-image: url('".get_image($contactSection['bg_image'],'1920x750')."')" : ''; ?>">
        <div class="contactForInformationContainerInner col-lg-12">
            <div class="contactForInformation container">
                <div class="row">
                    <div class="col-lg-6 col-sm-12 text-lg-right text-xs-left">
                        <?php if($contactSection): ?>
                            <div class="contactForInformationAd">
                                <p class="contactForInformationAdTitle aligner"><?php echo $contactSection['title']; ?></p>
                                <p class="contactForInformationAdSubTitle aligner"><?php echo nl2br($contactSection['content']); ?></p>
                                <p class="iconPhone aligner"><span class="fa fa-phone" style="font-size: 2.7em;color: #fff;"></span></p>
                                <p class="m0 contactInformationPhoneNumber aligner"><?php echo $contactSection['contact']; ?></p>
                                <p class="m0 contactInformationTime aligner"><?php echo nl2br($contactSection['open_hours']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-6 col-sm-12 text-lg-left text-xs-center">
                        <div class="contactForInformationFormContainer">
                            <form onsubmit="return false;" id="contactForInformationForm" name="contactForInformationForm" method="post" action="">
                                <input type="hidden" name="contact_type" value="job_seeker" />
                                <p class="formTitle aligner"><?php echo ML('job_seekers_contact_form_title'); ?></p>
                                <input type="text" name="contact_name" class="aligner" value="" placeholder="<?php echo ML('name'); ?>" />
                                <input type="email" name="contact_email" class="aligner" value="" placeholder="<?php echo ML('email'); ?>" />
                                <input type="text" name="contact_mobile" class="aligner" value="" placeholder="<?php echo ML('mobile_number'); ?>" />
                                <input type="text" name="contact_subject" class="aligner" value="" placeholder="<?php echo ML('subject'); ?>" />
                                <textarea class="aligner" name="contact_content" placeholder="<?php echo ML('message'); ?>"></textarea>
                                <button type="button" class="contact_form_submit" id="contact_form_submit"><?php echo ML('job_seeker_contact_btn'); ?></button>
                                <div class="contactMessageHolder contact-error dn"></div>
                                <p class="formHint m0 aligner"><?php echo ML('no_spam'); ?></p>
                            </form>
                        </div>
                    </div>
                    <script type="text/javascript">
                        init.push(function(){
                            $('#contact_form_submit').on('click', function(){
                                var ths = $(this);
                                var theForm = $('#contactForInformationForm');
                                var contactMsgHolder = $('.contactMessageHolder');

                                var data = {
                                    internalToken : _internalToken_,
                                    contact_type: $('[name="contact_type"]', theForm).val(),
                                    contact_name: $('[name="contact_name"]', theForm).val(),
                                    contact_email: $('[name="contact_email"]', theForm).val(),
                                    contact_mobile: $('[name="contact_mobile"]', theForm).val(),
                                    contact_subject: $('[name="contact_subject"]', theForm).val(),
                                    contact_content: $('[name="contact_content"]', theForm).val(),
                                    api_call: '1',
                                    contact_form_submit: '1',
                                    };
                                $.ajax({
                                    beforeSend: function(){
                                        contactMsgHolder.addClass('dn');
                                        contactMsgHolder.removeClass('contact-error');
                                        contactMsgHolder.removeClass('contact-success');
                                        contactMsgHolder.html('');
                                        theForm.find('input, button').attr('disabled', true);
                                        //show_button_overlay_working(ths);
                                        },
                                    complete: function(){
                                        contactMsgHolder.removeClass('dn');
                                        theForm.find('input, button').attr('disabled', false);
                                        //hide_button_overlay_working(ths);
                                        },
                                    url: window.location.href,
                                    type: 'post',
                                    dataType: 'JSON',
                                    data: data,
                                    success: function(ret){
                                        if(ret.error){
                                            for(var i=0;i<ret.error.length; i++){
                                                contactMsgHolder.append('<p><i class="fa fa-exclamation-circle"></i>&nbsp;'+ret.error[i]+'</p>');
                                            }
                                            contactMsgHolder.addClass('contact-error');
                                        }
                                        else{
                                            contactMsgHolder.append('<p><i class="fa fa-check-circle"></i>&nbsp;We have received your message, thank for contacting us.</p>');
                                            contactMsgHolder.addClass('contact-success');
                                        }
                                    },
                                    error: function(){
                                        contactMsgHolder.append('<p><i class="fa fa-exclamation-circle"></i>&nbsp;Network error, please try again</p>');
                                        contactMsgHolder.addClass('contact-error');
                                    },
                                });
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
	<div class="row">
		<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3187.8251607758875!2d89.91655381461841!3d24.251883484344592!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39fdfbe4dba8f139%3A0x92ee9af2e74718b9!2sBrac+Probashbandhu+Limited%2C+MSC+Tangail+Office!5e1!3m2!1sen!2sbd!4v1529729981532" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
	</div>
	
<?php include ('footer_inner_pages.php')?>
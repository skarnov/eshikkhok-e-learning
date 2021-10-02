<?php
$_SESSION['view_type'] = 'recruiters';
$findBetter = thePageExtras('find_better_section');
$serviceSection = thePageExtras('service_section');
$aboutCompanySection = thePageExtras('about_company_section');
$messageSection = thePageExtras('message_section');
$clientSection = thePageExtras('client_section');
$contactSection = thePageExtras('contact_section');
$pageManager = jack_obj('dev_page_management');
$cManager = jack_obj('dev_content_management');
$tagger = jack_obj('dev_tag_management');
$occupation = $tagger->get_tags(array('tag_group_slug' => 'occupation'));

$WORLD_COUNTRY_LIST = getWorldCountry();
include ('header_inner_pages.php');

?>
<?php if($findBetter): ?>
    <div class="row recruiters find_better_section_container" style="<?php echo $findBetter['bg_type'] == 'image' && $findBetter['bg_image'] ? "background-image: url('".get_image($findBetter['bg_image'],'1920x460')."')" : ''; ?>">
        <div class="col-lg-12 pl0 pr0 recruiters find_better_section_inner_container">
            <div class="find_better_section container tac" >
                <div class="find_better_section_title tac"><?php echo $findBetter['title']; ?></div>
                <div class="find_better_section_sub_title tac"><?php echo nl2br($findBetter['sub_title']); ?></div>
                <div class="findBetterContainer">
                    <form class="aligner searchDetailForm" name="searchDetailForm" action="" method="get">
                        <div class="form-group mb10">
                            <!---  <label class="radio-inline mr30">
                                <input type="radio" class="dn searchDetailSearchType" name="search_type" value="job" />
                                <span class="lbl"><?php echo ML('search_job'); ?></span>
                                <span class="highlightChecked"></span>
                            </label> !--->
                            <label class="radio-inline">
                                <input type="radio" class="dn searchDetailSearchType" name="search_type" value="talent" checked/>
                                <span class="lbl"><?php echo ML('find_talent'); ?></span>
                                <span class="highlightChecked"></span>
                            </label>
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
                            <input type="submit" name="search" value="Search" />
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
        <?php
        if($findBetter['bg_type'] == 'gallery' && $findBetter['bg_gallery']){
            $params = array(
                'published_till_now' => true,
                'content_id' => $findBetter['bg_gallery'],
                'include_child' => true,
                'single' => true,
                );
            $theGallery = $cManager->get_contents($params);
            if($theGallery && $theGallery['childs']){
                ?>
                <style type="text/css">
                    .recruiters_find_better_gallery_container{
                        z-index: 0;
                        }
                    .recruiters_find_better_gallery_container,
                    .recruiters_find_better_gallery_container .owl-stage-outer,
                    .recruiters_find_better_gallery_container .owl-stage,
                    .recruiters_find_better_gallery_each_img{
                        position: absolute !important;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        }
                    .recruiters_find_better_gallery_container .owl-item{
                        width: 100%;
                        height: 100%;
                        }
                    .recruiters_find_better_gallery_each_img{
                        background: none;
                        background-size: cover;
                        background-repeat: no-repeat;
                        background-position: center center;
                        filter: none;
                        }
                </style>
                <div class="recruiters_find_better_gallery_container owl-carousel owl-theme">
                <?php
                foreach($theGallery['childs'] as $i=>$v){
                    ?>
                    <div style="background-image: url('<?php echo get_image($v['content_thumbnail'],'1920x460'); ?>')" class="recruiters_find_better_gallery_each_img">

                    </div>
                    <?php
                    }
                ?>
                </div>
                <script type="text/javascript">
                    init.push(function(){
                        $('.recruiters_find_better_gallery_container').owlCarousel({
                            items: 1,
                            slideBy: 1,
                            //autoWidth: true,
                            nav: false,
                            //navContainer: ths.closest('.productDescriptionContainer').find('.productSlideNav'),
                            //navText: ["<a class='owlNavBtn'><i class='fa fa-chevron-left'></i></a>","<a class='owlNavBtn'><i class='fa fa-chevron-right'></i></a>"],
                            dots: false,
                            autoplay: true,
                            autoplayTimeout: 4000,
                            autoplayHoverPause: false,
                            loop:true,
                            });
                        });
                </script>
                <?php
                }
            }
            ?>

    </div>
    <div class="row find_better_section_footer_container">
        <div class="find_better_section_footer container tac">
            <div class="col-12"><?php echo $findBetter['bottom_line']; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
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
                                    <img src="<?php echo get_image($aboutCompanySection['photo'], '486x325'); ?>" />
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
                                    <img src="<?php echo get_image($messageSection['photo'],'315x315'); ?>" />
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
    if($serviceSection){
        $output = '';
        if($serviceSection['service_one']){
            $params = array(
                'published_till_now' => true,
                'content_id' => $serviceSection['service_one'],
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
                    <div class="boxModel_1" id="<?php echo $thisID; ?>" >
                        <div class="boxModel_1_inner">
                            <p class="boxModel_1_title"><?php echo processToRender($theService['content_title']); ?></p>
                            <p class="boxModel_1_text"><?php echo processToRender($theService['content_excerpt']); ?></p>
                            <a href="<?php echo detail_url($theService); ?>" class="btn3">Learn More</a>
                        </div>
                    </div>
                </div>
                <?php
                $output .= ob_get_clean();
            }
        }
        if($serviceSection['service_two']){
            $params = array(
                'published_till_now' => true,
                'content_id' => $serviceSection['service_two'],
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
                    <div class="boxModel_1" id="<?php echo $thisID; ?>" >
                        <div class="boxModel_1_inner">
                            <p class="boxModel_1_title"><?php echo processToRender($theService['content_title']); ?></p>
                            <p class="boxModel_1_text"><?php echo processToRender($theService['content_excerpt']); ?></p>
                            <a href="<?php echo detail_url($theService); ?>" class="btn3">Learn More</a>
                        </div>
                    </div>
                </div>
                <?php
                $output .= ob_get_clean();
            }
        }

        if(strlen($output)){
            ?>
            <div class="row bestPeopleContainer fixMyChildHeights" data-height-type="outer" data-fix-child=".boxModel_1_inner">
                <div class="container">
                    <p class="mb20 fs_8 tac pink_title"><?php echo $serviceSection['title']; ?></p>
                    <p class="mb70 fs_15 tac"><?php echo nl2br($serviceSection['sub_title']); ?></p>
                    <div class="row">
                        <?php echo $output; ?>
                    </div>
                </div>
            </div>
            <?php
            }
        }
    ?>
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
                    <p class="tac mb20 partnerContainerTitle pink_title"><?php echo $clientSection['title']; ?></p>
                    <p class="tac mb20 partnerContainerSubTitle sub_title"><?php echo nl2br($clientSection['sub_title']); ?></p>
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
                                <p class="m0 contactInformationTime aligner"><?php echo $contactSection['open_hours']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-6 col-sm-12 text-lg-left text-xs-center">
                        <div class="contactForInformationFormContainer">
                            <form onsubmit="return false;" id="contactForInformationForm" name="contactForInformationForm" method="post" action="">
                                <input type="hidden" name="contact_type" value="recruiter" />
                                <p class="formTitle aligner pink_title"><?php echo ML('recruiters_contact_form_title'); ?></p>
                                <input type="text" name="contact_name" class="aligner" value="" placeholder="<?php echo ML('name'); ?>" />
                                <input type="email" name="contact_email" class="aligner" value="" placeholder="<?php echo ML('email'); ?>" />
                                <input type="text" name="contact_company" class="aligner" value="" placeholder="<?php echo ML('name_of_organization'); ?>" />
                                <input type="text" name="contact_country" class="aligner" value="" placeholder="<?php echo ML('country'); ?>" />
                                <input type="text" name="contact_skills" class="aligner" value="" placeholder="<?php echo ML('skill_requirements'); ?>" />
                                <textarea name="contact_content" class="aligner" placeholder="<?php echo ML('message'); ?>"></textarea>
                                <button type="button" class="contact_form_submit" id="contact_form_submit"><?php echo ML('recruiters_contact_btn'); ?></button>
                                <div class="contactMessageHolder contact-error dn">

                                </div>
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
                                    contact_company: $('[name="contact_company"]', theForm).val(),
                                    contact_country: $('[name="contact_country"]', theForm).val(),
                                    contact_skills: $('[name="contact_skills"]', theForm).val(),
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
<?php include ('footer_inner_pages.php')?>
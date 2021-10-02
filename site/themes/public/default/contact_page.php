<?php
$thePage = $_config['current_page'];
$getInTouchSettings = thePageExtras('get_in_touch');
$feebackSettings = thePageExtras('feedback');
include ('header_inner_pages.php')
?>
    <style type="text/css">
        .find_better_section_container{
        <?php if(strlen($thePage['page_thumbnail'])): ?>
            background-image: url('<?php echo get_image($thePage['page_thumbnail'],'1920x450x1');?>');
        <?php else: ?>
            background-image: none;
            background-color: #414141;
        <?php endif; ?>
        }
    </style>
    <div class="row find_better_section_container">
        <div class="col-lg-12 pl0 pr0 find_better_section_inner_container">
            <div class="find_better_section container tac" >
                <div class="find_better_section_title tac"><?php echo processToRender($thePage['page_title']); ?></div>
                <div class="find_better_section_sub_title tac"><?php echo processToRender($thePage['page_sub_title']); ?></div>
            </div>
        </div>
    </div>
    <div class="row static_page_container">
        <div class="container">
            <?php echo processToRender($thePage['page_description']); ?>
        </div>
    </div>
    <div class="row" style="padding-bottom: 110px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 contactUsGetInTouch aligner">
                    <div class="contactUsGetInTouchTitle mb30">
                        <?php echo ML('get_in_touch'); ?>
                    </div>
                    <div class="contactUsText mb20"><?php echo $getInTouchSettings['text']; ?></div>
                    <div class="oh footerGetInTouch">
                        <div class="getInTouchRow">
                            <div class="getInTouchLeft"><?php echo ML('address'); ?></div>
                            <div class="getInTouchRight"><?php echo processToRender(nl2br($get_in_touch['address'])); ?></div>
                        </div>
                        <div class="getInTouchRow">
                            <div class="getInTouchLeft"><?php echo ML('phone'); ?></div>
                            <div class="getInTouchRight"><?php echo processToRender(nl2br($get_in_touch['phone'])); ?></div>
                        </div>
                        <div class="getInTouchRow">
                            <div class="getInTouchLeft"><?php echo ML('email'); ?></div>
                            <div class="getInTouchRight"><?php echo processToRender(nl2br($get_in_touch['email'])); ?></div>
                        </div>
                        <div class="getInTouchRow">
                            <div class="getInTouchLeft"><?php echo ML('follow_us'); ?></div>
                            <div class="getInTouchRight">
                                <?php printSocialLinks() ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 contactUsContactForm aligner">
                    <div class="contactUsContactFormTitle mb30">
                        <?php echo ML('feedback'); ?>
                    </div>
                    <div class="contactUsText mb20"><?php echo $feebackSettings['text']; ?></div>
                    <form onsubmit="return false" id="contactUsForm" class="contactUsForm" method="post" action="">
                        <div class="row">
                            <div class="col-lg-6">
                                <input class="aligner" type="text" name="contact_name" value="" required placeholder="<?php echo ML('full_name'); ?>"/>
                            </div>
                            <div class="col-lg-6">
                                <input class="aligner" type="email" name="contact_email" value="" placeholder="<?php echo ML('email_address'); ?>"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <input class="aligner" type="text" name="contact_mobile" value="" placeholder="<?php echo ML('your_phone_number'); ?>"/>
                            </div>
                            <div class="col-lg-6">
                                <input class="aligner" type="text" name="contact_website" value="" placeholder="<?php echo ML('your_website'); ?>"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <textarea class="aligner" name="contact_content" placeholder="<?php echo ML('your_message'); ?>"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="contact_form_submit" id="contact_form_submit"><?php echo ML('send_message'); ?></button>
                                <br /><br />
                                <div class="contactMessageHolder contact-error dn"></div>
                            </div>
                        </div>
                    </form>
                    <script type="text/javascript">
                        init.push(function(){
                            $('#contact_form_submit').on('click', function(){
                                var ths = $(this);
                                var theForm = $('#contactUsForm');
                                var contactMsgHolder = $('.contactMessageHolder');

                                var data = {
                                    internalToken : _internalToken_,
                                    contact_type: $('[name="contact_type"]', theForm).val(),
                                    contact_name: $('[name="contact_name"]', theForm).val(),
                                    contact_email: $('[name="contact_email"]', theForm).val(),
                                    contact_mobile: $('[name="contact_mobile"]', theForm).val(),
                                    contact_website: $('[name="contact_website"]', theForm).val(),
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
<?php include ('footer_inner_pages.php');

		<!-- / #content-wrapper -->

        </div> <!-- / #content-wrapper -->


        <div id="main-menu-bg"></div>
        </div> <!-- / #main-wrapper -->
        <div class="footerWrapper">
            <span style="text-align:center; padding:10px;"> &copy; 2016 3DEVs IT Ltd., All Rights Reserved || Developed By <a
                        href="http://3-devs.com/" target="b_blank">3DEVs IT Ltd.</a></span>
        </div>
        <!-- Get jQuery from Google CDN -->
        <!--[if !IE]> -->
        <script type="text/javascript"> window.jQuery || document.write('<script src="<?php echo theme_path().'/assets/javascripts/jquery-2.0.3.min.js'?>">'+"<"+"/script>"); </script>
        <!-- <![endif]-->
        <!--[if lte IE 9]>

        <script type="text/javascript"> window.jQuery || document.write('<script src="<?php echo theme_path().'/assets/javascripts/jquery-1.8.3.min.js'?>">'+"<"+"/script>"); </script>
        <![endif]-->

        <!-- Pixel Admin's javascripts -->
        <?php
        minify_handler::$jsBundle['thirdParty'] = array(
            theme_path('absolute').'/assets/javascripts/bootstrap.min.js',
            theme_path('absolute').'/assets/javascripts/pixel-admin.min.js',
            common_files('absolute').'/js/jquery.fancybox.min.js',
            common_files('absolute').'/js/jquery-ui.min.js',
            common_files('absolute').'/js/jQuery.print.js',
            common_files('absolute').'/js/jquery-ui-timepicker-addon.js',
            common_files('absolute').'/js/jquery.mjs.nestedSortable.js',
            common_files('absolute').'/js/calendar.min.js',
            );

        minify_handler::renderMinifiedJs('thirdParty', false, 1);
        ?>
        <!--script src="<?php echo theme_path(); ?>/assets/javascripts/bootstrap.min.js"></script>
        <script src="<?php echo theme_path(); ?>/assets/javascripts/pixel-admin.min.js"></script-->
        <script src="<?php echo common_files(); ?>/js/tinymce_4.7.13/tinymce.min.js"></script>
        <!--script src="<?php echo common_files(); ?>/js/fancybox.js"></script-->
        <!--script src="<?php echo common_files(); ?>/js/jquery.mjs.nestedSortable.js"></script-->

        <!--script src="<?php echo common_files(); ?>/js/jquery-ui.min.js"></script-->
        <!--script src="<?php echo common_files(); ?>/js/jquery-ui-timepicker-addon.js"></script-->
        <!--script src="<?php echo common_files(); ?>/js/jQuery.print.js"></script-->
        <script src="<?php echo common_files(); ?>/js/jcookies.js"></script>
        <!--script src="<?php echo common_files(); ?>/js/calendar.min.js"></script-->

        <script src="<?php echo common_files(); ?>/js/common.js?v=20"></script>
        <script src="<?php echo common_files(); ?>/js/tinyMce.js?v=5"></script>
        <script src="<?php echo theme_path(); ?>/assets/javascripts/custom.js?v=22"></script>
        <?php echo get_footer('admin');?>
        <script type="text/javascript">
            $('.activeSortColumn').closest('th').addClass('bg-info');

            var BD_LOCATIONS = <?php echo isset($BD_LOCATION_JSON_REQUIRED) && $BD_LOCATION_JSON_REQUIRED ? getBDLocation() : '{}';?>;
            var bd_new_location_selector = function(opt){
                var config = {
                    'post-office' : null,
                    'police-station' : null,
                    'sub-district' : null,
                    'district' : null,
                    'division' : null,
                    };
                $.extend( true, config, opt );

                var currentDivision = config.division && typeof $(config.division).attr('data-selected') !== 'undefined' ? $(config.division).attr('data-selected') : null;
                var currentDistrict = config.district && typeof $(config.district).attr('data-selected') !== 'undefined' ? $(config.district).attr('data-selected') : null;
                var currentSubDistrict = config['sub-district'] && typeof $(config['sub-district']).attr('data-selected') !== 'undefined' ? $(config['sub-district']).attr('data-selected') : null;
                var currentPoliceStation = config['police-station'] && typeof $(config['police-station']).attr('data-selected') !== 'undefined' ? $(config['police-station']).attr('data-selected') : null;
                var currentPostOffice = config['post-office'] && typeof $(config['post-office']).attr('data-selected') !== 'undefined' ? $(config['post-office']).attr('data-selected') : null;
                var i = null;

                config.updateDistrict = function(){
                    currentDivision = config.division ? $(config.division).val() : null;
                    if(config.district) $(config.district).html('');
                    if(config.district && currentDivision){
                        $(config.district).append('<option value="">Any</option>');
                        for(i in BD_LOCATIONS[currentDivision]){
                            $(config.district).append('<option value="'+i+'" '+(currentDistrict && currentDistrict == i ? 'selected' : '')+'>'+i+'</option>');
                            }
                        }
                    if(currentDistrict) $(config.district).change();
                    };

                config.updateSubDistrict = function(){
                    currentDivision = config.division ? $(config.division).val() : null;
                    currentDistrict = config.district ? $(config.district).val() : null;
                    if(config['sub-district']) $(config['sub-district']).html('');
                    if(config['sub-district'] && currentDivision && currentDistrict){
                        $(config['sub-district']).append('<option value="">Any</option>');
                        for(i in BD_LOCATIONS[currentDivision][currentDistrict]['sub-district']){
                            var thisSubDistrict = BD_LOCATIONS[currentDivision][currentDistrict]['sub-district'][i];
                            $(config['sub-district']).append('<option value="'+thisSubDistrict+'" '+(currentSubDistrict && currentSubDistrict == thisSubDistrict ? 'selected' : '')+'>'+thisSubDistrict+'</option>');
                            }
                        }
                    };

                config.updatePoliceStation = function(){
                    currentDivision = config.division ? $(config.division).val() : null;
                    currentDistrict = config.district ? $(config.district).val() : null;
                    if(config['police-station']) $(config['police-station']).html('');
                    if(config['police-station'] && currentDivision && currentDistrict){
                        $(config['police-station']).append('<option value="">Any</option>');
                        var totalPoliceStation = BD_LOCATIONS[currentDivision][currentDistrict]['police-stations'].length;
                        for(i=0;i<totalPoliceStation;i++){
                            var thisPoliceStation = BD_LOCATIONS[currentDivision][currentDistrict]['police-stations'][i];
                            $(config['police-station']).append('<option value="'+thisPoliceStation+'" '+(currentPoliceStation && currentPoliceStation == thisPoliceStation ? 'selected' : '')+'>'+thisPoliceStation+'</option>');
                            }
                        }
                    };

                config.updatePostOffice = function(){
                    currentDivision = config.division ? $(config.division).val() : null;
                    currentDistrict = config.district ? $(config.district).val() : null;
                    if(config['post-office']) $(config['post-office']).html('');
                    if(config['post-office'] && currentDivision && currentDistrict){
                        $(config['post-office']).append('<option value="">Any</option>');
                        for(i in BD_LOCATIONS[currentDivision][currentDistrict]['post-office']){
                            var thisPostOffice = i;
                            $(config['post-office']).append('<option value="'+thisPostOffice+'" '+(currentPostOffice && currentPostOffice == thisPostOffice ? 'selected' : '')+'>'+thisPostOffice+'</option>');
                            }
                        }
                    };

                if(config.division){
                    $(config.division).append('<option value="">Any</option>');
                    for(i in BD_LOCATIONS){
                        $(config.division).append('<option value="'+i+'" '+(currentDivision && currentDivision == i ? 'selected' : '')+'>'+i+'</option>');
                        }
                    $(config.division).on('change', function(){config.updateDistrict()});
                    }

                if(config.district){
                    $(config.district).on('change', function(){
                        config.updateSubDistrict();
                        config.updatePoliceStation();
                        config.updatePostOffice();
                        });
                    }

                if(config.division){
                    if(currentDivision) $(config.division).change();
                    }



                };
            if ($('body').hasClass('fixed_footer')) {
                window.onresize = function () {
                    $('#content-wrapper').css({'padding-bottom': ($('.footerWrapper').outerHeight() + 4) + 'px'});
					if($('#main-menu').is(':visible'))
						$('.footerWrapper').css({'left' : ($('#main-menu').position().left + $('#main-menu').width())});
                    }
                }

            init.push(function () {
                //disabling all SUBMIT buttons on click
                $('form.preventDoubleClick').on('submit',function(){
                    return preventDoubleClick($(this));
                    });
                var _url = '<?php echo current_url(true);?>';
                var full_url = '<?php echo current_url();?>';
                if($('#main-menu-inner .navigation a[href="'+full_url+'"]').length)
                    $('#main-menu-inner .navigation a[href="'+full_url+'"]').closest('li').addClass('active').closest('.mm-dropdown-root').addClass('open');
                else if($('#main-menu-inner .navigation a[href="'+_url+'"]').length)
                    $('#main-menu-inner .navigation a[href="'+_url+'"]').closest('li').addClass('active').closest('.mm-dropdown-root').addClass('open');
                });

            $('.dropdown-toggle').dropdown();
            $('.navbar-toggle').each(function(index,element){
                var toggleClass = $(element).attr('data-toggle');
                var target = $($(element).attr('data-target'));
                $(element).click(function(){
                    target.toggleClass(toggleClass);
                    });
                });
            window.PixelAdmin.start(init);

            if($('.filter-panel').length){
                $('<p style="height: 40px;"></p>').insertAfter('.filter-panel');
                }

            $('.smart_action_btn tr').on('mouseenter', function(){
                var ths = $(this);
                var actions = ths.find('.action_column').html();
                if(!$('#row_action_container').length) $('body').append('<div id="row_action_container"><div class="action_btn_background"></div></div>');
                var actionContainer = $('#row_action_container');
                actionContainer.find('.action_btn_background').html(actions);
                actionContainer.css('visibility', 'visible');
                var _left = ths.position().left;
                //var _right = _left + ths.width();
                var _top = ths.position().top - ths.outerHeight();
                console.log(ths.outerHeight(), _top);
                actionContainer.css({
                    left: _left,
                    top: _top,
                    //right: _right,
                    width: ths.width(),
                });
            });
            $('.smart_action_btn tr').on('mouseleave', function(){
                $('#row_action_container .action_btn_background').html('');
                $('#row_action_container').css('visibility', 'hidden');
            });

            $('[data-toggle="popover"]').popover();
            $('[data-toggle="tooltip"]').tooltip();
        </script>
        <?php
        $pullNotifier = jack_obj('dev_pull_notification');
        if($pullNotifier && has_permission('receive_pull_notification')){
            ?>
            <script type="text/javascript">
                var push_notification_sound = new Audio();
                push_notification_sound.src = '<?php echo common_files().'/audio/push_notification.mp3' ;?>';
            </script>
            <?php
            $pullNotifier->pop_notification_js();
            }
        ?>
        <div class="floating_right_panel dn">
            <div class="floating_panel_handle"><i class="fa fa-chevron-circle-left"></i></div>
            <div class="floating_right_panel_content">

            </div>
        </div>
        <script type="text/javascript">
            $('.floating_panel_handle').on('click', function(){
                var ths = $(this);
                var container = ths.closest('.floating_right_panel');
                if(container.hasClass('floating_right_panel_open')){
                    container.removeClass('floating_right_panel_open');
                    ths.find('i').addClass('fa-chevron-circle-left').removeClass('fa-chevron-circle-right');
                    }
                else{
                    container.addClass('floating_right_panel_open');
                    ths.find('i').removeClass('fa-chevron-circle-left').addClass('fa-chevron-circle-right');
                    }
                });
        </script>
        
        
        <link href="http://vitalets.github.io/x-editable/assets/x-editable/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">
<script src="http://vitalets.github.io/x-editable/assets/x-editable/bootstrap3-editable/js/bootstrap-editable.js"></script>


    </body>
</html>
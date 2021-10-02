<?php
$args = array(
    'childs_only' => true,
    'render_function' => 'footer_useful_links_render_function'
);
$footerUsefulLinks = __render_menu('footer-useful-links-position',$args);

$args = array(
    'childs_only' => true,
    'render_function' => 'basic_menu_render_function'
);
$headerHiddenMenu = __render_menu('header-hidden-primary-menu-position',$args);

$thePrimaryMenu = 'header-primary-menu-position';
if(isset($_SESSION['view_type'])){
    if($_SESSION['view_type'] == 'recruiters') $thePrimaryMenu = 'recruiters-primary-position';
    else if($_SESSION['view_type'] == 'job_seekers') $thePrimaryMenu = 'job-seeker-primary-position';
    }
$args = array(
    'childs_only' => true,
    'render_function' => 'basic_menu_render_function'
    );
$primaryMenu = __render_menu($thePrimaryMenu,$args);

$args = array(
    'childs_only' => true,
    'render_function' => 'basic_menu_render_function'
);
$tinyMenu = __render_menu('header-tiny-menu-position',$args);

$args = array(
    'childs_only' => true,
    'render_function' => 'basic_menu_render_function'
);
//$footerBottomMenu = __render_menu('footer-bottom-position',$args);

$get_in_touch = $JACK_SETTINGS->get_saved_settings('Get In Touch', true);
$footer_settings = $JACK_SETTINGS->get_saved_settings('Footer Section', true);

$cManager = jack_obj('dev_content_management');
$footerLatestNews = $cManager->get_contents(array(
    'content_types' => 'News',
    'order_by' => array('col' => 'content_published_time', 'order' => 'DESC'),
    'limit' => array('start' => 0, 'count' => 5)
    ));

include('header.php') ?>
    <header>
        <div class="topSmallNavContainer row">
            <div class="container">
                <div class="row">
                    <div class="col-8">
                        <ul class="topSmallNav collapsibleMenu"><li><a class="collapsibleMenuSwitch" href="javascript:"><i class="fa fa-bars"></i></a></li><?php echo $tinyMenu; ?></ul>
                    </div>
                    <div class="col-4 tar">
                        <ul class="topSmallNav collapsibleMenu languageMenu">
                            <li><a class="collapsibleMenuSwitch current_language" href="javascript:"><?php echo $_config['langs'][$_config['slang']]['title'] ?></a></li>
                            <li><a class="current_language" href="javascript:"><?php echo $_config['langs'][$_config['slang']]['title'] ?></a></li>
                            <?php
                            foreach ($_config['langs'] as $v=>$i) {
                                if ($v == $_config['slang']) continue;
                                ?>
                                <li><a href="<?php echo getChangeLanguageLink($v) ?>"><?php echo $i['title'] ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div id="stickyHeaderSection">
            <div class="headerContainer row">
                <header class="container">
                    <div class="row">
                        <div class="col-md-4 col-3 text-md-left text-xs-center" style="padding-top:4px;padding-bottom: 4px;">
                            <a class="dib" href="<?php echo url(); ?>"><img src="<?php echo theme_path(); ?>/images/bpl_logo.png" style="width: 55px;height:55px" /></a>
                        </div>
                        <div class="col-md-8 col-9 pt5 pb5 headerRightContainer">
                            <div class="float-right tac">
                                <a class="dib mainHiddenMenu"><i class="fa fa-bars"></i></a>
                                <div class="dn theHiddenMenuContainer">
                                    <a href="javascript:" class="fa fa-times closeMainMenuContainer"></a>
                                    <ul class="theHiddenMenu collapsibleMenu"><?php echo $headerHiddenMenu; ?></ul>
                                    <div style="padding: 0 45px">
                                        <?php printSocialLinks() ?>
                                    </div>
                                </div>
                                <script type="text/javascript">
                                    init.push(function(){
                                        $(document).on('click','.mainHiddenMenu',function(){
                                            var ths = $(this);
                                            //$('.theHiddenMenuContainer').css('top',ths.offset().top+ths.outerHeight()).removeClass('dn');
                                            $('.theHiddenMenuContainer').removeClass('dn');
                                            });
                                        $(document).mouseup(function(e){
                                            if($(".theHiddenMenuContainer").hasClass('dn')) return;
                                            var container = $(".theHiddenMenuContainer");

                                            // if the target of the click isn't the container nor a descendant of the container
                                            if (!container.is(e.target) && container.has(e.target).length === 0){
                                                container.addClass('dn');
                                                }
                                            });
                                        $('.closeMainMenuContainer').click(function(){
                                            $('.theHiddenMenuContainer').addClass('dn');
                                            });
                                        });
                                </script>
                            </div>
                            <div class="float-md-right float-left mr20 tac">
                                <?php
                                if($_SESSION['view_type'] == 'recruiters'){
                                    ?>
                                    <a href="<?php echo url('job_seekers?view_type=job_seekers'); ?>" class="db headerBigButton mo">
                                        <span class="db fs_6"> <?php echo ML('job_seekers'); ?></span>
                                        <span class="db fs_7"><?php echo ML('find_the_right_job'); ?></span>
                                    </a>
                                    <?php
                                    }
                                elseif(true || $_SESSION['view_type'] == 'job_seekers'){
                                    ?>
                                    <a href="<?php echo url('recruiters?view_type=recruiters'); ?>" class="db headerBigButton mo">
                                        <span class="db fs_6"><?php echo ML('recruiters'); ?></span>
                                        <span class="db fs_7"><?php echo ML('post-job-find-talent'); ?></span>
                                    </a>
                                    <?php
                                    }
                                ?>
                            </div>
                            <!--div class="fr tac mr20">
                                <span class="m0 accountIcon icon_profile"></span>
                                <p class="m0 accountText"><?php echo ML('account'); ?></p>
                            </div-->
                            <div class="float-md-right float-sm-center mr20 d-none d-lg-block">
                                <span class="headerIconPhone fa fa-phone"></span>
                                <div class="tac headerIconPhoneNumber">
                                    <p class="m0 thePhoneNumber">+8802 9881265 ext.5755</p>
                                    <p class="m0 thePhoneNumberHint"><?php echo ML('for_a_consultantion_call'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            </div>
            <div class="navContainer row">
                <nav class="container">
                    <ul class="mainNavMenu aligner collapsibleMenu"><li><a class="collapsibleMenuSwitch" href="javascript:"><i class="fa fa-bars"></i></a></li><?php echo $primaryMenu; ?></ul>
                </nav>
            </div>
        </div>
        <div id="stickyHeaderSupporter"></div>
    </header>
    <div class="primaryNotificationContainer row">
        <div class="container">
            <?php echo $notify_user->get_notification(); ?>
        </div>
    </div>
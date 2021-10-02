<?php global $paths; ?>
<!DOCTYPE html>
<!--[if IE 8]>         <html class="ie8"> <![endif]-->
<!--[if IE 9]>         <html class="ie9 gt-ie8"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="gt-ie8 gt-ie9 not-ie"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo processToRender($_config['site_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon" /><link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <script type="text/javascript">
        var __dev__user__ = <?php echo $_config['user'] ? 1 : 0?>;
        var _root_path_ = '<?php echo _path('root');?>';
        var _theme_path_ = '<?php echo theme_path();?>';
        var _internalToken_ = '<?php echo $SAFEGUARD->internal_token;?>';
        var _current_url_ = '<?php echo current_url()?>';
        var _dlang_ = '<?php echo $_config['dlang'];?>';
        var _slang_ = '<?php echo $_config['slang'];?>';
        var _langs_ = <?php echo to_json_object($_config['langs']);?>;
    </script>

    <!-- Open Sans font from Google CDN -->
    <link href="<?php echo $paths['protocol']; ?>://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,700,300&subset=latin" rel="stylesheet" type="text/css">
    <?php
    minify_handler::$cssBundle['assets'] = array(
        theme_path('absolute').'/assets/stylesheets/bootstrap.min.css',
        theme_path('absolute').'/assets/stylesheets/pixel-admin.min.css',
        theme_path('absolute').'/assets/stylesheets/widgets.min.css',
        theme_path('absolute').'/assets/stylesheets/pages.min.css',
        theme_path('absolute').'/assets/stylesheets/rtl.min.css',
        theme_path('absolute').'/assets/stylesheets/themes.min.css',
        );
    ?>
    <?php minify_handler::renderMinifiedCss('assets'); ?>
    <link href="<?php echo common_files(); ?>/css/jquery.fancybox.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo theme_path(); ?>/assets/stylesheets/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo theme_path(); ?>/assets/stylesheets/dev_style.css?v=24" rel="stylesheet" type="text/css">
    <link href="<?php echo common_files(); ?>/css/common.css?v=3" rel="stylesheet" type="text/css">
    <link href="<?php echo common_files(); ?>/css/jquery-ui-timepicker-addon.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo common_files()?>/css/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo theme_path(); ?>/assets/stylesheets/calendar.css">
	 <link rel="stylesheet" href="<?php echo theme_path(); ?>/assets/stylesheets/ui_styles_override.css?v=2">
    <?php echo get_header('admin'); ?>
    <script>var init = [];</script>
    <script type="text/javascript">
        function preventDoubleClick(form){
            var btn = form.find('input[type="submit"]');
            form.attr('data-submit-button',btn.val());
            btn.addClass('disabled').val('Please Wait ...');
            return true;
            }
        function releasePreventDoubleClick(form, submitForm){
            submitForm = typeof submitForm === 'undefined' ? false : submitForm;
            var btn = form.find('input[type="submit"]');
            btn.removeClass('disabled').val(form.attr('data-submit-button'));
            if(submitForm) form.submit();
            return true;
            }
        function addLoading(){
            var theUnloading = document.createElement('div');
            theUnloading.setAttribute('id', 'unloading');
            theUnloading.setAttribute('class', 'pageLoading');
            theUnloading.innerHTML = '<span class="pageLoadingInner label label-warning">Please Wait. Loading...</span>';

            document.body.insertBefore(theUnloading, document.body.firstChild );
            }
        function showLoading(){
            $('#unloading').fadeIn();
            }
        function hideLoading(){
            $('#unloading').fadeOut();
            }
        function removeLoading(){
            var element = document.getElementById("unloading");
            if(typeof element != 'undefined' && element != null) element.parentNode.removeChild(element);
            }
    </script>
</head>
<body onbeforeunload="showLoading()" class="theme-<?php echo $_config['adminTheme'] ? $_config['adminTheme'] : 'default'?> main-menu-animated <?php echo $_config['mainNavMenuToRight'] ? 'main-menu-right' : ''?>  main-navbar-fixed main-menu-fixed hanging_footer .fixed_footer .mmc <?php echo getProjectSettings('features,backend_left_menu') ? '' : 'no-main-menu'; ?>">
    <script type="text/javascript">
        addLoading();
    </script>
    <div id="main-wrapper">
        <div id="main-navbar" class="navbar navbar-inverse" role="navigation">
            <button type="button" id="main-menu-toggle"><i class="navbar-icon fa fa-bars icon"></i><span class="hide-menu-text">HIDE MENU</span></button>
            <div class="navbar-inner">

                <div class="navbar-header">
                    <?php
                    if(!$_config['noFront']){
                        ?>
                        <a target="_blank" href="<?php echo url('','public'); ?>" class="navbar-brand">
                            <i class="fa fa-external-link"></i>&nbsp;Visit Site
                        </a>
                        <div class="company_logo"><img  width="175px" src="<?php echo theme_path();?>/assets/images/logo.png" alt="" class="company_logo_img"></div>
                        <?php
                        }
                    else{
                        ?>
                        <a target="_blank" href="javascript:" class="navbar-brand">&nbsp;</a>
                        <div class="company_logo"></div>
                        <?php
                        }
                    ?>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar-collapse"><i class="navbar-icon fa fa-bars"></i></button>
                </div>

                <div id="main-navbar-collapse" class="collapse navbar-collapse main-navbar-collapse">
                    <div>
                        <?php echo topAdminMenu();?>

                        <div class="right clearfix">
                            <ul class="nav navbar-nav pull-right right-navbar-nav">
                                <?php
                                if(count($_config['langs']) > 1){
                                    ?>
                                    <li class="dropdown" style="">
                                        <a href="#" class="dropdown-toggle lang-menu" data-toggle="dropdown">
                                            <span><?php echo $_config['langs'][$_config['slang']]['title'] ?>&nbsp;<i
                                                    class="fa fa-caret-down"></i></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <?php
                                            foreach($_config['langs'] as $v=>$i){
                                                if($v == $_config['slang']) continue;
                                                ?>
                                                <li>
                                                    <a href="<?php echo build_url(array('change_language' => $v)) ?>">
                                                        <?php echo $i['title'] ?>
                                                    </a>
                                                </li>
                                                <?php
                                                }
                                            ?>
                                        </ul>
                                    </li>
                                    <?php
                                    }
                                ?>
                                <?php
                                if($jacker->jack_exist('dev_pull_notification') && has_permission('receive_pull_notification')){
                                    ?>
                                    <li class="nav-icon-btn nav-icon-btn-danger dropdown" id="push_notification_container">
                                        <a href="#"  id="notification_count" class="dropdown-toggle" data-toggle="dropdown">
                                            <span class="label number_of_notifications"></span>
                                            <i class="nav-icon fa fa-bullhorn"></i>
                                            <span class="small-screen-text">Notifications</span>
                                        </a>
                                        <script>
                                            init.push(function () {
                                                $('#main-navbar-notifications').slimScroll({ height: 250 });
                                            });
                                        </script>
                                        <div class="dropdown-menu widget-notifications no-padding" style="width: 300px">
                                            <div class="notifications-list" id="main-navbar-notifications">

                                            </div>
                                            <a href="<?php echo url('admin/dev_pull_notification/manage_notifications'); ?>" class="notifications-link">MORE NOTIFICATIONS</a>
                                        </div>
                                    </li>
                                    <?php
                                    }
                                ?>
                                <?php
                                $sysLangs = $_config['langs'];
                                if(false && count($sysLangs) > 1){
                                    ?>
                                    <script type="text/javascript">
                                        function changeSystemLanguage(obj){
                                            var theForm = $('[name=change_system_language]');
                                            theForm.find('#nextLanguage').val($(obj).attr('data-language'));
                                            theForm.submit();
                                            }
                                    </script>
                                    <li class="dropdown">
                                        <form name="change_system_language" method="post">
                                            <input type="hidden" id="nextLanguage" name="nextLanguage" value="" />
                                        </form>
                                        <a href="javascript:" class="dropdown-toggle" data-toggle="dropdown">
                                            <?php echo $_config['langs'][$_config['slang']]['title']; ?>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <?php
                                            foreach($sysLangs as $l=>$L){
                                                if($_config['slang'] == $l) continue;
                                                ?>
                                                <li><a href="javascript:" onclick="changeSystemLanguage(this)" data-language="<?php echo $l?>"><?php echo $L['title']?></a></li>
                                                <?php
                                                }
                                            ?>
                                        </ul>
                                    </li>
                                    <?php
                                    }
                                ?>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle user-menu" data-toggle="dropdown">
                                        <img src="<?php echo $_config['user']['rel_user_picture']?>" alt="">
                                        <span><?php echo $_config['user']['user_fullname']; ?></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="<?php echo GET_LOGOUT_LINK(); ?>"><i class="dropdown-icon fa fa-power-off"></i>&nbsp;&nbsp;Log Out</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="main-menu" role="navigation">
            <div id="main-menu-inner">
                <div class="menu-content top" id="menu-content-demo">
                    <div>
                        <div class="text-bg"><span class="text-slim">Welcome,</span> <span class="text-semibold" style="    display: block;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;"><?php echo $_config['user']['user_fullname']; ?></span></div>
                        <div class="user_picture"><img  src="<?php echo $_config['user']['rel_user_picture']; ?>" alt="" class=""></div>
						
                        <div class="btn-group logout_btn">
                            <!--a href="#" class="btn btn-xs btn-primary btn-outline dark"><i class="fa fa-envelope"></i></a>
                            <a href="#" class="btn btn-xs btn-primary btn-outline dark"><i class="fa fa-user"></i></a>
                            <a href="#" class="btn btn-xs btn-primary btn-outline dark"><i class="fa fa-cog"></i></a-->
                            <a href="<?php echo GET_LOGOUT_LINK(); ?>" class="btn btn-xs btn-danger btn-outline dark "><i class="fa fa-power-off"></i></a>
                        </div>
                        <a href="#" class="close">&times;</a>
                    </div>
                </div>
                <?php echo getProjectSettings('features,backend_left_menu') ? $adminmenu->get_admin_menu(null) : '<ul class="navigation"></ul>'?>
            </div> <!-- / #main-menu-inner -->
        </div> <!-- / #main-menu -->
        <div id="content-wrapper" style="min-height: 100%">


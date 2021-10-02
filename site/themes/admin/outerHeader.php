<?php ?>
<!DOCTYPE html>
<!--[if IE 8]>         <html class="ie8"> <![endif]-->
<!--[if IE 9]>         <html class="ie9 gt-ie8"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="gt-ie8 gt-ie9 not-ie"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo $PAGE_NAME; ?> - <?php echo processToRender($_config['site_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">

    <!-- Open Sans font from Google CDN -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,700,300&subset=latin" rel="stylesheet" type="text/css">

    <!-- Pixel Admin's stylesheets -->
    <link href="<?php echo _path('admin'); ?>/assets/stylesheets/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo _path('admin'); ?>/assets/stylesheets/pixel-admin.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo _path('admin'); ?>/assets/stylesheets/pages.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo _path('admin'); ?>/assets/stylesheets/rtl.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo _path('admin'); ?>/assets/stylesheets/themes.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo common_files(); ?>/css/common.css" rel="stylesheet" type="text/css">

    <!--[if lt IE 9]>
    <script src="<?php echo _path('admin'); ?>/assets/javascripts/ie.min.js"></script>
    <![endif]-->
    <style type="text/css">
        .background_holder{
            background-size: cover;
        <?php
        if($_config['admin_page_bg_image']){
            ?>
            background-image: url('<?php echo image_url($_config['admin_page_bg_image'])?>');
        <?php
        }
    ?>
        }
        .background_holder, .the_background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -2;
        }
        .the_background{
        <?php echo $_config['admin_page_bg_color'] ? 'background-color: '.$_config['admin_page_bg_color'].';' : ''?>
        <?php echo $_config['admin_page_bg_color_opacity'] ? 'opacity:'.$_config['admin_page_bg_color_opacity'].';' : ''?>
            z-index: -1;
        }
        .loginFormBG {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            background-color: <?php echo $_config['admin_page_login_form_bg_color'] ? $_config['admin_page_login_form_bg_color'] : '#fff'?>;
            opacity: <?php echo $_config['admin_page_login_form_bg_color_opacity'] ? $_config['admin_page_login_form_bg_color_opacity'] : '1'?>;
        }
        .login_form{
            background:none;
            border:0 none;
            margin-top: 30px !important;
        <?php
        if($_config['login_form_position']){
            if($_config['login_form_position'] == 'left'){
                ?>
            float:left;
            margin-left: 30px !important;
        <?php
        }
    elseif($_config['login_form_position'] == 'right'){
        ?>
            float:right;
            margin-right: 30px !important;
        <?php
        }
    }
?>
        }
        .theme-default .demo-logo{
            background-color: transparent;
        }
        .signin-header .logo{
        <?php
         if($_config['admin_page_header_text_color']){
            ?>
            color: <?php echo $_config['admin_page_header_text_color']?> !important;
        <?php
         }
     ?>
        }
        .signin-header{
        <?php
         if($_config['admin_page_header_bg_color']){
            ?>
            background-color: <?php echo $_config['admin_page_header_bg_color']?> !important;
        <?php
         }
     ?>
        }
        #signin-form_id{
            display: none;
        }
        #signin-form_id h1, #signin-form_id label{
            color: <?php echo $_config['admin_login_prompt_text_color'] ? $_config['admin_login_prompt_text_color'] : '#555555'?>;
        }
    </style>
</head>
<body class="theme-default page-signin-alt">
<div class="background_holder">
    <div class="the_background"></div>
</div>
<div class="signin-header">
    <a href="javascript:" class="logo">
        <?php
        if($_config['admin_page_logo']){
            ?>
            <div class="demo-logo"><img src="<?php echo image_url($_config['admin_page_logo']) ?>" alt="" style="margin-top: -4px;height: 40px;"></div>&nbsp;-&nbsp;
            <?php
            }
        echo $PAGE_NAME;
        ?>
    </a>
</div>

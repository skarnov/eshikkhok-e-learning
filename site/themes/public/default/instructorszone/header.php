<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="robots" content="nofollow" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Home</title>
        <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="<?php echo common_files()?>/css/jquery-ui.css">
        <link href="<?php echo theme_path(); ?>/assets/styles.css" rel="stylesheet" />

        <script type="text/javascript">
            var __dev__user__ = <?php echo $_config['user'] ? 1 : 0?>;
            var _root_path_ = '<?php echo _path('root');?>';
            var _theme_path_ = '<?php echo theme_path();?>';
            var _internalToken_ = '<?php echo $SAFEGUARD->internal_token;?>';
            var _current_url_ = '<?php echo current_url()?>';
            var _dlang_ = '<?php echo $_config['dlang'];?>';
            var _slang_ = '<?php echo $_config['slang'];?>';
            var _langs_ = <?php echo to_json_object($_config['langs']);?>;
            var init = [];
        </script>
    </head>

    <body>
<?php ?>
<!--[if !IE]> -->
<script type="text/javascript"> window.jQuery || document.write('<script src="<?php echo theme_path().'/assets/javascripts/jquery-2.0.3.min.js'?>">'+"<"+"/script>"); </script>
<!-- <![endif]-->
<!--[if lte IE 9]>

<script type="text/javascript"> window.jQuery || document.write('<script src="<?php echo theme_path().'/assets/javascripts/jquery-1.8.3.min.js'?>">'+"<"+"/script>"); </script>
<![endif]-->


<!-- Pixel Admin's javascripts -->
<script src="<?php echo _path('admin'); ?>/assets/javascripts/bootstrap.min.js"></script>
<script src="<?php echo _path('admin'); ?>/assets/javascripts/pixel-admin.min.js"></script>
<script src="<?php echo common_files(); ?>/js/jquery-ui.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $( "#signin-form_id" ).fadeIn(1000);
    });

</script>
</body>
</html>


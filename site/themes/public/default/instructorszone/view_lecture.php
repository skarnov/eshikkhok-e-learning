<div class="container">
    <?php echo $notify_user->get_notification(); ?>
    <div class="row">
        <div class="col-md-10 mt-5">
            <?php
            if ($_GET['content']) {
                echo $_GET['content'];
            } else {
                ?>
                <video width="320" height="240" controls>
                    <source src="<?php echo url() ?>upload/<?php echo $_GET['video'] ?>" type="video/mp4">
                </video> 
            <?php } ?>
        </div>
    </div>
</div>
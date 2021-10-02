<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="<?php echo url('instructorszone') ?>">E-Shikkhok</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item active">
                <a class="nav-link" href="<?php echo url('instructorszone') ?>">Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo url('instructorszone/new_course') ?>">Create A Course</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo url('instructorszone/basic_info') ?>">Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo url('instructorszone/course_management') ?>">Course Management</a>
            </li>
        </ul>
    </div>
</nav>
<?php
    $notification = $notify_user->get_notification();
    if($notification):
?>
<div class="alert alert-danger" role="alert">
    <?php echo $notification ?>
</div>
<?php
    endif;
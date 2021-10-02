<?php

$args = array(
    'user_id' => $_GET['view'],
    'single' => true,
    );
$user_info = $this->get_users($args);
$user_info = $user_info['data'];

doAction('render_start');
?>

<div class="page-header">
    <div class="oh pull-left mr10">
        <img src="<?php echo $user_info['rel_user_picture'] ?>" alt="<?php echo $user_info['user_fullname']?>"/>
    </div>
    <div class="oh">
        <h1><strong>User Profile</strong></h1>
        <hr />
        <div class="note <?php echo $user_info['user_status'] == 'active' ? 'note-info' : ($user_info['user_status'] == 'inactive' ? 'note-danger' : 'note-success'); ?>">
            <h2 class="note-title"><?php echo $user_info['user_fullname']?></h2>
           </strong></br>Email: <strong><?php echo $user_info['user_email']; ?></strong>|
            Mobile: <strong><?php echo $user_info['user_email']; ?></strong> | Status: <strong><?php echo ucfirst($user_info['user_status']); ?></strong>
        </div>
        <div class="oh">
            <div class="btn-group btn-group-sm">
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array(),array('action','view')),
                    'action' => 'list',
                    'icon' => 'icon_list',
                    'text' => 'All Users',
                    'title' => 'All Users',
                    'size' => 'sm',
                    ));
                ?>
            </div>
        </div>
    </div>
</div>
<div class="panel">
    <div class="panel-body">
        <table class="table profile_table">
            <tbody>
            <tr>
                <td>Full Name</td>
                <td><?php echo $user_info['user_fullname']?></td>
            </tr>
            <tr>
                <td>Birthdate</td>
                <td><?php echo $user_info['user_birthdate']?></td>
            </tr>
            <tr>
                <td>Religion</td>
                <td><?php echo $user_info['user_religion']?></td>
                <td>Gender</td>
                <td><?php echo $user_info['user_gender']?></td>
            </tr>
            <tr>
                <td>User Name</td>
                <td><?php echo $user_info['user_name']?></td>
                <td>Country</td>
                <td><?php echo $user_info['user_country']?></td>
            </tr>
            <tr>
                <td>User Type</td>
                <td><?php echo $user_info['user_type']?></td>
            </tr>
            <tr>
                <td>Father's Name</td>
                <td><?php echo $user_info['fathers_name']?></td>
                <td>Mother's Name</td>
                <td><?php echo $user_info['mothers_name']?></td>
            </tr>
            <tr>
                <td>User Behaviour</td>
                <td><?php echo $user_info['educational_qualification']?></td>
                <td>About Me</td>
                <td><?php echo $user_info['about_me']?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<?php

$args = array(
    'user_id' => $_config['user']['pk_user_id'],
    'single' => true,
    );
$user_info = $this->get_users($args);
$user_info = $user_info['data'];

doAction('render_start');
?>

<div class="page-header">
    <div class="oh pull-left mr10">
        <img src="<?php echo $user_info['user_picture'] ? $user_info['user_picture'] : $user_info['rel_user_picture']?>" alt="<?php echo $user_info['user_fullname']?>"/>
    </div>
    <div class="oh">
        <h1><strong>User Profile</strong></h1>
        <hr />
        <div class="note <?php echo $user_info['user_status'] == 'active' ? 'note-info' : ($user_info['user_status'] == 'inactive' ? 'note-danger' : 'note-success'); ?>">
            <h2 class="note-title"><?php echo $user_info['user_fullname']?></h2>
            </strong></br>Email: <strong><?php echo $user_info['user_email']; ?></strong>|
            Mobile: <strong><?php echo $user_info['user_email']; ?></strong>
        </div>
        <div class="oh">
            <div class="btn-group btn-group-sm">
                <a href="<?php echo $myUrl.'?action=edit_my_account&edit='.$_config['user']['pk_user_id']?>" class="btn btn-flat btn-labeled" title="">
                    <span class="btn-label icon fa fa-edit"></span>
                    Edit
                </a>
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
                    <td>Country</td>
                    <td><?php echo $user_info['user_country']?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
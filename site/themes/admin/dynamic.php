<?php
//include('header.php');
//pre('here');
echo $notify_user->get_notification();
if(!$gMan->admin_output){
    ?>
    <div class="note note-dark note-danger">
        <h4 class="note-title">Access Denied</h4>
        Reason can be one or more from the followings:
        <br /><br />
        <ol>
            <li>You don't have enough permission</li>
            <li>The content is not available at this moment</li>
            <li>The content is moved to a different address</li>
        </ol>
        <br />
        Please contact an administrator.
    </div>
    <?php
    }
echo $gMan->admin_output;
include('footer.php');
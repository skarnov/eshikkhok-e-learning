<?php

    $sql = "DELETE FROM e_user_trainings WHERE pk_training_id = '".$_GET['id']."'";
    $devdb->query($sql);
    
    $sql = "DELETE FROM dev_content_tag_relation WHERE fk_content_id = '".$_GET['id']."' AND content_type = '".$_GET['type']."'";
    $devdb->query($sql);
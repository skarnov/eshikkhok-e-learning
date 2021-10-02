<?php

    $id = $_GET['id'];
    $name = $_GET['name'];
    $value = $_GET['value'];
        
    if($name == 'featured_image'){
        unlink(_path('uploads','absolute').'/'.$value);
        $sql = "UPDATE e_courses SET ".$_GET['name']." = 'NULL' WHERE pk_item_id = '".$_GET['id']."'";
        $devdb->query($sql);
    }
    elseif($name == 'promotional_video'){
        unlink('upload/'.$value);
        $sql = "UPDATE e_courses SET ".$_GET['name']." = 'NULL' WHERE pk_item_id = '".$_GET['id']."'";
        $devdb->query($sql);
    }
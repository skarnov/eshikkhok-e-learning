<?php

    $sql = "SELECT lecture_video FROM e_lectures WHERE fk_module_id = '".$_GET['id']."'";
    $video = $devdb->query($sql)[0]['lecture_video'];
    
    unlink('upload/'.$video);

    $sql = "DELETE FROM e_lectures WHERE pk_lecture_id = '".$_GET['id']."'";
    $devdb->query($sql);
    
    header('location: ' . url('instructorszone/new_curriculum?id=' . $_GET['cur']));
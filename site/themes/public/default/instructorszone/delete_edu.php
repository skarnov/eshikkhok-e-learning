<?php

    $sql = "DELETE FROM e_user_educations WHERE pk_education_id = '".$_GET['id']."'";
    $devdb->query($sql);
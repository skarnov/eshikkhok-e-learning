<?php

    $sql = "DELETE FROM e_user_employments WHERE pk_employment_id = '".$_GET['id']."'";
    $devdb->query($sql);
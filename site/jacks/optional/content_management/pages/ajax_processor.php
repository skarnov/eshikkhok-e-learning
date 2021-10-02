<?php
if($_POST['ajax_type'] == 'youtube_detail'){
    echo json_encode(youtube_video_info($_POST['video_url']));
    exit();
    }
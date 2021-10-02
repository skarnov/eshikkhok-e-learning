<?php
$url = $_config['url_parameters'];
$toPage = $url['content_type'] ? $url['content_type'] : 'dashboard';

include 'header.php';

include($toPage.'.php');

include 'footer.php';

exit();
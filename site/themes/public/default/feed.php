<?php
$seoManager = jack_obj('dev_seo_management');
header('Content-type: application/xml');
echo $seoManager->create_site_feed(null);
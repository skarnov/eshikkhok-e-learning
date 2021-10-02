<?php
$thePage = $_config['current_page'];
$theContent = $_config['current_content'];
$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 12;

if($theContent){
    include ('header_inner_pages.php');
    ?>
    <style type="text/css">
        .find_better_section_container{
        <?php if(strlen($theContent['content_thumbnail'])): ?>
            background-image: url('<?php echo get_image($theContent['content_thumbnail'],'1920x450x1');?>');
        <?php else: ?>
            background-image: none;
            background-color: #414141;
        <?php endif; ?>
        }
    </style>
    <div class="row">
        <img style="width: 100%; height: 100%" src="<?php echo get_image($theContent['content_thumbnail'],'1920x450x1');?>" />
    </div>
    <!--div class="row find_better_section_container">
        <div class="col-lg-12 pl0 pr0 find_better_section_inner_container">
            <div class="find_better_section container tac" >

            </div>
        </div>
    </div-->
    <div class="row static_page_container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class=""><?php echo processToRender($theContent['content_title']); ?></h1>
                    <h2 class=""><?php echo processToRender($theContent['content_sub_title']); ?></h2>
                    <h4 class="detail_content_price">Price: <?php echo handleEmptyStrings(getMetaValue($theContent, 'service_price'),'0'); ?> <?php echo ML('bdt'); ?></h4>
                    <hr />
                    <div class="detail_content_detail"><?php echo processToRender($theContent['content_description']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
else{
    $cManager = jack_obj('dev_content_management');
    $params = array(
        'published_till_now' => true,
        'content_types' => 'Service',
        'page_id' => $the_page['pk_page_id'],
        'include_meta' => true,
        'order_by' => array('col' => 'content_published_time', 'order' => 'DESC'),
        'limit' => array('start' => $start*$per_page_items, 'count' => $per_page_items),
        );

    $contents = $cManager->get_contents($params);

    $pagination = pagination($contents['total'],$per_page_items,$start);

    include ('header_inner_pages.php');
    ?>
    <style type="text/css">
        .find_better_section_container{
        <?php if(strlen($thePage['page_thumbnail'])): ?>
            background-image: url('<?php echo get_image($thePage['page_thumbnail'],'1920x380x1');?>');
        <?php else: ?>
            background-image: none;
            background-color: #414141;
        <?php endif; ?>
        }
    </style>
    <div class="row find_better_section_container">
        <div class="col-lg-12 pl0 pr0 find_better_section_inner_container">
            <div class="find_better_section container tac" >
                <h1 class="find_better_section_title tac"><?php echo processToRender($thePage['page_title']); ?></h1>
                <div class="find_better_section_sub_title tac"><?php echo processToRender($thePage['page_sub_title']); ?></div>
            </div>
        </div>
    </div>
    <div class="row static_page_container">
        <div class="container">
            <div class="row fixMyChildHeights" data-fix-child=".each_service_container" data-height-type="outer">
                <?php
                if($contents['data']){
                    foreach($contents['data'] as $i=>$item){
                        $detailLink = detail_url($item);
                        ?>
                        <div class="col-lg-4 each_service_container">
                            <div class="each_service">
                                <div class="featured_image_container">
                                    <a href="<?php echo $detailLink; ?>"><img src="<?php echo get_image($item['content_thumbnail'],'540x360'); ?>" /></a>
                                </div>
                                <h2 class="content_title aligner"><a href="<?php echo $detailLink; ?>"><?php echo processToRender($item['content_title']); ?></a></h2>
                                <h3 class="content_subtitle aligner"><a href="<?php echo $detailLink; ?>"><?php echo processToRender($item['content_sub_title']); ?></a></h3>
                                <h4 class="content_price aligner"><a href="<?php echo $detailLink; ?>">Price: <?php echo handleEmptyStrings(getMetaValue($item,'service_price'),'0'); ?> <?php echo ML('BDT'); ?></a></h4>
                                <p class="content_excerpt aligner"><?php echo processToRender($item['content_excerpt']); ?></p>
                            </div>
                        </div>
                        <?php
                        }
                    }
                ?>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?php echo $pagination; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
?>
<?php include ('footer_inner_pages.php')?>
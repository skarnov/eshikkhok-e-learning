<?php
$thePage = $_config['current_page'];
$theContent = $_config['current_content'];
$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 16;

if($theContent){
    include ('header_inner_pages.php');
    ?>
    <div class="row static_page_container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class=""><?php echo processToRender($theContent['content_title']); ?></h1>
                    <h2 class=""><?php echo processToRender($theContent['content_sub_title']); ?></h2>
                    <hr />
                    <div class="detail_content_detail"><?php echo processToRender($theContent['content_description']); ?></div>

                    <?php
                    if($theContent && $theContent['childs']){
                        ?>
                        <div class="galleryDetailPage">
                            <div class="galleryDetailPageImage">
                                <a href="javascript:" class="sliderControlBtn previousBtn"><i class="fa fa-chevron-left"></i></a>
                                <a href="javascript:" class="sliderControlBtn nextBtn"><i class="fa fa-chevron-right"></i></a>
                                <div class="galleryDetailPageImages">
                                    <?php
                                    foreach($theContent['childs'] as $i=>$v){
                                        ?>
                                        <img src="<?php echo get_image($v['content_thumbnail'],'1170x480x2xebebeb'); ?>" />
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="galleryDetailPageText">
                                <?php
                                foreach($theContent['childs'] as $i=>$v){
                                    $ex = json_decode($v['content_extra_settings'], true)
                                    ?>
                                    <div class="eachSlideItem">
                                        <h3 class="slideTitle"><?php echo processToRender($v['content_title']); ?></h3>
                                        <p class="slideText"><?php echo processToRender($v['content_description']); ?></p>
                                        <?php if($ex['item_url']):?>
                                            <a class="btn2" href="<?php echo $ex['item_url']; ?>">Learn More</a>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <script type="text/javascript">
                            init.push(function(){
                                new devSplitSlider({
                                    sectionOne: $('.galleryDetailPage .galleryDetailPageText .eachSlideItem'),
                                    sectionTwo: $('.galleryDetailPage .galleryDetailPageImage .galleryDetailPageImages img'),
                                    sectionOneEffect: 'slide',
                                    sectionOneIncomingDirection: 'up',
                                    sectionOneOutgoingDirection: 'down',
                                    sectionTwoEffect: 'slide',
                                    sectionTwoIncomingDirection: 'right',
                                    sectionTwoOutgoingDirection: 'left',
                                    autoStart: false,
                                    previousSlideController: $('.galleryDetailPage .galleryDetailPageImage .previousBtn'),
                                    nextSlideController: $('.galleryDetailPage .galleryDetailPageImage .nextBtn'),
                                });
                            });
                        </script>
                        <?php
                        }
                        ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
else{
    $cManager = jack_obj('dev_content_management');
    $params = array(
        'include_meta' => true,
        'published_till_now' => true,
        'content_types' => 'gallery',
        'limit' => array('start' => $start*$per_page_items, 'count' => $per_page_items)
        );
    $contents = $cManager->get_contents($params);
    //pre($contents);
    $pagination = pagination($contents['total'],$per_page_items,$start);
    include ('header_inner_pages.php');
    ?>
    <!--style type="text/css">
        .find_better_section_container{
        <?php if(strlen($thePage['page_thumbnail'])): ?>
            background-image: url('<?php echo get_image($thePage['page_thumbnail'],'1920x450x1');?>');
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
    </div-->
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
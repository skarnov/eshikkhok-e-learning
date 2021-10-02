<?php
$thePage = $_config['current_page'];
$theContent = $_config['current_content'];
$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 20;

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
    <!--div class="row find_better_section_container">
        <div class="col-lg-12 pl0 pr0 find_better_section_inner_container">
            <div class="find_better_section container tac" >
                <h1 class="find_better_section_title tac"><?php echo processToRender($theContent['content_title']); ?></h1>
                <div class="find_better_section_sub_title tac"><?php echo processToRender($theContent['content_sub_title']); ?></div>
            </div>
        </div>
    </div-->
    <div class="row">
        <img style="width: 100%; height: 100%" src="<?php echo get_image($theContent['content_thumbnail'],'1920x450x1');?>" />
    </div>
    <div class="row static_page_container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class=""><?php echo processToRender($theContent['content_title']); ?></h1>
                    <h2 class=""><?php echo processToRender($theContent['content_sub_title']); ?></h2>
                    <div class="upper_info_container aligner">
                        <span class="lastModifiedTimeContainer"><?php echo ML('updated_at'); ?>: <span class="lastModifiedTime"><?php echo print_date($theContent['modified_at']); ?></span></span> |
                        <span class="publishedTimeContainer"><?php echo ML('published_at'); ?>: <span class="publishedTime"><?php echo print_date($theContent['content_published_time']); ?></span></span>
                    </div>
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
        //'sql_only' => true,
        'published_till_now' => true,
        'content_types' => 'News',
        'order_by' => array('col' => 'content_published_time', 'order' => 'DESC'),
        'limit' => array('start' => $start*$per_page_items, 'count' => $per_page_items)
        );
    $contents = $cManager->get_contents($params);
    //pre($contents);
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
            <?php
            if($contents['data']){
                foreach($contents['data'] as $i=>$item){
                    $detailLink = detail_url($item);
                    ?>
                    <div class="row each_news_container mb20 pb20" style="border-bottom: 1px solid #ddd">
                        <div class="col-12 col-sm-3">
                            <div class="featured_image_container">
                                <a href="<?php echo $detailLink; ?>"><img src="<?php echo get_image($item['content_thumbnail'],'200x200'); ?>" /></a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-9 aligner">
                            <h2 class="content_title"><a href="<?php echo $detailLink; ?>"><?php echo processToRender($item['content_title']); ?></a></h2>
                            <h3 class="content_subtitle"><a href="<?php echo $detailLink; ?>"><?php echo processToRender($item['content_sub_title']); ?></a></h3>
                            <div class="upper_info_container"><a href="<?php echo $detailLink; ?>">
                                    <span class="lastModifiedTimeContainer"><?php echo ML('updated_at'); ?>: <span class="lastModifiedTime"><?php echo print_date($item['modified_at']); ?></span></span>
                                </a></div>
                            <p class="content_excerpt"><?php echo processToRender($item['content_excerpt']); ?></p>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
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
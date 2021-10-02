<?php
$thePage = $_config['current_page'];
$theContent = $_config['current_content'];
$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 100;

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
    <div class="row eachTestimonial">
        <div class="container tac">
            <div class="theTestimonial mb40">&quot;<?php echo processToRender($theContent['content_description']); ?>&quot;</div>
            <?php
            if($theContent['content_thumbnail']){
                ?>
                <img class="testimonialIcon mb30" src="<?php echo get_image($theContent['content_thumbnail'], '100x100'); ?>">
                <?php
            }
            ?>
            <p class="testimonialBy"><?php echo processToRender($theContent['content_title']); ?></p>
            <p class="testimonialInfo"><?php echo $theContent['content_sub_title'] ? processToRender($theContent['content_sub_title']).', ' : ''; ?><?php echo getMetaValue($theContent,'company'); ?></p>
        </div>
    </div>
    <?php
    }
else{
    $cManager = jack_obj('dev_content_management');
    $params = array(
        'include_meta' => true,
        'published_till_now' => true,
        'content_types' => 'Testimonial',
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
    <?php if(strlen($thePage['page_description'])): ?>
        <div class="row static_page_container testimonial_container">
            <div class="container mb20">
                <div class="detail_content_detail"><?php echo processToRender($thePage['page_description']); ?></div>
            </div>
        </div>
    <?php endif; ?>
    <div >
    <?php
    if($contents['data']){
        foreach($contents['data'] as $i=>$item){
            $detailLink = 'javascript:';//detail_url($item);
            ?>
            <div class="row eachTestimonial">
                <div class="container tac">
                    <div class="theTestimonial mb40">&quot;<?php echo processToRender($item['content_description']); ?>&quot;</div>
                    <?php
                    if($item['content_thumbnail']){
                        ?>
                        <img class="testimonialIcon mb30" src="<?php echo get_image($item['content_thumbnail'], '100x100'); ?>">
                        <?php
                    }
                    ?>
                    <p class="testimonialBy"><?php echo processToRender($item['content_title']); ?></p>
                    <p class="testimonialInfo"><?php echo $item['content_sub_title'] ? processToRender($item['content_sub_title']).', ' : ''; ?><?php echo getMetaValue($item,'company'); ?></p>
                </div>
            </div>
            <?php
            }
        }
    ?>
    </div>
    <div class="row static_page_container testimonial_container">
        <div class="container">
            <div class="col-lg-12">
                <?php echo $pagination; ?>
            </div>
        </div>
    </div>
    <?php
    }
?>
<?php include ('footer_inner_pages.php')?>
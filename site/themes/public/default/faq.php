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
    <div class="row static_page_container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h3 class="content_title"><?php echo processToRender($theContent['content_title']); ?></h3>
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
        'content_types' => 'Faq',
        'page_id' => $the_page['pk_page_id'],
        'order_by' => array('col' => 'content_published_time', 'order' => 'DESC'),
        'limit' => array('start' => $start*$per_page_items, 'count' => $per_page_items)
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
        <div class="container mb20">
            <div class="detail_content_detail"><?php echo processToRender($thePage['page_description']); ?></div>
        </div>
        <div class="container">
            <div class="faq_container">
                <?php
                if($contents['data']){
                    foreach($contents['data'] as $i=>$item){
                        $detailLink = 'javascript:';//detail_url($item);
                        ?>
                        <div class="each_faq" style="border-bottom: 1px solid #ddd">
                            <h4 class="content_title"><a href="<?php echo $detailLink; ?>"><i class="fa fa-plus-square-o"></i>&nbsp;<?php echo processToRender($item['content_title']); ?></a></h4>
                            <div class="detail_content_detail"><?php echo processToRender($item['content_description']); ?></div>
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
    <script type="text/javascript">
        init.push(function(){
            $('.each_faq .content_title').on('click', function(){
                if(!$(this).hasClass('opened')){
                    $('.each_faq .content_title').removeClass('opened');
                    $('.each_faq .detail_content_detail').slideUp('fast');
                    $('.each_faq .content_title i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
                    $(this).next('.detail_content_detail').slideDown('fast');
                    $(this).find('i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
                    $(this).addClass('opened');
                    }
                else{
                    $(this).next('.detail_content_detail').slideUp('fast');
                    $(this).find('i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
                    $(this).removeClass('opened');
                    }
                });
            });
    </script>
<?php include ('footer_inner_pages.php')?>
<?php
$thePage = $_config['current_page'];
include ('header_inner_pages.php')
?>
    <style type="text/css">
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
                <div class="find_better_section_title tac"><?php echo processToRender($thePage['page_title']); ?></div>
                <div class="find_better_section_sub_title tac"><?php echo processToRender($thePage['page_sub_title']); ?></div>
            </div>
        </div>
    </div>
    <div class="row static_page_container">
        <div class="container">
            <?php echo processToRender($thePage['page_description']); ?>
        </div>
    </div>
<?php include ('footer_inner_pages.php')?>
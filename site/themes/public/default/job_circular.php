<?php
$thePage = $_config['current_page'];
$theContent = $_config['current_content'];
$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 16;
$WORLD_COUNTRY_LIST = getWorldCountry();
$tagger = jack_obj('dev_tag_management');
$occupation = $tagger->get_tags(array('tag_group_slug' => 'occupation'));

if($theContent){
    $job_country = getMetaValue($theContent, 'job_country');
    $occupation = getMetaValue($theContent, 'job_occupation');
    $expireDate = getMetaValue($theContent, 'content_expire_date');
    if(strtotime(date('d-m-Y')) > strtotime($expireDate)){
        load404();
        }
    $salary = getMetaValue($theContent, 'job_salary');
    if($occupation){
        $occupation = $tagger->get_tags(array('tag_id' => $occupation, 'single' => true));
        $occupation = processToRender($occupation['tag_title']);
        }

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
        <img style="width: 100%; height: 100%" src="<?php echo get_image($theContent['content_thumbnail'],'1920x380x1');?>" />
    </div>
    <div class="row static_page_container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class=""><?php echo processToRender($theContent['content_title']); ?></h1>
                    <h2 class=""><?php echo processToRender($theContent['content_sub_title']); ?></h2>
                    <hr />
                    <div class="row">
                        <div class="col-6 col-md-3"><strong><?php echo ML('country'); ?>:</strong></div>
                        <div class="col-6 col-md-3"><?php echo handleEmptyStrings($WORLD_COUNTRY_LIST[$job_country]); ?></div>
                        <div class="col-6 col-md-3"><strong><?php echo ML('occupation'); ?>:</strong></div>
                        <div class="col-6 col-md-3"><?php echo handleEmptyStrings($occupation); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-6 col-md-3"><strong><?php echo ML('salary'); ?>:</strong></div>
                        <div class="col-6 col-md-3"><?php echo handleEmptyStrings($salary,'Negotiable'); ?></div>
                        <div class="col-6 col-md-3"><strong><?php echo ML('last_date'); ?>:</strong></div>
                        <div class="col-6 col-md-3"><?php echo print_date($expireDate,false,false); ?></div>
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
        //'print_sql' => true,
        'meta' => array('DATE' => array(
            'LESS_THAN_EQUAL' => array(
                array(
                    'meta' => 'content_expire_date',
                    'value' => date('Y-m-d'),
                    ),
                ),
            )),
        'join_metas_with' => 'AND',
        'published_till_now' => true,
        'content_types' => 'Job-Circular',
        'include_meta' => true,
        'order_by' => array('col' => 'content_published_time', 'order' => 'DESC'),
        'limit' => array('start' => $start*$per_page_items, 'count' => $per_page_items)
        );
    if($_GET['occupation'] || $_GET['country']){
        $params['meta']['AND'] = array();
        if($_GET['occupation']) $params['meta']['AND']['job_occupation'] = $_GET['occupation'];
        if($_GET['country']) $params['meta']['AND']['job_country'] = $_GET['country'];
        }
    $contents = $cManager->get_contents($params);
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
    <div class="row find_better_section_container">
        <div class="col-lg-12 pl0 pr0 find_better_section_inner_container">
            <div class="find_better_section container tac" >
                <div class="find_better_section_title tac"><?php echo processToRender($thePage['page_title']); ?></div>
                <div class="find_better_section_sub_title tac"><?php echo processToRender($thePage['page_sub_title']); ?></div>
                <div class="dib">
                    <form class="searchDetailForm aligner" name="jobSearchForm" action="<?php echo url('job-circulars');?>" method="get">
                        <div class="form-group findBetterSelect2Container mb0">
                            <div class="input-group">
                                <span class="fa fa-search theSearchIcon"></span>
                                <span class="input-group-addon clearThisFilter <?php echo $_GET['occupation'] ? '' : 'dn'; ?>"><i class="fa fa-times"></i></span>
                                <select class="adv_select" name="occupation">
                                    <option value=""><?php echo ML('occupation'); ?></option>
                                    <?php
                                    foreach($occupation['data'] as $i=>$v){
                                        $selected = $_GET['occupation'] && $_GET['occupation'] == $v['pk_tag_id'] ? 'selected' : '';
                                        echo '<option value="'.$v['pk_tag_id'].'" '.$selected.'>'.processToRender($v['tag_title']).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <span class="fa fa-search theSearchIcon"></span>
                                <span class="input-group-addon clearThisFilter <?php echo $_GET['country'] ? '' : 'dn'; ?>"><i class="fa fa-times text-danger"></i></span>
                                <select class="adv_select" name="country">
                                    <option value=""><?php echo ML('country'); ?></option>
                                    <?php
                                    foreach($WORLD_COUNTRY_LIST as $i=>$v){
                                        $selected = $_GET['country'] && $_GET['country'] == $i ? 'selected' : '';
                                        echo '<option value="'.$i.'" '.$selected.'>'.$v.'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <!--input type="text" name="skills" placeholder="skills" value="" />
                            <input type="text" name="country" placeholder="Bangladesh" value="" /-->
                            <input type="submit" name="search" value="Search" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row static_page_container">
        <div class="container">
            <?php
            if($contents['data']){
                foreach($contents['data'] as $i=>$item){
                    $detailLink = detail_url($item);
                    $job_country = getMetaValue($item, 'job_country');
                    $occupation = getMetaValue($item, 'job_occupation');
                    $expireDate = getMetaValue($item, 'content_expire_date');
                    $salary = getMetaValue($item, 'job_salary');

                    if($occupation){
                        $occupation = $tagger->get_tags(array('tag_id' => $occupation, 'single' => true));
                        $occupation = processToRender($occupation['tag_title']);
                        }
                    ?>
                    <div class="row each_news_container mb20 pb20" style="border-bottom: 1px solid #ddd">
                        <div class="col-12 col-sm-3">
                            <div class="featured_image_container">
                                <a href="<?php echo $detailLink; ?>"><img src="<?php echo get_image($item['content_thumbnail'],'200x200'); ?>" /></a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-9">
                            <h2 class="content_title"><a href="<?php echo $detailLink; ?>"><?php echo processToRender($item['content_title']); ?></a></h2>
                            <h3 class="content_subtitle"><a href="<?php echo $detailLink; ?>"><?php echo processToRender($item['content_sub_title']); ?></a></h3>
                            <div class="row">
                                <div class="col-6 col-md-3"><strong><?php echo ML('country'); ?>:</strong></div>
                                <div class="col-6 col-md-3"><?php echo handleEmptyStrings($WORLD_COUNTRY_LIST[$job_country]); ?></div>
                                <div class="col-6 col-md-3"><strong><?php echo ML('occupation'); ?>:</strong></div>
                                <div class="col-6 col-md-3"><?php echo handleEmptyStrings($occupation); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-3"><strong><?php echo ML('salary'); ?>:</strong></div>
                                <div class="col-6 col-md-3"><?php echo handleEmptyStrings($salary,'Negotiable'); ?></div>
                                <div class="col-6 col-md-3"><strong><?php echo ML('last_date'); ?>:</strong></div>
                                <div class="col-6 col-md-3"><?php echo print_date($expireDate,false,false); ?></div>
                            </div>
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
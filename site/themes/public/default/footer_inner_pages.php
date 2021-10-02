<div class="row footerContainer">
    <footer class="container">
        <div class="row">
            <div class="col-lg-3 col-sm-6 col-xs-12 mb20 aligner">
                <img src="<?php echo theme_path(); ?>/images/bpl_logo.png" style="width: 76px;height: 71px;margin-bottom: 30px;" />
                <p class="mb40 footerAboutUs"><?php echo processToRender(nl2br($footer_settings['about_us'])); ?></p>
               <!---   <p class="emailNewsLetterTitle mb20 aligner"><?php echo ML('email_newsletters'); ?></p>
              <form name="emailNewsLetterSubscription">
                    <input class="aligner" type="email" name="userEmail" placeholder="<?php echo ML('email_address'); ?>" />
                </form> !--->
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12 mb20 aligner">
                <div class="footerSectionTitle mb30">
                   <?php echo ML('latest_news'); ?>
                    <div><span class="highlightBorder"></span></div>
                </div>
                <ul class="footerLatestNews">
                    <?php
                    foreach($footerLatestNews['data'] as $i=>$v){
                        $detailLink = detail_url($v);
                        ?>
                        <li>
                            <a href="<?php echo $detailLink; ?>">
                                <img src="<?php echo get_image($v['content_thumbnail'], '100x100'); ?>" />
                                <div class="latestNewsLabelContainer">
                                    <div class="latestNewsLabel"><?php echo processToRender($v['content_title']); ?></div>
                                    <div class="latestNewsDate"><?php echo print_date($v['modified_at'],false,false,true); ?></div>
                                </div>
                            </a>
                        </li>
                        <?php
                        }
                    ?>
                </ul>
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12 mb20 aligner">
                <div class="footerSectionTitle mb30">
                 <?php echo ML('useful_links'); ?>
                    <div><span class="highlightBorder"></span></div>
                </div>
                <ul class="footerUsefulLinks collapsibleMenu"><?php echo $footerUsefulLinks; ?></ul>
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12 mb20 aligner">
                <div class="footerSectionTitle mb30">
                  <?php echo ML('get_in_touch'); ?> 
                    <div><span class="highlightBorder"></span></div>
                </div>
                <div class="oh footerGetInTouch">
                    <div class="getInTouchRow">
                        <div class="getInTouchLeft"><?php echo ML('address'); ?></div>
                        <div class="getInTouchRight"><?php echo processToRender(nl2br($get_in_touch['address'])); ?></div>
                    </div>
                    <div class="getInTouchRow">
                        <div class="getInTouchLeft"><?php echo ML('phone'); ?></div>
                        <div class="getInTouchRight"><?php echo processToRender(nl2br($get_in_touch['phone'])); ?></div>
                    </div>
                    <div class="getInTouchRow">
                        <div class="getInTouchLeft"><?php echo ML('email'); ?></div>
                        <div class="getInTouchRight"><?php echo processToRender(nl2br($get_in_touch['email'])); ?></div>
                    </div>
                    <div class="getInTouchRow">
                        <div class="getInTouchLeft"><?php echo ML('follow_us'); ?></div>
                        <div class="getInTouchRight">
                            <?php printSocialLinks() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row  footer_bottom_section">
            <div class="col-lg-12 tac">
               <!--- <div class="footer_bottom_menu">
                    <ul class="generalMenuBehave centerAlignedMenu"><?php echo $footerBottomMenu; ?></ul>
                </div> !---->
                <p><?php echo processToRender($footer_settings['copyright']) ?></p>
                <p><?php echo ML('designed_developed'); ?></p>
                <a href="http://3-devs.com"><img src="<?php echo theme_path(); ?>/images/3-devs-logo.png" /></a>
            </div>
        </div>
    </footer>
</div>
<?php include('footer.php') ?>
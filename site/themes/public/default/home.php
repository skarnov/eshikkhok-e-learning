<a href="<?php echo url('login'); ?>">LOGIN</a>
<a href="<?php echo url('logout'); ?>">LOGOUT</a>
<a href="<?php echo url('instructorszone'); ?>">INSTRUCTOR DASHBOARD</a>
<a href="<?php echo url('studentszone'); ?>">STUDENT DASHBOARD</a>
<?php
exit();
if($_GET['user_language']){
    $_SESSION['homepage_lang_selected'] = $_GET['user_language'];
    header('Location: '.build_url(null, array('user_language')));
    exit();
    }
include('header.php'); ?>
    <style type="text/css">
        .container{
            background-color: #f7f7f7;
        }
    </style>
    <div class="homepage_container_holder" style="background-image: url('<?php echo theme_path(); ?>/images/bg1.gif');">
        <div class="homepage_container"  style="background:url('<?php echo theme_path(); ?>/images/bg2.gif');">
            <div class="homepagelanguage topBar pl5 pr5 tar <?php echo !isset($_SESSION['homepage_lang_selected']) ? 'vh' : '' ?>">
                <div class="btn-group-sm">
                <a href="javascript:" class="current_language no-radius btn btn-sm btn-secondary"><?php echo $_config['langs'][$_config['slang']]['title'] ?></a>
                <?php
                foreach ($_config['langs'] as $v=>$i) {
                    if ($v == $_config['slang']) continue;
                    ?>
                    <a class="other_language btn btn-sm no-radius" href="<?php echo getChangeLanguageLink($v) ?>"><?php echo $i['title'] ?></a>
                    <?php
                    }
                ?>
                </div>
            </div>
            <div class="tac  pl5 pr5">
                <div class="tac homeLogoContainer">
                    <img src="<?php echo theme_path(); ?>/images/gif2.gif" />
                </div>
                <p class="fs_3 homeTitle" style="color: #fff"><?php echo thePageExtras('welcome_text'); ?></p>
                <div class="mb30 homePageBigBtnGroup tac">
                    <?php
                    if(isset($_SESSION['homepage_lang_selected'])){
                        ?>
                        <a href="<?php echo url('job_seekers'); ?>" class="btn"><?php echo ML('job_seekers'); ?></a>
                        <a href="<?php echo url('recruiters'); ?>" class="btn"><?php echo ML('recruiters'); ?></a>
                        <?php
                        }
                    else{
                        foreach ($_config['langs'] as $v=>$i) {
                            if ($v == $_config['slang']){
                                ?>
                                <a class="btn" href="<?php echo build_url(array('user_language' => $v)) ?>"><?php echo $i['in_language_title'] ?></a>
                                <?php
                                }
                            else{
                                ?>
                                <a class="btn" href="<?php echo build_url(array('user_language' => $v),null, getChangeLanguageLink($v)) ?>"><?php echo $i['in_language_title'] ?></a>
                                <?php
                                }
                            }
                        }
                    ?>
                </div>
                <div class="tac mb20">
                    <?php printSocialLinks() ?>
                </div>
                <div class="tac copyRight fs_4">
                    <?php echo ML('copy_right'); ?>
                </div>
            </div>
        </div>
    </div>
<?php include('footer.php'); ?>
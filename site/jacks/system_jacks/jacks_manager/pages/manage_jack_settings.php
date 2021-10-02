<?php
global $JACK_SETTINGS;

$post_error = false;
if($_POST){
    foreach($JACK_SETTINGS->settings[$_POST['jack_id']] as $i=>$v){
        if(isset($v['modify'])){
            if($v['modify'] == 'internal_external_link'){
                $theLink = $_POST[$i];
                if(strpos($theLink, _path('root')) !== false){
                    $_POST[$i] = trim(str_replace(_path('root'), '', $theLink),'/');
                    $_POST[$i.'_type'] = 'internal';
                    }
                else $_POST[$i] = 'external';
                }
            }
        }
    $ret = $JACK_SETTINGS->put_settings($_POST['jack_id'], $_POST);

    if($ret){
        if($ret == count($_POST))
            add_notification('Your settings has been saved.','success');
        else
            add_notification('One or more settings has not been saved.','error');

        user_activity::add_activity('Options for '.$_POST['jack_id'].' has been updated', 'success', 'update');

        if($_POST['lastTab'])
            header('location:'.build_url(array('lastTab'=>$_POST['lastTab'])));
        elseif($_GET['lastTab'])
            header('location:'.build_url(array('lastTab'=>$_GET['lastTab'])));
        else header('location:'.build_url());
        exit();
        }
    else{
        add_notification('Something went wrong, please try again.','error');
        $post_error = $_POST['jack_id'];
        }
    }
doAction('render_start');
?>
<div class="page-header">
    <h1>Manage Configurations</h1>
    <!--div class="oh">
        <div class="btn-group btn-group-sm">
            <a href="<?php echo $myUrl?>" class="btn btn-flat btn-labeled" title="">
                <span class="btn-label icon fa fa-cog"></span>
                Manage Jacks
            </a>
        </div>
    </div-->
</div>
	
<div class="panel panel-dark panel-primary">
    <div class="panel-body">
        <div class="side_aligned_tab">
        <ul id="uidemo-tabs-default-demo" class="nav nav-tabs">
        <?php
        if($JACK_SETTINGS->settings){
            $first = ' active ';
            $count = 0;
            foreach($JACK_SETTINGS->settings as $i=>$v){
                ?>
                <li  class="<?php echo $first?>">
                    <a data-id="<?php echo $i?>" href="#<?php echo $count++?>" data-toggle="tab"><?php echo $i?></a>
                </li>
                <?php
                $first = '';
                }
            }
        ?>
        </ul>
        <div class="tab-content tab-content-bordered">
            <?php
            if($JACK_SETTINGS->settings){
                $first = ' active in ';
                $count = 0;
                foreach($JACK_SETTINGS->settings as $i=>$v){
                    $actionUrl = build_url(array('lastTab'=>$i),null,current_url());
                    ?>
                    <div class="tab-pane oh fade <?php echo $first?>" id="<?php echo $count++?>">
                        <form name="jack_settings" method="post" action="<?php echo $actionUrl?>" enctype="multipart/form-data">
                            <div class="panel no-border m0 p0">
                                <div class="panel-body p0">
                                    <input type="hidden" name="jack_id" value="<?php echo $i?>" />
                                    <?php
                                    if($post_error)
                                        echo $JACK_SETTINGS->get_settings_form($i,$_POST);
                                    else
                                        echo $JACK_SETTINGS->get_settings_form($i);
                                    ?>
                                </div>
                                <div class="panel-footer tar">
                                    <input type="hidden" name="submitSettings" value="Save Settings" />
                                    <?php
                                    echo submitButtonGenerator(array(
                                        'name' => 'submitSettings',
                                        'value' => 'Save Settings',
                                        'text' => 'Save Settings',
                                        'title' => 'Save Settings',
                                        'action' => 'save',
                                        'size' => '',
                                        'icon' => 'icon_save'
                                        ));
                                    ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php
                    $first = '';
                    }
                }
            ?>
        </div>
    </div>
    </div>
</div>
<script type="text/javascript">
    init.push(function(){
        var lastTab = '<?php echo $_GET['lastTab'] ? $_GET['lastTab'] : ''?>';

        if(lastTab){
            $('#uidemo-tabs-default-demo li').removeClass('active');
            $('#uidemo-tabs-default-demo li a[data-id="'+lastTab+'"]').trigger('click');
            }

        $('textarea').autosize().css('resize', 'none');
    });
</script>
        
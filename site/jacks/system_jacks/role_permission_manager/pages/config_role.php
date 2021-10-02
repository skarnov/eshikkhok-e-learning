<?php
if($_GET['edit'] == '-1'){
    add_notification('Invalid role to configure, please select an user role to configure permissions.', 'error');
    header("Location: ".$myUrl);
    exit();
    }
if($_POST['permission_submit'] && $_GET['edit']){
    $data = array(
        'role_permissions' => serialize($_POST['permission'])
        );

    $update = $devdb->insert_update('dev_user_roles', $data, " pk_role_id = '".$_GET['edit']."'");
    if($update['success']){
        $this->reCachePermissions($_GET['edit']);
        add_notification("The Role Has Been Updated.","success");

        $args = array(
            'role_id' => $_GET['edit']
            );
        $roles = $this->get_roles($args);
        //$roles = $roles['data'];

        user_activity::add_activity("Permissions for the role (ID: ".$roles['pk_user_id'].") has been updated.",'success', 'update');
        header("Location:".$_SERVER['REQUEST_URI']);
        exit();
        }
    else{
        add_notification("The Role wasn't updated, please try again.","success");
        }
    }
//gather all permissions
global $cPermission;
//pre($cPermission);
//gather permissions of this role
$args = array(
    'role_id' => $_GET['edit']
    );
$roles = $this->get_roles($args);
//$roles = $roles['data'];
$user_permissions = unserialize($roles['role_permissions']);
//pre($roles);
function permission_form_html($sys_permission, $user_permission = array()){
    $output = '';
    foreach($sys_permission as $i=>$v){
        $checked = $user_permission[$i] ? 'checked="checked"' : '';
        if(is_array($v)){
            ob_start();
            ?>
            <div class="form-group ml30 mb0">
                <label class="mb15">
                    <input class="has_child px <?php echo strlen($checked) ? 'skip_child_check' : ''?>" <?php echo $checked; ?>type="checkbox" name="permission[<?php echo $i?>]" value="yes"/>
                    <span class="lbl"><?php echo str_replace('_',' ',ucfirst($i))?></span>
                </label>
                <div class="form-group ml20 <?php echo $i?>">
                    <?php echo permission_form_html($v,$user_permission,$output)?>
                </div>
            </div>
            <?php
            $output .= ob_get_clean();
            }
        else{
            $checked = $user_permission[$i] ? 'checked="checked"' : '';
            ob_start();
            ?>
            <div class="form-group ml30 mb0">
                <label>
                    <input class="px" <?php echo $checked; ?>type="checkbox" name="permission[<?php echo $i?>]" value="yes"/>
                    <span class="lbl"><?php echo $v?></span>
                </label>
            </div>
            <?php
            $output .= ob_get_clean();
            }
        }

    return $output;
    }

doAction('render_start');
?>
<div class="page-header">
    <h1>Configure Permissions for Role: <strong><?php echo $roles['user_fullname']?></strong></h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => $myUrl,
                'action' => 'list',
                'icon' => 'icon_list',
                'text' => 'All Roles',
                'title' => 'All Roles',
                'size' => 'sm',
                ));
            echo linkButtonGenerator(array(
                'href' => build_url(array('action' => 'assign_role')),
                'action' => 'add',
                'icon' => 'icon_dot',
                'text' => 'Assign Roles',
                'title' => 'Assign Roles to Users',
                'size' => 'sm',
            ));
            ?>
        </div>
    </div>
</div>
<div class="panel">
    <form name="widget_pos_add_edit" method="post" action="" enctype="multipart/form-data">
        <div class="panel-body">
            <div class="side_aligned_tab">
                <ul id="uidemo-tabs-default-demo" class="nav nav-tabs">
                <?php
                $first = true;
                $count = 1;
                foreach($cPermission->permissions as $i=>$v){
                    $thisId = 'item_'.$count;
                    ?>
                    <li class="<?php echo $first ? 'active' : ''; ?>">
                        <a href="#<?php echo $thisId; ?>" data-toggle="tab"><?php echo $i?></a>
                    </li>
                    <?php
                    $first = false;
                    $count += 1;
                    }
                ?>
                </ul>
                <div class="tab-content tab-content-bordered">
                    <?php
                    $first = true;
                    $count = 1;
                    foreach($cPermission->permissions as $i=>$v){
                        $thisId = 'item_'.$count;
                        ?>
                        <div class="tab-pane fade <?php echo $first ? 'active in' : ''; ?>" id="<?php echo $thisId; ?>">
                            <?php echo permission_form_html($v,$user_permissions) ?>
                        </div>
                        <?php
                        $first = false;
                        $count += 1;
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="panel-footer tar">
            <?php
            echo submitButtonGenerator(array(
                'action' => 'save',
                'icon' => 'icon_save',
                'text' => 'Save Permissions',
                'title' => 'Save Permissions',
                'name' => 'permission_submit',
                'value' => 'Save Permissions',
                'size' => ''
                ));
            ?>
        </div>
    </form>
</div>
<script type="text/javascript">
    init.push(function () {
        $('.switchers').switcher({ theme: 'square' });

        $(document).on('change','.has_child',function(){
            var ths = $(this);
            $is_checked = ths.is(':checked');
            if(ths.hasClass('skip_child_check')){
                ths.removeClass('skip_child_check');
                return false;
                }
            ths.closest('.form-group').find('>.form-group >.form-group input[type="checkbox"]').each(function(index, element){
                $this_checked = $(element).is(':checked');
                if($this_checked != $is_checked)
                    $(element).closest('label').trigger('click');
                if(!$is_checked) $(element).addClass('disabled').attr('disabled',true);
                else $(element).removeClass('disabled').removeAttr('disabled');
                });
            });
        $('.has_child').trigger('change');
        });
</script>
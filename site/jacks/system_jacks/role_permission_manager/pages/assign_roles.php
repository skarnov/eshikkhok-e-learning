<?php
$profileManager = jack_obj('dev_profile_management');
if($_POST && $_POST['name'] == 'assign_roles'){
    $user = $devdb->escape($_POST['pk']);
    $roles = $devdb->deep_escape($_POST['value']);

    $this->assign_role($user, $roles);

    echo json_encode(array('status' => 'success'));
    exit();
    }

$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 100;

if(!strlen($_GET['order_by'])){
    $_GET['order_by'] = 'user_fullname';
    $_GET['order'] = 'ASC';
    }

$args = array(
    'visible' => 1,
    'NOT' => array('user_role' => '-1'),
    'limit' => array(
        'start' => $start*$per_page_items,
        'count' => $per_page_items
        ),
    );
//appling filters
if($_GET['q']) $args['q'] = $_GET['q'];
if($_GET['status']) $args['user_status'] = $_GET['status'];
if($_GET['type']) $args['user_type'] = $_GET['type'];
if($_GET['order_by']) $args['order_by'] = array('col' => $_GET['order_by'], 'order' => ($_GET['order'] ? $_GET['order'] : 'ASC'));

$profileManager = jack_obj('dev_profile_management');

$users = $profileManager->get_users($args);

$pagination = pagination($users['total'],$per_page_items,$start);
$roles = $this->get_roles(array('data_only' => true));

doAction('render_start');
?>
<div class="page-header">
    <h1>Assign Roles to Users</h1>
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
            ?>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<div class="form-group col-sm-4">
    <label>Query</label>
    <input type="text" class="form-control <?php echo activeFilter('q'); ?>" name="q" value="<?php echo $_GET['q'] ? $_GET['q'] : ''?>" />
</div>
<div class="form-group col-sm-4">
    <label>Status</label>
    <select class="form-control <?php echo activeFilter('status'); ?>" name="status">
        <option value="">All</option>
        <option value="active" <?php echo $_GET['status'] == 'active' ? 'selected' : ''?>>Active</option>
        <option value="inactive" <?php echo $_GET['status'] == 'inactive' ? 'selected' : ''?>>Inactive</option>
    </select>
</div>
<div class="form-group col-sm-4">
    <label>Type</label>
    <select class="form-control <?php echo activeFilter('type'); ?>" name="type">
        <option value="">All</option>
        <option value="admin" <?php echo $_GET['type'] == 'admin' ? 'selected' : ''?>>Admin</option>
        <option value="public" <?php echo $_GET['type'] == 'public' ? 'selected' : ''?>>Public</option>
    </select>
</div>
<?php
$filterForm = ob_get_clean();
filterForm($filterForm);
?>
<div class="table-primary table-responsive">
    <div class="table-header">
        <?php echo searchResultText($users['total'],$start,$per_page_items,count($users['data']),'')?>
    </div>
    <table class="table table-hover table-striped table-bordered table-condensed">
        <thead>
        <tr>
            <th><?php echo get_sortable_column_link('User', 'user_fullname', 'alpha'); ?></th>
            <th class="tar">Role</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($users['data'] as $i=>$item){
            ?>
            <tr>
                <td>
                    <div class="oh fl">
                        <div class="fl mrmb5" style="width: 64px; height: 64px;text-align: center;border: 1px solid #fff;">
                            <img src="<?php echo $item['rel_user_picture']; ?>" alt="<?php echo $item['user_fullname']; ?>" style="max-height: 62px;max-width: 62px"/>
                        </div>
                    </div>
                    <div class="oh">
                        <?php echo $item['user_fullname']?>
                    </div>
                </td>
                <td class="tar vam ">
                    <a href="javascript:" data-placement="left" class="assign_roles" data-value="<?php echo $item['user_roles']; ?>" id="assign_roles" data-type="checklist" data-pk="<?php echo $item['pk_user_id']; ?>" data-title="Select Roles"></a>
                </td>
            </tr>
            <?php
            }
        ?>
        </tbody>
    </table>
    <div class="table-footer">
        <?php echo $pagination ? $pagination : ''?>
    </div>
</div>
<script type="text/javascript">
    init.push(function(){
        $('.assign_roles').editable({
            //mode: "inline",
            //params: {ajax_type: 'assign_roles'},
            url: window.location.href,
            ajaxOptions: {
                success: function(response, newValue){
                    response = jQuery.parseJSON(response);
                    if(response.status == 'success') $.growl.notice({title: 'Approved Status Updated.', message : ''});
                    else $.growl.error({title: 'Update failed.', message : response.msg});
                    }
                },
            source: [
                <?php
                foreach($roles['data'] as $i=>$v){
                    echo "{value: '".$v['pk_role_id']."', text: '".$v['role_name']."'},";
                    }
                ?>
                ],
            });
        });

</script>
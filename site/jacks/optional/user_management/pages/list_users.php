<?php
$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 10;

if(!strlen($_GET['order_by'])){
    $_GET['order_by'] = 'user_fullname';
    $_GET['order'] = 'ASC';
    }

$args = array(
    'visible' => 1,
	'limit' => array(
		'start' => $start*$per_page_items,
		'count' => $per_page_items
		),
	);
//if(!HAS_USER('sadmin')) $args['NOT']['user_role'] = '-1';
$args['NOT']['user_role'] = '-1';

//appling filters
if($_GET['user_id']) $args['user_id'] = $_GET['user_id'];
if($_GET['status']) $args['user_status'] = $_GET['status'];
if($_GET['type']) $args['user_type'] = $_GET['type'];
if($_GET['order_by']) $args['order_by'] = array('col' => $_GET['order_by'], 'order' => ($_GET['order'] ? $_GET['order'] : 'ASC'));

$users = $this->get_users($args);

$pagination = pagination($users['total'],$per_page_items,$start);
doAction('render_start');
?>
<div class="page-header">
    <h1>All Users</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => $myUrl.'?action=add_edit_user',
                'action' => 'add',
                'icon' => 'icon_add',
                'text' => 'New User',
                'title' => 'Manage New User',
                ));
            ?>
        </div>
    </div>
</div>
<?php
ob_start();
?>
<div class="form-group col-sm-3" id="user_autocomplete_container">
    <label>User</label>
    <script type="text/javascript">
        init.push(function(){
            new set_autosuggest({
                container: '#user_autocomplete_container',
                //submit_labels: false,
                ajax_page: _root_path_+'/api/dev_profile_management/get_user_auto_complete',
                single: true,
                input_field: '#input_user_id',
                field_name: 'user_id',
                existing_items: <?php echo $_GET['user_id'] ? to_json_object(array(array('id' => $_GET['user_id'], 'label' => $_GET['user_id_label']))) : '{}';?>,
                parameters: {visible: 1}
            });
        });
    </script>
</div>
<div class="form-group col-sm-2">
    <label>Status</label>
    <select class="form-control <?php echo activeFilter('status'); ?>" name="status">
        <option value="">All</option>
        <option value="active" <?php echo $_GET['status'] == 'active' ? 'selected' : ''?>>Active</option>
        <option value="inactive" <?php echo $_GET['status'] == 'inactive' ? 'selected' : ''?>>Inactive</option>
    </select>
</div>
<div class="form-group col-sm-2">
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
        <?php echo searchResultText($users['total'],$start,$per_page_items,count($users['data']),'users')?>
    </div>
    <table class="table table-bordered table-condensed">
        <thead>
        <tr>
            <th><?php echo get_sortable_column_link('Name', 'user_fullname', 'alpha'); ?></th>
            <th><?php echo get_sortable_column_link('Email', 'user_email', 'alpha'); ?></th>
            <th><?php echo get_sortable_column_link('Type', 'user_type', 'alpha'); ?></th>
            <th><?php echo get_sortable_column_link('Status', 'user_status', 'alpha'); ?></th>
            <th class="tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($users['data'] as $i=>$user){
            ?>
            <tr>
                <td>
                    <div class="oh fl">
                        <div class="fl mrmb5" style="width: 52px; height: 52px;text-align: center;border: 1px solid #fff;">
                            <img src="<?php echo $user['rel_user_picture'] ?>" alt="<?php echo $user['user_fullname']; ?>" style="max-height: 50px;max-width: 50px"/>
                        </div>
                    </div>
                    <div class="oh">
                        <?php echo $user['user_fullname']; ?>
                    </div>
                </td>
                <td><?php echo $user['user_email']; ?></td>
                <td><?php echo ucfirst($user['user_type']); ?></td>
                <td><?php echo ucfirst($user['user_status']); ?></td>
                <td class="tar action_column">
                    <?php if(has_permission('edit_user')):?>
                        <div class="btn-toolbar">
                        <?php
                        echo linkButtonGenerator(array(
                            'href' => build_url(array('action' => 'add_edit_user', 'edit' => $user['pk_user_id'])),
                            'action' => 'edit',
                            'icon' => 'icon_edit',
                            'text' => 'Edit',
                            'title' => 'Edit User',
                        ));
                        echo linkButtonGenerator(array(
                            'href' => build_url(array('action' => 'view_profile', 'view' => $user['pk_user_id'])),
                            'action' => 'view',
                            'icon' => 'icon_view',
                            'text' => 'View',
                            'title' => 'View User',
                        ));
                        ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
            }
        ?>
        </tbody>
    </table>
    <div class="table-footer">
        <?php echo $pagination?>
    </div>
</div>
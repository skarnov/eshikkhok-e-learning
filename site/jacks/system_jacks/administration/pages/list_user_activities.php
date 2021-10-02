<?php
$activity_status_badges = array(
    'success' => 'success',
    'error' => 'danger'
    );
$activity_type_badges = array(
    'create' => 'info',
    'read' => 'success',
    'update' => 'warning',
    'delete' => 'danger',
    'login' => 'primary',
    'logout' => 'primary',
    );
if($_POST['ajax_type'] == 'delete_single_log'){
    $ret = array('success' => array(), 'error' => array());
    if(has_permission('manage_user_activities')){
        $log = trim($_POST['log_id']);
        if(strlen($log)){
            $ret2 = user_activity::delete_activity($log);
            if($ret2) $ret['success'] = 1;
            else $ret['error'][] = 'Could not delete the log.';
            }
        else $ret['error'][] = 'Invalid Log.';
        }
    else $ret['error'][] = 'You do not have enough permission to delete logs.';

    echo json_encode($ret);
    exit();
    }
if($_GET['flush']){
    if(!has_permission('manage_user_activities')){
        add_notification('You do not have enough permission to flush logs.','error');
        user_activity::add_activity($_config['user']['user_fullname'].' tried to flush logs without permission.','error', 'delete');
        header('Location:'.$myUrl);
        exit();
        }
	$ret = user_activity::delete_activity();
	
	if($ret){
		add_notification('All Logs has been deleted.','success');
		user_activity::add_activity('All User Activity Logs has been deleted.','success', 'delete');
		}
	else add_notification('All Logs has not been deleted, please try again.','error');
	
	header('location:'.$myUrl);
	exit();
	}
	
$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 50;

if(!strlen($_GET['order_by'])){
    $_GET['order_by'] = 'created_at';
    $_GET['order'] = 'DESC';
    }

$args = array(
	'limit' => array(
		'start' => $start * $per_page_items,
		'count' => $per_page_items
		),
	);

if($_GET['type']) $args['type'] = array($_GET['type']);
if($_GET['user_id']) $args['user_id'] = array($_GET['user_id']);
if($_GET['order_by']) $args['order_by'] = array('col' => $_GET['order_by'], 'order' => ($_GET['order'] ? $_GET['order'] : 'ASC'));

$activities = user_activity::get_activity($args);

$pagination = pagination($activities['total'],$per_page_items,$start);
doAction('render_start');
?>
<div class="page-header">
    <h1>All User Activities</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php if(has_permission('manage_user_activities')):?>
            <?php echo buttonButtonGenerator(array(
                        'action' => 'delete',
                        'icon' => 'icon_delete',
                        'text' => 'Flush Logs',
                        'size' => 'sm',
                        'classes' => 'flush_logs')) ?>
            <?php endif;?>
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
    <label>Type</label>
    <select class="form-control <?php echo activeFilter('type'); ?>" name="type">
        <option value="">Any</option>
        <option value="create" <?php echo $_GET['type'] == 'create' ? 'selected' : ''?>>Create</option>
        <option value="read" <?php echo $_GET['type'] == 'read' ? 'selected' : ''?>>Read</option>
        <option value="update" <?php echo $_GET['type'] == 'update' ? 'selected' : ''?>>Update</option>
        <option value="delete" <?php echo $_GET['type'] == 'delete' ? 'selected' : ''?>>Delete</option>
        <option value="login" <?php echo $_GET['type'] == 'login' ? 'selected' : ''?>>Login</option>
        <option value="logout" <?php echo $_GET['type'] == 'logout' ? 'selected' : ''?>>Logout</option>
    </select>
</div>
<!--<div class="form-group col-sm-2">
    <label>Status</label>
    <select class="form-control" name="type">
        <option value="">Any</option>
        <option value="success" <?php /*echo $_GET['type'] == 'success' ? 'selected' : ''*/?>>Success</option>
        <option value="error" <?php /*echo $_GET['type'] == 'error' ? 'selected' : ''*/?>>Error</option>
    </select>
</div>-->
<?php
$filterForm = ob_get_clean();
filterForm($filterForm);
?>
<div class="table-primary table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th class="tac vam"><?php echo get_sortable_column_link('Type', 'activity_type', 'alpha'); ?></th>
                <!--<th class="tac vam">Type</th>-->
                <th>Activity</th>
                <th><?php echo get_sortable_column_link('User', 'user_fullname', 'alpha'); ?></th>
                <th><?php echo get_sortable_column_link('Date and Time', 'created_at', 'numeric'); ?></th>
                <th class="tar action_column">...</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach($activities['data'] as $i=>$item){
        ?>
            <tr>
                <td class="tac vam"><span class="badge badge-<?php echo $activity_type_badges[$item['activity_type']]; ?>"><?php echo ucfirst($item['activity_type'])?></span></td>
                <!--<td class="tac vam"><span class="badge badge-<?php /*echo $activity_status_badges[$item['activity_status']]; */?>"><?php /*echo ucfirst($item['activity_status'])*/?></span></td>-->
                <td><?php
                    if(strlen($item['activity_url']))
                        echo '<a href="'.$item['activity_url'].'" target="_blank">'.$item['activity_msg'].'</a>';
                    else
                        echo $item['activity_msg']?>
                </td>
                <td><?php echo $item['user_fullname']?></td>
                <td><?php echo $item['created_at']?></td>
                <td class="tar action_column">
                    <?php if(has_permission('manage_user_activities')):?>
                        <?php echo buttonButtonGenerator(array(
                                'action' => 'delete',
                                'icon' => 'icon_delete',
                                'text' => 'Delete',
                                'title' => 'Delete Log',
                                'attributes' => array('data-id' => $item['pk_activity_log']),
                                'classes' => 'delete_single_log')) ?>
                    <?php endif;?>
                </td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-footer oh">
        <div class="pull-left">
            <?php echo $pagination?>
        </div>
    </div>
</div>
<script type="text/javascript">
    init.push(function(){
        $(document).on('click', '.delete_single_log', function(){
            var ths = $(this);
            var thisCell = ths.closest('td');
            var thisRow = ths.closest('tr');
            var logId = ths.attr('data-id');
            if(!logId) return false;

            show_button_overlay_working(thisCell);
            bootboxConfirm({
                title: 'Delete Log',
                msg: 'Do you really want to delete this log?',
                confirm: {
                    callback: function(){
                        basicAjaxCall({
                            url: _current_url_,
                            data: {
                                ajax_type: 'delete_single_log',
                                log_id: logId,
                                },
                            success: function(ret){
                                if(ret.success){
                                    thisRow.slideUp('slow').remove();
                                    $.growl.notice({message: 'Log deleted.'});
                                    }
                                else growl_error(ret.error);
                                }
                            });
                        }
                    },
                cancel: {
                    callback: function(){
                        hide_button_overlay_working(thisCell);
                        }
                    }
                });
            });
        $(document).on('click', '.flush_logs', function(){
            var ths = $(this);
            var thisCell = ths.closest('td');

            show_button_overlay_working(thisCell);
            bootboxConfirm({
                title: 'Flush Log',
                msg: 'Do you really want to delete all logs?',
                confirm: {
                    callback: function(){
                        window.location.href = build_url({flush: 1});
                        }
                    },
                cancel: {
                    callback: function(){
                        hide_button_overlay_working(thisCell);
                        }
                    }
                });
            });
        });
</script>
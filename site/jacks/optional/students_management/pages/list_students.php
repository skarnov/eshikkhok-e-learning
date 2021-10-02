<?php
global $multilingualFields;

$delete = $_GET['delete'] ? $_GET['delete'] : NULL;

if($delete){
    $result = $this->delete_student($delete);
    header('location:'.$myUrl);
    exit();
    }

$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 20;

if($this->default_role){
    $args = array(
        'user_role' => $this->default_role,
        'order_by' => array(
            'col' => 'dev_users.user_fullname',
            'order' => 'ASC'
            ),
        'limit' => array(
            'start' => $start*$per_page_items,
            'count' => $per_page_items
            ),
        );

    if($_GET['sort_by']) $args['order_by']['col'] = 'dev_users.'.$_GET['sort_by'];
    if($_GET['order']) $args['order_by']['order'] = $_GET['order'];
    if($_GET['q']) $args['q'] = $_GET['q'];
    if($args['q']){
        $args['full_string'] = true;
        $args['q_only_title'] = true;
        }

    $all_students = $this->get_students($args);

    $pagination = pagination($all_students['total'],$per_page_items,$start);
    }
else{
    $all_students = array('data' => array());
    $pagination = null;
    }


doAction('render_start');
?>
<div class="page-header">
    <h1>All Students</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php if(has_permission('add_student')):?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_student')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New Student',
                    'title' => 'Create New Student',
                    'size' => 'sm',
                ));
                ?>
            <?php endif;?>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<div class="form-group col-sm-2">
    <label>Student Name</label>
    <input type="text" class="form-control" name="q" placeholder="Type title ..." value="<?php echo $_GET['q'] ? $_GET['q'] : ''?>" />
</div>
<?php
$formFilter = ob_get_clean();
filterForm($formFilter, array('instructor'));
?>
<div class="table-primary table-responsive">
    <div class="table-header"><?php echo searchResultText($all_students['total'], $start, $per_page_items, count($all_students['data']), 'instructors'); ?></div>
    <table class="student-table table table-bordered table-condensed table-hover">
        <thead>
        <tr>
            <th>Student Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Status</th>
            <th class="tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if($all_students['data']){
            foreach($all_students['data'] as $item){
                ?>
                <tr>
                    <td><?php echo $item['user_fullname'] ?></td>
                    <td><?php echo $item['user_email'] ?></td>
                    <td><?php echo $item['user_mobile'] ?></td>
                    <td><?php echo dbReadableString($item['user_status'])?></td>
                    <td class="action_column">
                        <?php if(has_permission('edit_student')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'href' => build_url(array('action' => 'add_edit_student', 'edit' => $item['pk_user_id'])),
                                'action' => 'edit',
                                'icon' => 'icon_edit',
                                'text' => 'Edit',
                                'title' => 'Edit Student',
                            ));
                            ?>
                        <?php endif;?>
                        <?php if(has_permission('delete_student')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'action' => 'remove',
                                'icon' => 'icon_remove',
                                'text' => 'Delete',
                                'title' => 'Delete Student',
                                'classes' => 'confirm_delete',
                                'attributes' => array('rel' => build_url(array('delete' => $item['pk_user_id'])))
                            ));
                            ?>
                        <?php endif;?>
                    </td>
                </tr>
                <?php
                }
            }
        ?>
        </tbody>
    </table>
    <?php
    if($pagination){
        ?>
        <div class="table-footer oh">
            <?php echo $pagination ?>
        </div>
        <?php
        }
    ?>
</div>
<script type="text/javascript">
    var myUrl = '<?php echo $myUrl?>';
    init.push(function(){
        emptyTableFill($('.student-table'));
        });
</script>
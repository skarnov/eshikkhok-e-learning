<?php
global $multilingualFields;

//TODO: Delete operation

$delete = $_GET['delete'] ? $_GET['delete'] : NULL;
if($delete){
    $result = $this->delete_course($delete);
    header('location:'.$myUrl);
    exit();
}

$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 20;

$args = array(
    'type' => 'course',
    'order_by' => array(
            'col' => 'e_courses.created_at_int',
            'order' => 'DESC'
            ),
    'limit' => array(
            'start' => $start*$per_page_items,
            'count' => $per_page_items
            ),
    );

if($_GET['sort_by']) $args['order_by']['col'] = 'e_courses.'.$_GET['sort_by'];
if($_GET['order']) $args['order_by']['order'] = $_GET['order'];
if($_GET['q']) $args['q'] = $_GET['q'];
if($args['q']){
    $args['full_string'] = true;
    $args['q_only_title'] = true;
    }
    
    if($_GET['publication_status']){
        $args['publication_status'] = $_GET['publication_status'];
    }
    if($_GET['access_mode']){
        $args['access_mode'] = $_GET['access_mode'];
    }
    if($_GET['item_difficulties']){
        $args['item_difficulties'] = $_GET['item_difficulties'];
    }
    if($_GET['item_language']){
        $args['item_language'] = $_GET['item_language'];
    }

    
$all_courses = $this->get_courses($args);

$pagination = pagination($all_courses['total'],$per_page_items,$start);

doAction('render_start');
?>
<div class="page-header">
    <h1>All Courses</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php if(has_permission('add_course')):?>
                <?php
                echo linkButtonGenerator(array(
                    'href' => build_url(array('action' => 'add_edit_course')),
                    'action' => 'add',
                    'icon' => 'icon_add',
                    'text' => 'New Course',
                    'title' => 'Create New Course',
                    'size' => 'sm',
                ));
                ?>
            <?php endif;?>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<div class="form-group col-sm-2">
    <label>Course Title</label>
    <input type="text" class="form-control" name="q" placeholder="Type title ..." value="<?php echo $_GET['q'] ? $_GET['q'] : ''?>" />
</div>
<div class="form-group col-sm-2">
    <label>Publication Status</label>
    <select class="form-control" name="publication_status">
        <option value="">Any</option>
        <option value="drafted" <?php echo $_GET['publication_status'] == 'drafted' ? 'selected' : ''?>>Drafted</option>
        <option value="published" <?php echo $_GET['publication_status'] == 'published' ? 'selected' : ''?>>Published</option>
        <option value="pending-approval" <?php echo $_GET['publication_status'] == 'pending-approval' ? 'selected' : ''?>>Pending Approval</option>
        <option value="approved" <?php echo $_GET['publication_status'] == 'approved' ? 'selected' : ''?>>Approved</option>
        <option value="paused" <?php echo $_GET['publication_status'] == 'paused' ? 'selected' : ''?>>Paused</option>
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Access Mode</label>
    <select class="form-control" name="access_mode">
        <option value="">Any</option>
        <option value="online" <?php echo $_GET['access_mode'] == 'online' ? 'selected' : ''?>>Online</option>
        <option value="offline" <?php echo $_GET['access_mode'] == 'offline' ? 'selected' : ''?>>Offline</option>
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Course Difficulties</label>
    <select class="form-control" name="item_difficulties">
        <option value="">Any</option>
        <option value="beginner" <?php echo $_GET['item_difficulties'] == 'beginner' ? 'selected' : ''?>>Beginner</option>
        <option value="medium" <?php echo $_GET['item_difficulties'] == 'medium' ? 'selected' : ''?>>Medium</option>
        <option value="advanced" <?php echo $_GET['item_difficulties'] == 'advanced' ? 'selected' : ''?>>Advanced</option>
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Course Language</label>
    <select class="form-control" name="item_language">
        <option value="">Any</option>
        <option value="bangla" <?php echo $_GET['item_language'] == 'bangla' ? 'selected' : ''?>>Bangla</option>
        <option value="english" <?php echo $_GET['item_language'] == 'english' ? 'selected' : ''?>>English</option>
    </select>
</div>

<?php
$formFilter = ob_get_clean();
filterForm($formFilter, array('course'));
?>
<div class="table-primary table-responsive">
    <div class="table-header"><?php echo searchResultText($all_courses['total'], $start, $per_page_items, count($all_courses['data']), 'courses'); ?></div>
    <table class="course-table table table-bordered table-condensed table-hover">
        <thead>
        <tr>
            <th>Course Title</th>
            <th>Price</th>
            <th>Difficulties</th>
            <th>Status</th>
            <th class="tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if($all_courses['data']){
            foreach($all_courses['data'] as $item){
                ?>
                <tr>
                    <td><?php echo processToRender($item['item_title'])?></td>
                    <td><?php echo processToRender($item['price'])?></td>
                    <td><?php echo dbReadableString($item['item_difficulties'])?></td>
                    <td><?php echo dbReadableString($item['publication_status'])?></td>
                    <td class="action_column">
                        <?php if(has_permission('view_course')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'href' => build_url(array('action' => 'add_edit_course', 'edit' => $item['pk_item_id'])),
                                'action' => 'edit',
                                'icon' => 'icon_edit',
                                'text' => 'Edit',
                                'title' => 'Edit Course',
                            ));
                            ?>
                        <?php endif;?>
                        <?php if(has_permission('delete_course')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'action' => 'remove',
                                'icon' => 'icon_remove',
                                'text' => 'Delete',
                                'title' => 'Delete Course',
                                'classes' => 'confirm_delete',
                                'attributes' => array('rel' => build_url(array('delete' => $item['pk_item_id'])))
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
            <?php echo $pagination?>
        </div>
        <?php
        }
    ?>
</div>
<script type="text/javascript">
    var myUrl = '<?php echo $myUrl?>';
    init.push(function(){
        emptyTableFill($('.course-table'));
        });
</script>
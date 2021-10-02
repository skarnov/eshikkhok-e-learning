<?php
global $multilingualFields;

//TODO: Delete operation

$newargs = array(
    'type' => 'course',        
    'order_by' => array(
            'col' => 'e_courses.created_at_int',
            'order' => 'DESC'
        ),
    );

$all_courses = $this->get_courses($newargs);

$delete = $_GET['delete'] ? $_GET['delete'] : NULL;

if($delete){
    $result = $this->delete_course($delete);
    header('location:'.$myUrl);
    exit();
}

$start = $_GET['start'] ? $_GET['start'] : 0;
$per_page_items = 20;

$args = array(
    'type' => 'lecture',    
    'course_name' => true,    
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

$all_lectures = $this->get_courses($args);

$pagination = pagination($all_lectures['total'],$per_page_items,$start);

doAction('render_start');
?>
<div class="page-header">
    <h1>All Lectures</h1>
    <div class="oh">
         <script>             
		init.push(function () {   
                    $('#bs-x-editable-lecture').editable({
                        mode: 'inline',
                        placement:'bottom',
                        prepend: "Select Course",
                        source: [
                            <?php foreach ($all_courses['data'] as $v_course):
                                echo "{value:".$v_course['pk_item_id'].", text: '".processToRender($v_course['item_title'])."'},";
                            endforeach ?>
                        ],
                        display: function(value, sourceData) {
                            if(value){
                                window.location.href = '<?php echo url('admin/dev_course_management/manage_lectures?action=add_edit_lecture&course_id=') ?>' + value;
                            }
                        }
                    });				
		});
            </script>
            <?php if(has_permission('add_lecture')):?>
                <a href="#" class="btn btn-success text-white" id="bs-x-editable-lecture" data-type="select" data-pk="1" data-value="" data-title="Select lecture" class="editable editable-click" style="color: gray;" data-original-title="" title="">New Lecture</a>
            <?php endif;?>

        <div class="btn-group btn-group-sm">
            <?php
//                echo linkButtonGenerator(array(
//                    'href' => build_url(array('action' => 'add_edit_lecture')),
//                    'action' => 'add',
//                    'icon' => 'icon_add',
//                    'text' => 'New Lecture',
//                    'title' => 'Create New Lecture',
//                    'size' => 'sm',
//                ));
            ?>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<div class="form-group col-sm-2">
    <label>Title</label>
    <input type="text" class="form-control" name="q" placeholder="Type title ..." value="<?php echo $_GET['q'] ? $_GET['q'] : ''?>" />
</div>
<div class="form-group col-sm-2">
    <label>Status</label>
    <select class="form-control" name="content_status">
        <option value="">Any</option>
        <option value="published" <?php echo $_GET['content_status'] == 'published' ? 'selected' : ''?>>Published</option>
        <option value="draft" <?php echo $_GET['content_status'] == 'draft' ? 'selected' : ''?>>Draft</option>
        <!--<option value="pending" <?php /*echo $_GET['content_status'] == 'pending' ? 'selected' : ''*/?>>Pending</option>-->
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Pages</label>
    <select class="form-control" name="page_id">
        <option value="">Any</option>
        <option value="-1" <?php echo isset($_GET['page_id']) && $_GET['page_id'] == '-1' ? 'selected' : ''; ?>>No Page</option>
        <?php
        echo getPageSelectOptions($_GET['page_id'],null,0,null,true);
        ?>
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Sort by</label>
    <select class="form-control" name="sort_by">
        <option value="created_at_int" >Create Time</option>
        <option value="content_published_time" <?php echo $_GET['sort_by'] == 'content_published_time' ? 'selected' : ''; ?>>Published Time</option>
        <option value="modified_at_int" <?php echo $_GET['sort_by'] == 'modified_at' ? 'selected' : ''; ?>>Last Modified Time</option>
        <option value="content_view_count" <?php echo $_GET['sort_by'] == 'content_view_count' ? 'selected' : ''; ?>>Views</option>
    </select>
</div>
<div class="form-group col-sm-2">
    <label>Sort Order</label>
    <select class="form-control" name="order">
        <option value="">Descending</option>
        <option value="ASC" <?php echo $_GET['order'] == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
    </select>
</div>
<?php
$formFilter = ob_get_clean();
filterForm($formFilter, array('item_type'));
?>
<div class="table-primary table-responsive">
    <div class="table-header"><?php echo searchResultText($all_lectures['total'], $start, $per_page_items, count($all_lectures['data']), 'lectures'); ?></div>
    <table class="lecture-table table table-bordered table-condensed table-hover">
        <thead>
        <tr>
            <th>Course Name</th>
            <th>Lecture Title</th>
            <th>Price</th>
            <th>Difficulties</th>
            <th>Status</th>
            <th class="tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if($all_lectures['data']){
            foreach($all_lectures['data'] as $item){
                ?>
                <tr>
                    <td><?php echo processToRender($item['course_name']['item_title']) ?></td>
                    <td><?php echo processToRender($item['item_title'])?></td>
                    <td><?php echo processToRender($item['price'])?></td>
                    <td><?php echo dbReadableString($item['item_difficulties'])?></td>
                    <td><?php echo dbReadableString($item['publication_status'])?></td>
                    <td class="action_column">
                        <?php if(has_permission('edit_lecture')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'href' => build_url(array('action' => 'add_edit_lecture', 'edit' => $item['pk_item_id'])),
                                'action' => 'edit',
                                'icon' => 'icon_edit',
                                'text' => 'Edit',
                                'title' => 'Edit Lecture',
                            ));
                            ?>
                        <?php endif;?>
                        <?php if(has_permission('delete_lecture')):?>
                            <?php
                            echo linkButtonGenerator(array(
                                'action' => 'remove',
                                'icon' => 'icon_remove',
                                'text' => 'Delete',
                                'title' => 'Delete Lecture',
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
        emptyTableFill($('.lecture-table'));
        });
</script>
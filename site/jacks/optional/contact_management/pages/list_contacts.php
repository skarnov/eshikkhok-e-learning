<?php
$contactType = $_GET['contact_type'] ? $_GET['contact_type'] : 'general';
$contactConfig = $this->contactTypes[$contactType];

if($_GET['delete']){
    if(!has_permission('delete_contacts')){
        add_notification('You don\'t have enough permission to delete contacts.','error');
        header('Location:'.build_url(NULL,array('delete')));
        exit();
        }

    $sql = "DELETE FROM dev_contacts WHERE pk_con_id = '".$_GET['delete']."'";
    $ret = $devdb->query($sql);
    if($ret){
        add_notification($contactConfig->name.' has been deleted.','success');
        user_activity::add_activity('A '.$contactConfig->name.' (ID: '.$_GET['delete'].') has been deleted','success', 'delete');
        }
    else add_notification($contactConfig->name.' has not been delete, please try again.','error');

    header('location:'.build_url(null,array('delete')));
    exit();
    }

$per_page_items = 50;
$start = $_GET['start'] ? $_GET['start'] : 0;
$args = array(
    'COMBINED_LIKE' => array(
        'name' => $_GET['q'] ? $_GET['q'] : null,
        'email' => $_GET['q'] ? $_GET['q'] : null,
        'content' => $_GET['q'] ? $_GET['q'] : null,
        ),
    'COMBINED_LIKE_OPERATOR' => 'OR',
    'type' => $contactType,
    'limit' => array(
        'start' => $start*$per_page_items,
        'count' => $per_page_items,
        ),
    'order_by' => array(
        'col' => 'dev_contacts.con_created_at',
        'order' => 'DESC'
        ),
    );

$contacts = $this->get_contact_entry($args);
$pagination = pagination($contacts['total'],$per_page_items,$start);
doAction('render_start');
?>
<div class="page-header">
    <h1>All <?php echo $contactConfig->label; ?></h1>
</div>
<?php ob_start() ?>
<div class="form-group col-sm-3">
    <label>Query</label>
    <input class="form-control" type="text" name="q" value="<?php echo $_GET['q'] ? $_GET['q'] : ''?>" />
</div>
<?php
$filterForm = ob_get_clean();
filterForm($filterForm, array('contact_type'));
?>
<div class="table-primary table-responsive">
    <table class="contacts-table table table-condensed table-hover table-bordered">
        <thead>
        <tr>
            <?php
            foreach($contactConfig->view_columns as $i=>$v){
                echo '<th>'.$v.'</th>';
                }
            ?>
            <th class="tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($contacts['data'] as $i=>$item){
            ?>
            <tr>
                <?php
                foreach($contactConfig->view_columns as $field=>$label){
                    echo '<td>'.$this->getPrintableValue($field, $item).'</td>';
                    }
                ?>
                <td class="tar action_column">
                    <?php
                    if(has_permission('delete_contacts')){
                        echo linkButtonGenerator(array(
                            'action' => 'remove',
                            'icon' => 'icon_remove',
                            'text' => 'Delete',
                            'title' => 'Delete Message',
                            'classes' => 'confirm_delete',
                            'attributes' => array('rel' => build_url(array('delete' => $item['pk_con_id'])))
                            ));
                        }
                    ?>
                </td>
            </tr>
        <?php
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
    init.push(function(){
        emptyTableFill($('.contacts-table'));
        });
</script>
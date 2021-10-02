<?php
global $multilingualFields;

$cTypes = $this->get_content_types();
$cTypes_status = array();
foreach($cTypes as $i=>$v){
    $cTypes_status[$i] = 'off';
    }

if($_POST['submit']){
    foreach($_POST['ctype'] as $i=>$v){
        $cTypes_status[$i] = 'on';
        }

    foreach($cTypes_status as $i=>$v){
        $updateCtype = array(
            'ctype_status' => $v
            );
        $update = $devdb->insert_update('dev_content_types',$updateCtype," content_type_slug = '".$i."'");
        }

    add_notification('Content Types has been updated.','success');
    header('location:'.current_url());
    exit();
    }

doAction('render_start');
?>
<div class="page-header">
    <h1>All Content Types</h1>
</div>
<form name="content_type_form" method="post" action="">
    <div class="table-primary table-responsive">
        <table class="table table-bordered table-condensed table-hover">
            <thead>
            <tr>
                <th>Content Type</th>
                <th class="tar action_column">Status</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($cTypes as $i=>$item){
                if($multilingualFields['dev_content_types']){
                    foreach($item as $index=>$value){
                        if(in_array($index, $multilingualFields['dev_content_types']) !== false)
                            $item[$index] = processToRender($item[$index]);
                    }
                }
            ?>
                <tr>
                    <td>
                        <?php
                        echo $item['content_type_title'].' (<em class="color-grey">'.$item['content_type_slug'].'</em>)';
                        ?>
                    </td>
                    <td class="tar action_column">
                        <div class="switchers-colors-square">
                            <input type="checkbox" name="ctype[<?php echo $item['content_type_slug']?>]" <?php echo $item['ctype_status'] == 'on' ? 'checked' : ''; ?> />
                        </div>
                    </td>
                </tr>
                <?php
                }
            ?>
            </tbody>
        </table>
        <div class="table-footer tar">
            <?php
            echo submitButtonGenerator(array(
                'action' => 'update',
                'size' => '',
                'title' => 'Save Content Types',
                'icon' => 'icon_update',
                'name' => 'submit',
                'value' => 'SAVE CONTENT TYPES',
                'text' => 'Save Content Types',));
            ?>
        </div>
    </div>
</form>
<script type="text/javascript">
    init.push(function(){
        $('.switchers-colors-square > input').switcher({ theme: 'square' });
        });
</script>
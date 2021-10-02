<?php
global $apis;
//loop through all apis and make one single sql to delete from db which are not in $api.
foreach($apis->all_apis as $i=>$v){
    foreach($v as $m=>$n){
        $apiis[] = $n['api'];
        }
    }

$sql = "DELETE FROM dev_api_settings WHERE the_api NOT IN( '".implode("','",$apiis)."') ";

$delete = $devdb->query($sql);
if($delete)
    add_notification('Deleted successfully','success');

doAction('render_start');
?>
<div class="page-header">
    <h1>All APIs</h1>
</div>
<div id="plugin_msg">
</div>
<div class="">
    <?php
    if($apis->all_apis) {
        foreach ($apis->all_apis as $i => $v) {
            ?>
            <div class="panel panel-default tile">
                <div class="panel-heading">
                    <span class="panel-title"><?php echo $i ?></span>
                </div>
                <div class="panel-body table-responsive overflow">
                    <table class="mb0 table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th class="tac col-sm-3">API</th>
                            <th class="tac col-sm-3">Type</th>
                            <th colspan="3" class="tac col-sm-6">Access Permissions</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="tac col-sm-2">From Jacks</th>
                            <th class="tac col-sm-2">From AJAX</th>
                            <th class="tac col-sm-2">From PUBLIC</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $administrator = jack_obj('dev_administration');
                        foreach ($v as $col => $item) {
                            $api_name = $item['api'];
                            $api_settings = $administrator->has_api_permission($api_name,$single);

                            $checked_jack = $api_settings['access_from_another_jack'] == 1 ? 'checked' : '';
                            $checked_ajax = $api_settings['access_from_ajax'] == 1 ? 'checked' : '';
                            $checked_public = $item['api_type']=='local_api' ? 'disabled' : ($api_settings['access_from_public'] == 1 ? 'checked' : '');
                            ?>
                            <tr class="tac">
                                <td><?php echo $item['api'] ?></td>
                                <td><?php echo $item['api_type'] ?></td>
                                <td><input type="checkbox" api="<?php echo $n['api']?>" permission="access_from_another_jack" class="switcher jack" <?php echo $checked_jack ?>/></td>
                                <td><input type="checkbox" api="<?php echo $n['api']?>" permission="access_from_ajax" class="switcher jack" <?php echo $checked_ajax ?>/></td>
                                <td><input type="checkbox" api="<?php echo $n['api']?>" permission="access_from_public" class="switcher jack" <?php echo $checked_public ?>/></td>
                            </tr>
                        <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
        }
    }
    ?>
</div>
<script type="text/javascript">
    init.push(function(){
        $('.switcher').switcher({
            theme: 'modern',
            on_state_content: '<span class="fa fa-check"></span>',
            off_state_content: '<span class="fa fa-times"></span>'
        });
        $('.jack').click(function(){
            var ths = $(this);
            data = ({
                'data' : ths.is(':checked'),
                'permission' : ths.attr('permission'),
                'api' : ths.attr('api'),
                'internalToken' : _internalToken_
                });
            $.ajax({
                type: "POST",
                url: '<?php echo url('api/dev_administration/update_api_settings');?>',
                data: data, //--> send id of checked checkbox on other page
                success: function(re) {
                    $("#plugin_msg").html("<div class='alert alert-success'> API settings Updated. </div>");
                },
                error: function() {
                    $("#plugin_msg").html("<div class='alert alert-danger'> SORRY! There is a problem. </div>");
                },
                complete: function() {
                    //console.log("compleate");
                }
                });
            });
    });
</script>
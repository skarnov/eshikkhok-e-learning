<?php
if($_POST['submit']){
    $path = _path('optional_jacks','absolute').'/';
    $target_file = $path . basename($_FILES["ufile"]["name"]);
    $uploaded = move_uploaded_file($_FILES["ufile"]["tmp_name"], $target_file);
    if ($uploaded) {
        $ret = zip_extractor($target_file);
        $path = dirname($target_file);
        $files = array('index.php');
        $folder = basename($target_file, ".zip");
        foreach($files as $i=>$v){
            $error_file_path = $path.'/'.$folder.'/'.$v;
            if(!(file_exists($error_file_path))){
                $temp[] = $v;
                }
            }
        if($temp){
            foreach($temp as $i=>$v){
                add_notification('Files '.$v.' doesn\'t exist in this plugin','warning');
                }
            $folder = basename($target_file, ".zip");

            delete_folder($path.'/'.$folder);
            }

        else{
            add_notification('New Jack Installed successfully', 'success');
            user_activity::add_activity('A new jack has been installed', 'success', 'create');
            header('Location: '.$myUrl);
            exit();
            }
        }
    else {
        add_notification('Error Plugin installing', 'warning');
        }
    }

global $jacker;
$all_jacks = array_merge($jacker->all_jacks,$jacker->new_jacks);
$jack_statuses = $this->get_jack_statuses();
doAction('render_start');
?>
<div class="page-header">
    <h1>Manage Jacks</h1>
    <div class="oh">
        <div class="btn-group btn-group-sm">
            <?php
            echo linkButtonGenerator(array(
                'href' => $myUrl.'?action=manage_jack_settings',
                'title' => 'Manage Settings',
                'text' => 'Settings',
                'action' => 'config',
                'icon' => 'icon_config',
                'size' => 'sm'
                ));
            ?>
        </div>
    </div>
</div>
<div class="page-header">
    <div class="">
        <form action="" name="form1" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Install New Jack</label>
                <input type="file" name="ufile" class="form-control" id="styled-finputs-example">
            </div>
            <?php
            echo submitButtonGenerator(array(
                'name' => 'submit',
                'action' => 'add',
                'icon' => 'icon_add',
                'title' => 'Install Jack',
                'text' => 'Submit',
                ));
            ?>
       </form>
    </div>
</div>
<div class="panel">
	<div class="panel-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Jack</th>
                        <th class="tac">Activate / Deactivate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($all_jacks as $i=>$pl){
                        $jackName = str_replace('dev','',$i);
                        $jackName = ucwords(trim(str_replace('_',' ',$jackName)));
                        ?>
                        <tr>
                            <td><?php echo $jackName?></td>
                            <td class="tac">
                                <div class="switchers-colors-square">
                                    <input type="checkbox" data-id="<?php echo $i?>" class="jack_status" name="jack_status" data-class="switcher-info" <?php echo $jack_statuses[$i] ? 'checked' : ''; ?> >
                                </div>
                                <i style="font-size: 28px;" class="dn working fa fa-cog fa-spin"></i>
                            </td>
                        </tr>
                        <?php
                        }
                    ?>
                </tbody>
            </table>
        </div>
	</div>
</div>

<script>
	init.push(function (){
		$('.switchers-colors-square > input').switcher({ theme: 'square' });
        var activating = false;
        $(".jack_status").click(function() {
            if(activating) return;
            activating = true;
            var ths = $(this);
            var ths_row = ths.closest('tr');
            var new_status = $(this).is(':checked') ? 'activated' : 'deactivated';
            var post_uri = '<?php echo url('api/dev_jacks_manager/update_jack_status');?>',
                data = ({
                    'data': $(this).is(':checked'),
                    'd_id': $(this).attr("data-id"),
                    'internalToken' : _internalToken_
                    });
            $.ajax({
                beforeSend: function(){
                    ths_row.find('.switchers-colors-square').addClass('dn');
                    ths_row.find('.working').removeClass('dn');
                    $('.switchers-colors-square input').switcher('disable');
                    },
                type: "POST",
                url: post_uri,
                data: data, //--> send id of checked checkbox on other page
                success: function(re) {
                    $.growl.notice({ message: 'Jack has been ' + new_status });
                    },
                error: function() {
                    $.growl.error({ message: 'Please try again.' });
                    },
                complete: function() {
                    ths_row.find('.switchers-colors-square').removeClass('dn');
                    ths_row.find('.working').addClass('dn');
                    $('.switchers-colors-square input').switcher('enable');
                    activating = false;
                    }
                });
            });
	    });
</script>
<script>
    init.push(function () {
        $('#styled-finputs-example').pixelFileInput({ placeholder: 'No File Selected...' });
        })
</script>
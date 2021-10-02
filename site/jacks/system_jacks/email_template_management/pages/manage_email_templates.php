<?php
if($_POST['ajax_type'] == 'get_template_detail'){
    if(isset($this->email_templates[$_POST['template_id']]))
        echo json_encode($this->email_templates[$_POST['template_id']]);
    else
        echo json_encode(array());
    exit();
    }
elseif($_POST['submit']){
    $data = $_POST;

    $set = $this->set_email_template($data);

    if($set['success']){
        add_notification('success','success');
        header('location: '.url());
        exit();
        }

    }
doAction('render_start');
?>
<div class="page-header">
    <h1>All Email Templates</h1>
</div>
<div class="col-sm-3 pl5 pr5">
    <div class="panel panel-dark panel-info tile pl0 pr0">
        <div class="panel-heading">
            <span class="panel-title">Emails</span>
        </div>
        <div class="panel-body email_templates p5">
            <form id="email_templates_order" name="email_templates_order">
            <?php
            foreach($this->email_templates as $i=>$v){
                ?>
                <a style="white-space: normal;" class="template tal btn btn-default col-sm-12" data-id="<?php echo $v->name?>" href="javascript:">
                   <!-- <input type="hidden" name="emailTemplates[]" value="<?php /*echo $v->pk_etemplate_id */?>" />-->
                    <?php echo $v->label?>
                </a>
                <?php
                }
            ?>
            </form>
        </div>
    </div>
</div>
<div class="col-sm-9 pl5 pr5">
    <div class="template_editor_panel tile panel panel-info panel-dark pl0 pr0">
        <div class="panel-heading">
            <span class="panel-title current_panel_title"></span>
        </div>
        <div class="panel-body pt5 pb5 pl0 pr0">
            <div class="col-sm-3 pl5 pr5">
                <div class="panel panel-info  pl0 pr0">
                    <div class="panel-heading">
                        <span class="panel-title">Available Variables</span>
                    </div>
                    <div class="panel-body variables p0">
                        <table class="mb0 available_variables table table-condensed table-striped">
                            <tbody style="cursor: pointer;">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-9 pl5 pr5">
                <div class="panel panel-info pl0 pr0">
                    <div class="panel-heading">
                        <span class="panel-title">Template Code</span>
                    </div>
                    <div class="panel-body p5">
                        <div class="form-group">
                            <textarea rows="10" id="template_user_code" name="user_code" class="text_user_code form-control"></textarea>
                        </div>
                        <div class="form-group mb0 tar">
                            <?php
                            /*echo buttonButtonGenerator(array(
                                'action' => 'reset',
                                'icon' => 'icon_reset',
                                'classes' => 'reset_to_default',
                                'size' => '',
                                'title' => 'Reset Template to Default',
                                'text' => 'Reset to Default Template',
                                ));*/
                            ?>
                            <?php
                            echo submitButtonGenerator(array(
                                'name' => 'any',
                                'value' => 'Update Template',
                                'action' => 'update',
                                'icon' => 'icon_update',
                                'classes' => 'save_template',
                                'size' => '',
                                'title' => 'Update Template',
                                'text' => 'Update Template',
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</form>
<script type="text/javascript">
    init.push(function(){
        $theTinyMce = init_tinymce({
            selector: '#template_user_code',
            external_filemanager_path: '<?php echo _path('common_files');?>/filemanager/',
            });

        var templates_original = <?php echo json_encode($this->email_templates, JSON_FORCE_OBJECT);?>;
        var current_template = null;
        var template_saved = true;
        //init system

        $('.template').click(function(){
            var ths = $(this);
            if(ths.attr('data-id') == current_template) return;
            if(!template_saved) {
                bootbox.dialog({
                    message: "You have edited the template.<br /><br />Do you want to continue without saving?",
                    title: "Unsaved Template",
                    buttons: {
                        success: {
                            label: "Cancel",
                            className: "btn-default",
                            callback: function() {}
                            },
                        danger: {
                            label: "Continue",
                            className: "btn-danger",
                            callback: function() {
                                template_saved = true;
                                template_clicked(ths);
                                }
                            },
                        main: {
                            label: "Save &amp; Continue",
                            className: "btn-success",
                            callback: function() {
                                save_template($('.save_template')).done(function(return_data){
                                    template_saved = true;
                                    template_clicked(ths);
                                    });
                                }
                            }
                        }
                    });
                }
            else template_clicked(ths);
            });

        $('.reset_to_default').click(function(){
            if(!current_template) return;

            var data = {
                'template_id' : current_template,
                'internalToken' : _internalToken_
                };
            $.ajax({
                beforeSend: show_inner_working($('.template_editor_panel'),''),
                complete: hide_inner_working($('.template_editor_panel')),
                type: "POST",
                url: '<?php echo url('api/dev_email_template_manager/reset_template_to_default') ?>',
                data: data,
                cache: false,
                dataType : 'json',
                success: function(reply_data){
                    if(reply_data.success){
                        $.growl.notice({ message: reply_data.success });
                        window.location.reload();
                        }
                    else
                        $.growl.error({ message: reply_data.error});
                    }
                });
            });

        function template_clicked(obj, first){
            if(first === undefined) first = false;
            $('.current_panel_title').html('Edit Email Template: '+obj.text());
            $('.template').removeClass('btn-success');
            obj.addClass('btn-success');
            var ths_id = obj.attr('data-id');
            var var_table = $('.available_variables');
            if(templates_original[ths_id]){
                current_template = ths_id;
                var thisTemplate = templates_original[ths_id];
                //first fill the form with the registered data for the email template
                var_table.find('tbody').html('');
                var variables = thisTemplate.availableVariables;
                //var variables_detail = thisTemplate.variable_detail;

                for(var i in variables){
                    var thisVar = variables[i];
                    $popoverData = '';//'class="popover-success popover-dark" data-trigger="hover" data-toggle="popover" data-placement="auto" data-content="'+variables_detail[thisVar]+'" data-title="'+thisVar+'" data-original-title="" title=""';
                    var_table.find('tbody').append('<tr><td '+$popoverData+'>'+thisVar+'</td></tr>');
                    }
                //$('[data-toggle="popover"]').popover();

                var setToTinyMceTimer = setInterval(function(){
                    if(typeof(tinymce) == "object"){
                        tinymce.get('template_user_code').setContent(thisTemplate.email_body);
                        tinymce.triggerSave();
                        clearInterval(setToTinyMceTimer);
                        }
                    },300);
                }
            }

        function save_template(obj){
            var newEmailBody = tinymce.get('template_user_code').getContent();
            var data = {
                'pk_id' : templates_original[current_template]['pk_etemplate_id'],
                'template_id' : current_template,
                'user_code' : newEmailBody,
                'internalToken' : _internalToken_
                };
            return $.ajax({
                beforeSend: show_inner_working($('.template_editor_panel'),''),
                complete: hide_inner_working($('.template_editor_panel')),
                async: false,
                type: "POST",
                url: '<?php echo url('api/dev_email_template_manager/set_email_template') ?>',
                data: data,
                cache: false,
                dataType : 'json',
                success: function(reply_data){
                    if(reply_data['success']){
                        if(!templates_original[current_template]['pk_etemplate_id'])
                            templates_original[current_template]['pk_etemplate_id'] = reply_data['success'];
                        template_saved = true;
                        templates_original[current_template]['email_body'] = newEmailBody;
                        $.growl.notice({ message: 'Template Saved Successfully.' });
                        }
                    },
                error: function(){
                    $.growl.error({ message: 'Template wasn\'t saved.' });
                    }
                });
            }

        $('.save_template').click(function(){
            if(!current_template) return;
            var ths = $(this);
            return save_template(ths);
            });

        $('.text_user_code').keyup(function(){
            if($('.text_user_code').val().length != templates_original[current_template]['email_body'].length)
                template_saved = false;
            });

        $('.email_templates .template:first-child').trigger('click');
        });
</script>
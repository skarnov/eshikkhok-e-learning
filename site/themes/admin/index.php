<?php
ini_set('max_execution_time', 3000);

load_js(array(
    common_files().'/js/jcookies.js',
    ));
if($_POST['ajax_type'] == 'save_dashboard_widget_order'){
    if(has_permission('save_dashboard_widget_order_for_system')){
        $sql = "DELETE FROM dev_config WHERE config_name = 'dashboard_widget_order' ";
        $deleted = $devdb->query($sql);
        $insertOrder = array(
            'config_name' => 'dashboard_widget_order',
            'config_value' => json_encode($_POST['order']),
            );
        $inserted = $devdb->insert_update('dev_config',$insertOrder);
        removeCache('devConfig');
        echo json_encode(array('success' => 1));
        }
    else echo json_encode(array('error' => array('Not enough permission')));
    exit();
    }

include('header.php');
echo $notify_user->get_notification();
?>
<?php
if(has_permission('access_to_dashboard')){
    ?>
    <div class="dashboard_widgets block-area row">
        <?php echo $adminWidgets->render_widgets();?>
    </div>
    <style type="text/css">
        .widget_extender {
            position: absolute;
            left: 0;
            bottom: 0;
            right: 0;
            z-index: 999;
            background-color: rgba(0,0,0,.5);
            color: #fff;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
        }
        .dashboard_widgets .dashboardWidgetSortHandle{
            position: absolute;
            top: 0;
            left: 11px;
            bottom: 22px;
            width: 20px;
            background: #cecdcd;
            cursor: move;
        }
        dashboard_widgets. .dashboardWidgetSortHandle:hover{
            background: #9e9e9e;
        }
        .dashboard_widgets .panel{
            *margin-left: 20px;
            -webkit-animation: all 1s;
            -o-animation: all 1s;
            animation: all 1s;
            }
        .dashboard_widgets .panel.shortened{
            height:350px !important;
            overflow: hidden;
        }
        .dashboard_widgets .panel.extended .panel-body{
            padding-bottom: 40px !important;
        }
        .dashboard_widget{

            float: left;
            box-sizing: border-box;
        }
        .widget_placeholder{
            background: repeating-linear-gradient( -45deg, #eeeeee, #eeeeee 2px, #ffffff 10px, #ffffff 15px );
            float:left;
            box-sizing: border-box;
            /*margin: 10px;*/
        }
    </style>
    <script type="text/javascript">
        var save_dashboard_widget_order_for_system = <?php echo has_permission('save_dashboard_widget_order_for_system') ? 1 : 0?>;
        var widget_update_notification_displayed = false;
        init.push(function(){
            $(document).off('click').on('click','.widget_extender',function(){
                var ths = $(this);
                var panel_ = $(this).closest('.panel');
                if(panel_.hasClass('extended')){
                    //need to short
                    panel_.removeClass('extended').addClass('shortened');
                    ths.find('.widget_extender_text').html('<i class="fa fa-hand-o-down"></i>&nbsp;EXPAND');
                }
                else{
                    //need to extended
                    panel_.removeClass('shortened').addClass('extended');
                    ths.find('.widget_extender_text').html('<i class="fa fa-hand-o-up"></i>&nbsp;SHORTEN');
                }
            });
            function init_widget_extender(){
                $('.dashboard_widgets .dashboard_widget:not(.col-sm-12)').each(function(index,element){
                    var elm = $(element).find('>.panel');
                    if(elm.find('.widget_extender').length) return true;
                    elm.append('<div class="widget_extender"><span class="widget_extender_text"><i class="fa fa-hand-o-down"></i>&nbsp;EXPAND</span></div>');
                    elm.addClass('shortened');
                });
            }
            $(document).ready(function(){
                init_widget_extender();

                var total = $('.dashboard_widgets .dashboard_widget').length;

                var system_sort_order = <?php echo isset($_config['dashboard_widget_order']) && strlen($_config['dashboard_widget_order']) ? $_config['dashboard_widget_order'] : "''"?>;
                var user_sort_order = $.jCookies({ get : 'sort_order', error: true });
                // var size_order = $.jCookies({ get : 'size_order', error: true});
                //console.log(size_order);
                var len = 0, i = 0;
                if(user_sort_order.length){
                    len = user_sort_order.length;
                    for(i=0;i<len;i++){
                        $(".dashboard_widgets .dashboard_widget:eq("+i+")").before($('.dashboard_widgets .dashboard_widget[data-id="'+user_sort_order[i]+'"]'));
                        }
                    }
                else if(system_sort_order.length){
                    len = system_sort_order.length;
                    for(i=0;i<len;i++){
                        $(".dashboard_widgets .dashboard_widget:eq("+i+")").before($('.dashboard_widgets .dashboard_widget[data-id="'+system_sort_order[i]+'"]'));
                        }
                    }

                <?php if(getProjectSettings('features,dashboard_widget_sortable') && has_permission('sort_dashboard_widget')): ?>
                $('.dashboard_widgets').sortable({
                    handle: ".dashboardWidgetSortHandle",
                    cursor: "auto",
                    placeholder: 'widget_placeholder',
                    tolerance: "pointer",
                    start: function(event, ui){
                        $('.widget_placeholder').css({
                            height: ui.item.height() - 30,
                            width: ui.item.width() - 30,
                            opacity:.5
                        });
                    },
                    update: function(event, ui){
                        var total = $('.dashboard_widgets .dashboard_widget').length;
                        var order = [];
                        for(var i=0;i<total;i++){
                            var elm = $('.dashboard_widgets .dashboard_widget')[i];
                            elm = $(elm);
                            order[i] = elm.attr('data-id');
                            }

                        $.jCookies({
                            name : 'sort_order',
                            value : order
                        });
                        if(save_dashboard_widget_order_for_system){
                            $.growl.notice({ title: 'SAVED', message: 'Do you want to save current layout for overall system?<br /><span class="btn btn-xs btn-warning save_dashboard_widget_order_for_system">YES</span>', location: 'br', size: 'large'});
                            }
                        }
                });
                <?php endif; ?>
                $(document).on('click','.growl-close',function(){
                    if($(this).closest('.growl').find('.widget_update_notification_displayed'))
                        widget_update_notification_displayed = false;
                    });
                <?php if(getProjectSettings('features,dashboard_widget_sortable')): ?>
                $(document).on('click','.save_dashboard_widget_order_for_system',function(){
                    var order = $.jCookies({ get : 'sort_order', error: true });
                    if(order){
                        $.ajax({
                            url: _root_path_+'/',
                            type: 'post',
                            //dataType: 'json',
                            data: {
                                ajax_type: 'save_dashboard_widget_order',
                                order: order
                                },
                            success: function(ret){
                                console.log(ret);
                                if(ret.success){
                                    $.growl.notice({message: 'Widget order saved for overall system.'});
                                    }
                                }
                            });
                        }
                    });
                <?php endif; ?>
                });
            });
    </script>
    <?php
    }
else{
    ?>
    <div class="note note-dark note-danger">
        <h4 class="note-title">Access Denied to Dashboard</h4>
        You are not allowed to view Dashboard.
        To view Dashboard please contact an administrator.
        You can access your other permitted panels.
    </div>
    <?php
    }
?>

<?php
include('footer.php');
?>

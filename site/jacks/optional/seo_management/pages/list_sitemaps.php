<?php doAction('render_start'); ?>
<div class="page-header">
    <h1>Generate &amp; Submit Sitemaps</h1>
</div>
<div class="table-primary table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Sitemap</th>
            <th>URL</th>
            <th class="tar action_column">...</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if(self::$siteMaps){
            foreach(self::$siteMaps as $i=>$v){
                ?>
                <tr>
                    <td><?php echo $v['title']?></td>
                    <td><a target="_blank" href="<?php echo url($v['file'], 'public'); ?>">/<?php echo $v['file']?></a></td>
                    <td class="tar action_column">
                        <div class="btn-toolbar">
                        <?php
                        echo buttonButtonGenerator(array(
                            'action' => 'config',
                            'icon' => 'icon_config',
                            'text' => 'Generate',
                            'title' => 'Generate Sitemap',
                            'classes' => 'generate_now',
                            'attributes' => array('data-api' => $v['api_url'])
                            ));
                        echo buttonButtonGenerator(array(
                            'action' => 'config',
                            'icon' => 'icon_config',
                            'text' => 'Submit',
                            'title' => 'Submit Sitemap to Search Engines',
                            'classes' => 'submit_now',
                            'attributes' => array('data-id' => $v['file'])
                            ));
                        ?>
                        </div>
                        <div class="submit_result dn">
                            <table class="table table-bordered mb0 mt5">
                                <tbody></tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php
                }
            }
        ?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    init.push(function(){
        $('.generate_now').click(function(){
            var ths = $(this);
            basicAjaxCall({
                beforeSend: function(){show_button_overlay_working(ths)},
                complete: function(){hide_button_overlay_working(ths)},
                url: ths.attr('data-api'),
                success: function(ret){
                    if(ret.success)
                        $.growl.notice({message: 'Generated Successfully'});
                    else growl_error(ret.error);
                    },
                error: function(){
                    growl_error(['Something went wrong, please try again']);
                    }
                });

            });
        $('.submit_now').click(function(){
            var ths = $(this);
            var _td = ths.closest('td');
            basicAjaxCall({
                beforeSend: function(){
                    show_button_overlay_working(ths);
                    _td.find('.submit_result table tbody').html('');
                    _td.find('.submit_result').addClass('dn');
                    },
                complete: function(){hide_button_overlay_working(ths)},
                url: '<?php echo url('api/dev_seo_management/submit_sitemap');?>',
                data: {
                    url: _root_path_+'/'+ths.attr('data-id'),
                    sitemap: ths.attr('data-id'),
                    },
                success: function(ret){
                    var tbody = _td.find('.submit_result table tbody');
                    for(i in ret){
                        var code = ret[i];
                        tbody.append('<tr><td>'+ i.toUpperCase()+'</td><td>'+(code == 200 ? 'OK' : 'NOT OK')+'</td></tr>')
                        }
                    _td.find('.submit_result').removeClass('dn');
                    },
                error: function(){
                    growl_error(['Something went wrong, please try again.']);
                    }
                });
            });
        });
</script>
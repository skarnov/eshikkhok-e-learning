<?php
$current_language = $_GET['language'] ? $_GET['language'] : $_config['slang'];

if($_POST['ajax_type'] == 'add_term'){
    $term = form_modifiers::slug($_POST['term_slug']);
    $term_title = $_POST['term_label'];

    $ret = array('error' => array());

    $temp = form_validator::required($term);
    if($temp !== true) $ret['error'][] = "Term Slug ".$temp;

    $temp = form_validator::_length($term, 250);
    if($temp !== true) $ret['error'][] = "Term Slug ".$temp;

    $temp = form_validator::required($term_title);
    if($temp !== true) $ret['error'][] = "Term Title ".$temp;

    if($ret['error']) echo json_encode($ret);
    else{
        $found = $devdb->get_row("SELECT * FROM dev_multilingual_terms WHERE term_slug = '".$term."'");
        if($found) echo json_encode(array('error' => array('The term already exists')));
        else{
            $insertData = array(
                'term_slug' => $term,
                'term_title' => $term_title
                );
            $insertNow = $devdb->insert_update('dev_multilingual_terms', $insertData);
            if($insertNow['success']){
                $insertNow['success'] = $insertData;
                removeCache('language_all_terms');
                user_activity::add_activity('A new term "'.$term_title.'" has been created.', 'success', 'create');
                }
            echo json_encode($insertNow);
            }
        }
    exit();
    }

if($_POST['ajax_type'] == 'single_translation_save'){
    $term = $_POST['term'];
    $value = $_POST['value'];
    $isExisting = $_POST['existing'];
    if($isExisting)
        $devdb->query("DELETE FROM dev_multilingual_data WHERE fk_term_slug_id = '".$term."' AND language_id = '".$current_language."'");

    $insertData = array(
        'fk_term_slug_id' => $term,
        'language_id' => $current_language,
        'translated_text' => $value
        );

    $insertNow = $devdb->insert_update('dev_multilingual_data', $insertData);

    if($insertNow['success']){
        user_activity::add_activity('Translation of term (ID: '.$term.') in language '.$current_language.' has been created.', 'success', 'create');
        removeCache("language_data_".$current_language);
        }

    echo json_encode($insertNow);
    exit();
    }

$languageData = $this->getLanguageDataIndexed($current_language);
$terms = $this->getTerms();
$terms = $terms['data'];

doAction('render_start');
?>
<div class="page-header">
    <h1>Manage Translation of Static Texts: <strong><?php echo $_config['langs'][$current_language]['title'] ?></strong></h1>
</div>
<div class="panel">
    <div class="panel-body">
        <form method="get">
            <div class="form-group col-sm-3">
                <label>Select Language</label>
                <select name="language" id="change_language" class="form-control">
                    <?php
                    foreach($_config['langs'] as $i=>$v){
                        $selected = $i == $current_language ? 'selected' : '';
                        echo '<option value="'.$i.'" '.$selected.'>'.$v['title'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-sm-3">
                <label class="checkbox-inline">
                    <input type="checkbox" id="show_empty_only" value="yes" />
                    <span class="lbl">Show Empty Only</span>
                </label>
            </div>
        </form>
    </div>
</div>
<div class="table-primary table-responsive">
    <table class="terms_table table table-bordered table-condensed table-striped table-hover">
        <thead>
            <tr>
                <th width="20px">#</th>
                <th>Term</th>
                <th>Translation in <?php echo $_config['langs'][$current_language]['title'] ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if($terms){
                $count = 1;
                foreach ($terms as $i=>$term) {
                    ?>
                    <tr>
                        <td width="20px"><?php echo $count++ ?></td>
                        <td ><?php echo $term['term_title'] ?><p class="color-grey"><?php echo $term['term_slug'] ?></p></td>
                        <td>
                            <input class="form-control translation" data-existing="<?php echo isset($languageData[$term['term_slug']]) ? '1' : '0'; ?>" data-term="<?php echo $term['term_slug'] ?>" type="text"
                                   name="translation"
                                   value="<?php echo isset($languageData[$term['term_slug']]) ? $languageData[$term['term_slug']] : '' ?>"/>
                        </td>
                    </tr>
                    <?php
                    }
                }
            else echo '<tr><td colspan="4" class="tac text-danger">No Terms, No Translation</td></tr>';
            ?>
        </tbody>
    </table>
    <div class="table-footer">
        <?php
        echo buttonButtonGenerator(array(
            'id' => 'addTerm',
            'action' => 'add',
            'icon' => 'icon_add',
            'text' => 'New Term',
            'title' => 'Create New Term',
            'size' => 'sm',
            ));
        ?>
    </div>
</div>
<div class="dn">
    <div id="termAddForm" title="Add New Term">
        <form>
            <input type="hidden" name="ajax_type" value="add_term" />
            <div class="form-group">
                <label>Term Slug</label>
                <input required type="text" class="form-control char_limit" data-max-char="250" name="term_slug" />
                <p class="help-block">Do not use spaces, underscores and special characters. Use dash or hyphen as separator. No more than 200 characters. Only use english language</p>
            </div>
            <div class="form-group">
                <label>Term Label</label>
                <input required type="text" class="form-control" name="term_label" />
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    init.push(function(){
        initCharLimit();
        $('#show_empty_only').on('change', function(){
            var showEmpty = $(this);
            $('.translation').each(function(){
                if(showEmpty.is(':checked') && $(this).val().length) $(this).closest('tr').hide();
                else $(this).closest('tr').show();
                });
            });
        $('#change_language').on('change', function(){
            $(this).closest('form').submit();
            });
        $('.translation').on('blur',function(){
            var ths = $(this);
            var value = ths.val();
            var term = ths.attr('data-term');
            var isExisting = ths.attr('data-existing');

            $.ajax({
                beforeSend: function(){ths.attr('readonly',true)},
                complete: function(){ths.attr('readonly',false)},
                url: '<?php echo current_url() ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    ajax_type: 'single_translation_save',
                    term: term,
                    value: value,
                    existing: isExisting,
                    },
                success: function(ret){
                    if(ret['success']) $.growl.warning({title: 'Done', message: 'Translation Saved'});
                    else $.growl.error({title: 'Errors !', message: 'Failed to save translation'});
                    }
                });
            });

        $('#addTerm').on('click', function(){
            var ths = $(this);
            in_page_add_event({
                form_title: 'Add New Term',
                ths: ths,
                form_container: $('#termAddForm'),
                url: window.location.href,
                callback: function(ret){
                        $('.terms_table tbody').append('\
                        <tr>\
                            <td>'+($('.terms_table tbody tr').length+1)+'</td>\
                            <td>'+ret.term_title+'<p class="color-grey">'+ret.term_slug+'</p></td>\
                            <td ><input class="form-control translation" data-existing="0" data-term="'+ret.term_slug+'" type="text" name="translation" value=""/></td>\
                        </tr>\
                        ');
                    },
                });
            });
        });
</script>
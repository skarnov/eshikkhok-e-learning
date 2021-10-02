/*JQUERY FUNCTION ADDITIONAL*/
var devSplitSlider = function(opt){
    var ths = $(this);

    var options = {
        sectionOne: null,
        sectionOneEffect: 'slide',
        sectionOneIncomingDirection: 'left',
        sectionOneOutgoingDirection: 'right',

        sectionTwo: null,
        sectionTwoEffect: 'slide',
        sectionTwoIncomingDirection: 'left',
        sectionTwoOutgoingDirection: 'right',

        slideDuration: 5000,
        previousSlideController: null,
        nextSlideController: null,
        bullets: null,
        currentSlide: 0,
        transitionTimer: null,
        autoStart: true,
        };
    options = $.extend(true, options, opt);

    options.totalSlides = options.sectionOne.length;
    options.maxSlideCount = options.totalSlides-1;

    ths.startTransitionTimer = function(){

        if(typeof this.callCount === 'undefined') this.callCount = 0;


        this.callCount++;

        if(parseInt(this.callCount) != 2) return;
        else this.callCount = 0;

        if(options.autoStart){
            options.transitionTimer = setTimeout(function(){ths.nextSlide()}, options.slideDuration);
            }
        };

    ths.nextSlide = function(){
        clearTimeout(options.transitionTimer);

        var nextSlide = parseInt(options.currentSlide)+1 > parseInt(options.maxSlideCount) ? 0 : parseInt(options.currentSlide)+1;

        options.sectionOne.eq(options.currentSlide).hide(options.sectionOneEffect,{direction: options.sectionOneOutgoingDirection},'slow',function(){
            options.sectionOne.eq(nextSlide).show(options.sectionOneEffect,{direction: options.sectionOneIncomingDirection},'slow', function(){
                ths.startTransitionTimer();
                });
            });
        options.sectionTwo.eq(options.currentSlide).hide(options.sectionTwoEffect,{direction: options.sectionTwoOutgoingDirection},'slow',function(){
            options.sectionTwo.eq(nextSlide).show(options.sectionTwoEffect,{direction: options.sectionTwoIncomingDirection},'slow', function(){
                ths.startTransitionTimer();
                });
            });

        options.currentSlide = nextSlide;
        };

    ths.previousSlide = function(){
        clearTimeout(options.transitionTimer);

        var previousSlide = options.currentSlide-1 < 0 ? 0 : options.currentSlide-1;

        options.sectionOne.eq(options.currentSlide).hide(options.sectionOneEffect,{direction: options.sectionOneOutgoingDirection},'slow',function(){
            options.sectionOne.eq(previousSlide).show(options.sectionOneEffect,{direction: options.sectionOneIncomingDirection},'slow', function(){
                ths.startTransitionTimer();
                });
            });
        options.sectionTwo.eq(options.currentSlide).hide(options.sectionTwoEffect,{direction: options.sectionTwoOutgoingDirection},'slow',function(){
            options.sectionTwo.eq(previousSlide).show(options.sectionTwoEffect,{direction: options.sectionTwoIncomingDirection},'slow', function(){
                ths.startTransitionTimer();
                });
            });

        options.currentSlide = previousSlide;
        };

    if(options.previousSlideController){
        $(options.previousSlideController).on('click', function(){
            ths.previousSlide();
            });
        }
    if(options.nextSlideController){
        $(options.nextSlideController).on('click', function(){
            ths.nextSlide();
            });
        }

    if(options.autoStart){
        options.transitionTimer = setTimeout(function(){ths.nextSlide()}, options.slideDuration);
        }
    };
function currentTime(){
    var x = new Date(); return x.getTime();
    }
function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
    }
String.prototype.rtrim = function(chars){
    var pattern = new RegExp(chars+"+$");
    return this.replace(pattern,'');
    };
String.prototype.ltrim = function(chars){
    var pattern = new RegExp("^"+chars+"+");
    return this.replace(pattern,'');
    };
function processFormElementName(theName){
    var firstName = theName[0];
    var restNames = theName.splice(1);
    return firstName + '[' + restNames.join('][') + ']';
    }
jQuery.fn.tableUpdated = function(){
    if(this.find('tbody tr:not(.emptyFillRow)').length) this.find('tbody tr.emptyFillRow').remove();
    };
jQuery.fn.tagName = function() {
    return this.prop("tagName");
    };
jQuery.fn._removeClass = function(term,include_me){
    include_me = include_me !== undefined ? include_me : false;
    $(this).removeClass (function (index, css) {
        var exp = include_me ? new RegExp("\\b("+term+"\\S+|"+term+")","g") : new RegExp("\\b("+term+"\\S+)","g");
        return (css.match (exp) || []).join(' ');
        });
    };
jQuery.fn.notEmpty = function(){
    if(typeof this != 'object') return false;
    if(jQuery.isEmptyObject(this)) return false;
    else return true;
    };
jQuery.fn.getFloat = function(toFixed) {
    var value = isNaN(parseFloat(this.val())) ? 0 : parseFloat(this.val());
    if(toFixed !== undefined) value = parseFloat(value.toFixed(toFixed));
    return value;
    };
jQuery.fn.getInt = function() {
    return isNaN(parseInt(this.val())) ? 0 : parseInt(this.val());
    };
/****************************/
function get_image(image,size){
    var params = {
        image: image,
        size: size,
        internalToken: _internalToken_
        };
    var query = Object.keys(params).map(function(k){return encodeURIComponent(k) + '=' + encodeURIComponent(params[k])}).join('&');
    return _root_path_+'/api/dev_content_management/get_image?'+query;
    }
function get_responsive_image(opt){
    var defaults = {
        image: '',
        size: '',
        path_folder: 'uploads',
        save_dir: null,
        alternatives: {},
        force_max_width: null,
        screenWidth: $(window).width(),
        baseWidth: 1349,
        internalToken: _internalToken_
        };
    var params = $.extend(true, defaults, opt);
    var query = Object.keys(params).map(function(k){return encodeURIComponent(k) + '=' + encodeURIComponent(params[k])}).join('&');

    return _root_path_+'/api/dev_content_management/get_responsive_image?'+query;
    }
function set_responsive_image_link(opt, obj){
    var defaults = {
        image: '',
        size: '',
        path_folder: 'uploads',
        save_dir: null,
        alternatives: {},
        force_max_width: null,
        screenWidth: $(window).width(),
        baseWidth: 1349,
        internalToken: _internalToken_
    };
    var params = $.extend(true, defaults, opt);

    $.ajax({
        beforeSend: function(){},
        complete: function(){},
        timeout: function(){},
        error: function(){},
        url: _root_path_+'/api/dev_content_management/get_responsive_image?',
        data: params,
        type: 'GET',
        dataType: 'json',
        success: function(ret){
            if(ret.success) obj.attr('src', ret.success);
            },
        });
    }
function set_responsive_bg_image_link(opt, obj){
    var defaults = {
        image: '',
        size: '',
        path_folder: 'uploads',
        save_dir: null,
        alternatives: {},
        force_max_width: null,
        screenWidth: $(window).width(),
        baseWidth: 1349,
        internalToken: _internalToken_
        };
    var params = $.extend(true, defaults, opt);

    $.ajax({
        beforeSend: function(){},
        complete: function(){},
        timeout: function(){},
        error: function(){},
        url: _root_path_+'/api/dev_content_management/get_responsive_image?',
        data: params,
        type: 'GET',
        dataType: 'json',
        success: function(ret){
            if(ret.success) obj.css('background-image', "url('"+ret.success+"')");
            },
        });
    }
function emptyTableFill(table, text){
    text = typeof text == 'undefined' ? 'No Data Found' : text;
    var totalCol = 0;
    if(!table.find('thead tr').length){
        totalCol = table.find('thead tr').find('th').length;
        }
    else{
        var totalTh = table.find('thead tr').eq(0).find('th').length;
        for(var i = 0; i<totalTh; i++){
            totalCol += 1;
            var thisTH = table.find('thead tr').eq(0).find('th').eq(i);
            if(thisTH.attr('colspan')) totalCol += parseInt(thisTH.attr('colspan'));
            }
        }
    table.find('tbody').each(function(index, element){
        if(!$(element).find('tr').length){
            $(element).append('<tr class="emptyFillRow"><td colspan="'+totalCol+'" class="text-danger tac">'+text+'</td></tr>');
            }
        });
    }
function number_format (number, decimals, dec_point, thousands_sep) {
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
            };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
        }
    return s.join(dec);
    }
function currencyToNumber(currency){
    return currency.replace(/[,]/g,'');
    }
function clog(data){
    console.log(data);
    }
function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if(pair[0] == variable){return pair[1];}
        }
    return(false);
    }
function sanitize_form_inputs(form){
    var source = $('[name="data_sanitize"]',form);
    var value = source.val();
    if(value === undefined) return;
    var parts = value.split(',');
    var len = parts.length;
    console.log(parts);
    for(var i=0;i<len;i+=2){
        var elm = $('[name="'+parts[i+1]+'"]');
        var func = parts[i];
        if(typeof window[func] == 'function'){
            var ret = window[func](elm.val());
            elm.val(ret);
            console.log(ret);
            }
        }
    }
function xmlEscape(s){
    var search = {
            '&' : '&amp;',
            "'" : '&apos;',
            '"' : '&quot;',
            '<' : '&lt;',
            '>' : '&gt;'
            };
    for(var i in search){
        s = s.replace(new RegExp(i,'g'),search[i]);
        }
    return s;
    }
function reverse(s){
    return s.split('').reverse().join('');
    }
function growl_error($e,opt){
    var html = '';
    for(var i in $e){
        html += '<p>'+$e[i]+'</p>';
        }
    var obj = {
        title: 'Errors !',
        message: html,
        };
    obj = $.extend(true, obj, opt);

    $.growl.error(obj);
    }
function stripTrailingSlash(str) {
    if(str.substr(-1) == '/') {
        return str.substr(0, str.length - 1);
        }
    return str;
    }
function image_url(img){
    return _root_path_+'/site/contents/uploads/'+img;
    }
function show_container_working(msg, cont){
    var message = msg ? msg : 'Please Wait. Working ...';
    var container = $(cont);

    if(container.find('.pageLoadingContainer').length)
        container.find('.pageLoadingContainer').remove();

    container.css('position', 'relative');

    container.append('<div id="unloading" class="pageLoading pageLoadingContainer" style="display: block;">\
                            <span class="pageLoadingInner label label-warning">'+message+'</span>\
                            </div>');
    container.find('.pageLoadingContainer').fadeIn();
    }
function hide_container_working(cont){
    $(cont).find('.pageLoadingContainer')
        .fadeOut()
        .remove();
    }
function show_working(msg){
    var message = msg ? msg : 'Processing Request ...';
    if(!$('#working_modal_container').length){
        $('body').append('<div id="working_modal_container">\
                            <div class="working_modal">\
                                <div class="loader_loading"></div>\
                                    <span class="panel-title">'+message+'</span>\
                            </div>\
                        </div>');
        $('#working_modal_container').show();
        }
    else{
        $('#working_modal_container').remove();
        show_working(msg);
        }
    }
function hide_working(){
    $('#working_modal_container').fadeOut();
    }
function show_inner_working(container,msg){
    var message = msg ? msg : 'Processing Request ...';
    container.css({'position':'relative'});
    if(!container.find('.working_modal_container').length){
        container.append('<div class="working_modal_container">\
                            <div class="working_modal">\
                                <div class="loader_loading"></div>\
                                    <span class="panel-title"></span>\
                            </div>\
                        </div>');
    }
    container.find('.working_modal_container .panel-title').html(message);
    container.find('.working_modal_container').show();
    }
function hide_inner_working(container){
    container.find('.working_modal_container').fadeOut();
    }
function show_button_working(btn,message){
    var iconElm = $(btn).find('i.fa');
    var iconClass = iconElm.length ? iconElm.attr('class') : '';
    if(iconElm.length){
        $(iconElm).attr('class','fa fa-cog fa-spin');
        $(btn).attr('data-pre-icon',iconClass);
        }
    else{
        $(btn).prepend('<i class="fa fa-cog fa-spin"></i>');
        }
    $(btn).attr('disabled',true);
    }
function hide_button_working(btn){
    if($(btn).attr('data-pre-icon')){
        $(btn).find('i.fa').attr('class',$(btn).attr('data-pre-icon'));
        }
    else{
        $(btn).find('i.fa').remove();
        }
    $(btn).attr('disabled',false);
    }
function show_button_overlay_working(btn, opt){
    opt = typeof opt === 'undefined' ? {} : opt;
    var options = $.extend(true,{
        'stripPadding' : false,
        }, opt);

    var btn = $(btn);
    if(btn.hasClass('btn_overlay_active')) return false;
    var paddingLeft = btn.css('padding-left');
    var paddingRight = btn.css('padding-right');
    var current_position = btn.css('position');
    var toPossition = current_position == 'absolute' || current_position == 'fixed' ? current_position : 'relative';
    btn
        .addClass('btn_overlay_active')
        .css({position: toPossition})
        .append(
            '<div class="progress progress-striped active btn_overlay"><div class="progress-bar progress-bar-info"></div></div>'
            )
        .attr('disabled',true);
    if(options.stripPadding){
        btn
            .find(' > .btn_overlay.progress, > .btn_overlay .progress-bar').css({
                left: paddingLeft,
                right: paddingRight,
                })
        }
    }
function hide_button_overlay_working(btn){
    $(btn)
        .removeClass('btn_overlay_active')
        .attr('disabled', false)
        .find('.btn_overlay').remove();
    }

$(document).on('click','.confirm_delete',function(){
    var rel = $(this).attr('rel');
    var txt = $(this).attr('data-delete_title') ? $(this).attr('data-delete_title') : 'this';
    bootbox.confirm({
        message: 'Do you really want to delete '+txt+'?',
        callback: function(result) {
            if(result) document.location = rel;
            },
        className: "bootbox-sm"
        });
    });
$(document).on('click','.confirm_delete_p',function(){
    var rel = $(this).attr('rel');
    var txt = $(this).attr('data-delete_title') ? $(this).attr('data-delete_title') : 'this';
    if(confirm('Do you really want to delete '+txt+'?')){
        document.location = rel;
        }
    });
function datetime_to_user($data){
    $data = $data.split(' ');
    $date = $data[0];
    $date = $date.split('-');
    return $date[2]+'-'+$date[1]+'-'+$date[0]+' '+$data[1];
    }
function date_to_user($data){
    $data = $data.split(' ');
    $data = $data[0];
    $data = $data.split('-');
    return $data[2]+'-'+$data[1]+'-'+$data[0];
    }
function time_to_user($data){
    $data = $data.split(' ');

    if($data[1] !== undefined) $data = $data[1];
    else $data = $data[0];

    return $data;
    }
//auto complete field
function split( val ){
    return val.split(/,\s*/);
    }
function extractLast(term){
    return split( term ).pop();
    }
function basename(path){
    var img = path.split(/[\\/]/).pop();
    return img;
    }
function ucfirst(str){
    str = str.charAt(0).toUpperCase() + str.slice(1);
    return str;
    }
var set_autosuggest = function(options){
    /*
    * ajax_page
    * minLength
    * from : table name
    * id: field that will be used as ID
    * label: field that will be used as label
    * select_fields: [] array of fields that will be selected and returned back
    * condition: SQL condition as string
    * compare: [] array of fields with which the comparision will be made
    * new_item: true or false |
    * after_select: function() to call after a selection has been made.
    * on_select: function() to call on a selection has been made.
    * render_as: function() to call on how to generate the suggestion list
    * field_name : the name of the hidden input field that will be associated with each selected item
    * confirm_add : false;
    * api_call: {
    *   jack:
    *   function:
    *   param:{
    *
    *       }
    *   term_param: param index in which the term will be used as value
    *   }
    * */

    var ths = $(this);
    ths.options = {
        single: false, //TRUE to allow only one item to add
        input_field: '',//ID (with #) to be used for the input field of this autocomplete box, this field will have no name, because this wont submit
        input_field_class: '',
        field_name: '', //Name to be used for each added item, you will receive this name in $_POST
        container: 0, //ID (with #) of a div or any container where the autocomplete box will be placed
        submit_labels: true, //create hidden input for submitting labels along with ID
        existing_items: {}, //a json object with id, label, value to pre-fill the autocomplete box
        parameters: {}, //additional data to be sent while requesting search result
        add_new: false, //TRUE to allow adding new item
        add_what: '', //a label to be used in the add new confirmation box like Do you want to add this COMPANY
        data_for_add: {}, //additional data to be sent along with add request
        url_for_add: '', //URL to send the add request
        field_for_add: '', //the field in which the term you typed that will be sent with add request
        multilingual: false, //TRUE to process texts before rendering
        searchOnClick: false,
        minLength: 2,
        confirm_add: false,
        };

    $.extend(true, ths.options, options);

    ths.options.input_field_id = ths.options.input_field.match('^#') ? ths.options.input_field.substring(1,ths.options.input_field.length) : ths.options.input_field;

    if($(ths.options.container).length){
        $(ths.options.container).append('\
            <div class="select2-info select2-autocomplete-container">\
                <div class="select2-container select2-container-multi form-control">\
                    <ul class="select2-choices">\
                        <li class="autocomplete_inputbox"><input type="text" id="'+ths.options.input_field_id+'" class="'+ths.options.input_field_class+'"value="" /></li>\
                    </ul>\
                </div>\
            </div>\
            ');
        }

    ths.options.o_ajax_page = ths.options.ajax_page ? ths.options.ajax_page : _root_path_ + '/api/dev_administration/autocomplete_handler';
    //ths.options.minLength = ths.options.minLength ? ths.options.minLength : 2;
    ths.input_field = $(ths.options.input_field);
    ths.item_list = ths.input_field.closest('.select2-choices');
    ths.loadContainer = ths.input_field.closest('.select2-container');
    ths.options.field_name_label = ths.options.field_name.match(']$') ? ths.options.field_name.substring(0,ths.options.field_name.length-1)+'_label]' : ths.options.field_name+'_label';

    ths.generate_new_item = function(ui){
        var new_item_add;
        if(ths.options.multilingual){
            ui.item.label = processToRender(ui.item.label, null, true);
            }

        if(ths.options.on_select) new_item_add = window[ths.options.on_select](ths.options,ui);
        else{
            if(ths.options.single){
                ths.item_list.find('li:not(.autocomplete_inputbox)').remove();
                new_item_add = $('<li>')
                    .attr('class', 'select2-search-choice')
                    .html('<div>'+ui.item.label+'<input class="clearable" type="hidden" name="'+ths.options.field_name+'" value="'+ui.item.id+'"/>'+(ths.options.submit_labels ? '<input class="clearable" type="hidden" name="'+ths.options.field_name_label+'" value="'+ui.item.label+'"/>' : '')+'</div>\
                                                    <a href="javascript:" class="select2-search-choice-close" tabindex="-1"></a>');
                }
            else{
                new_item_add = $('<li>')
                    .attr('class', 'select2-search-choice')
                    .html('<div>'+ui.item.label+'<input type="hidden" name="'+ths.options.field_name+'[]" value="'+ui.item.id+'"/>'+(ths.options.submit_labels ? '<input class="clearable" type="hidden" name="'+ths.options.field_name_label+'[]" value="'+ui.item.label+'"/>' : '')+'</div>\
                                    <a href="javascript:" class="select2-search-choice-close" tabindex="-1"></a>');
                }
            }

        return new_item_add;
        };

    ths.remove_item = function(closeBtn){
        ths.input_field.show().focus();
        closeBtn.closest("li").remove();
        };

    ths.bind_item_actions = function(elm){
        var closeBtn = elm.find('.select2-search-choice-close');
        closeBtn.on('click', function(){
            ths.remove_item($(this));
            });
        };

    ths.init = function(){

        ths.item_list.attr('data-single', ths.options.single ? '1' : '0');
        // don't navigate away from the field on tab when selecting an item
        ths.input_field.bind("keydown",function(event){
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $(this).autocomplete("instance").menu.active){
                event.preventDefault();
                }
            });

        ths.input_field.autocomplete({
            create: function(event, ui){
                if(ths.options.existing_items){
                    var totalItems = Object.keys(ths.options.existing_items).length;
                    for(var i=0;i<totalItems;i++){
                        var thisItem = ths.options.existing_items[i];
                        ths.item_list.prepend(ths.generate_new_item({item:{id:thisItem.id, label:thisItem.label}}));
                        }
                    }


                if(ths.options.single && ths.item_list.find('.select2-search-choice').length)
                    ths.input_field.hide();
                if(ths.item_list.find('.select2-search-choice').length){
                    ths.item_list.find('.select2-search-choice').each(function(index,element){
                        ths.bind_item_actions($(element));
                        });
                    }
                },
            source: function( request, response ) {
                ths.current_term = extractLast( request.term );
                $.ajax({
                    beforeSend: function(){show_button_overlay_working(ths.loadContainer)},
                    complete: function(){hide_button_overlay_working(ths.loadContainer)},
                    url: ths.options.o_ajax_page,
                    type: 'post',
                    data: {
                        term: extractLast( request.term ),
                        internalToken: _internalToken_,
                        _data: ths.options
                        },
                    cache: false,
                    dataType: "json",
                    success: function(ret){
                        if(ret.error) return null;
                        else response(ret);
                        }
                    });
                },
            search: function() {
                var term = extractLast( this.value );
                if ( term.length < ths.options.minLength ){
                    //console.log('could not search as term lengh is not accepted');
                    return false;
                    }
                },
            response: function(event, ui){
                if(ui.content.length){
                    var matchFound = false;
                    for(var i in ui.content){
                        ui.content[i]['label'] = ths.options.multilingual ? processToRender(ui.content[i]['label'], null, true) : ui.content[i]['label'];
                        if(ui.content[i]['label'].toLowerCase() == ths.current_term.toLowerCase()){
                            matchFound = true;
                            }
                        }
                    if(!matchFound && ths.options.add_new) ui.content.unshift({id: null, label: ths.current_term});
                    }
                else if(ths.options.add_new) ui.content.unshift({id: null, label: ths.current_term});
                },
            focus: function() {
                return false;
                },
            select: function( event, ui ) {
                var new_item = null;

                if(ths.options.add_new && !ui.item.id){
                    if(ths.options.confirm_add){
                        bootboxConfirm({
                            'title' : 'Create New '+ths.options.add_what,
                            'msg' : 'Do you want to create this new '+ths.options.add_what,
                            'confirm': {
                                'callback': function(){
                                    var dataForAdd = {internalToken: _internalToken_,for_autocomplete: true};
                                    dataForAdd[ths.options.field_for_add] = ui.item.label;
                                    $.extend(true, dataForAdd, ths.options.data_for_add);
                                    $.ajax({
                                        beforeSend: function(){show_button_overlay_working(ths.loadContainer)},
                                        complete: function(){hide_button_overlay_working(ths.loadContainer)},
                                        url: ths.options.url_for_add,
                                        cache: false,
                                        type: 'post',
                                        dataType: 'json',
                                        data: dataForAdd,
                                        success : function(ret){
                                            if(ret.success){
                                                new_item = ths.generate_new_item({item:ret.success});

                                                ths.input_field.parent().before(new_item);
                                                ths.input_field.val('');
                                                if(ths.options.single) ths.input_field.hide();
                                                ths.bind_item_actions(new_item);
                                                if(ths.options.after_select) ths.options.after_select(ui);
                                            }
                                            else modern_alert('Failed','Failed to create the item, please try again.','error',null);
                                        },
                                        error: function(){
                                            modern_alert('Failed','Network Error.','error',null);
                                        }
                                    });
                                },
                            },
                            'cancel' : {
                                'callback' : function(){
                                    ths.input_field.focus();
                                    ths.input_field.val('');
                                }
                            }
                        });
                        }
                    else {
                        var dataForAdd = {internalToken: _internalToken_,for_autocomplete: true};
                        dataForAdd[ths.options.field_for_add] = ui.item.label;
                        $.extend(true, dataForAdd, ths.options.data_for_add);
                        $.ajax({
                            beforeSend: function(){show_button_overlay_working(ths.loadContainer)},
                            complete: function(){hide_button_overlay_working(ths.loadContainer)},
                            url: ths.options.url_for_add,
                            cache: false,
                            type: 'post',
                            dataType: 'json',
                            data: dataForAdd,
                            success : function(ret){
                                if(ret.success){
                                    new_item = ths.generate_new_item({item:ret.success});

                                    ths.input_field.parent().before(new_item);
                                    ths.input_field.val('');
                                    if(ths.options.single) ths.input_field.hide();
                                    ths.bind_item_actions(new_item);
                                    if(ths.options.after_select) ths.options.after_select(ui);
                                }
                                else modern_alert('Failed','Failed to create the item, please try again.','error',null);
                            },
                            error: function(){
                                modern_alert('Failed','Network Error.','error',null);
                            }
                        });
                        }
                    }
                else{
                    new_item = ths.generate_new_item(ui);

                    ths.input_field.parent().before(new_item);
                    ths.input_field.val('');
                    if(ths.options.single) ths.input_field.hide();
                    ths.bind_item_actions(new_item);
                    if(ths.options.after_select) ths.options.after_select(ui);
                    }

                return false;
                }
            });

        /*ths.input_field.on('focusin', function(){
            console.log('focus in'+ths.options.searchOnClick);
            if(ths.options.searchOnClick) ths.input_field.autocomplete('search', ths.input_field.val());
            });*/

        ths.input_field.data('ui-autocomplete')._renderItem = function (ul, item) {
           /* if(ths.options.multilingual){
                item.label = processToRender(item.label, null, true);
                }*/
            var html = '';
            if(ths.options.render_as) html = window[ths.options.render_as](item);
            else{
                if(ths.options.add_new && !item.id) html = '<a href="javascript:" class="tar" style="border-bottom: 1px solid #ddd;"><i class="fa fa-plus-circle"></i>&nbsp;<strong>'+item.label+'</strong> (Create New)</a>';
                else html = '<a href="javascript:">'+item.label+'</a>';
                }
            return $( "<li>" )
                .append(html)
                .appendTo( ul );
            };

        ths.item_list.on('click', function(e){
            $(this).find('.autocomplete_inputbox input').focus();
            });
        };

    ths.select_manually = function(item){
        ths.input_field.data('ui-autocomplete')._trigger('select', 'autocompleteselect', {item: item});
        };

    ths.init();

    return ths;
    };

function on_render_user(item){
    if(item == undefined) return '';
    var ret = '<a class="db"><div class="oh">\
                    <div style="line-height: 30px"><img src="'+item['user_picture_link']+'" style="padding:1px" width="30" height="30" class="mr5"/><strong>'+item['user_fullname']+'</strong></div>\
            </div></a>';

    return ret;
    }
function on_select_user(options,ui){
    if(ui == undefined || ui.item == undefined) return '';

    var new_item_add = '<li class="select2-search-choice">\
                            <input type="hidden" name="'+options.field_name+'[]" value="'+ui.item['pk_user_id']+'">\
                            <div class="oh">\
                                <div style="display: flex;line-height: 30px"><img src="'+ui.item['user_picture_link']+'" style="padding:1px" width="30" height="30" class="mr5"/><strong>'+ui.item['user_fullname']+'</strong></div>\
                            </div>\
                            <a href="javascript:" class="select2-search-choice-close" tabindex="-1"></a>\
                        </li>';

    return new_item_add;
    }
function on_select_students(options,ui){
    if(ui == undefined || ui.item == undefined) return '';

    var new_item_add = '<li class="select2-search-choice">\
                            <input type="hidden" name="'+options.field_name+'[]" value="'+ui.item.id+'"></div>\
                            <div class="oh">\
                                <img src="'+_root_path_+'/site/contents/uploads/'+ui.item['reg_students_photo']+'" width="40" style="float:left;margin:0 5px 5px 0">\
                                <div style="display: flex"><strong>'+ui.item['reg_fullname']+'</strong></div>\
                            </div>\
                            <a href="javascript:" class="select2-search-choice-close" tabindex="-1"></a>\
                        </li>';

    return new_item_add;
    }
function on_render_students(item){
    if(item == undefined) return '';
    var ret = '<a class="db"><div class="oh">\
                    <img src="'+_root_path_+'/site/contents/uploads/'+item['reg_students_photo']+'" width="40" style="float:left;margin:0 5px 5px 0">\
                    <div><strong>'+item['reg_fullname']+'</strong></div>\
                    <hr style="margin-top: 5px;margin-bottom: 5px;" />\
            </div></a>';

    return ret;
    }
function on_render_notice(item){
    if(item == undefined) return '';
    var ret = '<a class="db"><div class="oh">\
                    <div><strong>'+item['user_fullname']+'</strong></div>\
            </div></a>';

    return ret;
    }
function on_select_notice(options,ui){
    if(ui == undefined || ui.item == undefined) return '';

    var new_item_add = '<li class="select2-search-choice">\
                            <input type="hidden" name="'+options.field_name+'[]" value="'+ui.item['pk_user_id']+'"></div>\
                            <div class="oh">\
                                <div style="display: flex"><strong>'+ui.item['user_fullname']+'</strong></div>\
                            </div>\
                            <a href="javascript:" onclick="return false;" class="select2-search-choice-close" tabindex="-1"></a>\
                        </li>';

    return new_item_add;
    }
function set_autocomplete(options){
    options.o_ajax_page = options.ajax_page;
    options.ajax_page = options.ajax_page + '&ajax_type=' + options.ajax_type + '&tag_group=' + options.tag_group;
    var added_items = $(options.field).closest('.select2-info').find('.select2-choices');
    var auto_loader = $(options.field).closest('.select2-info').parent().find('.autocomplete_loading');
    options.field_name = options.field_name ? options.field_name : 'tags';

    $(options.field)
        // don't navigate away from the field on tab when selecting an item
        .bind("keydown",function(event){
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $(this).autocomplete("instance").menu.active){
                event.preventDefault();
                }
            })
        .autocomplete({
            source: function( request, response ) {
                $.getJSON( options.ajax_page, {
                    term: extractLast( request.term )
                    }, response );
                },
            search: function() {
                auto_loader.show();
                var term = extractLast( this.value );
                if ( term.length < 1 ) {
                    return false;
                    }
                },
            response: function(event, ui){
                auto_loader.hide();
                },
            focus: function() {
                return false;
                },
            select: function( event, ui ) {
                var terms = split( this.value );
                terms.pop();
                terms.push( "" );
                this.value = terms.join( ", " );

                if(!ui.item.id){
                    $.ajax({
                        beforeSend: function(){auto_loader.show();},
                        url:options.o_ajax_page+'&ajax_type=add_a_tag&tag_title='+ui.item.label+'&tag_group='+options.tag_group,
                        cache: false,
                        type: 'get',
                        dataType: 'json',
                        success : function(ret){
                            console.log(ret);
                            if(ret.success){
                                var new_item_add = '<li class="select2-search-choice">\
                                            <div>'+ui.item.label+'<input type="hidden" name="'+options.field_name+'[]" value="'+ret.success+'"></div>\
                                            <a href="#" onclick="return false;" class="select2-search-choice-close" tabindex="-1"></a>\
                                            </li>';

                                added_items.find('li.autocomplete_inputbox').before(new_item_add);
                                }
                            else modern_alert('Failed','Failed to create the tag, please try again.','error',null);
                            },
                        error: function(){
                            modern_alert('Failed','Failed to create the tag, please try again.','error',null);
                            },
                        complete: function(){
                            auto_loader.hide();
                            }
                        });
                    }
                else{
                    var new_item_add = '<li class="select2-search-choice">\
                                        <div>'+ui.item.label+'<input type="hidden" name="'+options.field_name+'[]" value="'+ui.item.id+'"></div>\
                                        <a href="javascript:" onclick="return false;" class="select2-search-choice-close" tabindex="-1"></a>\
                                        </li>';

                    added_items.find('li.autocomplete_inputbox').before(new_item_add);
                    }

                return false;
                }
            }).data('ui-autocomplete')._renderItem = function (ul, item) {
                if(!item.id){
                    return $( "<li>" )
                        .append( '<a href="javascript:" class="tar" title="Create tag \''+item.label+'\' &amp; Add here also" style="border-bottom: 1px solid #ddd;"><i class="fa fa-plus-circle"></i>&nbsp;<strong>'+item.label+'</strong> (Create New)</a>')
                        .appendTo( ul );
                    }
                else{
                    return $( "<li>" )
                        .append( '<a href="javascript:">'+item.label+'</a>')
                        .appendTo( ul );
                    }
                };
    }

$(document).on('click','.filter-panel .panel-heading',function(){
    $(this).closest('.filter-panel').find('.panel-body').slideToggle();
    });
$(document).on('click',".closeTag",function(){
    $(this).closest(".tagsItems").remove();
    });

if(jQuery().fancybox){
    // file manager as input field
    $('.iframe-btn').fancybox({
        fitToView: false,
        autoSize: false,
        autoDimensions: false,
        'width'	: 880,
        'height': 570,
        'type'	: 'iframe'
        });

    $('body').on('focusin', function(){
        $('.img-iframe-btn').fancybox({
            fitToView: false,
            autoSize: false,
            autoDimensions: false,
            iframe : {
                css : {
                    width : '880px',
                    height: '570px'
                    }
                },
            'type'	: 'iframe',
            afterClose : function(instance, currentSlide){
                var thsElm = $($(this)[0]['opts']['$orig']['context']);
                var container = thsElm.closest('.image_upload_container');
                var inputField = container.find('input');
                if(inputField.val().length){
                    show_button_overlay_working(container);
                    if(!container.find('img').length)
                        container.append('<img src="" style="display: none" />');
                    container.find('img').on('load',function(){
                        hide_button_overlay_working(container);
                        container.find('img').show();
                    });
                    container.find('img').attr('src', get_image(inputField.val(), thsElm.attr('data-img-size')));
                }
            }
        });
        });

    $('.previewFeaturedImageAll').on('click', function(){
        var socialSites = {
            'fb' : {
                size: '1200x630',
                label: 'Facebook (1200x630)'
                },
            'li' : {
                size: '1200x627',
                label: 'Linked In (1200x627)'
            },
            'gp' : {
                size: '1200x576',
                label: 'Google Plus (1200x576)'
            },
            'tw' : {
                size: '1200x586',
                label: 'Twitter (1200x586)'
            },
            'pi' : {
                size: '1200x630',
                label: 'Pinterest (1200x630)'
            }};
        var ths = $(this);
        var socialSite = ths.attr('data-social');
        var container = ths.closest('.panel');
        var inputField = container.find('input');
        if(inputField.val().length){
            var slides = [];
            for(var i in socialSites){
                slides.push({
                    src  : get_image(inputField.val(), socialSites[i]['size']),
                    opts : {
                        caption : socialSites[i]['label'],
                        thumb   : get_image(inputField.val(), '100x100'),
                        }
                    });
                }
            $.fancybox.open(slides,
                {
                    loop : false
                });
            }
        });
    $('.previewFeaturedImage').on('click', function(){
        var socialSites = {'fb' : {
                        size: '1200x630',
                        label: 'Facebook (1200x630)'
                        },
                    'li' : {
                        size: '1200x627',
                        label: 'Linked In (1200x627)'
                    },
                    'gp' : {
                        size: '1200x576',
                        label: 'Google Plus (1200x576)'
                    },
                    'tw' : {
                        size: '1200x586',
                        label: 'Twitter (1200x586)'
                    },
                    'pi' : {
                        size: '1200x630',
                        label: 'Pinterest (1200x630)'
                    }};
        var ths = $(this);
        var socialSite = ths.attr('data-social');
        var container = ths.closest('.panel');
        var inputField = container.find('input');
        if(inputField.val().length){
            show_button_overlay_working(ths);
            $.fancybox.open([
                {
                    src  : get_image(inputField.val(), socialSites[socialSite]['size']),
                    opts : {
                        caption : socialSites[socialSite]['label'],
                        thumb   : get_image(inputField.val(), '100x100'),
                        afterShow : function( instance, current ) {
                            hide_button_overlay_working(ths);
                        }
                    }
                }],
                {
                loop : false
                });
            }
        });
    $(document).on('click', '.image_upload_container .trashBtn', function(){
        $(this).closest('.image_upload_container').find('input').val('');
        $(this).closest('.image_upload_container').find('img').remove();
        });
    $('.auto-iframe-btn').fancybox();
    }
function clear_page_alert(){
    PixelAdmin.plugins.alerts.clear(
        true, // animate
        'pa_page_alerts_default' // namespace
        );
    }
function page_alert(type, text, autoClose, autoCloseTime){
    /*
    * type ; warning, danger, success, info,
    * */
    if(autoClose == undefined) autoClose = false;
    if(autoCloseTime == undefined) autoCloseTime = 3;

    var $this = $(this);
    // Go to the top
    $('html,body,#main-wrapper').animate({ scrollTop: 0 }, 500);
    //$('#content-wrapper').animate({ scrollTop: 0 }, 500);
    // Wait while page is scrolling
    setTimeout(function () {
        var options = {
            type: type,
            namespace: 'pa_page_alerts_default'
            };
        if (autoClose)
            options['auto_close'] = autoCloseTime; // seconds
        PixelAdmin.plugins.alerts.add(text, options);
        }, 600);
    }
function modern_confirm(msg, callback, className, title){
    className = className === undefined ? 'bootbox-sm' : className;
    title = title === undefined ? false : title;
    bootbox.confirm({
        title: title,
        message: msg,
        callback: function(result){
            window[callback](result);
            },
        className: className
        });
    }
function modern_alert(msg_title, msg_text,type,callback){
    if(!callback) callback = function(){};
    if(type == 'error') type = 'danger';

    var icon = '';
    if(type == 'success') icon = 'fa-check-circle';
    else if(type == 'danger') icon = 'fa-times-circle';
    else if(type == 'warning') icon = 'fa-warning';
    else if(type == 'info') icon = 'fa-info-circle';

    var thisID = type+'_'+Date.now();
    var thisTriggerId = thisID+'_button';

    var _html = '<div style="z-index:9999999999" id="'+thisID+'" class="modal modal-alert modal-'+type+' fade">\
                    <div class="modal-dialog">\
                        <div class="modal-content">\
                            <div class="modal-header">\
                                <i class="fa '+icon+'"></i>\
                            </div>\
                            <div class="modal-title">'+msg_title+'</div>\
                            <div class="modal-body">'+msg_text+'</div>\
                            <div class="modal-footer">\
                                <button type="button" class="btn btn-'+type+' modal_button" onclick="" data-dismiss="modal">OK</button>\
                            </div>\
                        </div>\
                    </div>\
                </div><button class="dn" id="'+thisTriggerId+'" data-toggle="modal" data-target="#'+thisID+'">';

    $('body').append(_html);
    $('#'+thisTriggerId).trigger('click');
    $('#'+thisID+' .modal_button').on('click', function(){callback()});
    }
function bootboxConfirm(opt){
    var options = {
        title: '',
        msg: '',
        confirm: {
            text: 'Yes',
            btnIcon: 'fa-check',
            btnClass: 'btn-success',
            callback: function(){},
            },
        cancel: {
            text: 'No',
            btnIcon: 'fa-times',
            btnClass: 'btn-danger',
            callback: function(){},
            },
        className: '',
        };
    options = $.extend(true, options, opt);
    var ret = bootbox.confirm({
        title: options.title,
        message: options.msg,
        buttons: {
            confirm: {
                label: '<i class="btn-label fa ' + options.confirm.btnIcon + '"></i> ' + options.confirm.text+' <span class="keyboard_btn">ENTER</span>',
                className: options.confirm.btnClass + ' btn-flat btn-labeled'
                },
            cancel: {
                label: '<i class="btn-label fa ' + options.cancel.btnIcon + '"></i> ' + options.cancel.text+' <span class="keyboard_btn">ESC</span>',
                className: options.cancel.btnClass + ' btn-flat btn-labeled'
                }
            },
        callback: function (result) {
            if(result) options.confirm.callback();
            else options.cancel.callback();

            $(ret).off('keyup');
            },
        closeButton: false,
        className: options.className
        });

    $(ret).on('keyup',function(e){
        if(e.keyCode == 13){
            e.preventDefault();
            $(ret).find('[data-bb-handler="confirm"]').trigger('click');
            }
        });
    }

function build_url(replaceItems, deleteItems, theUrl){
    if(!replaceItems || !Object.keys(replaceItems).length) replaceItems = {};
    if(!deleteItems) deleteItems = [];
    if(!theUrl) theUrl = null;

    var currentUrl = theUrl ? theUrl : window.location.href;//'http://www.ijc.com/admin?start=2&id=10';//
    var urlParts = currentUrl.split('?');

    var directory = urlParts[0];
    var i = 0,
        v = 0,
        j = 0,
        thisTerm = '',
        thisTermParts = '',
        found = false;

    var query = typeof urlParts[1] !== 'undefined' ? urlParts[1] : null;
    if(query) query = query.split('&');
    else query = [];

    var count = query.length;

    for(i in replaceItems){
        found = false;

        if(query.length){
            for(j in query){
                thisTerm = query[j];
                if(thisTerm.length){
                    thisTermParts = thisTerm.split('=');
                    if(thisTermParts[0] == i){
                        found = true;
                        query[j] = i + '=' + replaceItems[i];
                        break;
                        }
                    }
                }
            }

        if(!found) query[query.length] = i + '=' + replaceItems[i];
        }


    for(i in deleteItems){
        for(j in query){
            thisTerm = query[j];
            if(thisTerm.length){
                thisTermParts = thisTerm.split('=');
                if(thisTermParts[0] == deleteItems[i]){
                    query.splice(j,1);
                    break;
                    }
                }
            }
        }

    query = query.join('&');

    currentUrl = directory + '?' + query;

    return currentUrl;
    }

/* Custom Accordion JS Code */
$(document).on('click','.custom_accordion li.dropdown',function(){
    var ths = $(this);
    var the_child_menu = $(ths).find('.dropdown-menu');
    var opened = the_child_menu.hasClass('open');
    
    $(ths).closest('.custom_accordion').find('.dropdown-menu').slideUp().removeClass('open');
    $(ths).closest('.custom_accordion').find('li.dropdown > a ._icon').removeClass('open').addClass('closed');

    if(!opened){
        $(ths).find('.dropdown-menu').slideDown().addClass('open');
        $(ths).find('> a ._icon').addClass('open').removeClass('closed');
        }
    });

$(document).ready(function(e){
    $('.custom_accordion').each(function(index,element){
        var ths = $(element);

        $('li.dropdown',element).each(function(i,j){
            $(j).find('>a').append('<i class="_icon closed fa fa-chevron-circle-left"></i>');
            });
        });
    });

//like dislike action handling
$(document).on('click','.like_dislike_btn',function(){
    var ths = $(this);
    var container = ths.closest('.dev_LikeDislike');
    var action = ths.attr('data-action');
    var content = container.attr('data-id');
    var icon = ths.find('.like_dislike_icon');

    if(__dev__user__){
        $.ajax({
            beforeSend: function(){
                icon.removeClass('fa-thumbs-up fa-thumbs-down').addClass('fa-spinner fa-pulse');
                },
            complete: function(){
                if(ths.hasClass('like_it'))
                    icon.addClass('fa-thumbs-up').removeClass('fa-spinner fa-pulse');
                else
                    icon.addClass('fa-thumbs-down').removeClass('fa-spinner fa-pulse');
                },
            url: _root_path_ + '/ajaxRequests',
            data: {
                'ajax_type' : 'like_dislike_action',
                'content_type' : 'content',
                'content' : content,
                'action' : action
                },
            dataType: 'json',
            type: 'post',
            success: function(r){
                if(r.like_added && r.dislike_deleted){
                    container.find('.like_it').addClass('taken');
                    container.find('.like_count').html(r.total_like);
                    container.find('.like_it').attr('title','Unlike It');

                    container.find('.dislike_it').removeClass('taken');
                    container.find('.dislike_count').html(r.total_dislike);
                    container.find('.dislike_it').attr('title','Dislike It');
                    }
                else if(r.like_deleted && r.dislike_added){
                    container.find('.like_it').removeClass('taken');
                    container.find('.like_count').html(r.total_like);
                    container.find('.like_it').attr('title','Like It');

                    container.find('.dislike_it').addClass('taken');
                    container.find('.dislike_count').html(r.total_dislike);
                    container.find('.dislike_it').attr('title','Undislike It');
                    }
                else if(r.like_deleted){
                    container.find('.like_it').removeClass('taken');
                    container.find('.like_count').html(r.total_like);
                    container.find('.like_it').attr('title','Like It');
                    }
                else if(r.dislike_deleted){
                    container.find('.dislike_it').removeClass('taken');
                    container.find('.dislike_count').html(r.total_dislike);
                    container.find('.dislike_it').attr('title','Dislike It');
                    }
                else if(r.like_added){
                    container.find('.like_it').addClass('taken');
                    container.find('.like_count').html(r.total_like);
                    container.find('.like_it').attr('title','Unlike It');
                    }
                else if(r.dislike_added){
                    container.find('.dislike_it').addClass('taken');
                    container.find('.dislike_count').html(r.total_dislike);
                    container.find('.dislike_it').attr('title','Undislike It');
                    }
                },
            error: function(e){

                }
            });
        }
    else{
        if(confirm('Are you a MORNINGTONER? SignIn now! Otherwise SignUp to like-dislike posts.')){
            window.location.href = _root_path_+'/login';
            }
        }
    });
//like dislike status checking
$(document).ready(function(){
    if(__dev__user__){
        $('.dev_LikeDislike').each(function(index, element){
            var container = $(element);
            var content = container.attr('data-id');
            var icon = container.find('.like_dislike_icon');
            var like_it = container.find('.like_it');
            var dislike_it = container.find('.dislike_it');

            $.ajax({
                beforeSend: function(){
                    like_it.find('.like_dislike_icon').removeClass('fa-thumbs-up').addClass('fa-spinner fa-pulse');
                    dislike_it.find('.like_dislike_icon').removeClass('fa-thumbs-down').addClass('fa-spinner fa-pulse');
                    },
                complete: function(){
                    like_it.find('.like_dislike_icon').addClass('fa-thumbs-up').removeClass('fa-spinner fa-pulse');
                    dislike_it.find('.like_dislike_icon').addClass('fa-thumbs-down').removeClass('fa-spinner fa-pulse');
                    },
                url: _root_path_ + '/ajaxRequests',
                data: {
                    'ajax_type' : 'like_dislike_status',
                    'content_type' : 'content',
                    'content' : content
                    },
                dataType: 'json',
                type: 'post',
                success: function(r){
                    if(r.status){
                        if(r.status == 'like'){
                            like_it.attr('title','Unlike It').addClass('taken');
                            }
                        else{
                            dislike_it.attr('title','Undislike It').addClass('taken');
                            }
                        }
                    },
                error: function(e){

                    }
                });
            });
        }
    });

/*--Form Related--*/
function clearFilters(btn){
    var the_form = $(btn.closest('form'));
    the_form.find('input[type!="hidden"], select, textarea').removeAttr('name');
    the_form.find('.select2-choices input').removeAttr('name');
    the_form.find('input.clearable').removeAttr('name');
    the_form.submit();
    }
function clear_n_submit(theForm){
    if(clear_form(theForm)){
        theForm.submit();
        }
    }
function clear_form(opt){
    var options = {};

    $(opt).find('input, select, textarea').each(function(index,element){
        clear_form_element($(element),true);
        });
    return true;
    }
function clear_form_element(elm, exclude_hidden){
    var tag = elm.tagName().toLowerCase();
    var type = elm.attr('type');
    if( (tag == 'input' && (type == 'text' || type == 'hidden' || type == 'file' || type == 'number'))
        || tag == 'textarea'){
        if(type == 'hidden'){
            console.log(elm.parent().parent());
            if(elm.parent().parent().hasClass('select2-search-choice')){
                //clear these, these are auto-complete fields
                elm.val('');
                }
            else if(!exclude_hidden) elm.val('');
            }
        else elm.val('');
        }
    else if(tag == 'input' && (type == 'radio' || type == 'checkbox')){
        elm.removeAttr('checked');
        }
    else if(tag == 'select'){
        elm.find('option').removeAttr('selected');
        }
    }
function reset_form(theForm){

    }
function reset_form_element(elm){

    }
/***************/

$(document).on('click','.select2-choices', function(){
    $(this).find('.autocomplete_inputbox input').focus();
    });
/*** file manager***/
$(document).ready(function(){
    init_filemanager_input();
    });
function init_filemanager_input(){
    $('input[type="text"]').each(function(){
        var ths = $(this);
        var theParent = ths.parent();
        if(theParent.hasClass('input-group')){
            if(theParent.find('> .iframe-btn').length){
                ths.attr('readonly',true);
                ths.addClass('file-input');
                ths.click(function(){
                    theParent.find('> .iframe-btn').trigger('click');
                    });
                }
            }
        });
    if(jQuery().fancybox){
        $('.iframe-btn').fancybox({
            fitToView: false,
            autoSize: false,
            autoDimensions: false,
            'width'	: 880,
            'height': 570,
            'type'	: 'iframe'
            });
        }
    }

/********/
$(document).ready(function(){
    $('.hue_picker').each(function(index,element){
        var valueFormat = $(element).attr('data-format') ? $(element).attr('data-format') : 'hex';
        var options = {
            control: 'hue',
            format: valueFormat,
            position: 'bottom left',
            theme: 'bootstrap'
            };
        $(element).minicolors(options);
        });
    });

$(document).on('click','.shareOn',function(){
    var pTitle = encodeURIComponent(document.title);
    var pLink = encodeURIComponent(window.location.href);
    var pShareUrl = '';

    if($(this).hasClass('facebook'))
        pShareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + pLink;
    else if($(this).hasClass('twitter'))
        pShareUrl = 'https://twitter.com/intent/tweet?text=' + pTitle + '&url=' + pLink;
    else if($(this).hasClass('gplus'))
        pShareUrl = 'https://plus.google.com/share?url=' + pLink;
    else return false;

    window.open(pShareUrl, '_blank','width=600,height=400');
    });

$(document).on('click','.printNow',function(){
    var printSelector = $(this).attr('data-printSelector');
    thePrintFunction(printSelector,{});
    });
function thePrintFunction(theContainer,options){
    var config = {'iframe' : false};
    $.extend( true, config, options );
    $(theContainer).print(config);
    }
function fixMyChildHeights(){
    $('.fixMyChildHeights').each(function(index,element){
        var container = $(element);
        var maxHeight = 0;
        var childSelector = container.attr('data-fix-child') ? container.attr('data-fix-child') : ' > *';
        var heightType = container.attr('data-height-type') ? container.attr('data-height-type') : 'normal'; //outer
        if(!childSelector.length) childSelector = ' > *';

        var children = container.find(childSelector);
        for(var i = 0; i < children.length; i++){
            var thisHeight = 0;
            if(heightType == 'normal') thisHeight = $(children[i]).height();
            else if(heightType == 'outer') thisHeight = $(children[i]).outerHeight();

            if(thisHeight > maxHeight)
                maxHeight = thisHeight;
           }
        children.css('min-height',maxHeight);
        });
    }
$(document).ready(function(){
    $('.hue_picker').each(function(index,element){
        var valueFormat = $(element).attr('data-format') ? $(element).attr('data-format') : 'hex';
        var options = {
            control: 'hue',
            format: valueFormat,
            position: 'bottom left',
            theme: 'bootstrap'
            };
        $(element).minicolors(options);
        });

    init_filemanager_input();

    if(__dev__user__){
        $('.dev_LikeDislike').each(function(index, element){
            var container = $(element);
            var content = container.attr('data-id');
            var icon = container.find('.like_dislike_icon');
            var like_it = container.find('.like_it');
            var dislike_it = container.find('.dislike_it');

            $.ajax({
                beforeSend: function(){
                    like_it.find('.like_dislike_icon').removeClass('fa-thumbs-up').addClass('fa-spinner fa-pulse');
                    dislike_it.find('.like_dislike_icon').removeClass('fa-thumbs-down').addClass('fa-spinner fa-pulse');
                },
                complete: function(){
                    like_it.find('.like_dislike_icon').addClass('fa-thumbs-up').removeClass('fa-spinner fa-pulse');
                    dislike_it.find('.like_dislike_icon').addClass('fa-thumbs-down').removeClass('fa-spinner fa-pulse');
                },
                url: _root_path_ + '/ajaxRequests',
                data: {
                    'ajax_type' : 'like_dislike_status',
                    'content_type' : 'content',
                    'content' : content
                },
                dataType: 'json',
                type: 'post',
                success: function(r){
                    if(r.status){
                        if(r.status == 'like'){
                            like_it.attr('title','Unlike It').addClass('taken');
                        }
                        else{
                            dislike_it.attr('title','Undislike It').addClass('taken');
                        }
                    }
                },
                error: function(e){

                }
            });
        });
    }

    $('.custom_accordion').each(function(index,element){
        var ths = $(element);

        $('li.dropdown',element).each(function(i,j){
            $(j).find('>a').append('<i class="_icon closed fa fa-chevron-circle-left"></i>');
            });
        });

    //init a datetimepicker
    if($.fn.datepicker)
        $( ".pick_date" ).datepicker({dateFormat:'yy-mm-dd',changeMonth: true,changeYear: true});
    if($.fn.timepicker)
        $( ".pick_time" ).timepicker({timeFormat: "HH:mm"});
    if($.fn.datetimepicker)
        $( ".datetimepicker" ).datetimepicker({dateFormat:'yy-mm-dd',changeMonth: true,changeYear: true,timeFormat: "HH:mm"});

    //fix div heights to maximum
    fixMyChildHeights();
    });

$(document).on('click', '.linked_button', function(){
    window.location.href = $(this).attr('data-link');
    });

function getTableColumnWidths(table, width_unit){
    var widthUnit = typeof width_unit === 'undefined' ? '%' : width_unit; //%, px
    var container = table.find('>thead>tr');
    var selector = 'th'
    if(table.find('>thead>tr').length) {container = table.find('>thead>tr');selector = 'th'}
    else if(table.find('>thead>th').length) {container = table.find('>thead');selector = 'th'}
    else if(table.find('>tbody>tr').length) {container = table.find('>tbody>tr');selector = 'td'}
    else if(table.find('>tr').length) {container = table.find('>tr');selector = 'td'}
    var output = [];
    var totalWidth = container.outerWidth();

    for(var i = 0; i<$(selector, container).length;i++){
        var element = $(selector, container)[i];
        var thisWidth = $(element).outerWidth();
        if(widthUnit == '%') thisWidth = ((thisWidth/totalWidth)*100);
        output.push(thisWidth);
        }

    return output;
    }

function getChildWidths(container, width_unit) {
    var widthUnit = typeof width_unit === 'undefined' ? '%' : width_unit; //%, px
    var output = [];
    var totalWidth = container.outerWidth();
    for (var i = 0; i < container.find('>*').length; i++) {
        var element = container.find('>*')[i];
        var thisWidth = $(element).outerWidth();
        if (widthUnit == '%') thisWidth = ((thisWidth / totalWidth) * 100);
        output.push(thisWidth);
        }
    return output;
    }

$(document).on('click','.primaryNotificationContainer .close',function(){
    $(this).closest('.primaryNotificationContainer').slideUp().remove();
    });

function processToRender(data, language, returnWhatever){
    if(typeof language == 'undefined' || !language) language = _slang_;
    if(typeof data == 'undefined' || !data) return '';

    find = new RegExp('{:'+language+'}(.*){:'+language+'}','m');
    ret = data.match(find);

    if(ret) return ret[1];
    else{
        //no match for required language
        //check match for default language
        find = new RegExp('{:'+_dlang_+'}(.*){:'+_dlang_+'}','m');
        ret = data.match(find);
        if(ret) return ret[1];
        else{
            //no match even for the default language
            //check match for any language, the first one available will be sent back
            find = new RegExp('{:('+Object.keys(_langs_).join('|')+')}(.*){:(\1)}','m');
            ret = data.match(find);
            if(ret) return ret[1];
            else if(typeof returnWhatever != 'undefined' && returnWhatever) return data;
            else return '';
            }
        }
    }
$('.disableRightClick').each(function(i,e){
    $(e).on('contextmenu', function(event){event.preventDefault();});
    });
$('.disableRightClick').on('contextmenu', function(e){e.preventDefault();});
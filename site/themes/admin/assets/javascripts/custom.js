var textAreaCount = function(textarea){
    var thisObject = $(this);
    $(textarea).wrap('<div></div>');
    $(textarea).closest('div').append('<span class="label label-info"><span class="counter"></span> Characters</span>');
    $(textarea).on('keyup',function(){
        $(this).closest('div').find('.counter').html($(this).val().length);
        });
    $(textarea).closest('div').find('.counter').html($(textarea).val().length);
    };
var in_page_add_event = function(option){
    var thisObject = $(this);
    var the_form_container = option.form_container;
    var ths = ths ? option.ths : null;
    var url = option.url;
    var success_message = typeof option.success_message !== "undefined" ? option.success_message : null;
    var edit_mode = typeof option.edit_mode !== "undefined" ? true : false;
    var view_mode = typeof option.view_mode !== "undefined" ? true : false;
    var edit_form_url = option.edit_form_url;
    var label_column = typeof option.title !== "undefined" ? option.title : null;
    var value_column = typeof option.value !== "undefined" ? option.value : null;
    var error_callback = option.error_callback ? option.error_callback : null;
    var callback = option.callback ? option.callback : null;
    var edit_form_error_callback = option.edit_form_error_callback ? option.edit_form_error_callback : null;
    var edit_form_success_callback = option.edit_form_success_callback ? option.edit_form_success_callback : null;
    var view_mode_error_callback = option.view_mode_error_callback ? option.view_mode_error_callback : null;
    var view_mode_success_callback = option.view_mode_success_callback ? option.view_mode_success_callback : null;
    var additional_data = option.additional_data ? option.additional_data : null;
    var modal_width = option.modal_width ? (option.modal_width == 'full' ? $(window).width() - 100 : option.modal_width) : 500;
    var data = 'internalToken='+_internalToken_;
    var select_box = ths ? ths.closest('.input-group').find('select') : null;
    var for_select = (label_column && value_column) ? true : (typeof option.not_for_select_box !== "undefined" && option.not_for_select_box ? false : true);
    var clean_the_form = typeof option.clean_form !== "undefined" ? option.clean_form : true;
    var confirmation_required = typeof option.confirm_submission !== 'undefined' ? option.confirm_submission : false;
    var confirm_box_title = typeof option.confirm_box_title !== 'undefined' ? option.confirm_box_title : 'Submit Data';
    var confirm_box_message = typeof option.confirm_box_message !== 'undefined' ? option.confirm_box_message : 'Do you really want to submit these data?';

    if(clean_the_form) clear_form(the_form_container.find('form'));

    if (additional_data) {
        for(var k in additional_data){
            data += '&'+k+'='+additional_data[k];
            }
        }
    success_message = success_message ? success_message : (edit_mode ? 'Item was updated successfully' : 'Item was added successfully');

    var modalTitle = null;

    thisObject.next_step = function(){
        if(typeof option.form_title !== "undefined"){
            the_form_container.attr('title',option.form_title);
            modalTitle = option.form_title;
            }
        var submitBtn = option.submit_button ? option.submit_button : (edit_mode ? 'UPDATE' : 'ADD');
        var thisSubmitID = 'submit_'+Date.now();
        var this_dialog = the_form_container.dialog({
            title: modalTitle ? modalTitle : '',
            autoOpen: true,
            modal: true,
            width: modal_width,
            show: { effect: "blind", direction: 'up', duration: 500 },
            hide: { effect: "blind", duration: 500 },
            buttons: {
                'Cancel' : {
                    'text' : 'Cancel',
                    'click': function(){
                        this_dialog.dialog('close');
                        }
                    },
                'Add' : {
                    'text' : submitBtn,
                    'id' : thisSubmitID,
                    'click': function(){
                        var thisSubmitButton = $('#'+thisSubmitID);
                        show_button_overlay_working(thisSubmitButton);
                        if(!the_form_container.find('form').valid()){
                            $.growl.error({message: 'Please fix the errors.'});
                            hide_button_overlay_working(thisSubmitButton);
                            return false;
                            }

                        function submitAction(){
                            sanitize_form_inputs(the_form_container.find('form'));
                            var serialized_data = the_form_container.find('form').serialize();

                            basicAjaxCall({
                                beforeSend: function(){show_button_overlay_working(thisSubmitButton);},
                                complete: function(){hide_button_overlay_working(thisSubmitButton);},
                                url: url,
                                data: data + '&' + serialized_data,
                                success: function(reply_data){
                                    show_button_overlay_working(thisSubmitButton);
                                    var html = '';
                                    if(reply_data.hasOwnProperty('error') && Object.keys(reply_data.error).length){
                                        growl_error(reply_data.error);
                                        if(error_callback) error_callback(reply_data);
                                        }
                                    else{
                                        reply_data = reply_data['success'];
                                        $.growl.notice({ message: success_message });
                                        if(for_select && label_column && value_column && select_box){
                                            select_box.find('option').removeAttr('selected');
                                            select_box.append('<option value="'+reply_data[value_column]+'" selected>'+reply_data[label_column]+'</option>');
                                            select_box.change();
                                            }
                                        this_dialog.dialog('close');
                                        if(callback) callback(reply_data);
                                        }
                                    hide_button_overlay_working(thisSubmitButton);
                                    },
                                error: function(a,b,c){
                                    var e = ["Network fail, can not connect to server."];
                                    growl_error(e);
                                    }
                                });
                            }

                        if(confirmation_required){
                            bootboxConfirm({
                                title: confirm_box_title,
                                msg: confirm_box_message,
                                confirm: {
                                    callback: function(){
                                        submitAction();
                                        }
                                    },
                                cancel: {
                                    callback: function(){
                                        hide_button_overlay_working(thisSubmitButton);
                                        }
                                    }
                                });
                            }
                        else submitAction();
                        }
                    }
                }
            });
        };

    if(edit_mode){
        if(typeof option.edit_form_title !== "undefined"){
            the_form_container.attr('title',option.edit_form_title);
            modalTitle = option.edit_form_title;
            }

        basicAjaxCall({
            beforeSend: function(){if(ths) show_button_overlay_working(ths); showLoading()},
            complete: function(){if(ths) hide_button_overlay_working(ths); hideLoading()},
            url: edit_form_url,
            data: data,
            success: function(reply_data){
                var html = '';
                if(reply_data.hasOwnProperty('error') && Object.keys(reply_data.error).length){
                    growl_error(reply_data.error);
                    if(edit_form_error_callback) edit_form_error_callback();
                    }
                else{
                    the_form_container.html(reply_data.success);
                    the_form_container.find('form').validate();
                    if(edit_form_success_callback) edit_form_success_callback();
                    thisObject.next_step();
                    }
                },
            error: function(){
                var e = ["Network fail, can not connect to server."];
                growl_error(e);
                }
            });
        }
    else if(view_mode){
        if(typeof option.view_title !== "undefined"){
            modalTitle = option.view_title;
            the_form_container.attr('title',option.view_title);
            }

        $.ajax({
            beforeSend: show_working('Working ...'),
            complete: hide_working(),
            type: "POST",
            url: url,
            data: data,
            cache: false,
            dataType : 'json',
            success: function(reply_data){
                var html = '';
                if(reply_data['error']){
                    growl_error(reply_data['error']);
                    if(view_mode_error_callback) view_mode_error_callback();
                    }
                else{
                    the_form_container.html(reply_data.success);
                    var this_dialog = the_form_container.dialog({
                        open: function(){
                            if(view_mode_success_callback) view_mode_success_callback();
                            },
                        autoOpen: true,
                        modal: true,
                        width: modal_width,
                        show: { effect: "blind", direction: 'up', duration: 500 },
                        hide: { effect: "blind", duration: 500 },
                        buttons: {
                            'Close' : {
                                'text' : 'Close',
                                'click': function(){
                                    this_dialog.dialog('close');
                                }
                            }
                        }
                    });
                }
            },
            error: function(){
                var e = ["Network fail, can not connect to server."];
                growl_error(e);
            }
        });
    }
    else thisObject.next_step();
    };
var basicAjaxCall = function(option){
    var config = {
        beforeSend: function(){showLoading()},
        complete: function(){hideLoading()},
        url: window.location.href,
        data: {
            'internalToken' : _internalToken_
            },
        success: function(ret){},
        error: function(ret){},
        cache: false,
        dataType: 'json',
        timeout: 60000,
        crossDomain: false,
        };
    $.extend( true, config, option );
    $.ajax({
        beforeSend: function(){
            if(typeof config.beforeSend == "function") config.beforeSend();
            else window[config.beforeSend]();
            },
        complete: function(){
            if(typeof config.complete == "function") config.complete();
            else window[config.complete]();
            },
        url: config.url,
        type: 'post',
        data: config.data,
        dataType: config.dataType,
        timeout: config.timeout,
        cache: config.cache,
        crossDomain: config.crossDomain,
        success: function(ret){
            if(typeof config.success == "function") config.success(ret);
            else window[config.success](ret);
            },
        error: function(a,b,c){
            if(typeof config.error == "function") config.error(a,b,c);
            else $.growl.error({ title: 'Errors !', message: 'Something went wrong, please try again' });
            }
        });
    };

/***
 * open form class- to load and action via jQuery UI Dialog and Ajax
 * @param opt
 * - formContainer - the html element to load the form on e.g. $('#the_agent_form')
 * - formWidth - the width of the form; default is 500px
 * - formTitle - the title of the form; default is 'Add/Edit Form'
 * - formUrl - the url to load the form through ajax
 * - actionUrl - the url to do the action through button clicking of the loaded form via ajax
 * - actionButton - the action button of the form
 * - actionButtonText - the text of the action button of the form
 * - targetElem: the element to update after action e.g. ths.closest('.input-group').find('select'),
 * - targetType: the type of the element to update e.g. 'selectBox',
 * - Title: the title of the updating value e.g. 'agent_name',
 * - Value: the value to be updated with e.g. 'pk_supply_agent_id',
 * - preActionApi: the api to be executed before form input sanitizing
 * - successFunction: the success function to be executed after successful action
 * - errorFunction: the error function to be executed after action error
 * - if no formUrl is given::
 * - - actionJack: the action jack when no actionUrl is used
 * - - actionApi: the action api if no actionUrl is used
 * - if no actionUrl is given::
 * - - formJack: the jack to use to load form if no formUrl is used
 * - - formApi: the api to call to load form if no formUrl is used
 * * *
 * example:::::::::::::::::::::::::::::
 * $('.add_agent').click(function(){
            var ths = $(this);
            new open_form({
                formContainer: $('#the_agent_form'),
                //actionJack: 'dev_supply_management',
                preActionApi: 'abc',
                //actionApi: 'add_edit_agents',
                //formJack: 'dev_supply_management',
                //formApi: 'get_agents_form',
                formTitle: 'Add Agent',
                formUrl: 'dev_supply_management/get_agents_form',
                actionUrl: 'dev_supply_management/add_edit_agents',
                formWidth: 500,
                actionButton: 'Add',
                actionButtonClass: 'btn btn-primary',
                actionButtonText: 'UPDATE',
                targetElem: ths.closest('.input-group').find('select'),
                targetType: 'selectBox',
                Title: 'agent_name',
                Value: 'pk_supply_agent_id',
                successFunction: '',
                errorFunction: ''
                });
});
 */
function abc(){
    console.log('do something!');
    }
var open_form = function(opt){
    if (formUrl && actionUrl) return null;
    var formContainer = opt.formContainer;
    var actionJack = opt.actionJack;
    var actionApi = opt.actionApi;
    var formJack = opt.formJack;
    var formApi = opt.formApi;
    var actionUrl = opt.actionUrl;
    var formUrl = opt.formUrl;
    var formTitle = opt.formTitle ? opt.formTitle : 'ADD/Edit Form';
    var formWidth = opt.formWidth ? opt.formWidth : 500;
    var actionButton = opt.actionButton;
    var actionButtonText = opt.actionButtonText;
    var actionButtonClass = opt.actionButtonClass;
    var targetElem = opt.targetElem;
    var targetType = opt.targetType;
    var actionData = opt.data;
    var myButtons = {
        actionButton : {
            'text' : actionButtonText,
            'class' : actionButtonClass,
            'click': function(){
                $.when(preActionApi(opt)).done(sanitizeFormInputs());
                }
            },
        "Cancel": function () {
            $(this).dialog("close");
            }
        };

    var this_dialog = formContainer.dialog({
        autoOpen: true,
        modal: true,
        width: formWidth,
        title:formTitle,
        show: { effect: "blind", direction: 'up', duration: 500 },
        hide: { effect: "blind", duration: 500 }
        });

    var init = function(){
        getForm();
        formContainer.dialog('option', 'buttons', myButtons);
        };

    var getForm = function(){

        var data = 'internalToken='+_internalToken_;
        $.ajax({
            beforeSend: opt.beforeSendFunction ? window[opt.beforeSendFunction]() : show_working('Working ...'),
            complete: hide_working(),
            type: "POST",
            url: _root_path_ + (formUrl ? '/api/'+formUrl : '/api/' + formJack + '/' + formApi),
            cache: false,
            data: data,
            dataType : 'json',
            success: function(reply_data){
                var html = '';
                if(reply_data['error']) growl_error(reply_data['error']);
                else{
                    reply_data = reply_data['success'];
                    formContainer.html(reply_data);
                    }
                },
            error: function(){
                var e = ["Network fail, can not connect to server."];
                growl_error(e);
                }
            });
        };
    var errorFunction = function(opt){
        if(typeof window[opt.errorFunction] == 'function') window[opt.errorFunction]();
        };
    var successFunction = function(opt){
        if(typeof window[opt.successFunction] == 'function') window[opt.successFunction]();
        };
    var preActionApi = function(opt){
        var deferredReady = $.Deferred();
        if(typeof window[opt.preActionApi] == 'function') window[opt.preActionApi]();
        deferredReady.resolve(opt);
        return deferredReady.promise();
        };
    var getAction = function(opt,data,serialized_data){

        $.ajax({
            beforeSend: opt.beforeSendFunction ? opt.beforeSendFunction() : show_working('Working ...'),
            complete: hide_working(),
            type: "POST",
            url: _root_path_ + (actionUrl ? '/api/'+actionUrl : '/api/' + actionJack + '/' + actionApi)  + '?' + serialized_data,
            data: data,
            cache: false,
            dataType : 'json',
            success: function(reply_data){
                var html = '';
                if(reply_data['error']){
                    growl_error(reply_data['error']);
                    if(opt.errorFunction){
                        errorFunction();
                    }
                }
                else{
                    reply_data = reply_data['success'];
                    html = 'Item was added successfully.';
                    $.growl.notice({ message: html });
                    if(targetType=='selectBox'){
                        targetElem.find('option').removeAttr('selected');
                        targetElem.append('<option value="'+reply_data[opt.Value]+'" selected>'+reply_data[opt.Title]+'</option>');
                        targetElem.change();
                        this_dialog.dialog('close');
                        // if(callback) callback(reply_data);
                        }
                    else if(targetType=='textBox'){
                        targetElem.val(opt.value);
                        }
                    if(opt.successFunction){
                        successFunction(opt);
                        }

                    formContainer.dialog("close");

                    }
                },
            error: function(){
                var e = ["Network fail, can not connect to server."];
                growl_error(e);
                }
            });
        };

    var sanitizeFormInputs = function(){
        var data = 'internalToken='+_internalToken_;
        if (actionData) {
            for(var k in actionData){
                data += '&'+k+'='+actionData[k];
                }
            }
        sanitize_form_inputs(formContainer.find('form'));
        var serialized_data = formContainer.find('form').serialize();
        getAction(opt,data,serialized_data);
        };

    if(formUrl && actionUrl)
        init();
    else
      return null;
    };

/*Date Time Related */
function _datetimepicker(id){
    var elm = $('#'+id);
    var elm_wrapper = elm.parent();
    elm.datetimepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd-mm-yy',
        timeFormat : 'HH:mm'
        });
    if(!elm_wrapper.hasClass('date')) elm_wrapper.addClass('date');
    if(!elm_wrapper.hasClass('input-group')) elm_wrapper.addClass('input-group');
    elm_wrapper.find('.input-group-addon').remove();
    elm_wrapper.append('<span class="input-group-addon the_calendar_icon"><i class="fa fa-calendar"></i></span><span class="input-group-addon date_clear clear_date"><i class="text-danger fa fa-times"></i></span>');
    }
function _datepicker(id,args){
    var elm = $('#'+id);
    var elm_wrapper = elm.parent();
    var options = {
        changeMonth: true,
        changeYear: true,
        dateFormat : 'dd-mm-yy',
        yearRange: 'c-30:c+10'
        };
    $.extend( true, options, args );
    elm.datepicker(options);
    if(!elm_wrapper.hasClass('date')) elm_wrapper.addClass('date');
    if(!elm_wrapper.hasClass('input-group')) elm_wrapper.addClass('input-group');
    elm_wrapper.find('.input-group-addon').remove();
    elm_wrapper.append('<span class="input-group-addon the_calendar_icon"><i class="fa fa-calendar"></i></span><span class="input-group-addon date_clear clear_date"><i class="text-danger fa fa-times"></i></span>');
    }
function _timepicker(id){
    var elm = $('#'+id);
    var elm_wrapper = elm.parent();
    elm.timepicker({
        timeFormat : 'HH:mm:ss'
        });
    if(!elm_wrapper.hasClass('date')) elm_wrapper.addClass('date');
    if(!elm_wrapper.hasClass('input-group')) elm_wrapper.addClass('input-group');
    elm_wrapper.find('.input-group-addon').remove();
    elm_wrapper.append('<span class="input-group-addon the_calendar_icon"><i class="fa fa-calendar"></i></span><span class="input-group-addon date_clear clear_date"><i class="text-danger fa fa-times"></i></span>');
    }
/* ---------------- */

function initSelect2(selector){
    $(selector).not('.select2-container').select2();
    }

$(document).on('mouseenter','.nav.navbar-nav.openOnHover li',function(){
    $(this).addClass('open');
    });
$(document).on('mouseleave','.nav.navbar-nav.openOnHover li',function(){
    $(this).removeClass('open');
    });

$(document).off('click','.date_clear').on('click','.date_clear',function(){
    $(this).closest('.input-group').find('input').val('');
    });
$(document).on('click','.clear_date',function(){
    $(this).closest('.date').find('input[type="text"]').val('');
    });

function select2MatchStart(params, data) {
    params = params || '';

    if (data.toUpperCase().indexOf(params.toUpperCase()) == 0) {
        return data;
        }
    return false;
    }

function initRowSelectableTable(){
    $('.row-selectable').each(function(){
        var ths = $(this);
        ths.find('tbody tr').css('cursor' ,'pointer');
        ths.find('tbody tr').off('click').on('click',function(){
            ths.find('tbody tr').removeClass('row-selectable-selected');
            $(this).addClass('row-selectable-selected');
            if($(this).find('.row-selectable-radio').length)
                $(this).find('.row-selectable-radio').prop('checked',true);
            });
        });
    }
initRowSelectableTable();

var linkFreezer = function(elm, opt){
    var ths = $(elm);

    ths.options = {
        status: 'none',
        oldIconClasses: '',
        oldText: '',
        oldHtml: '',
        };
    ths.options = $.extend(true, ths.options, opt);

    ths.freeze = function(){
        if(ths.options.status == 'freeze') return true;

        ths.options.oldHtml = ths.html();
        ths.html('<i class="fa fa-cog fa-spin"></i>&nbsp;Loading...');
        ths.prop('disabled',true).attr('disabled',true);
        };

    ths.release = function(){
        if(ths.options.status == 'released') return true;
        ths.html(ths.options.oldHtml);
        ths.prop('disabled',false).attr('disabled',false);
        };

    return ths;
    };

//Filter Form related
function filterFormSubmit(obj, type){
    var theForm = $(obj).closest('form');
    var submitBtn = theForm.find('input[type="submit"][value="FILTER"]');
    if(type == 'auto'){
        $(theForm).submit();
        }
    else if(type == 'manual'){}
    }

$('select:not([data-filter-submit="manual"]), input:not([data-filter-submit="manual"])','.filter_form').on('change',function(e){
    var tagName = $(this).prop('tagName');
    if(tagName == 'SELECT'){
        if($(this).find('option:selected').attr('data-filter-submit') == 'manual'
            || typeof e.val === 'undefined'
            ) return false;
        if(e.originalEvent) filterFormSubmit($(this),'auto');
        else if(e.val) filterFormSubmit($(this),'auto');
        }
    else if(tagName == 'INPUT'){
        if($(this).attr('data-filter-submit') == 'manual') return false;

        filterFormSubmit($(this),'auto');
        }
    });
$('input:not([data-filter-submit="manual"])','.filter_panel').on('keypress',function(){

    });

$('.the_calendar_icon').on('click',function(){
    if($(this).closest('div').hasClass('date')){
        $(this).closest('div').find('.hasDatepicker').trigger('focus');
        }
    });

function reloadAfterAddEdit(title,message,type,waitFor,toUrl){
    title = title || 'Success';
    message = message || 'Please Wait';
    type = type || 'success';
    waitFor = waitFor || 2000;
    toUrl = toUrl || window.location.href;

    modern_alert(title,message,type);
    setTimeout(function(){
        window.location.href = toUrl;
        },waitFor);
    }

function fixDateFields(){
    $('.date').each(function(){
        if(!$(this).find('.the_calendar_icon').length)
            $(this).prepend('<a href="javascript:" class="input-group-addon the_calendar_icon"><i class="fa fa-calendar"></i></a>');
        if(!$(this).find('.clear_date').length)
            $(this).append('<a href="javascript:" class="input-group-addon clear_date"><i class="fa fa-times text-danger"></i></a>');
        });
    }
fixDateFields();

function printPdfNow(){
    var thePdf = document.getElementById('pdfFrame');
    thePdf.contentWindow.print();
    setTimeout(function(){hideLoading();}, 1000);
    }
$(document).on('click','.print-pdf',function(){
    var ths = $(this);
    var pdfUrl = ths.attr('data-pdf-url');
    if($('#pdfFrame').length) $('#pdfFrame').remove();
    showLoading();
    $('body').append('<iframe id="pdfFrame" onload="printPdfNow();" style="display:none" src="'+pdfUrl+'"></iframe>');
    });
$('.adv_select').not('.select2-offscreen').select2();
$('.autoHeightTextarea').autosize();

function advanceSelect(selector){
    $(selector).not('.select2-offscreen').each(function(i,e){
        $(e).select2();
        })
    }
function autoHeight(selector){
    $(selector).each(function(i,e){
        $(e).autosize();
        })
    }

var currency_converter = function(option){
    var config = {
        container: null,
        convertingCurrency: null,
        from_amount: null,
        to_amount: null,
        to_currency: null,
    };
    config = $.extend( true, config, option );
    var container = $(config.container);
    var from_amount = $(config.from_amount);
    var to_amount = $(config.to_amount);
    var to_currency = $(config.to_currency);

    config.convert_currency = function(ths, event){
        if(event){
            if(event.keyCode != 13) return false;
        }
        var converterContainer = $(ths).closest(config.container);
        var fromAmountElement = converterContainer.find(config.from_amount);
        var toCurrencyElement = converterContainer.find(config.to_currency);
        var toAmountElement = converterContainer.find(config.to_amount);

        var fromAmountVal = fromAmountElement.val();
        var toCurrencyVal = toCurrencyElement.val();
        var toAmountVal = toAmountElement.val();

        var thisName = '';
        var thisAmount = 0;
        var thisFrom = 'BDT';
        var thisTo = 'USD';
        var updateField = '';

        if($(ths).hasClass('to_amount')){
            thisName = 'to_amount';
            thisAmount = toAmountVal;
            thisFrom = toCurrencyVal;
            thisTo = 'BDT';
            updateField = fromAmountElement;
            show_button_overlay_working(fromAmountElement);
            show_button_overlay_working(toCurrencyElement);
        }
        else if($(ths).hasClass('to_currency')){
            thisName = 'to_currency';
            thisAmount = toAmountVal;
            thisFrom = toCurrencyVal;
            thisTo = 'BDT';
            updateField = fromAmountElement;
            show_button_overlay_working(fromAmountElement);
            show_button_overlay_working(toAmountElement);
        }
        else if($(ths).hasClass('from_amount')){
            thisName = 'from_amount';
            thisAmount = fromAmountVal;
            thisFrom = 'BDT';
            thisTo = toCurrencyVal;
            updateField = toAmountElement;
            show_button_overlay_working(toCurrencyElement);
            show_button_overlay_working(toAmountElement);
        }

        if(config.convertingCurrency){
            //convertingCurrency.abort();
            hide_button_overlay_working(fromAmountElement);
            hide_button_overlay_working(toCurrencyElement);
            hide_button_overlay_working(toAmountElement);
        }

        if(thisAmount == 0){
            updateField.val(0);
            hide_button_overlay_working(fromAmountElement);
            hide_button_overlay_working(toCurrencyElement);
            hide_button_overlay_working(toAmountElement);
            return false;
        }


        basicAjaxCall({
            beforeSend: function(){
                if(thisName == 'to_amount'){
                    show_button_overlay_working(fromAmountElement);
                    show_button_overlay_working(toCurrencyElement);
                }
                else if(thisName == 'to_currency'){
                    show_button_overlay_working(fromAmountElement);
                    show_button_overlay_working(toAmountElement);
                }
                else if(thisName == 'from_amount'){
                    show_button_overlay_working(toCurrencyElement);
                    show_button_overlay_working(toAmountElement);
                }
            },
            complete: function(){
                hide_button_overlay_working(fromAmountElement);
                hide_button_overlay_working(toCurrencyElement);
                hide_button_overlay_working(toAmountElement);
            },
            error: function(){
                var e = ["Network fail, can not connect to server."];
                growl_error(e);
            },
            url: _root_path_+'/api/dev_administration/get_converted_currency',
            data: {
            'from' : thisFrom,
                'to' : thisTo,
                'amount' : thisAmount,
        },
        success: function(reply){
            if(reply){
                if(reply.hasOwnProperty('success')){
                    updateField.val(reply.success);
                }
                else $.growl.error({ message: 'Failed to convert currency from'});
            }
        }
    })
    }

    container.find(config.from_amount+', '+config.to_amount).on('keyup', function(e){
        config.convert_currency($(this), e)});
    container.find(to_currency).on('change', function(){config.convert_currency($(this), null)});

};

var date_range_selector = function(option){
    var config = {
        container: null,
        id: null,
        label: null,
        };

    config = $.extend( true, config, option );

    var container = $(config.container);
    var field_name = config.id;
    var field_id = config.id;
    var field_specific = field_name+'_specific_date';
    var field_range_from = field_name+'_range_from';
    var field_range_to = field_name+'_range_to';

    container.append('\
        <label>'+config.label+'</label>\
        <select class="form-control" id="'+field_id+'" name="'+field_name+'">\
            <option value="" selected>Any</option>\
            <option value="today">Today</option>\
            <option value="yesterday">Yesterday</option>\
            <option value="tomorrow">Tomorrow</option>\
            <option value="upcoming">Upcoming</option>\
            <option value="earlier">Earlier</option>\
            <option value="this_month">This Month</option>\
            <option value="this_year">This Year</option>\
            <option value="next_7_days">Next 7 Days</option>\
            <option value="next_15_days">Next 15 Days</option>\
            <option value="next_30_days">Next 30 Days</option>\
            <option value="next_6_months">Next 6 Months</option>\
            <option value="next_12_months">Next 12 Months</option>\
            <option value="7">Last 7 Days</option>\
            <option value="30">Last 30 Days</option>\
            <option value="last_month">Last Month</option>\
            <option data-filter-submit="manual" value="specific_date">Specific Date</option>\
            <option data-filter-submit="manual" value="date_range">Date Range</option>\
        </select>\
        <div class="specific_date dn mt15" >\
            <label>Select Date</label>\
                <div class="input-group date">\
                    <input id="'+field_specific+'" type="text" class="form-control" name="'+field_specific+'" value="">\
                    <span class="input-group-addon the_calendar_icon"><i class="fa fa-calendar"></i></span>\
                    <span class="input-group-addon clear_date"><i class="text-danger fa fa-times"></i></span>\
                </div>\
        </div>\
        <div class="date_range mb0 mt15 dn row" >\
            <div class="col-sm-6">\
                <label>From</label>\
                <div class="input-group date">\
                    <input data-filter-submit="manual" id="'+field_range_from+'" type="text" class="form-control" name="'+field_range_from+'" value="">\
                    <span class="input-group-addon the_calendar_icon"><i class="fa fa-calendar"></i></span>\
                    <span class="input-group-addon clear_date"><i class="text-danger fa fa-times"></i></span>\
                </div>\
            </div>\
            <div class="col-sm-6">\
                <label>To</label>\
                <div class="input-group date">\
                    <input data-filter-submit="manual" id="'+field_range_to+'" type="text" class="form-control" name="'+field_range_to+'" value="">\
                    <span class="input-group-addon the_calendar_icon"><i class="fa fa-calendar"></i></span>\
                    <span class="input-group-addon clear_date"><i class="text-danger fa fa-times"></i></span>\
                </div>\
            </div>\
        </div>\
        ');

    _datepicker(field_specific);
    _datepicker(field_range_from);
    _datepicker(field_range_to);

    $('#'+field_id).on('change',function(){
        if($(this).val() == 'specific_date'){
            container.find('.date_range').addClass('dn');
            container.find('.specific_date').removeClass('dn');
            }
        else if($(this).val() == 'date_range'){
            container.find('.specific_date').addClass('dn');
            container.find('.date_range').removeClass('dn');
            }
        else{
            container.find('.specific_date').addClass('dn');
            container.find('.date_range').addClass('dn');
            }
        }).change();

    var current_val = getQueryVariable(field_name);
    var specific_val = getQueryVariable(field_specific);
    var range_from_val = getQueryVariable(field_range_from);
    var range_to_val = getQueryVariable(field_range_to);

    if(current_val) $('#'+field_name).val(current_val);
    if(specific_val) $('#'+field_specific).val(specific_val);
    if(range_from_val) $('#'+field_range_from).val(range_from_val);
    if(range_to_val) $('#'+field_range_to).val(range_to_val);

    $('#'+field_name).change();
    };

function initCharLimit(){
    if(typeof this.count === 'undefined') this.count = 0;
    var elements = $('.char_limit:not(.char_limited)');
    var totalElements = elements.length;
    for(var i = 0; i < totalElements; i++){
        var ths = $(elements[i]);
        var theID = 'char_limit_label_'+this.count+'_'+(new Date().getTime());
        var maxChar = ths.attr('data-max-char');
        ths.after('<div id="'+theID+'" class="limiter-label form-group-margin">Characters left: <span class="limiter-count"></span></div>');
        ths.limiter(maxChar, { label: '#'+theID });
        ths.addClass('char_limited');
        this.count++;
        }
    }

function initSwitcher(){
    if(typeof this.count === 'undefined') this.count = 0;
    var elements = $('.switcherElement:not(.switcherActivated)');
    var totalElements = elements.length;
    for(var i = 0; i < totalElements; i++){
        var ths = $(elements[i]);
        var inputElm = ths.find('>input');
        var onStateContent = ths.attr('data-on-state') ? ths.attr('data-on-state') : 'YES';
        var offStateContent = ths.attr('data-off-state') ? ths.attr('data-off-state') : 'NO';
        var switcherTheme = ths.attr('data-theme') ? ths.attr('data-theme') : 'square';

        inputElm.switcher({
            theme: switcherTheme,
            on_state_content: onStateContent,
            off_state_content: offStateContent
            });
        ths.addClass('switcherActivated');
        this.count++;
        };
    }

$('.linked-row-table > tbody > tr > td').on('click', function(){
    window.location.href = $(this).closest('tr').attr('data-href');
    });
/**
 * Created by Tanmay on 3/30/2015.
 */
// tiny mce code
function init_tinymce(options) {
    /*
     * selector = the selector id, such as, '#content_description'
     * external_filemanager_path
     * */
    var defaultOptions = {
        selector: '',
        theme: "modern",
        height: '260',
        theme_modern_font_sizes: ["6px,7px,8px,9px,10px,11px,12px,13px,14px,15px,16px,17px,18px,19px,20px,21px,22px,23px,24px,25px,26px,27px,28px,29px,30px,31px,32px,36px,38px,40px"],
        font_size_style_values: ["6px,7px,8px,9px,10px,11px,12px,13px,14px,15px,16px,17px,18px,19px,20px,21px,22px,23px,24px,25px,26px,27px,28px,29px,30px,31px,32px,36px,38px,40px"],
        plugins: [
            "advlist lists link textcolor colorpicker image",
            "table responsivefilemanager media autoresize wordcount spellchecker"
            ],
        spellchecker_language: 'en',
        autoresize_max_height: options.autoResizeMaxHeight ? options.autoResizeMaxHeight : 400,
        autoresize_min_height: options.autoResizeMinHeight ? options.autoResizeMinHeight : 300,
        relative_urls: true,
        browser_spellcheck : true ,
        filemanager_title:"My Files",
        filemanager_crossdomain: true,
        external_filemanager_path: '',
        image_caption: true,
        image_title: true,
        image_advtab: true,
        external_plugins: '',
        block_formats: 'Paragraph=p;Header 3=h3;Header 4=h4;Header 5=h5;Header 6=h6;Preformatted=pre',
        toolbar1: "formatselect | undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
        toolbar2: "forecolor backcolor | table | link | media responsivefilemanager image smileys | fontsizeselect | insert_featured_image",
        menubar: false,
        toolbar_items_size: 'medium',
        filemanager_access_key: "efc8c0471af3fe5aa736b1fbd6f9d802",
        file_browser_callback :true,
        object_resizing : true,
        style_formats: [
            {title: 'Image Left', selector: 'img', styles: {
                'float' : 'left',
                'margin': '0 10px 10px 0'
                }},
            {title: 'Image Right', selector: 'img', styles: {
                'float' : 'right',
                'margin': '0 0 10px 10px'
                }},
            {title: 'Image Center', selector: 'img', styles: {
                'float' : 'none',
                'display' : 'block',
                'margin': '10px auto'
                }},
            /*{ title: 'Bold text', inline: 'strong' },
            { title: 'Red text', inline: 'span', styles: { color: '#ff0000' } },
            { title: 'Red header', block: 'h1', styles: { color: '#ff0000' } },
            { title: 'Badge', inline: 'span', styles: { display: 'inline-block', border: '1px solid #2276d2', 'border-radius': '5px', padding: '2px 5px', margin: '0 2px', color: '#2276d2' } },
            { title: 'Table row 1', selector: 'tr', classes: 'tablerow1' }*/
            ],
        formats: {
            /*alignleft: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'left' },
            aligncenter: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'center' },
            alignright: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'right' },
            alignfull: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'full' },
            bold: { inline: 'span', 'classes': 'bold' },
            italic: { inline: 'span', 'classes': 'italic' },
            underline: { inline: 'span', 'classes': 'underline', exact: true },
            strikethrough: { inline: 'del' },
            customformat: { inline: 'span', styles: { color: '#00ff00', fontSize: '20px' }, attributes: { title: 'My custom format' }, classes: 'example1' },*/
            },
        //extended_valid_elements : "featuredImage",
        //custom_elements: "featuredImage",
        setup: function(editor){
            editor.addButton('insert_featured_image', {
                text: 'Insert Featured Image',
                icon: 'mce-ico mce-i-image',
                onclick: function () {
                    //var mycontent = tinyMCE.activeEditor.selection.getContent();
                    editor.insertContent('<br /><br />Featured-Image-Will-Be-Displayed-Here<br /><br />');
                    }
                });
            },
        };

    var configs = $.extend(true,defaultOptions,options);

    if(configs.toolbar2 == false) configs.toolbar2 = '';
    if(configs.public_filemanager) configs.filemanager_access_key = "b9b7d0f30c2c300dbde1d961c5b00284";
    configs.external_plugins = { "filemanager" : configs.external_filemanager_path+"plugin.min.js"};

    tinymce.init(configs);
    }

function init_mini_tinymce(options) {
    /*
     * selector = the selector id, such as, '#content_description'
     * external_filemanager_path
     * */
    tinymce.init({
        selector: options.selector,
        theme: "modern",
        height: options.height ? options.height : 100,
        elementpath: false,
        statusbar: false,
        theme_modern_font_sizes: ["6px,7px,8px,9px,10px,11px,12px,13px,14px,15px,16px,17px,18px,19px,20px,21px,22px,23px,24px,25px,26px,27px,28px,29px,30px,31px,32px,36px,38px,40px"],
        font_size_style_values: ["6px,7px,8px,9px,10px,11px,12px,13px,14px,15px,16px,17px,18px,19px,20px,21px,22px,23px,24px,25px,26px,27px,28px,29px,30px,31px,32px,36px,38px,40px"],
        plugins: [
            "advlist lists link   image ",
            " responsivefilemanager media"
        ],

        relative_urls: true,
        browser_spellcheck : true ,
        filemanager_title:"My Files",
        filemanager_crossdomain: true,
        external_filemanager_path: options.external_filemanager_path,
        image_caption: true,
        image_title: true,
        image_advtab: true,
        external_plugins: { "filemanager" : options.external_filemanager_path+"plugin.min.js"},
        image_advtab: false,
        toolbar1: options.toolbar1 ? options.toolbar1 : "bold italic | bullist numlist | link | media responsivefilemanager image",
        //toolbar2: options.toolbar2 ? options.toolbar2 : (options.toolbar2 == false ? '' : "link | media responsivefilemanager image"),
        menubar: false,
        toolbar_items_size: 'medium',
        filemanager_access_key: options.public_filemanager ? "b9b7d0f30c2c300dbde1d961c5b00284" : "efc8c0471af3fe5aa736b1fbd6f9d802",
        object_resizing : true,
        style_formats: [
            {title: 'Image Left', selector: 'img', styles: {
                'float' : 'left',
                'margin': '0 10px 10px 0'
            }},
            {title: 'Image Right', selector: 'img', styles: {
                'float' : 'right',
                'margin': '0 0 10px 10px'
            }},
            /*{ title: 'Bold text', inline: 'strong' },
             { title: 'Red text', inline: 'span', styles: { color: '#ff0000' } },
             { title: 'Red header', block: 'h1', styles: { color: '#ff0000' } },
             { title: 'Badge', inline: 'span', styles: { display: 'inline-block', border: '1px solid #2276d2', 'border-radius': '5px', padding: '2px 5px', margin: '0 2px', color: '#2276d2' } },
             { title: 'Table row 1', selector: 'tr', classes: 'tablerow1' }*/
        ],
        formats: {
            /*alignleft: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'left' },
             aligncenter: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'center' },
             alignright: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'right' },
             alignfull: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'full' },
             bold: { inline: 'span', 'classes': 'bold' },
             italic: { inline: 'span', 'classes': 'italic' },
             underline: { inline: 'span', 'classes': 'underline', exact: true },
             strikethrough: { inline: 'del' },
             customformat: { inline: 'span', styles: { color: '#00ff00', fontSize: '20px' }, attributes: { title: 'My custom format' }, classes: 'example1' },*/
        }
    });
}



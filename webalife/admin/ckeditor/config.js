/*
 Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
    config.toolbar = 'Pages';
    config.skin = 'v2';
    config.toolbar_Pages =
        [
            { name:'document', items:[ 'Source', '-', 'Save', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates' ] },
            { name:'clipboard', items:[ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
            { name:'editing', items:[ 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt' ] },
            '/',
            { name:'basicstyles', items:[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
            { name:'paragraph', items:[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
                '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
            { name:'links', items:[ 'Link', 'Unlink', 'Anchor' ] },
            { name:'insert', items:[ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
            '/',
            { name:'styles', items:[ 'Styles', 'Format', 'Font', 'FontSize' ] },
            { name:'colors', items:[ 'TextColor', 'BGColor' ] },
            { name:'tools', items:[ 'Maximize', 'ShowBlocks', '-', 'About' ] }
        ];

    config.toolbar_DevLab =
        [
            { name:'document', items:[ 'Source', '-', 'Save', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates' ] },
            { name:'clipboard', items:[ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
            { name:'editing', items:[ 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt' ] },
            '/',
            { name:'basicstyles', items:[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
            { name:'paragraph', items:[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
                '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
            { name:'links', items:[ 'Link', 'Unlink', 'Anchor' ] },
            { name:'insert', items:[ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar'] },
            '/',
            { name:'styles', items:[ 'Styles', 'Format', 'Font', 'FontSize' ] },
            { name:'colors', items:[ 'TextColor', 'BGColor' ] },
            { name:'tools', items:[ 'Maximize', 'ShowBlocks', '-', 'About' ] }
        ];

    config.filebrowserBrowseUrl = '/mks/admin/ckfinder/ckfinder.html';
    config.filebrowserImageBrowseUrl = '/mks/admin/ckfinder/ckfinder.html?Type=Images';
    config.filebrowserFlashBrowseUrl = '/mks/admin/ckfinder/ckfinder.html?Type=Flash';
    config.filebrowserUploadUrl = '/mks/admin/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
    config.filebrowserImageUploadUrl = '/mks/admin/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
    config.filebrowserFlashUploadUrl = '/mks/admin/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
};

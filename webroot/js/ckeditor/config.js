/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For the complete reference:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    config.format_tags = 'p;h2;h3;h4';
    config.language = 'de';
    config.allowedContent = true; // allow iframe and co...
    config.entities = false;

    config.extraPlugins = 'showblocks,justify,format';

    config.startupOutlineBlocks = true;
    config.forcePasteAsPlainText = true;

    config.filebrowserBrowseUrl = '/js/elfinder/elfinder.html';
    config.filebrowserImageBrowseUrl = '/js/elfinder/elfinder.html';

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
        { name: 'editing',     groups: [ 'find', 'selection' ] },
        { name: 'links' },
        { name: 'insert' },
        { name: 'forms' },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'list', 'blocks', 'align', 'bidi' ] },
        { name: 'colors' },
        { name: 'styles' },
        { name: 'tools' },
        { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'others' },
    ];

    // Remove some buttons, provided by the standard plugins, which we don't
    // need to have in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript,Strike,Paste,PasteText,Table,HorizontalRule,SpecialChar,Maximize,ShowBlocks,Blockquote,Styles';


    // Make dialogs simpler.
    //config.removeDialogTabs = 'image:advanced;link:advanced';
};

CKEDITOR.timestamp = 'v4.13.1'; // change this string if version is updated
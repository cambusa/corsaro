/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    
    config.uiColor= '#CCCCCC';
	config.language = 'it';
	config.toolbar = 'MyToolbar';

	config.toolbar_MyToolbar =
	[
		{ name: 'document', items : [ 'Source', 'NewPage', 'PasteText', "Print", 'Undo', 'Redo' ] },
		{ name: 'basicstyles', items : [ 'Bold', 'Italic', 'Underline', 'Subscript','Superscript', 'RemoveFormat' ] },
		{ name: 'styles', items : [ 'Styles', 'Format' ] },
        { name: 'colors', items : [ 'TextColor' , 'BGColor' ] },
        '/',
		{ name: 'paragraph', items : [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock',  
		                               'NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote' ] },
		{ name: 'insert', items : [ 'Image', 'Table' , 'CreateDiv', 'Iframe', 'HorizontalRule', 'Smiley', 'SpecialChar'] },
		{ name: 'links', items : [ 'Link', 'Unlink' ] },
		{ name: 'editing', items : [ 'Find', 'Replace', 'SelectAll', "ShowBlocks", 'Maximize'] }
	];

    config.resize_enabled = false;
};

/**
 * plugin.js
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/*global tinymce:true */

tinymce.PluginManager.add('pre', function(editor) {
	editor.addCommand('pre', function() {
		editor.execCommand('mceInsertContent', false, '<pre class="prettyprint"><code></code></pre>');
	});

	editor.addButton('pre', {
		icon: 'code',
		tooltip: 'pre',
		cmd: 'pre'
	});

	editor.addMenuItem('pre', {
		icon: 'code',
		text: 'code',
		cmd: 'pre',
		context: 'insert'
	});
});

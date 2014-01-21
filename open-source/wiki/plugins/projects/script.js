/* DOKUWIKI:include codemirror-compressed.js */

var modes = new Object();
modes['bash'] = 'text/x-sh';
modes['c'] = 'text/x-csrc';
modes['css'] = 'text/css';
modes['cpp'] = 'text/x-c++src';
modes['csharp'] = 'text/x-sharp';
modes['html'] = 'text/html';
modes['java'] = 'text/x-java';
modes['javascript'] = 'text/x-javascript';
modes['latex'] = 'text/x-stex';
modes['php'] = 'application/x-httpd-php';
modes['r'] = 'text/x-rsrc';
modes['ruby'] = 'text/x-ruby';
modes['pascal'] = 'text/x-pascal';
modes['perl'] = 'text/x-perl';
modes['python'] = 'text/x-python';
modes['xml'] = 'application/xml';
modes['plain'] = 'text/plain';

jQuery(function(){
    var $editor = jQuery('#wiki__text');
    if ($editor.length == 0) return;
    var $lang = jQuery('input[name$="projects_wiki_lang"]');
    if ($lang.length == 0) return;
    var lang = $lang.attr('value');
    if (modes[lang] == undefined) lang = 'plain';
    var $toolbar = jQuery('#tool__bar');
    if ($toolbar.length > 0) $toolbar.remove();
    var ss = document.styleSheets[0];
    if (ss.insertRule)
        ss.insertRule('div.dokuwiki pre { border: none; padding: 0px; margin: 0 0 0 0; marginBottom: 0px; font-size: 100%;}', ss.cssRules.length);
    else if (ss.addRule) 
        ss.addRule('div.dokuwiki pre { border: none; padding: 0px; margin: 0 0 0 0; marginBottom: 0px; font-size: 100%;}', ss.cssRules.length);
    var cm = CodeMirror.fromTextArea($editor[0], { 
        lineNumbers: true,
        matchBrackets: true,
        mode: modes[lang],
        lineWrapping: true
    });
});
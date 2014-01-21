<?php
/**
 * Options for the Formular Plugin
 */
 
$conf['AllowInclude'] = 0;   // Is including PHP-Script allowed? Should be set to 0 on public sites!
$conf['mailPath'] = 'data/formplugin/';   //This is the path where the Mail-Scripts are stored.
$conf['selectPage'] = 'formplugin:select';   //This is the wiki-page where SelectBox names are defined. 
$conf['mailSubject'] = 'Message - formular plugin';   //This is the Subject of the mails send by the plugin
$conf['mailFrom'] = 'Formular-Plugin <formular-plugin@dokuwiki>';   //This is the sender of the mail. For example “user@domain” or "User Name <user@domain>".
$conf['DebugMode'] = 0; // Shall Debug-Information be filed-out?


//Setup VIM: ex: et ts=2 enc=utf-8 :
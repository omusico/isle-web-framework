<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the skype plugin
 *
 * @author    Zahno Silvan <zaswiki@gmail.com>
 */
$meta['function'] = array('multichoice',
                                       '_choices' => array('chat', 'add', 'call', 'userinfo', 'voicemail', 'sendfile'));
$meta['size'] = array('multichoice',
                                 '_choices' => array('big', 'small'));
$meta['content']  = array('multichoice',
                                 '_choices' => array('icon+text', 'icon'));
$meta['style'] = array('multichoice',
                                 '_choices' => array('balloon', 'classic'));
// vim:ts=4:sw=4:et:enc=utf-8:

<?php
/**
 * English language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca>
 */

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Admin News...'; 

$lang['btn_prune'] = 'Auswahl l&ouml;schen';
$lang['btn_restore'] = 'R&uuml;ckg&auml;ngig';
$lang['btn_review'] = 'L&ouml;schen überprüfen';
$lang['btn_confirm'] = 'L&ouml;schen best&auml;tigen';
$lang['invalid'] = 'Falsche Eingabe!';
$lang['btn_generate'] = 'Aktualisiere Feed';
$lang['menu'] = 'News plugin Administration';


$lang['instructions'] ='<p>Markieren Sie das "L&ouml;schen" K&auml;stchen neben dem Feed Namen, um diesen zum L&ouml;schen auszuw&auml;hlen.
 Danach klicken Sie auf den "Auswahl L&ouml;schen" Knopf &uuml;ber der Tabelle. Sie k&ouml;nnen mehrere Feeds auf einmal l&ouml;schen, sofern
 Sie die K&auml;stchen mehrerer Feeds ausgew&auml;hlt haben. Die Feed Tabelle wird automatisch aktualisiert, um Ihnen eine Ergebnisvorschau
 anzuzeigen. Sollten Sie einen Feed unabsichtlich ausgew&auml;hlt haben, so k&ouml;nnen sie mit dem Knopf "R&uuml;ckg&auml;ngig" Ihre Auswahl
 r&uuml;ckg&auml;ngig machen und es wird keine Ver&auml;nderung an der Feed Datenbank vorgenommen. Erst wenn Sie den Knopf "L&ouml;schen best&auml;tigen" 
 dr&uuml;cken wird die Datenbank der Feeds aktualisiert und die Ausgew&auml;hlten nun endg&uuml;ltig gel&ouml;scht.</p>
<p>Das L&ouml;schen der Feeds aktualisiert nur den momentanen Zustand in der Datenbank. Um beim n&auml;chsten Update erneute Eintr&auml;ge der
 gel&ouml;schten Feeds zu verhindern, m&uuml;ssen Sie nun noch die Plugin Syntax von den Feed generierenden seiten l&ouml;schen. Sofern Sie die
 Syntax auf der Seite belassen, wird sie nach der Aktualisierung des DokuWiki Cache wieder als Feed erscheinen.
</p>
 
';

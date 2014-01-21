<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Jacques Bouthier <jacques@jack31.net>
 */
$lang['menu']                  = 'Administration du plugin de News';
$lang['btn_prune']             = 'Supprimer les sélections';
$lang['btn_restore']           = 'Restaurer';
$lang['btn_review']            = 'Reviser les suppressions';
$lang['btn_confirm']           = 'Confirmer les suppressions';
$lang['invalid']               = 'Entrée invalide détectée !';
$lang['btn_generate']          = 'Diffuser';
$lang['instructions']          = '<p> Pour supprimer un flux, cliquer sur la case à cocher "Supprimer" en dessous du nom du flux. Ensuite cliquer &#160;
sur "Supprimer les sélections" en haut de l\'écran. Vous pouvez supprimez plusieurs lux à la fois en une seule opération en cliquant plusieurs cases à cocher.  La table listant les flux sera mise à jour pour réfléter mais les suppressions ne seront effectives qu\'après avoir cliqué sur le bouton "Confirmer les Suppressions", moment auquel la database sera mise à jour. Cliquer sur "Restaurer" avant de confirmer remettra la table dans son état initial.</p> 
<p> 
Une suppression supprime seulement le statut actuel de la database. Vous devez également supprimer la syntaxe du plugin des pages qui &#160;
 ont généré le flux. Si l\'écriture du plugin reste sur une page, le flux de cette page sera regénéré la prochaine fois que le cache de Dokuwiki est mis à jour.&#160;  </p> &#160;&#160;   
';

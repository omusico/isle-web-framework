<?php
####################################################################################################
## File : items.queries.php
## Author : Nils Laumaillé
## Description : File contains queries for ajax
##
## DON'T CHANGE !!!
##
####################################################################################################

session_start();
require_once('../includes/language/'.$_SESSION['user_language'].'.php');
include('../includes/settings.php');
require_once('../includes/include.php');
header("Content-type: text/html; charset=".$k['charset']);
include('main.functions.php');

$allowed_tags = '<b><i><sup><sub><em><strong><u><br><a><strike><ul><blockquote><blockquote><img><li><h1><h2><h3><h4><h5><ol><small><font>';

//Connect to mysql server
require_once("class.database.php");
$db = new Database($server, $user, $pass, $database, $pre);
$db->connect();

// Construction de la requête en fonction du type de valeur
if ( isset($_POST['type']) ){
    switch($_POST['type'])
    {
        ### CASE ####
        ### creating a new ITEM
        case "new_item":
            //check if element doesn't already exist
            $create_new_item = true;
            if ( isset($_SESSION['settings']['duplicate_item']) && $_SESSION['settings']['duplicate_item'] == 0 ){
                $data = $db->fetch_row("SELECT COUNT(*) FROM ".$pre."items WHERE label = '".mysql_real_escape_string(stripslashes(($_POST['label'])))."' AND inactif=0");
                if ( $data[0] != 0 ){
                    echo '$("#div_formulaire_saisi").dialog("open");';
                    echo 'document.getElementById("new_show_error").innerHTML = "'.$txt['error_item_exists'].'";';
                    echo '$("#new_show_error").show();';
                    $create_new_item = false;
                }
            }

            if( $create_new_item == true) {
                echo '$("#new_show_error").hide();';

                //Manage specific characters (&, +)
                $pw_received = string_utf8_decode($_POST['pw']); //password_replacement($_POST['pw']);

                $resticted_to = $_POST['restricted_to'];
                //encrypt PW
                if ($_POST['salt_key_set']==1 && isset($_POST['salt_key_set']) && $_POST['is_pf']==1 && isset($_POST['is_pf'])){
                    $pw = encrypt($pw_received,mysql_real_escape_string(stripslashes($_SESSION['my_sk'])));
                    $resticted_to = $_SESSION['user_id'];
                }else{
                	$pw = encrypt($pw_received);
                }


                //ADD item
                $new_id = $db->query_insert(
                    'items',
                    array(
                        'label' => stripslashes($_POST['label']),
                        'description' => addslashes($_POST['desc']),
                        'pw' => $pw,
                        'url' => mysql_real_escape_string(stripslashes(($_POST['url']))),
                        'id_tree' => $_POST['categorie'],
                        'login' => stripslashes($_POST['login']),
                        'inactif' => '0',
                        'restricted_to' => $resticted_to,
	                    'perso' => ( $_POST['salt_key_set']==1 && isset($_POST['salt_key_set']) && $_POST['if_pf']==1 && isset($_POST['if_pf'])) ? '1' : '0',
	                    'anyone_can_modify' => $_POST['anyone_can_modify'] == "on" ? '1' : '0'
                    )
                );

                //log
                $db->query_insert(
                    'log_items',
                    array(
                        'id_item' => $new_id,
                        'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                        'id_user' => $_SESSION['user_id'],
                        'action' => 'at_creation'
                    )
                );

                //Add tags
                $tags = explode(' ',$_POST['tags']);
                foreach($tags as $tag){
                    if ( !empty($tag) )
                        $db->query_insert(
                            'tags',
                            array(
                                'item_id' => $new_id,
                                'tag' => strtolower($tag)
                            )
                        );
                }

                // Check if any files have been added
                if ( !empty($_POST['random_id_from_files']) ){
                    $sql = "SELECT id
                            FROM ".$pre."files
                            WHERE id_item=".$_POST['random_id_from_files'];
                    $rows = $db->fetch_all_array($sql);
                    foreach ($rows as $reccord){
                        //update item_id in files table
                        $db->query_update(
                            'files',
                            array(
                                'id_item' => $new_id
                            ),
                            "id='".$reccord['id']."'"
                        );
                    }
                }

                //Update CACHE table
                UpdateCacheTable("add_value",$new_id);

                //Announce by email?
                if ( $_POST['annonce'] == 1 ){
                    require_once("class.phpmailer.php");
                    //envoyer email
                    $destinataire= explode(';',$_POST['diffusion']);
                    foreach($destinataire as $mail_destinataire){
                        //envoyer ay destinataire
                        $mail = new PHPMailer();
                        $mail->SetLanguage("en","../includes/libraries/phpmailer/language");
                        $mail->IsSMTP();                                   // send via SMTP
                        $mail->Host     = $smtp_server; // SMTP servers
                        $mail->SMTPAuth = $smtp_auth;     // turn on SMTP authentication
                        $mail->Username = $smtp_auth_username;  // SMTP username
                        $mail->Password = $smtp_auth_password; // SMTP password
                        $mail->From     = $email_from;
                        $mail->FromName = $email_from_name;
                        $mail->AddAddress($mail_destinataire);     //Destinataire
                        $mail->WordWrap = 80;                              // set word wrap
                        $mail->IsHTML(true);                               // send as HTML
                        $mail->Subject  =  $txt['email_subject'];
                        $mail->AltBody     =  $txt['email_altbody_1']." ".mysql_real_escape_string(stripslashes(($_POST['label'])))." ".$txt['email_altbody_2'];
                        $corpsDeMail = $txt['email_body_1'].mysql_real_escape_string(stripslashes(($_POST['label']))).$txt['email_body_2'].
                        $_SESSION['settings']['cpassman_url']."/index.php?page=items&group=".$_POST['categorie']."&id=".$new_id.$txt['email_body_3'];
                        $mail->Body  =  $corpsDeMail;
                        $mail->Send();
                    }
                }
                //Refresh page
                echo '$("#random_id").val("");';
                echo 'window.location.href = "index.php?page=items&group='.$_POST['categorie'].'&id='.$new_id.'";';

            }
        break;

    #############
    ### CASE ####
    ### update an ITEM
        case "update_item":
            //init
            $reload_page = false;

            //Get existing values
            $data = $db->query_first("SELECT * FROM ".$pre."items WHERE id=".$_POST['id']);
            /*
            //decrypt
            require_once '../includes/libraries/crypt/aes.class.php';     // AES PHP implementation
            require_once '../includes/libraries/crypt/aesctr.class.php';  // AES Counter Mode implementation
            $pw = urldecode(AesCtr::decrypt($_POST['pw'], $_SESSION['cle_session'], 256));
            $login = urldecode(AesCtr::decrypt($_POST['login'], $_SESSION['cle_session'], 256));
            $label = urldecode(AesCtr::decrypt($_POST['label'], $_SESSION['cle_session'], 256));
            */

        	$pw = string_utf8_decode($_POST['pw']);
            $resticted_to = $_POST['restricted_to'];

            //encrypt PW
        	if ($_POST['salt_key_set']==1 && isset($_POST['salt_key_set']) && $_POST['if_pf']==1 && isset($_POST['if_pf'])){
        		$pw = encrypt($pw,mysql_real_escape_string(stripslashes($_SESSION['my_sk'])));
        		$resticted_to = $_SESSION['user_id'];
        	}else
        		$pw = encrypt($pw);

            //---Manage tags
                //deleting existing tags for this item
                $db->query("DELETE FROM ".$pre."tags WHERE item_id = '".$_POST['id']."'");

                //Add new tags
                $tags = explode(' ',$_POST['tags']);
                foreach($tags as $tag){
                    if ( !empty($tag) )
                        $db->query_insert(
                            'tags',
                            array(
                                'item_id' => $_POST['id'],
                                'tag' => strtolower($tag)
                            )
                        );
                }

            //update item
            $db->query_update(
                'items',
                array(
                    'label' => stripslashes($_POST['label']),
                    'description' => $_POST['description'],
                    'pw' => $pw,
                    'login' => stripslashes($_POST['login']),
                    'url' => mysql_real_escape_string(stripslashes(($_POST['url']))),
                    'id_tree' => mysql_real_escape_string($_POST['categorie']),
                    'restricted_to' => $resticted_to,
                    'anyone_can_modify' => $_POST['anyone_can_modify'] == "on" ? '1' : '0'
                ),
                "id='".$_POST['id']."'"
            );

            //Update CACHE table
            UpdateCacheTable("update_value",$_POST['id']);

            //Log all modifications done
                ## LABEL ##
                if ( $data['label'] != $_POST['label'] )
                    $db->query_insert(
                        'log_items',
                        array(
                            'id_item' => $_POST['id'],
                            'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                            'id_user' => $_SESSION['user_id'],
                            'action' => 'at_modification',
                            'raison' => 'at_label : '.$data['label'].' => '.stripslashes($_POST['label'])
                        )
                    );
                ## LOGIN ##
                if ( $data['login'] != $_POST['login'] )
                    $db->query_insert(
                        'log_items',
                        array(
                            'id_item' => $_POST['id'],
                            'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                            'id_user' => $_SESSION['user_id'],
                            'action' => 'at_modification',
                            'raison' => 'at_login : '.$data['login'].' => '.stripslashes($_POST['login'])
                        )
                    );
                ## URL ##
                if ( $data['url'] != $_POST['url'] )
                    $db->query_insert(
                        'log_items',
                        array(
                            'id_item' => $_POST['id'],
                            'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                            'id_user' => $_SESSION['user_id'],
                            'action' => 'at_modification',
                            'raison' => 'at_url : '.$data['url'].' => '.mysql_real_escape_string(stripslashes(($_POST['url'])))
                        )
                    );
                ## DESCRIPTION ##
                if ( $data['description'] != $_POST['description'] )
                    $db->query_insert(
                        'log_items',
                        array(
                            'id_item' => $_POST['id'],
                            'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                            'id_user' => $_SESSION['user_id'],
                            'action' => 'at_modification',
                            'raison' => 'at_description'
                        )
                    );
                ## FOLDER ##
                if ( $data['id_tree'] != mysql_real_escape_string($_POST['categorie']) ){
                    $db->query_insert(
                        'log_items',
                        array(
                            'id_item' => $_POST['id'],
                            'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                            'id_user' => $_SESSION['user_id'],
                            'action' => 'at_modification',
                            'raison' => 'at_category : '.$data['id_tree'].' => '.mysql_real_escape_string(stripslashes(($_POST['categorie'])))
                        )
                    );
                    //ask for page reloading
                    $reload_page = true;
                }
                ## PASSWORD ##
                if ( $data['pw'] != $pw ){
                    $db->query_insert(
                        'log_items',
                        array(
                            'id_item' => $_POST['id'],
                            'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                            'id_user' => $_SESSION['user_id'],
                            'action' => 'at_modification',
                            'raison' => 'at_pw'
                        )
                    );
                }

            //Reload new values
            $data_item = $db->query_first("
                SELECT *
                FROM ".$pre."items AS i
                INNER JOIN ".$pre."log_items AS l ON (l.id_item = i.id)
                WHERE i.id=".$_POST['id']."
                    AND l.action = 'at_creation'"
            );

            //Reload History
            $history = "";
            $rows = $db->fetch_all_array("
                SELECT l.date AS date, l.action AS action, l.raison AS raison, u.login AS login
                FROM ".$pre."log_items AS l
                LEFT JOIN ".$pre."users AS u ON (l.id_user=u.id)
                WHERE id_item=".$_POST['id']);
            foreach($rows as $reccord){
                $reason = explode(':',$reccord['raison']);
                if ( empty($history) )
                    $history = date("d/m/Y H:i:s",$reccord['date'])." - ". $reccord['login'] ." - ".$txt[$reccord['action']]." - ".(!empty($reccord['raison']) ? (count($reason) > 1 ? $txt[trim($reason[0])].' : '.$reason[1] : $txt[trim($reason[0])] ):'');
                else
                    $history .= "<br />".date("d/m/Y H:i:s",$reccord['date'])." - ". $reccord['login'] ." - ".$txt[$reccord['action']]." - ".(!empty($reccord['raison']) ? (count($reason) > 1 ? $txt[trim($reason[0])].' : '.$reason[1] : $txt[trim($reason[0])] ):'');
            }

            //Get list of restriction
            $liste = explode(";",$data_item['restricted_to']);
            $liste_restriction = "";
            foreach($liste as $elem){
                if ( !empty($elem) ){
                    $data2 = $db->fetch_row("SELECT login FROM ".$pre."users WHERE id=".$elem);
                    $liste_restriction .= $data2[0].";";
                }
            }

            //decrypt PW
            if ( empty($_POST['salt_key']) ){
                $pw = decrypt($data_item['pw']);
            }else{
                $pw = decrypt($data_item['pw'],mysql_real_escape_string(stripslashes($_SESSION['my_sk'])));
            }
            $pw = CleanString($pw);

            // Prepare files listing
                $files = $files_edit = "";
                // launch query
                $rows = $db->fetch_all_array(
                    "SELECT *
                    FROM ".$pre."files
                    WHERE id_item=".$_POST['id']
                );
                foreach ($rows as $reccord){
                    // get icon image depending on file format
                    $icon_image = file_format_image($reccord['extension']);
                    // If file is an image, then prepare lightbox. If not image, then prepare donwload
                    if ( in_array($reccord['extension'],$k['image_file_ext']) )
                        $files .=   '<img src=\'includes/images/'.$icon_image.'\' /><a class=\'image_dialog\' href=\''.$_SESSION['settings']['cpassman_url'].'/upload/'.$reccord['file'].'\' title=\''.$reccord['name'].'\'>'.$reccord['name'].'</a><br />';
                    else
                        $files .=   '<img src=\'includes/images/'.$icon_image.'\' /><a href=\'sources/downloadFile.php?name='.urlencode($reccord['name']).'&path=../upload/'.$reccord['file'].'&size='.$reccord['size'].'&type='.urlencode($reccord['type']).'\' target=\'_blank\'>'.$reccord['name'].'</a><br />';
                    // Prepare list of files for edit dialogbox
                    $files_edit .= '<span id=\'span_edit_file_'.$reccord['id'].'\'><img src=\'includes/images/'.$icon_image.'\' /><img src=\'includes/images/document--minus.png\' style=\'cursor:pointer;\'  onclick=\'delete_attached_file(\"'.$reccord['id'].'\")\' />&nbsp;'.$reccord['name']."</span><br />";
                }


            echo '$(\'#id_label\').text("'.addslashes($data_item['label']).'");';
            echo '$(\'#id_pw\').text("'.mysql_real_escape_string($pw).'");';
            echo 'document.getElementById(\'id_url\').innerHTML = "'.$data_item['url'].'";';
            echo 'document.getElementById(\'id_desc\').innerHTML = "'.stripslashes(str_replace('\n','<br>',strip_tags(mysql_real_escape_string($_POST['description']),$allowed_tags))).'";';
            echo 'document.getElementById(\'id_login\').innerHTML = "'.addslashes($data_item['login']).'";';
            echo 'document.getElementById(\'id_info\').innerHTML = "'.mysql_real_escape_string($history).'";';
            echo 'document.getElementById(\'id_restricted_to\').innerHTML = "'.$liste_restriction.'";';
            echo 'document.getElementById(\'id_tags\').innerHTML = "'.trim($_POST['tags']).'";';
            echo 'document.getElementById(\'item_edit_list_files\').innerHTML = "'.$files_edit.'";';
            echo 'document.getElementById(\'id_files\').innerHTML = "'.$files.'";';

            //Fill in hidden fields
            echo 'document.getElementById(\'hid_label\').value = "'.addslashes($data_item['label']).'";';
            echo 'document.getElementById(\'hid_pw\').value = "'.mysql_real_escape_string($pw).'";';
            echo 'document.getElementById(\'hid_url\').value = "'.$data_item['url'].'";';
            echo 'document.getElementById(\'hid_desc\').value = "'.str_replace('<br />','\n',stripslashes(str_replace('\n','<br />',mysql_real_escape_string($_POST['description'])))).'";';
            echo 'document.getElementById(\'hid_login\').value = "'.addslashes($data_item['login']).'";';
            echo 'document.getElementById(\'id_categorie\').value = "'.$data_item['id_tree'].'";';
            echo 'document.getElementById(\'id_item\').value = "'.$data_item['id'].'";';
            echo 'document.getElementById(\'hid_restricted_to\').value = "'.$data_item['restricted_to'].'";';
            echo 'document.getElementById(\'hid_tags\').value = "'.trim($_POST['tags']).'";';
            echo 'document.getElementById(\'hid_files\').value = "'.$files.'";';

            // function calling image lightbox when clicking on link
            echo '$(\'a.image_dialog\').click(function(event){event.preventDefault();PreviewImage($(this).attr(\'href\'),$(this).attr(\'title\'));}); ';

            //Send email
            if ( !empty($_POST['diffusion']) ){
                require_once("class.phpmailer.php");
                $destinataire= explode(';',$_POST['diffusion']);
                foreach($destinataire as $mail_destinataire){
                    //envoyer ay destinataire
                    $mail = new PHPMailer();
                    $mail->SetLanguage("en","../includes/libraries/phpmailer/language");
                    $mail->IsSMTP();                                   // send via SMTP
                    $mail->Host     = $smtp_server; // SMTP servers
                    $mail->SMTPAuth = $smtp_auth;     // turn on SMTP authentication
                    $mail->Username = $smtp_auth_username;  // SMTP username
                    $mail->Password = $smtp_auth_password; // SMTP password
                    $mail->From     = $email_from;
                    $mail->FromName = $email_from_name;
                    $mail->AddAddress($mail_destinataire);     //Destinataire
                    $mail->WordWrap = 80;                              // set word wrap
                    $mail->IsHTML(true);                               // send as HTML
                    $mail->Subject  =  "Mise à jour d'un mot de passe";
                    $mail->AltBody     =  "Le mot de passe de ".mysql_real_escape_string(stripslashes(($_POST['label'])))." a été mis à jour.";
                    $corpsDeMail = "Bonjour,<br><br>Le mot de passe de '" .mysql_real_escape_string(stripslashes(($_POST['label'])))."' a été mis à jour.<br /><br />".
                    "Vous pouvez le consulter <a href=\"".$_SESSION['settings']['cpassman_url']."/index.php?page=items&group=".$_POST['categorie']."&id=".$_POST['id']."\">ICI</a><br /><br />".
                    "A bientot";
                    $mail->Body  =  $corpsDeMail;
                    $mail->Send();
                }
            }
            //reload if category has changed
            if ( $reload_page == true )
                echo 'window.location.href = "index.php?page=items&group='.$data_item['id_tree'].'&id='.$data_item['id'].'";';
        break;

        #############
        ### CASE ####
        ### Display informations of selected item
        case "show_details_item":
            //Change the class of this selected item
            echo 'var tmp = \'fileclass\'+document.getElementById(\'selected_items\').value;';
            echo 'if ( tmp != "fileclass") document.getElementById(tmp).className = "file";';
            echo 'document.getElementById(\'selected_items\').value = "'.$_POST['id'].'";';

            //Get all informations for this item
            $sql = "SELECT *
                    FROM ".$pre."items AS i
                    INNER JOIN ".$pre."log_items AS l ON (l.id_item = i.id)
                    WHERE i.id=".$_POST['id']."
                    AND l.action = 'at_creation'";
            $data_item = $db->query_first($sql);

            //Get all tags for this item
            $tags = "";
            $sql = "SELECT tag
                    FROM ".$pre."tags
                    WHERE item_id=".$_POST['id'];
            $rows = $db->fetch_all_array($sql);
            foreach ($rows as $reccord)
                $tags .= $reccord['tag']." ";

            //check that actual user can access this item
            $access = explode(';',$data_item['id_tree']);
            $restriction_active = true;
            $restricted_to = explode(';',$data_item['restricted_to']);
            if ( in_array($_SESSION['user_id'],$restricted_to) ) $restriction_active = false;
            if ( empty($data_item['restricted_to']) ) $restriction_active = false;

            //Uncrypt PW
            if ( isset($_POST['salt_key_required']) && $_POST['salt_key_required'] == 1 && isset($_POST['salt_key_set']) && $_POST['salt_key_set'] == 1){
	            $pw = decrypt($data_item['pw'],mysql_real_escape_string(stripslashes($_SESSION['my_sk'])));
	            echo '$("#edit_item_salt_key").show();';
            }else{
                $pw = decrypt($data_item['pw']);
                echo '$("#edit_item_salt_key").hide();';//echo "=>".$pw;
            }


            //check if item is expired
            if ( isset($_POST['expired_item']) && $_POST['expired_item'] == 0 ) $item_is_expired = false;
            else $item_is_expired = true;


            //Check if actual USER can see this ITEM
            if ((
            	( in_array($access[0],$_SESSION['groupes_visibles']) || $_SESSION['is_admin'] == 1 )
                &&  ( $data_item['perso']==0 || ($data_item['perso']==1 && $data_item['id_user'] == $_SESSION['user_id'] ) )
                && $restriction_active == false
            	)||
            	($data_item['anyone_can_modify']==1 && ( in_array($access[0],$_SESSION['groupes_visibles']) || $_SESSION['is_admin'] == 1 ))
            ){
                //Display menu icon for deleting if user is allowed
                if ($data_item['id_user'] == $_SESSION['user_id'] || $_SESSION['is_admin'] == 1 || ($_SESSION['user_gestionnaire'] == 1 && $_SESSION['settings']['manager_edit'] == 1) || $data_item['anyone_can_modify']==1){
                    echo '$(\'#menu_button_edit_item,#menu_button_del_item\').removeAttr(\'disabled\');';
                    $user_is_allowed_to_modify = true;
                }else{
                    echo '$(\'#menu_button_edit_item,#menu_button_del_item\').attr(\'disabled\',\'disabled\');';
                    $user_is_allowed_to_modify = false;
                }

                //GET Audit trail
                $historique = "";
                $rows = $db->fetch_all_array("
                    SELECT l.date AS date, l.action AS action, l.raison AS raison, u.login AS login
                    FROM ".$pre."log_items AS l
                    LEFT JOIN ".$pre."users AS u ON (l.id_user=u.id)
                    WHERE id_item=".$_POST['id']
                );
                foreach ( $rows as $reccord ){
                    $reason = explode(':',$reccord['raison']);
                    if ( empty($historique) )
                        $historique = date("d/m/Y H:i:s",$reccord['date'])." - ". $reccord['login'] ." - ".$txt[$reccord['action']]." - ".(!empty($reccord['raison']) ? (count($reason) > 1 ? $txt[trim($reason[0])].' : '.$reason[1] : $txt[trim($reason[0])] ):'');
                    else
                        $historique .= "<br />".date("d/m/Y H:i:s",$reccord['date'])." - ". $reccord['login']  ." - ".$txt[$reccord['action']]." - ".(!empty($reccord['raison']) ? (count($reason) > 1 ? $txt[trim($reason[0])].' : '.$reason[1] : $txt[trim($reason[0])] ):'');
                }

                //Get restriction list
                $liste = explode(";",$data_item['restricted_to']);
                $liste_restriction = "";
                foreach($liste as $elem){
                    if ( !empty($elem) ){
                        $data2 = $db->fetch_row("SELECT login FROM ".$pre."users WHERE id=".$elem);
                        $liste_restriction .= $data2[0].";";
                    }
                }

                //Prepare DIalogBox data
                if ( $item_is_expired == false ) {
                    echo 'document.getElementById(\'item_details_ok\').style.display = "";';
                    echo 'document.getElementById(\'item_details_expired\').style.display = "none";';
                }else if ( $user_is_allowed_to_modify == true && $item_is_expired == true ){
                    echo 'document.getElementById(\'item_details_ok\').style.display = "";';
                    echo 'document.getElementById(\'item_details_expired\').style.display = "";';
                }else{
                    echo 'document.getElementById(\'item_details_ok\').style.display = "none";';
                    echo 'document.getElementById(\'item_details_expired\').style.display = "";';
                }
                echo 'document.getElementById(\'item_details_nok\').style.display="none";';
                echo 'document.getElementById(\'fileclass'.$_POST['id'].'\').className = "fileselected";';

                echo '$(\'#id_label\').text("'.addslashes($data_item['label']).'");';
                echo '$(\'#id_pw\').text(\''.preg_replace ( "/\S/", " * ",CleanString(addslashes($pw))).'\');';
                if ( substr($data_item['url'],0,7) == "http://" || substr($data_item['url'],0,8) == "https://" ) $lien = stripslashes(str_replace('\n','',mysql_real_escape_string($data_item['url'])));
                else $lien = "http://".(str_replace('\n','',mysql_real_escape_string($data_item['url'])));
                echo 'document.getElementById(\'id_url\').innerHTML = "'.stripslashes(str_replace('\n','',mysql_real_escape_string($data_item['url']))).'',!empty($data_item['url'])?'&nbsp;<a href=\''. $lien.'\' target=\'_blank\'><img src=\'includes/images/arrow_skip.png\' style=\'border:0px;\' title=\'Ouvrir la page\'></a>':'','";';
                echo 'document.getElementById(\'id_desc\').innerHTML = "'.stripslashes(str_replace('\n', '<br />', mysql_real_escape_string(strip_tags($data_item['description'],$allowed_tags)))).'";';
                echo 'document.getElementById(\'id_login\').innerHTML = "'.addslashes($data_item['login']).'";';
                if ( $data_item['perso'] == 0 ) $perso = "Non"; else $perso = "Oui";
                echo 'document.getElementById(\'id_info\').innerHTML = "'.CleanString(addslashes($historique)).'";';
                echo 'document.getElementById(\'id_restricted_to\').innerHTML = "'.$liste_restriction.'";';
                echo 'document.getElementById(\'id_restricted_to\').innerHTML = "'.$liste_restriction.'";';
            	echo 'document.getElementById(\'id_tags\').innerHTML = "'.trim($tags).'";';

                //renseigner les champs masqués
                echo 'document.getElementById(\'hid_label\').value = "'.addslashes($data_item['label']).'";';
                echo 'document.getElementById(\'hid_pw\').value = \''.CleanString(addslashes($pw)).'\';';
                echo 'document.getElementById(\'hid_url\').value = "'.stripslashes(str_replace('\n','',mysql_real_escape_string(($data_item['url'])))).'";';
                echo 'document.getElementById(\'hid_desc\').value = "'.str_replace('<br />','\n',stripslashes(str_replace('\n','<br />',mysql_real_escape_string($data_item['description'])))).'";';
                echo 'document.getElementById(\'hid_login\').value = "'.addslashes($data_item['login']).'";';
                echo 'document.getElementById(\'id_categorie\').value = "'.$data_item['id_tree'].'";';
                echo 'document.getElementById(\'id_item\').value = "'.$data_item['id'].'";';
                echo 'document.getElementById(\'hid_restricted_to\').value = "'.$data_item['restricted_to'].'";';
            	echo 'document.getElementById(\'hid_tags\').value = "'.trim($tags).'";';
            	echo 'document.getElementById(\'hid_anyone_can_modify\').value = "'.$data_item['anyone_can_modify'].'";';

                //Prepare clipboard copies
                if ( $pw != "" ) {
                    echo 'var clip = new ZeroClipboard.Client(); clip.setText( "'.CleanString(addslashes($pw)).'" ); clip.addEventListener( "onMouseDown", function(client) {$("#message_box").html("'.$txt['pw_copied_clipboard'].'").show().fadeOut(1000);});clip.glue(\'menu_button_copy_pw\');';   //
                }
                if ( $data_item['login'] != "" ) {
                    echo 'var clip = new ZeroClipboard.Client(); clip.setText( "'.addslashes($data_item['login']).'" );clip.glue( "menu_button_copy_login" );clip.addEventListener( "onMouseDown", function(client) {$("#message_box").html("'.$txt['login_copied_clipboard'].'").show().fadeOut(1000);});';
                }

                //prepare link to clipboard
                $link = $_SESSION['settings']['cpassman_url'].'/index.php?page=items&group='.$data_item['id_tree'].'&id='.$data_item['id'];
                echo 'var clip = new ZeroClipboard.Client();clip.setText( "'.$link.'" );clip.addEventListener( "onMouseDown", function(client) {$("#message_box").html("'.$txt['url_copied'].'").show().fadeOut(1000);});clip.glue( "menu_button_copy_link" );'; //

                //Add this item to the latests list
                if ( isset($_SESSION['latest_items']) && isset($_SESSION['settings']['max_latest_items']) && !in_array($data_item['id'],$_SESSION['latest_items']) ){
                    if ( count($_SESSION['latest_items']) >= $_SESSION['settings']['max_latest_items'] ){
                        array_pop($_SESSION['latest_items']);   //delete last items
                    }
                    array_unshift($_SESSION['latest_items'],$data_item['id']);
                    //update DB
                    $db->query_update(
                        "users",
                        array(
                            'latest_items' => implode(';',$_SESSION['latest_items'])
                        ),
                        "id=".$_SESSION['user_id']
                    );
                }

                // Prepare files listing
                    $files = $files_edit = "";
                    // launch query
                    $rows = $db->fetch_all_array(
                        "SELECT *
                        FROM ".$pre."files
                        WHERE id_item=".$_POST['id']
                    );
                    foreach ($rows as $reccord){
                        // get icon image depending on file format
                        $icon_image = file_format_image($reccord['extension']);
                        // If file is an image, then prepare lightbox. If not image, then prepare donwload
                        if ( in_array($reccord['extension'],$k['image_file_ext']) )
                            $files .=   '<img src=\'includes/images/'.$icon_image.'\' /><a class=\'image_dialog\' href=\''.$_SESSION['settings']['cpassman_url'].'/upload/'.$reccord['file'].'\' title=\''.$reccord['name'].'\'>'.$reccord['name'].'</a><br />';
                        else
                            $files .=   '<img src=\'includes/images/'.$icon_image.'\' /><a href=\'sources/downloadFile.php?name='.urlencode($reccord['name']).'&path=../upload/'.$reccord['file'].'&size='.$reccord['size'].'&type='.urlencode($reccord['type']).'\'>'.$reccord['name'].'</a><br />';
                        // Prepare list of files for edit dialogbox
                        $files_edit .= '<span id=\'span_edit_file_'.$reccord['id'].'\'><img src=\'includes/images/'.$icon_image.'\' /><img src=\'includes/images/document--minus.png\' style=\'cursor:pointer;\'  onclick=\'delete_attached_file(\"'.$reccord['id'].'\")\' />&nbsp;'.$reccord['name']."</span><br />";
                    }
                    //display lists
                    echo 'document.getElementById("item_edit_list_files").innerHTML = "'.$files_edit.'";';
                    echo 'document.getElementById("id_files").innerHTML = "'.$files.'";';
                    // function calling image lightbox when clicking on link
                    echo '$(\'a.image_dialog\').click(function(event){event.preventDefault();PreviewImage($(this).attr(\'href\'),$(this).attr(\'title\'));}); ';

                //Refresh last seen items
                    $text = $txt['last_items_title'].":&nbsp;";
                    $_SESSION['latest_items_tab'][] = "";
                    foreach($_SESSION['latest_items'] as $item){
                        if ( !empty($item) ){
                            $data = $db->query_first("SELECT label,id_tree FROM ".$pre."items WHERE id = ".$item);
                            $_SESSION['latest_items_tab'][$item] = array(
                                'label'=>addslashes($data['label']),
                                'url'=>'index.php?page=items&amp;group='.$data['id_tree'].'&amp;id='.$item
                            );
                            $text .= '<span style=\"cursor:pointer;\" onclick=\"javascript:window.location.href = \''.$_SESSION['latest_items_tab'][$item]['url'].'\'\"><img src=\"includes/images/tag_small.png\" />'.$_SESSION['latest_items_tab'][$item]['label'].'</span>&nbsp;';
                        }
                    }
                    echo 'document.getElementById("div_last_items").innerHTML = "'.$text.'";';

                    //enable copy buttons
                    echo '$("#menu_button_show_pw, #menu_button_copy_pw, #menu_button_copy_login, #menu_button_copy_link, #menu_button_copy_item").removeAttr(\'disabled\');';

                    //disable add bookmark if alread bookmarked
                    if ( in_array($_POST['id'],$_SESSION['favourites']) ) {
                        echo '$("#menu_button_add_fav").attr(\'disabled\',\'disabled\');';
                        echo '$("#menu_button_del_fav").removeAttr(\'disabled\');';
                    }else{
                        echo '$("#menu_button_add_fav").removeAttr(\'disabled\');';
                        echo '$("#menu_button_del_fav").attr(\'disabled\',\'disabled\');';
                    }
            }else{
                echo 'document.getElementById(\'item_details_nok\').style.display="";';
                echo 'document.getElementById(\'item_details_ok\').style.display = "none";';
                echo 'document.getElementById(\'item_details_expired\').style.display="none";';
                echo '$(\'#menu_button_edit_item, #menu_button_del_item, #menu_button_copy_item, #menu_button_add_fav, #menu_button_del_fav, #menu_button_show_pw, #menu_button_copy_pw, #menu_button_copy_login, #menu_button_copy_link\').attr(\'disabled\',\'disabled\');';
            }
        break;

        #############
        ### CASE ####
        ### Generate a password
        case "pw_generate":
            $key = "";
            //call class
            include('../includes/libraries/pwgen/pwgen.class.php');
            $pwgen = new PWGen();

            // Set pw size
            $pwgen->setLength($_POST['size']);
            // Include at least one number in the password
            $pwgen->setNumerals( ($_POST['num'] == "true")? true : false);
            // Include at least one capital letter in the password
            $pwgen->setCapitalize( ($_POST['maj'] == "true")? true : false);
            // Include at least one symbol in the password
            $pwgen->setSymbols( ($_POST['symb'] == "true")? true : false);
            // Complete random, hard to memorize password
            if ($_POST['secure'] == "true"){
                $pwgen->setSecure(true);
                $pwgen->setSymbols(true);
                $pwgen->setCapitalize(true);
                $pwgen->setNumerals(true);
            }else
                $pwgen->setSecure(false);

            // Generate KEY
            $key = $pwgen->generate();

            if ( isset($_POST['fixed_elem']) && $_POST['fixed_elem'] == 1 ) $myElem = $_POST['elem'];
            else $myElem = $_POST['elem'].'pw1';

            echo 'document.getElementById(\''.$myElem.'\').value = "'.addslashes($key).'";';

            if ( !isset($_POST['fixed_elem']) )
                echo 'runPassword(document.getElementById(\''.$myElem.'\').value, \''.$_POST['elem'].'mypassword\');';

            echo '$("#'.$_POST['elem'].'pw_wait").hide();';
        break;

        #############
        ### CASE ####
        ### Delete an item
        case "del_item":
            //delete item consists in disabling it
            $db->query_update(
                "items",
                array(
                    'inactif' => '1',
                ),
                "id = ".$_POST['id']
            );
            //log
            $db->query_insert(
                "log_items",
                array(
                    'id_item' => $_POST['id'],
                    'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                    'id_user' => $_SESSION['user_id'],
                    'action' => 'at_delete'
                )
            );

            //Update CACHE table
            UpdateCacheTable("delete_value",$_POST['id']);

            //Reload
            echo 'window.location.href = "index.php?page=items&group='.$_POST['groupe'].'";';
        break;

        #############
        ### CASE ####
        ### Create a new Group
        case "new_rep":
            //Check if title doesn't contains html codes
            if (preg_match_all("|<[^>]+>(.*)</[^>]+>|U",$_POST['title'],$out)) {
                echo '$("#div_ajout_rep").dialog("open");';
                echo 'document.getElementById("new_rep_show_error").innerHTML = "'.$txt['error_html_codes'].'";';
                echo '$("#new_rep_show_error").show();';
            }

            //Check if duplicate folders name are allowed
            $create_new_folder = true;
            if ( isset($_SESSION['settings']['duplicate_folder']) && $_SESSION['settings']['duplicate_folder'] == 0 ){
                $data = $db->fetch_row("SELECT COUNT(*) FROM ".$pre."nested_tree WHERE title = '".mysql_real_escape_string(stripslashes(($_POST['title'])))."'");
                if ( $data[0] != 0 ){
                    echo '$("#div_ajout_rep").dialog("open");';
                    echo 'document.getElementById("new_rep_show_error").innerHTML = "'.$txt['error_group_exist'].'";';
                    echo '$("#new_rep_show_error").show();';
                    $create_new_folder = false;
                }
            }

            if ( $create_new_folder == true ){
                //Check if group is a personnal folder
                $data = $db->fetch_row("SELECT personal_folder FROM ".$pre."nested_tree WHERE id = ".$_POST['groupe']);
                $new_id=$db->query_insert(
                    "nested_tree",
                    array(
                        'parent_id' => $_POST['groupe'],
                        'title' => mysql_real_escape_string(stripslashes(($_POST['title']))),
                        'personal_folder' => $data[0]
                    )
                );

                //Add complexity
                $db->query_insert(
                    "misc",
                    array(
                        'type' => 'complex',
                        'intitule' => $new_id,
                        'valeur' => $_POST['complexite']
                    )
                );

            	//Add this folder to the role the creator has
                foreach(array_filter(explode(';', $_POST['role_id'])) as $role_id) {
                    $db->query_insert(
                        "roles_values",
                        array(
                            'folder_id' => $new_id,
                            'role_id' =>  $role_id
                        )
                    );
                }

                require_once('NestedTree.class.php');
                $tree = new NestedTree($pre.'nested_tree', 'id', 'parent_id', 'title');
                $tree->rebuild();

                //Get user's rights
                IdentificationDesDroits($_SESSION['groupes_visibles'].';'.$new_id,$_SESSION['groupes_interdits'],$_SESSION['is_admin'],$_SESSION['fonction_id'],true);

                //Reload page
                echo 'window.location.href = "index.php?page=items";';
            }
        break;

        #############
        ### CASE ####
        ### Update a Group
        case "update_rep":
            //Check if title doesn't contains html codes
            if (preg_match_all("|<[^>]+>(.*)</[^>]+>|U",$_POST['title'],$out)) $html_codes = true;
            else $html_codes = false;

            if ( $html_codes == true ) {
                echo '$("#div_editer_rep").dialog("open");';
                echo 'document.getElementById("edit_rep_show_error").innerHTML = "'.$txt['error_html_codes'].'";';
                echo '$("#edit_rep_show_error").show();';
            }else{
                //update Folders table
                $db->query_update(
                    "nested_tree",
                    array(
                        'title' => mysql_real_escape_string(stripslashes(($_POST['title'])))
                    ),
                    'id='.$_POST['groupe']
                );

                //update complixity value
                $db->query_update(
                    "misc",
                    array(
                        'valeur' => $_POST['complexite']
                    ),
                    'intitule = "'.$_POST['groupe'].'" AND type = "complex"'
                );

                //rebuild fuild tree folder
                require_once('NestedTree.class.php');
                $tree = new NestedTree($pre.'nested_tree', 'id', 'parent_id', 'title');
                $tree->rebuild();

                //reload page
                echo 'window.location.href = "index.php?page=items";';
            }
        break;

        #############
        ### CASE ####
        ### Delete a Group
        case "delete_rep":

            //Build tree
            require_once ("NestedTree.class.php");
            $tree = new NestedTree($pre.'nested_tree', 'id', 'parent_id', 'title');

            // this will delete all sub folders and items associated
            // Get through each subfolder
            $folders = $tree->getDescendants($_POST['groupe'],true);
            foreach($folders as $folder){
                //delete folder
                $db->query("DELETE FROM ".$pre."nested_tree WHERE id = ".$folder->id);

                //delete items & logs
                $items = $db->fetch_all_array("SELECT id FROM ".$pre."items WHERE id_tree='".$folder->id."'");
                foreach( $items as $item ) {
                    //Delete item
                    $db->query("DELETE FROM ".$pre."items WHERE id = ".$item['id']);
                    //log
                    $db->query("DELETE FROM ".$pre."log_items WHERE id_item = ".$item['id']);
                }
            }
            echo 'window.location.href = "index.php?page=items";';
        break;

        #############
        ### CASE ####
        ### Store hierarchic position of Group
        case 'save_position':
            require_once ("NestedTree.class.php");
            $db->query_update(
                "nested_tree",
                array(
                    'parent_id' => $_POST['destination']
                ),
                'id = '.$_POST['source']
            );
            $tree = new NestedTree($pre.'nested_tree', 'id', 'parent_id', 'title');
            $tree->rebuild();
        break;

        #############
        ### CASE ####
        ### List items of a group
        case 'lister_items_groupe':
            $arbo_html = "";
            $background_color = "#FFFFFF";

            //préparer l'arborescence
            require_once ("NestedTree.class.php");
            $tree = new NestedTree($pre.'nested_tree', 'id', 'parent_id', 'title');
            $arbo = $tree->getPath($_POST['id'], true);
            foreach($arbo as $elem){
                if ( $elem->title == $_SESSION['user_id'] && $elem->nlevel == 1 ) $elem->title = $_SESSION['login'];
                $arbo_html .= $elem->title." > ";
            }
            //check if items exist
            $data_count = $db->fetch_row("SELECT COUNT(*) FROM ".$pre."items WHERE inactif = 0");
            if ( $data_count[0] > 0 ){
                //init variables
                $init_personal_folder = false;
                $expired_item = false;

                //List all ITEMS
                $html = '<ul class="liste_items">';
                $rows = $db->fetch_all_array("
                    SELECT i.id AS id, i.restricted_to AS restricted_to, i.perso AS perso, i.label AS label, i.description AS description, i.pw AS pw, i.login AS login, i.anyone_can_modify AS anyone_can_modify,
                        l.date AS date,
                        n.renewal_period AS renewal_period
                    FROM ".$pre."items AS i
                    INNER JOIN ".$pre."nested_tree AS n ON (i.id_tree = n.id)
                    INNER JOIN ".$pre."log_items AS l ON (i.id = l.id_item)
                    WHERE i.inactif = 0
                    AND i.id_tree=".$_POST['id']."
                    AND (l.action = 'at_creation' OR (l.action = 'at_modification' AND l.raison LIKE 'at_pw :%'))
                    ORDER BY i.label ASC, l.date DESC");
                $id_managed = '';
                $i = 0;
                $items_id_list = array();
                foreach( $rows as $reccord ) {
                    //exclude all results except the first one returned by query
                    if ( empty($id_managed) || $id_managed != $reccord['id'] ){

                        //Get Expiration date
                        $expiration_flag = '';
                        $expired_item = false;
                        if ( $_SESSION['settings']['activate_expiration'] == 1 ){
                            $expiration_flag = '<img src="includes/images/flag-green.png">';
                            if ( $reccord['renewal_period']> 0 && ($reccord['date'] + ($reccord['renewal_period'] * $k['one_month_seconds'])) < time() ){
                                $expiration_flag = '<img src="includes/images/flag-red.png">';
                                $expired_item = true;
                            }
                        }
                        //list of restricted users
                        $restricted_users_array = explode(';',$reccord['restricted_to']);
                        $item_pw = "";
                        $item_login = "";
                        $display_item = $need_sk = false;

                        //Case where item is in own personal folder
                        if ( in_array($_POST['id'],$_SESSION['personal_visible_groups']) && $reccord['perso'] == 1 ){	// && !empty($reccord['restricted_to'])
                            $perso = '<img src="includes/images/tag-small-alert.png">';
                            echo '$("#recherche_group_pf").val("1");';
                            $action = 'AfficherDetailsItem(\''.$reccord['id'].'\',\'1\',\''.$expired_item.'\')';
                            $display_item = $need_sk = true;
                        }else
                        //CAse where item is restricted to a group of users included user
                        if ( !empty($reccord['restricted_to']) && in_array($_SESSION['user_id'],$restricted_users_array) ){
                            $perso = '<img src="includes/images/tag-small-yellow.png">';
                            echo 'document.getElementById("recherche_group_pf").value = "0";';
                            $action = 'AfficherDetailsItem(\''.$reccord['id'].'\',\'0\',\''.$expired_item.'\')';
                            $display_item = true;
                        }else
                        //CAse where item is restricted to a group of users included user
                        if ( $reccord['perso'] == 1 || (!empty($reccord['restricted_to']) && !in_array($_SESSION['user_id'],$restricted_users_array)) ){
                            $perso = '<img src="includes/images/tag-small-red.png">';
                            $action = 'AfficherDetailsItem(\''.$reccord['id'].'\',\'0\',\''.$expired_item.'\')';
                            //reinit in case of not personal group
                            if ( $init_personal_folder == false ){
                                echo 'document.getElementById("recherche_group_pf").value = "";';
                                $init_personal_folder = true;
                            }
                            //
                            if ( !empty($reccord['restricted_to']) && in_array($_SESSION['user_id'],$restricted_users_array) ) $display_item = true;
                        }
                        //Case where item can be seen by user
                        else{
                            $perso = '<img src="includes/images/tag-small-green.png">';
                            $action = 'AfficherDetailsItem(\''.$reccord['id'].'\',\'0\',\''.$expired_item.'\')';
                            $display_item = true;
                            //reinit in case of not personal group
                            if ( $init_personal_folder == false ){
                                echo 'document.getElementById("recherche_group_pf").value = "";';
                                $init_personal_folder = true;
                            }
                        }

                        //define background color for the line
                        if ( $background_color == "#FFFFFF" ) $background_color = "#F3F3F3";
                        else $background_color = "#FFFFFF";

                        // Prepare full line
                        $html .= '<li class="item" style="background-color:'.$background_color.';">'.$expiration_flag.''.$perso.'&nbsp;<a id="fileclass'.$reccord['id'].'" class="file" onclick="'.$action.'">'.stripslashes($reccord['label']);
                        if (!empty($reccord['description']) )
                            $html .= '&nbsp;<font size=2px>['.strip_tags(stripslashes(substr(CleanString($reccord['description']),0,30))).']</font>';
                        $html .= '</a>';

                        // display quick icon shortcuts ?
                    	$item_login = '<img src="includes/images/mini_user_disable.png" id="icon_login_'.$reccord['id'].'" />';
                    	$item_pw = '<img src="includes/images/mini_lock_disable.png" id="icon_pw_'.$reccord['id'].'" />';
                    	if ($display_item == true) {
                    		if (!empty($reccord['login'])) {
                    			$item_login = '<img src="includes/images/mini_user_enable.png" id="icon_login_'.$reccord['id'].'" title="'.$txt['item_menu_copy_login'].'" />';
                    		}
                    		if (!empty($reccord['pw'])) {
                    			$item_pw = '<img src="includes/images/mini_lock_enable.png" id="icon_pw_'.$reccord['id'].'" title="'.$txt['item_menu_copy_pw'].'" />';
                    		}
                    	}

                    	//mini icon for collab
                    	if (isset($_SESSION['settings']['anyone_can_modify']) && $_SESSION['settings']['anyone_can_modify'] == 1) {
                    		if ($reccord['anyone_can_modify'] == 1) {
                    			$item_collab = '&nbsp;<img src="includes/images/mini_collab_enable.png" title="'.$txt['item_menu_collab_enable'].'" />';
                    		}else{
                    			$item_collab = '&nbsp;<img src="includes/images/mini_collab_disable.png" title="'.$txt['item_menu_collab_disable'].'" />';
                    		}
                    	}else{
                    		$item_collab = "";
                    	}


                    	//Continue line construction
                    	$html .= '<span style="float:right;margin:2px 10px 0px 0px;">'.$item_login.'&nbsp;'.$item_pw;

                    	// Prepare make Favorite small icon
                    	$html .= '&nbsp;<span id="quick_icon_fav_'.$reccord['id'].'" title="Manage Favorite">';
                    	if (in_array($reccord['id'], $_SESSION['favourites'])) {
                    		$html .= '<img src="includes/images/mini_star_enable.png" onclick="ActionOnQuickIcon('.$reccord['id'].',0)" />';
                    	}else {
                    		$html .= '<img src="includes/images/mini_star_disable.png"" onclick="ActionOnQuickIcon('.$reccord['id'].',1)" />';
                    	}

                        $html .= $item_collab.'</span></span></li>';

                        // increment array for icons shortcuts
                    	if ($need_sk == true && isset($_SESSION['my_sk'])) {
                    		$pw = decrypt($reccord['pw'],mysql_real_escape_string(stripslashes($_SESSION['my_sk'])));
                    	}else{
                    		$pw = decrypt($reccord['pw']);
                    	}
                        array_push($items_id_list,array($reccord['id'],$pw,$reccord['login'],$display_item));

                        $i ++;
                    }
                    $id_managed = $reccord['id'];

                }
                $html .= '</ul>';
                echo 'document.getElementById(\'liste_des_items\').style.display = "";';
                echo 'document.getElementById(\'liste_des_items\').innerHTML = "'.addslashes($html).'";';
                echo 'document.getElementById(\'arborescence\').innerHTML = "'.addslashes(substr($arbo_html,0,strlen($arbo_html)-3)).'";';
                echo 'document.getElementById(\'selected_items\').value = "";';
                echo 'document.getElementById(\'hid_cat\').value = "'.$_POST['id'].'";';

                // Build clipboard for pw
                foreach($items_id_list as $cb_item){
                	if (!empty($cb_item[1]) && $cb_item[3] == 1 && !empty($cb_item[3])){
                		echo 'var clip = new ZeroClipboard.Client(); clip.setText( "'.CleanString(addslashes($cb_item[1])).'" ); clip.addEventListener( "onMouseDown", function(client) {$("#message_box").html("'.$txt['pw_copied_clipboard'].'").show().fadeOut(1000);});clip.glue(\'icon_pw_'.$cb_item[0].'\');';
                	}else {
                		echo 'var clip = new ZeroClipboard.Client(); clip.setText(""); clip.glue(\'icon_pw_'.$cb_item[0].'\');';
                	}
                	if (!empty($cb_item[2]) && $cb_item[3] == 1 && !empty($cb_item[3])) {
                		echo 'var clip = new ZeroClipboard.Client(); clip.setText( "'.addslashes($cb_item[2]).'" ); clip.addEventListener( "onMouseDown", function(client) {$("#message_box").html("'.$txt['login_copied_clipboard'].'").show().fadeOut(1000);});clip.glue(\'icon_login_'.$cb_item[0].'\');';
                	}else {
                		echo 'var clip = new ZeroClipboard.Client(); clip.setText(""); clip.glue(\'icon_login_'.$cb_item[0].'\');';
                	}
                }

                RecupDroitCreationSansComplexite($_POST['id']);
            }else{
                echo '$("#liste_des_items").css("display", "");';
                echo '$("#liste_des_items").html("");';
                echo '$("#arborescence").html("'.addslashes(substr($arbo_html,0,strlen($arbo_html)-3)).'");';
                echo '$("#selected_items").val("");';
                echo '$("#hid_cat").val("'.$_POST['id'].'");';
            }

            //Identify of it is a personal folder
            if (in_array($_POST['id'],$_SESSION['personal_visible_groups'])){
                echo '$("#recherche_group_pf").val("1");';
            }else{
                echo '$("#recherche_group_pf").val("");';
            }


        break;

        #############
        ### CASE ####
        ### Get complexity level of a group
        case "recup_complex":
            $data = $db->fetch_row("SELECT valeur FROM ".$pre."misc WHERE type='complex' AND intitule = '".$_POST['groupe']."'");
            echo 'document.getElementById("complexite_groupe").value = "'.$data[0].'";';

            //aficher la complexité attendue
            if ( $_POST['edit']==1 ) {
                $div = "edit_complex_attendue";
            }else{
                $div = "complex_attendue";
            }
            echo 'document.getElementById("'.$div.'").innerHTML = "<b>', @((!empty($data[0]) || $data[0] == 0) ? $mdp_complexite[$data[0]][1] : $txt['not_defined']), '</b>";';

            //afficher la visibilité
            $visibilite = "";
            if ( !empty($data_pf[0]) ){
                $visibilite = $_SESSION['login'];
            }else{
            	$rows = $db->fetch_all_array("
								SELECT t.title
								FROM ".$pre."roles_values AS v
								INNER JOIN ".$pre."roles_title AS t ON (v.role_id = t.id)
								WHERE v.folder_id = '".$_POST['groupe']."'");
            	foreach ($rows as $reccord){
            		if ( empty($visibilite) ) $visibilite = $reccord['title'];
            		else $visibilite .= " - ".$reccord['title'];
            	}
            }
            if ( $_POST['edit']==1 ) $div = "edit_afficher_visibilite"; else $div = "afficher_visibilite";
            if ( empty($visibilite) ) $visibilite = $txt['admin_error_no_visibility'];
            echo 'document.getElementById("'.$div.'").innerHTML = "<img src=\'includes/images/users.png\'>&nbsp;<b>'.$visibilite.'</b>";';

            RecupDroitCreationSansComplexite($_POST['groupe']);
        break;

        #############
        ### CASE ####
        ### DELETE attached file from an item
        case "delete_attached_file":
            //Get some info before deleting
            $data = $db->fetch_row("SELECT name,id_item,file FROM ".$pre."files WHERE id = '".$_POST['file_id']."'");
            if ( !empty($data[1]) ){

                //Delete from FILES table
                $db->query("DELETE FROM ".$pre."files WHERE id = '".$_POST['file_id']."'");

                //Update the log
                $db->query_insert(
                    'log_items',
                    array(
                        'id_item' => $data[1],
                        'date' => mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('y')),
                        'id_user' => $_SESSION['user_id'],
                        'action' => 'at_modification',
                        'raison' => 'at_del_file : '. $data[0]
                    )
                );

                //Strike through file
                echo '$("#span_edit_file_'.$_POST['file_id'].'").css("textDecoration", "line-through");';

                //Delete file from server
                @unlink("../upload/".$data[2]);
            }
        break;

        #############
        ### CASE ####
        ### REBUILD the description editor
        case "rebuild_description_textarea":
            if ( isset($_SESSION['settings']['richtext']) && $_SESSION['settings']['richtext'] == 1 ){
            	if ( $_POST['id'] == "desc" ){
            		echo '$("#desc").ckeditor({toolbar :[["Bold", "Italic", "Strike", "-", "NumberedList", "BulletedList", "-", "Link","Unlink","-","RemoveFormat"]], height: 100,language: "'. $k['langs'][$_SESSION['user_language']].'"});';
            	}else if ( $_POST['id'] == "edit_desc" ){
            		echo 'CKEDITOR.replace("edit_desc",{toolbar :[["Bold", "Italic", "Strike", "-", "NumberedList", "BulletedList", "-", "Link","Unlink","-","RemoveFormat"]], height: 100,language: "'. $k['langs'][$_SESSION['user_language']].'"});';
            	}
            }

        	//Multselect
        	echo '$("#edit_restricted_to_list").multiselect({selectedList: 7, minWidth: 430, height: 145, checkAllText: "'.$txt['check_all_text'].'", uncheckAllText: "'.$txt['uncheck_all_text'].'",noneSelectedText: "'.$txt['none_selected_text'].'"});';

            //Display popup
            if ( $_POST['id'] == "edit_desc" )
                echo '$("#div_formulaire_edition_item").dialog("open");';
            else
                echo '$("#div_formulaire_saisi").dialog("open");';
        break;


        #############
        ### CASE ####
        ### Get password for an ITEM
        case "copy_to_clipboard":
            //Get all informations for this item
            $sql = "SELECT pw
                    FROM ".$pre."items
                    WHERE id=".$_POST['item_id'];
            $data_item = $db->query_first($sql);

            //Uncrypt PW
            $pw = decrypt($data_item['pw']);

            //Display clipboard flash elemnt
            echo 'var clip = new ZeroClipboard.Client();clip.setText( "'.addslashes($pw).'" );clip.addEventListener( "onMouseDown", function(client) {$("#message_box").html("'.$txt['pw_copied_clipboard'].'").show().fadeOut(1000);}); clip.glue(\'icon_cp_pw_'.$_POST['icon_id'].'\'); ';
            echo '$("#clipboard_loaded_'.$_POST['icon_id'].'").val("true");';

        	break;


    	#############
    	### CASE ####
    	### Clear HTML tags
    	case "clear_html_tags":
    		//Get information for this item
    		$sql = "SELECT description
                    FROM ".$pre."items
                    WHERE id=".$_POST['id_item'];
    		$data_item = $db->query_first($sql);

    		//Clean up the string
    		echo '$("#edit_desc").val("'.stripslashes(str_replace('\n','\\\n',mysql_real_escape_string(strip_tags($data_item['description'])))).'");';

    		break;

    	/*
    	   * FUNCTION
    	   * Launch an action when clicking on a quick icon
    	   * $action = 0 => Make not favorite
    	   * $action = 1 => Make favorite
    	*/
		case "action_on_quick_icon":
			if ($_POST['action'] == 1) {
				//Add new favourite
				array_push($_SESSION['favourites'], $_POST['id']);
				$db->query_update(
				"users",
				array(
				    'favourites' => implode(';', $_SESSION['favourites'])
				),
				'id = '.$_SESSION['user_id']
				);

				//Update SESSION with this new favourite
				$data = $db->query("SELECT label,id_tree FROM ".$pre."items WHERE id = ".$_POST['id']);
				$_SESSION['favourites_tab'][$_POST['id']] = array(
		            'label'=>$data['label'],
		            'url'=>'index.php?page=items&amp;group='.$data['id_tree'].'&amp;id='.$_POST['id']
		        );
			}else if ($_POST['action'] == 0) {
				//delete from session
				foreach ($_SESSION['favourites'] as $key => $value){
					if ($_SESSION['favourites'][$key] == $_POST['id']){
						unset($_SESSION['favourites'][$key]);
						break;
					}
				}

				//delete from DB
				$db->query("UPDATE ".$pre."users SET favourites = '".implode(';', $_SESSION['favourites'])."' WHERE id = '".$_SESSION['user_id']."'");
				//refresh session fav list
				foreach ($_SESSION['favourites_tab'] as $key => $value){
					if ($key == $_POST['id']){
						unset($_SESSION['favourites_tab'][$key]);
						break;
					}
				}
			}
		break;
    }
}

if ( isset($_POST['type']) ){
    //Hide the ajax loader image
    echo 'document.getElementById(\'div_loading\').style.display = "none";';
}


// Build the QUERY in case of GET
if ( isset($_GET['type']) ){
    switch($_GET['type'])
    {
        #############
        ### CASE ####
        ### Autocomplet for TAGS
        case "autocomplete_tags":
            //Get a list off all existing TAGS
            $rows = $db->fetch_all_array("SELECT tag FROM ".$pre."tags GROUP BY tag");
            foreach ($rows as $reccord ){
                echo $reccord['tag']."|".$reccord['tag']."\n";
            }
        break;
    }
}


/*
* FUNCTION
* Identify if this group authorize creation of item without the complexit level reached
*/
function RecupDroitCreationSansComplexite($groupe){
    global $db, $pre;
    $data = $db->fetch_row("SELECT bloquer_creation,bloquer_modification,personal_folder FROM ".$pre."nested_tree WHERE id = '".$groupe."'");

    //Check if it's in a personal folder. If yes, then force complexity overhead.
    if ( $data[2] == 1 ){
        echo 'document.getElementById("bloquer_modification_complexite").value = "1";';
        echo 'document.getElementById("bloquer_creation_complexite").value = "1";';
    }else{
        echo 'document.getElementById("bloquer_creation_complexite").value = "'.$data[0].'";';
        echo 'document.getElementById("bloquer_modification_complexite").value = "'.$data[1].'";';
    }
}

/*
   * FUNCTION
* permits to identify what icon to display depending on file extension
*/
function file_format_image($ext){
	global $k;
	if ( in_array($ext,$k['office_file_ext']) ) $image = "document-office.png";
	else if ( $ext == "pdf" ) $image = "document-pdf.png";
	else if ( in_array($ext,$k['image_file_ext']) ) $image = "document-image.png";
	else if ( $ext == "txt" ) $image = "document-txt.png";
	else  $image = "document.png";
	return $image;
}

/*
   * FUNCTION
   * permits to remplace some specific characters in password
*/
function password_replacement($pw){
	$pw_patterns = array('/ETCOMMERCIAL/','/SIGNEPLUS/');
	$pw_remplacements = array('&','+');
	return preg_replace($pw_patterns,$pw_remplacements,$pw);
}


?>

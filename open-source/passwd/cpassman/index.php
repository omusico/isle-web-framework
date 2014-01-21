<?php
    session_start();

	/* DEFINE WHAT LANGUAGE TO USE
	   * By default language is ENGLISH
	*/
	if (!isset($_SESSION['user_language']) && !isset($_COOKIE['user_language'])) {
		if (isset($_POST['language'])) {
			$_SESSION['user_language'] = filter_var($_POST['language'], FILTER_SANITIZE_STRING);
			$_COOKIE['user_language'] = filter_var($_POST['language'], FILTER_SANITIZE_STRING);
		}else {
			$_SESSION['user_language'] = "english";
			$_COOKIE['user_language'] = "english";
		}
	}else {
		if (isset($_POST['language'])) {
			$_SESSION['user_language'] = filter_var($_POST['language'], FILTER_SANITIZE_STRING);
			$_COOKIE['user_language'] = filter_var($_POST['language'], FILTER_SANITIZE_STRING);
		}else if (isset($_COOKIE['user_language'])) {
			$_SESSION['user_language'] = $_COOKIE['user_language'];
		}
	}

    setcookie('user_language', $_SESSION['user_language'], (time() + 3600));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
/*******************************************************************************
*  File : index.php
*  Author : Nils Laumaill�
*  Description : main page
*
*  DON'T CHANGE !!!
*
*******************************************************************************/

//Test if settings.file exists, if not then install
if (!file_exists('includes/settings.php')) {
	echo '
    <script language="javascript" type="text/javascript">
    <!--
    document.location.replace("install/install.php");
    -->
    </script>';
}

//Laod languages files
require_once('includes/language/'.$_SESSION['user_language'].'.php');
if (isset($_GET['page']) && $_GET['page'] == "kb") {
	require_once('includes/language/'.$_SESSION['user_language'].'_kb.php');
}

//Include files
require_once('includes/settings.php');
require_once('includes/include.php');

// connect to the server
require_once("sources/class.database.php");
$db = new Database($server, $user, $pass, $database, $pre);
$db->connect();

// Include main functions used by cpassman
require_once('sources/main.functions.php');

// Load CORE
require_once("sources/core.php");

// Load links, css and javascripts
require_once("load.php");

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=<?php echo $k['charset'];?>" />
        <title>Collaborative Passwords Manager</title>
        <?php
        echo $htmlHeaders;
        ?>
    </head>

    <body onload="countdown()">
    <?php

    /* HEADER */
    echo '
    <div id="top">
        <div id="logo"><img src="includes/images/logo_cpm.png" alt="" /></div>';

        //Display menu
        if (isset($_SESSION['login'])) {
            echo '
        <div style="float:left;margin:0px 0 0 60px;">
            <div style="font-size:12px;margin-left:40px;margin-top:-5px;width:100%;">
                <b>'.$_SESSION['login'].'</b> - '.$txt['index_expiration_in'].' <div style="display:inline;" id="countdown"></div>
            </div>
            <div style="margin-left:40px; margin-top:3px;width:100%;" id="main_menu">
                <button title="'.$txt['home'].'" onclick="MenuAction(\'\');">
                    <img src="includes/images/home.png" alt="" />
                </button>
                <button style="margin-left:10px;" title="'.$txt['pw'].'" onclick="MenuAction(\'items\');">
                    <img src="includes/images/menu_key.png" alt="" />
                </button>
                <button title="'.$txt['find'].'" onclick="MenuAction(\'find\');">
                    <img src="includes/images/binocular.png" alt="" />
                </button>
                <button title="'.$txt['last_items_icon_title'].'" onclick="ouvrir_div(\'div_last_items\')">
                    <img src="includes/images/tag_blue.png" alt="" />
                </button>';

                // Favourites menu
	        	if (isset($_SESSION['settings']['enable_favourites']) && $_SESSION['settings']['enable_favourites'] == 1) {
	        		echo '
                <button title="'.$txt['my_favourites'].'" onclick="MenuAction(\'favourites\');">
                    <img src="includes/images/favourite.png" alt="" />
                </button>';
	        	}

	        	// KB menu
	        	if (isset($_SESSION['settings']['enable_kb']) && $_SESSION['settings']['enable_kb'] == 1) {
	        		echo '
	                <button style="margin-left:10px;" title="'.$txt['kb_menu'].'" onclick="MenuAction(\'kb\');">
	                    <img src="includes/images/direction.png" alt="" />
	                </button>';
	        	}

                //Admin menu
	        	if ($_SESSION['user_admin'] == 1) {
	        		echo '
                <button style="margin-left:10px;" title="'.$txt['admin_main'].'" onclick="MenuAction(\'manage_main\');">
                    <img src="includes/images/menu_informations.png" alt="" />
                </button>
                <button title="'.$txt['admin_settings'].'" onclick="MenuAction(\'manage_settings\');">
                    <img src="includes/images/menu_settings.png" alt="" />
                </button>';
	        	}

	        	if ($_SESSION['user_admin'] == 1 || $_SESSION['user_gestionnaire'] == 1) {
	        		echo '
                <button title="'.$txt['admin_groups'].'" onclick="MenuAction(\'manage_folders\');">
                    <img src="includes/images/menu_groups.png" alt="" />
                </button>
                <button title="'.$txt['admin_functions'].'" onclick="MenuAction(\'manage_roles\');">
                    <img src="includes/images/menu_functions.png" alt="" />
                </button>
                <button title="'.$txt['admin_users'].'" onclick="MenuAction(\'manage_users\');">
                    <img src="includes/images/menu_user.png" alt="" />
                </button>
                <button title="'.$txt['admin_views'].'" onclick="MenuAction(\'manage_views\');">
                    <img src="includes/images/menu_views.png" alt="" />
                </button>';
	        	}

                //1 hour
                echo '
                <button style="margin-left:10px;" title="'.$txt['index_add_one_hour'].'" onclick="AugmenterSession();">
                    <img src="includes/images/clock__plus.png" alt="" />
                </button>';

                //Disconnect menu
                echo '
                <button title="'.$txt['disconnect'].'" onclick="MenuAction(\'deconnexion\');">
                    <img src="includes/images/door-open.png" alt="" />
                </button>
            </div>
        </div>';
        }

        //Display language menu
        $langues = array("french" => "fr","spanish" => "sp","german" => "de","english" => "us","czech" => "cz","russian" => "ru");
        foreach ($langues as $lang => $abrev) {
            if ($_SESSION['user_language'] == $lang) {
                $flag = $abrev;
                break;
            }
        }

        echo '
        <div style="float:right;margin-right:3px;">
            <dl id="flags" class="dropdown" title="'.$txt['select_language'].'">
                <dt><img src="includes/images/flag_'.$flag.'.png" alt="" /></dt>
                <dd>
                    <ul>
                        <!--<li><a href="#"><img class="flag" src="includes/images/flag_cz.png" alt="Czech" onclick="ChangeLanguage(\'czech\')" /></a></li>-->
                        <li><a href="#"><img class="flag" src="includes/images/flag_us.png" alt="English" onclick="ChangeLanguage(\'english\')" /></a></li>
                        <li><a href="#"><img class="flag" src="includes/images/flag_fr.png" alt="French" onclick="ChangeLanguage(\'french\')" /></a></li>
                        <li><a href="#"><img class="flag" src="includes/images/flag_de.png" alt="German" onclick="ChangeLanguage(\'german\')" /></a></li>
                        <!--<li><a href="#"><img class="flag" src="includes/images/flag_es.png" alt="Spanish" onclick="ChangeLanguage(\'spanish\')" /></a></li>-->
                        <!--<li><a href="#"><img class="flag" src="includes/images/flag_ru.png" alt="Russian" onclick="ChangeLanguage(\'russian\')" /></a></li>-->
                    </ul>
                </dd>
            </dl>
        </div>
    </div>';


    /* LAST SEEN */
    echo '
    <div style="display:none;" id="div_last_items" class="ui-corner-bottom">
        '.$txt['last_items_title'].":&nbsp;";
        if (isset($_SESSION['latest_items_tab'])) {
            foreach ($_SESSION['latest_items_tab'] as $item) {
            	if (!empty($item)) {
            		echo '
                    <span style="cursor:pointer;" onclick="javascript:window.location.href = \''.$item['url'].'\'"><img src="includes/images/tag-small.png" alt="" />'.stripslashes($item['label']).'</span>&nbsp;';
            	}
            }
        }else echo $txt['no_last_items'];
    echo '
    </div>';



    /* MAIN PAGE */
    echo '
    <form action="" name="temp_form" method="post" class="niceform">
        <input type="text" style="display:none;" id="temps_restant" value="', isset($_SESSION['fin_session']) ? $_SESSION['fin_session'] : '', '" />
        <input type="hidden" name="language" id="language" value="" />
    </form>';


    /* INSERT ITEM BUTTONS IN MENU BAR */
    if (isset($_SESSION['autoriser']) && $_SESSION['autoriser'] == true && isset($_GET['page']) && $_GET['page'] == "items") {
        echo '
        <div style="position:absolute;margin:10px -32px 0 1000px;background:#FF8000;padding:3px;" class="ui-corner-right" id="div_right_menu">
            <button title="'.$txt['item_menu_refresh'].'" id="menu_button_refresh_page" style="margin-bottom:5px;" onclick="javascript:document.new_item.submit()">
                <img src="includes/images/refresh.png" alt="" />
            </button>
            <br />',
            ((isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1) || (isset($_SESSION['user_gestionnaire']) && $_SESSION['user_gestionnaire'] == 1)) ? '
            <button title="'.$txt['item_menu_add_rep'].'" id="menu_button_add_group" onclick="open_add_group_div()">
                <img src="includes/images/folder__plus.png" alt="" />
            </button>
            <br />
            <button title="'.$txt['item_menu_edi_rep'].'" id="menu_button_edit_group" onclick="open_edit_group_div()">
                <img src="includes/images/folder__pencil.png" alt="" />
            </button>
            <br />
            <button title="'.$txt['item_menu_del_rep'].'" id="menu_button_del_group" style="margin-bottom:5px;" onclick="open_del_group_div()">
                <img src="includes/images/folder__minus.png" alt="" />
            </button>
            <br />' : '', '
            <button title="'.$txt['item_menu_add_elem'].'" id="menu_button_add_item" onclick="open_add_item_div()"><img src="includes/images/key__plus.png" alt="" /></button>
            <br />
            <button title="'.$txt['item_menu_edi_elem'].'" id="menu_button_edit_item" onclick="open_edit_item_div()"><img src="includes/images/key__pencil.png" alt="" /></button>
            <br />
            <button title="'.$txt['item_menu_del_elem'].'" id="menu_button_del_item" onclick="open_del_item_div()"><img src="includes/images/key__minus.png" alt="" /></button>
            <br />
            <button title="'.$txt['item_menu_copy_elem'].'" id="menu_button_copy_item" onclick="open_copy_item_div()" style="margin-bottom:5px;"><img src="includes/images/key_copy.png" alt="" /></button>
            <br />
            <button title="'.$txt['pw_copy_clipboard'].'" id="menu_button_copy_pw" ><img src="includes/images/ui-text-field-password.png" id="div_copy_pw" alt="" /></button>
            <br />
            <button title="'.$txt['login_copy'].'" style="margin-bottom:5px;" id="menu_button_copy_login"><img src="includes/images/ui-text-field.png" id="div_copy_login" alt="" /></button>
            <br />
            <button title="'.$txt['mask_pw'].'" style="margin-bottom:5px;" id="menu_button_show_pw" onclick="ShowPassword()"><img src="includes/images/eye.png" alt="" /></button>
            <br />
            <button title="'.$txt['link_copy'].'" id="menu_button_copy_link"><img src="includes/images/target.png" id="div_copy_link" alt="" /></button>
        </div>';
    }

    echo '
    <div id="main">';

    // MESSAGE BOX
    echo '
        <div style="position:absolute;width:980px;">
            <div id="message_box" style="display:none;float:right;width:200px;height-min:25px;background-color:#FFC0C0;border:2px solid #FF0000;padding:5px;text-align:center;"></div>
        </div>';

    // Main page
    if (isset($_SESSION['autoriser']) && $_SESSION['autoriser'] == true) {
        //Show menu
        echo '
        <form method="post" name="main_form" action="">
            <input type="hidden" name="menu_action" id="menu_action" value="" />
            <input type="hidden" name="changer_pw" id="changer_pw" value="" />
            <input type="hidden" name="form_user_id" id="form_user_id" value="', isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '', '" />
            <input type="hidden" name="is_admin" id="is_admin" value="', isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : '', '" />
        </form>';
    }

    //---------
    // Display a help to admin
        $errorAdmin = "";
        //error nb folders
	    if (isset($_SESSION['nb_folders']) && $_SESSION['nb_folders'] == 0) {
	    	$errorAdmin = '<span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_no_folders'].'<br />';
	    }
        //error nb roles
        if (isset($_SESSION['nb_roles']) && $_SESSION['nb_roles'] == 0) {
            if (empty($errorAdmin)) {
                $errorAdmin = '<span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_no_roles'];
    		}else {
                $errorAdmin .= '<br /><span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_no_roles'];
            }
        }
        //error Salt key
        if (isset($_SESSION['error']['salt']) && $_SESSION['error']['salt'] == 0) {
            if (empty($errorAdmin)) {
                $errorAdmin = '<span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_salt'];
			}else {
                $errorAdmin .= '<br /><span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_salt'];
			}
		}

        if (isset($_SESSION['validite_pw']) && $_SESSION['validite_pw']) {
            //error cpassman dir
            if (isset($_SESSION['settings']['cpassman_dir']) && empty($_SESSION['settings']['cpassman_dir']) || !isset($_SESSION['settings']['cpassman_dir'])) {
                if (empty($errorAdmin)) {
                    $errorAdmin = '<span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_cpassman_dir'];
				}else {
                    $errorAdmin .= '<br /><span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_cpassman_dir'];
				}
			}
            //error cpassman url
            if (isset($_SESSION['validite_pw']) && (isset($_SESSION['settings']['cpassman_url']) && empty($_SESSION['settings']['cpassman_url']) || !isset($_SESSION['settings']['cpassman_url']))) {
                if (empty($errorAdmin)) {
                    $errorAdmin = '<span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_cpassman_url'];
				}else {
                    $errorAdmin .= '<br /><span class="ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_cpassman_url'];
				}
			}
        }

        //Display help
        if (!empty($errorAdmin)) {
            echo '
            <div style="margin:10px;padding:10px;" class="ui-state-error ui-corner-all">
            '.$errorAdmin.'
            </div>';
		}
    //-----------

    //Display system errors
    if (isset($_SESSION['error']['salt'])) {
        echo '
        <div style="margin:10px;padding:10px;" class="ui-state-error ui-corner-all">
            ', ( isset($_SESSION['error']['salt']) ) ? '<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['error_salt'].'' : '', '
        </div>';
	}

    //Display Maintenance mode information
    if (isset($_SESSION['settings']['maintenance_mode']) && $_SESSION['settings']['maintenance_mode'] == 1 && isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1) {
        echo '
        <div style="text-align:center;margin-bottom:5px;padding:10px;" class="ui-state-highlight ui-corner-all">
            <b>'.$txt['index_maintenance_mode_admin'].'</b>
        </div>';
	}

    //Display UPDATE NEEDED information
    if (isset($_SESSION['settings']['update_needed']) && $_SESSION['settings']['update_needed'] == true && isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1) {
        echo '
        <div style="text-align:center;margin-bottom:5px;padding:10px;" class="ui-state-highlight ui-corner-all">
            <b>'.$txt['update_needed_mode_admin'].'</b>
        </div>';
	}

    //Display pages
    if (isset($_SESSION['validite_pw']) && $_SESSION['validite_pw'] == true && !empty($_GET['page'])) {
        if ($_GET['page'] == "items") {
            //SHow page with Items
            include("items.php");
        }else if ($_GET['page'] == "find") {
            //Show page for items findind
            include("find.php");
        }else if ($_GET['page'] == "favourites") {
            //Show page for user favourites
        	include("favourites.php");
        }else if ($_GET['page'] == "kb") {
        	//Show page for user favourites
        	include("kb.php");
        }else if (in_array($_GET['page'], array_keys($mngPages))) {
            //Define if user is allowed to see management pages
            if ($_SESSION['user_admin'] == 1 || $_SESSION['user_gestionnaire'] == 1) {
                include($mngPages[$_GET['page']]);
			}else {
                $_SESSION['error'] = "1000";    //not allowed page
                include("error.php");
            }
        }else {
            $_SESSION['error'] = "1001";    //page don't exists
            include("error.php");
        }
    }else if (empty($_SESSION['user_id'])) {
        //Automatic redirection
        if (strpos($_SERVER["REQUEST_URI"], "?") > 0) {
            $nextUrl = substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], "?"));
		}else {
            $nextUrl = "";
		}

        // MAINTENANCE MODE
        if (isset($_SESSION['settings']['maintenance_mode']) && $_SESSION['settings']['maintenance_mode'] == 1) {
            echo '
            <div style="text-align:center;margin-top:30px;margin-bottom:20px;padding:10px;" class="ui-state-error ui-corner-all">
                <b>'.$txt['index_maintenance_mode'].'</b>
            </div>';
		}else {
        //SESSION FINISHED => RECONNECTION ASKED
            echo '
                <div style="text-align:center;margin-top:30px;margin-bottom:20px;padding:10px;" class="ui-state-error ui-corner-all">
                    <b>'.$txt['index_session_expired'].'</b>
                </div>';
		}
        
        // CONNECTION FORM
        echo '
            <form method="post" name="form_identify" action="">
                <div style="width:300px; margin-left:auto; margin-right:auto;margin-bottom:50px;padding:25px;" class="ui-state-highlight ui-corner-all">
                    <div style="text-align:center;font-weight:bold;margin-bottom:20px;">
                        '.$txt['index_get_identified'].'
                        &nbsp;<img id="ajax_loader_connexion" style="display:none;" src="includes/images/ajax-loader.gif" alt="" />
                    </div>
                    <div id="erreur_connexion" style="color:red;display:none;text-align:center;margin:5px;">'.$txt['index_bas_pw'].'</div>';

					echo '
					<div style="margin-bottom:3px;">
	                    <label for="login" class="form_label">'.$txt['index_login'].'</label>
	                    <input type="text" size="10" id="login" name="login" class="input_text text ui-widget-content ui-corner-all" />
                    </div>
					<div id="connect_pw" style="margin-bottom:3px;">
	                    <label for="pw" class="form_label">'.$txt['index_password'].'</label>
	                    <input type="password" size="10" id="pw" name="pw" onkeypress="if (event.keyCode == 13) identifyUser(\''.$nextUrl.'\')" class="input_text text ui-widget-content ui-corner-all" />
                    </div>
					<div style="margin-bottom:3px;">
	                    <label for="duree_session" class="">'.$txt['index_session_duration'].'&nbsp;('.$txt['minutes'].') </label>
	                    <input type="text" size="4" id="duree_session" name="duree_session" value="60" onkeypress="if (event.keyCode == 13) identifyUser(\''.$nextUrl.'\')" class="input_text text ui-widget-content ui-corner-all" />
                    </div>

                    <div style="text-align:center;margin-top:5px;font-size:10pt;">
                        <a href="#" onclick="javascript:$(\'#div_forgot_pw\').dialog(\'open\');" style="padding:3px;cursor:pointer;">'.$txt['forgot_my_pw'].'</a>
                    </div>

                    <div style="text-align:center;margin-top:15px;">
                        <input type="button" id="but_identify_user" onclick="identifyUser(\''.$nextUrl.'\')" style="padding:3px;cursor:pointer;" class="ui-state-default ui-corner-all" value="'.$txt['index_identify_button'].'" />
                    </div>
                </div>
            </form>
            <script type="text/javascript">
                document.getElementById("login").focus();
            </script>';

            //DIV for forgotten password
            echo '
            <div id="div_forgot_pw" style="display:none;">
                <div style="margin:5px auto 5px auto;" id="div_forgot_pw_alert"></div>
                <div style="margin:5px auto 5px auto;">'.$txt['forgot_my_pw_text'].'</div>
                <label for="forgot_pw_email">'.$txt['email'].'</label>
                <input type="text" size="40" name="forgot_pw_email" id="forgot_pw_email" />
            </div>';
    }else {
        //PAGE BY DEFAULT
        include("home.php");
    }
    echo '
    </div>';

    //FOOTER
    /* DON'T MODIFY THE FOOTER
    * PLEASE DON'T SUPPRESS THE SOURCEFORGE LOGO WHICH HELPS THIS TOOL TO BE WELL PLACED AND QUOTED ... MANY THANKS TO YOU */
    echo '
    <div id="footer">
        <div style="width:500px;">
            <a href="http://cpassman.org" target="_blank">cPassMan</a> '.$k['version'].' � copyright 2009-2010
        </div>
        <div style="float:right;margin-top:-15px;">
            <a href="http://sourceforge.net/projects/communitypasswo" target="_blank"><img src="', !empty($_SERVER['HTTPS']) ? 'https' : 'http', '://sflogo.sourceforge.net/sflogo.php?group_id=280505&amp;type=9" width="80" height="15" alt="Get cPassMan at SourceForge.net. Fast, secure and Free Open Source software downloads" style="border: 0;" /></a>
            <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/3.0/" title="Collaborative Passwords Manager by Nils Laumaill&#233; is licensed under a Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 License" target="_blank"><img alt="Creative Commons License" style="border-width:0" src="', !empty($_SERVER['HTTPS']) ? 'https' : 'http', '://i.creativecommons.org/l/by-nc-nd/3.0/80x15.png" /></a>
        </div>
    </div>';


    //PAGE LOADING
    echo '
    <div id="div_loading" style="display:none;">
        <div style="border:2px solid #969696; padding:5px; background-color:#B8C2E7;z-index:99999;">
            <img src="includes/images/ajax-loader_2.gif" alt="" />
        </div>
    </div>';

    //Alert BOX
    echo '
    <div id="div_dialog_message" style="display:none;">
		<div id="div_dialog_message_text"></div>
    </div>';

    //ENDING SESSION WARNING
    echo '
    <div id="div_fin_session" style="display:none;">
        <div style="padding:10px;text-align:center;">
            <img src="includes/images/alarm-clock.png" alt="" />&nbsp;<b>'.$txt['index_session_ending'].'</b>
        </div>
    </div>';

    //WARNING FOR QUERY ERROR
    echo '
    <div id="div_mysql_error" style="display:none;">
        <div style="padding:10px;text-align:center;" id="mysql_error_warning"></div>
    </div>';

    //Close DB connection
    $db->close();
    ?>
    </body>
</html>

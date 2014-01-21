<?php
session_start();
header("Content-type: text/html; charset=utf8");
if ( isset($_POST['type']) ){
    switch( $_POST['type'] ){
        case "step1":
            $abspath = str_replace('\\','/',$_POST['abspath']);
            $_SESSION['abspath'] = $abspath;
            if ( substr($abspath,strlen($abspath)-1) == "/" ) $abspath = substr($abspath,0,strlen($abspath)-1);
            $ok_writable = $ok_version = true;
            $ok_extensions = true;
            $txt = "";
            $x=1;
            $tab = array($abspath."/install/settings.php",$abspath."/install/",$abspath."/includes/",$abspath."/files/",$abspath."/upload/");
            foreach($tab as $elem){
                if ( is_writable($elem) )
                    $txt .= '<span style=\"padding-left:30px;font-size:13pt;\">'.$elem.'&nbsp;&nbsp;<img src=\"images/tick-circle.png\"></span><br />';
                else{
                    $txt .= '<span style=\"padding-left:30px;font-size:13pt;\">'.$elem.'&nbsp;&nbsp;<img src=\"images/minus-circle.png\"></span><br />';
                    $ok_writable = false;
                }
                $x++;
            }

            if (!extension_loaded('mcrypt')) {
                $ok_extensions = false;
                $txt .= '<span style=\"padding-left:30px;font-size:13pt;\">PHP extension \"mcrypt\"&nbsp;&nbsp;<img src=\"images/minus-circle.png\"></span><br />';
            }else{
                $txt .= '<span style=\"padding-left:30px;font-size:13pt;\">PHP extension \"mcrypt\"&nbsp;&nbsp;<img src=\"images/tick-circle.png\"></span><br />';
            }

        	if (version_compare(phpversion(), '5.2.0', '<')) {
        		$ok_version = false;
        		$txt .= '<span style=\"padding-left:30px;font-size:13pt;\">PHP version '.phpversion().' is not OK (minimum is 5.2.0) &nbsp;&nbsp;<img src=\"images/minus-circle.png\"></span><br />';
        	}else{
        		$txt .= '<span style=\"padding-left:30px;font-size:13pt;\">PHP version '.phpversion().' is OK&nbsp;&nbsp;<img src=\"images/tick-circle.png\"></span><br />';
        	}

            if ( $ok_writable == true && $ok_extensions == true && $ok_version == true ) {
                echo 'document.getElementById("but_next").disabled = "";';
                echo 'document.getElementById("status_step1").innerHTML = "Elements are OK.";';
                echo 'gauge.modify($("pbar"),{values:[0.20,1]});';
            }else{
                echo 'document.getElementById("but_next").disabled = "disabled";';
                echo 'document.getElementById("status_step1").innerHTML = "Correct the shown errors and click on button Launch to refresh";';
                echo 'gauge.modify($("pbar"),{values:[0.10,1]});';
            }

            echo 'document.getElementById("res_step1").innerHTML = "'.$txt.'";';
            echo 'document.getElementById("loader").style.display = "none";';
        break;

        #==========================
        case "step2":
            $res = "";
        	$db_password = str_replace(" ","+",urldecode($_POST['db_password']));
            // connexion
            if (@mysql_connect($_POST['db_host'], $_POST['db_login'], $db_password)) {
                if ( @mysql_select_db($_POST['db_bdd']) ){
                    echo 'gauge.modify($("pbar"),{values:[0.40,1]});';
                    $res = "Connection is successfull";
                    echo 'document.getElementById("but_next").disabled = "";';
                }else{
                    echo 'gauge.modify($("pbar"),{values:[0.30,1]});';
                    $res = "Impossible to get connected to table";
                    echo 'document.getElementById("but_next").disabled = "disabled";';
                }
            }else{
                echo 'gauge.modify($("pbar"),{values:[0.30,1]});';
                $res = "Impossible to get connected to server";
                echo 'document.getElementById("but_next").disabled = "disabled";';
            }
            echo 'document.getElementById("res_step2").innerHTML = "'.$res.'";';
            echo 'document.getElementById("loader").style.display = "none";';
        break;

        #==========================
        case "step4":
            // Populate Database
            $res = "";

            @mysql_connect($_SESSION['db_host'],$_SESSION['db_login'],$_SESSION['db_pw']);
            @mysql_select_db($_SESSION['db_bdd']);
            $db_tmp = mysql_connect($_SESSION['db_host'], $_SESSION['db_login'], $_SESSION['db_pw']);
            mysql_select_db($_SESSION['db_bdd'],$db_tmp);

            ## TABLE 2
            $res2 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."items` (
                  `id` int(12) NOT NULL AUTO_INCREMENT,
                  `label` varchar(100) NOT NULL,
                  `description` text NOT NULL,
                  `pw` varchar(100) NOT NULL,
                  `url` varchar(250) DEFAULT NULL,
                  `id_tree` varchar(10) DEFAULT NULL,
                  `perso` tinyint(1) NOT NULL DEFAULT '0',
                  `login` varchar(200) DEFAULT NULL,
                  `inactif` tinyint(1) NOT NULL DEFAULT '0',
                  `restricted_to` varchar(200) NOT NULL,
                  `anyone_can_modify` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                );");
            if ( $res2 ){
                echo 'document.getElementById("tbl_2").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table ITEMS!";';
                echo 'document.getElementById("tbl_2").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE 3
            $res3 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."log_items` (
                  `id_item` int(8) NOT NULL,
                  `date` varchar(50) NOT NULL,
                  `id_user` tinyint(4) NOT NULL,
                  `action` varchar(250) NOT NULL,
                  `raison` text NOT NULL
                );");
            if ( $res3 ){
                echo 'document.getElementById("tbl_3").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table LOG_ITEMS!";';
                echo 'document.getElementById("tbl_3").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE 4 - MISC
            require_once("../includes/language/english.php");
            require_once("../includes/include.php");
            $res4 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."misc` (
                  `type` varchar(50) NOT NULL,
                  `intitule` varchar(100) NOT NULL,
                  `valeur` varchar(100) NOT NULL
                );");
            mysql_query("
                INSERT INTO `".$_SESSION['tbl_prefix']."misc` (`type`, `intitule`, `valeur`) VALUES
                ('admin', 'max_latest_items', '10'),
                ('admin', 'enable_favourites', '1'),
                ('admin', 'show_last_items', '1'),
                ('admin', 'enable_pf_feature', '0'),
                ('admin', 'log_connections', '0'),
                ('admin', 'time_format', 'H:i:s'),
                ('admin', 'date_format', 'd/m/Y'),
                ('admin', 'duplicate_folder', '0'),
                ('admin', 'duplicate_item', '0'),
                ('admin', 'number_of_used_pw', '3'),
                ('admin', 'manager_edit', '1'),
                ('admin', 'cpassman_dir', '".$_SESSION['abspath']."'),
                ('admin', 'cpassman_url', '".$_SESSION['url_path']."'),
                ('admin', 'favicon', '".$_SESSION['url_path']."/favicon.ico'),
                ('admin', 'activate_expiration', '0'),
                ('admin','pw_life_duration','0'),
                ('admin','maintenance_mode','1'),
                ('admin','cpassman_version','".$k['version']."'),
                ('admin','ldap_mode','0'),
                ('admin','richtext','0'),
                ('admin','allow_print','0'),
                ('admin','anyone_can_modify','0'),
                ('admin','nb_bad_authentication','0'),
                ('admin', 'send_stats', '".$_SESSION['send_stats']."');");
            if ( $res4 ){
                echo 'document.getElementById("tbl_4").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table MISC!";';
                echo 'document.getElementById("tbl_4").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE 5 - NEESTED_TREE
            $res5 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."nested_tree` (
                  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `parent_id` int(11) NOT NULL,
                  `title` varchar(255) NOT NULL,
                  `nleft` int(11) NOT NULL,
                  `nright` int(11) NOT NULL,
                  `nlevel` int(11) NOT NULL,
                  `bloquer_creation` tinyint(1) NOT NULL DEFAULT '0',
                  `bloquer_modification` tinyint(1) NOT NULL DEFAULT '0',
                  `personal_folder` tinyint(1) NOT NULL DEFAULT '0',
                  `renewal_period` TINYINT( 4 ) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `id` (`id`),
                  KEY `nested_tree_parent_id` (`parent_id`),
                  KEY `nested_tree_nleft` (`nleft`),
                  KEY `nested_tree_nright` (`nright`),
                  KEY `nested_tree_nlevel` (`nlevel`)
                );");
            if ( $res5 ){
                echo 'document.getElementById("tbl_5").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table NESTED_TREE!";';
                echo 'document.getElementById("tbl_5").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE 6 - RIGHTS
            $res6 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."rights` (
                  `id` int(12) NOT NULL AUTO_INCREMENT,
                  `tree_id` int(12) NOT NULL,
                  `fonction_id` int(12) NOT NULL,
                  `authorized` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                );");
            if ( $res6 ){
                echo 'document.getElementById("tbl_6").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table RIGHTS!";';
                echo 'document.getElementById("tbl_6").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE 7 - USERS
            $res7 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."users` (
                  `id` int(12) NOT NULL AUTO_INCREMENT,
                  `login` varchar(50) NOT NULL,
                  `pw` varchar(50) NOT NULL,
                  `groupes_visibles` varchar(250) NOT NULL,
                  `derniers` text NOT NULL,
                  `key_tempo` varchar(100) NOT NULL,
                  `last_pw_change` varchar(30) NOT NULL,
                  `last_pw` text NOT NULL,
                  `admin` tinyint(1) NOT NULL DEFAULT '0',
                  `fonction_id` varchar(255) NOT NULL,
                  `groupes_interdits` varchar(255) NOT NULL,
                  `last_connexion` varchar(30) NOT NULL,
                  `gestionnaire` int(11) NOT NULL DEFAULT '0',
                  `email` varchar(300) NOT NULL,
                  `favourites` varchar(300) NOT NULL,
                  `latest_items` varchar(300) NOT NULL,
                  `personal_folder` int(1) NOT NULL DEFAULT '0',
                  `disabled` tinyint(1) NOT NULL DEFAULT '0',
                  `no_bad_attempts` tinyint(1) NOT NULL DEFAULT '0',
                  `can_create_root_folder` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `login` (`login`)
                );");
            if ( $res7 ){
                echo 'document.getElementById("tbl_7").innerHTML = "<img src=\"images/tick.png\">";';

                //v�rifier que l'admin n'existe pas
                $tmp = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM `".$_SESSION['tbl_prefix']."users` WHERE login = 'admin'"));
                if ( $tmp[0] == 0 ){
                    $res8 = mysql_query("
                        INSERT INTO `".$_SESSION['tbl_prefix']."users` (`id`, `login`, `pw`, `groupes_visibles`, `derniers`, `key_tempo`, `last_pw_change`, `last_pw`, `admin`, `fonction_id`, `groupes_interdits`, `last_connexion`, `gestionnaire`, `email`, `favourites`, `latest_items`, `personal_folder`) VALUES ( NULL, 'admin', '21232f297a57a5a743894a0e4a801fc3', '', '', '', '', '', '1', '', '', '', '0', '', '', '', '0')
                        ");
                    if ( $res8 ){
                        echo 'document.getElementById("tbl_8").innerHTML = "<img src=\"images/tick.png\">";';
                    }else{
                        echo 'document.getElementById("res_step4").innerHTML = "Could not import admin account!";';
                        echo 'document.getElementById("tbl_8").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                        echo 'document.getElementById("loader").style.display = "none";';
                        mysql_close($db_tmp);
                        break;
                    }
                }else echo 'document.getElementById("tbl_8").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table USERS!";';
                echo 'document.getElementById("tbl_7").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE 8 - TAGS
            $res8 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."tags` (
                  `id` int(12) NOT NULL AUTO_INCREMENT,
                  `tag` varchar(30) NOT NULL,
                  `item_id` int(12) NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `id` (`id`)
                );");
            if ( $res8 ){
                echo 'document.getElementById("tbl_9").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table TAGS!";';
                echo 'document.getElementById("tbl_9").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE 9 - LOG_SYSTEM
            $res8 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."log_system` (
                  `id` int(12) NOT NULL AUTO_INCREMENT,
                  `type` varchar(20) NOT NULL,
                  `date` varchar(30) NOT NULL,
                  `label` text NOT NULL,
                  `qui` varchar(30) NOT NULL,
                  PRIMARY KEY (`id`)
                );");
            if ( $res8 ){
                echo 'document.getElementById("tbl_10").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table LOG_SYSTEM!";';
                echo 'document.getElementById("tbl_10").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE 10 - FILES
            $res9 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."files` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_item` int(11) NOT NULL,
                `name` varchar(50) NOT NULL,
                `size` int(10) NOT NULL,
                `extension` varchar(10) NOT NULL,
                `type` varchar(50) NOT NULL,
                `file` varchar(50) NOT NULL,
                PRIMARY KEY (`id`)
                );");
            if ( $res9 ){
                echo 'document.getElementById("tbl_11").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table FILES!";';
                echo 'document.getElementById("tbl_11").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE CACHE
            $res9 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."cache` (
                `id` int(12) NOT NULL,
                `label` varchar(50) NOT NULL,
                `description` text NOT NULL,
                `tags` text NOT NULL,
                `id_tree` int(12) NOT NULL,
                `perso` tinyint(1) NOT NULL,
                `restricted_to` varchar(200) NOT NULL
                );");
            if ( $res9 ){
                echo 'document.getElementById("tbl_12").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table FILES!";';
                echo 'document.getElementById("tbl_12").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

        	## TABLE 13 - ROLES_TITLES
            $res13 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."roles_title` (
                  `id` int(12) NOT NULL AUTO_INCREMENT,
                  `title` varchar(50) NOT NULL,
                  PRIMARY KEY (`id`)
                );");
            if ( $res13 ){
                echo 'document.getElementById("tbl_13").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table ITEMS!";';
                echo 'document.getElementById("tbl_13").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

        	## TABLE 14 - ROLES_VALUES
        	$res14 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."roles_values` (
                  `role_id` int(12) NOT NULL,
                  `folder_id` int(12) NOT NULL
                );");
        	if ( $res14 ){
        		echo 'document.getElementById("tbl_14").innerHTML = "<img src=\"images/tick.png\">";';
        	}else{
        		echo 'document.getElementById("res_step4").innerHTML = "An error appears on table ITEMS!";';
        		echo 'document.getElementById("tbl_14").innerHTML = "<img src=\"images/exclamation-red.png\">";';
        		echo 'document.getElementById("loader").style.display = "none";';
        		mysql_close($db_tmp);
        		break;
        	}

        	## TABLE KB
        	$res = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."kb` (
					`id` int(12) NOT NULL AUTO_INCREMENT,
					`category_id` int(12) NOT NULL,
					`label` varchar(200) NOT NULL,
					`description` text NOT NULL,
					`author_id` int(12) NOT NULL,
					`anyone_can_modify` tinyint(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)
                );");
        	if ( $res ){
        		echo 'document.getElementById("tbl_15").innerHTML = "<img src=\"images/tick.png\">";';
        	}else{
        		echo 'document.getElementById("res_step4").innerHTML = "An error appears on table KB!";';
        		echo 'document.getElementById("tbl_15").innerHTML = "<img src=\"images/exclamation-red.png\">";';
        		echo 'document.getElementById("loader").style.display = "none";';
        		mysql_close($db_tmp);
        		break;
        	}

        	## TABLE KB_CATEGORIES
        	$res = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."kb_categories` (
					`id` int(12) NOT NULL AUTO_INCREMENT,
					`category` varchar(50) NOT NULL,
					PRIMARY KEY (`id`)
                );");
        	if ( $res ){
        		echo 'document.getElementById("tbl_16").innerHTML = "<img src=\"images/tick.png\">";';
        	}else{
        		echo 'document.getElementById("res_step4").innerHTML = "An error appears on table KB_CATEGORIES!";';
        		echo 'document.getElementById("tbl_16").innerHTML = "<img src=\"images/exclamation-red.png\">";';
        		echo 'document.getElementById("loader").style.display = "none";';
        		mysql_close($db_tmp);
        		break;
        	}

        	## TABLE 14 - ROLES_VALUES
        	$res14 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."roles_values` (
                  `role_id` int(12) NOT NULL,
                  `folder_id` int(12) NOT NULL
                );");
        	if ( $res14 ){
        		echo 'document.getElementById("tbl_14").innerHTML = "<img src=\"images/tick.png\">";';
        	}else{
        		echo 'document.getElementById("res_step4").innerHTML = "An error appears on table ITEMS!";';
        		echo 'document.getElementById("tbl_14").innerHTML = "<img src=\"images/exclamation-red.png\">";';
        		echo 'document.getElementById("loader").style.display = "none";';
        		mysql_close($db_tmp);
        		break;
        	}
            
            ## TABLE KB_ITEMS
            $res = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."kb_items` (
                `kb_id` tinyint(12) NOT NULL,
                `item_id` tinyint(12) NOT NULL
                );");
            if ( $res ){
                echo 'document.getElementById("tbl_17").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step4").innerHTML = "An error appears on table KB_ITEMS!";';
                echo 'document.getElementById("tbl_17").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            echo 'gauge.modify($("pbar"),{values:[0.80,1]});';
            echo 'document.getElementById("but_next").disabled = "";';
            echo 'document.getElementById("res_step4").innerHTML = "Database has been populated";';
            echo 'document.getElementById("loader").style.display = "none";';
            mysql_close($db_tmp);
        break;


        case "step5":
            $filename = "../includes/settings.php";
            $events = "";
            if (file_exists($filename)) {
                if ( !copy($filename, $filename.'.'.date("Y_m_d",mktime(0,0,0,date('m'),date('d'),date('y')))) ) {
                    echo 'document.getElementById("res_step4").innerHTML = "Setting.php file already exists and cannot be renamed. Please do it by yourself and click on button Launch.";';
                    echo 'document.getElementById("loader").style.display = "none";';
                    break;
                }else{
                    $events .= "The file $filename already exist. A copy has been created.<br />";
                    unlink($filename);
                }
            }
            $fh = fopen($filename, 'w');

            fwrite($fh, "<?php
global \$lang, \$txt, \$k, \$chemin_passman, \$url_passman, \$mdp_complexite, \$mngPages;
global \$smtp_server, \$smtp_auth, \$smtp_auth_username, \$smtp_auth_password, \$email_from,\$email_from_name;
global \$server, \$user, \$pass, \$database, \$pre, \$db;

\$k['charset'] = \"".$_SESSION['charset']."\";  //the charset you want to use    : French => ISO-8859-15
@define('SALT', '".$_SESSION['encrypt_key']."'); //Define your encryption key => NeverChange it once it has been used !!!!!

### EMAIL PROPERTIES ###
\$smtp_server = \"".$_SESSION['smtp_server']."\";
\$smtp_auth = ".$_SESSION['smtp_auth']."; //false or true
\$smtp_auth_username = \"".$_SESSION['smtp_auth_username']."\";
\$smtp_auth_password = \"".$_SESSION['smtp_auth_password']."\";
\$email_from = \"".$_SESSION['email_from']."\";
\$email_from_name = \"".$_SESSION['email_from_name']."\";

### DATABASE connexion parameters ###
\$server = \"".$_SESSION['db_host']."\";
\$user = \"".$_SESSION['db_login']."\";
\$pass = \"".$_SESSION['db_pw']."\";
\$database = \"".$_SESSION['db_bdd']."\";
\$pre = \"".$_SESSION['tbl_prefix']."\";

?>");

            fclose($fh);
            echo 'gauge.modify($("pbar"),{values:[1,1]});';
            echo 'document.getElementById("but_next").disabled = "";';
            echo 'document.getElementById("res_step5").innerHTML = "Setting.php file has created.";';
            echo 'document.getElementById("loader").style.display = "none";';
        break;
    }
}
?>
<?php
session_start();

require_once("../includes/language/english.php");
require_once("../includes/include.php");

################
## Function permits to get the value from a line
################
function getSettingValue($val){
    $val = trim(strstr($val,"="));
    return trim(str_replace('"','',substr($val,1,strpos($val,";")-1)));
}

################
## Function permits to check if a column exists, and if not to add it
################
function add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NULL" ){
  $exists = false;
  $columns = mysql_query("show columns from $db");
  while($c = mysql_fetch_assoc($columns)){
      if($c['Field'] == $column){
          $exists = true;
          break;
      }
  }
  if(!$exists){
      return mysql_query("ALTER TABLE `$db` ADD `$column`  $column_attr");
  }
}

function table_exists($tablename, $database = false) {

    if(!$database) {
        $res = mysql_query("SELECT DATABASE()");
        $database = mysql_result($res, 0);
    }

    $res = mysql_query("
        SELECT COUNT(*) AS count 
        FROM information_schema.tables 
        WHERE table_schema = '$database' 
        AND table_name = '$tablename'
    ");

    return mysql_result($res, 0) == 1;

}



if ( isset($_POST['type']) ){
    switch( $_POST['type'] ){
        case "step1":
            $abspath = str_replace('\\','/',$_POST['abspath']);
            $_SESSION['abspath'] = $abspath;
            if ( substr($abspath,strlen($abspath)-1) == "/" ) $abspath = substr($abspath,0,strlen($abspath)-1);
            $ok_writable = true;
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

            if ( $ok_writable == true && $ok_extensions == true ) {
                echo 'document.getElementById("but_next").disabled = "";';
                echo 'document.getElementById("res_step1").innerHTML = "Elements are OK.";';
                echo 'gauge.modify($("pbar"),{values:[0.25,1]});';
            }else{
                echo 'document.getElementById("but_next").disabled = "disabled";';
                echo 'document.getElementById("res_step1").innerHTML = "Correct the shown errors and click on button Launch to refresh";';
                echo 'gauge.modify($("pbar"),{values:[0.25,1]});';
            }

            echo 'document.getElementById("res_step1").innerHTML = "'.$txt.'";';
            echo 'document.getElementById("loader").style.display = "none";';
        break;

        #==========================
        case "step2":
            $res = "";
        	$db_password = str_replace(" ","+",urldecode($_POST['db_password']));
            // connexion
            if ( @mysql_connect($_POST['db_host'],$_POST['db_login'],$db_password) ){
                if ( @mysql_select_db($_POST['db_bdd']) ){
                    echo 'gauge.modify($("pbar"),{values:[0.50,1]});';
                    $res = "Connection is successfull";
                    echo 'document.getElementById("but_next").disabled = "";';
                }else{
                    echo 'gauge.modify($("pbar"),{values:[0.50,1]});';
                    $res = "Impossible to get connected to table";
                    echo 'document.getElementById("but_next").disabled = "disabled";';
                }
            }else{
                echo 'gauge.modify($("pbar"),{values:[0.50,1]});';
                $res = "Impossible to get connected to server";
                echo 'document.getElementById("but_next").disabled = "disabled";';
            }
            echo 'document.getElementById("res_step2").innerHTML = "'.$res.'";';
            echo 'document.getElementById("loader").style.display = "none";';
        break;

        #==========================
        case "step3":
            // Database
            $res = "";

            @mysql_connect($_SESSION['db_host'],$_SESSION['db_login'],$_SESSION['db_pw']);
            @mysql_select_db($_SESSION['db_bdd']);
            $db_tmp = mysql_connect($_SESSION['db_host'], $_SESSION['db_login'], $_SESSION['db_pw']);
            mysql_select_db($_SESSION['db_bdd'],$db_tmp);

            ## Populate table MISC
            $val = array(
                array('admin', 'max_latest_items', '10',0),
                array('admin', 'enable_favourites', '1',0),
                array('admin', 'show_last_items', '1',0),
                array('admin', 'enable_pf_feature', '0',0),
                array('admin', 'menu_type', 'context',0),
                array('admin', 'log_connections', '0',0),
                array('admin', 'time_format', 'H:i:s',0),
                array('admin', 'date_format', 'd/m/Y',0),
                array('admin', 'duplicate_folder', '0',0),
                array('admin', 'duplicate_item', '0',0),
                array('admin', 'number_of_used_pw', '3',0),
                array('admin', 'manager_edit', '1',0),
                array('admin', 'cpassman_dir', '',0),
                array('admin', 'cpassman_url', '',0),
                array('admin', 'favicon', '',0),
                array('admin', 'activate_expiration', '0',0),
                array('admin','pw_life_duration','30',0),
                array('admin','maintenance_mode','1',1),
                array('admin','cpassman_version',$k['version'],1),
                array('admin','ldap_mode','0',0),
				array('admin','richtext',0,0),
	            array('admin','allow_print',0,0),
	            array('admin','anyone_can_modify',0,0),
                array('admin','nb_bad_authentication',0,0),
                array('admin','send_stats', empty($_SESSION['send_stats']) ? '0' : $_SESSION['send_stats'],1));
            $res1 = "na";
            foreach($val as $elem){
                //Check if exists before inserting
                $res_tmp = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM ".$_SESSION['tbl_prefix']."misc WHERE type='".$elem[0]."' AND intitule='".$elem[1]."'"));
                if ( $res_tmp[0] == 0 ){
                     $res1 = mysql_query("INSERT INTO `".$_SESSION['tbl_prefix']."misc` (`type`, `intitule`, `valeur`) VALUES ('".$elem[0]."', '".$elem[1]."', '".$elem[2]."');");
                     if ( !$res1 ) break;
                }else{
                    // Force update for some settings
                    if ( $elem[3] == 1 ){
                        mysql_query("UPDATE `".$_SESSION['tbl_prefix']."misc` SET `valeur` = '".$elem[2]."' WHERE type = 'admin' AND intitule = '".$elem[1]."'");
                    }
                }
            }

            if ( $res1 || $res1 == "na" ){
                echo 'document.getElementById("tbl_1").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step3").innerHTML = "An error appears when inserting datas!";';
                echo 'document.getElementById("tbl_1").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

        	## Alter ITEMS table
        	$res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."items","anyone_can_modify","TINYINT(1) NOT NULL DEFAULT '0'");

            ## Alter USERS table
            $res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."users","favourites","VARCHAR(300)");
            $res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."users","latest_items","VARCHAR(300)");
            $res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."users","personal_folder","INT(1) NOT NULL DEFAULT '0'");
            $res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."users","disabled","TINYINT(1) NOT NULL DEFAULT '0'");
            $res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."users","no_bad_attempts","TINYINT(1) NOT NULL DEFAULT '0'");
            $res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."users","can_create_root_folder","TINYINT(1) NOT NULL DEFAULT '0'");
            echo 'document.getElementById("tbl_2").innerHTML = "<img src=\"images/tick.png\">";';

            ## Alter nested_tree table
            $res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."nested_tree","personal_folder","TINYINT(1) NOT NULL DEFAULT '0'");
            $res2 = add_column_if_not_exist($_SESSION['tbl_prefix']."nested_tree","renewal_period","TINYINT(4) NOT NULL DEFAULT '0'");
            echo 'document.getElementById("tbl_5").innerHTML = "<img src=\"images/tick.png\">";';

            #to 1.08
            //include('upgrade_db_1.08.php');

            ## TABLE TAGS
            $res8 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."tags` (
                  `id` int(12) NOT NULL AUTO_INCREMENT,
                  `tag` varchar(30) NOT NULL,
                  `item_id` int(12) NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `id` (`id`)
                );");
            if ( $res8 ){
                echo 'document.getElementById("tbl_3").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step3").innerHTML = "An error appears on table TAGS!";';
                echo 'document.getElementById("tbl_3").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            ## TABLE LOG_SYSTEM
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
                echo 'document.getElementById("tbl_4").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step3").innerHTML = "An error appears on table LOG_SYSTEM!";';
                echo 'document.getElementById("tbl_4").innerHTML = "<img src=\"images/exclamation-red.png\">";';
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
                echo 'document.getElementById("tbl_6").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step3").innerHTML = "An error appears on table FILES!";';
                echo 'document.getElementById("tbl_6").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }
            mysql_query("ALTER TABLE `".$_SESSION['tbl_prefix']."files` CHANGE id id INT(11) AUTO_INCREMENT PRIMARY KEY;");

            ## TABLE CACHE
            mysql_query("DROP TABLE IF EXISTS `".$_SESSION['tbl_prefix']."cache`");
            $res8 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."cache` (
                `id` int(12) NOT NULL,
                `label` varchar(50) NOT NULL,
                `description` text NOT NULL,
                `tags` text NOT NULL,
                `id_tree` int(12) NOT NULL,
                `perso` tinyint(1) NOT NULL,
                `restricted_to` varchar(200) NOT NULL
                );");
            if ( $res8 ){
                //ADD VALUES
                $sql = "SELECT *
                        FROM ".$_SESSION['tbl_prefix']."items
                        WHERE inactif=0";
                $rows = mysql_query($sql);
                while( $reccord = mysql_fetch_array($rows)){
                    //Get all TAGS
                    $tags = "";
                    $items_res = mysql_query("SELECT tag FROM ".$_SESSION['tbl_prefix']."tags WHERE item_id=".$reccord['id']) or die(mysql_error());
                    $item_tags = mysql_fetch_array($items_res);
                    if ( !empty($item_tags) )
                        foreach( $item_tags as $item_tag ){
                            if ( !empty($item_tag['tag']))
                                $tags .= $item_tag['tag']. " ";
                        }
                    //store data
                    mysql_query(
                        "INSERT INTO ".$_SESSION['tbl_prefix']."cache
                        VALUES (
                            '".$reccord['id']."',
                            '".$reccord['label']."',
                            '".$reccord['description']."',
                            '".$tags."',
                            '".$reccord['id_tree']."',
                            '".$reccord['perso']."',
                            '".$reccord['restricted_to']."'
                        )"
                    );
                }
                echo 'document.getElementById("tbl_7").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step3").innerHTML = "An error appears on table CACHE!";';
                echo 'document.getElementById("tbl_7").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            /*
            *  Change table FUNCTIONS
            *  By 2 tables ROLES
            */
        	$res9 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."roles_title` (
                `id` int(12) NOT NULL,
                `title` varchar(50) NOT NULL
                );");
        	$res10 = mysql_query("
                CREATE TABLE IF NOT EXISTS `".$_SESSION['tbl_prefix']."roles_values` (
                `role_id` int(12) NOT NULL,
                `folder_id` int(12) NOT NULL
                );");
            if (table_exists($_SESSION['tbl_prefix']."functions")) {
                $table_function_exists = true;
            }else{
                $table_function_exists = false;
            }
        	if ( $res9 && $res10 && $table_function_exists == true){
            	//Get data from tables FUNCTIONS and populate new ROLES tables
        		$rows = mysql_query("SELECT * FROM ".$_SESSION['tbl_prefix']."functions");
        		while( $reccord = mysql_fetch_array($rows)){
        			//Add new role title
        			mysql_query(
        				"INSERT INTO ".$_SESSION['tbl_prefix']."roles_title
                        VALUES (
                            '".$reccord['id']."',
                            '".$reccord['title']."'
                        )"
        			);

        			//Add each folder in roles_values
        			foreach(explode(';', $reccord['groupes_visibles']) as $folder_id){
        				if (!empty($folder_id)) {
        					mysql_query(
								"INSERT INTO ".$_SESSION['tbl_prefix']."roles_values
								VALUES (
								'".$reccord['id']."',
								'".$folder_id."'
								)"
							);
        				}
        			}
        		}

        		//Now alter table roles_title in order to create a primary index
        		mysql_query("ALTER TABLE `".$_SESSION['tbl_prefix']."roles_title` ADD PRIMARY KEY(`id`)");
        		mysql_query("ALTER TABLE `".$_SESSION['tbl_prefix']."roles_title` CHANGE `id` `id` INT( 12 ) NOT NULL AUTO_INCREMENT ");
                
                //Drop old table
                mysql_query("DROP TABLE ".$_SESSION['tbl_prefix']."functions");

            	echo 'document.getElementById("tbl_9").innerHTML = "<img src=\"images/tick.png\">";';
        	}else if($table_function_exists == false) {
                echo 'document.getElementById("tbl_9").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
        		echo 'document.getElementById("res_step3").innerHTML = "An error appears on tables ROLES creation!";';
        		echo 'document.getElementById("tbl_9").innerHTML = "<img src=\"images/exclamation-red.png\">";';
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
        		echo 'document.getElementById("tbl_10").innerHTML = "<img src=\"images/tick.png\">";';
        	}else{
        		echo 'document.getElementById("res_step3").innerHTML = "An error appears on table KB!";';
        		echo 'document.getElementById("tbl_10").innerHTML = "<img src=\"images/exclamation-red.png\">";';
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
        		echo 'document.getElementById("tbl_11").innerHTML = "<img src=\"images/tick.png\">";';
        	}else{
        		echo 'document.getElementById("res_step3").innerHTML = "An error appears on table KB_CATEGORIES!";';
        		echo 'document.getElementById("tbl_11").innerHTML = "<img src=\"images/exclamation-red.png\">";';
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
                echo 'document.getElementById("tbl_12").innerHTML = "<img src=\"images/tick.png\">";';
            }else{
                echo 'document.getElementById("res_step3").innerHTML = "An error appears on table KB_ITEMS!";';
                echo 'document.getElementById("tbl_12").innerHTML = "<img src=\"images/exclamation-red.png\">";';
                echo 'document.getElementById("loader").style.display = "none";';
                mysql_close($db_tmp);
                break;
            }

            /* Unlock this step */
            echo 'gauge.modify($("pbar"),{values:[0.75,1]});';
            echo 'document.getElementById("but_next").disabled = "";';
            echo 'document.getElementById("res_step3").innerHTML = "Database has been populated";';
            echo 'document.getElementById("loader").style.display = "none";';
            mysql_close($db_tmp);
        break;

    //=============================
    case "step4":
            $filename = "../includes/settings.php";
            $events = "";
            if (file_exists($filename)) {
                //copy some constants from this existing file
                $settings_file = file($filename);
                while(list($key,$val) = each($settings_file)) {
                    if (substr_count($val,'charset')>0) $_SESSION['charset'] = getSettingValue($val);
                    else if (substr_count($val,'@define(')>0) $_SESSION['encrypt_key'] = substr($val,17,strpos($val,"')"));
                    else if (substr_count($val,'$smtp_server')>0) $_SESSION['smtp_server'] = getSettingValue($val);
                    else if (substr_count($val,'$smtp_auth')>0) $_SESSION['smtp_auth'] = getSettingValue($val);
                    else if (substr_count($val,'$smtp_auth_username')>0) $_SESSION['smtp_auth_username'] = getSettingValue($val);
                    else if (substr_count($val,'$smtp_auth_password')>0) $_SESSION['smtp_auth_password'] = getSettingValue($val);
                    else if (substr_count($val,'$email_from')>0) $_SESSION['email_from'] = getSettingValue($val);
                    else if (substr_count($val,'$email_from_name')>0) $_SESSION['email_from_name'] = getSettingValue($val);
                }

                //Do a copy of the existing file
                if ( !copy($filename, $filename.'.'.date("Y_m_d",mktime(0,0,0,date('m'),date('d'),date('y')))) ) {
                    echo 'document.getElementById("res_step4").innerHTML = "Setting.php file already exists and cannot be renamed. Please do it by yourself and click on button Launch.";';
                    echo 'document.getElementById("loader").style.display = "none";';
                    break;
                }else{
                    $events .= "The file $filename already exist. A copy has been created.<br />";
                    unlink($filename);
                }

            	$fh = fopen($filename, 'w');

            	//prepare smtp_auth variable
            	if (empty($_SESSION['smtp_auth'])) $_SESSION['smtp_auth'] = 'false';
            	if (empty($_SESSION['smtp_auth_username'])) $_SESSION['smtp_auth_username'] = 'false';
            	if (empty($_SESSION['smtp_auth_password'])) $_SESSION['smtp_auth_password'] = 'false';
            	if (empty($_SESSION['email_from_name'])) $_SESSION['email_from_name'] = 'false';

            	fwrite($fh, "<?php
global \$lang, \$txt, \$k, \$chemin_passman, \$url_passman, \$mdp_complexite, \$mngPages;
global \$smtp_server, \$smtp_auth, \$smtp_auth_username, \$smtp_auth_password, \$email_from,\$email_from_name;
global \$server, \$user, \$pass, \$database, \$pre, \$db;

\$k['charset'] = \"". $_SESSION['charset'] ."\";  //the charset you want to use    : French => ISO-8859-15
@define('SALT', '". $_SESSION['encrypt_key'] ."'); //Define your encryption key => NeverChange it once it has been used !!!!!

### EMAIL PROPERTIES ###
\$smtp_server = \"". $_SESSION['smtp_server'] ."\";
\$smtp_auth = ". $_SESSION['smtp_auth'] ."; //false or true
\$smtp_auth_username = \"". $_SESSION['smtp_auth_username'] ."\";
\$smtp_auth_password = \"". $_SESSION['smtp_auth_password'] ."\";
\$email_from = \"". $_SESSION['email_from'] ."\";
\$email_from_name = \"". $_SESSION['email_from_name'] ."\";

### DATABASE connexion parameters ###
\$server = \"". $_SESSION['db_host'] ."\";
\$user = \"". $_SESSION['db_login'] ."\";
\$pass = \"". $_SESSION['db_pw'] ."\";
\$database = \"". $_SESSION['db_bdd'] ."\";
\$pre = \"". $_SESSION['tbl_prefix'] ."\";

?>");

            	fclose($fh);
            	echo 'gauge.modify($("pbar"),{values:[1,1]});';
            	echo 'document.getElementById("but_next").disabled = "";';
            	echo 'document.getElementById("res_step4").innerHTML = "Setting.php file has created.";';
            	echo 'document.getElementById("loader").style.display = "none";';

            }else{
            	//settings.php file doesn't exit => ERROR !!!!
            	echo 'document.getElementById("res_step4").innerHTML = "<img src=\"../includes/images/error.png\">&nbsp;Setting.php file doesn\'t exist! Upgrade can\'t continue without this file.<br />Please copy your existing settings.php into the \"includes\" folder of your cpassman installation ";';
            	echo 'document.getElementById("loader").style.display = "none";';
            }

        break;
    }
}
?>
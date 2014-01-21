<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2010
 */

session_start();
include('../includes/settings.php');
require_once('../includes/include.php');
header("Content-type: text/html; charset=".$k['charset']);

require_once('main.functions.php');

//Connect to mysql server
require_once("class.database.php");
$db = new Database($server, $user, $pass, $database, $pre);
$db->connect();

function utf8Urldecode($value)
{
	$value = preg_replace('/%([0-9a-f]{2})/ie', 'chr(hexdec($1))', (string) $value);
	return $value;
}

// Construction de la requ�te en fonction du type de valeur
if ( !empty($_POST['type']) ){
	switch($_POST['type'])
	{
		case "kb_in_db":
			//check if allowed to modify
            if (isset($_POST['id']) && !empty($_POST['id'])) {
			    $row = $db->query("SELECT anyone_can_modify, author_id FROM ".$pre."kb WHERE id = ".$_POST['id']);
			    $ret = $db->fetch_array($row);
                if ($ret['anyone_can_modify'] == 1 || $ret['author_id'] == $_SESSION['user_id']) {
                    $manage_kb = true;
                }else{
                    $manage_kb = false;
                }
            }else{
                $manage_kb = true;
            }
			if ($manage_kb == true) {
				$label = utf8_decode($_POST['label']);
				$category = utf8_decode($_POST['category']);
				$description = utf8_decode($_POST['description']);

				//Add category if new
				$data = $db->fetch_row("SELECT COUNT(*) FROM ".$pre."kb_categories WHERE category = '".mysql_real_escape_string($category)."'");
				if ( $data[0] == 0 ){
					$cat_id = $db->query_insert(
					"kb_categories",
					array(
					    'category' => mysql_real_escape_string($category)
					)
					);
				}else{
					//get the ID of this existing category
					$cat_id = $db->fetch_row("SELECT id FROM ".$pre."kb_categories WHERE category = '".mysql_real_escape_string($category)."'");
					$cat_id = $cat_id[0];
				}

				if (isset($_POST['id']) && !empty($_POST['id'])) {
					//update KB
					$new_id = $db->query_update(
					    "kb",
					    array(
					        'label' => ($label),
					        'description' => mysql_real_escape_string($description),
					        'author_id' => $_SESSION['user_id'],
					        'category_id' => $cat_id,
					        'anyone_can_modify' => $_POST['anyone_can_modify']
					    ),
					    "id='".$_POST['id']."'"
					);
				}else{
					//add new KB
					$new_id = $db->query_insert(
					    "kb",
					    array(
					        'label' => $label,
					        'description' => mysql_real_escape_string($description),
					        'author_id' => $_SESSION['user_id'],
						    'category_id' => $cat_id,
						    'anyone_can_modify' => $_POST['anyone_can_modify']
					    )
					);
				}
                
                
                //delete all associated items to this KB
                $db->query_delete(
                    "kb_items",
                    array(
                        'kb_id' => $_POST['id']
                    )
                );
                //add all items associated to this KB
                foreach(explode(',', $_POST['kb_associated_to']) as $item_id) {
                    $db->query_insert(
                        "kb_items",
                        array(
                            'kb_id' => $_POST['id'],
                            'item_id' => $item_id
                        )
                    );
                }


				echo '$("#kb_form").dialog("close");oTable = $("#t_kb").dataTable();LoadingPage();oTable.fnDraw();';
			}else{
				echo '$("#kb_form").dialog("close");';
			}


		break;


		case "open_kb":
			$row = $db->query("SELECT k.id AS id, k.label AS label, k.description AS description, k.category_id AS category_id, k.author_id AS author_id, k.anyone_can_modify AS anyone_can_modify,
							u.login AS login, c.category AS category
							FROM ".$pre."kb AS k
							INNER JOIN ".$pre."kb_categories AS c ON (c.id = k.category_id)
							INNER JOIN ".$pre."users AS u ON (u.id = k.author_id)
							WHERE k.id = '".$_POST['id']."'
			");
			$ret = $db->fetch_array($row);
			echo '$("#kb_label").val("'.addslashes($ret['label']).'");';
			echo '$("#kb_category").val("'.$ret['category'].'");';
			echo '$("#kb_description").val("'.$ret['description'].'");';
			echo '$("#kb_id").val("'.$_POST['id'].'");';
			if ($ret['anyone_can_modify'] == 0) {
				echo '$("#modify_kb_no").attr("checked", "checked");';
			}else{
				echo '$("#modify_kb_yes").attr("checked", "checked");';
			}
            
            //select associated items
            $rows = $db->fetch_all_array("SELECT item_id
                            FROM ".$pre."kb_items
                            WHERE kb_id = '".$_POST['id']."'
            ");
            foreach( $rows as $reccord ) {
                echo '$("#kb_associated_to option[value='.$reccord['item_id'].']").attr("selected","selected");';
            }
            
            //open KB dialog
			echo '$("#kb_form").dialog("open");LoadingPage();';
		break;


		case "delete_kb":
			$db->query_delete(
				"kb",
				array(
					'id' => $_POST['id']
				)
			);
			//echo 'oTable = $("#t_kb").dataTable();LoadingPage();oTable.fnDraw();';
			break;
	}
}
?>
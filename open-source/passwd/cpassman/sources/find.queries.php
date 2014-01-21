<?php
####################################################################################################
## File : main.queries.php
## Author : Nils Laumaill�
## Description : File contains queries for ajax
##
## DON'T CHANGE !!!
##
####################################################################################################
session_start();
global $k, $settings;
include('../includes/settings.php');
header("Content-type: text/html; charset=".$k['charset']);

require_once("class.database.php");
$db = new Database($server, $user, $pass, $database, $pre);
$db->connect();

//load the full Items tree
require_once ("NestedTree.class.php");
$tree = new NestedTree($pre.'nested_tree', 'id', 'parent_id', 'title');

//Columns name
$aColumns = array( 'id', 'label', 'description', 'tags', 'id_tree' );

//init SQL variables
$sWhere = $sOrder = $sLimit = "";

//get list of personal folders
$array_pf = array();
$list_pf = "";
$rows = $db->fetch_all_array("SELECT id FROM ".$pre."nested_tree WHERE personal_folder=1 AND NOT title = ".$_SESSION['user_id']);
foreach( $rows as $reccord ){
    if ( !in_array($reccord['id'],$array_pf) ) {
        //build an array of personal folders ids
        array_push($array_pf,$reccord['id']);
        //build also a string with those ids
        if (empty($list_pf)) $list_pf = $reccord['id'];
        else $list_pf .= ','.$reccord['id'];
    }
}

/* BUILD QUERY */
//Paging
$sLimit = "";
if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
        $sLimit = "LIMIT ". $_GET['iDisplayStart'] .", ". $_GET['iDisplayLength'] ;
}

//Ordering

if ( isset( $_GET['iSortCol_0'] ) )
{
        $sOrder = "ORDER BY  ";
        for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
        {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                        $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                                ".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
                }
        }

        $sOrder = substr_replace( $sOrder, "", -2 );
        if ( $sOrder == "ORDER BY" )
        {
                $sOrder = "";
        }
}

/*
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */
if ( $_GET['sSearch'] != "" )
{
    $sWhere = " WHERE ";
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
            $sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
    }
    $sWhere = substr_replace( $sWhere, "", -3 );
}

// Do NOT show the items in PERSONAL FOLDERS
if ( !empty($list_pf) ) {
    if ( empty($sWhere) ) $sWhere = " WHERE ";
    else $sWhere .= "AND ";
    $sWhere .= "id_tree NOT IN (".$list_pf.") ";
}

$sql = "SELECT SQL_CALC_FOUND_ROWS *
        FROM ".$pre."cache
        $sWhere
        $sOrder
        $sLimit";

$rResult = mysql_query( $sql ) or die(mysql_error()." ; ".$sql);    //$rows = $db->fetch_all_array("

/* Data set length after filtering */
$sql_f = "
        SELECT FOUND_ROWS()
";
$rResultFilterTotal = mysql_query( $sql_f) or die(mysql_error());
$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

/* Total data set length */
$sql_c = "
        SELECT COUNT(id)
        FROM   ".$pre."cache
";
$rResultTotal = mysql_query( $sql_c ) or die(mysql_error());
$aResultTotal = mysql_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];


/*
 * Output
 */
$sOutput = '{';
$sOutput .= '"sEcho": '.intval($_GET['sEcho']).', ';
$sOutput .= '"iTotalRecords": '.$iTotal.', ';
$sOutput .= '"iTotalDisplayRecords": '.$iFilteredTotal.', ';
$sOutput .= '"aaData": [ ';

$rows = $db->fetch_all_array($sql);
foreach( $rows as $reccord ){
    $sOutput .= "[";

    //col1
    $sOutput .= '"<img src=\"includes/images/key__arrow.png\" onClick=\"javascript:window.location.href = &#039;index.php?page=items&amp;group='.$reccord['id_tree'].'&amp;id='.$reccord['id'].'&#039;;\" style=\"cursor:pointer;\" />",';

    //col2
    $sOutput .= '"'.htmlspecialchars(stripslashes($reccord['label']), ENT_QUOTES).'",';

    //col3
    if ( $reccord['perso']==1 || !empty($reccord['restricted_to']) || !in_array($_SESSION['user_id'],explode(';',$reccord['restricted_to'])) != $_SESSION['user_id'] ){
        $sOutput .= '"<img src=\"includes/images/lock.png\" />",';
    }else{
        $txt = str_replace(array('\n','<br />','\\'),array(' ',' ',''),strip_tags(mysql_real_escape_string($reccord['description'])));
        if (strlen($txt) > 50) {
            $sOutput .= '"'.(substr(htmlspecialchars(stripslashes($txt), ENT_QUOTES), 0, 50)).'",';
        }else{
            $sOutput .= '"'.(htmlspecialchars(stripslashes($txt), ENT_QUOTES)).'",';
        }  
    }

    //col4 - TAGS
    $sOutput .= '"'.htmlspecialchars(stripslashes($reccord['tags']), ENT_QUOTES).'",';

    //col5 - Prepare the Treegrid
    $sOutput .= '"';
    $arbo = $tree->getPath($reccord['id_tree'], true);
    foreach($arbo as $elem){
        if ( $elem->title == $_SESSION['user_id'] && $elem->nlevel == 1 ) $elem->title = $_SESSION['login'];
        $sOutput .= htmlspecialchars(stripslashes($elem->title), ENT_QUOTES)." > ";
    }
    $sOutput .= '"';

    //Finish the line
    $sOutput .= '],';


}
$sOutput = substr_replace( $sOutput, "", -1 );
$sOutput .= '] }';

echo $sOutput;
?>

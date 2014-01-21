<?php
####################################################################################################
## File : admin.php
## Author : Nils Laumaill�
## Description : Admin page
## 
## DON'T CHANGE !!!
## 
####################################################################################################

echo '
    <div class="title ui-widget-content ui-corner-all">'.$txt['admin'].'</div>
    <div style="width:900px;margin-left:50px; line-height:25px;height:100%;overflow:auto;">';

    // Div for tool info
    echo '
        <div id="CPM_infos" style="float:left;margin-top:10px;margin-left:15px;width:500px;">'.$txt['admin_info_loading'].'&nbsp;<img src="includes/images/ajax-loader.gif" alt="" /></div>';
     
     //div for information
     echo '   
        <div style="float:right;width:300px;padding:10px;" class="ui-state-highlight ui-corner-all">
            <span class="ui-icon ui-icon-comment" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['support_page'].'
            <br />
            <span class="ui-icon ui-icon-wrench" style="float: left; margin-right: .3em;">&nbsp;</span>'.$txt['bugs_page'].'
            <div style="text-align:center;margin-top:10px;">
                <div style="margin:5px;"><script type="text/javascript" src="' , !empty($_SERVER['HTTPS']) ? 'https' : 'http' , '://www.ohloh.net/p/468653/widgets/project_thin_badge.js"></script></div>
                <div style="margin:5px;"><a href="://sourceforge.net/projects/communitypasswo" target="_blank"><img src="' , !empty($_SERVER['HTTPS']) ? 'https' : 'http' , '://sflogo.sourceforge.net/sflogo.php?group_id=280505&amp;type=12" width="120" height="30" alt="Get cPassMan at SourceForge.net. Fast, secure and Free Open Source software downloads" style="border:0;" /></a></div>
                <div style="margin:5px;"><a href="http://sourceforge.net/donate/index.php?group_id=280505" target="_blank"><img src="' , !empty($_SERVER['HTTPS']) ? 'https' : 'http' , '://images.sourceforge.net/images/project-support.jpg" width="88" height="32" border="0" alt="Support This Project" /> </a></div>
                '.$txt['thku'].'
            </div>
        </div>';
     
    // Display the readme file
    $Fnm = "readme.txt";
    if (file_exists($Fnm)) {
        $tab = file($Fnm);
        echo '
        <div style="float:left;width:900px;height:150px;overflow:auto;">
        <div style="float:left;" class="readme">
            <h3>'.$txt['changelog'].'</h3>';
        $show = false;
        $cnt = 0;
        while(list($cle,$val) = each($tab)) {
            if ( $show == true && $cnt < 30 ){
                echo $val."<br />";
                $cnt ++;
            }
            else if ( $cnt == 30 ){
                echo '...<br /><br /><b><a href="readme.txt" target="_blank">'.$txt['readme_open'].'</a></b>';
                break;
            }
            if ( substr_count($val,"CHANGELOG") == 1 && $show == false ) $show = true;         
        }
        echo '
        </div></div>';
    }
    echo '
    </div>';
    
?>
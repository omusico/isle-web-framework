<?php
/*
*
* GERMAN ADMIN HELP LANGUAGE FILE
* 
*/

$txt['help_on_roles'] = "<div class='ui-state-highlight ui-corner-all' style='padding:5px;font-weight:bold;'>
Auf dieser Seite k�nnen Rollen angelegt und bearbeitet werden.<br />
Einer Rolle werden bestimmte sichtbare und unsichtbare Ordner zugeordnet.<br />
Wenn Sie Rollen angelegt haben, k�nnen diese Benutzern zugeordnet werden.
</div>
<div id='accordion'>
    <h3><a href='#'>Eine neue Rolle hinzuf�gen</a></h3>
    <div>
        Klicken Sie einfach auf dieses Symbol <img src='includes/images/users--plus.png' alt='' />. Eine entsprechende Dialogbox wird erscheinen und Sie werden aufgefordert, die Bezeichnung f�r die neue Rolle anzugeben.
    </div>
    
    <h3><a href='#'>Einen Ordner freigeben oder sperren</a></h3>
    <div>
	Sie k�nnen die Ansicht 'Rollen und Ordner' verwenden um die Zugangsrechte zu definieren. Ist ein Feld rot, dann kann die Rolle den Ordner nicht �ffnen. Ist das Feld gr�n, dann ist die Rolle berechtigt, den Ordner zu �ffnen.<br />
        Um eine Berechtigung zu �ndern k�nnen Sie einfach auf das jeweilige Feld klicken.<br/>
        <p style='text-align:center;'>
            <span style='text-align:center;'><img src='includes/images/help/roles_1.png' alt='' /></span>
        </p>
        Auf diesem Screenshot ist ersichtlich, dass die Rolle 'Dev', jedoch nicht die Rolle 'Commercial' Zugriff auf den Ordner 'Cleaner' hat.
    </div>
    
    <h3><a href='#'>Die Ansicht manuell aktualisieren</a></h3>
    <div>
        Klicken Sie einfach auf dieses Symbol <img src='includes/images/arrow_refresh.png' alt='' />.
    </div>
    
    <h3><a href='#'>Eine Rolle bearbeiten</a></h3>
    <div>
	Sie k�nnen die Bezeichnung einer Rolle ohne Beeinflussung der eingegebenen Parameter �ndern.<br />
        W�hlen Sie die zu bearbeitende Rolle und klicken Sie auf dieses Symbol <img src='includes/images/ui-tab--pencil.png' alt='' />.<br />
        Es wird eine Dialogbox ge�ffnet und Sie werden nach einer neuen Bezeichnung gefragt.
    </div>
    
    <h3><a href='#'>Eine Rolle entfernen</a></h3>
    <div>
	Sie k�nnen bestehende Rollen entfernen.<br />
        W�hlen Sie die zu entfernende Rolle und klicken Sie auf dieses Symbol <img src='includes/images/ui-tab--minus.png' alt='' />.<br />
        Es wird eine Dialogbox ge�ffnet und Sie werden aufgefordert diese Aktion zu best�tigen.
    </div>";

$txt['help_on_users'] = "<div class='ui-state-highlight ui-corner-all' style='padding:5px;font-weight:bold;'>
Auf dieser Seite k�nnen Benutzer angelegt und bearbeitet werden.<br />
Jeder Benutzer ben�tigt ein Benutzerkonto, um cPassMan benutzen zu d�rfen.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>Im ersten Schritt muss dem Benutzer einige Rollen zugeordnet werden.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>im zweiten Schritt muss definiert werden, welche Ordner der Benutzer nutzen darf.
</div>
<div id='accordion'>
    <h3><a href='#'>Einen neuen Benutzer hinzuf�gen</a></h3>
    <div>
        Klicken Sie einfach auf dieses Symbol <img src='includes/images/user--plus.png' alt='' />. Eine entsprechende Dialogbox wird sich �ffnen, hier m�ssen Sie folgendes eintragen:<br />        
        - den Benutzernamen<br />
        - ein Passwort (kann beim Erstellen generiert werden, der Benutzer muss es nach der ersten Anmeldung �ndern)<br />
        - eine g�ltige Email-Adresse<br />
        - ob der Benutzer ein Administrator sein soll (voller Zugang zu allen Funktionen)<br />
        - ob der Benutzer ein Manager sein soll (volle Rechte auf Ihm zugeordnete Elemente)<br />
        - ob der Benutzer ein pers�nlichen Ordner haben soll (nur sichtbar f�r den Benutzer)  
    </div>
    <h3><a href='#'>Eine Rolle einem Benutzer zuordnen</a></h3>
    <div>
        Sie k�nnen einem Benutzer beliebig viele Rollen zuordnen. Klicken Sie einfach auf dieses Symbol <img src='includes/images/cog_edit.png' alt='' />.<br />
        Eine entsprechende Dialogbox wird sich �ffnen und Sie k�nnen die gew�nschten Rollen ausw�hlen.<br /><br />
        Wenn eine Rolle einem Benutzer zugeordnet wird, hat er Zugriff auf die Ordner, die f�r die Rolle sichtbar sind, und keinen Zugriff die unsichtbaren.<br /><br />
        Jetzt k�nnen Sie noch spezieller Rechte f�r den einzelnen Benutzer in den Feldern 'Sichtbare Ordner' und 'Versteckte Ordner' vergeben. Sie k�nnen hier Ordner freigeben oder sperren. Hier k�nnen auch andere als die in der Rolle definiert werden.
        <div style='margin:2px Opx 0px 20px;'>
            Zum Beispiel:
            <p style='margin-left:20px;margin-top: 2px;'>
            - BENUTZER1 wurden ROLLE1 und ROLLE2 zugeordnet. <br />
            - ROLLE1 hat Zugriff auf Ordner V1 und V2. <br />
            - V1 hat die vier Unterverordner U1, U2, U3 und U4.<br />
            - Das bedeutet: BENUTZER1 hat Zugriff auf V1, V2, U1, U2, U3 und U4.<br />
            - Sie k�nnen nun BENUTZER1 bearbeiten und Ihm den Zugriff auf U4 �ber diese Seite verbieten.
            </p>
        </div>
    </div>
    <h3><a href='#'>Administratoren</a></h3>
    <div>
        Sie k�nnen einem Benutzer das GOTT Recht zuweisen. W�hlen Sie einfach das K�stchen aus.<br /> 
	GOTT kann in cPassMan alles ohne jegliche Beschr�nkungen machen... seien Sie also vorsichtig!!!
        <p style='text-align:center;'>
        <img src='includes/images/help/users_1.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Manager</a></h3>
    <div>
        Sie k�nnen einem Benutzer das Manager Recht zuweisen. W�hlen Sie einfach das K�stchen aus.<br /> 
	Ein Manager kann Elemente und Ordner bearbeiten und l�schen, auch die nicht von Ihm erstellten.
	Ein Manager kann nur Ordner und Elemente betrachten die Ihm zugeordnet wurden. Sie k�nnen verschiedene Manager f�r verschiedene Bereiche definieren.   
        <p style='text-align:center;'>
        <img src='includes/images/help/users_2.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Einen Benutzer l�schen</a></h3>
    <div>
        Sie k�nnen Benutzer l�schen. Klicken Sie einfach auf dieses Symbol <img src='includes/images/user--minus.png' alt='' />.
        <p style='text-align:center;'>
        <img src='includes/images/help/users_3.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Das Benutzer-Passwort �ndern</a></h3>
    <div>
	Sie k�nnen das Passwort eines Benutzers �ndern. Klicken Sie einfach auf dieses Symbol <img src='includes/images/lock__pencil.png' alt='' />.<br /> 
	Bei der n�chsten Anmeldung muss der Benutzer das Passwort �ndern.
        <p style='text-align:center;'>
        <img src='includes/images/help/users_4.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Die Email-Adresse eines Benutzers �ndern</a></h3>
    <div>
	Sie k�nnen die Email-Adresse eines Benutzers �ndern. Klicken Sie einfach auf dieses Symbol <img src='includes/images/mail--pencil.png' alt='' />.<br />   
        <p style='text-align:center;'>
        <img src='includes/images/help/users_5.png' alt='' />
        </p>
    </div>
</div>
";

$txt['help_on_folders'] = "<div class='ui-state-highlight ui-corner-all' style='padding:5px;font-weight:bold;'>
Auf dieser Seite k�nnen Sie Ordner erstellen und bearbeiten.<br />
Ordner werden ben�tigt, um Elemente zu organisieren. Es funktioniert �hnlich wie die Verzeichnisstruktur von Windows.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>Der oberste Ordnder der Struktur hei�t ROOT.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>Alle Ordner und Unterordner bilden eine Baumstruktur.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>Jedem Ordner ist eine Verzeichnisebene in der Baumstruktur zugewiesen.
</div>
<div id='accordion'>
    <h3><a href='#'>Einen neuen Ordner hinzuf�gen</a></h3>
    <div>
        Klicken Sie einfach auf dieses Symbol <img src='includes/images/folder--plus.png' alt='' />. Eine entsprechende Dialogbox �ffnet sich und Sie m�ssen Folgendes eintragen:<br />        
        - die Bezeichnung<br />
        - �bergeordnete Ordner (jeder Ordner ist ein Unterornder eines anderen)<br />
        - die Komplexit�tsstufe (die Komplexit�tsstufe wird f�r Passw�rter verwendet. Wird ein neues Element erstellt, darf das Passwort nicht von geringerer Komplexit�t sein als vorgegeben)<br />
        - den Erneuerungsintervall in Monaten (gibt an, wann ein Passwortwechsel erfolgen muss).    
    </div>
    <h3><a href='#'>Einen bestehenden Ordner bearbeiten</a></h3>
    <div>
        Um eine bestehende Bezeichnung, die Komplexit�tsstufe, einen �bergeordnete Ordner oder den Erneuerungsintervall zu �ndern, m�ssen Sie nur in das jeweilige Feld klicken.<br />
        Dadurch k�nnen Sie das Feld bearbeiten. �ndern Sie den Wert und klicken Sie auf folgendes Symbol <img src='includes/images/disk_black.png' alt='' /> um zu speichern, oder auf das folgende <img src='includes/images/cross.png' alt='' /> um die �nderungen zu verwerfen.<br />
        <p style='text-align:center;'>
        <img src='includes/images/help/folders_1.png' alt='' />
        </p>
        <div style='margin:10px Opx 0px 20px;'>
            Bitte beachten Sie: wenn Sie einen �bergeordneten Ordner �ndern, werden alle Unterordner ge�ndert (verschoben).
        </div>
    </div>
    <h3><a href='#'>Einen Ordner l�schen</a></h3>
    <div>
        Sie k�nnen Ordner l�schen, indem Sie auf folgendes Symbol klicken <img src='includes/images/folder--minus.png' alt='' />.<br /> 
        Dadurch werden alle Unterordner und alle Elemente darin gel�scht ... seien Sie also vorsichtig!!!!
        <p style='text-align:center;'>
        <img src='includes/images/help/folders_2.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Spezielle Einstellungen</a></h3>
    <div>
        Es existieren zwei spezielle Einstellungen f�r Ordner.<br />
        Die erste erlaubt es, Passw�rter ungeachtet der eingestellten Komplexit�tsstufe zu erstellen.<br /> 
        Die zweite erlaubt es, Passw�rter ungeachtet der eingestellten Komplexit�tsstufe zu �ndern.<br /> 
        Beide Einstellungen k�nnen kombiniert werden.<br />
        Sie k�nnen auch nur tempor�r verwendet werden.   
        <p style='text-align:center;'>
        <img src='includes/images/help/folders_3.png' alt='' />
        </p>
    </div>
</div>
";
?>

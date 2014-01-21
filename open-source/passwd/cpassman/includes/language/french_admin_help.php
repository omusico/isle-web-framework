<?php
/*
*
* FRENCH ADMIN HELP LANGUAGE FILE
* 
*/

$txt['help_on_roles'] = "<div class='ui-state-highlight ui-corner-all' style='padding:5px;font-weight:bold;'>
Cette page est utilis�e pour cr�er et modifier les ROLES.<br />
Un role est associ� � un ensemble de r�pertoires autoris�s et interdits.<br />
Une fois plusieurs roles param�tr�s, vous pouvez les utiliser pour les associer � un compte utilisateur.
</div>
<div id='accordion'>
    <h3><a href='#'>Ajouter un ROLE</a></h3>
    <div>
        Cliquer sur l'icone <img src='includes/images/users--plus.png' alt='' />. Une boite de dialogue sp�cifique vous demandera de saisir l'intitul� de ce nouveau role.
    </div>
    
    <h3><a href='#'>Autoriser ou interdire un REPERTOIRE</a></h3>
    <div>
        Vous devez utiliser la matrice 'Roles / R�pertoires' pour d�finir les droits d'acc�s des roles. Si la couleur de la cellule est rouge, alors le role ne pourra pas acc�der � ce r�pertoire, et si la cellule est verte, alors le role pourra acc�der � la cellule.<br />
        Pour changer le droit d'acces d'un role � un r�pertoire, il suffit de cliquer dessus.<br/>
        <p style='text-align:center;'>
            <span style='text-align:center;'><img src='includes/images/help/roles_1.png' alt='' /></span>
        </p>
        Dans la capture d'�cran, vous voyez que le r�pertoire 'Cleaner' est autoris� pour le role 'Dev' mais qu'il ne l'est pas pour le role 'Commercial'.
    </div>
    
    <h3><a href='#'>Rafraichir manuellement la matrice</a></h3>
    <div>
        Il vous suffit de cliquer sur l'icone <img src='includes/images/arrow_refresh.png' alt='' />.
    </div>
    
    <h3><a href='#'>Editer un role</a></h3>
    <div>
        Il est possible de changer l'intitul� d'un role sans aucun impact sur les diff�rents param�trages effectu�s..<br />
        Selectionner le role que vous voulez renomm� et cliquer sur l'icone <img src='includes/images/ui-tab--pencil.png' alt='' />.<br />
        Cela ouvrira une boite de dialogue dans laquelle vous pourrez saisir le nouvel intitul�.
    </div>
    
    <h3><a href='#'>Supprimer un role</a></h3>
    <div>
        Vous pouvez tout � fait supprimer un role. Cela aura pour effet de supprimer ce role de chaque utilisateur le poss�dant.<br />
        Selectionner le role que vous voulez supprim� et cliquer sur l'icone <img src='includes/images/ui-tab--minus.png' alt='' />.<br />
        Cela ouvrira une boite de dialogue dans laquelle vous devrez confirmer la suppression.
    </div>
</div>";

$txt['help_on_users'] = "<div class='ui-state-highlight ui-corner-all' style='padding:5px;font-weight:bold;'>
Cette page est utilis�e pour cr�er et g�rer les UTILISATEURS.<br />
Un compte utilisateur est n�cessaire pour chaque personne physique devant utiliser cPassMan.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>La 1�re �tape consiste � associer l'utilisateur � un ou plusieurs roles.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>La 2de �tape (optionnelle) consiste � d�finir les r�pertoires sp�cifiques auxquels l'utilisateur peut avoir acc�s.
</div>
<div id='accordion'>
    <h3><a href='#'>Ajouter un UTILISATEUR</a></h3>
    <div>
        Cliquer sur l'icone <img src='includes/images/user--plus.png' alt='' />. Dans la boite de dialogue, il conviendra de saisir :<br />        
        - l'identifiant de connexion de l'utilisateur<br />
        - un mot de passe (peut etre g�n�rer automatiquement et sera obligatoirement chang� � la 1�re connexion)<br />
        - un email valide<br />
        - si l'utilisateur sera un administrateur (acc�s sans limite aux fonctionnalit�s)<br />
        - si l'utilisateur sera un Manager (tous les droits sur les �l�ments accessibles)<br />
        - si l'utilisateur peut avoir acc�s � des r�pertoires personnels 
    </div>
    <h3><a href='#'>Ajouter un ROLE � un UTILISATEUR</a></h3>
    <div>
        Vous pouvez associer un UTILISATEUR � autant de ROLES que vous voulez. Pour cela, il suffit de cliquer sur l'icone <img src='includes/images/cog_edit.png' alt='' />.<br />
        Une boite de dialogue vous permettra de s�lectionner les roles d�sir�s.<br /><br />
        Quand un role est ajout� � un utilisateur ce dernier aura alors la possibilit� de consulter les �l�ments des r�pertoires autoris�s et n'aura pas acc�s � ceux qui se trouvent dans les r�pertoires interdits.<br /><br />
        Maintenant il est possible d'etre beaucoup plus pr�cis en associant en plus des roles des r�pertoires autoris�s et inerdits pour chaque utilisateur. En effet, vous pouvez autoriser et interdire d'autres r�pertoires que ceux pr�sents dans la d�finition des ROLES.
        <div style='margin:2px Opx 0px 20px;'>
            Par exemple :
            <p style='margin-left:20px;margin-top: 2px;'>
            - UTILISATEUR1 est associ� au ROLE1 et ROLE2. <br />
            - ROLE1 donne acc�s aux r�pertoires R1 et R2. <br />
            - R1 poss�de 4 sous r�pertoires S1, S2, S3 et S4.<br />
            - Cela signifie que l'UTILISATEUR1 a acc�s � F1, F2, S1, S2, S3 et S4.<br />
            - Vous pouvez �galement param�trer UTILISATEUR1 pour qu'il ne puisse pas acc�der � S4.
            </p>
        </div>
    </div>
    <h3><a href='#'>Est Administrateur (DIEU)</a></h3>
    <div>
        Vous pouvez autoriser tout utilisateur � etre DIEU. Pour cela, vous n'avez qu'� cocher la case correspondante.<br /> 
        Attention cependant car un utilisateur DIEU peut acc�der � toutes les fonctionnalit�s de cPassMan !!!!
        <p style='text-align:center;'>
        <img src='includes/images/help/users_1.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Est Manager</a></h3>
    <div>
        Vous pouvez autoriser tout utilisateur � etre MANAGER. Pour cela, vous n'avez qu'� cocher la case correspondante.<br /> 
        Un Manager peut modifier et supprimer des �l�ments et des r�pertoires, y compris ceux qu'il n'a pas cr��.<br /> 
        Un Manager n'a cependant acc�s qu'aux r�pertoires qu'il est autoris� � voir. Il est donc possible de cr�er plusieurs Managers en fonction des Services par exemple.    
        <p style='text-align:center;'>
        <img src='includes/images/help/users_2.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Supprimer un UTILISATEUR</a></h3>
    <div>
        Vous pouvez supprimer un utilisateur. Pour cela, il suffit de cliquer sur l'icone <img src='includes/images/user--minus.png' alt='' />.
        <p style='text-align:center;'>
        <img src='includes/images/help/users_3.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Changer le mot de passe d'un utilisateur</a></h3>
    <div>
        Il est tout � fait possible pour un administrateur de changer le mot de passe d'un utilisateur. Pour cela, il suffit de cliquer sur l'icone <img src='includes/images/lock__pencil.png' alt='' />.<br /> 
        A la 1ere connexion de l'utilisateur, il devra la modifier. 
        <p style='text-align:center;'>
        <img src='includes/images/help/users_4.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Changer l'email d'un utilisateur</a></h3>
    <div>
        Il est tout � fait possible pour un administrateur de changer l'email d'un utilisateur. Pour cela, il suffit de cliquer sur l'icone <img src='includes/images/mail--pencil.png' alt='' />.<br />   
        <p style='text-align:center;'>
        <img src='includes/images/help/users_5.png' alt='' />
        </p>
    </div>
</div>
";

$txt['help_on_folders'] = "<div class='ui-state-highlight ui-corner-all' style='padding:5px;font-weight:bold;'>
Cette page est utilis�e pour cr�er et g�rer les REPERTOIRES.<br />
Un r�pertoire est n�cessaire pour organiser et structurer vos �l�ments. Il est similaire � un r�pertoire de fichiers de Windows.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>Le niveau le plus bas est appel� la RACINE.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>L'ensemble des r�pertoires et sous-r�pertoires constitue l'arborescence.<br />
<span class='ui-icon ui-icon-lightbulb' style='float: left;'>&nbsp;</span>Chaque r�pertoire est associ� � un niveau de profondeur dans l'arborescence.
</div>
<div id='accordion'>
    <h3><a href='#'>Ajouter un REPERTOIRE</a></h3>
    <div>
        Cliquer sur l'icone <img src='includes/images/folder--plus.png' alt='' />. Une boite de dialogue vous permettra de saisir :<br />        
        - l'intitul� du r�pertoire<br />
        - le r�pertoire parent (chaque r�pertoire �tant associ� � un autre r�pertoire parent)<br />
        - un niveau de complexit� (celui-ci est utilis� pour la complexit� des mots de passe. Quand un utilisateur cr�� un nouvel �l�ment, le mot de passe associ� doit au moins r�pondre � ce crit�re de complexit�)<br />
        - une p�riode de renouvellement exprim�e en mois (est n�cessaire pour demander un renouvellement des mots de passe).    
    </div>
    <h3><a href='#'>Editer un r�pertoire existant</a></h3>
    <div>
        De fa�on � changer un intitul�, la complexit�, le r�pertoire parent ou la p�riode de renouvellement d'un r�pertoire, vous devez cliquer sur la cellule correspondante.<br />
        Cela rendre la cellule modifiable. Changer alors la valeur et cliquer sur l'icone <img src='includes/images/disk_black.png' alt='' /> pour sauvegarder, ou sur l'icone <img src='includes/images/cross.png' alt='' /> pour annuler.<br />
        <p style='text-align:center;'>
        <img src='includes/images/help/folders_1.png' alt='' />
        </p>
        <div style='margin:10px Opx 0px 20px;'>
            Si vous d�cidez de changer le r�pertoire parent alors tous les sous-r�pertoires seront �galement d�plac�s.
        </div>
    </div>
    <h3><a href='#'>Supprimer un r�pertoire</a></h3>
    <div>
        Vous pouvez supprimer un r�pertoire. Pour cela, il suffit de cliquer sur l'icone <img src='includes/images/folder--minus.png' alt='' />.<br /> 
        Attention car cela aura pour cons�quence de supprimer �galement tous les �l�ments et les sous-r�pertoires !!!!
        <p style='text-align:center;'>
        <img src='includes/images/help/folders_2.png' alt='' />
        </p>
    </div>
    <h3><a href='#'>Astuces sp�ciales</a></h3>
    <div>
        2 astuces existent sur les r�pertoires.<br />
        La 1�re autorise la cr�ation d'un �l�ment sans avoir � respecter la complexit� minimal du mot de passe.<br /> 
        La 2de autorise la modification d'un �l�ment sans avoir � respecter la complexit� minimal du mot de passe.<br /> 
        Vous pouvez �galement combiner les 2..<br />
        Vous pouvez �galement l'utiliser temporairement
        .   
        <p style='text-align:center;'>
        <img src='includes/images/help/folders_3.png' alt='' />
        </p>
    </div>
</div>
";
?>

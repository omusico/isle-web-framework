<?php
####################################################################################################
## File : items.load.php
## Author : Nils Laumaillé
## Description : Loads things depending on the page ITEMS. It is called by items.php page.
##
## DON'T CHANGE !!!
##
####################################################################################################
?>

<script type="text/javascript">

    function AddNewNode(){
        //Select first child node in tree
        $('#2').click();
        //Add new node to selected node
        simpleTreeCollection.get(0).addNode(1,'A New Node')
    }

    function EditNode(){
        //Select first child node in tree
        $('#2').click();
        //Add new node to selected node
        simpleTreeCollection.get(0).addNode(1,'A New Node')
    }

    function DeleteNode(){
        //Select first child node in tree
        $('#2').click();
        //Add new node to selected node
        simpleTreeCollection.get(0).delNode()
    }

    function showItemsInTree(type){
        if ( document.getElementById('img_funnel').src == "includes/images/funnel_plus.png" )
            document.getElementById('img_funnel').src="includes/images/funnel_minus.png"
        else
            document.getElementById('img_funnel').src="includes/images/funnel_plus.png"
    }

    //FUNCTION mask/unmask passwords characters
    function ShowPassword(){
        if ( document.getElementById('id_pw').innerHTML.indexOf('*',1) > 0 )
            document.getElementById('id_pw').innerHTML = document.getElementById('hid_pw').value;
        else
            document.getElementById('id_pw').innerHTML = document.getElementById('id_pw').innerHTML.replace(/./g, " * ");
    }


//###########
//## FUNCTION : Launch the listing of all items of one category
//###########
function ListerItems(groupe_id){
    if ( groupe_id != undefined ){
        LoadingPage();

        //clean form
        document.getElementById('id_label').innerHTML = "";
        document.getElementById('id_pw').innerHTML = "";
        document.getElementById('id_url').innerHTML = "";
        document.getElementById('id_desc').innerHTML = "";
        document.getElementById('id_login').innerHTML = "";
        document.getElementById('id_info').innerHTML = "";
        document.getElementById('id_restricted_to').innerHTML = "";
        document.getElementById('id_files').innerHTML = "";
        document.getElementById('id_tags').innerHTML = "";

        //Disable menu buttons
        $('#menu_button_edit_item,#menu_button_del_item,#menu_button_add_fav,#menu_button_del_fav,#menu_button_show_pw,#menu_button_copy_pw,#menu_button_copy_login,#menu_button_copy_link,#menu_button_copy_item').attr('disabled', 'disabled');

        //ajax query
        var data = "type=lister_items_groupe"+
                    "&id="+groupe_id;
        httpRequest("sources/items.queries.php",data);
    }
}

function pwGenerate(elem){
    if ( elem != "" ) elem = elem+"_";

    //show ajax image
    $("#"+elem+"pw_wait").show();

    var data = "type=pw_generate"+
                "&size="+$("#"+elem+'pw_size').text()+
                "&num="+document.getElementById(elem+'pw_numerics').checked+
                "&maj="+document.getElementById(elem+'pw_maj').checked+
                "&symb="+document.getElementById(elem+'pw_symbols').checked+
                "&secure="+document.getElementById(elem+'pw_secure').checked+
                "&elem="+elem;
    httpRequest("sources/items.queries.php",data+"&force=false");
}

function pwCopy(elem){
    if ( elem != "" ) elem = elem+"_";
    document.getElementById(elem+'pw2').value = document.getElementById(elem+'pw1').value;
}

function catSelected(val){
    document.getElementById("hid_cat").value= val;
}

function RecupComplexite(val,edit){
    var data = "type=recup_complex"+
                "&groupe="+val+
                "&edit="+edit;
    httpRequest("sources/items.queries.php",data);
}

function AjouterItem(){
    document.getElementById('error_detected').value = '';   //Refresh error foolowup
    var erreur = "";
    var  reg=new RegExp("[.|;|:|!|=|+|-|*|/|#|\"|'|&|]");

    if ( document.getElementById("label").value == "" ) erreur = "<?php echo $txt['error_label'];?>";
    else if ( document.getElementById("pw1").value == "" ) erreur = "<?php echo $txt['error_pw'];?>";
    else if ( document.getElementById("categorie").value == "na" ) erreur = "<?php echo $txt['error_group'];?>";
    else if ( document.getElementById("pw1").value != document.getElementById("pw2").value ) erreur = "<?php echo $txt['error_confirm'];?>";
    else if ( document.getElementById("item_tags").value != "" && reg.test(document.getElementById("item_tags").value) ) erreur = "<?php echo $txt['error_tags'];?>";
    else{
        //vérifier le niveau de complexité du mdp
        if (
            ( document.getElementById("bloquer_creation_complexite").value == 0 && parseInt(document.getElementById("mypassword_complex").value) >= parseInt(document.getElementById("complexite_groupe").value) )
            ||
            ( document.getElementById("bloquer_creation_complexite").value == 1 )
            ||
            ( $('#recherche_group_pf').val() == 1 && $('#personal_sk_set').val() == 1 )
        ){

            LoadingPage();  //afficher image de chargement
            var annonce = 0;
            if ( document.getElementById('annonce').checked ) annonce = 1;

            //gérer les restrictions
            var myselect = document.getElementById('restricted_to_list');
            var restriction = "";
            for (var loop=0; loop < myselect.options.length; loop++) {
                if (myselect.options[loop].selected == true && myselect.options[loop].value != "") restriction = restriction + myselect.options[loop].value + ";";
            }
            if ( restriction != "" && restriction.indexOf(document.getElementById('form_user_id').value) == "-1" )
                restriction = document.getElementById('form_user_id').value+";"+restriction
            if ( restriction == ";" ) restriction = "";

            //gérer la liste de diffusion
            var myselect = document.getElementById('annonce_liste_destinataires');
            var diffusion = "";
            for (var loop=0; loop < myselect.options.length; loop++) {
                if (myselect.options[loop].selected == true) diffusion = diffusion + myselect.options[loop].value + ";";
            }
            if ( diffusion == ";" ) diffusion = "";

            if (CKEDITOR.instances["desc"]) {
            	var description = CKEDITOR.instances["desc"].getData();
            }else{
            	var description = $("#desc").val();
            }
            
            //encrypt some fileds
            //var data_to_send = "&pw="+aes_encrypt(escape($('#pw1').val()))+"&label="+aes_encrypt(escape($('#label').val()))+"&login="+aes_encrypt(escape($('#item_login').val()));

            var data = "type=new_item"+
                        "&pw="+encodeURIComponent($('#pw1').val())+
                        "&label="+escape(document.getElementById('label').value)+
                        "&desc="+escape(description)+
                        "&url="+escape(document.getElementById('url').value)+
                        "&login="+escape($('#item_login').val())+
                        "&annonce="+annonce+
                        "&diffusion="+diffusion+
                        "&categorie="+document.getElementById('categorie').value+
                        "&restricted_to="+restriction+
                        "&tags="+document.getElementById('item_tags').value+
                        "&salt_key_set="+$('#personal_sk_set').val()+
                        "&is_pf="+$('#recherche_group_pf').val()+
                        "&anyone_can_modify="+$('#anyone_can_modify:checked').val()+
                        "&random_id_from_files="+document.getElementById('random_id').value;
            httpRequest("sources/items.queries.php",data);
            //Clear upload queue
            $('#item_file_queue').html('');
            //Select 1st tab
            $( "#item_tabs" ).tabs({ selected: 0 });
            //Close dialognox
            $("#div_formulaire_saisi").dialog('close');
        }else{
            document.getElementById('new_show_error').innerHTML = "<?php echo $txt['error_complex_not_enought'];?>";
            $("#new_show_error").show();
        }
    }
    if ( erreur != "") {
        document.getElementById('new_show_error').innerHTML = erreur;
        $("#new_show_error").show();
    }
}

function EditerItem(){
    var erreur = "";
    var  reg=new RegExp("[.|,|;|:|!|=|+|-|*|/|#|\"|'|&]");
    if ( document.getElementById("edit_label").value == "" ) erreur = "<?php echo $txt['error_label'];?>";
    else if ( document.getElementById("edit_pw1").value == "" ) erreur = "<?php echo $txt['error_pw'];?>";
    else if ( document.getElementById("edit_pw1").value != document.getElementById("edit_pw2").value ) erreur = "<?php echo $txt['error_confirm'];?>";
    else if ( document.getElementById("edit_tags").value != "" && reg.test(document.getElementById("edit_tags").value) ) erreur = "<?php echo $txt['error_tags'];?>";
    else{
        //vérifier le niveau de complexité du mdp
        if ( (
                document.getElementById("bloquer_modification_complexite").value == 0 &&
                parseInt(document.getElementById("edit_mypassword_complex").value) >= parseInt(document.getElementById("complexite_groupe").value)
            )
            ||
            ( document.getElementById("bloquer_modification_complexite").value == 1 )
            ||
            ($('#recherche_group_pf').val() == 1 && $('#personal_sk_set').val() == 1)
        ){
            LoadingPage();  //afficher image de chargement
            var annonce = 0;
            if ( document.getElementById('edit_annonce').checked ) annonce = 1;

            //gérer les restrictions
            var myselect = document.getElementById('edit_restricted_to_list');
            var restriction = "";
           for (var loop=0; loop < myselect.options.length; loop++) {
                if (myselect.options[loop].selected == true && myselect.options[loop].value != "") restriction = restriction + myselect.options[loop].value + ";";
            }
            if ( restriction == ";" ) restriction = "";

            //gérer la liste de diffusion
            var myselect = document.getElementById('edit_annonce_liste_destinataires');
            var diffusion = "";
            for (var loop=0; loop < myselect.options.length; loop++) {
                if (myselect.options[loop].selected == true) diffusion = diffusion + myselect.options[loop].value + ";";
            }
            if ( diffusion == ";" ) diffusion = "";

            if (CKEDITOR.instances["edit_desc"]) {
            	var description = CKEDITOR.instances["edit_desc"].getData();
            }else{
            	var description = $("#edit_desc").val();
            }
            
            //encrypt some fileds
            //var data_to_send = "&pw="+aes_encrypt(escape($('#edit_pw1').val()))+"&label="+aes_encrypt(escape($('#edit_label').val()))+"&login="+aes_encrypt(escape($('#edit_item_login').val()));
                        
            //Send query
            var data = "type=update_item"+
                        "&pw="+encodeURIComponent($('#edit_pw1').val())+
                        "&label="+escape(document.getElementById('edit_label').value)+
                        "&description="+description+
                        "&url="+escape(document.getElementById('edit_url').value)+
                        "&login="+escape(document.getElementById('edit_item_login').value)+
                        "&categorie="+escape(document.getElementById('edit_categorie').value)+
                        "&annonce="+annonce+
                        "&diffusion="+diffusion+
                        "&id="+document.getElementById('id_item').value+
                        "&restricted_to="+restriction+
                        "&salt_key_set="+$('#personal_sk_set').val()+
                        "&is_pf="+$('#recherche_group_pf').val()+
                        "&anyone_can_modify="+$('#edit_anyone_can_modify:checked').val()+
                        "&tags="+document.getElementById('edit_tags').value;
            httpRequest("sources/items.queries.php",data);





            //Clear upload queue
            $('#item_edit_file_queue').html('');
            //Select 1st tab
            $( "#item_edit_tabs" ).tabs({ selected: 0 });
            //Close dialogbox
            $("#div_formulaire_edition_item").dialog('close');
        }else{
            document.getElementById('edit_show_error').innerHTML = "<?php echo $txt['error_complex_not_enought'];?>";
            $("#edit_show_error").show();
        }
    }

    if ( erreur != "") {
        document.getElementById('edit_show_error').innerHTML = erreur;
        $("#edit_show_error").show();
    }
}

function aes_encrypt(text) {
    return Aes.Ctr.encrypt(text, "<?php echo $_SESSION['cle_session'];?>", 256);
}

function AjouterFolder(){
    if ( document.getElementById("new_rep_titre").value == "0" ) alert("<?php echo $txt['error_group_label'];?>");
    else if ( document.getElementById("new_rep_complexite").value == "" ) alert("<?php echo $txt['error_group_complex'];?>");
    else{
    	LoadingPage();
    	if ($("#new_rep_role").val() == undefined) {
    		role_id = "<?php echo $_SESSION['fonction_id'];?>";
    	}else{
    		role_id = $("#new_rep_role").val();
    	}
        var data = "type=new_rep"+
                    "&title="+escape(document.getElementById('new_rep_titre').value)+
                    "&complexite="+escape(document.getElementById('new_rep_complexite').value)+
                    "&groupe="+document.getElementById("new_rep_groupe").value+
                    "&role_id="+role_id;
        httpRequest("sources/items.queries.php",data);
    }
}

function EditerFolder(){
    if ( document.getElementById("edit_rep_titre").value == "" ) alert("<?php echo $txt['error_group_label'];?>");
    else if ( document.getElementById("edit_rep_groupe").value == "0" ) alert("<?php echo $txt['error_group'];?>");
    else if ( document.getElementById("edit_rep_complexite").value == "" ) alert("<?php echo $txt['error_group_complex'];?>");
    else{
        var data = "type=update_rep"+
                    "&title="+escape(document.getElementById('edit_rep_titre').value)+
                    "&complexite="+escape(document.getElementById('edit_rep_complexite').value)+
                    "&groupe="+document.getElementById("edit_rep_groupe").value;
        httpRequest("sources/items.queries.php",data);
    }
}

function SupprimerFolder(){
    if ( document.getElementById("delete_rep_groupe").value == "0" ) alert("<?php echo $txt['error_group'];?>");
    else if ( confirm("<?php echo $txt['confirm_delete_group'];?>") ) {
        var data = "type=delete_rep"+
                    "&groupe="+document.getElementById("delete_rep_groupe").value;
        httpRequest("sources/items.queries.php",data);
    }
}

function AfficherDetailsItem(id, salt_key_required, expired_item){
    LoadingPage();  //afficher image de chargement
    if ( document.getElementById("is_admin").value == "1" ){
        $('#menu_button_edit_item,#menu_button_del_item,#menu_button_copy_item').attr('disabled', 'disabled');
    }

    //Check if personal SK is needed and set
    if ( ($('#recherche_group_pf').val() == 1 && $('#personal_sk_set').val() == 0) && salt_key_required == 1 ){
    	$("#div_dialog_message_text").html("<div style='font-size:16px;'><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'><\/span><?php echo $txt['alert_message_personal_sk_missing'];?><\/div>");
    	LoadingPage();
    	$("#div_dialog_message").dialog("open");
    }else if ($('#recherche_group_pf').val() == 0 || ($('#recherche_group_pf').val() == 1 && $('#personal_sk_set').val() == 1)) {
    	var data = "type=show_details_item"+
                "&id="+id+
                "&salt_key_required="+$('#recherche_group_pf').val()+
                "&salt_key_set="+$('#personal_sk_set').val()+
                "&expired_item="+expired_item;
        httpRequest("sources/items.queries.php",data);
    }
}


//Gérer l'affichage d'une recherche
function AfficherRecherche(){
    if ( document.getElementById('recherche_id').value != "" ){
        ListerItems(document.getElementById('recherche_groupe').value);
        AfficherDetailsItem(document.getElementById('recherche_id').value);
    }else if ( document.getElementById('recherche_groupe').value != "" ){
        ListerItems(document.getElementById('recherche_groupe').value);
    }else
        ListerItems(<?php echo $first_group;?>);
}

/*
   * FUNCTION
   * Launch an action when clicking on a quick icon
   * $action = 0 => Make not favorite
   * $action = 1 => Make favorite
*/
function ActionOnQuickIcon(id, action){
	//change quick icon
	if (action == 1) {
		$("#quick_icon_fav_"+id).html("<img src='includes/images/mini_star_enable.png' onclick='ActionOnQuickIcon("+id+",0)' //>");
	}else if (action == 0) {
		$("#quick_icon_fav_"+id).html("<img src='includes/images/mini_star_disable.png' onclick='ActionOnQuickIcon("+id+",1)' //>");
	}


	var data = "type=action_on_quick_icon"+
               "&id="+id+
               "&action="+action;
    httpRequest("sources/items.queries.php",data);
}

//###########
//## FUNCTION : prepare new folder dialogbox
//###########
function open_add_group_div() {
	//Select the actual forlder in the dialogbox
	$('#new_rep_groupe').val($('#hid_cat').val());
    $('#div_ajout_rep').dialog('open');
}

//###########
//## FUNCTION : prepare editing folder dialogbox
//###########
function open_edit_group_div() {
	//Select the actual forlder in the dialogbox
	$('#edit_rep_groupe').val($('#hid_cat').val());
    $('#div_editer_rep').dialog('open');
}

//###########
//## FUNCTION : prepare delete folder dialogbox
//###########
function open_del_group_div() {
    $('#div_supprimer_rep').dialog('open');
}

//###########
//## FUNCTION : prepare new item dialogbox
//###########
function open_add_item_div() {
    LoadingPage();

    //Check if personal SK is needed and set
    if ( $('#recherche_group_pf').val() == 1 && $('#personal_sk_set').val() == 0 ){
    	$("#div_dialog_message_text").html("<div style='font-size:16px;'><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'><\/span><?php echo $txt['alert_message_personal_sk_missing'];?><\/div>");
    	LoadingPage();
    	$("#div_dialog_message").dialog("open");
    }else if ($('#recherche_group_pf').val() == 0 || ($('#recherche_group_pf').val() == 1 && $('#personal_sk_set').val() == 1)) {
	    //Select the actual forlder in the dialogbox
	    $('#categorie').val($('#hid_cat').val());
	    //Get the associated complexity level
	    RecupComplexite($('#hid_cat').val(),0);
	   //Rebuild the description editor
	   var data = "type=rebuild_description_textarea"+
					"&id=desc";
	   httpRequest("sources/items.queries.php",data);
	}
}

//###########
//## FUNCTION : prepare editing item dialogbox
//###########
function open_edit_item_div() {
    LoadingPage();
    $('#edit_display_title').html(document.getElementById('hid_label').value);
    document.getElementById('edit_label').value = document.getElementById('hid_label').value;
	document.getElementById('edit_desc').value = document.getElementById('hid_desc').value;
	document.getElementById('edit_pw1').value = document.getElementById('hid_pw').value;
	document.getElementById('edit_pw2').value = document.getElementById('hid_pw').value;
	document.getElementById('edit_item_login').value = document.getElementById('hid_login').value;
	document.getElementById('edit_url').value = document.getElementById('hid_url').value;
	document.getElementById('edit_categorie').value = document.getElementById('id_categorie').value;
	document.getElementById('edit_restricted_to').value = document.getElementById('hid_restricted_to').value;
	document.getElementById('edit_tags').value = document.getElementById('hid_tags').value;
	if (document.getElementById('hid_anyone_can_modify').value == "1") {
		$('#edit_anyone_can_modify').attr("checked","checked");
		$('#edit_anyone_can_modify').button("refresh");
	}else{
		$('#edit_anyone_can_modify').attr("checked",false);
		$('#edit_anyone_can_modify').button("refresh");
	}
	//document.getElementById('edit_personal_salt_key').value = "<?php if (isset($_SESSION['my_sk'])) echo $_SESSION['my_sk'];?>";

	//recharger la complexité du mdp affiché
	runPassword(document.getElementById('edit_pw1').value, 'edit_mypassword');

	//récupérer la complexité des mdp de ce groupe
	RecupComplexite(document.getElementById('hid_cat').value,1);

	//charger la liste des personnes dans la liste de restriction
	var myselect = document.getElementById('edit_restricted_to_list');
	myselect.options.length = 0;
	var liste = document.getElementById('input_liste_utilisateurs').value.split(';');
	for (var i=0; i<liste.length; i++) {
	    var elem = liste[i].split('.');
	    if ( elem[0] != "" ){
	        myselect.options[myselect.options.length] = new Option(elem[1], elem[0]);
	        var index = document.getElementById('edit_restricted_to').value.lastIndexOf(elem[0]+";");
	        if ( index != -1 ) {
	            myselect.options[i].selected = true;
	        }else myselect.options[i].selected = false;
	    }
	}

	//Rebuild the description editor
	var data = "type=rebuild_description_textarea"+
		"&id=edit_desc";
	httpRequest("sources/items.queries.php",data);
}

//###########
//## FUNCTION : prepare new item dialogbox
//###########
function open_del_item_div() {
    $('#div_del_item').dialog('open');
}

//###########
//## FUNCTION : copy an existing item
//###########
function open_copy_item_div() {
    LoadingPage();
    $('#display_title').html(document.getElementById('hid_label').value);
    document.getElementById('label').value = document.getElementById('hid_label').value;
    document.getElementById('desc').value = document.getElementById('hid_desc').value;
    document.getElementById('pw1').value = document.getElementById('hid_pw').value;
    document.getElementById('pw2').value = document.getElementById('hid_pw').value;
    document.getElementById('item_login').value = document.getElementById('hid_login').value;
    document.getElementById('url').value = document.getElementById('hid_url').value;
    document.getElementById('categorie').value = document.getElementById('id_categorie').value;
    document.getElementById('restricted_to').value = document.getElementById('hid_restricted_to').value;
    document.getElementById('item_tags').value = document.getElementById('hid_tags').value;
    //document.getElementById('personal_salt_key').value = "<?php if (isset($_SESSION['my_sk'])) echo $_SESSION['my_sk'];?>";

    //recharger la complexité du mdp affiché
    runPassword(document.getElementById('pw1').value, 'mypassword');

    //récupérer la complexité des mdp de ce groupe
    RecupComplexite(document.getElementById('hid_cat').value,0);

    //charger la liste des personnes dans la liste de restriction
    var myselect = document.getElementById('restricted_to_list');
    myselect.options.length = 0;
    var liste = document.getElementById('input_liste_utilisateurs').value.split(';');
    for (var i=0; i<liste.length; i++) {
        var elem = liste[i].split('.');
        if ( elem[0] != "" ){
            myselect.options[myselect.options.length] = new Option(elem[1], elem[0]);
            var index = document.getElementById('restricted_to').value.lastIndexOf(elem[0]+";");
            if ( index != -1 )
                myselect.options[i].selected = true;
            else
                myselect.options[i].selected = false;
        }
    }

    //Rebuild the description editor
    var data = "type=rebuild_description_textarea"+
    "&id=desc";
    httpRequest("sources/items.queries.php",data);
}

//###########
//## FUNCTION : Clear HTML tags from a string
//###########
function clear_html_tags(){
    var data = "type=clear_html_tags"+
                "&id_item="+document.getElementById('id_item').value;
    httpRequest("sources/items.queries.php",data);
}

//###########
//## FUNCTION : Permits to start uploading files in EDIT ITEM mode
//###########
function upload_attached_files_edit_mode() {
    // Pass dynamic ITEM id
    var post_id = $('#selected_items').val();
    var user_id = $('#form_user_id').val();

    $('#item_edit_files_upload').uploadifySettings('scriptData', {'post_id':post_id,'user_id':user_id,'type':'modification'});

    // Launch upload
    $("#item_edit_files_upload").uploadifyUpload();
}

//###########
//## FUNCTION : Permits to start uploading files in NEW ITEM mode
//###########
function upload_attached_files() {
    // Pass dynamic ITEM id
    var post_id  = "";
    var user_id = $('#form_user_id').val();

    //generate fake id if needed
    if ( document.getElementById("random_id").value == "" ) var post_id = CreateRandomString(9,"num");
    else var post_id = $("#random_id").val();

    //Save fake id
    $("#random_id").val(post_id);

    $('#item_files_upload').uploadifySettings('scriptData', {'post_id':post_id,'user_id':user_id,'type':'creation'});

    // Launch upload
    $("#item_files_upload").uploadifyUpload();
}

//###########
//## FUNCTION : Permits to delete an attached file
//###########
function delete_attached_file(file_id){
    var data = "type=delete_attached_file"+
                "&file_id="+file_id;
    httpRequest("sources/items.queries.php",data);
}

//###########
//## FUNCTION : Permits to preview an attached image
//###########
PreviewImage = function(uri,title) {

  //Get the HTML Elements
  imageDialog = $("#dialog_files");
  imageTag = $('#image_files');

  //Split the URI so we can get the file name
  uriParts = uri.split("/");

  //Set the image src
  imageTag.attr('src', uri);

  //When the image has loaded, display the dialog
  imageTag.load(function(){

  imageDialog.dialog({
      modal: true,
      resizable: false,
      draggable: false,
      width: 'auto',
      title: title
    });
  });
}

function copy_to_clipboard(elem_to_copy,item_id,icon_id){
    if ( $("#clipboard_loaded_"+icon_id).val() == "" ){
        var data = "type=copy_to_clipboard"+
                    "&elem_to_copy="+elem_to_copy+
                    "&item_id="+item_id+
                    "&icon_id="+icon_id;
        httpRequest("sources/items.queries.php",data);
    }
}

//###########
//## EXECUTE WHEN PAGE IS LOADED
//###########
$(function() {
    //Disable menu buttons
    $('#menu_button_edit_item,#menu_button_del_item,#menu_button_add_fav,#menu_button_del_fav').attr('disabled', 'disabled');

    // Autoresize Textareas
    //$("#desc, #edit_desc").autoResizable();

    // Build buttons
    $("#custom_pw, #edit_custom_pw").buttonset();
    $(".cpm_button, #anyone_can_modify, #annonce, #edit_anyone_can_modify, #edit_annonce").button();

    //Build multiselect box
    $("#restricted_to_list").multiselect({
    	selectedList: 7,
    	minWidth: 430,
    	height: 145,
    	checkAllText: "<?php echo $txt['check_all_text'];?>",
    	uncheckAllText: "<?php echo $txt['uncheck_all_text'];?>",
    	noneSelectedText: "<?php echo $txt['none_selected_text'];?>"
    });
/*
	$("#categorie, #edit_categorie").multiselect({
		multiple: false,
		selectedList: 1,
		position: {
			my: 'left bottom',
			at: 'left top'
		},
        noneSelectedText: "<?php echo $txt['none_selected_text'];?>",
		click: function(event, ui){
            RecupComplexite(ui.value,0)
			$(this).multiselect("close");
		}
	});
*/
    //autocomplete for TAGS
    $("#item_tags, #edit_tags").focus().autocomplete('sources/items.queries.php?type=autocomplete_tags', {
        width: 300,
        multiple: true,
        matchContains: false,
        multipleSeparator: " "
    });

    //TreeView for FOLDERS
    $("#browser").treeview({
        collapsed: false,
        animated: "fast",
        control:"#sidetreecontrol",
        persist: "cookie",
        cookieId: "cpassman_treeview"
    });

    $("#add_folder").click(function() {
        var posit = document.getElementById('item_selected').value;
        alert($("ul").text());
    });

    $("#for_searchtext").hide();
    $("#copy_pw_done").hide();
    $("#copy_login_done").hide();

    //PREPARE DIALOGBOXES
    //=> ADD A NEW GROUP
    $("#div_ajout_rep").dialog({
        bgiframe: true,
        modal: true,
        autoOpen: false,
        width: 400,
        height: 200,
        title: "<?php echo $txt['item_menu_add_rep'];?>",
        buttons: {
            "<?php echo $txt['save_button'];?>": function() {
                AjouterFolder();
                $(this).dialog('close');
            },
            "<?php echo $txt['cancel_button'];?>": function() {
                $(this).dialog('close');
            }
        }
    });
    //<=
    //=> EDIT A GROUP
    $("#div_editer_rep").dialog({
        bgiframe: true,
        modal: true,
        autoOpen: false,
        width: 400,
        height: 200,
        title: "<?php echo $txt['item_menu_edi_rep'];?>",
        buttons: {
            "<?php echo $txt['save_button'];?>": function() {
                EditerFolder();
                $(this).dialog('close');
            },
            "<?php echo $txt['cancel_button'];?>": function() {
                $(this).dialog('close');
            }
        }
    });
    //<=
    //=> DELETE A GROUP
    $("#div_supprimer_rep").dialog({
        bgiframe: true,
        modal: true,
        autoOpen: false,
        width: 300,
        height: 200,
        title: "<?php echo $txt['item_menu_del_rep'];?>",
        buttons: {
            "<?php echo $txt['save_button'];?>": function() {
                SupprimerFolder();
                $(this).dialog('close');
            },
            "<?php echo $txt['cancel_button'];?>": function() {
                $(this).dialog('close');
            }
        }
    });
    //<=
    //=> ADD A NEW ITEM
    $("#div_formulaire_saisi").dialog({
        bgiframe: true,
        modal: true,
        autoOpen: false,
        width: 505,
        height: 600,
        title: "<?php echo $txt['item_menu_add_elem'];?>",
        buttons: {
            "<?php echo $txt['save_button'];?>": function() {
                AjouterItem();
            },
            "<?php echo $txt['cancel_button'];?>": function() {
                //Clear upload queue
                $('#item_file_queue').html('');
                //Select 1st tab
                $( "#item_tabs" ).tabs({ selected: 0 });
                $(this).dialog('close');
            }
        }
    });
    //<=
    //=> EDITER UN ELEMENT
    $("#div_formulaire_edition_item").dialog({
        bgiframe: true,
        modal: true,
        autoOpen: false,
        width: 505,
        height: 600,
        title: "<?php echo $txt['item_menu_edi_elem'];?>",
        buttons: {
            "<?php echo $txt['save_button'];?>": function() {
                EditerItem();
            },
            "<?php echo $txt['cancel_button'];?>": function() {
                //Clear upload queue
                $('#item_edit_file_queue').html('');
                //Select 1st tab
                $( "#item_edit_tabs" ).tabs({ selected: 0 });
                //Close dialog box
                $(this).dialog('close');
            }
        },
        close: function(event,ui) {
        	if(CKEDITOR.instances["edit_desc"]){
        		CKEDITOR.instances["edit_desc"].destroy();
        	}
        }

    });
    //<=
    //=> SUPPRIMER UN ELEMENT
    $("#div_del_item").dialog({
        bgiframe: true,
        modal: true,
        autoOpen: false,
        width: 300,
        height: 150,
        title: "<?php echo $txt['item_menu_del_elem'];?>",
        buttons: {
            "<?php echo $txt['del_button'];?>": function() {
                var data = "type=del_item"+
                            "&groupe="+document.getElementById('hid_cat').value+
                            "&id="+document.getElementById('id_item').value;
                httpRequest("sources/items.queries.php",data);
                $(this).dialog('close');
            },
            "<?php echo $txt['cancel_button'];?>": function() {
                $(this).dialog('close');
            }
        }
    });
    //<=
    //=> SHOW LINK COPIED DIALOG
    $("#div_item_copied").dialog({
        bgiframe: true,
        modal: true,
        autoOpen: false,
        width: 500,
        height: 200,
        title: "<?php echo $txt['admin_main'];?>",
        buttons: {
            "<?php echo $txt['close'];?>": function() {
                $(this).dialog('close');
            }
        }
    });
    //<=

    //automatic height
    var hauteur = $(window).height();
    $("#div_items, #content").height( (hauteur-150) );
    $("#content_1").height( (hauteur-400) );
    $("#liste_des_items").height(hauteur-420);
    $("#sidebar").height(hauteur-170);

    //display first group items
    AfficherRecherche();

    //CALL TO UPLOADIFY FOR FILES UPLOAD in EDIT ITEM
    $("#item_edit_files_upload").uploadify({
        "uploader"  : "includes/libraries/uploadify/uploadify.swf",
        "script"    : "includes/libraries/uploadify/uploadify.php",
        "cancelImg" : "includes/libraries/uploadify/cancel.png",
        "auto"      : false,
        "multi"     : true,
        "folder"    : "upload",
        "sizeLimit" : 16777216,
        "queueID"   : "item_edit_file_queue",
        "onComplete": function(event, queueID, fileObj, reponse, data){document.getElementById("item_edit_list_files").append(fileObj.name+"<br />");},
        "buttonText": "<?php echo $txt['upload_button_text'];?>"
    });

    //CALL TO UPLOADIFY FOR FILES UPLOAD in NEW ITEM
    $("#item_files_upload").uploadify({
        "uploader"  : "includes/libraries/uploadify/uploadify.swf",
        "script"    : "includes/libraries/uploadify/uploadify.php",
        "cancelImg" : "includes/libraries/uploadify/cancel.png",
        "auto"      : false,
        "multi"     : true,
        "folder"    : "upload",
        "sizeLimit" : 16777216,
        "queueID"   : "item_file_queue",
        "onComplete": function(event, queueID, fileObj, reponse, data){document.getElementById("item_files_upload").append(fileObj.name+"<br />");},
        "buttonText": "<?php echo $txt['upload_button_text'];?>"
    });
});
</script>

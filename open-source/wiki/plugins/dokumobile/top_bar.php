<?php
// This is the top Toolbar for dokumobile
?>



<table width="300px" cellspacing="0" cellpadding="0" style="border:0;margin:5px auto 5px auto;">
  <tr>
    <td width="33%" style="border:0;" align="left"><img src="<?php echo DOKU_TPL?>images/icon_left.png ?>" id="toggle_toc" /></td>
    <td width="33%" style="border:0;" align="center"><img src="<?php echo DOKU_TPL?>images/icon_middle.png ?>" id="toggle_admin" /></td>
    <td width="33%" style="border:0;" align="right"><img src="<?php echo DOKU_TPL?>images/icon_right.png ?>" id="toggle_nav" /></td>
  </tr>
</table>

<hr>


<div id="wiki_admin" style="display:none" class="dokuwiki">
<h5 style="text-align:center;margin:0;border:none;">Wiki Toolbar</h5>

    <?php tpl_searchform()?>&nbsp;
	
    <div class="bar" id="bar__top">

		<div class="bar-left" id="bar__bottomleft">
		<?php tpl_button('edit')?>
        <?php tpl_button('history')?>
        <?php tpl_button('revert')?>
		<?php tpl_button('subscribe')?>
        <?php tpl_button('subscribens')?>
		<?php tpl_button('admin')?> 
        <?php tpl_button('edit')?>
        <?php tpl_button('profile')?>
        <?php tpl_button('login')?>
		<?php tpl_button('index')?>
      </div>
      
    </div>
	
</div>


<div id="wiki_toc" style="display:none" class="dokuwiki">
<h5 style="text-align:center;margin:0;border:none;">Table of Contents</h5>
	<?php tpl_toc()?>
</div>

<div id="wiki_nav" style="display:none" class="dokuwiki" >
<h5 style="text-align:center;margin:0;border:none;">Navigation</h5>
	<?php tpl_include_page(":wiki:navigation_mobile");?>
</div>


<script>
	
	jQuery('#toggle_nav').click(function() {
    jQuery('#wiki_nav').toggle();
	jQuery('#wiki_toc').hide();
	jQuery('#wiki_admin').hide();
	
}); 

	jQuery('#toggle_toc').click(function() {
    jQuery('#wiki_toc').toggle();
	jQuery('#wiki_nav').hide();
	jQuery('#wiki_admin').hide();
	
});    

	jQuery('#toggle_admin').click(function() {
    jQuery('#wiki_admin').toggle();
	jQuery('#wiki_toc').hide();
	jQuery('#wiki_nav').hide();
	
});  
</script>



<?php

/**
 * iDokuWiki Template
 *
 * hacked up version of the default template
 * 
 *
 * 
 * 
 *
 * @link   http://dokuwiki.org/templates:iDokuwiki
 * @author hiflyer.x <hiflyer.x@gmail.com>
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction']?>">
<head>
  <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


  <title>
    <?php tpl_pagetitle()?>
    [<?php echo strip_tags($conf['title'])?>]
  </title>

  <?php tpl_metaheaders()?>

  <link rel="shortcut icon" href="<?php echo DOKU_TPL?>images/favicon.ico" />

  <?php /*old includehook*/ @include(dirname(__FILE__).'/meta.html')?>
  
  <script type="text/javascript">
	addEventListener(
		"load", 
		function() {
		setTimeout(hideURLbar, 0);
		}, false
	);
	function hideURLbar() { window.scrollTo(0, 1); }
</script>



</head>

<body>
	<div class="iDokuwiki_container">

	
<!-- Top Search Bar -->

<?php include 'top_bar.php'; ?>

<!-- End Top Searchbar -->


<?php /*old includehook*/ @include(dirname(__FILE__).'/topheader.html')?>



<div class="dokuwiki">
  <?php html_msgarea()?>

   <?php flush()?>

  <?php /*old includehook*/ @include(dirname(__FILE__).'/pageheader.html')?>

  <div class="page">
    <!-- wikipage start -->
	    <?php tpl_content(false)?>
    <!-- wikipage stop -->
  </div>

  <div class="clearer">&nbsp;</div>

  <?php flush()?>

  <div class="stylefoot">

    <div class="meta">
      <div class="user">
        <?php tpl_userinfo()?>
      </div>
	  <br>
	  <div class="doc">
        <?php tpl_pageinfo()?>
      </div>

    </div>

   <?php /*old includehook*/ @include(dirname(__FILE__).'/pagefooter.html')?>

  </div>

<!--  <?php tpl_license(false);?> --!>

</div>



<!-- <?php /*old includehook*/ @include(dirname(__FILE__).'/footer.html')?> --!>

<div class="no"><?php /* provide DokuWiki housekeeping, required in all templates */ tpl_indexerWebBug()?></div>

<!-- close container -->
</div>
</body>
</html>

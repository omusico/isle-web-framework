<?php
/**
 * DokuWiki Default Template
 *
 * This is the template you need to change for the overall look
 * of DokuWiki.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://dokuwiki.org/templates
 * @author Andreas Gohr <andi@splitbrain.org>
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction']?>">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    [<?php echo strip_tags($conf['title'])?>]
    <?php tpl_pagetitle()?>
  </title>

  <?php tpl_metaheaders()?>

<meta name="Content-Language" content="<?php echo $conf['lang']?>" />
<meta name="Language" content="<?php echo $conf['lang']?>" />

  <link rel="shortcut icon" href="<?php echo DOKU_TPL?>images/favicon.ico" />

  <?php /*old includehook*/ @include(dirname(__FILE__).'/meta.html')?>

  <?php if (file_exists(DOKU_PLUGIN.'displaywikipage/code.php'))
  include_once(DOKU_PLUGIN.'displaywikipage/code.php')?>
</head>

<body>
<?php /*old includehook*/ @include(dirname(__FILE__).'/topheader.html')?>


<div class="dokuwiki">
  <?php html_msgarea()?>

  <div class="stylehead">

    <div class="header">
      <div class="logoshadow">
        <div class="logo">
          <?php tpl_link(wl(),$conf['title'],'name="dokuwiki__top" id="dokuwiki__top" accesskey="h" title="[H]"')?>        
        </div>
      </div>
      <div class="clearer">&nbsp;</div>
    </div>

    <?php /*old includehook*/ @include(dirname(__FILE__).'/header.html')?>

  </div>

  <?php flush()?>

  <?php if (function_exists('dwp_display_wiki_page')){
    print('<div class="menue">');
    $result = '';
    dwp_display_wiki_page(":menue");
    print('  </div>');
  }?>
  <!--div class="clearer">&nbsp;</div-->
  <?php flush()?>

  <?php /*old includehook*/ @include(dirname(__FILE__).'/pageheader.html')?>
<div class="clearer">&nbsp;</div>
  <div class="content_margin">
    <div class="shadow">
      <div class="page">
      
      <!--
        <div class="navigation-bar">
          <?php if($conf['breadcrumbs']){?>
          <div class="breadcrumbs">
            <?php tpl_breadcrumbs()?>
            <?php //tpl_youarehere() //(some people prefer this)?>
          </div>
          <?php }?>

          <?php if($conf['youarehere']){?>
          <div class="breadcrumbs">
            <?php tpl_youarehere() ?>
          </div>
            <?php }?>
        </div>
        
          -->
          
            <?php if (function_exists('dwp_display_wiki_page')){
          print('<div class="headmenue">');
          $result = '';
          dwp_display_wiki_page(":headmenue");
          print('  </div>');
          }?>
          <!--div class="clearer">&nbsp;</div-->
          <?php flush()?>        
        
        <?php tpl_content()?>
        <div class="commonpages">
          <a href="<?php echo DOKU_BASE; ?>doku.php/impressum" title="Impressum">Impressum</a>
          <a href="<?php echo DOKU_BASE; ?>doku.php/kontakt" title="Kontakt">Kontakt</a>
          <a href="<?php echo DOKU_BASE; ?>doku.php/sitemap" title="Sitemap">Sitemap</a>
        </div>
        <div class="rssfeed">
          <a <?php echo $tgt?> href="<?php echo DOKU_BASE; ?>feed.php" title="Recent changes RSS feed"><img src="<?php echo DOKU_TPL; ?>images/button-rss.png" width="80" height="15" alt="Recent changes RSS feed" /></a>
          <a <?php echo $tgt?> href="http://dokuwiki.org/" title="Driven by DokuWiki"><img src="<?php echo DOKU_TPL; ?>images/button-dw.png" width="80" height="15" alt="Driven by DokuWiki" /></a>
        </div>
		<div class="seitzeichen">
			<script type="text/javascript">var szu=encodeURIComponent(location.href); var szt=encodeURIComponent(document.title).replace(/\'/g,'`'); var szjsh=(window.location.protocol == 'https:'?'https://ssl.seitzeichen.de/':'http://w3.seitzeichen.de/'); document.write(unescape("%3Cscript src='" + szjsh + "w/3b/7a/widget_3b7a6383defbbec79fb4f91e6b7a7c74.js' type='text/javascript'%3E%3C/script%3E"));</script>
		</div>
  		<div class="clearer">&nbsp;</div>		
      </div>
    </div>
  </div>

  <div class="clearer">&nbsp;</div>
        <!--<div class="meta">
          <div class="user">
			<?php tpl_userinfo()?>
          </div>
          <div class="doc">
            <?php tpl_pageinfo()?>
          </div>
        </div>-->
  <?php flush()?>

  <?php if (function_exists('dwp_display_wiki_page')){
    print('<div class="news">');
    dwp_display_wiki_page(":news");
    print('  </div>');
  }?>
  <div class="clearer">&nbsp;</div>
  <?php flush()?>

  <div class="stylefoot">

   <?php /*old includehook*/ @include(dirname(__FILE__).'/pagefooter.html')?>

    <div class="bar" id="bar__bottom">
      <div class="bar-left" id="bar__bottomleft">
      <?php tpl_button('login')?>
      </div>
      <div class="bar-right" id="bar__bottomright">
      <?php tpl_button('edit')?>
      <?php tpl_button('history')?>
      <?php tpl_button('subscribe')?>
      <?php tpl_button('subscribens')?>
      <?php tpl_button('admin')?>
      <?php tpl_button('profile')?>
      <?php tpl_button('index')?>
      <!--?php tpl_button('top')?-->
      <?php tpl_searchform()?>&nbsp;      
      </div>

      <div class="clearer"></div>
    </div>
  </div>
</div>
<?php /*old includehook*/ @include(dirname(__FILE__).'/footer.html')?>

<div class="no"><?php /* provide DokuWiki housekeeping, required in all templates */ tpl_indexerWebBug()?></div>
</body>
</html>

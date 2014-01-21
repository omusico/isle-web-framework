##TeXit plugin for Dokuwiki

This is a cloned repository of the [Dokuwiki TeXit plugin], the original website
was http://danjer.doudouke.org/tech/dokutexit, but the original author has
disapeared, as well as the whole doudouke.org domain (on which he had his mail
address).

### Changes

This version comes with a set of updates and changes:
 * name is now texit instead of dokutexit
 * code refactoring, simplifying and cleaning
 * documentation
 * integration with [nsbpc], simplification of configuration files
 * update of TeX template, LuaTeX and XeTeX integration
 * use of latexmk instead of manual compilation (thus working with BibTeX, indexes, etc.)
 * produced pdf and zip are now in the media namespace
 * possibility to export a whole namespace
 * less fancy exports (no ugly background link)
 * integration with [refnotes] for bibliography (only BibTeX entries)
 * removing complex administration interface
 * remove zip functions from outer space, using zlib instead

### Configuration

Configuration is organized this way:

##### Usual plugin config

 * *conf/defaults.php* (or the configuration manager) holds the global configuration, where you can choose several things, such as the default renderer (LaTeX+dvipdf, pdfLaTeX, XeLaTeX or LuaLaTeX)
 * *conf/header-namespace.tex* is the header which will be included when a whole namespace will be exported
 * *conf/header-page.tex* is the same for one page export
 * *conf/commands.tex* is where the TeX macros for dokuwiki styles will be held (this is common for namespace and page)
 * *conf/footer.tex*, if present, will be inserted before `\end{document}`, useful to insert table of contents or bibliographies.

##### NSBPC plugin config

You can use [nsbpc] to have per-namespace (and thus per-language) configuration. The configuration pages will be:
 * *nsbpc_texit-namespace* overriding *conf/header-namespace.tex*
 * *nsbpc_texit-page* overriding *conf/header-page.tex*
 * *nsbpc_texit-commands* overriding *conf/commands.tex*
 * *nsbpc_texit-footer* overriding *conf/footer.tex*

##### BibTeX config

If it exists, TeXit will handle the file *texit.bib* in the *conf/* directory of the plugin.

If you use [refnotes], TeXit will merge all BibTeX code from refnotes into a *texit.bib* in the namespace of the refnotes database pages (config option `reference-db-enable`).

If you want to insert the bibliography, please use the `\dokubibliography` (defined in *commands.tex*) macro in your header or footer, it will call the good filename -- just it case this one (*texit.bib*) changes.

### Output files

When clicking on the export button, the plugin will compute an .pdf file containing the produced PDF. The output for page *namespace:subnamespace:id* will be named *namespace:subnamespace:id.pdf* and will automaticall be plae in *media:namespace:subnamespace*.

The intermediate .tex files will be placed in the *texit:namespace:subnamespace* namespace. Suppose you have the following pages:

 * *namespace:subnamespace:page1*
 * *namespace:subnamespace:page2*

, if you generate all files, the *media:namespace:subnamespace* namespace will contain:

 * *page1.pdf*
 * *page1-tex.zip*, a zip file containing page1.pdf and the necessary tex files to compile it (see herebelow for the structure)
 * *page2.pdf*
 * *page2-tex.zip*
 * *all.pdf*, pdf containing page1 and page2 (the whole namespace)
 * *all-tex.zip*

The *texit:namespace:subnamespace* namespace will contain:

 * *commands.tex* : a copy of the corresponding file
 * *texit.bib* : the bibliography database (not mandatory)
 * *footer.tex* : the footer (not mandatory)
 * *page1-content.tex* : the translation content of the *page1* page in TeX (no header, not a complete tex file)
 * *page2-content.tex* : idem for page2
 * *page1.tex* : an adptation of *header-page.tex* for *page1.pdf*, `\include`ing the following tex files:
  * *commands.tex*
  * *page1-content.tex*
 * *page2.tex* : idem for page2
 * *all.tex* : an adaptation of *header-namespace.tex* for *all.pdf*, `\include`ing the following tex files:
  * *commands.tex*
  * *page1-content.tex*
  * *page2-content.tex*

The structure of the *.zip* files in *media:namespace:subnamespace* is the following one (if we take *all-tex.zip*):

 * *all.pdf*
 * *all.tex*
 * *commands.tex*
 * *page1-content.tex*
 * *page2-content.tex*
 * *texit.bib* (if relevant)
 * *footer.tex* (if exists)

All filenames will have characters \_ escaped as \- for good TeX integration. This means that bad things may happen if you have *foo\-bar* and *foo\_bar* in the same namespace.

When the user asks the pdf, intermediate TeX files will be produced only if the page has changed. As the compilation is done with latexmk, no unnecessary recompilation will happen if the page (or all pages in the namespace) haven't changed.

Optionnaly, a prefix may be prepended to PDF filename. It is `$prefix,namespace,subnamespace,` (see configuration manager for options about this). This is useful if you want people to download files with explicit filenames referencing your wiki.

### Warning for server saturation

Robots will follow every link, which means that they will generate all pdfs for all pages and namespaces when they'll reference your website. This can lead to server saturation and crash!

To prevent robots from following the links to PDF export, add a `robots.txt` in the root namespace of your wiki, reading

```
User-agent: *
Disallow: /*texit*
```

(untested yet).

### CMK integration

Note that you can define custom markups with [cmk] (see the README file of cmk).

### Refnotes integration

You can use texit with refnotes, with the following limitations:
  * only BibTeX configurations in database files will be taken into account
  * ref namespaces won't work at all, you should put everything in the root namespace

### Documentation

There is a documentation in help/, but it seems Dokuwiki doesn't allow plugins
to install docs, so you'll have to install it by yourself (you can, for
instance, follow the link in the administration page). What seems to me as
the most elegant solution is to create your help pages in the
manual:pluginsmanual namespace and to add 

    ====== Plugins ======
    
    {{nstoc :fr:wiki:pluginsmanual 2}}

in *manual/start*. Feel free to do otherwise.

### Nice sidebar buttons

Sidebar buttons are really painful to add! The normal way would be to add your images in `lib/tpl/<yourtemplate>/images/pagetools` and to call `lib/tpl/<yourtemplate>/images/pagetools-build.php`. The problem is that this script uses the `imagelayereffect()` php function from php-[gd]. This library has [human issues][gdpb], and Debian doesn't ship a php-gd library with this function (see [here][gdpbdeb]). So if you have a Debian server, the only way to make it work is to either hack your gd library, or do what the `pagetools-build.php` does by hand.

If you do it by hand, basically you have to merge images from the `pagetools` directory (coming from the [retina icon set][retina]) in the `lib/tpl/<yourtemplate>/images/pagetools-sprite.png`. To do so, first duplicate them, with one version in grey and the other in blue, and apply them a gradiant. Then stack them vertically, each 45px, in the `pagetools-sprite.png`.

Once you have a good `pagetools-sprite.png`, then you can change your template this way:

##### actions

All these buttons will be associated with actions, and thus with [action plugins][actionplugins]. If you just want to test the button adding, before developping your action plugin, you can put some dumb values (that you'll have to remove later). To do so, you can follow the instructions on [pdfexport plugin doc][pdfexport], section *Dokuwiki-template: Export Link in Pagetools*.

##### css

Add this at the end of `lib/tpl/<yourtemplate>/css/pagetools.css`:

```css
#dokuwiki__pagetools ul li a.<myaction> {
    background-position: right -1090px;
}
#dokuwiki__pagetools ul li a.<myaction>:before {
    margin-top: -1090px;
}
#dokuwiki__pagetools ul li a.<myaction>:hover,
#dokuwiki__pagetools ul li a.<myaction>:active,
#dokuwiki__pagetools ul li a.<myaction>:focus {
    background-position: right -1135px;
}
```

replacing `<myaction>` by the name of your action. Add it for each button you want in the pagetools bar, adding each time 45px to the values.

### License

The plugin seems to be under GPLv2+ license, which I'll assume.

### Requirements

The plugin supposes you have a recent TeXLive installation (it assumes 2013, but
it should have almost no problem with 2012 version or with MikTeX), with 
latexmk.

Not that you need to install imagemagick for image conversion.

You need to be able to use zip functions in php (on Debian, install `libphp-pclzip` package).

It has only been tested on a recent dokuwiki (Release 2013-05-10a "Weatherwax").

### Limitations and TODO

  * Cache is not well handled yet: if you change the configuration of a page without changing page content, you'll need to clean the cache, otherwise output will still be the old version (see following point).
  * When ready, use [nsbpc] function for cache (see TODO section of nsbpc's README and  [Dokuwiki documentation][cache]).
  * When used with CMK, if a .tex file is already compiled and cmk configuration changes, TeXit has currently no way to know it has te recompile. I believe CMK should put some infos about file dependencies in the medata of the page (it's possible, see link in previous point)

[Dokuwiki TeXit plugin]: https://www.dokuwiki.org/plugin:dokutexit
[nsbpc]: https://github.com/eroux/dokuwiki-plugin-nsbpc
[gd]: http://en.wikipedia.org/wiki/GD_Graphics_Library
[gdpbdeb]: https://bugs.launchpad.net/ubuntu/+source/php5/+bug/74647
[retina]: http://blog.twg.ca/2010/11/retina-display-icon-set/
[actionplugins]: https://www.dokuwiki.org/devel:action_plugins
[pdfexport]: https://www.dokuwiki.org/tips:pdfexport#dokuwiki-templateexport_link_in_pagetools
[refnotes]: https://www.dokuwiki.org/plugin:refnotes
[cmk]: https://github.com/eroux/dokuwiki-plugin-cmk
[cache]:https://www.dokuwiki.org/devel:caching#plugins

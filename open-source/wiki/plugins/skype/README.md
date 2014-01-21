# Dokuwiki Plugin Skype

<table>
  <tr>
    <th align="left">Description</th>
    <td>The Skype plugin let you add an Skype contact button easily</td>
  </tr>
  <tr>
    <th align="left">Author</th>
    <td>Zahno Silvan</td>
  </tr>
  <tr>
    <th align="left">Email</th>
    <td>zaswiki@gmail.com</td>
  </tr>
  <tr>
    <th align="left">Type</th>
    <td>syntax</td>
  </tr>
  <tr>
    <th align="left">Lastupdate</th>
    <td>2012-10-22</td>
  </tr>
  <tr>
    <th align="left">Tags</th>
    <td>button, skype, embed</td>
  </tr>
</table>

## Download
* Download to Dokuwiki plugin folder
* File     : https://github.com/tschinz/dokuwiki_skype_plugin/zipball/master

## Versions
* **2011-02-25**
  * Init version 
* **2012-06-14**
  * Added function in command, every button can have a different function now.
* **2012-10-22**
  * Moved Repo to github

## Syntax
```
{{skype>username}}
{{skype>username,function}}
```

You can see the official Skype button wizard here: [Official Skype Button Wizard](http://www.skype.com/intl/en/tell-a-friend/wizard/)

<table>
  <tr>
    <th>Admin setting</th>
    <th>Default value</th>
    <th>Description</th>
  </tr>
  <tr>
    <th align="left">Function</th>
    <td>chat</td>
    <td>click function (chet, sendfile, call, voicemail,userinfo or add)</td>
  </tr>
  <tr>
    <th align="left">Size</th>
    <td>big</td>
    <td>size if icon (bog or small)</td>
  </tr>
  <tr>
    <th align="left">Content</th>
    <td>icon+text</td>
    <td>only icon or icon with text</td>
  </tr>
  <tr>
    <th align="left">Style</th>
    <td>balloon</td>
    <td>New or classic design</td>
  </tr>
</table>

## Example
```
{{skype>username}}
{{skype>username,call}}
{{skype>username,chat}}
{{skype>username,sendfile}}
{{skype>voicemail,username}}
{{skype>userinfo,username}}
{{skype>add,username}}
```
or different Design

![anim_1](http://zawiki.dyndns.org/lib/exe/fetch.php/tschinz:programming:dw:skype:anim_rectangle.gif)
![anim_2](http://zawiki.dyndns.org/lib/exe/fetch.php/tschinz:programming:dw:skype:anim_balloon.gif)

## Possible Problems
### Status always "offline"
Then you need to change your privacy settings in Skype: `Options` -> `Privacy` -> `Allow Net Status`

### Nothing happens when I click the button?
Click on the button and scroll to the top of the page.

## Documentation

All documentation for the Skype Plugin is available online at:

  * [Dokuwiki Plugin Page](http://dokuwiki.org/plugin:skype)
  * [ZaWiki Plugin Page](http://zawiki.dyndns.org/doku.php/tschinz:dw_skype)
  * [Github Project Page](https://github.com/tschinz/dokuwiki_skype_plugin)

2011 by Zahno Silvan <zaswiki@gmail.com>

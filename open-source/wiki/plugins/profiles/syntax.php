<?php
/*
 *
 * @license    MIT
 * @author     Alexandru Radovici <msg4alex@gmail.com>
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');


class syntax_plugin_profiles extends DokuWiki_Syntax_Plugin {
 
    var $links = array ("facebook" => "https://www.facebook.com/", "github" => "https://github.com/", "googleplus" => "https://plus.google.com/", "linkedin" => "http://www.linkedin.com/", "twitter" => "https://twitter.com/", "bitbucket" => "https://bitbucket.org/", "blog" => "http://", "web" => "http://");
  
    function getInfo(){
        return confToHash(dirname(__FILE__).'/info.txt');
    }
 
 
    function getType(){ return 'substition'; }
    function getPType(){ return 'normal'; }
    function getAllowedTypes() { 
        return array('substition');
    }
    function getSort(){ 
      return 305; 
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{(?:facebook|github|googleplus|linkedin|twitter|blog|bitbucket|web)(?:|\..*?):.*?}}',$mode,'plugin_profiles');
    }
 
    function handle($match, $state, $pos, &$handler){

        switch ($state) {

          case DOKU_LEXER_SPECIAL : 
            preg_match ('/{{(?P<service>facebook|github|googleplus|linkedin|twitter|blog|bitbucket|web)(?P<type>|\..*?):(?P<parameter>.*?)}}/', $match, $data);
            $params = array ();
            $params["service"] = $data["service"];
            $params["type"] = $data["type"];
            $params["parameter"] = $data["parameter"];

            return $params;
        
          default:
            return "";
        }
    }
 
    function render($mode, &$renderer, $indata) {

        if($mode == 'xhtml'){

              $service = $indata["service"];
              $type = $indata["type"];
              if (strlen ($type)>0) $type = substr ($type, 1);
              $p = explode ("|", $indata["parameter"]);
              $parameter = htmlspecialchars($p[0]);
              if (strlen ($p[1])==0) $name = htmlspecialchars($p[0]);
              else $name = htmlspecialchars($p[1]);
              $ahref = $this->links[$service];
              if ($type == "link") $ahref = $parameter;
              else
              if ($service=="facebook")
              {
                if ($type == "profile" || $type == "page" || $type == "") $ahref = $ahref.$parameter;
              }
              else
              if ($service=="github")
              {
                $ahref = $ahref.$parameter;
              }
              else
              if ($service=="googleplus")
              {
                $ahref = $ahref.$parameter;
              }
              else
              if ($service=="linkedin")
              {
                if ($type == "profile" || $type == "") $ahref = $ahref."in/".$parameter;
                else if ($type == "page") $ahref = $ahref.$parameter;
              }
              else
              if ($service=="twitter")
              {
                if ($type != "link") $ahref = $ahref.$parameter;
              }
              else
              if ($service=="bitbucket")
              {
                if ($type != "link") $ahref = $ahref.$parameter;
              }
              else
              if ($service=="blog"||$service=="web")
              {
                if ($type != "link") $ahref = $ahref.$parameter;
              }
              $renderer->doc .= '<a href='.$ahref.' target="_blank"><img src="' . DOKU_BASE . 'lib/plugins/profiles/images/'.$service.'_icon.png" alt="'.$service.'" border="0"> '.$name.'</a>';
              
              return true;
        }
        
        return false;
    } 
}

?>

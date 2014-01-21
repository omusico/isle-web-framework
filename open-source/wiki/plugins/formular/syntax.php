<?php
/**
 * formular Plugin: Displays Forms
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ole Rienow <wikiform@rienow.eu>
 *
 */
 
/**
 * Changes Log
 * [2008-01-09] Original Form Plugin from Ole Rienow
 * [2008-02-17] Small additions by Stephane Chamberland <stephane.chamberland@gmail.com>
 * - rename to Formular (not to confuse with Esther's Form plugin) and to keep compatibility with previous versions settings
 * - make compatible with configuration plugin
 * - localize [include only the En, Fr translations]
 * - add default styles [borrowed from Esther's form plugin]
 * [2008-06-29] You can hand over GET-Parameters as Standard Values for Textfields and Textareas
 * [2008-07-03]
 *	- Many Refactorings
 *	- Constraints are now also possible for self written Scripts
 *	- Username in Mail
 *	- Added Grepnot Constraint
 * [2008-07-09]
 *	- Added local wikipage Language (e.g. Hidden lang "en";)
 * [2008-07-17]
 *  - Added Debug-Modus
 *  - Bugfix in the Constraint-Writer for self written Scripts
 * [2008-07-18]
 *  - Bugfix in MailForm creation
 *  - Added Feature to hold Data if given Data is not valid
 * [2008-07-19]
 *  - Added File-Upload feature
 * [2008-07-20]
 *  - Hold Data in Selectboxes, Radiobuttons and Checkboxes as well
 *  - Bugfix for Multiple Line Data in Textareas
 * [2008-07-23]
 *  - Writing Debug-Infos to File
 * [2009-03-12]
 *  - little bugfix of missing quotation mark in textareas
 *  - new italian translation - thanks to Diego Pierotto
 */

/**$renderer->info['cache'] = false;
 * Configuration specified in DOKUPATH/conf/local.php
 * 
 * $conf['plugin']['formular']['AllowInclude'] = 1|0;
 * $conf['plugin']['formular']['mailPath'] = "data/formplugin/";
 * $conf['plugin']['formular']['selectPage'] = "formplugin:select";
 * $conf['plugin']['formular']['mailSubject'] = "Message -- formular plugin";
 * $conf['plugin']['formular']['mailFrom'] = "";
 *
 */ 
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_formular extends DokuWiki_Syntax_Plugin {
 
 	var $scriptPath;
	var $mailPath;
	var $mailFile;
	var $internalScript = false;
	var $localLang = array();
	
	/**
	* return some info
	*/
	function getInfo(){
		return array(
			'author' => 'Ole Rienow',
			'email'  => 'wikiform@rienow.eu',
			'date'   => '2009-03-12',
			'name'   => 'Formular Plugin',
			'desc'   => 'Creates HTML Forms',
			'url'    => 'http://wiki.splitbrain.org/plugin:form',
		);
	}
 
	function getType(){
		return 'substition';
	}
	
	/**
	 * What about paragraphs? (optional)
	 */
	function getPType(){
		return 'block';
	}
	
	/**
	 * Where to sort in?
	 */ 
	function getSort(){
		return 999;
	}
 
	/**
	 * Connect pattern to lexer
	 */
	function connectTo($mode) {
		$this->Lexer->addEntryPattern('<FORM.*?>(?=.*?</FORM>)',$mode,'plugin_formular');
	}

	function postConnect() {
		$this->Lexer->addExitPattern('</FORM>', 'plugin_formular');
	}

 	function phpParsed($string) {
		require_once DOKU_INC . 'inc/parser/xhtml.php';
		require_once DOKU_INC . 'inc/parser/parser.php';
		
		$Parser = & new Doku_Parser(); 
		$Parser->Handler = & new Doku_Handler();
		$Parser->addMode('php',new Doku_Parser_Mode_PHP());
		
		$instructions = $Parser->parse($string);		// Get a list of instructions
		$Renderer = & new Doku_Renderer_XHTML();		// Create a renderer
		
		// Loop through the instructions
		foreach ( $instructions as $instruction ) {
			call_user_func_array(array(&$Renderer, $instruction[0]),$instruction[1]);
		}
		 
		return trim(substr($Renderer->doc, 4, -5));
	}
	
	function parsed($string) {
		require_once DOKU_INC . 'inc/parser/xhtml.php';
		require_once DOKU_INC . 'inc/parser/parser.php';
		
		$Parser = & new Doku_Parser(); 
		$Parser->Handler = & new Doku_Handler();
		
		$Parser->addMode('listblock',new Doku_Parser_Mode_ListBlock());
		$Parser->addMode('preformatted',new Doku_Parser_Mode_Preformatted()); 
		$Parser->addMode('notoc',new Doku_Parser_Mode_NoToc());
		$Parser->addMode('header',new Doku_Parser_Mode_Header());
		$Parser->addMode('table',new Doku_Parser_Mode_Table());
		$Parser->addMode('linebreak',new Doku_Parser_Mode_Linebreak());
		$Parser->addMode('footnote',new Doku_Parser_Mode_Footnote());
		$Parser->addMode('hr',new Doku_Parser_Mode_HR());
		$Parser->addMode('unformatted',new Doku_Parser_Mode_Unformatted());
		$Parser->addMode('php',new Doku_Parser_Mode_PHP());
		$Parser->addMode('html',new Doku_Parser_Mode_HTML());
		$Parser->addMode('code',new Doku_Parser_Mode_Code());
		$Parser->addMode('file',new Doku_Parser_Mode_File());
		$Parser->addMode('quote',new Doku_Parser_Mode_Quote());
		$Parser->addMode('acronym',new Doku_Parser_Mode_Acronym(array_keys(getAcronyms())));
		$Parser->addMode('multiplyentity',new Doku_Parser_Mode_MultiplyEntity());
		$Parser->addMode('quotes',new Doku_Parser_Mode_Quotes());
		$Parser->addMode('camelcaselink',new Doku_Parser_Mode_CamelCaseLink());
		$Parser->addMode('internallink',new Doku_Parser_Mode_InternalLink());
		$Parser->addMode('media',new Doku_Parser_Mode_Media());
		$Parser->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
		$Parser->addMode('windowssharelink',new Doku_Parser_Mode_WindowsShareLink());
		$Parser->addMode('filelink',new Doku_Parser_Mode_FileLink());
		$Parser->addMode('eol',new Doku_Parser_Mode_Eol());
		
		$formats = array (
			'strong', 'emphasis', 'underline', 'monospace',
			'subscript', 'superscript', 'deleted',
		);
		foreach ($formats as $format) {
			$Parser->addMode($format,new Doku_Parser_Mode_Formatting($format));
		}
		
		$instructions = $Parser->parse($string);		// Get a list of instructions
		$Renderer = & new Doku_Renderer_XHTML();		// Create a renderer
		
		// Loop through the instructions
		foreach ( $instructions as $instruction ) {
			call_user_func_array(array(&$Renderer, $instruction[0]),$instruction[1]);
		}
		 
		return trim(substr($Renderer->doc, 4, -5));
	}
	
	function setLocalLang($text) {
		$langFile = DOKU_PLUGIN . "formular/lang/$text/lang.php";
		if (is_File($langFile)) {
			include($langFile);
			$this->localLang = $lang;
		}
	}

	function getLocalLang($id) {
		return (isset($this->localLang[$id])) ?
			$this->localLang[$id] : $this->getLang($id);
	}

   /**
	* Function to create some Error-Output
	*/
	function insertError($name, $type=3){
		$header = '<tr><td colspan="2"><span style="color:#FF0000;">';
		$footer = '</span></td></tr>';
		switch ($type) {
		case 0:
			$error = $this->getLocalLang('name_not_unique').': ' . trim($name);
			break;
		case 1:
			$error = $this->getLocalLang('wrong_nb_of_arguments').': ' . trim($name);
			break;
		case 2:
			$error = $this->getLocalLang('undef_combo').': ' . trim($name);
			break;
		case 3:
			$error = trim($name);
			break;
		}
		return $header.$error.$footer;
	}
	
	/**
	 * Function to write something at beginning of file
	 */
	function writeBefore($file, $content) {
		if (!file_exists($file)) {
			$handle = fopen($file,'w');
			fclose($handle);
		}
			
		$fOld = file_get_contents($file);
		$newContent = $content.$fOld;
		$f = fopen($file,'w') or die($this->getLocalLang('cannot_open'));
		fwrite($f, $newContent);
		fclose($f);
		// file_put_contents($file, $newContent); not used because not implemented below php5
	}

	function writeDebugInfo($message) {
		if ($this->getConf('DebugMode')) {
			$this->writeBefore(DOKU_PLUGIN.'formular/debug.txt', $message."\n");
		}
	}
	
	function constraintError($error) {
		return "{ \n"
				. '    $back .= "error='.$error."\";\n"
				. '    header("Location: $back");'
				. '    exit();'
				."\n}\n";
	}
	
	/**
	 * At the moment this just works if the settings are set as follows:
	 * Use Nice URLs = 2 (DokuWiki Internal)
	 * Use Slash as Namespace seperator = 0 (Not Checked)
	 */
	function writeConstraintsToScript() {
		$this->writeDebugInfo("Function: writeConstraintsToScript");

		global $conf;
		if ($conf['userewrite'] != 2 || $conf['useslash'] == 1) {
			unlink($this->mailPath.'constraint.tmp');
			$this->writeDebugInfo("Wrong Settings for this.");
			return;
		}
		
		if ($this->internalScript && file_exists($this->mailPath.'constraint.tmp')) {
			$sgn_begin = "// Formular Plugin - Constraint Signature Begin";
			$sgn_end = "// Formular Plugin - Constraint Signature End";
			$sgn_warning = "// Don't remove these lines! Removing can lead to Data Loss in this File!";

			chdir("lib/"); // To ignore the ../ which is used for niceURLs
			$this->writeDebugInfo("Current Directory: ".getcwd());
			if(isset($this->scriptPath) && $this->scriptPath != '' && file_exists($this->scriptPath)) {
				$this->writeDebugInfo("Writing from: ../".$this->mailPath."constraint.tmp to: $this->scriptPath");
				$constraints = file_get_contents("../".$this->mailPath.'constraint.tmp');
				$constraints = "<?php \n"
								. $sgn_begin . "\n"
								. $sgn_warning . "\n"
								. $this->getBackLinkVariable() . "\n"
								. $constraints . "\n"
								. $sgn_end . "\n ?>\n";

				$lines = file($this->scriptPath);
				$position_bgn = array_search($sgn_begin."\n", $lines);
				$position_end = array_search($sgn_end."\n", $lines);
				if($position_bgn != false && $position_end != false) {
					array_splice($lines, $position_bgn-1, $position_end-$position_bgn+3);
				}
				
				foreach ($lines as $line) {
					$original_file .= $line;
				}
				$file = fopen($this->scriptPath,'w') or die($this->getLocalLang('cannot_open'));
				fwrite($file, $constraints . $original_file);
				fclose ($file);
				//file_put_contents($this->scriptPath, $constraints . $original_file); not used because not implemented below php5
			} else {
				$this->writeDebugInfo("Error: ScriptPath not set or File Does not Exist - ScriptPath: $this->scriptPath");
			}
			chdir("../");
		} else {
			$this->writeDebugInfo("Error: InternalScript: $this->internalScript - Constraint-File: $this->mailPath constraint.tmp");
		}
		unlink($this->mailPath.'constraint.tmp');
	}
	
	function createMailForm($match) {
		$this->writeDebugInfo("Creating MailForm");
		$daten = explode(" ", substr($match, 13, -1));
		$receipt=$daten[0];
		$args = explode('"', substr($match, 13, -1));
		
		// Parameter PHP used?
		if ($this->getConf('AllowInclude')) {
			$phpPos = strpos($match, 'PHP:');
			if ($phpPos !== false) {
				$include = "include(\""
								.substr($match, $phpPos +5, strpos($match, '"', $phpPos+5) - ($phpPos+5))
								."\");\n";
				$f = fopen($this->mailPath.'incl.tmp','w') or die($this->getLocalLang('cannot_open'));
				fwrite($f, $include);
				fclose($f);
			} else {
				$include = '';
			}
		}
		
		if (isset($_SERVER["SCRIPT_URI"])) {
			$URI = $_SERVER["SCRIPT_URI"] . "?id=";
		} else {
			$URI = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] . "?id=";
		}
		
		// Parameter Next used?
		$nextPos = strpos($match, 'NEXT:');
		if ($nextPos !== false) {
			$next = substr($match, $nextPos +6 , strpos($match, '"', $nextPos+6) - ($nextPos+6));
		}
		if (substr($next, 0, 4) == "www.") {
			$next = "http://" . $next;
		} elseif (substr($next, 0, 7) == "http://") {
			$next = $next;
		} else {
			$next = $URI.$next;
		}
		
		$mail = 'if ($_POST[\'submit\'] == true) { '. " \n" .
							'$date=date(\'d.m.Y\'); '. " \n" .
							'$time=date(\'H,i,s\'); '. " \n" .
							'$receipt=\'' . $receipt . "'; \n" .
							'$next="' . $next . "\"; \n" .
							'$string="'.$this->getLang('received_on').' $date : $time: "';
		$f = fopen($this->mailPath . 'mail.tmp','w') or die($this->getLocalLang('cannot_open'));
		fwrite($f, $mail);
		fclose($f);
	}

	function createConstraints($line, $name, $error) {
		$grepPos = strpos($line, 'grep=');
		$grepnotPos = strpos($line, 'grepnot=');
		$minLengthPos = strpos($line, 'minLength=');
		$maxLengthPos = strpos($line, 'maxLength=');
		$valueTypePos = strpos($line, 'valueType=');
		$evalFunctionPos = strpos($line, 'evalFunction=');
		$lengthPos = strpos($line, 'length=');
		$grepnotPos = strpos($line, 'grepnot=');

		if ($grepPos !== false) {
			$grep = substr($line, $grepPos+6 ,
									strpos($line, '"', $grepPos+6) - ($grepPos+6));
			$constraint .= 'if (! preg_match(\''.$grep.'\', $_POST[\''.$name.'\']))'."\n"
									.$this->constraintError($error);
		}
		if($grepnotPos !== false) {
			$grepnot = substr($line, $grepnotPos+9 ,
									strpos($line, '"', $grepnotPos+9) - ($grepnotPos+9));
			$constraint .= 'if(preg_match(\'' . $grepnot . '\', $_POST[\'' . $name . '\']))' . "\n"
									.$this->constraintError($error);
		}
		if ($lengthPos !== false) {
			(int) $length = substr($line, $lengthPos+8 ,
													strpos($line, '"', $lengthPos+8) - ($lengthPos+8));
			if (! is_numeric($length)) {
				$data .= $this->insertError($this->getLocalLang('len_not_num'));
			}
			$constraint .= 'if (strlen($_POST[\''.$name.'\']) != '.$length.')'."\n"
									.$this->constraintError($error);
		}
		if ($minLengthPos !== false) {
			(int) $minLength = substr($line, $minLengthPos+11 ,
														strpos($line, '"', $minLengthPos+11) - ($minLengthPos+11));
			if (! is_numeric($minLength)) {
				$data .= $this->insertError($this->getLocalLang('minlen_not_num'));
			}
			$constraint .= 'if (strlen($_POST[\''.$name.'\']) < '.$minLength.')'."\n"
									.$this->constraintError($error);
		}
		if ($maxLengthPos !== false) {
			(int) $maxLength = substr($line, $maxLengthPos+11 ,
														strpos($line, '"', $maxLengthPos+11) - ($maxLengthPos+11));
			if (! is_numeric($maxLength)) {
				$data .= $this->insertError($this->getLocalLang('maxlen_not_num'));
			}
			$constraint .= 'if (strlen($_POST[\'' . $name . '\']) > '.$maxLength.')'."\n"
									.$this->constraintError($error);
		}
		if ($valueTypePos !== false) {
			$valueType = substr($line, $valueTypePos+11 ,
											strpos($line, '"', $valueTypePos+11) - ($valueTypePos+11));
			switch ($valueType) {
			case 'integer' :
				$constraint .= 'if( (! preg_match("/^\d+$/", $_POST[\''.$name.'\'])) '
										.'|| (! is_numeric($_POST[\'' . $name . '\'])) )'."\n"
										. $this->constraintError($error);
				break;
			case 'float' :
				$constraint .= 'if(! is_numeric($_POST[\''.$name.'\']))'."\n "
										.$this->constraintError($error);
				break;
			case 'eMail' :
				$constraint .= '$pattern = "/^[A-z0-9\._-]+@[A-z0-9][A-z0-9-]*(\.[A-z0-9_-]+)*\.([A-z]{2,6})$/";';
				$constraint .= "\n"
										.'if (! preg_match($pattern, $_POST[\''.$name.'\']))'."\n"
										.$this->constraintError($error);
				break;
			}
		}
		if($grepnotPos !== false) {
				$grepnot = substr($line, $grepnotPos+9 , strpos($line, '"', $grepnotPos+9) - ($grepnotPos+9));
				$constraint .= 'if(preg_match(\'' . $grepnot . '\', $_POST[\'' . $name . '\']))' . "\n"
						. $this->constraintError($error);
		}

		if ($this->getConf('AllowInclude')) {
			if ($evalFunctionPos !== false) {
				$evalFunction = substr($line, $evalFunctionPos+14 ,
													strpos($line, '"', $evalFunctionPos+14) - ($evalFunctionPos+14));
				$constraint .= 'if(! '.$evalFunction.'($_POST[\''.$name.'\']))'."\n"
										.$this->constraintError($error);
			}
		}

		return $constraint;
	}

	function createFileElement($text, $width, $value) {
		$result = '<tr><td>'
					.$this->parsed($text) 
					.'</td><td>'
					.'<input class="edit" type="file" size="50" '
					.'name="file[formular][]"';

		if(isset($width) and $width != "") {
			$result .= ' style="width:'.$width.'px;"';
		}
		if(isset($value) and $value != "") {
			$result .= ' value="' . $value . '"';
		}
		
		$result .= ' /></td></tr>';
		return $result;
	}

	function createTextbox($name, $text, $width, $value) {
		$result = '<tr><td>'
					.$this->parsed($text) 
					.'</td><td>'
					.'<input class="edit" type="text" '
					."name=\"$name\"";

		if(isset($width) and $width != "") {
			$result .= ' style="width:'.$width.'px;"';
		}
		if(isset($value) and $value != "") {
			$result .= 'value="' . $value . '"';
		}
		
		$result .= ' /></td></tr>';
		return $result;
	}

	function createHidden($name, $text) {
		if ($name == 'lang') {
			$this->setLocalLang($text);
		}
		return '<input type="hidden" '
				.'name="'.$name
				.'" value="'.$this->phpParsed($text).'" />';
	}
	
	function createPassbox($name, $text, $width) {
		$result = '<tr><td>'
					.$this->parsed($text) 
					.'</td><td>'
					.'<input class="edit" type="password" '
					."name=\"$name\"";

		if(isset($width) and $width != "") {
			$result .= ' style="width:'.$width.'px;"';
		}
		$result .= ' /></td></tr>';
		return $result;
	}

	function createSelectBox($name, $text, $width, $options, $value) {
		$result = '<tr><td>' 
					.  $this->parsed($text) 
					.'</td><td>'
					.'<select name="' . $name . '" size="1"';

		if(isset($width) and $width != "") {
			$result .= ' style="width:'.$width.'px;"';
		}
		
		$result .= '><option value="n/a">[ '.$this->getLocalLang('pls_choose').' ]</option>';
					
		$first = '';
		// create Options
		foreach ($options as $option) {
			if(trim($option) == trim($value)) {
				$checked = 'selected';
			} else {
				$checked = '';
			}
		
			// this is for escape sequences
			if ($option[strlen($option)-1] == "\\" && $first != '') {
				$first = $first . ',' . substr($option, 0, -1);
			} else if ($option[strlen($option)-1] == "\\" && $first == '') {
				$first = substr($option, 0, -1);
			} else if ($option[strlen($option)-1] != "\\" && $first != '') {
				$result .= "<option $checked>".$first.','.$option."</option>";
				$first = '';
			} else if ($option[strlen($option)-1] != "\\" && $first == '') {
				$result .= "<option $checked>".$option."</option>";
			}
		}
		
		$result .= '</select></td></tr>';
		return $result;
	}

	function createTextarea($name, $text, $value, $rows, $width) {
		$result = '<tr><td>'
				.$this->parsed($text)
				.'</td><td>'
				.'<textarea class="edit" name="'.$name
				.'" rows="'.$rows.'"';

		if(isset($width) and $width != "") {
			$result .= ' style="width:'.$width.'px;"';
		}
		$result .= '>';
		if(isset($value) and $value != "") {
			$result .= str_replace("<br/>",chr(13).chr(10),$value);
		}
		$result .= '</textarea></td></tr>';

		return $result;
	}

	function createRadioButton($name, $text, $options, $value) {
		foreach ($options as $option) {
			// first is for escape sequence to print commata
			$first = '';
			$header = '';

			if ($option[strlen($option)-1] == "\\") {
				$first = substr($option, 0, -1);
				continue;
			}
			if ($first != '') {
				$first = $first.",";
				$i = 1;
			} else {
				$i = 0;
			}
			
			if (trim($option) == trim($value) || trim($first.$option) == trim($value)) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}
			// Is this the first Option? If first is set the first Option has the index 1 not 0
			if ($option == $options[$i]) {
				if ($value=='') $checked = 'checked="checked"';
				$header = $this->parsed($text);
			}
			
			$result .= '<tr><td>'.$header.'</td><td>'
						.'<input type="radio" name="'.$name
						.'" value="'.$first.$option.'" '
						.$checked.'>&nbsp;&nbsp;'
						.$first.$option
						.'</td></tr>';
		}
		return $result;
	}
	
	function setMailFile() {
		$this->writeDebugInfo("Function: setMailFile()");
		global $ID;
        $mailFilePath = $this->getConf('mailPath');
		if (! is_dir($mailFilePath)) mkdir($mailFilePath, 0777);

		// support namespaces
		$idSplit = explode(":", $ID);
		if (count($idSplit) > 1) {
			for ($i = 1; $i < count($idSplit); $i++) {
				$mailFilePath .= $idSplit[($i-1)] . "/";
				if (! is_dir($mailFilePath)) mkdir($mailFilePath, 0777);
			}
			$this->mailFile = $mailFilePath . "form_" . $idSplit[$i-1] . ".php";
		} else {
			$this->mailFile = $mailFilePath . "form_" . $ID . ".php";
		}
		$this->mailPath = $mailFilePath;
		$this->writeDebugInfo("MailPath: $mailFilePath");
		$this->writeDebugInfo("MailFile: $this->mailFile");
	}

	function getFileUploadScript() {
		return '$boundary = strtoupper(md5(uniqid(time())));' . "\n"
				. '$mail_header  = "From: '.$this->getConf('mailFrom').'\n";' . "\n"
				. '$mail_header .= "MIME-Version: 1.0";' . "\n"
				. '$mail_header .= "\nContent-Type: multipart/mixed; boundary=$boundary";' . "\n"
				. '$mail_header .= "\n\nThis is a multi-part message in MIME format  --  Dies ist eine mehrteilige Nachricht im MIME-Format";' . "\n"
				. '$mail_header .= "\n--$boundary";' . "\n"
				. '$mail_header .= "\nContent-Type: text/plain";' . "\n"
				. '$mail_header .= "\nContent-Transfer-Encoding: 8bit";' . "\n"
				. '$mail_header .= "\n\n$string";' . "\n"
				. '$i = 0;' . "\n"
				. 'while (is_uploaded_file($_FILES[\'file\'][\'tmp_name\'][\'formular\'][$i])) {' . "\n"
				. '    if ($_FILES[\'file\'][\'size\'][\'formular\'][$i] > ($_POST[\'file_maxsize\'] * 1000000)) {' . "\n"
				. '        $back .= "error='.$this->getLocalLang('filesize_exceeded').'";' . "\n"
				. '        header("Location: $back");' . "\n"
				. '        exit();' . "\n"
				. '    }' . "\n"
				. '    $file_content = fread(fopen($_FILES[\'file\'][\'tmp_name\'][\'formular\'][$i],"r"),$_FILES[\'file\'][\'size\'][\'formular\'][$i]);' . "\n"
				. '    //$file_content = readfile($_FILES[\'file\'][\'tmp_name\'][\'formular\'][$i]);' . "\n"
				. '    $file_content = chunk_split(base64_encode($file_content));' . "\n"
				. '    $mail_header .= "\n--$boundary";' . "\n"
				. '    $mail_header .= "\nContent-Type: ".$_FILES[\'file\'][\'type\'][\'formular\'][$i]."; name=\"".$_FILES[\'file\'][\'name\'][\'formular\'][$i]."\"";' . "\n"
				. '    $mail_header .= "\nContent-Transfer-Encoding: base64";' . "\n"
				. '    $mail_header .= "\nContent-Disposition: attachment; filename=\"".$_FILES[\'file\'][\'name\'][\'formular\'][$i]."\"";' . "\n"
				. '    $mail_header .= "\n\n$file_content";' . "\n"
				. '    $i++;' . "\n"
				. '}' . "\n"
				. '$mail_header .= "\n--$boundary--";' . "\n";
	}

	// create Constraints Backlink
	function getBackLinkVariable() {
		return '$back = $_POST["formular_url"]."?";'
				. "\n".'$blacklist=array("next","formular_url","submit","error","file_maxsize");'
				. "\n".'foreach($_POST as $key_name => $key_value) {'
				. "\n".'  if (in_array($key_name, $blacklist)) continue;'
				. "\n".'  $key_value = str_replace(chr(10),"<br/>",$key_value);'
				. "\n".'  $key_value = str_replace(chr(13),"",$key_value);'
				. "\n".'  $back .= $key_name . "=". utf8decode($key_value) . "&";'
				. "\n}\n";
	}
							
	//Only for Internal USE!
	function printAllInternInformation() {
		echo "\nMETHODS: \n";
		print_r(get_class_methods($this));
		echo "\nMETHODS-END: \n";
		echo "\nVARIABLES: \n";
		print_r(get_object_vars($this));
		echo "\nVARIABLES-END: \n";
		echo "\nGLOBALS: \n";
		print_r($GLOBALS);
		echo "\nGLOBALS-END: \n";
	}
	
	/**
	* Handle the match
	*/
	function handle($match, $state, $pos, &$handler) {
		global $ID;
		
		switch ($state) {
		case DOKU_LEXER_ENTER :
			$this->writeDebugInfo("DOKU_LEXER_ENTER"); $this->writeDebugInfo("WorkingDirectory: ".getcwd());
			$this->setMailFile();
			// MAILTO-Formular
			if (substr($match, 6, 7) == "MAILTO:") {
				$this->createMailForm($match);
				$action = DOKU_URL . $this->mailFile;
			} else {
				$args = explode('"', $match);
				//DOKUWIKI-PAGE
				if ((substr($args[1], 0, 7) != "http://") 
						&& (substr($args[1], -4, 4) != ".php") 
						&& (substr($args[1], 0, 3) != "www")) {
					$this->scriptPath = "doku.php?id=" . $args[1];
				//External Link without HTTP
				} elseif (substr($args[1], 0, 4) == "www.") {
					$this->scriptPath = "http://". $args[1];
				//External Link with HTTP
				} elseif (substr($args[1], 0, 7) == "http://") {
					$this->scriptPath = $args[1];
 				// Internal PHP-Script-Path
				} elseif (substr($args[1], -4, 4) == ".php") {
					$this->scriptPath = $args[1];
					$this->internalScript = true;
				} else {
					$this->scriptPath = $args[1];
				}
				
				$this->writeDebugInfo("ScriptPath: $this->scriptPath");
				$action = $this->scriptPath;
			}
			$data = "<form action=\"$action\" method=\"post\" accept-charset=\"utf-8\" "
						. "enctype=\"multipart/form-data\"> <table class=\"formular\">";

			if (isset($_GET['error'])) {
				$data .= '<tr><td colspan="2"><span style="color:#FF0000;">'
							. $_GET['error']
							. '</span></td></tr>';
			}
			$data .= $this->createHidden("formular_url", DOKU_URL."doku.php/".$ID);
			break;
			
		case DOKU_LEXER_EXIT :
			$this->writeDebugInfo("DOKU_LEXER_EXIT_BEGIN");
			$data = '</table></form>';
			if (is_file($this->mailPath.'mail.tmp')) {
                $subject = $this->getConf('mailSubject');
                $mail = '; '." \n"
                		. '$string .= "\nSender: ".$_SERVER[\'LOGON_USER\'].$_SERVER[\'REMOTE_USER\'];'." \n";
                $mail .= $this->getFileUploadScript();
				$mail .= 'mail($receipt, \''.$subject.'\', "", $mail_header);'
						. " \n"
						. 'header("Location: $next"); '
						. " \n } \n ?>";
						
				$f = fopen($this->mailPath.'mail.tmp','a') or die($this->getLocalLang('cannot_open'));
				fwrite($f, $mail);
				fclose($f);
				
				$mailhead = "<?php\n";
				if (is_file($this->mailPath.'incl.tmp')) {
					$mailhead .= file_get_contents($this->mailPath.'incl.tmp');
					unlink($this->mailPath.'incl.tmp');
				}
				// create Constraints Backlink
				$mailhead .= $this->getBackLinkVariable();
							
				$this->writeBefore ($this->mailPath.'mail.tmp', $mailhead);
				$this->writeDebugInfo("Copy From ".$this->mailPath.'mail.tmp - To: '.$this->mailFile);
				copy($this->mailPath.'mail.tmp', $this->mailFile);
				unlink($this->mailPath.'mail.tmp');
			// Write Constraints to self written Script-Files
			} elseif(is_file($this->mailPath.'constraint.tmp')) {
				$this->writeConstraintsToScript();
			}
			$this->writeDebugInfo("DOKU_LEXER_EXIT_END");
			break;
						
		// Complete Body between <FORM> and </FORM>
		default:
			$lines = explode(';', $match);
			// This is for global ; escape sequence
			foreach ($lines as $index => $line){
				if (isset($firstPart) && $firstPart != '') {
					$lines[$index] = $firstPart.$line;
					$line = $lines[$index];
					unset($firstPart);
				}
				if (substr($line, -1) == '\\') {
					$firstPart = substr($line, 0, -1).';';
					unset($lines[$index]);
					continue;
				}
			}
			$nameArray = array();

			foreach ($lines as $line) {
				$explBlank = explode(' ', $line);
				$explQuoteMark = explode('"', $line);
				$lastArgs = explode(' ', trim($explQuoteMark[count($explQuoteMark)-1]));
				$name = trim($explBlank[1]);
				$text = trim($explQuoteMark[1]);
				$value = $_GET["$name"];
			
				// Keyword
				switch (trim($explBlank[0])) {
				case 'Textbox':
				case 'Passbox':
				case 'Hidden' :
					// number of arguments given is correct?
					if (count($explQuoteMark) != 3 || count($explBlank) < 2) {
						$data .= $this->insertError($name, 1);
						break;
					}
					// routine to check if names are unique
					if (in_array($name, $nameArray)) {
						$data .= $this->insertError($name, 0);
						break;
					}
					array_push($nameArray, $name);
					
					$width = $lastArgs[0];
					if (trim($explBlank[0]) == 'Textbox') {
						$data .= $this->createTextbox($name, $text, $width, $value);
					} elseif (trim($explBlank[0]) == 'Passbox') {
						$data .= $this->createPassbox($name, $text, $width);
					} else {
						$data .= $this->createHidden($name, $text);
					}
					if (is_file($this->mailPath.'mail.tmp')) {
						$mail .= " . \"\\n $name ==> \" . \$_POST['$name'] " . "\n";
					}
					break;
				
				case 'Select' :
					// SelectBox with manual Options given by a list	
					if (count($explQuoteMark) == 5
							&& count(explode(' ', trim($explQuoteMark[4]))) == 1
							&& count(explode(' ', trim($explQuoteMark[0]))) == 2
							&& trim($explQuoteMark[2]) == '' ) {
						$width = $lastArgs[0];
						$optionsList = $explQuoteMark[3];
						$options = explode(",", $optionsList);
					}
					// SelectBox defined in wikipage select
					elseif (count($explQuoteMark) == 3
									&& count(explode(' ', trim($explQuoteMark[0]))) == 2
									&& (count(explode(' ', trim($explQuoteMark[2]))) == 2 
											|| count(explode(' ', trim($explQuoteMark[2]))) == 1)) {
						$optName = $lastArgs[0];
						$width = $lastArgs[1];
						
                        $selectPage = $this->getConf('selectPage');
						
						$found = 0;
						$selectPath = "data/pages";
						$selectSplit = explode(':', $selectPage);
						foreach ($selectSplit as $split) {
							$selectPath .= "/" . $split;
						}
						$selectPath .= ".txt";
						
						if (file_exists($selectPath)){
							$fileLines = file($selectPath);
							foreach ($fileLines as $fileLine) {
								$lineElements = explode(' ', $fileLine);
								// Is the defined name equal to name in argument list?
								if ($lineElements[0] == $optName) {
									$options = explode(",", substr($fileLine, strlen($lineElements[0])+1));
									$found = 1;
								}
							}
						}
						// Named SelectBox not found in WikiPage
						if($found == 0) {
							$data .= $this->insertError($optName, 2);
							break;
						}
					} else { // arguments where not correct
						$data .= $this->insertError($name, 1);
						break; 
					}
					
					// routine to check if names are unique
					if (in_array($name, $nameArray)) {
						$data .= $this->insertError($name, 0);
						break; 
					}
					array_push($nameArray, $name);
					
					if (is_file($this->mailPath.'mail.tmp')) {
						$mail .= " . \"\\n $name ==> \" . \$_POST['$name'] " . "\n";
					}

					$data .= $this->createSelectBox($name, $text, $width, $options, $value);
					break;
					
				case 'Checkbox' :
					// number of arguments given is correct?
					if (count($explQuoteMark) != 3 
							|| count(explode(' ', trim($explQuoteMark[0]))) != 2 
							|| count(explode(' ', trim($lastArgs))) != 1 ) {
						$data .= $this->insertError($name, 1);
						break;
					}
					// routine to check if names are unique
					if (in_array($name, $nameArray)) {
						$data .= $this->insertError($name, 0);
						break;
					}
					array_push($nameArray, $name);
					
					// should checkbox be checked
					// Not working proberly if Standard=checked but not checked when sending
					// In this case the value is not set and therefore it is checked again when coming back
					$checked = '';
					if (isset($value)) {
						if ($value='on') $checked = 'CHECKED';
					} else {
						if ($lastArgs[0] == "1") $checked = 'CHECKED';
					}
					
					if (is_file($this->mailPath.'mail.tmp')) {
						$mail .= " . \"\\n $name ==> \" . \$_POST['$name'] " . "\n";
					}
					
					$data .= '<tr><td colspan="2">'
								.'<input type="checkbox" '
								.'name="'.$name.'" '
								.$checked
								.'  value="'.$text.'"/>&nbsp;&nbsp;'
								.$this->parsed($text)
								.'</td></tr>';
					break;
					
				case 'Textarea' :
					// number of arguments given is correct?
					if (count($explQuoteMark) != 3 
							|| ( count($lastArgs) != 1 
									&& count($lastArgs) != 2 )) {
						$data .= $this->insertError($name, 1);
						break;
					}
					
					// routine to check if names are unique
					if (in_array($name, $nameArray)) {
						$data .= $this->insertError($name, 0);
						break;
					}
					array_push($nameArray, $name);
					
					if (is_file($this->mailPath.'mail.tmp')) {
						$mail .= " . \"\\n $name ==> \\\" \" . \$_POST['$name'] . \" \\\" \" " . "\n";
					}

					$rows = $lastArgs[0];
					$width = $lastArgs[1];
					$data .= $this->createTextarea($name, $text, $value, $rows, $width);
					break;
					
				case 'Submit' :
					// number of arguments given is correct?
					if ( count($lastArgs) != 1 
							|| count($explQuoteMark) != 3) {
						$data .= $this->insertError($explQuoteMark[1], 1);
						break;
					}
					
					$data .= '<tr><td></td><td>'
								.'<input class="button" type="submit" name="submit" '
								.'value="'.$explQuoteMark[1]
								.'" style="width:'.$lastArgs[0].'px;" />'
								.'</td></tr>';
					break;

				case 'Static' :
					switch (count($explQuoteMark)) {
					case 3 :
						$data .= "<tr><td colspan=\"2\">" 
									.$this->parsed($explQuoteMark[1])
									."</td></tr>";
						break;
					case 5 :
						$data .= "<tr><td>" 
									.$this->parsed($explQuoteMark[1])
									."</td><td>" 
									.$this->parsed($explQuoteMark[3])
									."</td></tr>";
						break;
					default :
						$data .= $this->insertError($explQuoteMark[0], 1);
					}
					break;

				case 'Radio' :
					// number of arguments given is correct?
					if (count($explQuoteMark) != 5 
							|| count(explode(' ', trim($explQuoteMark[0]))) != 2 
							|| count($lastArgs) > 1 
							|| $lastArgs[0] != '') {
						$data .= $this->insertError($name, 1);
						break; 
					}
					// routine to check if names are unique
					if (in_array($name, $nameArray)) {
						$data .= $this->insertError($name, 0);
						break;
					}
					array_push($nameArray, $name);
					
					$options = explode(',', $explQuoteMark[3]);
					$data .= $this->createRadioButton($name, $text, $options, $value);

					if (is_file($this->mailPath . 'mail.tmp')) {
						$mail .= " . \"\\n $name ==> \" . \$_POST['$name'] " . "\n";
					}
					break;

				case 'File' :
					// number of arguments given is correct?
					if (count($explQuoteMark) != 3
							|| count($explBlank) < 2
							|| count($lastArgs) < 1
							|| $lastArgs[0] == '') {
						$data .= $this->insertError($name, 1);
						break;
					}

					$maxsize = $lastArgs[0];
					$width = $lastArgs[1];
					$data .= $this->createFileElement($text, $width, $value);
					$data .= $this->createHidden('file_maxsize', $maxsize);
					break;
				
				case 'Line' :
					$data .= '<tr><td colspan="2">&nbsp;</td></tr>';
					break;
					
				case 'Buttons' :
					// number of arguments given is correct?
					if (count($explQuoteMark) != 5 
							|| count($lastArgs) != 1 
							|| trim($explQuoteMark[2]) != '') {
						$data .= $this->insertError('Buttons', 1); //TODO: localize string
						break;
					}
					
					$data .= '<tr><td></td><td> '
								.'<input class="button" type="submit" '
								.'value="' . $explQuoteMark[1] 
								.'" style="width:'.$lastArgs[0].'px;" />'
								.'<input class="button" type="reset" '
								.'value="'.$explQuoteMark[3]
								.'" style="width:'.$lastArgs[0].'px;" /> '
								.'</td></tr>';
					break;
					
				case 'Constraint' :
					// number of arguments given is correct?
					if (count($explQuoteMark) < 3 
							|| count(explode(' ', trim($explQuoteMark[0]))) != 2) {
						$data .= $this->insertError('Constraint ' . $name, 1);
						break;
					}
					$error = $explQuoteMark[1];
					$constraint .= $this->createConstraints($line, $name, $error);
					break;
				
				case '':
					break;
				
				// keyword was not recognised
				default:
					$data .= '<tr><td colspan="2"><span style="color:#FF0000;">'
								.$this->getLang('wrong_kw').' ' . trim($explBlank[0]) . '!'
								.'</span></td></tr>';
					break;
				} //switch body
			} //foreach line

			// File for creating Mail Script
			if ($mail != '') {
				if (isset($constraint) && $constraint != '') {
					$this->writebefore($this->mailPath.'mail.tmp', $constraint);
				}
				// file_put_contents($mailFilePath . 'mail.tmp', $mail, FILE_APPEND) or die("Could not write to mailscript temp file: $php_errormsg");
				$f = fopen($this->mailPath.'mail.tmp','a') or die($this->getLocalLang('cannot_open'));
				fwrite($f, $mail);
				fclose($f);
			} else {
				if (isset($constraint) && $constraint != '' && $this->internalScript) {
					$this->writebefore($this->mailPath.'constraint.tmp', $constraint);
				}
			}
		} //switch $state
		
		// return created string
		return $data;
	}
 
	/**
	 * Create output
	 */
	function render($mode, &$renderer, $data) {
		if ($mode == 'xhtml') {
            $renderer->info['cache'] = false;
			$renderer->doc .= $data;
			return true;
		}
		return false;
	}
}

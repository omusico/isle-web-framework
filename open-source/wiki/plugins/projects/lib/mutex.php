<?php

// adapted from http://www.dreamincode.net/code/snippet1666.htm

class Mutex {
    private $filename = NULL;			// The file to be locked
	private $locked = false;
	
	/* Constructor */
	function __construct($filename){
		// Append '.lck' extension to filename for the locking mechanism
		$this->filename = $filename . '.lock';
	}
	
	function __destruct() { $this->release(); }
	/* Methods */
	
	function acquire($block = true, $timeout = 1){
		// Create the locked file, the 'x' parameter is used to detect a preexisting lock
		global $ID;
		global $USERINFO;
		for ($i = 0; $i < $timeout; $i++) {
			$fp = @fopen($this->filename, 'x');
			// If an error occurs fail lock
			if($fp && @fwrite($fp, $USERINFO['name'])) {
				@fclose($fp);
				$this->locked = true;
				return true;
			}
			if (!$block) break;
			sleep(1);
		}
		$this->locked = false;
		return false;
	}
	
	function release(){
		// Delete the file with the extension '.lck'
		if ($this->locked) @unlink($this->filename);
		global $ID;
		$this->locked = false;
	}	
}
?>
#!/usr/bin/php
<?php

$phpfiles = array();

if ($handle = opendir('.')) {
    while (false !== ($file = readdir($handle))) {
        if (preg_match('/php$/',$file ) && $file != 'php_batch.php') {
            $phpfiles[] = "$file";
        }
    }
    closedir($handle); 
}


print "<pre>\n";

foreach ($phpfiles as $file) {
  print $file . "\n";
  system("php $file");
  print "\n\n--------\n\n";
}
print "</pre>\n";


?>

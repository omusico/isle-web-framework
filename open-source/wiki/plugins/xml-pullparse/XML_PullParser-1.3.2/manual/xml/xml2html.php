<?php
if ($handle = opendir('.')) {
    while (false !== ($file = readdir($handle))) {
        if (!is_dir($file)  && preg_match('/\.xml/', $file)) {            
            xml2html($file);
        }
    }
    closedir($handle);
}


function xml2html($file) {
  $cmd = "php article2html.php $file";
  $outfile =  preg_replace('/xml$/',"html",$file);
  echo "$cmd:  $outfile\n";

  exec($cmd, $output);

  $handle = fopen("html/$outfile", 'w');
 
  for($i=0; $i<count($output);  $i++) { 

     if ( preg_match('/<A\s+href/i', $output[$i]) ) {          
         $output[$i] = preg_replace('/(article2html|"refentry2html).php\?fn=/i', "",$output[$i]);
         $output[$i] = preg_replace('/\.xml/i', '.html',$output[$i]);
     }

      if (fwrite($handle, $output[$i] . "\n") === FALSE) {
            echo "Cannot write to file ($filename)";
            exit;
       }
  }
  fclose($handle);  



}

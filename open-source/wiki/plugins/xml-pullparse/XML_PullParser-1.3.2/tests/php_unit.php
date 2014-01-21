#!/usr/bin/php

<?php

  $http = "";
  if(isset($_SERVER['REMOTE_ADDR'])) {
       echo '<style type="text/css">';
       echo 'body { font-size: 10pt; font-weight:bold; line-height: 1.25;}</style>';

       echo "<br>";
       $http = "http";

       echo "<pre>\n";

  }
  echo "t1.php\n";
  system("php t1.php $http"); 
  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
  echo "\n";
 echo "t2.php\n";
  system("php t2.php $http"); 
  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
  echo "\n";
  echo "t3.php\n";
  system("php t3.php $http"); 
  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
 
  echo "\n";
 
 echo "t4.php\n";
  system("php t4.php $http"); 
  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
  echo "\n";

 echo "t1-ns.php\n";
  system("php t1-ns.php $http"); 
  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
  echo "\n";
  
 echo "t2-ns.php\n";
 system("php t2-ns.php $http"); 
 if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
  echo "\n";


 echo "t3-ns.php\n";
  system("php t3-ns.php $http"); 
  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
 

  echo "\n";

  echo "t3a-ns.php\n";
  system("php t3a-ns.php $http"); 
  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
 
  echo "\n";
 
 echo "t4-ns.php\n";
  system("php t4-ns.php $http"); 
  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "<br>";
  }
  echo "\n";

  if(isset($_SERVER['REMOTE_ADDR'])) {
    echo "</pre>\n";
  }
?>





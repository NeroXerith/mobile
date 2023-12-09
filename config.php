<?php
  $server="localhost"; //server ip
  $user="root";// user name
  $pass="";//password
  //$dbname="system_integration"; //database name
  $dbname="renton"; //database name
  $conn= new mysqli($server,$user,$pass,$dbname);
  if($conn->connect_error){
    die('Connection Problem:'.$conn->connect_error);
  }
?>

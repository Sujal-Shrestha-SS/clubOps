<?php

$host = "localhost";
$user = "root";
$pw = "";
$db = "efootball";

$conn = mysqli_connect($host, $user, $pw, $db);

if(!$conn){
    die("Connection failed: " .mysqli_connect_error());
}
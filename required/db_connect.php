<?php 
include_once 'const.php'; 
$mysqli = new mysqli(HOST, USER, DB_PASS, DB_NAME);
$mysqli->set_charset("utf8");
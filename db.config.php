<?php 
$mysql_host = 'localhost';
$username = 'user';
$password = 'pass';
$database = 'database'; 
$db = new PDO('mysql:host='.$mysql_host.';dbname='.$database, $username, $password );
$db->Query('SET NAMES "utf8" COLLATE "utf8_unicode_ci"');
?> 
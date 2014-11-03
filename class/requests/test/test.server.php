<?php


echo "\n||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||\n";
echo "\n\nSERVER INFO: \n\n";
print_r($_SERVER);
echo "\n\nPOST DATA: \n\n";
print_r($_POST);
echo "\n\nCookies:\n\n";
print_r($_COOKIES);

echo "\n\nPHP INPUT DATA: \n\n";
$raw_data = file_get_contents('php://input');
var_dump($raw_data);

?>
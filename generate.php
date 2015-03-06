<?php
require_once('cipher.php');

$cipher = new Cipher(file_get_contents('secret.txt'));

$encryptedtext = $cipher->encrypt(md5($argv[1]));

echo urlencode($encryptedtext)."\n";

?>
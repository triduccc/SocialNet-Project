<?php

if (isset($_GET["cookie"])) {

    echo "<h1>Captured Cookie</h1>";

    echo $_GET["cookie"];

} else {

    echo "No cookie received.";
}

?>

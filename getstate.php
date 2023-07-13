<?php
if (!isset($_GET["postid"])) exit;


if (file_exists("id_" . $_GET["postid"] . ".txt")) {

    $fp = fopen("id_" . $_GET["postid"] . ".txt", 'r+');
    if ($fp) {
        echo fgets($fp);
    }
} else {
    echo "0";
}

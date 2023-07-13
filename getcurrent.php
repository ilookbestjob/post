<?php
if (!isset($_GET["postid"])) exit;


if (file_exists("current_" . $_GET["postid"] . ".txt")) {

    $fp = fopen("current_" . $_GET["postid"] . ".txt", 'r+');
    if ($fp) {
        echo fgets($fp);
    }
} else {
    echo "0";
}

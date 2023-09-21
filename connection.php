<?php
    $mysqli = new mysqli("localhost", "root", "", "buyers");
    if ($mysqli->connect_error) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>

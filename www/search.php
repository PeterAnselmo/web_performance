<?php
require 'config.php';
require 'Database.php';

if(isset($_GET['term'])){
    $dbh = Database::getInstance();
    $result = $dbh->query("select * from users where username like '{$_GET['term']}%'");

    $options = Array();
    while($row = mysqli_fetch_assoc($result)){
        $options[] = $row['username'];
    }
    die(json_encode($options));
}

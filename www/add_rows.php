<?php
require 'config.php';
require 'Database.php';

//http://stackoverflow.com/questions/4356289/php-random-string-generator
function rand_string($length){
    return substr(md5(rand()), 0, $length);
}

function add_row(){
    $dbh = Database::getInstance();
    $query = 'INSERT INTO users(username, fname, lname) VALUES (?, ?, ?)';
    $sth = $dbh->prepare($query);
    $sth->bind_param('sss',rand_string(8), rand_string(5), rand_string(9));
    $result = $sth->execute();
}

function double_rows(){
    $dbh = Database::getInstance();
    $query = 'select count(*) as num_rows from users';
    $result = $dbh->query($query);
    $row = mysqli_fetch_assoc($result);
    echo $row['num_rows'] * 2;
    if($row['num_rows'] == 0){
        add_row();
    } else {
        for($i=0; $i<($row['num_rows'] * 2); ++$i){
            add_row();
        }
    }
}

double_rows();

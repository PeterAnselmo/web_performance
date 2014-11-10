<?php
require 'spyc.php';
$CONFIG = Spyc::YAMLLoad('../config.yml');
define('DEBUGGING', $CONFIG['debug']);
require 'Database.php';
$dbh = Database::getInstance();

$query = "INSERT INTO results (type, num_rows, page_size, request_start, response_end, render_time) 
          VALUES ('{$_POST['type']}', {$_POST['num_rows']}, ceil({$_POST['page_size']}), {$_POST['request_start']}, {$_POST['response_end']}, {$_POST['time']})";

$dbh->query($query);

//echo mysqli_error($dbh);
//file_put_contents('log_time.txt', "{$_POST['type']}\t{$_POST['size']}\t{$_POST['time']}\n", FILE_APPEND | LOCK_EX);

?>

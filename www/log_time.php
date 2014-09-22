<?php

file_put_contents('log_time.txt', "{$_POST['size']}\t{$_POST['time']}\n", FILE_APPEND | LOCK_EX);

?>

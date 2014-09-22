<?php
require 'config.php';
require 'Database.php';

$dbh = Database::getInstance();

$result = $dbh->query('select * from users');
?>
<!doctype html>
<html>
    <head>
        <title>Web Performance Testing</title>
        <script type="text/javascript" src="jquery-2.1.1.min.js"></script>
        <script>
            function measurePerf(){
                var perfEntries = performance.getEntriesByType('mark');
                for(var i=0; i<perfEntries.length; i++){
                    if(window.console){
                        console.log("name: " + perfEntries[i].name +
                                    " Entry Type: " + perfEntries[i].entryType +
                                    " Start: " + perfEntries[i].startTime +
                                    " Durantion: " + perfEntries[i].duration + "\n");
                    }
                }
            }
            jQuery(document).ready(function(){
                performance.mark("task1");
                var foo;
                for(var i=0; i<1000; i++){
                    foo += 1;
                }
                performance.mark("endtask2");

                measurePerf();

                var now = new Date().getTime();
                var num_rows = $('div#num_rows').data('num-rows');
                var page_load_time= now - performance.timing.navigationStart;

                $.post('log_time.php',
                    {'size':num_rows,
                    'time':page_load_time},
                    function(){}
                );

            });
        </script>
<table>
    <tr>
        <th>Username</th>
        <th>First Name</th>
        <th>Last Name</th>
    </tr>
<?php $num_rows = 0 ?>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<?php ++$num_rows ?>
    <tr>
        <td><?php echo $row['username'] ?></td>
        <td><?php echo $row['fname'] ?></td>
        <td><?php echo $row['lname'] ?></td>
    </tr>
<?php } ?>
</table>
<div id="num_rows" data-num-rows="<?php echo $num_rows ?>"></div>
    </body>
</html>

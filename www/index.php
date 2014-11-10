<?php
require 'spyc.php';
$CONFIG = Spyc::YAMLLoad('../config.yml');
define('DEBUGGING', $CONFIG['debug']);
require 'Database.php';

$dbh = Database::getInstance();

$result = $dbh->query('select * from users');
?>
<!doctype html>
<html>
    <head>
        <title>Web Performance Testing</title>
        <script type="text/javascript" src="jquery-2.1.1.min.js"></script>
        <?php if($_GET['type'] == 'autocomplete' || $_GET['type'] == 'autocomplete-ajax'){ ?>
            <script type="text/javascript" src="jquery-ui.min.js"></script>
            <link rel="stylesheet" type="text/css" href="jquery-ui.theme.min.css" />
        <?php } ?>
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

                var now = new Date().getTime();
                var num_rows = $('div#num_rows').data('num-rows');

                console.log('navigationStart: ' + (performance.timing.navigationStart - performance.timing.navigationStart));
                console.log('unloadEventStart: ' + (performance.timing.unloadEventStart - performance.timing.navigationStart));
                console.log('unloadEventEnd: ' + (performance.timing.unloadEventEnd - performance.timing.navigationStart));
                console.log('redirectStart: ' + (performance.timing.redirectStart - performance.timing.navigationStart));
                console.log('redirectEnd: ' + (performance.timing.redirectEnd - performance.timing.navigationStart));
                console.log('fetchStart: ' + (performance.timing.fetchStart - performance.timing.navigationStart));
                console.log('domainLookupStart: ' + (performance.timing.domainLookupStart - performance.timing.navigationStart));
                console.log('domainLookupEnd: ' + (performance.timing.domainLookupEnd - performance.timing.navigationStart));
                console.log('connectStart: ' + (performance.timing.connectStart - performance.timing.navigationStart));
                console.log('connectEnd: ' + (performance.timing.connectEnd - performance.timing.navigationStart));
                console.log('secureConnectionStart: ' + (performance.timing.secureConnectionStart - performance.timing.navigationStart));
                console.log('requestStart: ' + (performance.timing.requestStart - performance.timing.navigationStart));
                console.log('responseStart: ' + (performance.timing.responseStart - performance.timing.navigationStart));
                console.log('responseEnd: ' + (performance.timing.responseEnd - performance.timing.navigationStart));
                console.log('domLoading: ' + (performance.timing.domLoading - performance.timing.navigationStart));
                console.log('domInteractive: ' + (performance.timing.domInteractive - performance.timing.navigationStart));
                console.log('domContentLoadedEventStart: ' + (performance.timing.domContentLoadedEventStart - performance.timing.navigationStart));
                console.log('domContentLoadedEventEnd: ' + (performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart));
                console.log('domComplete: ' + (performance.timing.domComplete - performance.timing.navigationStart));
                console.log('loadEventStart: ' + (performance.timing.loadEventStart - performance.timing.navigationStart));
                console.log('loadEventEnd: ' + (performance.timing.loadEventEnd - performance.timing.navigationStart));

                var total_load_time= now - performance.timing.navigationStart;
                $.post('log_time.php',
                    {'type':'<?php echo $_GET['type'] ?>',
                    'num_rows':num_rows,
                    'page_size':$('html').html().length,
                    'request_start':performance.timing.requestStart - performance.timing.navigationStart,
                    'response_end':performance.timing.responseEnd - performance.timing.requestStart,
                    'time':performance.timing.domContentLoadedEventStart - performance.timing.responseEnd},
                    function(){}
                );

            });
        </script>
<?php if($_GET['type'] == 'list'){ ?>
    <ul>
        <li>U:username, F:fname, L:lname</li>
    <?php $num_rows = 0 ?>
    <?php while($row = mysqli_fetch_assoc($result)){ ?>
    <?php ++$num_rows ?>
        <li>U:<?php echo $row['username'] ?>, F:<?php echo $row['fname'] ?>, L:<?php echo $row['lname'] ?></li> 
    <?php } ?>
    </ul>
<?php } else if($_GET['type'] == 'checkbox'){ ?>
    <form>
    <?php $num_rows = 0 ?>
    <?php while($row = mysqli_fetch_assoc($result)){ ?>
    <?php ++$num_rows ?>
        <input type="checkbox" name="foo" value="<?php echo $row['username']?>" /><?php echo $row['fname'] ?> <?php echo $row['lname'] ?><br />
    <?php } ?>
    </form>

<?php } else if($_GET['type'] == 'dropdown'){ ?>
    <form>
    <select name="foo">
    <?php $num_rows = 0 ?>
    <?php while($row = mysqli_fetch_assoc($result)){ ?>
    <?php ++$num_rows ?>
        <option value="<?php echo $row['username']?>">F:<?php echo $row['fname'] ?> L:<?php echo $row['lname'] ?></option>
    <?php } ?>
    </select>
    </form>

<?php } else if($_GET['type'] == 'fixed-table'){ ?>
    <table>
        <tr>
            <th style="width: 200px;">Username</th>
            <th style="width: 200px;">First Name</th>
            <th style="width: 200px;">Last Name</th>
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
<?php } else if($_GET['type'] == 'autocomplete') { ?>
    <input type="text" name="foo" />
    <script type="text/javascript">
    var options = [
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<?php ++$num_rows ?>
<?php echo "'{$row['username']}{$row['fname']}{$row['lname']}'," ?>
<?php } ?>
        'asdf'];

    jQuery(document).ready(function($){
        $('input[name=foo]').autocomplete({
            source:options
        });
    });

    </script>
<?php } else if($_GET['type'] == 'autocomplete-ajax') { ?>
    <input type="text" name="foo" />
    <script type="text/javascript">

    jQuery(document).ready(function($){
        $('input[name=foo]').autocomplete({
            source: "search.php",
            minLength: 3,
        });
    });

    </script>

<?php } else { ?>
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
<?php } ?>
<div id="num_rows" data-num-rows="<?php echo $num_rows ?>"></div>
    </body>
</html>

<?php	
    $now = time();
    $then = strtotime('07/26/2013 4:00PM');
    echo "<h2>Current time is " . $now . "</h2>";
    echo "<h2>Target time is " . $then . "</h2>";

    if ($now > $then) {
        echo "<h1>We are PAST target time</h1>";

    } else {
        echo "<h1>We are behind target time</h1>";
    }

?>
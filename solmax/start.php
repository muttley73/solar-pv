<?php
/**
 * Created by PhpStorm.
 * User: muttley
 * Date: 03/12/18
 * Time: 15.03
 */

include('SolarMax.php');

// *** config ***
$lat = 39.1501857; #Latitude (+ to N)
$lng = 8.9848062; #Longitude (+ to E)

$ADDR = "10.0.0.123";
$PORT = "12345";
$DEVICE_ADDR = "5";
$TIMEOUT = 2; # seconds

// *** - ***
$sm = new SolarMax($ADDR, $PORT, $DEVICE_ADDR, $TIMEOUT);

while (true) {

    $sunrise = new DateTime();
    $sunrise->setTimestamp(date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 95));
#echo "alba: " . $sunrise->format("H:m:s") . "\n";

    $sunset = new DateTime();
    $sunset->setTimestamp(date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 95));
#echo "tramonto: " . $sunset->format("H:m:s") . "\n";

    $sm->connect();
    $f = $sm->generateReport();
    $sm->close_connect();
//print_r($f);

    if (!empty($f)) {
        $feed['status'] = 'on line';
        foreach ($f as $key => $value) {
            //echo "- ".$value['description']."\n";
            $feed[$value['description']] = $value['value'];
        }
        file_put_contents('feeds.json', json_encode($feed));
        sleep(5);
    } else {
        sleep(10);
    }



//print_r($feed);
}


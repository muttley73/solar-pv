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
$outputJsonFeeds = '/home/muttley/solar-pv/solmax/feeds.json';

$sunrise = new DateTime();
$sunrise->setTimestamp(date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 90));
echo  "\n\nalba: ...............: " . $sunrise->format("H:i:s");

$sunset = new DateTime();
$sunset->setTimestamp(date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 90));
echo  "\ntramonto alle .......: " . $sunset->format("H:i:s");

$now = new DateTime();
if ($now->getTimestamp() > $sunrise->getTimestamp() && $now->getTimestamp() < $sunset->getTimestamp()) {
    $timeToSunSet = $sunset->getTimestamp() - $now->getTimestamp();
    echo "\ntramonto fra $timeToSunSet secondi\n";
    $status = 'on line';
} else {
    $status = 'off line';
}

// *** - ***
$sm = new SolarMax($ADDR, $PORT, $DEVICE_ADDR, $TIMEOUT);

//while (true) {

    echo "\nconnecting  ........ ";
    echo $sm->connect() ? "[connected]" : "[failure]";
    echo "\nretrieve data ...... ";
    $f = $sm->generateReport();
    echo empty($f) ? "[failure]" : "[ok]";
    echo "\nclose connection ... ";
    $sm->close_connect();
    echo "[ok]";
    echo "\nwrite feeds ........ ";
    if (file_exists($outputJsonFeeds)) {
        $arrayFeeds = getFeedsArray($outputJsonFeeds);
    } else {
        $arrayFeeds = [];
    }

    $arrayFeeds['status'] = $status;

    foreach ($f as $key => $item) {
        $arrayFeeds[$item['description']] = $item['value'];
    }

    echo file_put_contents($outputJsonFeeds, json_encode($arrayFeeds)) ? "[ok]":"[failure]";
    //sleep(5);

//}

echo "\n\n";
die();

// ****** function *******

function getFeedsArray($inputFile) {
    try {
        $jsonFeeds = file_get_contents($inputFile);
        $arrayFeeds = json_decode($jsonFeeds, true);
        return is_array($arrayFeeds) ? $arrayFeeds : false;
    } catch (Exception $e) {
        return false;
    }
}


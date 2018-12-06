<?php
/**
 * Created by PhpStorm.
 * User: muttley
 * Date: 03/12/18
 * Time: 15.03
 */

include('SolarMax.php');

// *** config ***
#$lat = 39.1501857; #Latitude (+ to N)
#$lng = 8.9848062; #Longitude (+ to E)

$ADDR = "10.0.0.123";
$PORT = "12345";
$DEVICE_ADDR = "5";
$TIMEOUT = 2; # seconds

// *** - ***

$sunrise = new DateTime();
$sunrise->setTimestamp(date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 95));
#echo "alba: " . $sunrise->format("H:m:s") . "\n";

$sunset = new DateTime();
$sunset->setTimestamp(date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 95));
#echo "tramonto: " . $sunset->format("H:m:s") . "\n";



$sm = new SolarMax($ADDR, $PORT, $DEVICE_ADDR, $TIMEOUT);

$f = $sm->generateReport();
print_r($f);
//echo $f['PAC']['value'];

if (!empty($f)){
  $feed['status'] = 'on line';
  foreach ($f as $key => $value){
	$feed[$value['description']]=$value['value'];
  }

}

file_put_contents('feeds.json', json_encode($feed));

print_r($feed);



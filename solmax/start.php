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
$pidFile = '/home/muttley/solar-pv/solmax/solarMax.pid';
if (file_exists($pidFile)) {
    echo "\nprocesso già in esecuzione\n\n";
    die();
}

if (!file_put_contents($pidFile, '1')) {
    echo "\nnon riesco a creare il file $pidFile\n\n";
    die();
}

function checkSun($lat, $lng) {

    $sunrise = new DateTime();
    $sunrise->setTimestamp(date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 90));
    //echo "\n\nalba: ...............: " . $sunrise->format("H:i:s");

    $sunset = new DateTime();
    $sunset->setTimestamp(date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 90));
    //echo "\ntramonto alle .......: " . $sunset->format("H:i:s");

    $now = new DateTime();
    if ($now->getTimestamp() > $sunrise->getTimestamp() && $now->getTimestamp() < $sunset->getTimestamp()) {
        //$timeToSunSet = $sunset->getTimestamp() - $now->getTimestamp();
        //echo "\ntramonto fra $timeToSunSet secondi\n";
        return 'on line';
    } else {
        return 'off line';
    }
}

while (true) {
    try {
        echo "start...";
        $status = checkSun($lat, $lng);
        echo "\n$status";
        $log = DateTime::RFC822 . "|";
        echo $log;
        $f = [];
        if ($status == 'on line') {
            $sm = new SolarMax($ADDR, $PORT, $DEVICE_ADDR, $TIMEOUT);
            $log .= "connecting=";
            echo $log;
            if ($sm->connect()) {
                $log .= "[ok]|retrieve data=";
                echo $log;
                $f = $sm->generateReport();
                $log .= empty($f) ? "[failure]|" : "[ok]|";
                $log .= "close connection=";
                $sm->close_connect();
                $log .= "[ok]";
                echo $log;
            } else {
                file_put_contents($outputJsonFeeds, json_encode(array_merge(getFeedsArray($outputJsonFeeds), resetSensor())));
                $log .= "write reset sensor|";
                die();
            }

            $log .= "write feeds=";
            echo "\nScrivo feeds";

        } else {

	    file_put_contents($outputJsonFeeds, json_encode(array_merge(getFeedsArray($outputJsonFeeds), resetSensor())));
            $log .= "write reset sensor|";

	}

        if (file_exists($outputJsonFeeds)) {
            $arrayFeeds = getFeedsArray($outputJsonFeeds);
        } else {
            $arrayFeeds = [];
        }

        $arrayFeeds['status'] = $status;

        foreach ($f as $key => $item) {
            $arrayFeeds[$item['description']] = $item['value'];
        }

        $log .= file_put_contents($outputJsonFeeds, json_encode($arrayFeeds)) ? "[ok]" : "[failure]";

        echo $log . "\n";
        sleep(5);
    } catch (Exception $e) {
        echo $e->getMessage();
        die();
    }

}

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

function resetSensor() {
    $template = '{
  "status": "off line",
  "DC_voltage_mV": 0,
  "AC_voltage_mV": 0,
  "DC_current_mA": 0,
  "AC_current_mA": 0,
  "AC_power_Wh": 0,  
  "ac_power_p": 0,    
  "ac_frequency": 0
}';
    return json_decode($template, true);


}

<?php
/**
 * Created by PhpStorm.
 * User: muttley
 * Date: 03/12/18
 * Time: 15.03
 */

include('SolarMax.php');
include('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


// *** config ***
$lat = 39.1501857; #Latitude (+ to N)
$lng = 8.9848062; #Longitude (+ to E)

$ADDR = "10.0.0.123";
$PORT = "12345";
$DEVICE_ADDR = "5";
$TIMEOUT = 5; # seconds
$outputJsonFeeds = '/home/muttley/solar-pv/solmax/feeds.json';
$pidFile = '/home/muttley/solar-pv/solmax/solarMax.pid';
$logFile = "/home/muttley/solar-pv/solmax/log/" . date('Ymd', time()) . ".log";

$log = new Logger('default');
$log->pushHandler(new StreamHandler($logFile, Logger::DEBUG));


if (file_exists($pidFile)) {
    if ($argv[1] == '-f') {
        $log->debug("force mode, remove old pid file");
        unlink($pidFile);
    } else {
        $log->error("found a pid file, another process is running");
        die();
    }

}

if (!file_put_contents($pidFile, '1')) {
    $log->error("unable create pid file " . $pidFile);
    die();
}

function checkSun($lat, $lng) {
    $sun = [];
    $sun['position'] = 'above_horizon';
    $sunrise = new DateTime();
    $sunrise->setTimestamp(date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 90));
    //echo "\n\nalba: ...............: " . $sunrise->format("H:i:s");

    $sunset = new DateTime();
    $sunset->setTimestamp(date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lng, 90));
    //echo "\ntramonto alle .......: " . $sunset->format("H:i:s");

    $now = new DateTime();
    if ($sunset->getTimestamp() < $now->getTimestamp()){
        // add 1 day to sunset
        $sun['position'] = 'under_horizon';
        $sunset->add(new DateInterval('P1D'));
    }
    if ($sunrise->getTimestamp() < $now->getTimestamp()){
        // add 1 day to sunset
        $sunrise->add(new DateInterval('P1D'));
    }

    $timeToSunSet = $sunset->getTimestamp() - $now->getTimestamp();
    $sun['sun_set'] = $sunset;
    $sun['sun_set_second'] = $timeToSunSet;
    $sun['set_human_date'] = $sunset->format(DATE_RFC3339);

    $timeToSunRise = $sunrise->getTimestamp() - $now->getTimestamp();
    $sun['sun_rise'] = $sunrise;
    $sun['sun_rise_second'] = $timeToSunRise;
    $sun['rise_human_date'] = $sunrise->format(DATE_RFC3339);

    return $sun;

}


while (true) {
    try {

        $log->debug(' ---> check sunrise and sunset time');
        $sun = checkSun($lat, $lng);

        if (!is_array($sun) || empty($sun)) {
            $log->error('check sun error');
        }
        $log->debug($sun['position']);
        $log->debug("sun rise at " . $sun['rise_human_date']. " - (".$sun['sun_rise_second'].") second(s)");
        $log->debug("sun set at " . $sun['set_human_date']. " - (".$sun['sun_set_second'].") second(s)");


        $f = [];
        if ($sun['position'] == 'above_horizon') {
            $status = 'on line';
            $sm = new SolarMax($ADDR, $PORT, $DEVICE_ADDR, $TIMEOUT);
            $log->debug("connecting ... ");
            if ($sm->connect()) {
                $log->debug("connect");
                $log->debug("retrieve data");
                $f = $sm->generateReport();
                $log->debug("close connection");
                $sm->close_connect();
            } else {
                file_put_contents($outputJsonFeeds, json_encode(array_merge(getFeedsArray($outputJsonFeeds), resetSensor())));
                $log->error("system off line lan error");
            }

        } else {

            file_put_contents($outputJsonFeeds, json_encode(array_merge(getFeedsArray($outputJsonFeeds), resetSensor())));
            $log->info("system off line sun above horizont");

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

        if (!file_put_contents($outputJsonFeeds, json_encode($arrayFeeds))){
            $log->error("write feed error ".$outputJsonFeeds);
        }

        sleep(5);
    } catch (Exception $e) {
        $log->error($e->getMessage());
        die();
    }

}

$log->debug("exit ...");
$log->close();


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

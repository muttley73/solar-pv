<?php
/**
 * Created by PhpStorm.
 * User: muttley
 * Date: 03/12/18
 * Time: 15.03
 */
include ('config.php');
include ('SolarMax.php');

$ADDR = "10.0.0.123";
$PORT = "12345";
$DEVICE_ADDR = "5";
$TIMEOUT = 5; # seconds

$sm = new SolarMax($ADDR,$PORT,$DEVICE_ADDR,$TIMEOUT);
$c = $sm->getMessage('PAC');
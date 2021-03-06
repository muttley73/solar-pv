<?php
/**
 * Created by PhpStorm.
 * User: muttley
 * Date: 03/12/18
 * Time: 12.11
 */


error_reporting(E_ERROR);


class SolarMax {
    public $queryList;
    private $OPMODES;
    private $handlerSolarMax;
    private $host, $port, $timeout, $device_addr;

    private function convert($value) {
        return hexdec($value);
    }

    public function __construct($host, $port, $deviceAddr, $timeout) {

        $this->host = $host;
        $this->port = $port;
        $this->device_addr = $deviceAddr;
        $this->timeout = $timeout;

        $this->queryList = [
            /*
            "ADR" => ["name" => "Address", "value" => function($v) {return $this->convert($v);} ],
            "TYP" => ["name" => "Type", "value" => function($v) { return "0x" . $this->convert($v);}],
            "SWV" => ["name" => "Software version", "value" => function($v) {sprintf("%1.1f", $this->convert($v) / 10);}],
            "DDY" => ["name" => "Date day", "value" => function($v) {return $this->convert($v);}],
            "DMT" => ["name" => "Date month", "value" => function($v) {return $this->convert($v);}],
            "DYR" => ["name" => "Date year", "value" => function($v) {return $this->convert($v);}],
            "THR" => ["name" => "Time hours", "value" => function($v) {return $this->convert($v);}],
            "TMI" => ["name" => "Time minutes", "value" => function($v) {return $this->convert($v);}],            
            "E11" => ["name" => "???Error 1, number???", "value" => function($v) {return $this->convert($v);}],
            "E1D" => ["name" => "???Error 1, day???", "value" => function($v) {return $this->convert($v);}],
            "E1M" => ["name" => "???Error 1, month???", "value" => function($v) {return $this->convert($v);}],
            "E1h" => ["name" => "???Error 1, hour???", "value" => function($v) {return $this->convert($v);}],
            "E1m" => ["name" => "???Error 1, minute???", "value" => function($v) {return $this->convert($v);}],
            "E21" => ["name" => "???Error 2, number???", "value" => function($v) {return $this->convert($v);}],
            "E2D" => ["name" => "???Error 2, day???", "value" => function($v) {return $this->convert($v);}],
            "E2M" => ["name" => "???Error 2, month???", "value" => function($v) {return $this->convert($v);}],
            "E2h" => ["name" => "???Error 2, hour???", "value" => function($v) {return $this->convert($v);}],
            "E2m" => ["name" => "???Error 2, minute???", "value" => function($v) {return $this->convert($v);}],
            "E31" => ["name" => "???Error 3, number???", "value" => function($v) {return $this->convert($v);}],
            "E3D" => ["name" => "???Error 3, day???", "value" => function($v) {return $this->convert($v);}],
            "E3M" => ["name" => "???Error 3, month???", "value" => function($v) {return $this->convert($v);}],
            "E3h" => ["name" => "???Error 3, hour???", "value" => function($v) {return $this->convert($v);}],
            "E3m" => ["name" => "???Error 3, minute???", "value" => function($v) {return $this->convert($v);}],
            */
            "KHR" => ["name" => "operating_hours", "value" => function ($v) {
                return $this->convert($v);
            }],
            "KDY" => ["name" => "energy_today_wh", "value" => function ($v) {
                return $this->convert($v) * 100;
            }],
            "KLD" => ["name" => "energy_yesterday_Wh", "value" => function ($v) {
                return $this->convert($v) * 100;
            }],
            "KMT" => ["name" => "energy_this_month_kWh", "value" => function ($v) {
                return $this->convert($v);
            }],
            "KLM" => ["name" => "energy_last_monh_kWh", "value" => function ($v) {
                return $this->convert($v);
            }],
            "KYR" => ["name" => "energy_this_year_kWh", "value" => function ($v) {
                return $this->convert($v);
            }],
            "KLY" => ["name" => "energy_last_year_kWh", "value" => function ($v) {
                return $this->convert($v);
            }],
            "KT0" => ["name" => "energy_total_[kWh]", "value" => function ($v) {
                return $this->convert($v);
            }],
            //"LAN" => ["name" => "Language", "value" => function($v) {return $this->convert($v);}],
            "UDC" => ["name" => "DC_voltage_mV", "value" => function ($v) {
                return $this->convert($v) * 100;
            }],
            "UL1" => ["name" => "AC_voltage_mV", "value" => function ($v) {
                return $this->convert($v) * 100;
            }],
            "IDC" => ["name" => "DC_current_mA", "value" => function ($v) {
                return $this->convert($v) * 10;
            }],
            "IL1" => ["name" => "AC_current_mA", "value" => function ($v) {
                return $this->convert($v) * 10;
            }],
            "PAC" => ["name" => "AC_power_Wh", "value" => function ($v) {
                return $this->convert($v) * 500 / 1000;
            }],
            "PIN" => ["name" => "power_installed_mw", "value" => function ($v) {
                return $this->convert($v) * 500 / 1000;
            }],
            "PRL" => ["name" => "ac_power_p", "value" => function ($v) {
                return $this->convert($v);
            }],
            "CAC" => ["name" => "start_ups", "value" => function ($v) {
                return $this->convert($v);
            }],
            /*
            "FRD" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "SCD" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "SE1" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "SE2" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "SPR" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            */
            "TKK" => ["name" => "temerature_heat_sink_c", "value" => function ($v) {
                return $this->convert($v);
            }],
            "TNF" => ["name" => "ac_frequency", "value" => function ($v) {
                return $this->convert($v) / 100;
            }],
            "SYS" => ["name" => "operation_state", "value" => function ($v) {
                return $this->operationState($this->convert($v));
            }],
            /*
            "BDN" => ["name" => "Build number", "value" => function($v) {return $this->convert($v);}],
            "EC00" => ["name" => "EC00", "value" => function($v) {return $this->convert($v);}],
            "EC01" => ["name" => "EC01", "value" => function($v) {return $this->convert($v);}],
            "EC02" => ["name" => "EC02", "value" => function($v) {return $this->convert($v);}],
            "EC03" => ["name" => "EC03", "value" => function($v) {return $this->convert($v);}],
            "EC04" => ["name" => "EC04", "value" => function($v) {return $this->convert($v);}],
            "EC05" => ["name" => "EC05", "value" => function($v) {return $this->convert($v);}],
            "EC06" => ["name" => "EC06", "value" => function($v) {return $this->convert($v);}],
            "EC07" => ["name" => "EC07", "value" => function($v) {return $this->convert($v);}],
            "EC08" => ["name" => "EC08", "value" => function($v) {return $this->convert($v);}],
            */
        ];


        # Operating Modes...
        # For next 'release'...
        # Literal translation from German to English
        $this->OPMODES = [
            '20001,0' => 'codice sconosciuto',
            '20002,0' => 'Insufficient exposure',
            '20003,0' => 'Approach',
            '20004,0' => 'MPP operation',
            '20005,0' => 'codice sconosciuto',
            '20006,0' => 'codice sconosciuto',
            '20007,0' => 'codice sconosciuto',
            '20008,0' => 'Network operation',
            '20009,0' => 'codice sconosciuto'
        ];


        //$this->handlerSolarMax = $this->connect($this->host, $this->port, $this->timeout);
        /*
        if (!$this->handlerSolarMax) {
            echo "\nimpossibile comunicare con l'inverter\n\n";
            die();
        }
        */

    }


    function operationState($code) {
        if (key_exists($code, $this->OPMODES)) {
            return $this->OPMODES[$code];
        }
    }


    public function generateReport() {
        $report = [];
        foreach ($this->queryList as $key => $item) {
            $r = $this->getsmparam($key);
            if ($r === false) {
                continue;
            }
            $report[$key] = $this->getsmparam($key);
        }

        return $report;

    }

    public function getMessage($command) {
        return $this->getsmparam($command);

    }

    function getConnection() {
        return $this->handlerSolarMax;
    }

    function checksum16($msg) {    # calculates the checksum 16 of the given string argument
        $bytes = unpack("C*", $msg);
        $sum = 0;
        foreach ($bytes as $b) {
            $sum += $b;
            $sum = $sum % pow(2, 16);
        }
        return $sum;
    }

    function mkmsg($dst, $command) {    # makes a message with the items in the given array as questions
        $src = 'FB';
        $dst = sprintf('%02X', $dst);
        $len = '00';
        $cs = '0000';
        $msg = is_array($command) ? "64:" . implode(';', $command) : "64:" . $command;
        $len = strlen("{" . $src . ";" . $dst . ";" . $len . "|" . $msg . "|" . $cs . "}");
        $len = sprintf("%02X", $len);
        $cs = $this->checksum16($src . ";" . $dst . ";" . $len . "|" . $msg . "|");
        $cs = sprintf("%04X", $cs);
        return "{" . $src . ";" . $dst . ";" . $len . "|" . $msg . "|" . $cs . "}";
    }

    function getsmparam($command) {
        $V_MSG = $this->mkmsg($this->device_addr, $command);
        $V_RV = fwrite($this->handlerSolarMax, $V_MSG);
        if (!$V_RV) {
            return false;
        }
        # Reading first 9 bytes
        $V_MSG = fread($this->handlerSolarMax, 9);

        if (!preg_match("/([0-9A-F]{2});FB;([0-9A-F]{2})/", $V_MSG, $matches)) {
            return false;
        }

        if ($matches[1] != $this->device_addr) {
            flush();
            fclose($this->handlerSolarMax);
            die("wrong source address: {$matches[1]} != $this->device_addr");
        }
        $V_LEN = hexdec($matches[2]);
        $V_LEN -= 9; # header is already in
        $V_MSG = fread($this->handlerSolarMax, $V_LEN);

        #Logic required here to separately test OPSTATES and return that value
        if (!preg_match('/^\|64:(\w{3})=([0-9A-F]+)\|([0-9A-F]{4})}$/', $V_MSG, $matches)) {
            return false;
        }

        if (isset($matches[1]) && $matches[1] != $command) {
            return false;
        }

        $retval = !isset($retval) ? $this->queryList[$command]['value']($matches[2]) : $retval;
        return ['description' => $this->queryList[$command]["name"], "value" => $retval];
    }

    function close_connect() {
        try {
            fclose($this->handlerSolarMax);
        } catch (Exception $e) {
            echo "\nerrore durante la chiusura della socket\n\n";
            die();
        }
    }

    function connect($timeout = 3) {
        try {
            $fsock = fsockopen($this->host, $this->port, $errno, $errstr, $timeout);
            if (!$fsock) {
                return false;
            }
            $this->handlerSolarMax = $fsock;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}

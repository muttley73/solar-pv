<?php
/**
 * Created by PhpStorm.
 * User: muttley
 * Date: 03/12/18
 * Time: 12.11
 */





class SolarMax {
    public $queryList;
    private $OPMODES;
    private $handlerSolarMax;
    private $host,$port,$timeout,$device_addr;

    private function convert($value) {
        return hexdec($value);
    }

    public function __construct($host,$port,$deviceAddr,$timeout) {

        $this->host = $host;
        $this->port = $port;
        $this->device_addr = $deviceAddr;
        $this->timeout = $timeout;

        $this->queryList = [
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
            "KHR" => ["name" => "Operating hours", "value" => function($v) {return $this->convert($v);}],
            "KDY" => ["name" => "Energy today [Wh]", "value" => function($v) {return $this->convert($v) * 100;}],
            "KLD" => ["name" => "Energy yesterday [kWh]", "value" => function($v) {return $this->convert($v) * 100;}],
            "KMT" => ["name" => "Energy this month [kWh]", "value" => function($v) {return $this->convert($v);}],
            "KLM" => ["name" => "Energy last monh [kWh]", "value" => function($v) {return $this->convert($v);}],
            "KYR" => ["name" => "Energy this year [kWh]", "value" => function($v) {return $this->convert($v);}],
            "KLY" => ["name" => "Energy last year [kWh]", "value" => function($v) {return $this->convert($v);}],
            "KT0" => ["name" => "Energy total [kWh]", "value" => function($v) {return $this->convert($v);}],
            "LAN" => ["name" => "Language", "value" => function($v) {return $this->convert($v);}],
            "UDC" => ["name" => "DC voltage [mV]", "value" => function($v) {return $this->convert($v) * 100;}],
            "UL1" => ["name" => "AC voltage [mV]", "value" => function($v) {return $this->convert($v) * 100;}],
            "IDC" => ["name" => "DC current [mA]", "value" => function($v) {return $this->convert($v) * 10;}],
            "IL1" => ["name" => "AC current [mA]", "value" => function($v) {return $this->convert($v) * 10;}],
            "PAC" => ["name" => "AC power [mW]", "value" => function($v) {return $this->convert($v)*500/1000;}],
            "PIN" => ["name" => "Power installed [mW]", "value" => function($v) {return $this->convert($v) * 500;}],
            "PRL" => ["name" => "AC power [%]", "value" => function($v) {return $this->convert($v);}],
            "CAC" => ["name" => "Start ups", "value" => function($v) {return $this->convert($v);}],
            "FRD" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "SCD" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "SE1" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "SE2" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "SPR" => ["name" => "???", "value" => function($v) {return $this->convert($v);}],
            "TKK" => ["name" => "Temerature Heat Sink", "value" => function($v) {return $this->convert($v);}],
            "TNF" => ["name" => "AC Frequency", "value" => function($v) {return $this->convert($v) / 100;}],
            "SYS" => ["name" => "Operation State", "value" => function($v) {return $this->convert($v);}],
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

        ];


        # Operating Modes...
        # For next 'release'...
        # Literal translation from German to English
        $this->OPMODES = [
            ['mode' => '20001,0', 'desc' => '20001,0'],
            ['mode' => '20002,0', 'desc' => 'Insufficient exposure'],
            ['mode' => '20003,0', 'desc' => 'Approach'],
            ['mode' => '20004,0', 'desc' => 'MPP operation'],
            ['mode' => '20005,0', 'desc' => '20005,0'],
            ['mode' => '20006,0', 'desc' => '20006,0'],
            ['mode' => '20007,0', 'desc' => '20007,0'],
            ['mode' => '20008,0', 'desc' => 'Network operation'],
            ['mode' => '20009,0', 'desc' => '20009,0']
        ];


        $this->handlerSolarMax = $this->connect($this->host,$this->port,$this->timeout);


    }

    public function generateReport(){
        $report = [];
        foreach ($this->queryList as $key => $item){
            $report[]=$this->getMessage();
        }

        return $report;

    }

    public function getMessage($command){
        return $this->getsmparam($command);

    }

    function getConnection(){
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
        if (!$V_RV) die("Write error: $!");
        # Reading first 9 bytes
        $V_MSG = fread($this->handlerSolarMax, 9);

        if (!preg_match("/([0-9A-F]{2});FB;([0-9A-F]{2})/", $V_MSG, $matches)) {
            flush();
            fclose($this->handlerSolarMax);
            die("Invalid response from header");
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
            flush();
            fclose($this->handlerSolarMax);
            die("invalid response");
        }

        if ($matches[1] != $command) {
            flush();
            fclose($this->handlerSolarMax);
            die("wrong response");
        }

        $retval = $this->queryList[$command]['value']($matches[2]);
        return [$this->queryList[$command]["name"], "value" => $retval];
    }

    private function connect($host, $port, $timeout = 3) {
        $fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$fsock) {
            return 0;
        } else {
            return $fsock;
        }
    }

}
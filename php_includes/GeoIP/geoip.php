<?php
require __DIR__ . '/geoip2.phar';
class IP {
    protected static $geoIpReader = false;
    function __construct($ip) {
        if (! $geoIpReader) {
            self::$geoIpReader = new GeoIp2\Database\Reader(__DIR__ . '/GeoLite2-Country.mmdb');
        }
        try {
            $r = self::$geoIpReader->country($ip);
            $this->IP = $ip;
            $this->continent->name = $r->continent->name;
            $this->continent->code = $r->continent->code;
            $this->country->name = $r->country->name;
            $this->country->code = $r->raw['country']['iso_code'];

        } catch (\Throwable $th) {}
    }
}
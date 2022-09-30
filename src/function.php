<?php

if (!function_exists('get_ip_address')) {

    function get_ip_address() {
        if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = !empty($_SERVER["HTTP_CLIENT_IP"]);
        } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknown";
        }
        return $ip;
    }

}

if (!function_exists('get_server_host')) {

    function get_server_host() {
        if (!empty($_SERVER["SERVER_NAME"])) {
            $host = $_SERVER['SERVER_NAME'];
        } else if (getenv("SERVER_NAME")) {
            $host = getenv("SERVER_NAME");
        } else {
            $host = '';
        }
        return $host;
    }

}
<?php

if (!function_exists('get_ip_address')) {
    function get_ip_address() {
        return $_SERVER['REMOTE_ADDR'];
    }
}
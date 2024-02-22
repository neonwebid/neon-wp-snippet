<?php

add_filter('http_request_args', function ($parsed_args, $url) {
    if (strpos($url, 'neon-membership/update') !== false) {
        if (!isset($parsed_args['headers'])) {
            $parsed_args['headers'] = array();
        }
        $parsed_args['headers']['WakhidWicaksono'] = 'Owner';
    }

    return $parsed_args;
}, 10, 2);

<?php

$tasks = [
    [
        'classname' => 'local_dlservices\task\update_cache',
        'blocking' => 0,
        'minute' => '0', // For test, *, for prod */1
        'hour' => '0',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
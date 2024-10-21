<?php

// config/autoload/active-mq.global.php
return [
    'activemq' => [
        'broker_uri' => 'tcp://localhost:61613', // ActiveMQ broker URL
        'queue_name' => '/queue/sms',           // Queue name
        'username' => 'admin',                  // ActiveMQ username
        'password' => 'admin',                  // ActiveMQ password
    ],
];

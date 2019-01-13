<?php

return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => sprintf('%s/.cloudflare',getenv('HOME')),
        ],
    ],
];

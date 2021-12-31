<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Logging',
    'description' => 'Logging',
    'category' => 'be',
    'author' => 'Lukas Schönbeck',
    'author_email' => 'lukas.schoenbeck@hdnet.de',
    'state' => 'alpha',
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.0.0-9.99.99',
            'autoloader' => '*',
        ],
        'conflicts' => [
        ],
    ],
];

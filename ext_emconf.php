<?php

//#######################################################################
// Extension Manager/Repository config file for ext "in2template".
//#######################################################################

$EM_CONF[$_EXTKEY] = [
    'title' => 'migration',
    'description' => 'This extension is a boilerplate extension for any kind of in-database-migrations',
    'category' => 'misc',
    'author' => 'in2code GmbH',
    'author_email' => 'service@in2code.de',
    'dependencies' => 'extbase, fluid',
    'state' => 'stable',
    'author_company' => 'in2code GmbH',
    'version' => '4.0.1',
    'autoload' => [
        'psr-4' => ['In2code\\Migration\\' => 'Classes']
    ],
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

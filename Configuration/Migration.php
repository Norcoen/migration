<?php
return [
    // Default values if not given from CLI
    'configuration' => [
        'key' => '',
        'dryrun' => true,
        'limitToRecord' => null,
        'limitToPage' => null,
        'recursive' => false
    ],

    // Define your migrations
    'migrations' => [
        [
            'className' => \In2code\Migration\Migration\Importer\PageImporter::class,
            'keys' => [
                'page'
            ]
        ],
        [
            'className' => \In2code\Migration\Migration\Migrator\PageMigrator::class,
            'keys' => [
                'page'
            ]
        ],
        [
            'className' => \In2code\Migration\Migration\Migrator\ContentMigrator::class,
            'keys' => [
                'content'
            ]
        ]
    ]
];

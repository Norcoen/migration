<?php
declare(strict_types=1);
namespace In2code\Migration\Migration\Migrator;

use In2code\Migration\Migration\PropertyHelpers\ReplaceCssClassesInHtmlStringPropertyHelper;

/**
 * Class ContentMigrator
 */
class ContentMigrator extends AbstractMigrator implements MigratorInterface
{
    /**
     * @var string
     */
    protected $tableName = 'tt_content';

    /**
     * @var array
     */
    protected $values = [
        'editlock' => '0'
    ];

    /**
     * @var array
     */
    protected $propertyHelpers = [
        'bodytext' => [
            [
                'className' => ReplaceCssClassesInHtmlStringPropertyHelper::class,
                'configuration' => [
                    'condition' => [
                        'CType' => [
                            'textpic',
                            'text',
                            'textmedia'
                        ]
                    ],
                    'tags' => [
                        'a'
                    ],
                    'search' => [
                        'c-button--button',
                        'c-button'
                    ],
                    'replace' => [
                        'btn-primary',
                        'btn'
                    ]
                ]
            ]
        ]
    ];
}

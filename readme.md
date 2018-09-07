# TYPO3 Migration and Importer Boilerplate

## Description
This extension (with extension key **migration**) is a kickstarter extension (boilerplate)
to import or migrate TYPO3 stuff within the same database.
Boilerplate means in this case, take the extension and change it to your needs.

E.g: 
* **Import** from an old to a new table (like from tt_news to news)
* **Migrate** existing records an an existing table (like in tt_content from TemplaVoila to Gridelements)

## Introduction

### What's the roadmap on TYPO3 update and migration projects?

If your migration comes along with a TYPO3 update (like from 6.2 to 8.7 or so), you should go this way:
* Make your new page work with some testpages in a new TYPO3
* Add the functions you need to your small test instance (like news, powermail, etc...)
* Of course I would recommend to store the complete configuration (TypoScript, TSConfig etc...) in an extension 
* Import your old database
* Make a db compare (I would recommend the package typo3_console for this to do this from CLI)
* Make your update wizard steps (I would also recommend the package typo3_console for this to do this from CLI)
* Dump your new database
* Start with adding your own Migrators and Importers
* And then have fun with migrating, rolling back database, update your scripts, migrate again, and so on
* See also https://www.slideshare.net/einpraegsam/typo3-migration-in-komplexen-upgrade-und-relaunchprojekten-85961416

### Hands on

In the starter class some importers or migrators are related to the key "faq":

```
protected $migrationClasses = [
    [
        'className' => FaqImporter::class,
        'configuration' => [
            'migrationClassKey' => 'faq'
        ]
    ],
    [
        'className' => FaqCategoriesProductImporter::class,
        'configuration' => [
            'migrationClassKey' => 'faq'
        ]
    ],
    [
        'className' => FaqCategoriesSysCategoryImporter::class,
        'configuration' => [
            'migrationClassKey' => 'faq'
        ]
    ],
    [
        'className' => CreateRelationsFromProductsImporter::class,
        'configuration' => [
            'migrationClassKey' => 'faq'
        ]
    ],
    [
        'className' => DeleteFaqSysCategoriesDatabaseScript::class,
        'configuration' => [
            'migrationClassKey' => 'faq'
        ]
    ]
];
```

Example Importer class:
```
<?php
namespace In2code\Migration\Migration\Import;

use In2code\Migration\Migration\Import\PropertyHelper\ReplaceCssClassesInHtmlStringPropertyHelper;

/**
 * Class FaqImporter
 */
class FaqImporter extends AbstractImporter implements ImporterInterface
{

    /**
     * Table name where to migrate to
     *
     * @var string
     */
    protected $tableName = 'tx_in2faq_domain_model_question';

    /**
     * Table name from migrate to
     *
     * @var string
     */
    protected $tableNameOld = 'tx_udgmvfaq_domain_model_question';

    /**
     * @var array
     */
    protected $mapping = [
        'question' => 'question',
        'answer' => 'answer',
        'crdate' => 'crdate'
    ];

    /**
     * PropertyHelpers are called after initial build via mapping
     *
     *      "newProperty" => [
     *          [
     *              "className" => class1::class,
     *              "configuration => ["red"]
     *          ],
     *          [
     *              "className" => class2::class
     *          ]
     *      ]
     *
     * @var array
     */
    protected $propertyHelpers = [
        'answer' => [
            [
                'className' => ReplaceCssClassesInHtmlStringPropertyHelper::class,
                'configuration' => [
                    'search' => [
                        'btn',
                        'btn-neg',
                        'btn-green',
                        'btn-blue',
                        'contenttable-2',
                        'contenttable-3',
                        'ul-check'
                    ],
                    'replace' => [
                        'c-button',
                        'c-button--white',
                        'c-button--green',
                        '',
                        'u-table-transparent',
                        'u-table-blue',
                        'u-list-check'
                    ]
                ]
            ]
        ]
    ];
}
```

Example for an individual PropertyHelper class:
```
<?php
namespace In2code\Migration\Migration\Import\PropertyHelper;

use In2code\Migration\Migration\Utility\StringUtility;

/**
 * Class ReplaceCssClassesInHtmlStringPropertyHelper
 * to replace css classes in a HTML-string - e.g. RTE fields like tt_content.bodytext
 *
 *  Configuration example:
 *      'configuration' => [
 *          'search' => [
 *              'class1'
 *          ],
 *          'replace' => [
 *              'class2'
 *          ]
 *      ]
 */
class ReplaceCssClassesInHtmlStringPropertyHelper extends AbstractPropertyHelper implements PropertyHelperInterface
{

    /**
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        if (!is_array($this->getConfigurationByKey('search')) || !is_array($this->getConfigurationByKey('replace'))) {
            throw new \Exception('configuration search and replace is missing');
        }
    }

    /**
     * @return void
     */
    public function manipulate()
    {
        $string = $this->getProperty();
        $replacements = $this->getConfigurationByKey('replace');
        foreach ($this->getConfigurationByKey('search') as $key => $searchterm) {
            $replace = $replacements[$key];
            $string = StringUtility::replaceCssClassInString($searchterm, $replace, $string);
        }
        $this->setProperty($string);
    }

    /**
     * @return bool
     */
    public function shouldImport(): bool
    {
        foreach ($this->getConfigurationByKey('search') as $searchterm) {
            if (stristr($this->getProperty(), $searchterm)) {
                return true;
            }
        }
        return false;
    }
}
```

## Some notes
* Migration: This means migrate existing records in an existing table
* Import: This menas to import values with some logic from table A to table B

In your Migrator or Importer class you can define which record should be changed in which way.
Normally you can choose via class properties:
* if tables should be truncated or not
* if the where clause should be extended to find old records
* change orderings
* if uid should be kept
* etc...

If you extend your new tables with fields like _migrated, _migrated_uid and _migrated_table, they will
be filled automaticly with useful values

## Example CLI calls
```
./vendor/bin/typo3cms migrate:start --key=content --dryrun=0
./vendor/bin/typo3cms migrate:start --key=page --dryrun=1 --limit-to-page=1 --recursive=0
./vendor/bin/typo3cms migrate:start --key=news --dryrun=0 --limit-to-record=123
```

## Additional CommandControllers

- DataHandlerCommandController
  - handleCommand() Do TYPO3 pageactions (normally known from backend) via console. Move, delete, copy complete pages and trees without runtimelimit from CLI
- HelpCommandController
  - getListsOfSubPagesCommand() Simple show a commaseparated list of subpages to a page (helpful for further database commands)
- ImportExportCommandController
  - exportCommand() T3D Export with xml
  - importCommand() T3D Import with xml

## Changelog

| Version    | Date       | State      | Description                                                                  |
| ---------- | ---------- | ---------- | ---------------------------------------------------------------------------- |
| 2.0.0      | 2018-09-07 | Task       | Use extkey migration, add ImportExportCommandController, some improvements   |
| 1.1.1      | 2018-09-07 | Task       | Add Changelog                                                                |
| 1.1.0      | 2017-07-28 | Task       | Add DataHandler and Help CommandControllers                                  |
| 1.0.0      | 2017-07-26 | Task       | Initial release                                                              |

## Future Todos

* Rewrite all database queries with doctrine methods to enable migration extension also for TYPO3 9.x
* Add a fully functional generic importer - e.g. tt_news to tx_news

<?php
declare(strict_types=1);
namespace In2code\Migration\Command;

use In2code\Migration\Service\ImportService;
use In2code\Migration\Utility\DatabaseUtility;
use In2code\Migration\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportCommand
 * offers own json based import command for TYPO3 page-trees to fit the need to insert large page trees into
 * existing TYPO3 instances.
 */
class ImportCommand extends Command
{

    /**
     * Excluded tables for import
     *
     * @var array
     */
    protected $excludedTables = [
        'be_groups',
        'be_users',
        'sys_language',
        'sys_log',
        'sys_news',
        'sys_domain',
        'sys_template',
        'sys_note',
        'sys_history',
        'sys_file_storage',
        'tx_extensionmanager_domain_model_extension',
        'tx_extensionmanager_domain_model_repository',
        'sys_category_record_mm'
    ];

    /**
     * Import: Check if the file is already existing (compare path and name - no size or date)
     * and decide if it should be overwritten or not
     *
     * @var bool
     */
    protected $overwriteFiles = false;

    /**
     * Configure the command
     */
    public function configure()
    {
        $description = 'Importer command to import json export files into a current database. ' .
            'New uids will be inserted for records.' .
            'Note: At the moment only sys_file_reference is supported as mm table ' .
            '(e.g. no sys_category_record_mm support)';
        $this->setDescription($description);
        $this->addArgument('file', InputArgument::REQUIRED, 'Absolute path to a json export file');
        $argumentDescription = 'Page identifier to import new tree into (can also be 0 for an import into root)';
        $this->addArgument('pid', InputArgument::REQUIRED, $argumentDescription);
    }

    /**
     * Importer command to import json export files into a current database. New uids will be inserted for records.
     * Note: At the moment only sys_file_reference is supported as mm table (e.g. no sys_category_record_mm support)
     *
     * Example CLI call: ./vendor/bin/typo3cms migration:import /home/user/export.json 123
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $importService = ObjectUtility::getObjectManager()->get(
            ImportService::class,
            $input->getArgument('file'),
            (int)$input->getArgument('pid'),
            $this->excludedTables,
            $this->overwriteFiles
        );
        try {
            $this->checkTarget((int)$input->getArgument('pid'));
            $importService->import();
            $message = 'success!';
        } catch (\Exception $exception) {
            $message = $exception->getMessage() . ' (Errorcode ' . $exception->getCode() . ')';
        }
        $output->writeln($message);
        return 0;
    }

    /**
     * @param int $pid
     * @return void
     */
    protected function checkTarget(int $pid)
    {
        if ($pid > 0 && $this->isPageExisting($pid) === false) {
            throw new \LogicException('Target page with uid ' . $pid . ' is not existing', 1549535363);
        }
    }

    /**
     * @param int $pid
     * @return bool
     */
    protected function isPageExisting(int $pid): bool
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('pages', true);
        return (int)$queryBuilder
                ->select('uid')
                ->from('pages')
                ->where('uid=' . (int)$pid)
                ->execute()
                ->fetchColumn(0) > 0;
    }
}

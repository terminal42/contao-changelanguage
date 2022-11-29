<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Migration;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class CustomLanguageTextMigration extends AbstractMigration
{
    private Connection $connection;
    private ContaoFramework $framework;

    public function __construct(Connection $connection, ContaoFramework $framework)
    {
        $this->connection = $connection;
        $this->framework = $framework;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_module'])) {
            return false;
        }

        $tableColumns = $schemaManager->listTableColumns('tl_module');

        if (!isset($tableColumns['customlanguagetext'])) {
            return false;
        }

        $texts = $this->connection->fetchFirstColumn('SELECT customLanguageText FROM tl_module');

        foreach ($texts as $text) {
            $text = StringUtil::deserialize($text);

            if (\is_array($text) && isset($text[0]['label'])) {
                return true;
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $this->framework->initialize();

        $records = $this->connection->fetchAllAssociative('SELECT id, customLanguageText FROM tl_module');

        foreach ($records as $record) {
            $data = StringUtil::deserialize($record['customLanguageText']);

            if (\is_array($data) && isset($data[0]['label'])) {
                $newData = [];

                foreach ($data as $datum) {
                    $newData[] = ['key' => $datum['value'], 'value' => $datum['label']];
                }

                $this->connection->update('tl_module', ['customLanguageText' => serialize($newData)], ['id' => $record['id']]);
            }
        }

        return $this->createResult(true);
    }
}

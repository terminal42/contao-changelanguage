<?php

namespace Terminal42\ChangeLanguage\Tests;

use Contao\System;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

abstract class ContaoTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');

        foreach ($connection->getSchemaManager()->listTableNames() as $table) {
            $connection->query('DROP TABLE IF EXISTS '.$table);
        }

        $this->loadFixture('contao3.sql');
    }

    protected function loadFixture($fileName)
    {
        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');

        $query = file_get_contents(__DIR__.'/Fixtures/'.$fileName);

        $connection->exec($query);
    }

    protected function query($statement)
    {
        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');

        $connection->query($statement);

        return $connection->lastInsertId();
    }
}

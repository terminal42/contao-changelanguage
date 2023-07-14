<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests;

use Contao\System;
use Doctrine\DBAL\Connection;
use PHPUnit\Extensions\Database\DataSet\DefaultDataSet;
use PHPUnit\Extensions\Database\TestCase;

abstract class DbTestCase extends TestCase
{
    protected function setUp(): void
    {
        $connection = $this->getConnection();

        foreach ($connection->getSchemaManager()->listTableNames() as $table) {
            $connection->executeQuery('DROP TABLE IF EXISTS '.$table);
        }

        parent::setUp();
    }

    /**
     * @return Connection
     */
    final protected function getConnection()
    {
        return System::getContainer()->get('database_connection');
    }

    /**
     * Tell the unit test to use our actual DB for testing
     * Data is imported in the setUp() method.
     *
     * @return \PHPUnit_Extensions_Database_DataSet_DefaultDataSet|void
     */
    protected function getDataSet()
    {
        return new DefaultDataSet();
    }
}

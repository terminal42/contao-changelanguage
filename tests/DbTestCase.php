<?php

namespace Terminal42\ChangeLanguage\Tests;

use Contao\System;
use Doctrine\DBAL\Connection;

abstract class DbTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    protected function setUp()
    {
        $connection = $this->getConnection();

        foreach ($connection->getSchemaManager()->listTableNames() as $table) {
            $connection->query('DROP TABLE IF EXISTS '.$table);
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
        return new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }
}

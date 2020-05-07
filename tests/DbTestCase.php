<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

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

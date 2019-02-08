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

abstract class DbTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    private static $pdo = null;

    private $conn = null;

    protected function setUp()
    {
        // Empty table
        $pdo = $this->getConnection()->getConnection();
        $stmt = $pdo->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema=:db');
        $stmt->bindParam(':db', $GLOBALS['DB_DBNAME']);

        $stmt->execute();
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ((array) $tables as $table) {
            $pdo->query('DROP TABLE IF EXISTS '.$table);
        }

        parent::setUp();
    }

    final protected function getConnection()
    {
        if (null === $this->conn) {
            if (null === self::$pdo) {
                self::$pdo = new \PDO(
                    sprintf('mysql:host=%s;port=%s;dbname=%s;',
                            $GLOBALS['DB_HOST'],
                            $GLOBALS['DB_PORT'],
                            $GLOBALS['DB_DBNAME']
                    ),
                    $GLOBALS['DB_USER'],
                    $GLOBALS['DB_PASSWD'],
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    ]
                );
            }

            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
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

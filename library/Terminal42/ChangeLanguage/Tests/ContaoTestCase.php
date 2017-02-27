<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2017, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Tests;

abstract class ContaoTestCase extends DbTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixture('contao3.sql');
    }

    protected function loadFixture($fileName)
    {
        $pdo = $this->getConnection()->getConnection();
        $query = file_get_contents(__DIR__.'/Fixtures/'.$fileName);
        $pdo->exec($query);
    }

    protected function query($statement)
    {
        $this->getConnection()->getConnection()->query($statement);

        return $this->getConnection()->getConnection()->lastInsertId();
    }
}

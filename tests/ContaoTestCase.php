<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests;

use Contao\Model\Registry;
use Contao\PageModel;
use Contao\System;
use Contao\TestCase\ContaoDatabaseTrait;
use Contao\TestCase\FunctionalTestCase;

abstract class ContaoTestCase extends FunctionalTestCase
{
    use ContaoDatabaseTrait;

    protected function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
        System::setContainer(static::getContainer());
        static::resetDatabaseSchema();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        static::ensureKernelShutdown();
        Registry::getInstance()->reset();
    }

    protected function query(string $statement): int
    {
        $connection = static::getConnection();

        $connection->executeQuery($statement);

        return (int) $connection->lastInsertId();
    }

    protected function createRootPage(string $dns = '', string $language = '', bool $fallback = true, int $languageRoot = 0, bool $published = true): PageModel
    {
        $pageModel = new PageModel();
        $pageModel->type = 'root';
        $pageModel->title = 'foobar';
        $pageModel->dns = $dns;
        $pageModel->language = $language;
        $pageModel->fallback = $fallback;
        $pageModel->languageRoot = $languageRoot;
        $pageModel->published = $published;

        $pageModel->save();

        return $pageModel;
    }

    protected function createPage(int $pid = 0, int $languageMain = 0, bool $published = true): PageModel
    {
        $pageModel = new PageModel();
        $pageModel->pid = $pid;
        $pageModel->type = 'regular';
        $pageModel->languageMain = $languageMain;
        $pageModel->published = $published;

        $pageModel->save();

        return $pageModel;
    }
}

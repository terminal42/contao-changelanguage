<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests;

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use Symfony\Component\Filesystem\Filesystem;

class ClearCachePhpunitExtension implements BeforeFirstTestHook, AfterLastTestHook
{
    public function executeBeforeFirstTest(): void
    {
        (new Filesystem())->remove([
            __DIR__.'/../var',
        ]);
    }

    public function executeAfterLastTest(): void
    {
        $this->executeBeforeFirstTest();
    }
}

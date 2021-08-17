<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42ChangeLanguageBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}

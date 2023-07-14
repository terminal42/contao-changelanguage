<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\Model;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

interface NavigationHandlerInterface
{
    public function handleNavigation(ChangelanguageNavigationEvent $event, Model $model): void;
}

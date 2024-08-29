<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\ChangeLanguage\Navigation\NavigationItem;
use Terminal42\ChangeLanguage\Navigation\UrlParameterBag;

class ChangelanguageNavigationEvent extends Event
{
    private NavigationItem $navigationItem;

    private UrlParameterBag $urlParameterBag;

    private bool $skipped = false;

    public function __construct(NavigationItem $navigationItem, UrlParameterBag $urlParameters)
    {
        $this->navigationItem = $navigationItem;
        $this->urlParameterBag = $urlParameters;
    }

    /**
     * Gets the navigation item for this event.
     */
    public function getNavigationItem(): NavigationItem
    {
        return $this->navigationItem;
    }

    /**
     * Gets the UrlParameterBag for this navigation item.
     */
    public function getUrlParameterBag(): UrlParameterBag
    {
        return $this->urlParameterBag;
    }

    public function skipInNavigation(): void
    {
        $this->skipped = true;
        $this->stopPropagation();
    }

    public function isSkipped(): bool
    {
        return $this->skipped;
    }
}

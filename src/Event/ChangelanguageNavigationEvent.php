<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Event;

use Terminal42\ChangeLanguage\Navigation\NavigationItem;
use Terminal42\ChangeLanguage\Navigation\UrlParameterBag;

class ChangelanguageNavigationEvent
{
    private NavigationItem $navigationItem;
    private UrlParameterBag $urlParameterBag;
    private bool $skipped = false;
    private bool $stopPropagation = false;

    public function __construct(NavigationItem $navigationItem, UrlParameterBag $urlParameters)
    {
        $this->navigationItem = $navigationItem;
        $this->urlParameterBag = $urlParameters;
    }

    /**
     * Gets the navigation item for this event.
     *
     * @return NavigationItem
     */
    public function getNavigationItem()
    {
        return $this->navigationItem;
    }

    /**
     * Gets the UrlParameterBag for this navigation item.
     *
     * @return UrlParameterBag
     */
    public function getUrlParameterBag()
    {
        return $this->urlParameterBag;
    }

    public function skipInNavigation(): void
    {
        $this->skipped = true;
        $this->stopPropagation();
    }

    public function isSkipped()
    {
        return $this->skipped;
    }

    public function isPropagationStopped()
    {
        return $this->stopPropagation;
    }

    public function stopPropagation(): void
    {
        $this->stopPropagation = true;
    }
}

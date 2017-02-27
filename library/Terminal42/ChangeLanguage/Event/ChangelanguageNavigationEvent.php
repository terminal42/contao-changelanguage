<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2017, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Event;

use Terminal42\ChangeLanguage\Navigation\NavigationItem;
use Terminal42\ChangeLanguage\Navigation\UrlParameterBag;

class ChangelanguageNavigationEvent
{
    /**
     * @var NavigationItem
     */
    private $navigationItem;

    /**
     * @var UrlParameterBag
     */
    private $urlParameterBag;

    /**
     * @var bool
     */
    private $skipped = false;

    /**
     * @var bool
     */
    private $stopPropagation = false;

    /**
     * Constructor.
     *
     * @param NavigationItem  $navigationItem
     * @param UrlParameterBag $urlParameters
     */
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

    public function skipInNavigation()
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

    public function stopPropagation()
    {
        $this->stopPropagation = true;
    }
}

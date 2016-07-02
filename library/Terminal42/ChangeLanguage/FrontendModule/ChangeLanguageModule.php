<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\FrontendModule;

use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\System;
use Haste\Frontend\AbstractFrontendModule;
use Haste\Generator\RowClass;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;
use Terminal42\ChangeLanguage\Helper\AlternateLinks;
use Terminal42\ChangeLanguage\Helper\LanguageText;
use Terminal42\ChangeLanguage\Helper\UrlParameterBag;
use Terminal42\ChangeLanguage\Navigation\NavigationFactory;
use Terminal42\ChangeLanguage\Navigation\NavigationItem;
use Terminal42\ChangeLanguage\Navigation\PageFinder;

/**
 * @property bool  $hideActiveLanguage
 * @property bool  $hideNoFallback
 * @property bool  $keepUrlParams
 * @property bool  $customLanguage
 * @property array $customLanguageText
 */
class ChangeLanguageModule extends AbstractFrontendModule
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_changelanguage';

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            return $this->generateWildcard();
        }

        $buffer = parent::generate();

        return '' === $this->Template->items ? '' : $buffer;
    }

    /**
     * @inheritdoc
     */
    protected function compile()
    {
        $currentPage  = $this->getCurrentPage();
        $pageFinder   = new PageFinder();

        if ($this->customLanguage) {
            $languageText = LanguageText::createFromOptionWizard($this->customLanguageText);
        } else {
            $languageText = new LanguageText();
        }

        $navigationFactory = new NavigationFactory($pageFinder, $languageText);

        $items = $navigationFactory->findNavigationItems(
            $currentPage,
            $this->hideActiveLanguage,
            $this->hideNoFallback
        );

        $this->Template->items = $this->generateNavigationTemplate($items);
        $GLOBALS['TL_HEAD'][]  = $this->generateHeaderLinks($items);
    }

    /**
     * @param NavigationItem[] $navigationItems
     *
     * @return string|void
     */
    protected function generateNavigationTemplate(array $navigationItems)
    {
        if (0 === count($navigationItems)) {
            return '';
        }

        $items = [];
        $defaultUrlParameters = UrlParameterBag::createFromGlobals();

        foreach ($navigationItems as $item) {
            $urlParameters = clone $defaultUrlParameters;

            if (false === $this->executeHook($item, $urlParameters)) {
                continue;
            }

            $items[] = $item->getTemplateArray($urlParameters);
        }

        RowClass::withKey('class')->addFirstLast()->applyTo($items);

        /** @var FrontendTemplate|object $objTemplate */
        $objTemplate = new FrontendTemplate($this->navigationTpl ?: 'nav_default');

        $objTemplate->setData($this->arrData);
        $objTemplate->level = 'level_1';
        $objTemplate->items = $items;

        return $objTemplate->parse();
    }

    /**
     * @param NavigationItem[] $items
     *
     * @return string
     */
    protected function generateHeaderLinks(array $items)
    {
        $links = new AlternateLinks();

        foreach ($items as $item) {
            $links->addFromNavigationItem($item);
        }

        return $links->generate();
    }

    /**
     * @return PageModel
     */
    protected function getCurrentPage()
    {
        global $objPage;

        return $objPage;
    }

    /**
     * Returns false if navigation item should be skipped
     *
     * @param NavigationItem  $navigationItem
     * @param UrlParameterBag $urlParameterBag
     *
     * @return bool
     */
    protected function executeHook(NavigationItem $navigationItem, UrlParameterBag $urlParameterBag)
    {
        // HOOK: allow extensions to modify url parameters
        if (isset($GLOBALS['TL_HOOKS']['changelanguageNavigation'])
            && is_array($GLOBALS['TL_HOOKS']['changelanguageNavigation'])
        ) {
            $event = new ChangelanguageNavigationEvent($navigationItem, $urlParameterBag);

            foreach ($GLOBALS['TL_HOOKS']['changelanguageNavigation'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($event);

                if ($event->isPropagationStopped()) {
                    break;
                }
            }

            return !$event->isSkipped();
        }

        return true;
    }
}

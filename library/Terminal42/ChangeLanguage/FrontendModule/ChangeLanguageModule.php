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
use Haste\Frontend\AbstractFrontendModule;
use Haste\Generator\RowClass;
use Terminal42\ChangeLanguage\Helper\AlternateLinks;
use Terminal42\ChangeLanguage\Helper\LanguageText;
use Terminal42\ChangeLanguage\NavigationItem;
use Terminal42\ChangeLanguage\PageFinder;

/**
 * Class ChangeLanguageModule
 *
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
     * @var LanguageText
     */
    private $languageText;

    /**
     * @var AlternateLinks
     */
    private $alternateLinks;

    /**
     * @var PageFinder
     */
    private $pageFinder;

    /**
     * @inheritdoc
     */
    public function __construct(\ModuleModel $objModule, $strColumn)
    {
        parent::__construct($objModule, $strColumn);

        $this->alternateLinks = new AlternateLinks();
        $this->pageFinder     = new PageFinder();
        $this->languageText = LanguageText::createFromOptionWizard($this->customLanguageText);

        if ('' === $this->navigationTpl) {
            $this->navigationTpl = 'nav_default';
        }
    }

    /**
     * @inheritdoc
     */
    public function outputIsEmpty()
    {
        return $this->Template->items == '';
    }

    /**
     * Generate module
     */
    protected function compile()
    {
        /** @var PageModel $objPage */
        global $objPage;

        /** @var NavigationItem[] $navigationItems */
        $navigationItems = [];
        $rootPages = $this->pageFinder->findRootPagesForPage($objPage);

        foreach ($rootPages as $rootPage) {
            $language = strtolower($rootPage->language);

            if (array_key_exists($language, $navigationItems)) {
                throw new \OverflowException(
                    sprintf('Multiple root pages for the language "%s" found', $rootPage->language)
                );
            }

            $navigationItems[$language] = new NavigationItem($rootPage, $this->languageText->get($language));
        }

        foreach ($this->pageFinder->findAssociatedForPage($objPage) as $page) {
            $page->loadDetails();

            if (array_key_exists($page->rootId, $rootPages)) {
                $navigationItems[strtolower($page->language)]->setTargetPage($page, true);
            }
        }

        if ($this->customLanguage) {
            $this->languageText->orderNavigationItems($navigationItems);
        }

        $templateItems = [];

        foreach ($navigationItems as $item) {
            if ($this->hideActiveLanguage && $item->isIsCurrentPage()) {
                continue;
            }

            if ($this->hideNoFallback && !$item->isIsDirectFallback()) {
                continue;
            }

            if (!$item->hasTargetPage()) {
                $item->setTargetPage(
                    $this->pageFinder->findAssociatedParentForLanguage($objPage, $item->getLanguageTag()),
                    false
                );
            }

            $templateItems[] = $item->getTemplateArray();
            $this->alternateLinks->addFromNavigationItem($item);
        }

        if (0 === count($templateItems)) {
            return;
        }

        RowClass::withKey('class')->addFirstLast()->applyTo($templateItems);

        /** @var FrontendTemplate|object $objTemplate */
        $objTemplate = new FrontendTemplate($this->navigationTpl);
        $objTemplate->setData($this->arrData);
        $objTemplate->level = 'level_1';
        $objTemplate->items = $templateItems;

        $this->Template->items = $objTemplate->parse();

        $GLOBALS['TL_HEAD'][] = $this->alternateLinks->generate();
    }
}

<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\FrontendModule;

use Contao\FrontendTemplate;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Haste\Frontend\AbstractFrontendModule;
use Haste\Generator\RowClass;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;
use Terminal42\ChangeLanguage\Helper\AlternateLinks;
use Terminal42\ChangeLanguage\Helper\LanguageText;
use Terminal42\ChangeLanguage\Navigation\NavigationFactory;
use Terminal42\ChangeLanguage\Navigation\NavigationItem;
use Terminal42\ChangeLanguage\Navigation\UrlParameterBag;
use Terminal42\ChangeLanguage\PageFinder;

/**
 * @property bool  $hideActiveLanguage
 * @property bool  $hideNoFallback
 * @property bool  $customLanguage
 * @property array $customLanguageText
 */
class ChangeLanguageModule extends AbstractFrontendModule
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_changelanguage';

    /**
     * @var AlternateLinks
     */
    private static $alternateLinks;

    /**
     * @return AlternateLinks
     */
    public function getAlternateLinks()
    {
        if (null === self::$alternateLinks) {
            self::$alternateLinks = new AlternateLinks();
        }

        return self::$alternateLinks;
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            return $this->generateWildcard();
        }

        $buffer = parent::generate();

        return '' === (string) $this->Template->items ? '' : $buffer;
    }

    /**
     * {@inheritdoc}
     */
    protected function compile(): void
    {
        $currentPage = $this->getCurrentPage();
        $pageFinder = new PageFinder();

        if ($this->customLanguage) {
            $languageText = LanguageText::createFromOptionWizard($this->customLanguageText);
        } else {
            $languageText = new LanguageText();
        }

        $navigationFactory = new NavigationFactory($pageFinder, $languageText, $currentPage);
        $navigationItems = $navigationFactory->findNavigationItems($currentPage);

        // Do not generate module or header if there is none or only one link
        if (\count($navigationItems) < 2) {
            return;
        }

        $templateItems = [];
        $headerLinks = $this->getAlternateLinks();
        $queryParameters = $currentPage->languageQuery ? trimsplit(',', $currentPage->languageQuery) : [];
        $defaultUrlParameters = $this->createUrlParameterBag($queryParameters);

        foreach ($navigationItems as $item) {
            $urlParameters = clone $defaultUrlParameters;

            if (
                false === $this->executeHook($item, $urlParameters)
                || ($this->hideNoFallback && !$item->isDirectFallback())
            ) {
                continue;
            }

            if ($item->isDirectFallback() && !$headerLinks->has($item->getLanguageTag())) {
                $headerLinks->addFromNavigationItem($item, $urlParameters);
            }

            // Remove active language from navigation but not from header links!
            if ($this->hideActiveLanguage && $item->isCurrentPage()) {
                continue;
            }

            $templateItems[] = $this->generateTemplateArray($item, $urlParameters);
        }

        $this->Template->items = $this->generateNavigationTemplate($templateItems);
        $GLOBALS['TL_HEAD']['changelanguage_headers'] = $headerLinks->generate();
    }

    /**
     * Generates array suitable for nav_default template.
     *
     * @return array
     */
    protected function generateTemplateArray(NavigationItem $item, UrlParameterBag $urlParameterBag)
    {
        return [
            'isActive' => $item->isCurrentPage(),
            'class' => 'lang-'.$item->getNormalizedLanguage().($item->isDirectFallback() ? '' : ' nofallback').($item->isCurrentPage() ? ' active' : ''),
            'link' => $item->getLabel(),
            'subitems' => '',
            'href' => specialchars($item->getHref($urlParameterBag)),
            'title' => specialchars(strip_tags($item->getTitle())),
            'pageTitle' => specialchars(strip_tags($item->getPageTitle())),
            'accesskey' => '',
            'tabindex' => '',
            'nofollow' => false,
            'target' => ($item->isNewWindow() ? ' target="_blank"' : '').' hreflang="'.$item->getLanguageTag().'" lang="'.$item->getLanguageTag().'"',
            'item' => $item,
        ];
    }

    /**
     * @return string
     */
    protected function generateNavigationTemplate(array $items)
    {
        RowClass::withKey('class')->addFirstLast()->applyTo($items);

        $objTemplate = new FrontendTemplate($this->navigationTpl ?: 'nav_default');

        $objTemplate->setData($this->arrData);
        $objTemplate->level = 'level_1';
        $objTemplate->items = $items;

        return $objTemplate->parse();
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
     * Creates an UrlParameterBag from the current environment.
     *
     * @param array $queryParameters An array of query parameters to keep
     *
     * @return UrlParameterBag
     */
    protected function createUrlParameterBag(array $queryParameters = [])
    {
        $attributes = [];
        $query = [];
        $input = $_GET;

        // the current page language is set in $_GET
        unset($input['language'], $input['auto_item']);

        parse_str($_SERVER['QUERY_STRING'], $currentQuery);

        foreach ($input as $k => $value) {
            // GET parameters can be an array
            $value = Input::get($k, false, true);

            if (empty($value)) {
                continue;
            }

            if (!\array_key_exists($k, $currentQuery)) {
                $attributes[$k] = (string) $value;
            } elseif (\in_array($k, $queryParameters, false)) {
                $query[$k] = $value;
            }
        }

        return new UrlParameterBag($attributes, $query);
    }

    /**
     * Returns false if navigation item should be skipped.
     *
     * @return bool
     */
    protected function executeHook(NavigationItem $navigationItem, UrlParameterBag $urlParameterBag)
    {
        // HOOK: allow extensions to modify url parameters
        if (
            isset($GLOBALS['TL_HOOKS']['changelanguageNavigation'])
            && \is_array($GLOBALS['TL_HOOKS']['changelanguageNavigation'])
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

<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\FrontendModule;

use Contao\BackendTemplate;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Module;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Routing\Exception\ExceptionInterface;
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
class ChangeLanguageModule extends Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_changelanguage';

    private static ?AlternateLinks $alternateLinks = null;

    public function getAlternateLinks(): AlternateLinks
    {
        if (null === self::$alternateLinks) {
            self::$alternateLinks = new AlternateLinks();
        }

        return self::$alternateLinks;
    }

    public function generate(): string
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');

        if (null !== $request && $scopeMatcher->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');

            $template->wildcard = '### '.strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = System::getContainer()->get('router')->generate(
                'contao_backend',
                ['do' => 'themes', 'table' => 'tl_module', 'act' => 'edit', 'id' => $this->id]
            );

            return $template->parse();
        }

        $buffer = parent::generate();

        return '' === (string) $this->Template->items ? '' : $buffer;
    }

    protected function compile(): void
    {
        $currentPage = $this->getCurrentPage();
        $pageFinder = new PageFinder();

        if ($this->customLanguage) {
            $languageText = LanguageText::createFromOptionWizard($this->customLanguageText);
        } else {
            $languageText = new LanguageText();
        }

        $navigationFactory = new NavigationFactory($pageFinder, $languageText, $currentPage, System::getContainer()->get('contao.intl.locales')->getLocales());
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
                try {
                    $headerLinks->addFromNavigationItem($item, $urlParameters);

                    if ($item->getRootPage()->fallback && !$item->getRootPage()->languageRoot) {
                        $headerLinks->setDefault($item->getHref($urlParameters), $item->getTitle());
                    }
                } catch (ExceptionInterface $e) {
                    // Ignore unroutable pages
                }
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
     */
    protected function generateTemplateArray(NavigationItem $item, UrlParameterBag $urlParameterBag): array
    {
        return [
            'isActive' => $item->isCurrentPage(),
            'class' => 'lang-'.$item->getNormalizedLanguage().($item->isDirectFallback() ? '' : ' nofallback').($item->isCurrentPage() ? ' active' : ''),
            'link' => $item->getLabel(),
            'subitems' => '',
            'href' => StringUtil::specialchars($item->getHref($urlParameterBag, true)),
            'title' => StringUtil::specialchars(strip_tags($item->getTitle())),
            'pageTitle' => StringUtil::specialchars(strip_tags($item->getPageTitle())),
            'accesskey' => '',
            'tabindex' => '',
            'nofollow' => false,
            'rel' => ' hreflang="'.$item->getLanguageTag().'"'.(empty($item->getAriaLabel()) ? '' : ' aria-label="'.$item->getAriaLabel().'"'),
            'target' => ($item->isNewWindow() ? ' target="_blank"' : ''),
            'item' => $item,
            'languageTag' => $item->getLanguageTag(),
        ];
    }

    protected function generateNavigationTemplate(array $items): string
    {
        $objTemplate = new FrontendTemplate($this->navigationTpl ?: 'nav_default');

        $objTemplate->setData($this->arrData);
        $objTemplate->level = 'level_1';
        $objTemplate->items = $items;

        return $objTemplate->parse();
    }

    protected function getCurrentPage(): PageModel
    {
        global $objPage;

        return $objPage;
    }

    /**
     * Creates an UrlParameterBag from the current environment.
     *
     * @param array $queryParameters An array of query parameters to keep
     */
    protected function createUrlParameterBag(array $queryParameters = []): UrlParameterBag
    {
        $attributes = [];
        $query = [];
        $input = $_GET;

        // the current page language is set in $_GET
        unset($input['language'], $input['auto_item']);

        $currentQuery = [];

        if (!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $currentQuery);
        }

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
     */
    protected function executeHook(NavigationItem $navigationItem, UrlParameterBag $urlParameterBag): bool
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

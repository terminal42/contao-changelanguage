<?php
/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Navigation;

use Contao\PageModel;
use Terminal42\ChangeLanguage\Language;

class NavigationItem
{
    /**
     * @var PageModel
     */
    private $rootPage;

    /**
     * @var PageModel
     */
    private $targetPage;

    /**
     * @var bool
     */
    private $isDirectFallback = false;

    /**
     * @var bool
     */
    private $isCurrentPage = false;

    /**
     * Constructor.
     *
     * @param PageModel   $rootPage
     * @param string|null $label
     */
    public function __construct(PageModel $rootPage, $label = null)
    {
        if ('root' !== $rootPage->type) {
            throw new \RuntimeException(
                sprintf('Page ID "%s" has type "%s" but should be "root"', $rootPage->id, $rootPage->type)
            );
        }

        $this->rootPage  = $rootPage;
        $this->linkLabel = $label;

        if (null === $label) {
            $this->linkLabel = strtoupper($this->getLanguageTag());
        }
    }

    public function hasTargetPage()
    {
        return $this->targetPage instanceof PageModel;
    }

    /**
     * @return bool
     */
    public function isIsDirectFallback()
    {
        return $this->isDirectFallback;
    }

    /**
     * @return bool
     */
    public function isIsCurrentPage()
    {
        return $this->isCurrentPage;
    }

    /**
     * @param PageModel $targetPage
     * @param bool      $isDirectFallback
     * @param bool|null $isCurrentPage
     */
    public function setTargetPage(PageModel $targetPage, $isDirectFallback, $isCurrentPage = null)
    {
        $this->targetPage = $targetPage;
        $this->isDirectFallback = $isDirectFallback;
        $this->isCurrentPage = (bool) $isCurrentPage;

        if (null === $isCurrentPage) {
            global $objPage;

            if ($objPage instanceof PageModel && $objPage->id === $targetPage->id) {
                $this->isCurrentPage = true;
            }
        }
    }

    /**
     * @return string
     */
    public function getNormalizedLanguage()
    {
        return strtolower($this->getLocaleId());
    }

    /**
     * Returns the language formatted as IETF Language Tag (BCP 47)
     * Example: en, en-US, de-CH
     *
     * @return string
     *
     * @see http://www.w3.org/International/articles/language-tags/
     */
    public function getLanguageTag()
    {
        return Language::toLanguageTag($this->rootPage->language);
    }

    /**
     * Returns the language formatted as ICU Locale ID
     * Example: en, en_US, de_CH
     *
     * @return string
     *
     * @see http://userguide.icu-project.org/locale
     */
    public function getLocaleId()
    {
        return Language::toLocaleID($this->rootPage->language);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->linkLabel;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        $targetPage = $this->targetPage ?: $this->rootPage;

        return $targetPage->getFrontendUrl();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->targetPage ? $this->targetPage->title : $this->rootPage->title;
    }

    /**
     * Generates array suitable for nav_default template.
     *
     * @return array
     */
    public function getTemplateArray()
    {
        $targetPage = $this->targetPage ?: $this->rootPage;
        $newWindow  = 'redirect' === $targetPage->type && $targetPage->target;

        return [
            'isActive'  => $this->isCurrentPage,
            'class'     => 'lang-' . $this->getNormalizedLanguage() . ($this->isDirectFallback ? '' : ' nofallback') . ($this->isCurrentPage ? ' active' : ''),
            'link'      => $this->getLabel(),
            'subitems'  => '',
            'href'      => specialchars($this->getHref()),
            'pageTitle' => strip_tags($this->getTitle()),
            'accesskey' => '',
            'tabindex'  => '',
            'nofollow'  => false,
            'target'    => ($newWindow ? ' target="_blank"' : '') . ' hreflang="' . $this->getLanguageTag() . '" lang="' . $this->getLanguageTag() . '"',
            'item'      => $this,
        ];
    }
}

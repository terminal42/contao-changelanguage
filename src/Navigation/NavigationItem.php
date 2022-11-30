<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Navigation;

use Contao\PageModel;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Terminal42\ChangeLanguage\Language;

class NavigationItem
{
    private PageModel $rootPage;
    private ?PageModel $targetPage = null;
    private string $linkLabel;
    private ?bool $newWindow = null;
    private bool $isDirectFallback = false;
    private bool $isCurrentPage = false;

    public function __construct(PageModel $rootPage, string $label = null)
    {
        if ('root' !== $rootPage->type) {
            throw new \RuntimeException(sprintf('Page ID "%s" has type "%s" but should be "root"', $rootPage->id, $rootPage->type));
        }

        $this->rootPage = $rootPage->loadDetails();
        $this->linkLabel = $label;

        if (null === $label) {
            $this->linkLabel = strtoupper($this->getLanguageTag());
        }
    }

    public function hasTargetPage(): bool
    {
        return $this->targetPage instanceof PageModel;
    }

    public function isDirectFallback(): bool
    {
        return $this->isDirectFallback;
    }

    public function setIsDirectFallback($isDirectFallback): void
    {
        $this->isDirectFallback = (bool) $isDirectFallback;
    }

    public function isCurrentPage(): bool
    {
        return $this->isCurrentPage;
    }

    public function getRootPage(): PageModel
    {
        return $this->rootPage;
    }

    public function getLabel(): string
    {
        return $this->linkLabel;
    }

    public function setLabel(string $label): void
    {
        $this->linkLabel = $label;
    }

    public function getTargetPage(): ?PageModel
    {
        return $this->targetPage;
    }

    public function setTargetPage(PageModel $targetPage, bool $isDirectFallback, bool $isCurrentPage = false): void
    {
        $this->targetPage = $targetPage->loadDetails();
        $this->isDirectFallback = $isDirectFallback;
        $this->isCurrentPage = $isCurrentPage;
    }

    public function setIsCurrentPage(bool $isCurrentPage): void
    {
        $this->isCurrentPage = $isCurrentPage;
    }

    public function isNewWindow(): ?bool
    {
        if (null === $this->newWindow) {
            $targetPage = $this->targetPage ?: $this->rootPage;

            return 'redirect' === $targetPage->type && $targetPage->target;
        }

        return $this->newWindow;
    }

    public function setNewWindow(?bool $newWindow): void
    {
        $this->newWindow = $newWindow;
    }

    public function getNormalizedLanguage(): string
    {
        return strtolower($this->getLocaleId());
    }

    /**
     * Returns the language formatted as IETF Language Tag (BCP 47)
     * Example: en, en-US, de-CH.
     *
     * @see http://www.w3.org/International/articles/language-tags/
     */
    public function getLanguageTag(): string
    {
        return Language::toLanguageTag($this->rootPage->language);
    }

    /**
     * Returns the language formatted as ICU Locale ID
     * Example: en, en_US, de_CH.
     *
     * @see http://userguide.icu-project.org/locale
     */
    public function getLocaleId(): string
    {
        return Language::toLocaleID($this->rootPage->language);
    }

    /**
     * @throws ExceptionInterface
     */
    public function getHref(UrlParameterBag $urlParameterBag, bool $catch = false): string
    {
        $targetPage = $this->targetPage ?: $this->rootPage;

        if ('root' === $targetPage->type) {
            $targetPage = PageModel::findFirstPublishedRegularByPid($targetPage->id) ?: $targetPage;
        }

        try {
            $href = $targetPage->getAbsoluteUrl($urlParameterBag->generateParameters());
        } catch (ExceptionInterface $e) {
            if (!$catch) {
                throw $e;
            }

            $this->targetPage = null;
            $this->isDirectFallback = false;
            $this->isCurrentPage = false;

            return $this->getHref($urlParameterBag);
        }

        if (null !== ($queryString = $urlParameterBag->generateQueryString())) {
            $href .= '?'.$queryString;
        }

        return $href;
    }

    public function getTitle(): string
    {
        return $this->targetPage->title ?? $this->rootPage->title;
    }

    public function getPageTitle(): string
    {
        return $this->targetPage->pageTitle ?? $this->rootPage->pageTitle;
    }
}

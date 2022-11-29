<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Helper;

use Contao\Environment;
use Contao\FrontendTemplate;
use Terminal42\ChangeLanguage\Language;
use Terminal42\ChangeLanguage\Navigation\NavigationItem;
use Terminal42\ChangeLanguage\Navigation\UrlParameterBag;

/**
 * AlternateLinks is a helper class to handle <link rel="alternate"> in the page header.
 */
class AlternateLinks
{
    private array $links = [];

    /**
     * Returns whether a link already exists for a language.
     */
    public function has(string $language): bool
    {
        return \array_key_exists(Language::toLanguageTag($language), $this->links);
    }

    /**
     * Adds or replaces a link for a language.
     */
    public function add(string $language, string $href, string $title = ''): void
    {
        $language = Language::toLanguageTag($language);

        $this->store($language, $href, $title);
    }

    /**
     * Adds a link from a NavigationItem instance.
     */
    public function addFromNavigationItem(NavigationItem $item, UrlParameterBag $urlParameterBag): void
    {
        $this->add($item->getLanguageTag(), $item->getHref($urlParameterBag), $item->getTitle());
    }

    /**
     * Removes link for a language if it exists.
     */
    public function remove(string $language): void
    {
        unset($this->links[Language::toLanguageTag($language)]);
    }

    /**
     * Sets link for the x-default language.
     */
    public function setDefault(string $href, string $title = ''): void
    {
        $this->store('x-default', $href, $title);
    }

    /**
     * Generates template markup of links for the page header.
     */
    public function generate(string $templateName = 'block_alternate_links'): string
    {
        if (0 === \count($this->links)) {
            return '';
        }

        $template = new FrontendTemplate($templateName);

        $template->links = $this->links;

        return $template->parse();
    }

    private function store(string $language, string $href, string $title): void
    {
        // URLs must always be absolute
        if (0 !== strpos($href, 'http://') && 0 !== strpos($href, 'https://')) {
            $href = Environment::get('base').$href;
        }

        $this->links[$language] = ['language' => $language, 'href' => $href, 'title' => $title];
    }
}

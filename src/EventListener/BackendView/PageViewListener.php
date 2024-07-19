<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use League\Uri\Uri;
use League\Uri\UriModifier;

class PageViewListener extends AbstractViewListener
{
    protected function isSupported(): bool
    {
        return 'page' === Input::get('do') || ('article' === Input::get('do') && 'edit' !== Input::get('act'));
    }

    protected function getCurrentPage(): ?PageModel
    {
        $node = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend')->get('tl_page_node');

        if ($node < 1) {
            return null;
        }

        return PageModel::findById($node);
    }

    /**
     * @return array<int|string, string>
     */
    protected function getAvailableLanguages(PageModel $page): array
    {
        $options = [];

        foreach ($this->pageFinder->findAssociatedForPage($page, true, null, false) as $model) {
            $model->loadDetails();
            $options[$model->id] = $this->getLanguageLabel($model->language);
        }

        return $options;
    }

    protected function doSwitchView($id): void
    {
        $requestStack = System::getContainer()->get('request_stack');
        $requestStack->getSession()->getBag('contao_backend')->set('tl_page_node', (int) $id);

        $uri = Uri::createFromString($requestStack->getCurrentRequest()->getUri());
        $uri = UriModifier::removePairs($uri, 'switchLanguage');

        throw new RedirectResponseException((string) $uri);
    }
}

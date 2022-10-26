<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Codefog\HasteBundle\UrlParser;
use Contao\Controller;
use Contao\Input;
use Contao\PageModel;
use Contao\System;

class PageViewListener extends AbstractViewListener
{
    private UrlParser $urlParser;

    /**
     * @param UrlParser $urlParser
     */
    public function setUrlParser(UrlParser $urlParser): void
    {
        $this->urlParser = $urlParser;
    }

    /**
     * {@inheritdoc}
     */
    protected function isSupported()
    {
        return 'page' === Input::get('do') || ('article' === Input::get('do') && 'edit' !== Input::get('act'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCurrentPage()
    {
        $node = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend')->get('tl_page_node');

        if ($node < 1) {
            return null;
        }

        return PageModel::findByPk($node);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableLanguages(PageModel $page)
    {
        $options = [];

        foreach ($this->pageFinder->findAssociatedForPage($page, true) as $model) {
            $model->loadDetails();
            $options[$model->id] = $this->getLanguageLabel($model->language);
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSwitchView($id): void
    {
        System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend')->set('tl_page_node', (int) $id);

        Controller::redirect($this->urlParser->removeQueryString(['switchLanguage']));
    }
}

<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Contao\Controller;
use Contao\Input;
use Contao\PageModel;
use Contao\Session;
use Haste\Util\Url;

class PageViewListener extends AbstractViewListener
{
    /**
     * {@inheritdoc}
     */
    protected function isSupported()
    {
        return 'page' === Input::get('do') || 'article' === Input::get('do');
    }

    /**
     * {@inheritdoc}
     */
    protected function getCurrentPage()
    {
        $node = Session::getInstance()->get('tl_page_node');

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
        Session::getInstance()->set('tl_page_node', (int) $id);

        Controller::redirect(Url::removeQueryString(['switchLanguage']));
    }
}

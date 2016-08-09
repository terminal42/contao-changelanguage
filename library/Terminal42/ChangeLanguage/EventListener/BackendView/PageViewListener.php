<?php
/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Contao\Controller;
use Contao\DataContainer;
use Contao\PageModel;
use Contao\Session;
use Contao\System;

class PageViewListener extends AbstractViewListener
{
    /**
     * @inheritdoc
     */
    protected function getAvailableLanguages(DataContainer $dc)
    {
        $options = [];
        $current = $this->getCurrentPage();

        if (null !== $current) {
            foreach ($this->pageFinder->findAssociatedForPage($current, true) as $model) {
                $model->loadDetails();
                $options[$model->id] = $this->getLanguageLabel($model->language);
            }
        }

        return $options;
    }

    /**
     * @inheritdoc
     */
    protected function doSwitchView($id)
    {
        Session::getInstance()->set('tl_page_node', (int) $id);

        Controller::redirect(System::getReferer());
    }

    /**
     * Returns the page for current node filter.
     *
     * @return \PageModel|null
     */
    private function getCurrentPage()
    {
        $node = Session::getInstance()->get('tl_page_node');

        if ($node < 1) {
            return null;
        }

        return PageModel::findByPk($node);
    }
}

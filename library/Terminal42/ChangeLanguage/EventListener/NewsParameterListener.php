<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener;

use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;
use Haste\Input\Input;

/**
 * Translate URL parameters for news items
 */
class NewsParameterListener extends AbstractParameterListener
{
    /**
     * @inheritdoc
     */
    protected function getUrlKey()
    {
        return 'items';
    }

    /**
     * @inheritdoc
     */
    protected function findCurrent()
    {
        $alias = (string) Input::getAutoItem($this->getUrlKey(), false, true);

        if ('' === $alias) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if (($archives = NewsArchiveModel::findBy('jumpTo', $objPage->id)) === null) {
            return null;
        }

        return NewsModel::findPublishedByParentAndIdOrAlias($alias, $archives->fetchEach('id'));
    }

    /**
     * @inheritdoc
     */
    protected function findPublishedBy(array $columns, array $values = array(), array $options = array())
    {
        return NewsModel::findOneBy(
            $this->addPublishedConditions($columns, NewsModel::getTable()),
            $values,
            $options
        );
    }
}

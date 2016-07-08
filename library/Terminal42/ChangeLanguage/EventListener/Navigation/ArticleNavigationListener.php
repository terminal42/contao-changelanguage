<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\ArticleModel;
use Contao\Database;
use Contao\PageModel;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

class ArticleNavigationListener
{
    /**
     * Translate URL parameters for articles.
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event)
    {
        // Try to find matching article
        if ($event->getNavigationItem()->isCurrentPage() || !$event->getUrlParameterBag()->hasUrlAttribute('articles')) {
            return;
        }

        /** @var PageModel $objPage */
        global $objPage;

        $targetRoot     = $event->getNavigationItem()->getRootPage();
        $currentAlias   = $event->getUrlParameterBag()->getUrlAttribute('articles');
        $currentArticle = ArticleModel::findByIdOrAliasAndPid($currentAlias, $objPage->id);
        $targetArticle  = null;
        $t              = ArticleModel::getTable();

        if (null === $currentArticle
            || ($currentArticle->languageMain < 1
                && ($targetRoot->fallback || !$objPage->rootIsFallback)
            )
        ) {
            return;
        }

        if ($targetRoot->fallback) {
            // If the target root is fallback, the article ID will match our current "languageMain"
            $targetArticle = $this->findPublishedArticle(array("$t.id = " . $currentArticle->languageMain));

        } else {
            $arrSubpages = Database::getInstance()->getChildRecords($targetRoot->id, 'tl_page', true);

            if (0 === count($arrSubpages)) {
                return;
            }

            $targetArticle = $this->findPublishedArticle(
                array(
                    "$t.languageMain = ?",
                    "$t.pid IN (" . implode(',', $arrSubpages) . ')'
                ),
                array(
                    $objPage->rootIsFallback ? $currentArticle->id : $currentArticle->languageMain
                )
            );
        }

        if (null === $targetArticle) {
            return;
        }

        $event->getUrlParameterBag()->setUrlAttribute('articles', $targetArticle->alias);
    }

    /**
     * Find a published article with additional conditions.
     *
     * @param array $columns
     * @param array $values
     * @param array $options
     *
     * @return \ArticleModel|null
     */
    private function findPublishedArticle(array $columns, array $values = array(), array $options = array())
    {
        $t = ArticleModel::getTable();

        if (true !== BE_USER_LOGGED_IN) {
            $time      = \Date::floorToMinute();
            $columns[] = "($t.start='' OR $t.start<='$time')";
            $columns[] = "($t.stop='' OR $t.stop>'" . ($time + 60) . "')";
            $columns[] = "$t.published='1'";
        }

        return ArticleModel::findOneBy($columns, $values, $options);
    }
}

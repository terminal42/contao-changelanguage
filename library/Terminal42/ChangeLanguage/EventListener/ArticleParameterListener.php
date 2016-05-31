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

use Contao\ArticleModel;
use Contao\Database;
use Contao\PageModel;

class ArticleParameterListener
{
    /**
     * Translate URL parameters for articles.
     *
     * @param array  $parameters
     * @param string $language
     * @param array  $rootPage
     *
     * @return array
     */
    public function onTranslateUrlParameters(array $parameters, $language, array $rootPage)
    {
        // Try to find matching article
        if (!isset($parameters['url']['articles'])) {
            return $parameters;
        }

        /** @var PageModel $objPage */
        global $objPage;

        $currentArticle = ArticleModel::findByIdOrAliasAndPid($parameters['url']['articles'], $objPage->id);
        $targetArticle  = null;
        $t              = ArticleModel::getTable();

        if (null === $currentArticle
            || ($currentArticle->languageMain < 1 && ($rootPage['fallback'] || !$objPage->rootIsFallback))
        ) {
            return $parameters;
        }

        if ($rootPage['fallback']) {
            // If the target root is fallback, the article ID will match our current "languageMain"
            $targetArticle = $this->findPublishedArticle(array("$t.id = " . $currentArticle->languageMain));

        } else {
            $arrSubpages = Database::getInstance()->getChildRecords($rootPage['id'], 'tl_page', true);

            if (0 === count($arrSubpages)) {
                return $parameters;
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

        if (null !== $targetArticle) {
            $parameters['url']['articles'] = $targetArticle->alias;
        }

        return $parameters;
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

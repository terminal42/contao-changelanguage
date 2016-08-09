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

use Contao\ArticleModel;
use Contao\Controller;
use Contao\DataContainer;
use Contao\PageModel;
use Contao\Session;
use Haste\Util\Url;

class ArticleViewListener extends AbstractViewListener
{
    /**
     * @inheritdoc
     */
    protected function getAvailableLanguages(DataContainer $dc)
    {
        $currentArticle = ArticleModel::findByPk($dc->id);
        $currentPage    = PageModel::findWithDetails($currentArticle->pid);

        if (null === $currentArticle || null === $currentPage) {
            return [];
        }

        $options    = [];
        $masterRoot = $this->pageFinder->findMasterRootForPage($currentPage);
        $articleId  = $currentPage->rootId === $masterRoot->id ? $currentArticle->id : $currentArticle->languageMain;

        foreach ($this->pageFinder->findAssociatedForPage($currentPage, true) as $page) {
            $page->loadDetails();

            $articles = $this->findArticlesForPage($page, $articleId, $currentArticle);

            if (1 === count($articles)) {
                $options['tl_article.'.$articles[0]->id] = $this->getLanguageLabel($page->language);
            } else {
                $options['tl_page.'.$page->id] = $this->getLanguageLabel($page->language);
            }
        }

        return $options;
    }

    /**
     * @inheritdoc
     */
    protected function doSwitchView($id)
    {
        list ($table, $id) = explode('.', $id);

        switch ($table) {
            case 'tl_article':
                $url = Url::removeQueryString(['switchLanguage']);
                $url = Url::addQueryString('id='.$id, $url);
                break;

            case 'tl_page':
                Session::getInstance()->set('tl_page_node', (int) $id);

                $url = TL_SCRIPT.'?do=article&amp;rt=' . REQUEST_TOKEN;
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Table "%s" is not supported', $table));
        }

        Controller::redirect($url);
    }

    /**
     * @param PageModel    $page
     * @param              $articleId
     * @param ArticleModel $article
     *
     * @return ArticleModel[]
     */
    private function findArticlesForPage(PageModel $page, $articleId, ArticleModel $article)
    {
        $articles = ArticleModel::findBy(
            [
                'tl_article.pid=?',
                'tl_article.id!=?',
                '(tl_article.id=? OR tl_article.languageMain=? OR tl_article.inColumn=?)',
            ],
            [$page->id, $article->id, $articleId, $articleId, $article->inColumn, $articleId, $articleId],
            ['order' => 'tl_article.id=? DESC, tl_article.languageMain=? DESC']
        );

        if (null === $articles) {
            return [];
        }

        $articles = $articles->getModels();

        if ($articleId > 0 && ($articles[0]->id == $articleId || $articles[0]->languageMain == $articleId)) {
            return [$articles[0]];
        }

        return $articles;
    }
}

<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Contao\ArticleModel;
use Contao\Controller;
use Contao\Input;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\Session;
use Haste\Util\Url;

class ArticleViewListener extends AbstractViewListener
{
    /**
     * @var ArticleModel
     */
    private $currentArticle = false;

    /**
     * {@inheritdoc}
     */
    protected function isSupported()
    {
        return 'article' === (string) Input::get('do');
    }

    /**
     * {@inheritdoc}
     */
    protected function getCurrentPage()
    {
        if (false === $this->currentArticle) {
            $this->currentArticle = ArticleModel::findByPk($this->dataContainer->id);
        }

        if (null === $this->currentArticle) {
            return null;
        }

        return PageModel::findWithDetails($this->currentArticle->pid);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableLanguages(PageModel $page)
    {
        $options = [];
        $masterRoot = $this->pageFinder->findMasterRootForPage($page);
        $articleId = $page->rootId === $masterRoot->id ? $this->currentArticle->id : $this->currentArticle->languageMain;

        foreach ($this->pageFinder->findAssociatedForPage($page, true) as $model) {
            $model->loadDetails();

            $articles = $this->findArticlesForPage($model, $articleId);

            if (1 === \count($articles)) {
                $options['tl_article.'.$articles[0]->id] = $this->getLanguageLabel($model->language);
            } else {
                $options['tl_page.'.$model->id] = $this->getLanguageLabel($model->language);
            }
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function doSwitchView($id)
    {
        list($table, $id) = explode('.', $id);

        $url = Url::removeQueryString(['switchLanguage']);

        switch ($table) {
            case 'tl_article':
                $url = Url::addQueryString('id='.$id, $url);
                break;
            case 'tl_page':
                Session::getInstance()->set('tl_page_node', (int) $id);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Table "%s" is not supported', $table));
        }

        Controller::redirect($url);
    }

    /**
     * @param PageModel $page
     * @param int       $articleId
     *
     * @return ArticleModel[]
     */
    private function findArticlesForPage(PageModel $page, $articleId)
    {
        $articles = ArticleModel::findBy(
            [
                'tl_article.pid=?',
                'tl_article.id!=?',
                '(tl_article.id=? OR tl_article.languageMain=? OR tl_article.inColumn=?)',
            ],
            [
                $page->id,
                $this->currentArticle->id,
                $articleId,
                $articleId,
                $this->currentArticle->inColumn,
                $articleId,
                $articleId,
            ],
            ['order' => 'tl_article.id=? DESC, tl_article.languageMain=? DESC']
        );

        if (!$articles instanceof Collection) {
            return [];
        }

        /** @var ArticleModel[] $models */
        $models = $articles->getModels();

        if ($articleId > 0 && ($models[0]->id === $articleId || $models[0]->languageMain === $articleId)) {
            return [$models[0]];
        }

        return $models;
    }
}

<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Contao\ArticleModel;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Input;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\System;
use League\Uri\Uri;
use League\Uri\UriModifier;

class ArticleViewListener extends AbstractViewListener
{
    /**
     * @var ArticleModel|false|null
     */
    private $currentArticle = false;

    protected function isSupported(): bool
    {
        return 'article' === (string) Input::get('do')
            && (
                ('edit' === Input::get('act') && empty(Input::get('table')))
                || ($this->getTable() === Input::get('table'))
            );
    }

    protected function getCurrentPage(): ?PageModel
    {
        if (false === $this->currentArticle) {
            if (Input::get('table') === $this->getTable() && !empty(Input::get('act'))) {
                if ('paste' !== Input::get('act')) {
                    return null;
                }

                $this->currentArticle = ArticleModel::findOneBy(['tl_article.id=(SELECT pid FROM tl_content WHERE id=?)'], [$this->dataContainer->id]);
            } else {
                $this->currentArticle = ArticleModel::findById($this->dataContainer->id);
            }
        }

        if (null === $this->currentArticle) {
            return null;
        }

        return PageModel::findWithDetails($this->currentArticle->pid);
    }

    /**
     * @return array<int|string, string>
     */
    protected function getAvailableLanguages(PageModel $page): array
    {
        $options = [];
        $masterRoot = $this->pageFinder->findMasterRootForPage($page);
        $articleId = (int) ($page->rootId === $masterRoot->id ? $this->currentArticle->id : $this->currentArticle->languageMain);

        foreach ($this->pageFinder->findAssociatedForPage($page, true, null, false) as $model) {
            $model->loadDetails();

            $articles = $this->findArticlesForPage($model, $articleId);

            // Add single article without title
            if (1 === \count($articles)) {
                $options[$articles[0]->id] = $this->getLanguageLabel($model->language);
                continue;
            }

            // Add only exact match if we have one
            foreach ($articles as $article) {
                if ($articleId > 0 && ($article->id === $articleId || $article->languageMain === $articleId)) {
                    $options[$article->id] = $this->getLanguageLabel($model->language);
                    continue 2;
                }
            }

            $articles = array_values(array_filter(
                $articles,
                fn (ArticleModel $article): bool => $article->inColumn === $this->currentArticle->inColumn,
            ));

            if (1 === \count($articles)) {
                $options[$articles[0]->id] = $this->getLanguageLabel($model->language);
                continue;
            }

            // Otherwise add all articles
            foreach ($articles as $article) {
                $options[$article->id] = $this->getLanguageLabel($model->language).': '.$article->title;
            }
        }

        return $options;
    }

    protected function doSwitchView($id): void
    {
        $uri = Uri::createFromString(System::getContainer()->get('request_stack')->getCurrentRequest()->getUri());
        $uri = UriModifier::removeParams($uri, 'switchLanguage', 'act', 'mode');
        $uri = UriModifier::mergeQuery($uri, 'id='.$id);

        throw new RedirectResponseException((string) $uri);
    }

    /**
     * @return array<ArticleModel>
     */
    private function findArticlesForPage(PageModel $page, int $articleId): array
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
            ['order' => 'tl_article.id=? DESC, tl_article.languageMain=? DESC'],
        );

        if (!$articles instanceof Collection) {
            return [];
        }

        /** @var array<ArticleModel> $models */
        $models = $articles->getModels();

        if ($articleId > 0 && ($models[0]->id === $articleId || $models[0]->languageMain === $articleId)) {
            return [$models[0]];
        }

        return $models;
    }
}

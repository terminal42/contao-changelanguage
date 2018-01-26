<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2017, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;

class PageFieldsListener
{
    /**
     * Sets rootNodes when initializing the languageMain field.
     *
     * @param mixed         $value
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function onLoadLanguageMain($value, DataContainer $dc)
    {
        if (!$dc->id || 'page' !== Input::get('do')) {
            return $value;
        }

        $page = PageModel::findWithDetails($dc->id);
        $root = PageModel::findByPk($page->rootId);

        if ($root->fallback
            && (!$root->languageRoot || ($languageRoot = PageModel::findByPk($root->languageRoot)) === null)
        ) {
            return $value;
        }

        $pageFinder = new PageFinder();
        $masterRoot = $pageFinder->findMasterRootForPage($page);

        if (null !== $masterRoot) {
            $GLOBALS['TL_DCA']['tl_page']['fields']['languageMain']['eval']['rootNodes'] = Database::getInstance()
                ->prepare('SELECT id FROM tl_page WHERE pid=? ORDER BY sorting')
                ->execute($masterRoot->id)
                ->fetchEach('id')
            ;
        }

        return $value;
    }

    /**
     * Validate input value when saving tl_page.languageMain field.
     *
     * @param mixed         $value
     * @param DataContainer $dc
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function onSaveLanguageMain($value, DataContainer $dc)
    {
        // Validate that there is no other page in the current page tree with the same languageMain assigned
        if ($value > 0) {
            $currentPage = PageModel::findWithDetails($dc->id);
            $childIds = \Database::getInstance()->getChildRecords($currentPage->rootId, 'tl_page');

            $duplicates = PageModel::findBy(
                [
                    'tl_page.id IN ('.implode(',', $childIds).')',
                    'tl_page.languageMain=?',
                    'tl_page.id!=?',
                ],
                [$value, $dc->id]
            );

            if (null !== $duplicates) {
                $labels = [];

                foreach ($duplicates as $duplicate) {
                    $labels[] = sprintf('%s (ID %s)', $duplicate->id, $duplicate->title);
                }

                throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['MSC']['duplicateMainLanguage'], implode(', ', $labels)));
            }
        }

        return $value;
    }

    /**
     * Gets list of options for language root selection (linking multiple fallback roots on different domains).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function onLanguageRootOptions(DataContainer $dc)
    {
        /** @var PageModel[] $pages */
        $pages = PageModel::findBy(
            [
                "tl_page.type='root'",
                "tl_page.fallback='1'",
                'tl_page.languageRoot=0',
                'tl_page.id!=?',
            ],
            [$dc->id]
        );

        if (null === $pages) {
            return [];
        }

        $options = [];

        foreach ($pages as $page) {
            $options[$page->id] = sprintf(
                '%s%s [%s]',
                $page->title,
                (strlen($page->dns) ? (' ('.$page->dns.')') : ''),
                $page->language
            );
        }

        return $options;
    }
}

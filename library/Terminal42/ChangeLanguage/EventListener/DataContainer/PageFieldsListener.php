<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\DataContainer;
use Contao\PageModel;

class PageFieldsListener
{
    /**
     * Validate input value when saving tl_page.languageMain field.
     *
     * @param mixed         $value
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function onSaveLanguageMain($value, DataContainer $dc)
    {
        // Validate that there is no other page in the current page tree with the same languageMain assigned
        if ($value > 0) {
            $currentPage = PageModel::findWithDetails($dc->id);

            $duplicates = PageModel::countBy(
                [
                    'id IN (' . implode(',', \Database::getInstance()->getChildRecords($currentPage->rootId, 'tl_page')) . ')',
                    'languageMain=?',
                    'id!=?'
                ],
                [$value, $dc->id]
            );

            if ($duplicates > 0) {
                throw new \RuntimeException($GLOBALS['TL_LANG']['MSC']['duplicateMainLanguage']);
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
        $pages = PageModel::findBy(
            [
                "type='root'",
                "fallback='1'",
                'languageRoot=0',
                'id!=?'
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
                (strlen($page->dns) ? (' (' . $page->dns . ')') : ''),
                $page->language
            );
        }

        return $options;
    }

}

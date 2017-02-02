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

use Contao\Controller;
use Terminal42\ChangeLanguage\PageFinder;

class InsertTagsListener
{
    /**
     * Replaces {{changelanguage_*::*}} insert tag.
     *
     * @param string $insertTag
     *
     * @return string|false
     */
    public function onReplaceInsertTags($insertTag)
    {
        $parts = trimsplit('::', $insertTag);

        if (0 !== strpos($parts[0], 'changelanguage')) {
            return false;
        }

        try {
            $pageFinder  = new PageFinder();
            $currentPage = \PageModel::findByIdOrAlias($parts[1]);
            $targetPage = $pageFinder->findAssociatedForLanguage($currentPage, $parts[2]);
        } catch (\RuntimeException $e) {
            // parent page of current page not found or not published
            return '';
        }

        return Controller::replaceInsertTags(
            sprintf(
                '{{link%s::%s}}',
                substr($parts[0], 14),
                $targetPage->id
            )
        );
    }
}

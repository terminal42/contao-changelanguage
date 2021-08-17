<?php

namespace Terminal42\ChangeLanguage\EventListener;

use Contao\Controller;
use Contao\PageModel;
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
            $pageFinder = new PageFinder();
            $currentPage = PageModel::findByIdOrAlias($parts[1]);

            if (null === $currentPage) {
                return '';
            }

            $targetPage = $pageFinder->findAssociatedForLanguage($currentPage, $parts[2]);
        } catch (\RuntimeException $e) {
            // parent page of current page not found or not published
            return '';
        }

        return Controller::replaceInsertTags(
            sprintf(
                '{{%s::%s}}',
                substr($parts[0], 15),
                $targetPage->id
            )
        );
    }
}

<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\PageModel;
use Contao\StringUtil;
use Terminal42\ChangeLanguage\PageFinder;

#[AsHook('replaceInsertTags')]
class InsertTagsListener
{
    public function __construct(private readonly InsertTagParser $parser)
    {
    }

    /**
     * Replaces {{changelanguage_*::*}} insert tag.
     *
     * @return string|false
     */
    public function __invoke(string $insertTag)
    {
        $parts = StringUtil::trimsplit('::', $insertTag);

        if (!str_starts_with((string) $parts[0], 'changelanguage')) {
            return false;
        }

        try {
            $pageFinder = new PageFinder();
            $currentPage = PageModel::findByIdOrAlias($parts[1]);

            if (null === $currentPage) {
                return '';
            }

            $targetPage = $pageFinder->findAssociatedForLanguage($currentPage, $parts[2]);
        } catch (\RuntimeException) {
            // parent page of current page not found or not published
            return '';
        }

        return $this->parser->replace(
            \sprintf(
                '{{%s::%s}}',
                substr((string) $parts[0], 15),
                $targetPage->id,
            ),
        );
    }
}

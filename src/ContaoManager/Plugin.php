<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\ContaoManager;

use Codefog\HasteBundle\CodefogHasteBundle;
use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\FaqBundle\ContaoFaqBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use MenAtWork\MultiColumnWizardBundle\MultiColumnWizardBundle;
use Terminal42\ChangeLanguage\Terminal42ChangeLanguageBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            (new BundleConfig(Terminal42ChangeLanguageBundle::class))
                ->setReplace(['changelanguage'])
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    ContaoNewsBundle::class,
                    ContaoCalendarBundle::class,
                    ContaoFaqBundle::class,
                    MultiColumnWizardBundle::class,
                    CodefogHasteBundle::class,
                ]),
        ];
    }
}

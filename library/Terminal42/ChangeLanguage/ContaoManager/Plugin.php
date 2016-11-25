<?php

namespace Terminal42\ChangeLanguage\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

class Plugin implements BundlePluginInterface
{
    /**
     * @inheritdoc
     */
    public function getBundles(ParserInterface $parser)
    {
        return $parser->parse('changelanguage', 'ini');
    }
}

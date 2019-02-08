<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\DataContainer;

trait LanguageMainTrait
{
    protected function addLanguageMainField()
    {
        $GLOBALS['TL_DCA'][$this->getTable()]['fields']['languageMain'] = [
            'label' => &$GLOBALS['TL_LANG'][$this->getTable()]['languageMain'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $this->onLanguageMainOptions($dc);
            },
            'eval' => [
                'includeBlankOption' => true,
                'blankOptionLabel' => &$GLOBALS['TL_LANG'][$this->getTable()]['languageMain'][2],
                'chosen' => true,
                'tl_class' => 'w50',
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'table' => $this->getTable()],
        ];
    }

    abstract protected function onLanguageMainOptions(DataContainer $dc);

    abstract protected function getTable();
}

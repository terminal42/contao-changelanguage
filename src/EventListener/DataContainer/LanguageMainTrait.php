<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\DataContainer;

trait LanguageMainTrait
{
    protected function addLanguageMainField(): void
    {
        $GLOBALS['TL_DCA'][$this->getTable()]['fields']['languageMain'] = [
            'label' => &$GLOBALS['TL_LANG'][$this->getTable()]['languageMain'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => fn (DataContainer $dc) => $this->onLanguageMainOptions($dc),
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

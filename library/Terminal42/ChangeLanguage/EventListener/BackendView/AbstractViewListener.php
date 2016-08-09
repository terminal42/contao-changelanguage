<?php
/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Contao\Backend;
use Contao\DataContainer;
use Terminal42\ChangeLanguage\EventListener\AbstractTableListener;

abstract class AbstractViewListener extends AbstractTableListener
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $GLOBALS['TL_DCA'][$this->table]['config']['onload_callback'][] = function(DataContainer $dc) {
            $this->onLoad($dc);
        };
    }

    /**
     * Handler for onload_callback.
     *
     * @param DataContainer $dc
     */
    public function onLoad(DataContainer $dc)
    {
        if ($dc->table !== $this->table) {
            return;
        }

        $id = (string) \Input::get('switchLanguage');

        if ('' !== $id) {
            $this->doSwitchView($id);
        }

        $languages = $this->getAvailableLanguages($dc);

        if (0 !== count($languages)) {
            $this->addSwitchLink($languages);
        }
    }

    /**
     * Adds the language switch global operation to the current table.
     *
     * @param array $languages
     */
    public function addSwitchLink(array $languages)
    {
        $GLOBALS['TL_CSS'][] = 'system/modules/changelanguage/assets/backend.css';

        array_insert(
            $GLOBALS['TL_DCA'][$this->table]['list']['global_operations'],
            0,
            [
                'switchLanguage' => [
                    'button_callback' => function () use ($languages) {
                        return $this->onSwitchButtonCallback($languages);
                    },
                ],
            ]
        );
    }

    /**
     * Returns a list of languages the user can switch to
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    abstract protected function getAvailableLanguages(DataContainer $dc);

    /**
     * Switch language to the given ID.
     *
     * @param string $id
     */
    abstract protected function doSwitchView($id);

    /**
     * Returns HTML markup for the global operation.
     *
     * @param array $languages
     *
     * @return string
     */
    private function onSwitchButtonCallback(array $languages)
    {
        if (1 === count($languages)) {
            $language = reset($languages);
            $id       = key($languages);

            return sprintf(
                '<a href="%s" class="header_switchLanguage" title="%s">%s</a>',
                Backend::addToUrl('&amp;switchLanguage=' . $id),
                sprintf($GLOBALS['TL_LANG']['MSC']['switchLanguageTo'], $language),
                sprintf($GLOBALS['TL_LANG']['MSC']['switchLanguageTo'], $language)
            );

        }

        $markup = sprintf('<div class="header_switchLanguage">%s <ul>', $GLOBALS['TL_LANG']['MSC']['switchLanguage']);

        foreach ($languages as $id => $language) {
            $markup .= sprintf(
                '<li><a href="%s" title="%s">%s</a></li>',
                Backend::addToUrl('&amp;switchLanguage=' . $id),
                sprintf($GLOBALS['TL_LANG']['MSC']['switchLanguageTo'], $language),
                $language
            );
        }

        return $markup . '</ul></div>';
    }
}

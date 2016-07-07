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

use Contao\DataContainer;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener;

class CallbackSetupListener
{
    private static $parentListeners = [
        'tl_news_archive',
        'tl_calendar',
        'tl_faq_category',
    ];

    public function onLoadDataContainer($table)
    {
        if (in_array($table, self::$parentListeners, true)) {
            $GLOBALS['TL_DCA'][$table]['config']['onload_callback'][] = function (DataContainer $dc) use ($table) {
                $listener = new ParentTableListener($table);

                $listener->onLoad($dc);
            };
        }
    }
}

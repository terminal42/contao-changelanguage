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

use Terminal42\ChangeLanguage\EventListener\DataContainer\ChildTableListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener;

class CallbackSetupListener
{
    private static $parentTables = [
        'tl_news_archive',
        'tl_calendar',
        'tl_faq_category',
    ];


    public function onLoadDataContainer($table)
    {
        if (in_array($table, self::$parentTables, true)) {
            $listener = new ParentTableListener($table);
            $listener->register();

        }
    }
}

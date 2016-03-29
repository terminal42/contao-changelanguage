<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\DataContainer;

use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;

class News
{
    /**
     * Get records from the master archive
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getMasterArchive(DataContainer $dc)
    {
        $sameDay  = $GLOBALS['TL_LANG']['tl_news']['sameDay'];
        $otherDay = $GLOBALS['TL_LANG']['tl_news']['otherDay'];
        $dayBegin = strtotime('0:00', $dc->activeRecord->date);
        $arrItems = array($sameDay => array(), $otherDay => array());

        $objItems = Database::getInstance()
            ->prepare('
                SELECT * 
                FROM tl_news 
                WHERE pid=(
                    SELECT tl_news_archive.master 
                    FROM tl_news_archive 
                    LEFT OUTER JOIN tl_news ON tl_news.pid=tl_news_archive.id 
                    WHERE tl_news.id=?
                )
                ORDER BY date DESC, time DESC
            ')
            ->execute($dc->id)
        ;

        while ($objItems->next()) {
            $group = strtotime('0:00', $objItems->date) === $dayBegin ? $sameDay : $otherDay;

            $arrItems[$group][$objItems->id] = sprintf(
                '%s [%s]',
                $objItems->headline,
                Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $objItems->time)
            );
        }

        return $arrItems;
    }

    /**
     * Show the select menu only on slave archives
     *
     * @param DataContainer $dc
     */
    public function showSelectbox(DataContainer $dc)
    {
        if ('edit' === Input::get('act')) {
            $objArchive = Database::getInstance()
                ->prepare('
                    SELECT tl_news_archive.* 
                    FROM tl_news_archive 
                    LEFT OUTER JOIN tl_news ON tl_news.pid=tl_news_archive.id 
                    WHERE tl_news.id=?
                ')
                ->execute($dc->id)
            ;

            if ($objArchive->numRows && $objArchive->master > 0) {
                $GLOBALS['TL_DCA']['tl_news']['palettes']['default'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['default']);
                $GLOBALS['TL_DCA']['tl_news']['palettes']['internal'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['internal']);
                $GLOBALS['TL_DCA']['tl_news']['palettes']['external'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['external']);
                $GLOBALS['TL_DCA']['tl_news']['fields']['headline']['eval']['tl_class'] = 'w50';
                $GLOBALS['TL_DCA']['tl_news']['fields']['alias']['eval']['tl_class'] = 'clr w50';
            }
        } else if ('editAll' === Input::get('act')) {
            $GLOBALS['TL_DCA']['tl_news']['palettes']['regular'] = preg_replace('@([,|;]{1}language)([,|;]{1})@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['regular']);
        }
    }
}

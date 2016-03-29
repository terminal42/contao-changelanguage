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

use Contao\Backend;
use Contao\DataContainer;

class News extends Backend
{

    /**
     * Get records from the master archive
     *
     * @param    DataContainer
     * @return    array
     * @link    http://www.contao.org/callbacks.html#options_callback
     */
    public function getMasterArchive(DataContainer $dc)
    {
        $sameDay = $GLOBALS['TL_LANG']['tl_news']['sameDay'];
        $otherDay = $GLOBALS['TL_LANG']['tl_news']['otherDay'];

        $arrItems = array($sameDay => array(), $otherDay => array());
        $objItems = $this->Database->prepare("SELECT * FROM tl_news WHERE pid=(SELECT tl_news_archive.master FROM tl_news_archive LEFT OUTER JOIN tl_news ON tl_news.pid=tl_news_archive.id WHERE tl_news.id=?) ORDER BY date DESC, time DESC")->execute($dc->id);

        $dayBegin = strtotime('0:00', $dc->activeRecord->date);

        while( $objItems->next() )
        {
            if (strtotime('0:00', $objItems->date) == $dayBegin)
            {
                $arrItems[$sameDay][$objItems->id] = $objItems->headline . ' [' . $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objItems->time) . ']';
            }
            else
            {
                $arrItems[$otherDay][$objItems->id] = $objItems->headline . ' [' . $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objItems->time) . ']';
            }
        }

        return $arrItems;
    }


    /**
     * Show the select menu only on slave archives
     *
     * @param    DataContainer
     * @return    void
     * @link    http://www.contao.org/callbacks.html#onload_callback
     */
    public function showSelectbox(DataContainer $dc)
    {
        if(\Input::get('act') == "edit")
        {
            $objArchive = $this->Database->prepare("SELECT tl_news_archive.* FROM tl_news_archive LEFT OUTER JOIN tl_news ON tl_news.pid=tl_news_archive.id WHERE tl_news.id=?")
                                         ->limit(1)
                                         ->execute($dc->id);

            if($objArchive->numRows && $objArchive->master > 0)
            {
                $GLOBALS['TL_DCA']['tl_news']['palettes']['default'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['default']);
                $GLOBALS['TL_DCA']['tl_news']['palettes']['internal'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['internal']);
                $GLOBALS['TL_DCA']['tl_news']['palettes']['external'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['external']);
                $GLOBALS['TL_DCA']['tl_news']['fields']['headline']['eval']['tl_class'] = 'w50';
                $GLOBALS['TL_DCA']['tl_news']['fields']['alias']['eval']['tl_class'] = 'clr w50';
            }
        }
        else if(\Input::get('act') == "editAll")
        {
            $GLOBALS['TL_DCA']['tl_news']['palettes']['regular'] = preg_replace('@([,|;]{1}language)([,|;]{1})@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['regular']);
        }
    }
}

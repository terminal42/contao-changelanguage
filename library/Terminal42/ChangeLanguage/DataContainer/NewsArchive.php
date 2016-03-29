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

class NewsArchive
{
    /**
     * Get an array of possible news archives
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getArchives(DataContainer $dc)
    {
        $arrArchives = array();
        $objArchives = Database::getInstance()
            ->prepare('SELECT * FROM tl_news_archive WHERE language!=? AND id!=? AND master=0 ORDER BY title')
            ->execute($dc->activeRecord->language, $dc->id)
        ;

        while ($objArchives->next()) {
            $arrArchives[$objArchives->id] = sprintf(
                $GLOBALS['TL_LANG']['tl_news_archive']['isSlave'],
                $objArchives->title
            );
        }

        return $arrArchives;
    }
}

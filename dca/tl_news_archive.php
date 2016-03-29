<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */


/**
 * Return if the module is not active
 */
if (!in_array('news', \ModuleLoader::getActive()))
{
    return;
}


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace('jumpTo;', 'jumpTo;{language_legend},language,master;', $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['master'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['master'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('tl_news_archive_language', 'getArchives'),
    'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_news_archive']['isMaster']),
    'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['language'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['language'],
    'exclude'                 => true,
    'search'                  => true,
    'filter'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'rgxp'=>'language', 'maxlength'=>5, 'nospace'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(5) NOT NULL default ''"
);


class tl_news_archive_language extends Backend
{

    /**
     * Get an array of possible news archives
     *
     * @param    DataContainer
     * @return    array
     * @link    http://www.contao.org/callbacks.html#options_callback
     */
    public function getArchives(DataContainer $dc)
    {
        $arrArchives = array();
        $objArchives = $this->Database->prepare("SELECT * FROM tl_news_archive WHERE language!=? AND id!=? AND master=0 ORDER BY title")->execute($dc->activeRecord->language, $dc->id);

        while( $objArchives->next() )
        {
            $arrArchives[$objArchives->id] = sprintf($GLOBALS['TL_LANG']['tl_news_archive']['isSlave'], $objArchives->title);
        }

        return $arrArchives;
    }
}

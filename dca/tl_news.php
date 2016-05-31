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
 * Prevent DB fields to be added to an empty DCA
 */
if (in_array('news', \ModuleLoader::getActive(), true))
{

    /**
     * Config
     */
    $GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][] = array('Terminal42\ChangeLanguage\DataContainer\News', 'showSelectbox');


    /**
     * Fields
     */
    $GLOBALS['TL_DCA']['tl_news']['fields']['languageMain'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_news']['languageMain'],
        'exclude'                 => false,
        'inputType'               => 'select',
        'options_callback'        => array('Terminal42\ChangeLanguage\DataContainer\News', 'getMasterArchive'),
        'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
    );

}
